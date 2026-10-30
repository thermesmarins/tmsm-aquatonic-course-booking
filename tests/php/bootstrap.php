<?php
/**
 * Bootstrap PHPUnit : cales WordPress + classe publique du plugin + référence figée (legacy).
 */

date_default_timezone_set( 'UTC' );

require_once __DIR__ . '/Support/wp-stubs.php';
require_once __DIR__ . '/Support/class-test-wpdb.php';
require_once __DIR__ . '/../../public/class-tmsm-aquatonic-course-booking-public.php';
require_once __DIR__ . '/Legacy/class-legacy-times-calculator.php';
require_once __DIR__ . '/Support/abstract-times-test-case.php';

/**
 * Connexion à la base MySQL de test (jetable, lancée par run.sh).
 * Variables : TMSM_TEST_DB_HOST, TMSM_TEST_DB_PORT, TMSM_TEST_DB_USER, TMSM_TEST_DB_PASS, TMSM_TEST_DB_NAME.
 */
function tmsm_test_connect(): ?mysqli {
	mysqli_report( MYSQLI_REPORT_OFF );
	$db = @new mysqli(
		getenv( 'TMSM_TEST_DB_HOST' ) ?: '127.0.0.1',
		getenv( 'TMSM_TEST_DB_USER' ) ?: 'root',
		getenv( 'TMSM_TEST_DB_PASS' ) ?: 'test',
		getenv( 'TMSM_TEST_DB_NAME' ) ?: 'tmsm_test',
		(int) ( getenv( 'TMSM_TEST_DB_PORT' ) ?: 33306 )
	);

	return $db->connect_errno ? null : $db;
}
