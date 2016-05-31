<?php
/**
 * PHPUnit bootstrap file
 *
 * @package test-plugin
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the files being tested.
 */
function _manually_load_files() {
	require dirname( dirname( __FILE__ ) ) . '/src/class-refactored-settings.php';
	require dirname( dirname( __FILE__ ) ) . '/src/class-refactored-settings-section.php';
	require dirname( dirname( __FILE__ ) ) . '/src/class-refactored-settings-field.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_files' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
