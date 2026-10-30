<?php

/**
 * Filtre GravityForms de validation du créneau : à la soumission, le champ « heure » est refusé
 * si le créneau n'est plus proposé par le calendrier. Les ids de champs varient selon le site
 * (ici 6 / 10 / 11, comme sur le formulaire de réservation) : ils sont retrouvés par classe CSS.
 */
class TimeslotValidationTest extends Abstract_Times_Test_Case {

	private const PARTICIPANTS_ID = 6;
	private const HOURMINUTES_ID  = 10;
	private const DATE_ID         = 11;

	private string $date;
	private array $form;
	private Tmsm_Test_Gf_Field $hourminutes_field;

	protected function setUp(): void {
		parent::setUp();

		$this->date = ( new DateTime( 'today' ) )->modify( '+2 days' )->format( 'Y-m-d' );
		$this->setOptions( [
			'slotsize'      => 4,
			'courseaverage' => 90,
			'hoursbefore'   => 0,
			'hoursafter'    => 24 * 60 * 5,
			'timeslots'     => "{$this->date}=09:00-18:00=10",
		] );

		$this->hourminutes_field = new Tmsm_Test_Gf_Field( self::HOURMINUTES_ID, 'tmsm-aquatonic-course-hourminutes disabled' );
		$this->form              = [
			'id'     => 1,
			'fields' => [
				new Tmsm_Test_Gf_Field( self::PARTICIPANTS_ID, 'tmsm-aquatonic-course-participants' ),
				$this->hourminutes_field,
				new Tmsm_Test_Gf_Field( self::DATE_ID, 'tmsm-aquatonic-course-date disabled' ),
				new Tmsm_Test_Gf_Field( 12, 'tmsm-aquatonic-course-lastname' ),
			],
		];

		$this->submit( '2', $this->date, [ '14', '00' ] );
	}

	protected function tearDown(): void {
		$_POST = [];
	}

	private function submit( $participants, $date, $time ): void {
		$_POST = [
			'input_' . self::PARTICIPANTS_ID => $participants,
			'input_' . self::DATE_ID         => $date,
			'input_' . self::HOURMINUTES_ID  => $time,
		];
	}

	private function validate( $value = null, ?Tmsm_Test_Gf_Field $field = null, array $result = [ 'is_valid' => true, 'message' => '' ] ): array {
		return $this->current->gform_field_validation_timeslot(
			$result,
			$value ?? $_POST[ 'input_' . self::HOURMINUTES_ID ],
			$this->form,
			$field ?? $this->hourminutes_field
		);
	}

	private function book_everything_at_ten(): void {
		$this->wpdb->insert_bookings( [ [
			'participants' => 10,
			'course_start' => "{$this->date} 10:00:00",
			'course_end'   => "{$this->date} 11:30:00",
			'status'       => 'active',
		] ] );
	}

	public function test_available_slot_stays_valid(): void {
		$result = $this->validate();

		$this->assertTrue( $result['is_valid'] );
		$this->assertSame( '', $result['message'] );
	}

	public function test_slot_filled_in_the_meantime_is_refused_with_a_message(): void {
		$this->book_everything_at_ten();
		$this->submit( '2', $this->date, [ '10', '00' ] );

		$result = $this->validate();

		$this->assertFalse( $result['is_valid'] );
		$this->assertNotSame( '', $result['message'] );
	}

	public function test_too_many_participants_for_the_remaining_capacity_is_refused(): void {
		$this->wpdb->insert_bookings( [ [
			'participants' => 6,
			'course_start' => "{$this->date} 10:00:00",
			'course_end'   => "{$this->date} 11:30:00",
			'status'       => 'active',
		] ] );
		$this->submit( '5', $this->date, [ '10', '00' ] );

		$this->assertFalse( $this->validate()['is_valid'] );

		$this->submit( '4', $this->date, [ '10', '00' ] );
		$this->assertTrue( $this->validate()['is_valid'] );
	}

	public function test_hour_and_minutes_without_leading_zero_are_understood(): void {
		$this->book_everything_at_ten();

		$this->submit( '2', $this->date, [ '9', '0' ] ); // 09:00 croise la période saturée de 10:00
		$this->assertFalse( $this->validate()['is_valid'] );

		$this->submit( '2', $this->date, [ '14', '0' ] ); // 14:00 est libre
		$this->assertTrue( $this->validate()['is_valid'] );
	}

	public function test_time_given_as_a_string_is_understood(): void {
		$this->book_everything_at_ten();
		$this->submit( '2', $this->date, '10:00' );

		$this->assertFalse( $this->validate()['is_valid'] );
	}

	public function test_other_fields_are_left_alone(): void {
		$this->book_everything_at_ten();
		$this->submit( '2', $this->date, [ '10', '00' ] );
		$lastname = $this->form['fields'][3];

		$result = $this->validate( 'Dupont', $lastname );

		$this->assertTrue( $result['is_valid'] );
	}

	public function test_an_already_invalid_result_is_not_modified(): void {
		$result = $this->validate( null, null, [ 'is_valid' => false, 'message' => 'Required' ] );

		$this->assertFalse( $result['is_valid'] );
		$this->assertSame( 'Required', $result['message'] );
	}

	public function test_missing_values_are_left_to_the_required_field_validation(): void {
		$this->book_everything_at_ten();

		$this->submit( '', $this->date, [ '10', '00' ] );
		$this->assertTrue( $this->validate()['is_valid'], 'participants manquants' );

		$this->submit( '2', '', [ '10', '00' ] );
		$this->assertTrue( $this->validate()['is_valid'], 'date manquante' );

		$this->submit( '2', $this->date, [ '', '' ] );
		$this->assertTrue( $this->validate( [ '', '' ] )['is_valid'], 'heure manquante' );
	}

	public function test_malformed_date_is_refused(): void {
		$this->submit( '2', '31/02/2099', [ '14', '00' ] );

		$this->assertFalse( $this->validate()['is_valid'] );
	}

	private const BANNER = '<div class="gform_validation_errors"><h2>Une erreur s’est produite</h2></div>';

	public function test_banner_shows_the_message_of_the_hidden_time_field(): void {
		$this->hourminutes_field->failed_validation  = true;
		$this->hourminutes_field->validation_message = 'This time slot is no longer available, please choose another one.';

		$banner = $this->current->gform_validation_message_timeslot( self::BANNER, $this->form );

		$this->assertStringContainsString( 'This time slot is no longer available, please choose another one.', $banner );
		$this->assertStringContainsString( 'Une erreur s’est produite', $banner, 'le bandeau d\'origine est conservé' );
		// Le message est dans le bandeau, avant sa balise fermante.
		$this->assertLessThan( strrpos( $banner, '</div>' ), strpos( $banner, 'no longer available' ) );
	}

	public function test_banner_is_unchanged_when_the_time_field_is_valid(): void {
		$this->form['fields'][0]->failed_validation  = true; // un autre champ en erreur
		$this->form['fields'][0]->validation_message = 'Participants invalid';

		$this->assertSame( self::BANNER, $this->current->gform_validation_message_timeslot( self::BANNER, $this->form ) );
	}

	public function test_banner_message_is_escaped(): void {
		$this->hourminutes_field->failed_validation  = true;
		$this->hourminutes_field->validation_message = '<script>alert(1)</script>';

		$banner = $this->current->gform_validation_message_timeslot( self::BANNER, $this->form );

		$this->assertStringNotContainsString( '<script>', $banner );
		$this->assertStringContainsString( '&lt;script&gt;', $banner );
	}

	public function test_banner_without_closing_tag_gets_the_message_appended(): void {
		$this->hourminutes_field->failed_validation  = true;
		$this->hourminutes_field->validation_message = 'Heure invalide';

		$banner = $this->current->gform_validation_message_timeslot( 'Erreur', $this->form );

		$this->assertStringStartsWith( 'Erreur', $banner );
		$this->assertStringContainsString( 'Heure invalide', $banner );
	}

	public function test_non_customer_gets_the_same_check(): void {
		$GLOBALS['tmsm_test_user'] = new Tmsm_Test_User( 1, [ 'administrator' ] );
		$this->book_everything_at_ten();
		$this->submit( '2', $this->date, [ '10', '00' ] );

		$this->assertFalse( $this->validate()['is_valid'] );
	}
}
