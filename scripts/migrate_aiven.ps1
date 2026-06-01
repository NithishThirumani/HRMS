# Import HRMS schema/data into Aiven MySQL (SSL required).
# Usage:
#   $env:AIVEN_HOST = "mysql-xxxx.l.aivencloud.com"
#   $env:AIVEN_PORT = "15854"
#   $env:AIVEN_USER = "avnadmin"
#   $env:AIVEN_PASSWORD = "your-password"
#   $env:AIVEN_DATABASE = "defaultdb"
#   .\scripts\migrate_aiven.ps1

param(
    [string]$Host = $env:AIVEN_HOST,
    [string]$Port = $(if ($env:AIVEN_PORT) { $env:AIVEN_PORT } else { "3306" }),
    [string]$User = $env:AIVEN_USER,
    [string]$Password = $env:AIVEN_PASSWORD,
    [string]$Database = $(if ($env:AIVEN_DATABASE) { $env:AIVEN_DATABASE } else { "defaultdb" }),
    [string]$SourceSql = "$(Split-Path $PSScriptRoot -Parent)\database\emps.sql"
)

if (-not $Host -or -not $User -or -not $Password) {
    Write-Error "Set AIVEN_HOST, AIVEN_USER, AIVEN_PASSWORD (and optional AIVEN_PORT, AIVEN_DATABASE)."
    exit 1
}

$outSql = Join-Path (Split-Path $SourceSql) "emps_aiven.sql"
$content = [System.IO.File]::ReadAllText($SourceSql)
$content = $content -replace '/\*!50017 DEFINER=`[^`]+`@`[^`]+`\*/\s*', ''
$header = "SET NAMES utf8mb4;`nSET FOREIGN_KEY_CHECKS = 0;`nSET SESSION sql_require_primary_key = 0;`n"
[System.IO.File]::WriteAllText($outSql, $header + $content)

Write-Host "Importing $outSql to $Host ..."

docker run --rm -v "${outSql}:/import/emps.sql:ro" mysql:8.0 `
    mysql -h $Host -P $Port -u $User "-p$Password" --ssl-mode=REQUIRED $Database `
    -e "source /import/emps.sql"

if ($LASTEXITCODE -eq 0) {
    Write-Host "Done. Verify with: SHOW TABLES; SELECT COUNT(*) FROM employees;"
} else {
    Write-Error "Import failed. If sql_require_primary_key error, drop partial tables and re-run."
    exit $LASTEXITCODE
}
