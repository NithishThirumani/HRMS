# Localhost vs Aiven — two different databases

## The important point

| Environment | App URL | Database |
|-------------|---------|----------|
| **Localhost (XAMPP)** | `http://localhost/emps/` | Usually **local MySQL** (`EMPS` on your PC) |
| **Render (production)** | `https://hrms-vmhi.onrender.com/` | **Aiven** `defaultdb` (cloud) |

They are **not the same database**.

- Employee you **add on localhost** → saved in **local MySQL only**
- Employee on **Render** → only what was in **`database/emps.sql`** when we migrated, plus anything added **through the live site** on Render

That is why new employees on localhost **do not appear** on Render leave / view employees.

## How to confirm where localhost points

Check `includes/db_connection.php` fallback when `DB_*` env vars are **not** set:

- Host: `localhost`
- Database: `EMPS` (local)

Render sets `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_SSL` → Aiven.

## Options to sync new data to production

1. **Add employees on the live site** (Render URL) so data goes straight to Aiven.
2. **Re-export local DB** and import to Aiven (replaces cloud data):
   ```bash
   mysqldump -u root -p EMPS > fresh_emps.sql
   # then import to Aiven (see scripts/migrate_aiven.ps1)
   ```
3. **Export only new rows** (advanced): dump `employees` + `emp_login` for new IDs and import into Aiven.

## After migration snapshot

Aiven has **141** employees from the dump. Anything created locally **after** that export is only on your PC until you sync.
