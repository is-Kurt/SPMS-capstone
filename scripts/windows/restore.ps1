# SPMS Automated Recovery Script (Windows)
[CmdletBinding()]
param(
    [string]$BackupFile = "",
    [string]$DestDir    = "C:\laragon\www\backups"
)

$ErrorActionPreference = "Stop"
$AppDir = (Resolve-Path "$PSScriptRoot\..\..").Path
$DbPath = Join-Path $AppDir "writable\database\spms_db.sqlite3"
$PhpExe = "php"

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

    # Pre-restore safety snapshot of live database
    if (Test-Path $DbPath) {
        $Now = Get-Date -Format 'yyyyMMdd-HHmm'
        $SafetySnapshot = Join-Path $DestDir "before-restore-$Now.sqlite3"
        Write-Host ">>> [2/4] Saving safety snapshot of current DB to $SafetySnapshot..." -ForegroundColor Gray
        & $PhpExe -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);' -- $DbPath $SafetySnapshot
    }

    # Restore Database
    Write-Host ">>> [3/4] Restoring SQLite database..." -ForegroundColor Gray
    New-Item -ItemType Directory -Force (Split-Path $DbPath -Parent) | Out-Null
    if (Test-Path "$R\spms_db.sqlite3") {
        & $PhpExe -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle(''PRAGMA integrity_check''),PHP_EOL;' -- "$R\spms_db.sqlite3" $DbPath
    }

    # Restore Uploads
    Write-Host ">>> [4/4] Restoring upload folders..." -ForegroundColor Gray
    Remove-Item -Recurse -Force "$AppDir\writable\uploads" -ErrorAction SilentlyContinue
    Remove-Item -Recurse -Force "$AppDir\public\uploads"   -ErrorAction SilentlyContinue

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

