<?php

use PHPUnit\Framework\TestCase;

/**
 * Base commune : base MySQL de test, table des réservations, options du plugin.
 */
abstract class Abstract_Times_Test_Case extends TestCase {

	protected const PLUGIN_NAME = 'tmsm-aquatonic-course-booking';

	/** @var Tmsm_Test_Wpdb */
	protected $wpdb;

	/** @var Tmsm_Aquatonic_Course_Booking_Public */
	protected $current;

	/** @var Legacy_Times_Calculator */
	protected $legacy;

	protected function setUp(): void {
		$db = tmsm_test_connect();
		if ( null === $db ) {
			$this->markTestSkipped( 'MySQL de test injoignable : lancer tests/php/run.sh' );
		}

		$this->wpdb = new Tmsm_Test_Wpdb( $db );
		$GLOBALS['wpdb'] = $this->wpdb;

		$this->wpdb->exec( 'DROP TABLE IF EXISTS wp_aquatonic_course_booking' );
		$this->wpdb->exec(
			'CREATE TABLE wp_aquatonic_course_booking (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				participants TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
				course_start DATETIME NULL DEFAULT NULL,
				course_end DATETIME NULL DEFAULT NULL,
				status VARCHAR(20) NOT NULL DEFAULT \'active\',
				KEY idx_course (course_start, course_end)
			)'
		);

		$GLOBALS['tmsm_test_user'] = new Tmsm_Test_User();
		$this->setOptions( [] );

		$this->current = new Tmsm_Aquatonic_Course_Booking_Public( self::PLUGIN_NAME, '0.0.0-test' );
		$this->legacy  = new Legacy_Times_Calculator( self::PLUGIN_NAME );
	}

	/**
	 * Options du plugin avec des valeurs par défaut permissives (aucun créneau futur n'est filtré).
	 */
	protected function setOptions( array $options ): void {
		$GLOBALS['tmsm_test_options'] = [
			self::PLUGIN_NAME . '-options' => array_merge( [
				'courseaverage' => 90,
				'slotsize'      => 4,
				'hoursbefore'   => 0,
				'hoursafter'    => 24 * 60,
				'timeslots'     => '',
			], $options ),
		];
	}

	protected function reset_booking_query_count(): void {
		$this->wpdb->booking_queries = 0;
	}
}
