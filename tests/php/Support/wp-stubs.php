<?php
/**
 * Cales minimales des fonctions WordPress utilisées par le calcul des créneaux.
 * Les options et l'utilisateur courant sont pilotés par les tests via $GLOBALS.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wp/' );
}
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}
if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

class Tmsm_Test_User {
	public $ID = 0;
	public $roles = [];

	public function __construct( int $id = 0, array $roles = [] ) {
		$this->ID    = $id;
		$this->roles = $roles;
	}

	public function exists(): bool {
		return $this->ID > 0;
	}
}

function __( $text, $domain = 'default' ) {
	return $text;
}

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES );
}

function sanitize_text_field( $text ) {
	return trim( strip_tags( (string) $text ) );
}

function absint( $value ) {
	return abs( (int) $value );
}

function is_wp_error( $thing ) {
	return false;
}

function wp_timezone() {
	return new DateTimeZone( 'Europe/Paris' );
}

function get_option( $name, $default = false ) {
	return $GLOBALS['tmsm_test_options'][ $name ] ?? $default;
}

function wp_get_current_user() {
	return $GLOBALS['tmsm_test_user'] ?? new Tmsm_Test_User();
}

function get_userdata( $id ) {
	return false;
}
