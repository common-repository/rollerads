<?php

add_action('wp_head', 'rolleradsAddJSCode');

/**
 * Adding the sdk download code
 * @return void
 */
function rolleradsAddJSCode(): void
{
    $options = get_option('rollerads_options');
    $siteID = $options['site_id'] ?? null;
    $zoneID = $options['zone_id'] ?? null;
    $installed = $options['installed'] ?? false;
    $sdk = $options['sdk'] ?? null;

    if (!$siteID || !$zoneID || !$installed || !$sdk) {
        return;
    }

    echo $options['sdk'];
}

/**
 * Check if the service worker file exists
 * @return bool
 * @throws Exception
 */
function checkServiceWorkerFileExists(): bool
{
    $rolleradsApi = new RollerAdsApi();

    $zones = $rolleradsApi->zoneList();

    foreach ($zones as $zone) {
        if (is_file(ABSPATH . "{$zone['zone_id']}.sw.js")) {
            return true;
        }
    }

    return false;
}

/**
 * Creating a site, zone and service worker file
 * @return void
 * @throws Exception
 */
function rolleradsInstallServiceWorker(): void
{
    $rolleradsApi = new RollerAdsApi();
    $options = get_option('rollerads_options');

    $options['site_id'] = $options['zone_id'] = null;

    $zones = $rolleradsApi->zoneList();

    $foundZones = array_filter($zones, function ($zone) use ($options) {
        return str_contains($zone['zone_title'], 'Created from WordPress plugin');
    });

    foreach ($foundZones as $zone) {
        $zone = $rolleradsApi->getZone($foundZones[0]['zone_id']);

        if ($zone['format_id'] === 1) {
            $options['site_id'] = $zone['site_id'];
            $options['zone_id'] = $zone['zone_id'];
        }
    }

    if (!isset($options['site_id'])) {
        $options['site_id'] = $rolleradsApi->siteCreate();
    }

    if (!isset($options['zone_id'])) {
        $options['zone_id'] = $rolleradsApi->zoneCreate($options['site_id']);
    }

    $codes = $rolleradsApi->getCodes($options['site_id'], $options['zone_id']);

    $options['installed'] = true;
    $options['sdk'] = $codes[array_search('sdk', array_column($codes, 'alias'))]['template'];

    update_option('rollerads_options', $options);

    $scriptFilePath = ABSPATH . "{$options['zone_id']}.sw.js";

    if (!is_file($scriptFilePath)) {
        global $wp_filesystem;

        if (!$wp_filesystem) {
            require_once ABSPATH . "wp-admin/includes/file.php";
            WP_Filesystem();
        }

        $wp_filesystem->put_contents($scriptFilePath, $codes[array_search('service_worker', array_column($codes, 'alias'))]['template']);
    }
}

/**
 * Deleting a service worker file
 * @return void
 */
function rolleradsRemoveServiceWorker(): void
{
    $options = get_option('rollerads_options');

    $options['installed'] = false;

    update_option('rollerads_options', $options);

    if (isset($options['zone_id'])) {
        $scriptFilePath = ABSPATH . "{$options['zone_id']}.sw.js";

        if (is_file($scriptFilePath)) {
            global $wp_filesystem;

            if (!$wp_filesystem) {
                require_once ABSPATH . "wp-admin/includes/file.php";
                WP_Filesystem();
            }

            $wp_filesystem->delete($scriptFilePath);
        }
    }
}