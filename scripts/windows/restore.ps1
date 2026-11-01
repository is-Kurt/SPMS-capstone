# SPMS Automated Recovery Script (Windows)
[CmdletBinding()]
param(
    [string]$BackupFile = "",
    [string]$DestDir    = ""
)

$ErrorActionPreference = "Stop"
$AppDir = (Resolve-Path "$PSScriptRoot\..\..").Path

if ([string]::IsNullOrWhiteSpace($DestDir)) {
    $DestDir = Join-Path $AppDir "..\backups"
}

function Get-DbPath($BaseDir) {
    $EnvPath = Join-Path $BaseDir ".env"
    if (Test-Path $EnvPath) {
        $envMatch = Get-Content $EnvPath | Select-String '^[ \t]*database\.default\.database[ \t]*=[ \t]*(.+)'
        if ($envMatch) {
            $envVal = $envMatch.Matches[0].Groups[1].Value.Trim().Trim('"').Trim("'")
            if ($envVal -match 'writable[\\/](.+)$') {
                $rel = $matches[1]
                return (Join-Path $BaseDir "writable\$rel")
            } elseif (Test-Path $envVal) {
                return $envVal
            } elseif (Test-Path (Join-Path $BaseDir $envVal)) {
                return (Join-Path $BaseDir $envVal)
            }
        }
    }

    $candidates = @(
        (Join-Path $BaseDir "writable\database\spms_db.sqlite3"),
        (Join-Path $BaseDir "writable\spms_db.sqlite3"),
        (Join-Path $BaseDir "writable\database.sqlite")
    )
    foreach ($cand in $candidates) {
        if ((Test-Path $cand) -and ((Get-Item $cand).Length -gt 0)) {
            return $cand
        }
    }
    return (Join-Path $BaseDir "writable\database\spms_db.sqlite3")
}

function Invoke-SQLiteSnapshot($Src, $Dst) {
    $phpCmd = $null

    if (Get-Command php -ErrorAction SilentlyContinue) {
        $phpCmd = "php"
    } elseif (Get-Command sqlite3 -ErrorAction SilentlyContinue) {
        & sqlite3 $Src ".backup '$Dst'"
        & sqlite3 $Dst "PRAGMA integrity_check;"
        return
    } else {
        $fallbacks = @(
            (Get-ChildItem "C:\laragon\bin\php\php-*\php.exe" -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName -First 1),
            "C:\xampp\php\php.exe",
            "C:\php\php.exe",
            "C:\tools\php\php.exe",
            (Join-Path $env:USERPROFILE "scoop\apps\php\current\php.exe")
        )
        foreach ($cand in $fallbacks) {
            if ($cand -and (Test-Path $cand)) {
                $phpCmd = $cand
                break
            }
        }
    }

    if ($phpCmd) {
        & $phpCmd -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle(''PRAGMA integrity_check''),PHP_EOL;' -- $Src $Dst
    } elseif ((Get-Command docker -ErrorAction SilentlyContinue) -and (docker ps --format '{{.Names}}' | Select-String '^php_app$')) {
        docker exec -i php_app php -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle(''PRAGMA integrity_check''),PHP_EOL;' -- $Src $Dst
    } else {
        throw "Neither php, sqlite3, Laragon/XAMPP PHP, nor Docker php_app found to perform safe SQLite snapshot."
    }
}

$DbPath = Get-DbPath $AppDir

# If no backup file specified, automatically pick the latest backup!
if ([string]::IsNullOrWhiteSpace($BackupFile)) {
    $latest = Get-ChildItem (Join-Path $DestDir "spms-*.tar.gz") -ErrorAction SilentlyContinue | 
              Sort-Object LastWriteTime -Descending | 
              Select-Object -First 1
    if (-not $latest) {
        Write-Error "No backup files found in $DestDir"
        return
    }
    $BackupFile = $latest.FullName
} elseif (-not (Test-Path $BackupFile)) {
    # Check if a filename inside $DestDir was passed
    $alt = Join-Path $DestDir $BackupFile
    if (Test-Path $alt) {
        $BackupFile = $alt
    } else {
        Write-Error "Backup file not found: $BackupFile"
        return
    }
}

Write-Host ">>> Restoring from: $BackupFile" -ForegroundColor Cyan

# Extract to temp staging directory
$R = Join-Path $env:TEMP ("spms-restore-" + (Get-Random))
New-Item -ItemType Directory -Force $R | Out-Null

try {
    Write-Host ">>> [1/4] Extracting backup bundle..." -ForegroundColor Gray
    tar -xzf $BackupFile -C $R

    # Pre-restore safety snapshot of live database (if exists and not empty)
    if ((Test-Path $DbPath) -and ((Get-Item $DbPath).Length -gt 0)) {
        $Now = Get-Date -Format 'yyyyMMdd-HHmm'
        $SafetySnapshot = Join-Path $DestDir "before-restore-$Now.sqlite3"
        Write-Host ">>> [2/4] Saving safety snapshot of current DB to $SafetySnapshot..." -ForegroundColor Gray
        Invoke-SQLiteSnapshot $DbPath $SafetySnapshot
    } else {
        Write-Host ">>> [2/4] Skipping safety snapshot (no existing active database found at $DbPath)..." -ForegroundColor Gray
    }

    # Restore Database
    Write-Host ">>> [3/4] Restoring SQLite database to $DbPath..." -ForegroundColor Gray
    New-Item -ItemType Directory -Force (Split-Path $DbPath -Parent) | Out-Null
    if (Test-Path "$R\spms_db.sqlite3") {
        # Remove stale or locked file for clean overwrite
        Remove-Item -Force "$DbPath-wal" -ErrorAction SilentlyContinue
        Remove-Item -Force "$DbPath-shm" -ErrorAction SilentlyContinue
        try {
            Remove-Item -Force "$DbPath" -ErrorAction SilentlyContinue
            Copy-Item "$R\spms_db.sqlite3" "$DbPath" -Force
        } catch {
            # If process lock prevents direct overwrite, use SQLite Online Backup API
            Invoke-SQLiteSnapshot "$R\spms_db.sqlite3" "$DbPath"
        }
        Invoke-SQLiteSnapshot "$DbPath" "$DbPath.check"
        Remove-Item -Force "$DbPath.check" -ErrorAction SilentlyContinue
    }

    # Restore Uploads
    Write-Host ">>> [4/4] Restoring upload folders..." -ForegroundColor Gray
    Remove-Item -Recurse -Force "$AppDir\writable\uploads" -ErrorAction SilentlyContinue
    Remove-Item -Recurse -Force "$AppDir\public\uploads"   -ErrorAction SilentlyContinue

    New-Item -ItemType Directory -Force "$AppDir\writable" | Out-Null
    New-Item -ItemType Directory -Force "$AppDir\public"   | Out-Null

    if (Test-Path "$R\writable-uploads.tar.gz") {
        tar -xzf "$R\writable-uploads.tar.gz" -C "$AppDir\writable"
    }
    if (Test-Path "$R\public-uploads.tar.gz") {
        tar -xzf "$R\public-uploads.tar.gz" -C "$AppDir\public"
    }

    Write-Host "`n[OK] Recovery completed successfully!" -ForegroundColor Green
}
finally {
    Remove-Item -Recurse -Force $R -ErrorAction SilentlyContinue
}



