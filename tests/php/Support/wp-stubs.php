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

/**
 * Champ de formulaire GravityForms factice : lisible en tableau ($field['cssClass'])
 * comme en objet ($field->cssClass), comme un vrai GF_Field.
 */
class Tmsm_Test_Gf_Field implements ArrayAccess {
	public $id;
	public $cssClass;
	public $inputs = null;

	public function __construct( int $id, string $css_class ) {
		$this->id       = $id;
		$this->cssClass = $css_class;
	}

	public function offsetExists( $offset ): bool {
		return isset( $this->$offset );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->$offset ?? null;
	}

	public function offsetSet( $offset, $value ): void {
		$this->$offset = $value;
	}

	public function offsetUnset( $offset ): void {
		$this->$offset = null;
	}
}

/** Lit une valeur soumise, comme la fonction rgpost() de GravityForms. */
function rgpost( $name ) {
	return $_POST[ $name ] ?? null;
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
