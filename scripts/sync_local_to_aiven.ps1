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
    [string]$LocalHost = $(if ($env:LOCAL_DB_HOST) { $env:LOCAL_DB_HOST } else { "127.0.0.1" }),
    [string]$LocalPort = $(if ($env:LOCAL_DB_PORT) { $env:LOCAL_DB_PORT } else { "3307" }),
    [string]$LocalUser = $(if ($env:LOCAL_DB_USER) { $env:LOCAL_DB_USER } else { "root" }),
    [string]$LocalPassword = $(if ($env:LOCAL_DB_PASSWORD) { $env:LOCAL_DB_PASSWORD } else { "hrms_secret" }),
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

function Invoke-AivenDockerMysql {
    param(
        [string]$Query = "",
        [string]$StdinSql = "",
        [switch]$ImportFile
    )
    $prevEap = $ErrorActionPreference
    $ErrorActionPreference = "Continue"
    try {
        if ($ImportFile) {
            docker run --rm -v "${importFile}:/import/emps.sql:ro" mysql:8.0 mysql `
                -h $AivenHost -P $AivenPort -u $AivenUser "-p$AivenPassword" --ssl-mode=REQUIRED $AivenDatabase `
                -e "source /import/emps.sql" 2>&1 | Out-Null
        } elseif ($StdinSql -ne "") {
            $StdinSql | docker run --rm -i mysql:8.0 mysql `
                -h $AivenHost -P $AivenPort -u $AivenUser "-p$AivenPassword" --ssl-mode=REQUIRED $AivenDatabase 2>&1 | Out-Null
        } else {
            $raw = docker run --rm mysql:8.0 mysql `
                -h $AivenHost -P $AivenPort -u $AivenUser "-p$AivenPassword" --ssl-mode=REQUIRED -N $AivenDatabase `
                -e $Query 2>&1
            return @($raw | Where-Object { $_ -is [string] })
        }
        if ($LASTEXITCODE -ne 0) {
            throw "Aiven mysql command failed (exit code $LASTEXITCODE)."
        }
    } finally {
        $ErrorActionPreference = $prevEap
    }
}

# --- Step 1: Export local database ---
if (-not $SkipExport) {
    if (Test-Path $exportFile) { Remove-Item $exportFile -Force }

    Write-Host "Exporting local database [$LocalDatabase] from ${LocalHost}:${LocalPort} ..."
    Write-Host "  Docker EMPS: port 3307, password hrms_secret | XAMPP: port 3306, your root password"

    $dockerDb = docker ps --format "{{.Names}}" 2>$null | Where-Object { $_ -match "hrms.*db" } | Select-Object -First 1
    if ($dockerDb -and $LocalPort -eq "3307") {
        Write-Host "  Using Docker container: $dockerDb"
        docker exec $dockerDb mysqldump -u $LocalUser "-p$LocalPassword" --single-transaction --routines --triggers $LocalDatabase > $exportFile
    } else {
        if (-not $mysqldump) {
            Write-Error "mysqldump not found. Start Docker (hrms-db) or install XAMPP MySQL client."
            exit 1
        }
        $dumpArgs = @(
            "-h", $LocalHost,
            "-P", $LocalPort,
            "-u", $LocalUser,
            "-p$LocalPassword",
            "--single-transaction",
            "--routines",
            "--triggers",
            "--result-file=$exportFile",
            $LocalDatabase
        )
        & $mysqldump @dumpArgs 2>&1 | ForEach-Object { Write-Host "  $_" }
    }

    if (-not (Test-Path $exportFile) -or (Get-Item $exportFile).Length -lt 1000) {
        Write-Error "Export failed or file too small. Start: docker compose up -d  OR  XAMPP MySQL. Then set LOCAL_DB_PORT (3307 or 3306) and LOCAL_DB_PASSWORD."
        exit 1
    }
    $sizeMb = [math]::Round((Get-Item $exportFile).Length / 1MB, 2)
    Write-Host "Saved: $exportFile ($sizeMb MB)"
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
$dropSql = Invoke-AivenDockerMysql -Query "SELECT CONCAT('DROP TABLE IF EXISTS ``', table_name, '``;') FROM information_schema.tables WHERE table_schema='$AivenDatabase';"

if ($dropSql) {
    $dropScript = "SET FOREIGN_KEY_CHECKS=0;`n" + ($dropSql -join "`n") + "`nSET FOREIGN_KEY_CHECKS=1;"
    Invoke-AivenDockerMysql -StdinSql $dropScript
}

# --- Step 4: Import ---
Write-Host "Importing into Aiven (may take 1-3 minutes) ..."
Invoke-AivenDockerMysql -ImportFile

# --- Step 5: Verify ---
Write-Host "Verifying ..."
Invoke-AivenDockerMysql -Query "SELECT COUNT(*) AS employees FROM employees; SELECT COUNT(*) AS logins FROM emp_login; SELECT COUNT(*) AS tables_count FROM information_schema.tables WHERE table_schema='$AivenDatabase';" | ForEach-Object { Write-Host $_ }

Write-Host ""
Write-Host "Done. Aiven now matches your local EMPS snapshot."
Write-Host "On Render, new employees created at https://hrms-vmhi.onrender.com save to Aiven when DB_* env vars are set."
Write-Host "Stop adding employees only on localhost if you want them on production immediately."
