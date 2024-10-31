<?php

add_action('rest_api_init', 'rolleradsRegisterApiRoutes');

/**
 * Register API routes
 * @return void
 */
function rolleradsRegisterApiRoutes(): void
{
    register_rest_route('rollerads', '/login', [
        'methods' => 'GET',
        'callback' => 'login',
    ]);
}

/**
 * Saving an API Token
 * @param WP_REST_Request $data
 * @return void
 */
function login(WP_REST_Request $data): void
{
    if (!$data->get_query_params()['api_key']) {
        exit(wp_redirect(admin_url('admin.php?page=rollerads-configuration')));
    }

    $options = get_option('rollerads_options') ?: [];
    $options['api_key'] = $data->get_query_params()['api_key'];

    update_option('rollerads_options', $options);

    exit(wp_redirect(admin_url('admin.php?page=rollerads-configuration')));
}