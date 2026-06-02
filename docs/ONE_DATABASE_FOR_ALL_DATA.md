# One database for all HRMS data (employees, salary, leave)

## Rule

**New employees, salary, and leave only appear with “old” data when the app uses the same MySQL database.**

| Where you use the app | Database (typical) |
|----------------------|---------------------|
| Localhost / Docker | `EMPS` on `localhost:3307` |
| Render / communik (production) | Aiven `defaultdb` (`DB_HOST` env vars) |

If you add an employee on **localhost**, they exist only in **local MySQL**. Production (Aiven) will not show them until you run `scripts/sync_local_to_aiven.ps1` or add the employee on the **live URL**.

## Where each type of data is stored

| Action | Tables |
|--------|--------|
| Add employee | `employees`, `emp_login` |
| Add salary | `sal` (and related payroll tables) |
| Leave | `leave_applications`, balances, etc. |

Uploads (photo, visa/passport files) are on the **server disk** under e.g. `admin_panel/uploads/`, not in MySQL.

## Production checklist

1. On Render (or communik), set `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_SSL=true` to **Aiven**.
2. Add employees only on that live site **or** sync local → Aiven after testing locally.
3. On **Add Employee**, check the blue info box: it shows the active database host/name.

## Sync local data to Aiven

See [SYNC_LOCAL_TO_AIVEN.md](SYNC_LOCAL_TO_AIVEN.md).
