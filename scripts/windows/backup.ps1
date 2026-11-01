# SPMS Automated Backup Script (Windows)
[CmdletBinding()]
param(
    [string]$DestDir = ""
)

$ErrorActionPreference = "Stop"
$AppDir = (Resolve-Path "$PSScriptRoot\..\..").Path

if ([string]::IsNullOrWhiteSpace($DestDir)) {
    $DestDir = Join-Path $AppDir "..\backups"
}

# Ensure destination directory exists
New-Item -ItemType Directory -Force $DestDir | Out-Null

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
        (Join-Path $BaseDir "writable\spms_db"),
        (Join-Path $BaseDir "writable\database\spms_db.sqlite3"),
        (Join-Path $BaseDir "writable\spms_db.sqlite3"),
        (Join-Path $BaseDir "writable\database.sqlite")
    )
    foreach ($cand in $candidates) {
        if ((Test-Path $cand) -and ((Get-Item $cand).Length -gt 0)) {
            return $cand
        }
    }
    return (Join-Path $BaseDir "writable\spms_db")
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
$Stamp   = Get-Date -Format 'yyyyMMdd-HHmm'
$WorkDir = Join-Path $DestDir "spms-$Stamp"
New-Item -ItemType Directory -Force $WorkDir | Out-Null

Write-Host ">>> Starting SPMS Backup ($Stamp)..." -ForegroundColor Cyan

# 1. Safe SQLite live snapshot
if (Test-Path $DbPath) {
    Write-Host ">>> [1/4] Creating SQLite database snapshot from $DbPath..." -ForegroundColor Gray
    Invoke-SQLiteSnapshot $DbPath "$WorkDir\spms_db.sqlite3"
} else {
    Write-Warning "Database file not found at $DbPath"
}

# 2. Archive uploads
Write-Host ">>> [2/4] Archiving uploads..." -ForegroundColor Gray
New-Item -ItemType Directory -Force "$AppDir\writable\uploads","$AppDir\public\uploads" | Out-Null
tar -czf "$WorkDir\writable-uploads.tar.gz" -C "$AppDir\writable" uploads
tar -czf "$WorkDir\public-uploads.tar.gz"   -C "$AppDir\public"   uploads

# 3. Backup .env
Write-Host ">>> [3/4] Copying environment config..." -ForegroundColor Gray
if (Test-Path "$AppDir\.env") {
    Copy-Item "$AppDir\.env" "$WorkDir\env.backup"
}

# 4. Final bundle
Write-Host ">>> [4/4] Creating master archive..." -ForegroundColor Gray
$FinalArchive = Join-Path $DestDir "spms-$Stamp.tar.gz"
tar -czf $FinalArchive -C $WorkDir .
Remove-Item -Recurse -Force $WorkDir -ErrorAction SilentlyContinue

# 5. Clean up old backups (retention: keep last 30 days)
Get-ChildItem (Join-Path $DestDir "spms-*.tar.gz") -ErrorAction SilentlyContinue | 
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } | 
    Remove-Item -Force -ErrorAction SilentlyContinue

Write-Host "`n[OK] Backup completed successfully!" -ForegroundColor Green
Get-ChildItem $FinalArchive | Select-Object Name, Length, LastWriteTime

