<?php

namespace JustB2b\Shortcodes;

use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Traits\SingletonTrait;

defined('ABSPATH') || exit;

class FeatureShortcodes
{
    use SingletonTrait;
    use RuntimeCacheTrait;

    protected array $data = [];

    protected function __construct()
    {
        add_shortcode('justb2b_feature_section', [$this, 'renderSection']);
        add_shortcode('justb2b_feature', [$this, 'renderFeature']);
        $this->data = $this->loadData();
    }

    protected function loadData(): array
    {
        $path = plugin_dir_path(__FILE__) . '../../docs/feature-list.json';
        if (!file_exists($path)) {
            return [];
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    public function renderSection($atts): string
    {
        $atts = shortcode_atts(['key' => '', 'lang' => 'ru'], $atts);
        $key = $atts['key'];
        $lang = $atts['lang'];

        if (!isset($this->data[$key])) {
            return "<div class='justb2b-section-error'>Section \"$key\" not found.</div>";
        }

        $section = $this->data[$key];
        $title = $section['title'][$lang] ?? '';
        $desc = $section['description'][$lang] ?? '';
        $features = $section['features'] ?? [];

        ob_start();
        echo "<div class='justb2b-section'>";
        echo "<h2>$title</h2><p>$desc</p><ul>";
        foreach ($features as $featureKey => $feature) {
            $fTitle = $feature['title'][$lang] ?? '';
            $fDesc = $feature['description'][$lang] ?? '';
            echo "<li><strong>$fTitle</strong><br><small>$fDesc</small></li>";
        }
        echo "</ul></div>";

        return ob_get_clean();
    }

    public function renderFeature($atts): string
    {
        $atts = shortcode_atts(['key' => '', 'lang' => 'ru'], $atts);
        $key = $atts['key'];
        $lang = $atts['lang'];

        [$sectionKey, $featureKey] = array_pad(explode('.', $key), 2, null);

        if (!$sectionKey || !$featureKey || !isset($this->data[$sectionKey]['features'][$featureKey])) {
            return "<div class='justb2b-feature-error'>Feature \"$key\" not found.</div>";
        }

        $feature = $this->data[$sectionKey]['features'][$featureKey];
        $title = $feature['title'][$lang] ?? '';
        $desc = $feature['description'][$lang] ?? '';

        return "<div class='justb2b-feature'><strong>$title</strong><br><small>$desc</small></div>";
    }
}
