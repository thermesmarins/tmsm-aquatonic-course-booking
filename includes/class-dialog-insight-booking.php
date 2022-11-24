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

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log('Dialog Insight add booking');
		}

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
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log('Dialog Insight Add Record to Bookings Table : No response');
					return false;
				}
			}
			return true;
		}
		else{

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log('Contact doesnt exist');
				return false;
			}
		}

	}

	/**
	 * Update Booking
	 *
	 * @throws \Exception
	 */
	public function update(){
		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log( 'Booking update()' );
		}
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
					'ReturnRecordsOnSuccess' => true,
					'ReturnRecordsOnError'   => false,
					'FieldOptions'           => null,
				],
			];

			$bookings = \Dialog_Insight_API::request( $request, 'relationaltables', 'Merge' );

			if ( ! empty( $bookings->Records ) ) {
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log( 'Records found' );
				}

				if(!empty($bookings->Records[0])){
					if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
						error_log( 'Booking found' );
					}
					$booking          = $bookings->Records[0];
					if( ! empty($booking->Record)){
						$booking_record          = $booking->Record;
						if( !empty($booking_record->idContact)){
							if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
								error_log( '$booking_record->idContact:' . $booking_record->idContact );
							}
							$this->contact_id = $booking_record->idContact ?? null;
						}
					}
					else{
						if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
							error_log( 'No booking found' );
						}
					}
				}

			}
			else{
				if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
					error_log( 'No records found' );
				}
			}

		}

	}

	/**
	 * Update Booking
	 *
	 * @throws \Exception
	 */
	public function getCountByIdContact() {

		$request = [
			'Clause' => [
				[
					'$type'           => 'FieldClause',
					'Field'           => [
						'Name' => 'idContact',
					],
					'TypeOperator'    => 'Equal',
					'ComparisonValue' => $this->contact_id,
				],
			],
		];

		$bookings = \Dialog_Insight_API::request( $request, 'relationaltables', 'Get' );
		if ( defined( 'TMSM_AQUATONIC_COURSE_BOOKING_DEBUG' ) && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true ) {
			error_log( 'Counted ' . count($bookings->Records) . ' records/bookings for contact id' . $this->contact_id );
		}
		return count($bookings->Records);

	}

}