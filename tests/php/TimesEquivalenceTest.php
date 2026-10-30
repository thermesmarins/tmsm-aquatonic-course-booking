<?php

/**
 * Preuve d'équivalence : le nouveau calcul des créneaux (get_times_for_date / get_times_for_week)
 * doit renvoyer EXACTEMENT les mêmes données que le code d'avant l'optimisation (Legacy_Times_Calculator),
 * sur une base de réservations réaliste, pour plusieurs configurations, utilisateurs et nombres de participants.
 */
class TimesEquivalenceTest extends Abstract_Times_Test_Case {

	private const PARTICIPANTS = [ 0, 1, 3, 6, 30 ];
	private const WEEK_LENGTH  = 7;

	/** Configurations : pas de créneau (slotsize), durée du parcours, jours d'ouverture. */
	public static function configurations(): array {
		return [
			'pas de 15 min, 7j/7'             => [ 4, 90, 'full' ],
			'pas de 10 min, semaine seule'    => [ 6, 60, 'weekdays' ],
			'pas de 20 min, 7j/7'             => [ 3, 120, 'full' ],
			'pas de 30 min, plages coupées'   => [ 2, 90, 'split' ],
		];
	}

	/** @return string[] Dates de demain à J+8 (Y-m-d). */
	private function window_dates(): array {
		$dates = [];
		for ( $i = 1; $i <= 8; $i ++ ) {
			$dates[] = ( new DateTime( 'today' ) )->modify( "+$i days" )->format( 'Y-m-d' );
		}

		return $dates;
	}

	private function timeslots_config( string $variant, array $dates ): string {
		$lines = [];
		if ( 'weekdays' === $variant ) {
			foreach ( [ 1, 2, 3, 4, 5 ] as $day ) {
				$lines[] = "$day=09:00-21:00=40";
			}
		} elseif ( 'split' === $variant ) {
			foreach ( [ 0, 1, 2, 3, 4, 5, 6 ] as $day ) {
				$lines[] = "$day=09:00-12:00,14:00-21:30=35";
			}
		} else {
			$lines = [ '0=09:00-18:00=20', '1=09:00-21:00=40', '2=09:00-21:00=40', '3=09:00-21:00=30', '4=09:00-21:00=40', '5=09:00-21:30=50', '6=08:30-21:30=50' ];
		}
		// Dates spéciales : horaires réduits avec petite capacité, et jour fermé (capacité 0).
		$lines[] = "{$dates[2]}=10:00-14:00=15";
		$lines[] = "{$dates[4]}=09:00-21:00=0";

		return implode( "\n", $lines );
	}

	private function seed_bookings( array $dates, int $course_average ): void {
		mt_srand( 4242 + $course_average );
		$rows = [];
		foreach ( $dates as $date ) {
			for ( $i = 0; $i < 90; $i ++ ) {
				$start    = new DateTime( "$date 08:00:00" );
				$start->modify( '+' . ( 5 * mt_rand( 0, 156 ) ) . ' minutes' );
				$duration = $course_average + [ - 15, 0, 0, 15 ][ mt_rand( 0, 3 ) ];
				$end      = ( clone $start )->modify( "+$duration minutes" );
				$rows[]   = [
					'participants' => mt_rand( 1, 6 ),
					'course_start' => $start->format( 'Y-m-d H:i:s' ),
					'course_end'   => $end->format( 'Y-m-d H:i:s' ),
					'status'       => [ 'active', 'active', 'active', 'arrived', 'cancelled', 'noshow' ][ mt_rand( 0, 5 ) ],
				];
			}
		}
		// Créneau saturé sur la date spéciale à capacité 15 : 3 x 5 personnes à 10h00.
		for ( $i = 0; $i < 3; $i ++ ) {
			$rows[] = [ 'participants' => 5, 'course_start' => "{$dates[2]} 10:00:00", 'course_end' => "{$dates[2]} 11:30:00", 'status' => 'active' ];
		}
		$this->wpdb->insert_bookings( $rows );
	}

	private function arrange( int $slotsize, int $course_average, string $variant ): array {
		$dates = $this->window_dates();
		$this->setOptions( [
			'slotsize'      => $slotsize,
			'courseaverage' => $course_average,
			'timeslots'     => $this->timeslots_config( $variant, $dates ),
		] );
		$this->seed_bookings( $dates, $course_average );

		return array_slice( $dates, 0, self::WEEK_LENGTH );
	}

	private function legacy_times( string $date, int $participants ): array {
		$_REQUEST['date']         = $date;
		$_REQUEST['participants'] = (string) $participants;

		return ( new ReflectionMethod( $this->legacy, '_get_times' ) )->invoke( $this->legacy );
	}

	/** @return array<string, Tmsm_Test_User> */
	private function users(): array {
		return [
			'anonyme' => new Tmsm_Test_User( 0, [] ),
			'client'  => new Tmsm_Test_User( 2, [ 'customer' ] ),
			'admin'   => new Tmsm_Test_User( 1, [ 'administrator' ] ),
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'configurations' )]
	public function test_a_day_is_identical_to_the_previous_implementation( int $slotsize, int $course_average, string $variant ): void {
		$week      = $this->arrange( $slotsize, $course_average, $variant );
		$real      = 0;
		$closed    = 0;
		$capacities = [];

		foreach ( $this->users() as $label => $user ) {
			$GLOBALS['tmsm_test_user'] = $user;
			foreach ( self::PARTICIPANTS as $participants ) {
				foreach ( $week as $date ) {
					$expected = $this->legacy_times( $date, $participants );
					$actual   = $this->current->get_times_for_date( $date, $participants );

					$this->assertSame( $expected, $actual, "utilisateur $label, $participants pers., $date" );

					foreach ( $actual as $slot ) {
						null !== $slot['hourminutes'] ? $real ++ : null;
						'Closed' === ( $slot['message'] ?? '' ) ? $closed ++ : null;
						isset( $slot['capacity'] ) ? $capacities[ $slot['capacity'] ] = true : null;
					}
				}
			}
		}

		// Garde-fou : le test ne doit pas être "vert" parce que tout est vide.
		$this->assertGreaterThan( 100, $real, 'de vrais créneaux doivent être comparés' );
		$this->assertGreaterThan( 0, $closed, 'des jours fermés doivent être comparés' );
		$this->assertGreaterThan( 3, count( $capacities ), 'les réservations doivent faire varier les capacités restantes' );
	}

	#[\PHPUnit\Framework\Attributes\DataProvider( 'configurations' )]
	public function test_a_week_is_identical_to_seven_previous_day_calls( int $slotsize, int $course_average, string $variant ): void {
		$week = $this->arrange( $slotsize, $course_average, $variant );

		foreach ( $this->users() as $label => $user ) {
			$GLOBALS['tmsm_test_user'] = $user;
			foreach ( self::PARTICIPANTS as $participants ) {
				$actual = $this->current->get_times_for_week( $week[0], self::WEEK_LENGTH, $participants );

				$this->assertSame( $week, array_keys( $actual ), 'une entrée par jour, dans l\'ordre' );
				foreach ( $week as $date ) {
					$this->assertSame( $this->legacy_times( $date, $participants ), $actual[ $date ], "utilisateur $label, $participants pers., $date" );
				}
			}
		}
	}

	public function test_a_week_runs_at_most_one_booking_query_per_day_instead_of_one_per_slot(): void {
		$week = $this->arrange( 4, 90, 'full' );

		$this->reset_booking_query_count();
		foreach ( $week as $date ) {
			$this->legacy_times( $date, 2 );
		}
		$legacy_queries = $this->wpdb->booking_queries;

		$this->reset_booking_query_count();
		$this->current->get_times_for_week( $week[0], self::WEEK_LENGTH, 2 );
		$current_queries = $this->wpdb->booking_queries;

		fwrite( STDERR, sprintf( "\n[requêtes SQL sur les réservations pour une semaine] avant : %d — après : %d\n", $legacy_queries, $current_queries ) );

		$this->assertLessThanOrEqual( self::WEEK_LENGTH, $current_queries );
		$this->assertGreaterThan( 20 * $current_queries, $legacy_queries );
	}

	public function test_instantiating_the_class_does_not_compute_times(): void {
		$week = $this->arrange( 4, 90, 'full' );
		$_REQUEST['date']         = $week[0];
		$_REQUEST['participants'] = '2';

		$this->reset_booking_query_count();
		new Tmsm_Aquatonic_Course_Booking_Public( self::PLUGIN_NAME, '0.0.0-test' );

		$this->assertSame( 0, $this->wpdb->booking_queries, 'le constructeur ne doit lancer aucune requête (calcul inutile à chaque requête WordPress)' );
	}

	public function test_get_times_for_week_rejects_an_invalid_start_date(): void {
		$this->arrange( 4, 90, 'full' );

		$this->expectException( InvalidArgumentException::class );
		$this->current->get_times_for_week( '31/12/2030', self::WEEK_LENGTH, 2 );
	}

	public function test_get_times_for_week_clamps_the_number_of_days(): void {
		$week = $this->arrange( 4, 90, 'full' );

		$this->assertCount( self::WEEK_LENGTH, $this->current->get_times_for_week( $week[0], 99, 2 ) );
		$this->assertCount( 1, $this->current->get_times_for_week( $week[0], 0, 2 ) );
	}
}
