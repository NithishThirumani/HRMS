# HRMS Docker Setup

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running

## Quick start

```powershell
cd c:\Users\Nithish\Desktop\hrms
docker compose up -d --build
```

First start may take several minutes while MariaDB imports `database/emps.sql` into database **EMPS** (same name as XAMPP).

## URLs

| Page | URL |
|------|-----|
| Login | http://localhost:8080/emps/login.php |
| Setup admin (first time) | http://localhost:8080/emps/setup_admin.php |
| Admin panel | http://localhost:8080/emps/admin_panel/ |
| User panel | http://localhost:8080/emps/user_panel/ |
| HR panel | http://localhost:8080/emps/hr_panel/ |
| HOD panel | http://localhost:8080/emps/hod_panel/ |

## Database

- **Host (from web container):** `db`
- **Host (from your machine):** `localhost`
- **Port:** `3307`
- **User:** `root`
- **Password:** `hrms_secret`
- **Database:** `EMPS`

## Import your data (`emps.sql`)

Your senior’s command (XAMPP / local MySQL, from the `database` folder):

```powershell
cd c:\Users\Nithish\Desktop\hrms\database
mysql -u root -p EMPS < emps.sql
```

Use your local MySQL root password when prompted (XAMPP default is often empty — press Enter).

**Docker equivalent** (password is `hrms_secret`, no prompt):

```powershell
cd c:\Users\Nithish\Desktop\hrms
docker compose cp database/emps.sql db:/tmp/emps.sql
docker compose exec db sh -c "tail -n +2 /tmp/emps.sql | mysql -uroot -phrms_secret EMPS"
docker compose up -d
```

The `tail -n +2` skips the first line of `emps.sql` (MariaDB sandbox marker) so the import does not fail with `Unknown command '\-'`.

If the `EMPS` database does not exist yet in Docker:

```powershell
docker compose exec db mysql -uroot -phrms_secret -e "CREATE DATABASE IF NOT EXISTS EMPS;"
```

Then run the import commands above.

**Fresh Docker DB with your dump only** (wipes existing Docker DB data):

```powershell
docker compose down -v
docker compose up -d --build
```

## Commands

```powershell
# View logs
docker compose logs -f web
docker compose logs -f db

# Stop
docker compose down

# Reset database (deletes all data)
docker compose down -v
docker compose up -d --build
```

## Local XAMPP (without Docker)

Unset Docker env vars and use defaults in `includes/db_connection.php`: `localhost`, `EMPS`, password `Nizam123$`.
