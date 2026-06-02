# Why localhost worked but Render / production behaves differently

## 1. URL paths (`/emps/` vs site root)

| Environment | App URL base |
|-------------|----------------|
| Local XAMPP | `http://localhost/emps/` |
| Render | `https://hrms-vmhi.onrender.com/` (no `/emps/`) |

Old code linked to **`view_emp.php`**, **`/emps/admin_panel/...`**, etc. Those files or paths **do not exist** on Render → **404 Not Found**.

**Fix:** `includes/hrms_paths.php` + redirect stubs (`view_emp.php` → `view_emp1.php`). Deploy latest code.

## 2. Profile photos missing

On **localhost**, uploads stay on disk under `admin_panel/uploads/`.

On **Render**, the filesystem is **ephemeral** — files are lost on redeploy/restart unless you use persistent storage (S3, Render disk, etc.). The DB still has paths, so images break until re-uploaded.

**Fix:** Re-upload photos on production, or sync `admin_panel/uploads/` to the server / object storage.

## 3. One database

Local **EMPS** ≠ Aiven **defaultdb** unless env vars point to Aiven or you run `scripts/sync_local_to_aiven.ps1`.

Employees **are** created on whichever DB the site uses — check the blue box on Add Employee.

## 4. Duplicate email / visa errors

Often means the row **already exists** (including from a slow earlier submit). Search **View Employees** before adding again.

## 5. Employee profile / view actions

| Action | Correct page |
|--------|----------------|
| Grid **eye** icon | Modal via `get_employee_details.php` |
| Grid **pencil** | `edit_employee.php?id=...` |
| Old links | `view_emp.php` → redirects to `view_emp1.php` |

After deploy, hard-refresh (Ctrl+F5) on View Employees.
