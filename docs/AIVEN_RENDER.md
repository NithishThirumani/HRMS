# Aiven MySQL + Render

## Connection details (from Aiven console)

Parse your service URI:

`mysql://USER:PASSWORD@HOST:PORT/DATABASE?ssl-mode=REQUIRED`

| Render env var | Aiven value |
|----------------|-------------|
| `DB_HOST` | Hostname only (e.g. `mysql-xxxx.l.aivencloud.com`) |
| `DB_PORT` | Port (e.g. `15854`) |
| `DB_USER` | e.g. `avnadmin` |
| `DB_PASSWORD` | Your Aiven password |
| `DB_NAME` | e.g. `defaultdb` |
| `DB_SSL` | `1` or `true` (required for Aiven) |

**Paths on Render:** The app auto-detects Render (`RENDER` env) and uses site root instead of `/emps/`. Optional override: `APP_BASE_PATH=` (empty) or `APP_BASE_PATH=/emps` for subfolder deploys.

## Migrate database (Windows + Docker)

```powershell
cd c:\NEW\hrms
$env:AIVEN_HOST = "your-host.l.aivencloud.com"
$env:AIVEN_PORT = "15854"
$env:AIVEN_USER = "avnadmin"
$env:AIVEN_PASSWORD = "your-password"
$env:AIVEN_DATABASE = "defaultdb"
.\scripts\migrate_aiven.ps1
```

Or one-shot import of the prepared file:

```powershell
docker run --rm -v "c:/NEW/hrms/database/emps_aiven.sql:/import/emps.sql:ro" mysql:8.0 `
  mysql -h HOST -P PORT -u USER -pPASSWORD --ssl-mode=REQUIRED defaultdb `
  -e "source /import/emps.sql"
```

## Verify

```sql
SHOW TABLES;
SELECT COUNT(*) FROM employees;
SELECT COUNT(*) FROM admin;
```

## Security

- Never commit passwords or full connection URIs to Git.
- Rotate the Aiven password if it was shared in chat or tickets.
