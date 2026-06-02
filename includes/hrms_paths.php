<?php
/**
 * Application base path for URLs and session cookies.
 * Local XAMPP: /emps   |   Render (root deploy): empty
 *
 * Override anytime: APP_BASE_PATH=/emps  or  APP_BASE_PATH=  (empty = site root)
 */
if (!function_exists('hrms_base_path')) {
    function hrms_base_path(): string
    {
        $env = getenv('APP_BASE_PATH');
        if ($env !== false) {
            $env = trim($env);
            if ($env === '' || $env === '/') {
                return '';
            }
            return '/' . trim($env, '/');
        }

        // Render / cloud hosts serve from domain root, not /emps/
        if (getenv('RENDER') || getenv('RENDER_EXTERNAL_URL')) {
            return '';
        }

        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host !== '' && (str_contains($host, 'onrender.com') || str_contains($host, 'render.com'))) {
            return '';
        }

        // Shared hosting: app under /emps/ (e.g. communik.san-solutions.in/emps/)
        if ($host !== '' && (str_contains($host, 'san-solutions.in') || str_contains($host, 'communik'))) {
            return '/emps';
        }

        return '/emps';
    }
}

if (!function_exists('hrms_url')) {
    /** Build a path from site root, e.g. hrms_url('admin_panel/index.php') */
    function hrms_url(string $path = ''): string
    {
        $path = ltrim($path, '/');
        $base = hrms_base_path();

        if ($base === '') {
            return $path === '' ? '/' : '/' . $path;
        }

        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('hrms_redirect')) {
    function hrms_redirect(string $path): void
    {
        header('Location: ' . hrms_url($path));
        exit();
    }
}

if (!function_exists('hrms_cookie_path')) {
    function hrms_cookie_path(): string
    {
        $base = hrms_base_path();
        return $base === '' ? '/' : $base . '/';
    }
}

if (!function_exists('hrms_user_panel_url')) {
    function hrms_user_panel_url(string $path = 'index.php'): string
    {
        return hrms_url('user_panel/' . ltrim($path, '/'));
    }
}

if (!function_exists('hrms_admin_panel_url')) {
    function hrms_admin_panel_url(string $path = 'index.php'): string
    {
        return hrms_url('admin_panel/' . ltrim($path, '/'));
    }
}

if (!function_exists('hrms_hr_panel_url')) {
    function hrms_hr_panel_url(string $path = 'index.php'): string
    {
        return hrms_url('hr_panel/' . ltrim($path, '/'));
    }
}

if (!function_exists('hrms_employee_upload_url')) {
    /**
     * Turn DB path (e.g. uploads/profile_pics/x.jpg) into a full site URL for <img src>.
     *
     * @param string|null $storedPath Value from employees.profile_pic / visa_doc / etc.
     * @param string      $panel      admin_panel | hr_panel | hod_panel
     */
    function hrms_employee_upload_url(?string $storedPath, string $panel = 'admin_panel'): string
    {
        $default = hrms_url($panel . '/img/default-avatar.svg');

        if ($storedPath === null || trim($storedPath) === '') {
            return $default;
        }

        $storedPath = str_replace('\\', '/', trim($storedPath));

        if (preg_match('#^(admin_panel|hr_panel|hod_panel)/#', $storedPath)) {
            $relative = $storedPath;
        } elseif (str_starts_with($storedPath, 'uploads/')) {
            $relative = $panel . '/' . $storedPath;
        } else {
            $relative = $panel . '/uploads/profile_pics/' . basename($storedPath);
        }

        $diskPath = dirname(__DIR__) . '/' . $relative;
        if (is_file($diskPath)) {
            return hrms_url($relative);
        }

        return $default;
    }
}

if (!function_exists('hrms_employee_file_url')) {
    /** Public URL for a stored upload, or null if the file is not on disk. */
    function hrms_employee_file_url(?string $storedPath, string $panel = 'admin_panel'): ?string
    {
        if ($storedPath === null || trim($storedPath) === '') {
            return null;
        }

        $storedPath = str_replace('\\', '/', trim($storedPath));

        if (preg_match('#^(admin_panel|hr_panel|hod_panel)/#', $storedPath)) {
            $relative = $storedPath;
        } elseif (str_starts_with($storedPath, 'uploads/')) {
            $relative = $panel . '/' . $storedPath;
        } else {
            $relative = $panel . '/uploads/documents/' . basename($storedPath);
        }

        $diskPath = dirname(__DIR__) . '/' . $relative;

        return is_file($diskPath) ? hrms_url($relative) : null;
    }
}
