<?php

$inputFile = __DIR__ . '/docs/feature-list.json';
$outputDir = __DIR__ . '/docs';

// Создание директории, если не существует
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$json = file_get_contents($inputFile);
$data = json_decode($json, true);

if (!is_array($data)) {
    exit("❌ Failed to decode JSON.\n");
}

// Сбор всех языков из заголовков и описаний
$languages = [];

foreach ($data as $section) {
    foreach ($section['title'] ?? [] as $lang => $_) {
        $languages[$lang] = true;
    }
    foreach ($section['description'] ?? [] as $lang => $_) {
        $languages[$lang] = true;
    }

    foreach ($section['features'] ?? [] as $feature) {
        foreach ($feature['title'] ?? [] as $lang => $_) {
            $languages[$lang] = true;
        }
        foreach ($feature['description'] ?? [] as $lang => $_) {
            $languages[$lang] = true;
        }
    }
}

$languages = array_keys($languages);
sort($languages);

// Генерация HTML для каждого языка
foreach ($languages as $lang) {
    ob_start();
    echo "<!DOCTYPE html>\n<html lang=\"$lang\">\n<head>\n<meta charset=\"UTF-8\">\n";
    echo "<title>JustB2B Features [$lang]</title>\n";
    echo "<style>body{font-family:sans-serif;padding:2rem;}h1,h2{color:#2c3e50;}section{margin-bottom:3rem;}ul{padding-left:1.2rem;}</style>\n";
    echo "</head>\n<body>\n";
    echo "<h1>📘 JustB2B Features [$lang]</h1>\n";

    foreach ($data as $sectionKey => $section) {
        $sectionTitle = $section['title'][$lang] ?? '';
        $sectionDesc = $section['description'][$lang] ?? '';
        echo "<section>\n<h2>{$sectionTitle}</h2>\n<p>{$sectionDesc}</p>\n<ul>\n";

        foreach ($section['features'] ?? [] as $featureKey => $feature) {
            $featureTitle = $feature['title'][$lang] ?? '';
            $featureDesc = $feature['description'][$lang] ?? '';
            echo "<li><strong>{$featureTitle}</strong><br><small>{$featureDesc}</small></li>\n";
        }

        echo "</ul>\n</section>\n";
    }

    echo "</body>\n</html>";

    $html = ob_get_clean();
    $outputPath = "{$outputDir}/features-{$lang}.html";
    file_put_contents($outputPath, $html);
    echo "✅ Generated: {$outputPath}\n";
}
