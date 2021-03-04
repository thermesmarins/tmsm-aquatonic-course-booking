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


	public function __construct() {

		error_log('Dialog_Insight_Contact __construct');
	}

	/**
	 * Get contact by Email
	 *
	 * @throws \Exception
	 */
	public function get_by_email( ){


		error_log( 'dialoginsight_get_contact' );

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

		error_log( '$contacts:' );
		error_log( print_r( $contacts, true ) );

		if ( ! empty( $contacts->Records ) && ! empty( $contacts->Records[0] ) ) {
			error_log( 'First contact found, assigning values' );
			$contact          = $contacts->Records[0];
			$this->email      = $contact->f_EMail ?? null;
			$this->firstname  = $contact->f_FirstName ?? null;
			$this->lastname   = $contact->f_LastName ?? null;
			$this->contact_id = $contact->idContact ?? null;
		}

	}

}