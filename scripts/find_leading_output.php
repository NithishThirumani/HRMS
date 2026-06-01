<?php
$root = realpath(__DIR__ . '/..');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$problem = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = str_replace('\\','/',$file->getPathname());
    if (!preg_match('/\.php$/i',$path)) continue;
    $bytes = file_get_contents($path, false, null, 0, 4);
    // check for BOM
    if (substr($bytes,0,3) === "\xEF\xBB\xBF") {
        $problem[] = $path . " (BOM)";
        continue;
    }
    // check for leading whitespace/newline before <?php
    $content = file_get_contents($path);
    if (preg_match('/^[\s\r\n]+<\?php/i', $content)) {
        $problem[] = $path . " (leading whitespace)";
    }
}
foreach ($problem as $p) echo $p . "\n";
echo "Found " . count($problem) . " files with leading output.\n";
?>