<?php
/**
 * Plugin name: RollerAds Push Subscription
 * Description: RollerAds web-push notifications plugin for monetization and message sending.
 * Author: RollerAds
 * Author URI: https://rollerads.com
 * Version: 1.1
 * License: GPL v2 or later
 *
 * @package rollerads
 */

if (!defined('ABSPATH')) {
    exit;
}

if (is_file(plugin_dir_path(__FILE__) . 'config.develop.php')) {
    require_once plugin_dir_path(__FILE__) . 'config.develop.php';
} elseif (is_file(plugin_dir_path(__FILE__) . 'config.test.php')) {
    require_once plugin_dir_path(__FILE__) . 'config.test.php';
} else {
    define("ROLLER_ADS_ENV", 'production');
    define("ROLLERADS_API_URI", 'https://api.rollerads.com');
    define("ROLLERADS_URI", 'https://my.rollerads.com');
}

define('ROLLERADS_BASE_URL', plugin_dir_url(__FILE__));

$pluginData = get_file_data(plugin_dir_path(__FILE__) . 'rollerads.php', [
    'Version' => 'Version',
]);

define('ROLLERADS_VERSION', $pluginData['Version']);

require_once plugin_dir_path(__FILE__) . 'includes/api.php';
require_once plugin_dir_path(__FILE__) . 'includes/rollerads-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/service-worker.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';