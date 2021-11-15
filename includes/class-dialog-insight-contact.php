<?php
namespace Tmsm_Aquatonic_Course_Booking;

class Dialog_Insight_Contact {

	const PLUGIN_NAME = 'tmsm-aquatonic-course-booking';

	/**
	 * @var      int    $title
	 */
	public $title;

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
	 * @var      string    $postalcode
	 */
	public $postalcode;

	/**
	 * @var      string    $city
	 */
	public $city;

	/**
	 * @var      string    $beneficiary
	 */
	public $beneficiary;


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
			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log( 'First contact found, assigning values' );
			}
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

		$data = [
			'f_EMail'         => $this->email,
			'f_FirstName'     => $this->firstname,
			'f_civilite'     =>  ( $this->title == 1 ? 'M.' : 'Mme'),
			'f_LastName'      => $this->lastname,
		];
		if(!empty($this->birthdate)){
			$data['f_dateNaissance'] = self::format_birthdate($this->birthdate);
		}
		if(!empty($this->postalcode)){
			$data['f_origine_codePostal'] = self::format_postalcode($this->postalcode);
		}
		if(!empty($this->city)){
			$data['f_origine_ville'] = self::format_city($this->city);
		}
		if(!empty($this->phone)){
			$data['f_MobilePhone'] = self::format_phone($this->phone);
		}

		$request = [
			'Records' => [
				[
					'ID'   => [
						'key_f_EMail' => $this->email,
					],
					'Data' => $data,
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
	 * Update contact by ID
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function update_by_id( ){

		if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
			error_log( 'Dialog Insight Update Contact by ID' );
		}

		$result_array = [];
		$contact      = [];
		$data         = [
			'f_EMail' => $this->email,
		];

		if ($this->beneficiary == 1){
			$beneficiaryfield = self::get_option( 'dialoginsight_beneficiaryfield' );
			if( ! empty( $beneficiaryfield ) ){
				$data['f_'.$beneficiaryfield] = 1;
			}
		}

		$request = [
			'Records' => [
				[
					'ID'   => [
						'key_idContact' => $this->contact_id,
					],
					'Data' => $data,
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
				error_log( 'Dialog Insight Update Contact Error: ' . $exception->getMessage() );
			}
			return false;

		}

		if ( ! empty($result) && ! empty( $result->Success ) &&  $result->Success== true ) {

			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true){
				error_log( 'Dialog Insight updated added successfully' );
			}
			return true;
		}
		else{
			if(defined('TMSM_AQUATONIC_COURSE_BOOKING_DEBUG') && TMSM_AQUATONIC_COURSE_BOOKING_DEBUG === true && ! empty($result) && ! empty( $result->ErrorMessage )){
				error_log( 'Dialog Insight contact not updated, error: ' . $result->ErrorMessage );
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

	/**
	 * Format postalcode for Dialog Insight
	 *
	 * @param string $postalcode
	 *
	 * @return string
	 */
	static function format_postalcode( $postalcode){
		return substr( $postalcode, 0, 20 );
	}

	/**
	 * Format city for Dialog Insight
	 *
	 * @param string $postalcode
	 *
	 * @return string
	 */
	static function format_city( $city ){
		return substr( $city, 0, 40 );
	}

	/**
	 * Format phone for Dialog Insight
	 *
	 * @param string $phone
	 *
	 * @return string
	 */
	static function format_phone( $phone ){
		return str_replace(' ', '', trim( preg_replace( '/[^0-9\+\-\(\)\s]/', '-', preg_replace( '/[\x00-\x1F\x7F-\xFF]/', '', $phone ) ) ) );
	}

	/**
	 * Get option
	 * @param string $option_name
	 *
	 * @return null
	 */
	static function get_option($option_name = null){

		$options = get_option(self::PLUGIN_NAME . '-options');

		if(!empty($option_name)){
			return $options[$option_name] ?? null;
		}
		else{
			return $options;
		}

	}



}