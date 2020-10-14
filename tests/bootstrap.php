<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../src/class-shortcode-tree.php');

$testsSrc = __DIR__ . '/src';
if (!is_dir($testsSrc)) {
    mkdir($testsSrc);
}

$wpVersion = getenv('WP_CORE_VERSION') ?: 'latest';

if ('latest' === $wpVersion) {
    $updateData = json_decode(file_get_contents('https://api.wordpress.org/core/version-check/1.7/'));
    $wpVersion = implode('.', array_slice(explode('.', $updateData->offers[0]->current), 0, -1));
} else if (false !== strpos($wpVersion, '.')) {
    $wpVersion.='-branch';
}

// download all necessary files from WP core (instead of cloning the whole repo from the CI task)
$wpFiles = array('wp-includes/shortcodes.php');
foreach ($wpFiles as $file) {
    $filePath = implode('/', [$testsSrc, $wpVersion, $file]);
    if (!is_file($filePath)) {
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $repoFileUrl = 'https://raw.githubusercontent.com/WordPress/WordPress/' . $wpVersion . '/' . $file;
        $fileContents = file_get_contents($repoFileUrl);

        if (!$fileContents) {
            throw new Exception("Could not download necessary WP core file from $repoFileUrl");
        }

        file_put_contents($filePath, $fileContents);
    }
    require_once($filePath);
}
