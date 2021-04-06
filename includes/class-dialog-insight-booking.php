<?php
namespace Tmsm_Aquatonic_Course_Booking;

class Dialog_Insight_Booking {


	/**
	 * @var      string    $email
	 */
	public $email;

	/**
	 * @var      int    $contact_id
	 */
	public $contact_id;

	/**
	 * @var      string    $token
	 */
	public $token;

	/**
	 * @var      int    $participants
	 */
	public $participants;

	/**
	 * @var      string    $date_created
	 */
	public $date_created;

	/**
	 * @var      string    $course_start
	 */
	public $course_start;

	/**
	 * @var      string    $course_end
	 */
	public $course_end;

	/**
	 * @var      string    $status
	 */
	public $status;

	/**
	 * @var      string    $source
	 */
	public $source;


	public function __construct() {

	}

	/**
	 * Add Booking
	 *
	 * @throws \Exception
	 */
	public function add(){

		$contact = new Dialog_Insight_Contact();
		$contact->email = $this->email;
		$contact->get_by_email();

		// Contact exists and Auth OK
		if ( ! empty( $contact->contact_id ) ) {

			$request = [
				'Records' => [
					[
						'ID'   => [
							'key_idReservation' => $this->token,
						],
						'Data' => [
							'idContact'       => $contact->contact_id,
							'idReservation'   => $this->token,
							'nombre_personne' => $this->participants,
							'dateCreation'    => $this->date_created,
							'dateArrivee'     => $this->course_start,
							'dateFin'         => $this->course_end,
							'statut'          => $this->status,
							'source'          => $this->source,
						],
					],
				],
				'MergeOptions' => [
					'AllowInsert'            => true,
					'AllowUpdate'            => false,
					'SkipDuplicateRecords'   => false,
					'SkipUnmatchedRecords'   => false,
					'ReturnRecordsOnSuccess' => false,
					'ReturnRecordsOnError'   => false,
					'FieldOptions'           => null,
				],
			];

			$bookings = \Dialog_Insight_API::request( $request, 'relationaltables', 'Merge' );

			if ( empty( $bookings ) ) {
				//error_log( 'Dialog Insight Add Record to Bookings Table : No response' );
			}
		}
		else{
			//error_log( 'Contact doesnt exist' );
		}

	}

	/**
	 * Update Booking
	 *
	 * @throws \Exception
	 */
	public function update(){

		if( !empty($this->token) && !empty($this->status)){
			$request = [
				'Records' => [
					[
						'ID'   => [
							'key_idReservation' => $this->token,
						],
						'Data' => [
							'statut'          => $this->status,
						],
					],
				],
				'MergeOptions' => [
					'AllowInsert'            => false,
					'AllowUpdate'            => true,
					'SkipDuplicateRecords'   => false,
					'SkipUnmatchedRecords'   => false,
					'ReturnRecordsOnSuccess' => false,
					'ReturnRecordsOnError'   => false,
					'FieldOptions'           => null,
				],
			];

			$bookings = \Dialog_Insight_API::request( $request, 'relationaltables', 'Merge' );
		}

	}

}