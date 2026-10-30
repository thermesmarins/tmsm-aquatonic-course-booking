<?php
/**
 * Mini $wpdb branché sur un vrai MySQL : le SQL des requêtes est donc réellement exécuté.
 * Compte les requêtes sur la table des réservations pour mesurer le gain de l'optimisation.
 */
class Tmsm_Test_Wpdb {

	public $prefix = 'wp_';

	/** @var int Nombre de requêtes exécutées sur la table des réservations. */
	public $booking_queries = 0;

	/** @var mysqli */
	private $db;

	public function __construct( mysqli $db ) {
		$this->db = $db;
	}

	public function prepare( $query, ...$args ) {
		if ( count( $args ) === 1 && is_array( $args[0] ) ) {
			$args = $args[0];
		}
		$i = 0;

		return preg_replace_callback( '/%[sd]/', function ( $m ) use ( &$i, $args ) {
			$value = $args[ $i ++ ];

			return '%d' === $m[0] ? (string) (int) $value : "'" . $this->db->real_escape_string( (string) $value ) . "'";
		}, $query );
	}

	public function get_var( $sql ) {
		$result = $this->run( $sql );
		$row    = $result->fetch_row();

		return $row ? $row[0] : null;
	}

	public function get_results( $sql, $output = OBJECT ) {
		$result = $this->run( $sql );
		$rows   = [];
		while ( $row = ( ARRAY_A === $output ? $result->fetch_assoc() : $result->fetch_object() ) ) {
			$rows[] = $row;
		}

		return $rows;
	}

	public function exec( string $sql ): void {
		$this->db->query( $sql );
	}

	public function insert_bookings( array $rows ): void {
		if ( empty( $rows ) ) {
			return;
		}
		$values = [];
		foreach ( $rows as $r ) {
			$values[] = sprintf( "(%d,'%s','%s','%s')", $r['participants'], $r['course_start'], $r['course_end'], $r['status'] );
		}
		$this->db->query( "INSERT INTO {$this->prefix}aquatonic_course_booking (participants, course_start, course_end, status) VALUES " . implode( ',', $values ) );
	}

	private function run( $sql ) {
		if ( false !== strpos( $sql, 'aquatonic_course_booking' ) ) {
			$this->booking_queries ++;
		}
		$result = $this->db->query( $sql );
		if ( false === $result ) {
			throw new RuntimeException( 'SQL error: ' . $this->db->error . ' — ' . $sql );
		}

		return $result;
	}
}
