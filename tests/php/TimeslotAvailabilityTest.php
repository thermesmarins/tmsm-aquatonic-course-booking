<?php

/**
 * Contrôle d'un créneau choisi au moment de la validation du formulaire :
 * is_timeslot_available() doit répondre « oui » exactement quand le créneau
 * serait proposé par le calendrier (get_times_for_date), pour le même utilisateur.
 *
 * Configuration : pas de 15 min, parcours de 90 min, capacité 10 de 09:00 à 18:00 sur la date testée.
 */
class TimeslotAvailabilityTest extends Abstract_Times_Test_Case {

	private string $date;

	protected function setUp(): void {
		parent::setUp();

		$this->date = ( new DateTime( 'today' ) )->modify( '+2 days' )->format( 'Y-m-d' );
		$this->configure();
	}

	/** Options du jour testé ; $hoursbefore = délai minimum imposé aux clients et visiteurs. */
	private function configure( int $capacity = 10, int $hoursbefore = 0 ): void {
		$this->setOptions( [
			'slotsize'      => 4,
			'courseaverage' => 90,
			'hoursbefore'   => $hoursbefore,
			'hoursafter'    => 24 * 60 * 5,
			'timeslots'     => "{$this->date}=09:00-18:00=$capacity",
		] );
	}

	private function book( int $participants, string $start, string $end, string $status = 'active' ): void {
		$this->wpdb->insert_bookings( [ [
			'participants' => $participants,
			'course_start' => "{$this->date} $start:00",
			'course_end'   => "{$this->date} $end:00",
			'status'       => $status,
		] ] );
	}

	private function login_as( int $id, array $roles ): void {
		$GLOBALS['tmsm_test_user'] = new Tmsm_Test_User( $id, $roles );
	}

	public function test_free_slot_is_available(): void {
		$this->assertTrue( $this->current->is_timeslot_available( $this->date, '14:00', 4 ) );
	}

	public function test_slot_filled_by_other_bookings_is_not_available(): void {
		$this->book( 10, '10:00', '11:30' );

		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '10:00', 1 ) );
	}

	public function test_slot_overlapping_a_filled_period_is_not_available(): void {
		// Le parcours de 09:00 dure jusqu'à 10:30 et croise la période saturée de 10:00.
		$this->book( 10, '10:00', '11:30' );

		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '09:00', 1 ) );
	}

	public function test_slot_starting_when_the_other_booking_ends_is_available(): void {
		// La fin d'un parcours est exclue : 11:30 est libre.
		$this->book( 10, '10:00', '11:30' );

		$this->assertTrue( $this->current->is_timeslot_available( $this->date, '11:30', 10 ) );
	}

	public function test_remaining_capacity_decides_for_the_number_of_participants(): void {
		$this->book( 6, '10:00', '11:30' );

		$this->assertTrue( $this->current->is_timeslot_available( $this->date, '10:00', 4 ) );
		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '10:00', 5 ) );
	}

	public function test_cancelled_and_noshow_bookings_do_not_count(): void {
		$this->book( 10, '10:00', '11:30', 'cancelled' );
		$this->book( 10, '10:00', '11:30', 'noshow' );

		$this->assertTrue( $this->current->is_timeslot_available( $this->date, '10:00', 10 ) );
	}

	public function test_time_that_is_not_a_slot_is_not_available(): void {
		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '10:07', 1 ) );
	}

	public function test_slot_whose_course_ends_after_closing_is_not_available(): void {
		// 17:30 + 90 min dépasse la fermeture de 18:00, comme dans le calendrier.
		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '17:30', 1 ) );
	}

	public function test_closed_day_is_not_available(): void {
		$this->configure( 0 );

		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '14:00', 1 ) );
	}

	public function test_empty_or_invalid_input_is_not_available(): void {
		$this->assertFalse( $this->current->is_timeslot_available( '', '14:00', 2 ) );
		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '', 2 ) );
		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '14:00', 0 ) );
		$this->assertFalse( $this->current->is_timeslot_available( 'not-a-date', '14:00', 2 ) );
	}

	public function test_customer_cannot_book_before_the_hoursbefore_delay(): void {
		// La date testée est dans 2 jours : trop tôt avec un délai de 3 jours.
		$this->configure( 10, 24 * 3 );
		$this->login_as( 5, [ 'customer' ] );

		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '14:00', 2 ) );
	}

	public function test_anonymous_visitor_follows_the_customer_rule(): void {
		$this->configure( 10, 24 * 3 );

		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '14:00', 2 ) );
	}

	public function test_non_customer_follows_the_same_availability_rule_but_ignores_hoursbefore(): void {
		$this->configure( 10, 24 * 3 );
		$this->book( 10, '10:00', '11:30' );
		$this->login_as( 1, [ 'administrator' ] );

		// Même règle de capacité que le calendrier...
		$this->assertFalse( $this->current->is_timeslot_available( $this->date, '10:00', 1 ) );
		// ...et les mêmes créneaux que lui, y compris avant le délai « hoursbefore ».
		$this->assertTrue( $this->current->is_timeslot_available( $this->date, '14:00', 2 ) );
	}

	/**
	 * Garde-fou d'équivalence : pour chaque quart d'heure de la journée, la réponse du contrôle
	 * est identique à la présence du créneau dans la liste du calendrier.
	 */
	public function test_answer_matches_calendar_list_for_every_quarter_hour_of_the_day(): void {
		$this->book( 7, '10:00', '11:30' );
		$this->book( 3, '12:15', '13:45' );

		foreach ( [ 1, 3, 4, 8 ] as $participants ) {
			$offered = array_column( $this->current->get_times_for_date( $this->date, $participants ), 'hourminutes' );
			for ( $minute = 8 * 60; $minute < 19 * 60; $minute += 15 ) {
				$hourminutes = sprintf( '%02d:%02d', intdiv( $minute, 60 ), $minute % 60 );
				$this->assertSame(
					in_array( $hourminutes, $offered, true ),
					$this->current->is_timeslot_available( $this->date, $hourminutes, $participants ),
					"$hourminutes pour $participants participant(s)"
				);
			}
		}
	}
}
