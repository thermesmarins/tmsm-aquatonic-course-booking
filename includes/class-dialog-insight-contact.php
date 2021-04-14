<?php
namespace Tmsm_Aquatonic_Course_Booking;

class Dialog_Insight_Contact {


	/**
	 * @var      string    $email
	 */
	public $email;

	/**
	 * @var      int    $contact_id
	 */
	public $contact_id;

	/**
	 * @var      string    $firstname
	 */
	public $firstname;

	/**
	 * @var      string    $lastname
	 */
	public $lastname;

	/**
	 * @var      string    $phone
	 */
	public $phone;

	/**
	 * @var      string    $birthdate
	 */
	public $birthdate;

	/**
	 * @var      int    $title
	 */
	public $title;


	public function __construct() {

	}

	/**
	 * Get contact by Email
	 *
	 * @throws \Exception
	 */
	public function get_by_email( ){

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log( 'dialoginsight_get_contact' );
		}

		$result_array = [];
		$contact      = [];

		$request = [
			'Clause'    => [
				'$type'           => 'FieldClause',
				'Field'           => [
					'Name' => 'f_EMail',
				],
				'TypeOperator'    => 'Equal',
				'ComparisonValue' => $this->email,

			],
			'Tag'       => null,
		];

		$contacts = \Dialog_Insight_API::request( $request, 'contacts', 'Get' );

		//error_log( '$contacts:' );
		//error_log( print_r( $contacts, true ) );

		if ( ! empty( $contacts->Records ) && ! empty( $contacts->Records[0] ) ) {
			error_log( 'First contact found, assigning values' );
			$contact          = $contacts->Records[0];
			$this->email      = $contact->f_EMail ?? null;
			$this->firstname  = $contact->f_FirstName ?? null;
			$this->lastname   = $contact->f_LastName ?? null;
			$this->contact_id = $contact->idContact ?? null;
		}

	}

	/**
	 * Add contact
	 *
	 * @throws \Exception
	 */
	public function add( ){

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log( 'Dialog Insight Add Contact' );
		}

		$result_array = [];
		$contact      = [];

		$request = [
			'Records' => [
				[
					'ID'   => [
						'key_f_EMail' => $this->email,
					],
					'Data' => [
						'f_EMail'         => $this->email,
						'f_FirstName'     => $this->firstname,
						'f_civilite'     =>  ( $this->title == 1 ? 'M.' : 'Mme'),
						'f_LastName'      => $this->lastname,
						'f_dateNaissance' => self::format_birthdate($this->birthdate),
						'f_MobilePhone'   => $this->phone,
					],
				],
			],
			'MergeOptions' => [
				'AllowInsert'            => true,
				'AllowUpdate'            => true,
				'SkipDuplicateRecords'   => false,
				'SkipUnmatchedRecords'   => false,
				'ReturnRecordsOnSuccess' => false,
				'ReturnRecordsOnError'   => false,
				'FieldOptions'           => null,
			],
		];

		try {
			$result = \Dialog_Insight_API::request( $request, 'contacts', 'Merge' );
		} catch (\Exception $exception) {

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log( 'Dialog Insight Add Contact Error: ' . $exception->getMessage() );
			}
			return false;

		}

		//error_log( '$contacts:' );
		//error_log( print_r( $contacts, true ) );

		if ( ! empty($result) && ! empty( $result->Success ) &&  $result->Success== true ) {

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log( 'Dialog Insight contact added successfully' );
			}
			return true;
		}
		else{
			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true && ! empty($result) && ! empty( $result->ErrorMessage )){
				error_log( 'Dialog Insight contact not added, error: ' . $result->ErrorMessage );
			}
			return false;
		}

	}


	/**
	 * Format birthdate for Dialog Insight
	 *
	 * @param string $birthdate
	 *
	 * @return string
	 */
	static function format_birthdate( $birthdate){
		return str_replace('-', '.', $birthdate);
	}

}