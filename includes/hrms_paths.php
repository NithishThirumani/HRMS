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
