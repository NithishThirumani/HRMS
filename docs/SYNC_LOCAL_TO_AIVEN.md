# Sync local EMPS → Aiven (one database for production)

## How it works after sync

```
localhost/emps/  ──writes──►  Local MySQL (EMPS)     ← development only
                                    │
                                    │  sync_local_to_aiven.ps1
                                    ▼
Render URL     ──writes──►  Aiven (defaultdb)      ← production (live site)
```

- **Re-import** copies your **current local** data to Aiven (overwrites cloud).
- **Creating users on the Render URL** saves to **Aiven** automatically (if Render env vars are set below).
- **Creating users on localhost** saves to **local EMPS only** until you sync again.

## Render must use Aiven (check once)

Render → Web Service → **Environment**:

| Variable | Value |
|----------|--------|
| `DB_HOST` | `mysql-2b731ab9-nithishthirumani-a052.l.aivencloud.com` |
| `DB_PORT` | `15854` |
| `DB_USER` | `avnadmin` |
| `DB_PASSWORD` | *(Aiven password)* |
| `DB_NAME` | `defaultdb` |
| `DB_SSL` | `true` |

Redeploy after changes.

## Sync steps (PowerShell)

1. Start **XAMPP MySQL** (local database running).

2. Set Aiven credentials (use your real password):

```powershell
cd c:\NEW\hrms
$env:AIVEN_HOST = "mysql-2b731ab9-nithishthirumani-a052.l.aivencloud.com"
$env:AIVEN_PORT = "15854"
$env:AIVEN_USER = "avnadmin"
$env:AIVEN_PASSWORD = "YOUR_AIVEN_PASSWORD"
$env:AIVEN_DATABASE = "defaultdb"
```

3. If local MySQL user/password differ:

```powershell
$env:LOCAL_DB_USER = "root"
$env:LOCAL_DB_PASSWORD = "Nizam123$"
$env:LOCAL_DB_NAME = "EMPS"
```

4. Run sync (**replaces all data in Aiven**):

```powershell
.\scripts\sync_local_to_aiven.ps1
```

5. Confirm counts printed at the end match what you expect locally.

6. Test Render: add a test employee on the **live URL**, then in Aiven:

```sql
SELECT eid, full_name FROM employees ORDER BY id DESC LIMIT 5;
```

## Export without XAMPP mysqldump (Docker)

If mysqldump is not installed on Windows, export via Docker:

```powershell
docker run --rm mysql:8.0 mysqldump -h host.docker.internal -u root -pNizam123$ --single-transaction EMPS > database\local_export.sql
.\scripts\sync_local_to_aiven.ps1 -SkipExport
```

(Use `-SkipExport` only after `local_export.sql` exists.)

## Ongoing workflow

| Task | Where to do it |
|------|----------------|
| Production hires / real data | **Render URL** |
| Experiments / dev | localhost, then **sync** before demo |
| Full refresh cloud from PC | Run `sync_local_to_aiven.ps1` again |
