<?php

/**
 * Le calcul groupé des participants en cours doit donner, créneau par créneau,
 * exactement le même résultat que l'ancienne requête SQL exécutée pour chaque créneau.
 */
class ParticipantsOngoingTest extends Abstract_Times_Test_Case {

	private const DAY = '2030-06-10';

	private function slots( string $first, string $last, int $minutes ): array {
		$interval = DateInterval::createFromDateString( $minutes . ' minutes' );

		return [
			new DateTime( self::DAY . ' ' . $first ),
			new DateTime( self::DAY . ' ' . $last ),
			$interval,
		];
	}

	public function test_booking_counts_from_its_start_included_to_its_end_excluded(): void {
		$this->wpdb->insert_bookings( [
			[ 'participants' => 3, 'course_start' => self::DAY . ' 10:00:00', 'course_end' => self::DAY . ' 11:00:00', 'status' => 'active' ],
		] );

		[ $first, $last, $interval ] = $this->slots( '09:45', '11:15', 15 );
		$uses = $this->current->get_participants_ongoing_forperiod( $first, $last, $interval );

		$this->assertSame( 0, $uses[ self::DAY . ' 09:45:00' ] );
		$this->assertSame( 3, $uses[ self::DAY . ' 10:00:00' ], 'début inclus' );
		$this->assertSame( 3, $uses[ self::DAY . ' 10:45:00' ] );
		$this->assertSame( 0, $uses[ self::DAY . ' 11:00:00' ], 'fin exclue' );
	}

	public function test_only_active_and_arrived_bookings_are_counted_and_summed(): void {
		$day = self::DAY;
		$this->wpdb->insert_bookings( [
			[ 'participants' => 2, 'course_start' => "$day 10:00:00", 'course_end' => "$day 11:30:00", 'status' => 'active' ],
			[ 'participants' => 4, 'course_start' => "$day 10:15:00", 'course_end' => "$day 11:45:00", 'status' => 'arrived' ],
			[ 'participants' => 5, 'course_start' => "$day 10:00:00", 'course_end' => "$day 11:30:00", 'status' => 'cancelled' ],
			[ 'participants' => 6, 'course_start' => "$day 10:00:00", 'course_end' => "$day 11:30:00", 'status' => 'noshow' ],
		] );

		[ $first, $last, $interval ] = $this->slots( '10:00', '10:45', 15 );
		$uses = $this->current->get_participants_ongoing_forperiod( $first, $last, $interval );

		$this->assertSame( 2, $uses[ "$day 10:00:00" ] );
		$this->assertSame( 6, $uses[ "$day 10:15:00" ] );
		$this->assertSame( 6, $uses[ "$day 10:30:00" ] );
	}

	public function test_period_excludes_its_last_slot_like_the_previous_implementation(): void {
		[ $first, $last, $interval ] = $this->slots( '10:00', '11:00', 15 );
		$uses = $this->current->get_participants_ongoing_forperiod( $first, $last, $interval );

		$this->assertSame(
			[ '10:00:00', '10:15:00', '10:30:00', '10:45:00' ],
			array_map( fn( $key ) => substr( $key, 11 ), array_keys( $uses ) )
		);
	}

	public function test_grouped_result_matches_the_per_slot_query_with_overlaps_and_boundaries(): void {
		mt_srand( 20300610 );
		$rows = [];
		for ( $i = 0; $i < 150; $i ++ ) {
			$start  = new DateTime( self::DAY . ' 08:00:00' );
			$start->modify( '+' . ( 5 * mt_rand( 0, 150 ) ) . ' minutes' );
			$end    = ( clone $start )->modify( '+' . ( 5 * mt_rand( 6, 30 ) ) . ' minutes' );
			$rows[] = [
				'participants' => mt_rand( 1, 6 ),
				'course_start' => $start->format( 'Y-m-d H:i:s' ),
				'course_end'   => $end->format( 'Y-m-d H:i:s' ),
				'status'       => [ 'active', 'arrived', 'cancelled', 'noshow' ][ mt_rand( 0, 3 ) ],
			];
		}
		$this->wpdb->insert_bookings( $rows );

		foreach ( [ 10, 15, 20, 30 ] as $minutes ) {
			[ $first, $last, $interval ] = $this->slots( '08:00', '21:00', $minutes );
			$uses = $this->current->get_participants_ongoing_forperiod( $first, $last, $interval );

			$this->assertNotEmpty( $uses );
			foreach ( $uses as $slot => $count ) {
				$expected = (int) $this->legacy->get_participants_ongoing_forthetime( new DateTime( $slot ) );
				$this->assertSame( $expected, $count, "créneau $slot (pas de $minutes min)" );
			}
		}
	}

	public function test_a_single_query_is_executed_whatever_the_number_of_slots(): void {
		[ $first, $last, $interval ] = $this->slots( '08:00', '21:00', 15 );

		$this->reset_booking_query_count();
		$this->current->get_participants_ongoing_forperiod( $first, $last, $interval );

		$this->assertSame( 1, $this->wpdb->booking_queries );
	}
}
