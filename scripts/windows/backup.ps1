# SPMS Automated Backup Script (Windows)
[CmdletBinding()]
param(
    [string]$DestDir = "C:\laragon\www\backups"
)

$ErrorActionPreference = "Stop"
$AppDir = (Resolve-Path "$PSScriptRoot\..\..").Path
$DbPath = Join-Path $AppDir "writable\database\spms_db.sqlite3"
$PhpExe = "php"

# Ensure destination directory exists
New-Item -ItemType Directory -Force $DestDir | Out-Null

$Stamp   = Get-Date -Format 'yyyyMMdd-HHmm'
$WorkDir = Join-Path $DestDir "spms-$Stamp"
New-Item -ItemType Directory -Force $WorkDir | Out-Null

Write-Host ">>> Starting SPMS Backup ($Stamp)..." -ForegroundColor Cyan

# 1. Safe SQLite live snapshot
if (Test-Path $DbPath) {
    Write-Host ">>> [1/4] Creating SQLite database snapshot..." -ForegroundColor Gray
    & $PhpExe -r '$s=new SQLite3($argv[1]);$d=new SQLite3($argv[2]);$s->backup($d);echo $d->querySingle(''PRAGMA integrity_check''),PHP_EOL;' -- $DbPath "$WorkDir\spms_db.sqlite3"
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

Write-Host "`n[OK] Backup completed successfully!" -ForegroundColor Green
Get-ChildItem $FinalArchive | Select-Object Name, Length, LastWriteTime

