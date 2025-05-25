<?php

$dir = __DIR__ . '/includes';
$output = __DIR__ . '/docs/feature-list.json';

$sections = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($rii as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    preg_match_all('#/\*\*(.*?)\*/#s', $contents, $docblocks);

    foreach ($docblocks[1] as $block) {
        $lines = preg_split('/\r?\n/', trim($block));
        $type = null;
        $sectionKey = null;
        $featureKey = null;
        $order = 10000;

        foreach ($lines as $line) {
            $line = trim($line, " *\t\n\r\0\x0B");

            if (preg_match('/@feature-section\s+(\w+)/', $line, $m)) {
                $type = 'section';
                $sectionKey = $m[1];
                $sections[$sectionKey] ??= [
                    'title' => [],
                    'description' => [],
                    'features' => [],
                    'order' => 10000,
                ];
                continue;
            }

            if (preg_match('/@feature\s+(\w+)\s+(\w+)/', $line, $m)) {
                $type = 'feature';
                $sectionKey = $m[1];
                $featureKey = $m[2];
                $sections[$sectionKey] ??= ['title' => [], 'description' => [], 'features' => [], 'order' => 10000];

                // Prevent overwriting existing feature
                if (!isset($sections[$sectionKey]['features'][$featureKey])) {
                    $sections[$sectionKey]['features'][$featureKey] = [
                        'title' => [],
                        'description' => [],
                        'order' => $order,
                    ];
                } else {
                    // Skip duplicate feature
                    $type = null;
                    continue;
                }

                continue;
            }

            if (preg_match('/@title\[(\w+)]\s+(.+)/', $line, $m)) {
                $lang = $m[1];
                $title = trim($m[2]);

                if ($type === 'section' && $sectionKey) {
                    $sections[$sectionKey]['title'][$lang] = $title;
                } elseif ($type === 'feature' && $sectionKey && $featureKey) {
                    $sections[$sectionKey]['features'][$featureKey]['title'][$lang] = $title;
                }
                continue;
            }

            if (preg_match('/@desc\[(\w+)]\s+(.+)/', $line, $m)) {
                $lang = $m[1];
                $desc = trim($m[2]);

                if ($type === 'feature' && $sectionKey && $featureKey) {
                    $sections[$sectionKey]['features'][$featureKey]['description'][$lang] = $desc;
                } elseif ($type === 'section' && $sectionKey) {
                    $sections[$sectionKey]['description'][$lang] = $desc;
                }
                continue;
            }

            if (preg_match('/@order\s+(\d+)/', $line, $m)) {
                $order = (int) $m[1];
                if ($type === 'section' && $sectionKey) {
                    $sections[$sectionKey]['order'] = $order;
                } elseif ($type === 'feature' && $sectionKey && $featureKey) {
                    $sections[$sectionKey]['features'][$featureKey]['order'] = $order;
                }
                continue;
            }
        }

        // Remove feature if it has no title
        if ($type === 'feature' && $sectionKey && $featureKey) {
            if (empty($sections[$sectionKey]['features'][$featureKey]['title'])) {
                unset($sections[$sectionKey]['features'][$featureKey]);
            }
        }
    }
}

// Sort by order
uasort($sections, fn($a, $b) => $a['order'] <=> $b['order']);

foreach ($sections as &$section) {
    uksort($section['title'], 'strnatcmp');
    uksort($section['description'], 'strnatcmp');

    uasort($section['features'], fn($a, $b) => $a['order'] <=> $b['order']);

    foreach ($section['features'] as &$feature) {
        uksort($feature['title'], 'strnatcmp');
        uksort($feature['description'], 'strnatcmp');
    }
}
unset($section, $feature);

file_put_contents($output, json_encode($sections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ feature-list.json generated at: $output\n";
