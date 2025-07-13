<?php

$skipList = ['vendor', '.git', 'cdn', 'storage', 'tests', 'plugins'];
$cdnSaveFolders = [
    'js' => 'C:/PROJECTS/ON PREMISE/app/public/assets/cdn/js/',
    'css' => 'C:/PROJECTS/ON PREMISE/app/public/assets/cdn/css/',
    'img' => 'C:/PROJECTS/ON PREMISE/app/public/assets/cdn/img/',
];


function shouldSkip($path, $skipList) {
    foreach ($skipList as $skipItem) {
        if (str_contains($path, DIRECTORY_SEPARATOR . $skipItem) || basename($path) === $skipItem) {
            return true;
        }
    }
    return false;
}

function removePhpComments($content) {
    $tokens = token_get_all($content);
    $cleaned = '';

    foreach ($tokens as $token) {
        if (is_array($token)) {
            [$id, $text] = $token; 
            if (in_array($id, [T_COMMENT, T_DOC_COMMENT])) continue; // Remove PHP comments (//, #, /* */, and PHPDoc)
            if ($id === T_WHITESPACE) $text = ' '; // Normalize whitespace
            $cleaned .= $text;
        } else {
            $cleaned .= $token;
        }
    }

    $cleaned = preg_replace('/(?<!:)\/\/[^\n\r]*/', '', $cleaned); // Only if `//` is NOT part of URL (not preceded by colon)
    $cleaned = preg_replace('/<!--.*?-->/s', '', $cleaned); // Remove HTML comments (<!-- ... -->)
    $cleaned = preg_replace('/<\?php(?=\S)/', '<?php ', $cleaned); // Fix missing space after '<?php'
    return preg_replace('/\s+/', ' ', $cleaned); // Collapse whitespace
}


function minifyCss($css) {
    $css = preg_replace('!/\*.*?\*/!s', '', $css);
    return preg_replace('/\s+/', ' ', $css);
}

function minifyJs($js) {
    $js = preg_replace('!/\*.*?\*/!s', '', $js); // Remove multi-line comments
    $js = preg_replace('/(?<!:)(?<!http:)(?<!https:)\/\/[^\n\r]*/', '', $js); // Remove single-line comments (preserve URLs)
    $js = preg_replace('/\s*([{};=(),:+\-*\/])\s*/', '$1', $js); // Remove spaces around operators/symbols

    // Add exactly 1 space before keywords, only if preceded by } or newline/tab without space
    $keywords = ['function', 'var', 'let', 'const', 'if', 'for', 'while', 'async'];
    foreach ($keywords as $kw) {
        $js = preg_replace('/(?<=[}\n\r\t])' . $kw . '\b/', ' ' . $kw, $js);
    }

    $js = preg_replace('/\s+/', ' ', $js);
    return trim($js);
}




function downloadCdnAndReplace(&$content, $filePath, $cdnSaveFolders) {
    $pattern = '/(?:<script[^>]+src=["\']|<link[^>]+href=["\']|<img[^>]+src=["\'])(https?:\/\/|\/\/)([^"\']+\.(js|css|png|jpg|jpeg|gif|svg))["\'][^>]*>/i';

    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $protocol = $match[1] === '//' ? 'https://' : $match[1];
            $urlRest = $match[2];
            $fullUrl = $protocol . $urlRest;
            $ext = strtolower($match[3]);

            $folderKey = in_array($ext, ['js', 'css']) ? $ext : 'img';
            $targetFolder = $cdnSaveFolders[$folderKey];
            $originalName = basename(parse_url($fullUrl, PHP_URL_PATH));
            $localPath = $targetFolder . $originalName;
            $relativePath = '../'.str_replace('C:/PROJECTS/ON PREMISE/app/public/', '', $localPath);

            if (!file_exists($localPath)) {
                $cdnContent = @file_get_contents($fullUrl);
                if ($cdnContent !== false) {
                    file_put_contents($localPath, $cdnContent);
                    echo "📦 Downloaded CDN: <code>$fullUrl</code> → <code>$relativePath</code><br>";
                } else {
                    echo "<span style='color:red;'>❌ Failed: $fullUrl</span><br>";
                    continue;
                }
            }

            $escapedUrl = preg_quote($match[1] . $urlRest, '/');
            $content = preg_replace(
                '/(["\'])' . $escapedUrl . '(["\'])/',
                "'$relativePath'",
                $content
            );
        }
    }
}

function cleanFilesRecursively($directory, $skipList, $cdnSaveFolders) {
    if (!is_dir($directory)) {
        echo "<p style='color:red;'>❌ Directory not found: <code>$directory</code></p>";
        return;
    }

    // Check CDN folders exist
    foreach ($cdnSaveFolders as $folder) {
        if (!is_dir($folder)) {
            echo "<p style='color:red;'>❌ Missing CDN folder: <code>$folder</code><br>Aborting...</p>";
            return;
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $summary = ['php' => 0, 'js' => 0, 'css' => 0];

    foreach ($iterator as $file) {
        $filePath = $file->getPathname();
        if (!$file->isFile() || shouldSkip($filePath, $skipList)) continue;

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $content = file_get_contents($filePath);

        switch ($ext) {
            case 'php':
                $phpCleaned = removePhpComments($content);
                downloadCdnAndReplace($phpCleaned, $filePath, $cdnSaveFolders);
                $phpCleaned = preg_replace('/>[\s]+</', '><', $phpCleaned); // Remove space between tags
                file_put_contents($filePath, trim($phpCleaned));
                echo "🐘 Cleaned PHP: <code>$filePath</code><br>";
                $summary['php']++;
                break;

            case 'js':
                $minifiedJs = minifyJs($content);
                file_put_contents($filePath, $minifiedJs);
                echo "🈹 Minified JS: <code>$filePath</code><br>";
                $summary['js']++;
                break;

            case 'css':
                $minifiedCss = minifyCss($content);
                file_put_contents($filePath, $minifiedCss);
                echo "🈯 Minified CSS: <code>$filePath</code><br>";
                $summary['css']++;
                break;
        }
    }

    echo "<hr><strong>Summary:</strong><br>";
    echo "🐘 PHP files cleaned: {$summary['php']}<br>";
    echo "🈹 JS files minified: {$summary['js']}<br>";
    echo "🈯 CSS files minified: {$summary['css']}<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Universal Code Cleaner</title>
</head>
<body>
    <h2>Universal PHP / JS / CSS Obfuscator Tool</h2>
    <form method="post">
        <label for="inputPath">Enter Folder Path:</label><br>
        <input type="text" name="inputPath" id="inputPath" size="60" required><br><br>
        <button type="submit">Clean & Minify</button>
    </form>
    <hr>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputPath = rtrim($_POST['inputPath'], "/\\");
    cleanFilesRecursively($inputPath, $skipList, $cdnSaveFolders);
}
?>
</body>
</html>
