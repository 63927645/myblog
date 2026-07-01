<?php
/**
  Plugin Name: File Manager Advanced
  Plugin URI: https://wordpress.org/plugins/file-manager-advanced
  Description: Cpanel for files management in wordpress
  Author: wpexpertsio
  Version: 5.4.11
  Author URI: https://wpexperts.io
  License: GPLv2
**/

/**
 * Integrate Freemius SDK
 *
 * @package Advance File Manager
 */

// Some hosting environments (e.g. locked-down managed hosts) disable core streaming
// functions like fpassthru(). elFinder and cloud drivers rely on fpassthru() for
// efficient streaming (PDFs, large files). If it's disabled, PHP removes the
// internal function, so we provide a safe polyfill here.
if ( ! function_exists( 'fpassthru' ) ) {
	function fpassthru( $handle ) {
		if ( ! is_resource( $handle ) ) {
			return 0;
		}
		$bytes = 0;
		while ( ! feof( $handle ) ) {
			$chunk = fread( $handle, 8192 );
			if ( $chunk === false ) {
				break;
			}
			$bytes += strlen( $chunk );
			echo $chunk;
		}
		return $bytes;
	}
}

if ( ! function_exists( 'fma_fs' ) ) {
    // Create a helper function for easy SDK access.
    function fma_fs() {
        global $fma_fs;

        if ( ! isset( $fma_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';

            $fma_fs = fs_dynamic_init( array(
                'id'                  => '22156',
                'slug'                => 'file-manager-advanced',
                'premium_slug'        => 'advanced-file-manager-pro',
                'type'                => 'plugin',
                'public_key'          => 'pk_0fd5cbcb84f5aa475cb00b852ceaf',
                'is_premium'          => false,
                'has_addons'          => true,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'file_manager_advanced_ui',
                    'first-path'     => 'admin.php?page=file_manager_advanced_ui',
                    'contact'        => false,
                    'support'        => false,
                    'account'        => false,
                ),
            ) );
        }

        return $fma_fs;
    }

	// Init Freemius.
	fma_fs();
	// Signal that SDK was initiated.
	do_action( 'fma_fs_loaded' );
	
	// Hook into Freemius after_uninstall action
	// This hook fires when plugin is uninstalled through Freemius SDK
	add_action( 'fs_after_uninstall_file-manager-advanced', 'fma_freemius_after_uninstall', 10, 0 );
}

/**
 * Loading constants
 */
require_once('constants.php');

/**
 * Cleanup function - Shared by both uninstall and deactivation hooks
 */
function fma_cleanup_plugin_data() {
	global $wpdb;

	// 1. Delete all WordPress options (direct database query for reliability)
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'fmaoptions'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'fma_hide_review_section'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name = 'fma_blocks_dummy_data_created'" );

	// 2. Delete all fma_blocks custom post type posts and their meta
	// First get all post IDs
	$fma_blocks_ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
		'fma_blocks'
	) );

	if ( ! empty( $fma_blocks_ids ) ) {
		// Delete all post meta for these posts
		$post_ids_placeholders = implode( ',', array_fill( 0, count( $fma_blocks_ids ), '%d' ) );
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids_placeholders)",
			$fma_blocks_ids
		) );
		
		// Delete all posts
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->posts} WHERE post_type = %s",
			'fma_blocks'
		) );
	}

	// 3. Delete file logs table if it exists (created when pro is active)
	$table_name = $wpdb->prefix . 'fm_filelogs';
	$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

	// 4. Clean up any transients
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_fma_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_fma_%'" );

	// 5. Clean up user meta if any
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'fma_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'afmp_%'" );

	// 6. Clean up site options for multisite
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		$wpdb->query( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'fma_%'" );
	}
}

/**
 * Freemius after_uninstall hook callback
 * This hook fires when plugin is uninstalled through Freemius SDK
 * This is the most reliable method as Freemius handles the uninstall process
 */
function fma_freemius_after_uninstall() {
	// Check if user has enabled data deletion on uninstall
	$settings = get_option('fmaoptions', array());
	$delete_data_on_uninstall = isset($settings['fma_delete_data_on_uninstall']) && $settings['fma_delete_data_on_uninstall'] == '1';
	
	// If setting is disabled, don't delete data
	if (!$delete_data_on_uninstall) {
		return;
	}
	
	// Run cleanup when Freemius uninstall hook fires
	fma_cleanup_plugin_data();
}


/*
 * Advanced File Manager
 * Text Domain
 */
add_action('plugins_loaded', 'advanced_file_manager_load_text_domain');
function advanced_file_manager_load_text_domain()
{
    $domain = dirname(plugin_basename(__FILE__));
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    load_textdomain($domain, trailingslashit(WP_LANG_DIR).'plugins'.'/'.$domain.'-'.$locale.'.mo');
    load_plugin_textdomain($domain, false, basename(dirname(__FILE__)).'/languages/');
}
/**
 * Main application
 */
if(is_admin()) {
	include('application/class_fma_main.php');
	new class_fma_main;
}
/**
 * Shortcode class
 */
include('application/class_fma_shortcode.php');
include 'application/rest-api/class-fma-controller.php';
/**
 * Blocks class (for free version)
 */
if ( ! class_exists( 'file_manager_advanced_shortcode' ) ) {
	require_once( 'application/class_fma_blocks.php' );
	// Instantiate the class
	if ( class_exists( 'class_fma_blocks' ) ) {
		new class_fma_blocks();
	}
}


