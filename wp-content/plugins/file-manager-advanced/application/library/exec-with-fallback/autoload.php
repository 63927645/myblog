<?php
/**
 * ExecWithFallback Autoloader
 * 
 * Simple autoloader for exec-with-fallback library
 *
 * @package Advanced File Manager
 * @since 1.0.0
 */

// Skip ABSPATH check for standalone testing
if ( defined( 'ABSPATH' ) ) {
    // WordPress environment - continue
} else {
    // Standalone testing - continue
}

// Define the library path
$lib_path = dirname( __FILE__ ) . '/src/';

// Load all required files in correct order
require_once $lib_path . 'ExecWithFallback.php';
require_once $lib_path . 'ExecWithFallbackNoMercy.php';
require_once $lib_path . 'Passthru.php';
require_once $lib_path . 'POpen.php';
require_once $lib_path . 'ProcOpen.php';
require_once $lib_path . 'ShellExec.php';
require_once $lib_path . 'Availability.php';
