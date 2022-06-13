<?php

require_once 'vendasta-creds.php';

define( 'CHAT_ESSENTIAL_API_BASE', 'vendasta' );
define( 'CHAT_ESSENTIAL_SUBSCRIPTION', 'pro' );
define( 'CHAT_ESSENTIAL_SUBSCRIPTION_PREMIUM', true );
define( 'CHAT_ESSENTIAL_PLUGIN_ID', 'f762142c-5c77-44f7-9d09-f8b8a4673a52' );

define( 'VENDASTA_APP_ID', 'MP-T2RPGQHKWCMTHBMVCF3NHV6WSPNGPJPW' );
define( 'VENDASTA_OAUTH_BASE', 'https://sso-api-prod.apigateway.co/_gateway/oauth2/auth' );
define( 'VENDASTA_STORE_BASE_URL', 'https://partners.vendasta.com/marketplace/products' );

define( 'VENDASTA_CLIENT_ID', '32ce56e1-f50c-4478-8355-18790d4e9c0d' );
define( 'VENDASTA_OAUTH_REDIRECT', 'https://devapi.eyelevel.ai/oauth2/vendasta' );

define( 'CHAT_ESSENTIAL_AUTH_TYPE', 'vendasta' );

define(
    'CHAT_ESSENTIAL_DEPENDENCIES',
    array(
        'admin/includes/vendasta/vendasta-admin-login.php',
    ),
);

function get_domain() {
    $home_url = get_option('home');
    if (empty($home_url)) {
        $home_url = home_url();
        if (empty($home_url)) {
            return array(
                'error' => 'We cannot detect a valid Site Address for this WordPress installation. Please update your WordPress settings with a valid Site Address.',
            );
        }
    }

    $url = parse_url($home_url);
    if (empty($url) || empty($url['host'])) {
        return array(
            'error' => 'We cannot detect a valid Site Address for this WordPress installation. Please update your WordPress settings with a valid Site Address.',
        );
    }

    if (str_contains($url['host'], 'localhost') || str_contains($url['host'], '127.0.0.1')) {
        return array(
            'domain' => $url['host'],
            'warning' => 'This appears to be a local hosted WordPress installation. If this installation does not already have a license, one of your available licenses will be assigned to it.',
        );
    }

    return array(
        'domain' => $url['host'],
    );
}

function validate_vendasta() {
    if(!defined('VENDASTA_ACCOUNT_ID')) {
        return array(
            'error' => 'This plugin has been corrupted. Please install a valid version of the plugin.',
        );
    }

    return get_domain();
}

/*
define( 'WSP_ACCOUNT_ID', 'AG-FAKE' );
define( 'WSP_PARTNER_ID', 'VNDR' );
*/