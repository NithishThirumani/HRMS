<?php
$root = realpath(__DIR__ . '/..');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$changed = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = str_replace('\\', '/', $file->getPathname());
    if (!preg_match('/\.php$/i', $path)) continue;
    $content = file_get_contents($path);
    $orig = $content;

    // Normalize base host URLs to /emps/
    $content = str_replace('/emps/', '/emps/', $content);
    $content = str_replace('/emps/', '/emps/', $content);
    $content = str_replace('/emps/', '/emps/', $content);
    $content = str_replace('/emps/', '/emps/', $content);
    $content = str_replace('/emps', '/emps', $content);

    // Determine whether this file is admin or user panel related
    $isAdmin = (strpos($path, '/admin_panel/') !== false) || (strpos($path, '/hod_panel/') !== false) || (strpos($path, '/hr_panel/') !== false) || (strpos($path, '/super_admin_panel/') !== false);
    $isUser = (strpos($path, '/user_panel/') !== false) || (strpos($path, '/views/') !== false) || (strpos($path, '/employee_panel/') !== false);

    $target = null;
    if ($isAdmin) $target = '/emps/admin_panel/logout.php';
    elseif ($isUser) $target = '/emps/user_panel/logout.php';

    if ($target) {
        $patterns = [
            '/href\s*=\s*"[^"]*logout\.php"/i',
            '/href\s*=\s*\'[^\']*logout\.php\'/i',
            '/action\s*=\s*"[^"]*logout\.php"/i',
            '/action\s*=\s*\'[^\']*logout\.php\'/i',
        ];
        foreach ($patterns as $pat) {
            $content = preg_replace_callback($pat, function($m) use ($target) {
                $str = $m[0];
                if (stripos($str, 'href') !== false) {
                    $quote = (strpos($str, '"') !== false) ? '"' : "'";
                    return 'href=' . $quote . $target . $quote;
                } else {
                    $quote = (strpos($str, '"') !== false) ? '"' : "'";
                    return 'action=' . $quote . $target . $quote;
                }
            }, $content);
        }
    }

    if ($content !== $orig) {
        file_put_contents($path, $content);
        $changed[] = $path;
    }
}

echo "Updated " . count($changed) . " files:\n";
foreach ($changed as $c) echo $c . "\n";