<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$wpcli_quota_autoloader = dirname( __FILE__ ) . '/vendor/autoload.php';
if ( file_exists( $wpcli_quota_autoloader ) ) {
	require_once $wpcli_quota_autoloader;
}

WP_CLI::add_command( 'quota', 'Quota_Command' );