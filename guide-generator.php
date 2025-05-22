<?php

$dir = __DIR__ . '/includes';
$sections = [];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($rii as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $contents = file_get_contents($file->getPathname());

    // Match @feature-section
    preg_match_all('/@feature-section\s+(\w+)\s+(.+?)\n/', $contents, $sectionMatches, PREG_SET_ORDER);
    foreach ($sectionMatches as [$_, $section, $description]) {
        if (!isset($sections[$section])) {
            $sections[$section] = [
                'description' => trim($description),
                'features' => []
            ];
        }
    }

    // Match @feature[lang] or base @feature
    preg_match_all('/@feature(?:\[(\w+)\])?\s+(\w+)\s+(\w+)\s+(.+?)\n/s', $contents, $featureMatches, PREG_SET_ORDER);
    foreach ($featureMatches as [$_, $lang, $section, $key, $title]) {
        $lang = $lang ?: 'en';

        if (!isset($sections[$section])) {
            $sections[$section] = [
                'description' => '',
                'features' => []
            ];
        }

        $sections[$section]['features'][$key]['title'][$lang] = trim($title);
        $sections[$section]['features'][$key]['_last_match_file'] = $file->getFilename();
    }

    // Match @desc[lang]
    preg_match_all('/@desc\[(\w+)\]\s+(.+?)\n/s', $contents, $descMatches, PREG_SET_ORDER);
    foreach ($descMatches as [$_, $lang, $desc]) {
        // Link to the last matched feature in this file
        foreach ($sections as $section => &$sectionData) {
            foreach ($sectionData['features'] as $key => &$feature) {
                if (($feature['_last_match_file'] ?? null) === $file->getFilename()) {
                    $feature['description'][$lang] = trim($desc);
                    unset($feature['_last_match_file']); // cleanup
                    break 2; // stop after matching the first relevant one
                }
            }
        }
    }

    // Fallback: English descriptions from multiline text
    preg_match_all('/@feature\s+(\w+)\s+(\w+)\s+.+?\n(.*?)\*\//s', $contents, $descFallbackMatches, PREG_SET_ORDER);
    foreach ($descFallbackMatches as [$_, $section, $key, $desc]) {
        $desc = trim(preg_replace('/^\s*\*\s?/m', '', $desc));
        if (!isset($sections[$section]['features'][$key]['description']['en'])) {
            $sections[$section]['features'][$key]['description']['en'] = $desc;
        }
    }
}

// Clean up
foreach ($sections as &$section) {
    foreach ($section['features'] as &$f) {
        unset($f['_last_match_file']);
    }
    ksort($section['features']);
}
ksort($sections);
unset($section, $f);

// Save JSON
file_put_contents(__DIR__ . '/docs/feature-list.json', json_encode($sections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ feature-list.json generated\n";
