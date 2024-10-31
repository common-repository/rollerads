<?php

add_action('admin_init', 'rolleradsRegisterSettings');
add_action('admin_menu', 'rolleradsMenu');
add_action('init', 'rolleradsInit');
add_action('admin_enqueue_scripts', 'rolleradsDestroy');

/**
 * Register js, css files
 * @return void
 */
function rolleradsInit(): void
{
    $cssDirPath = ROLLERADS_BASE_URL . 'assets/css';

    wp_register_style('rollerads_style', "$cssDirPath/rollerads_style.css", array(), ROLLERADS_VERSION, false);
}

/**
 * Remove js, css files
 * @return void
 */
function rolleradsDestroy(): void
{
    wp_enqueue_style('rollerads_style');
}

/**
 * Registering Plugin Settings
 * @return void
 */
function rolleradsRegisterSettings(): void
{
    register_setting('rollerads', 'rollerads_options');

    add_settings_section(
        'rollerads',
        'Settings',
        '',
        'rollerads-configuration'
    );

    add_settings_field(
        'api_key',
        'API KEY',
        '',
        'rollerads-configuration',
        'rollerads'
    );

    add_settings_field(
        'site_id',
        'Site id',
        '',
        'rollerads-configuration',
        'rollerads'
    );

    add_settings_field(
        'zone_id',
        'Zone id',
        '',
        'rollerads-configuration',
        'rollerads'
    );

    add_settings_field(
        'installed',
        'Installed',
        '',
        'rollerads-configuration',
        'rollerads'
    );
}

/**
 * Adding a settings button to the admin panel
 * @return void
 */
function rolleradsMenu(): void
{
    $pageTitle = 'RollerAds'; // Settings page title
    $menuTitle = 'RollerAds'; // The name of the settings button in the admin panel
    $menuIconUrl = ROLLERADS_BASE_URL . 'assets/images/icon.png'; // Url of the icon that will be displayed on the settings button in the admin panel
    $capability = 'administrator'; // I still don't understand what the hell
    $menuSlug = 'rollerads-configuration'; // Route name
    $callback = 'rolleradsRenderSettingsPage'; // Settings page rendering function

    add_menu_page(
        $pageTitle,
        $menuTitle,
        $capability,
        $menuSlug,
        $callback,
        $menuIconUrl
    );
}

/**
 * Rendering the settings page
 * @return void
 * @throws Exception
 */
function rolleradsRenderSettingsPage(): void
{
    if (isset($_POST['install_service_worker'])) {
        try {
            if (checkServiceWorkerFileExists()) {
                add_settings_error('rollerads_messages', 'rollerads_messages', 'Service worker already installed.');
            } else {
                rolleradsInstallServiceWorker();

                add_settings_error('rollerads_messages', 'rollerads_messages', 'Install successful.', 'success');
            }
        } catch (Exception $e) {
            add_settings_error('rollerads_messages', 'rollerads_messages', 'Install error.');
        }
    }

    if (isset($_POST['remove_service_worker'])) {
        rolleradsRemoveServiceWorker();

        add_settings_error('rollerads_messages', 'rollerads_messages', 'Remove successful.', 'success');
    }

    if (isset($_POST['disconnect_plugin'])) {
        rolleradsRemoveServiceWorker();
        update_option('rollerads_options', []);

        add_settings_error('rollerads_messages', 'rollerads_messages', 'Disconnected.', 'success');
    }

    $options = get_option('rollerads_options');
    $apiKey = $options['api_key'] ?? '';
    $installed = $options['installed'] ?? false;

    if (isset($_GET['settings-updated'])) {
        $apiKeyIsCorrect = (new RollerAdsApi())->checkApiKey();

        if ($apiKeyIsCorrect) {
            add_settings_error('rollerads_messages', 'rollerads_messages', 'Settings saved.', 'success');
        } else {
            add_settings_error('rollerads_messages', 'rollerads_messages', 'API KEY is incorrect.');

            $options['api_key'] = $apiKey = '';

            update_option('rollerads_options', $options);
        }
    }

    settings_errors('rollerads_messages');

    ?>
    <div class="rollerads-card">
        <div class="header">
            <img src="<?php echo esc_url_raw(ROLLERADS_BASE_URL . 'assets/images/logo.svg'); ?>">
            <a class="site-link" href="<?php echo esc_url_raw(ROLLERADS_URI) ?>">
                <img src="<?php echo esc_url_raw(ROLLERADS_BASE_URL . 'assets/images/link-icon.svg'); ?>">
                RollerAds.com
            </a>
        </div>
        <div class="body">
            <div class="form">
                <?php

                if (!$apiKey) {
                    ?>
                    <div class="form-title">
                        You need to connect your WordPress RollerAds plugin with your<br>
                        RollerAds account. Follow those steps to do this:
                    </div>
                    <div class="register-step-card">
                        <a>1. Sign in on register in RollerAds here</a>
                        <a class="button button-primary"
                           target="_blank"
                           href="<?php echo esc_url_raw(ROLLERADS_URI) ?>/auth/login"
                        >Sign in</a>
                    </div>
                    <div class="register-step-card">
                        <a>2. Click here to set an API token</a>
                        <a class="button button-primary"
                           href="<?php echo esc_url_raw(ROLLERADS_URI) ?>/pub/wp-plugin?callback=<?php echo get_site_url() ?>/wp-json/rollerads/login"
                        >Set API token</a>
                    </div>
                    <?php
                }

                if ($apiKey) {
                    if (!$installed) {
                        ?>
                        <form method="POST">
                            <input type="hidden" name="install_service_worker" value="true"/>
                            <div class="register-step-card">
                                <a>Click here to install service worker:</a>
                                <input type="submit" name="submit" id="submit" class="button button-primary"
                                       value="Install service worker">
                            </div>
                        </form>
                        <?php
                    } else {
                        ?>
                        <form method="POST">
                            <input type="hidden" name="remove_service_worker" value="true"/>
                            <div class="register-step-card">
                                <a>Click here to remove service worker:</a>
                                <input type="submit" name="submit" id="submit" class="button button-primary"
                                       value="Remove service worker">
                            </div>
                        </form>
                        <?php
                    }

                    ?>
                    <form method="POST">
                        <input type="hidden" name="disconnect_plugin" value="true"/>
                        <div class="register-step-card">
                            <a>Disconnect plugin from RollerAds account:</a>
                            <input type="submit" name="submit" id="submit" class="button button-primary"
                                   value="Disconnect">
                        </div>
                    </form>
                    <?php
                }

                ?>
            </div>
        </div>
    </div>
    <?php
}