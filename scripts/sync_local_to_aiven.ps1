# Export local HRMS MySQL (XAMPP) and replace Aiven defaultdb with that data.
#
# BEFORE RUNNING — set Aiven credentials (do not commit passwords):
#   $env:AIVEN_HOST = "mysql-xxxx.l.aivencloud.com"
#   $env:AIVEN_PORT = "15854"
#   $env:AIVEN_USER = "avnadmin"
#   $env:AIVEN_PASSWORD = "your-aiven-password"
#   $env:AIVEN_DATABASE = "defaultdb"
#
# Optional local overrides:
#   $env:LOCAL_DB_HOST = "localhost"
#   $env:LOCAL_DB_USER = "root"
#   $env:LOCAL_DB_PASSWORD = "Nizam123$"
#   $env:LOCAL_DB_NAME = "EMPS"
#
# Usage:
#   cd c:\NEW\hrms
#   .\scripts\sync_local_to_aiven.ps1

param(
    [string]$AivenHost = $env:AIVEN_HOST,
    [string]$AivenPort = $(if ($env:AIVEN_PORT) { $env:AIVEN_PORT } else { "15854" }),
    [string]$AivenUser = $env:AIVEN_USER,
    [string]$AivenPassword = $env:AIVEN_PASSWORD,
    [string]$AivenDatabase = $(if ($env:AIVEN_DATABASE) { $env:AIVEN_DATABASE } else { "defaultdb" }),
    [string]$LocalHost = $(if ($env:LOCAL_DB_HOST) { $env:LOCAL_DB_HOST } else { "localhost" }),
    [string]$LocalUser = $(if ($env:LOCAL_DB_USER) { $env:LOCAL_DB_USER } else { "root" }),
    [string]$LocalPassword = $(if ($env:LOCAL_DB_PASSWORD) { $env:LOCAL_DB_PASSWORD } else { "Nizam123$" }),
    [string]$LocalDatabase = $(if ($env:LOCAL_DB_NAME) { $env:LOCAL_DB_NAME } else { "EMPS" }),
    [switch]$SkipExport
)

$ErrorActionPreference = "Stop"
$root = Split-Path $PSScriptRoot -Parent
$exportFile = Join-Path $root "database\local_export.sql"
$importFile = Join-Path $root "database\emps_aiven.sql"

$mysqlPaths = @(
    "C:\xampp\mysql\bin\mysqldump.exe",
    "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
)
$mysqldump = $mysqlPaths | Where-Object { Test-Path $_ } | Select-Object -First 1

if (-not $AivenHost -or -not $AivenUser -or -not $AivenPassword) {
    Write-Error "Set AIVEN_HOST, AIVEN_USER, AIVEN_PASSWORD before running."
    exit 1
}

# --- Step 1: Export local database ---
if (-not $SkipExport) {
    if (-not $mysqldump) {
        Write-Error "mysqldump not found. Install XAMPP/MySQL client or use Docker export (see docs/SYNC_LOCAL_TO_AIVEN.md)."
        exit 1
    }

    Write-Host "Exporting local database [$LocalDatabase] from $LocalHost ..."
    # XAMPP / MariaDB 10.x mysqldump does not support --set-gtid-purged
    $dumpArgs = @(
        "-h", $LocalHost,
        "-u", $LocalUser,
        "-p$LocalPassword",
        "--single-transaction",
        "--routines",
        "--triggers",
        $LocalDatabase
    )
    & $mysqldump @dumpArgs | Set-Content -Path $exportFile -Encoding utf8
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Local mysqldump failed. Check LOCAL_DB_* credentials and that MySQL is running."
        exit 1
    }
    Write-Host "Saved: $exportFile ($((Get-Item $exportFile).Length) bytes)"
} elseif (-not (Test-Path $exportFile)) {
    Write-Error "SkipExport set but $exportFile does not exist."
    exit 1
}

# --- Step 2: Prepare file for Aiven ---
Write-Host "Preparing SQL for Aiven ..."
$content = [System.IO.File]::ReadAllText($exportFile)
$content = $content -replace '/\*!50017 DEFINER=`[^`]+`@`[^`]+`\*/\s*', ''
$header = @"
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SESSION sql_require_primary_key = 0;

"@
[System.IO.File]::WriteAllText($importFile, $header + $content)

# --- Step 3: Drop all tables in Aiven target DB ---
Write-Host "Dropping existing tables in Aiven [$AivenDatabase] ..."
$dropSql = docker run --rm mysql:8.0 mysql `
    -h $AivenHost -P $AivenPort -u $AivenUser "-p$AivenPassword" --ssl-mode=REQUIRED -N $AivenDatabase `
    -e "SELECT CONCAT('DROP TABLE IF EXISTS ``', table_name, '``;') FROM information_schema.tables WHERE table_schema='$AivenDatabase';" 2>$null

if ($dropSql) {
    $script = "SET FOREIGN_KEY_CHECKS=0;`n" + ($dropSql -join "`n") + "`nSET FOREIGN_KEY_CHECKS=1;"
    $script | docker run --rm -i mysql:8.0 mysql `
        -h $AivenHost -P $AivenPort -u $AivenUser "-p$AivenPassword" --ssl-mode=REQUIRED $AivenDatabase 2>&1 | Out-Null
}

# --- Step 4: Import ---
Write-Host "Importing into Aiven (may take 1-3 minutes) ..."
docker run --rm -v "${importFile}:/import/emps.sql:ro" mysql:8.0 mysql `
    -h $AivenHost -P $AivenPort -u $AivenUser "-p$AivenPassword" --ssl-mode=REQUIRED $AivenDatabase `
    -e "source /import/emps.sql"

if ($LASTEXITCODE -ne 0) {
    Write-Error "Import failed. See message above."
    exit 1
}

# --- Step 5: Verify ---
Write-Host "Verifying ..."
docker run --rm mysql:8.0 mysql `
    -h $AivenHost -P $AivenPort -u $AivenUser "-p$AivenPassword" --ssl-mode=REQUIRED $AivenDatabase `
    -e "SELECT COUNT(*) AS employees FROM employees; SELECT COUNT(*) AS logins FROM emp_login; SELECT COUNT(*) AS tables_count FROM information_schema.tables WHERE table_schema='$AivenDatabase';"

Write-Host ""
Write-Host "Done. Aiven now matches your local EMPS snapshot."
Write-Host "On Render, new employees created at https://hrms-vmhi.onrender.com save to Aiven when DB_* env vars are set."
Write-Host "Stop adding employees only on localhost if you want them on production immediately."
