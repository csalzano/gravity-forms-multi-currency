<?php
/**
 * Plugin Name: Gravity Forms Multi Currency
 * Plugin URI: https://github.com/ilanco/gravity-forms-multi-currency
 * Description: Per form currency for Gravity Forms.
 * Version: 1.7.1
 * Author: Ilan Cohen <ilanco@gmail.com>
 * Author URI: https://github.com/ilanco
 * Text Domain: gravity-forms-multi-currency
 *
 * @package gravity-forms-multi-currency
 */

defined( 'ABSPATH' ) || exit;

define( 'GF_MC_VERSION', '1.7.1' );

define( 'GF_MC_MAINFILE', __FILE__ );

add_action( 'init', array( 'GFMultiCurrency', 'init' ), 9 );
// Register an add-on.
add_action( 'gform_loaded', array( 'GFMultiCurrency', 'register_addon' ), 5 );

/**
 * GFMultiCurrency
 */
class GFMultiCurrency {

	private static $instance;

	private $currency;

	/**
	 * __construct
	 *
	 * @return void
	 */
	private function __construct() {
		if ( ! $this->is_gravityforms_supported() ) {
			return;
		}

		add_action( 'wp', array( &$this, 'form_process' ), 8 );
		add_filter( 'gform_currency', array( &$this, 'form_currency' ) );

		if ( is_admin() ) {
			add_action( 'gform_admin_pre_render', array( &$this, 'admin_pre_render' ) );
			add_filter( 'gform_pre_form_settings_save', array( &$this, 'save_custom_form_settings' ) );
			add_action( 'gform_entry_detail_content_before', array( &$this, 'admin_entry_detail' ) );

			// Add our currency selector setting to each form.
			add_filter( 'gform_form_settings_fields', array( $this, 'add_settings_field' ), 10, 2 );
		} else {
			add_filter( 'gform_pre_render', array( &$this, 'pre_render' ) );
		}
	}

	/**
	 * register_addon
	 *
	 * @return void
	 */
	public static function register_addon() {
		// Registers the class name with GFAddOn.
		require_once dirname( GF_MC_MAINFILE ) . '/gravity-forms-multi-currency-addon.php';
		GFAddOn::register( 'Gravity_Forms_Multi_Currency_Addon' );
	}

	/**
	 * Adds the Default Currency setting to each form.
	 *
	 * @see https://docs.gravityforms.com/gform_form_settings_fields/
	 *
	 * @param array $fields Form Settings fields.
	 * @param array $form   The current form.
	 *
	 * @return array
	 */
	public function add_settings_field( $fields, $form = array() ) {
		$field = array(
			'name'          => 'gform_setting_currency',
			'type'          => 'select',
			'label'         => __( 'Currency', 'gravity-forms-multi-currency' ),
			'tooltip'       => __( 'Change the currency for this form.', 'gravity-forms-multi-currency' ),
			'default_value' => $this->gf_get_default_currency(),
			'choices'       => array(),
			'value'         => $form['currency'],
		);

		foreach ( RGCurrency::get_currencies() as $code => $currency ) {
			$field['choices'][] = array(
				'label' => sprintf( '%1$s (%2$s)', $currency['name'], $code ),
				'value' => $code,
			);
		}
		$fields['form_options']['fields'][] = $field;
		return $fields;
	}

	/**
	 * Maintains and returns an instance of this class.
	 *
	 * @return GFMultiCurrency
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new GFMultiCurrency();
		}

		return self::$instance;
	}

	/**
	 * Loads a form's currency setting into this class.
	 *
	 * @return void
	 */
	public function form_process() {
		$form_id = isset( $_POST['gform_submit'] ) ? $_POST['gform_submit'] : 0;
		if ( $form_id ) {
			$form_info     = GFAPI::get_form( $form_id );
			$is_valid_form = $form_info && $form_info->is_active;

			if ( $is_valid_form ) {
				$form = GFAPI::get_form( $form_id );
				if ( isset( $form['currency'] ) && $form['currency'] ) {
					$this->currency = $form['currency'];
				}
			}
		}
	}

	/**
	 * Changes the global currency to the one we stashed for this form earlier.
	 *
	 * @param  string $currency An ISO currency code.
	 * @return string
	 */
	public function form_currency( $currency ) {
		if ( $this->currency ) {
			$currency = $this->currency;
		}

		return $currency;
	}

	/**
	 * Loads a form's currency setting into this class while in the dashboard.
	 *
	 * @param  array $form Form meta array.
	 * @return array
	 */
	public function admin_pre_render( $form ) {
		if ( isset( $form['currency'] ) && $form['currency'] ) {
			$this->currency = $form['currency'];
		}

		return $form;
	}

	/**
	 * Saves a currency code with a form meta array.
	 *
	 * @param  array $form Form meta array.
	 * @return array
	 */
	public function save_custom_form_settings( $form ) {
		$form['currency'] = rgpost( 'form_currency' );

		return $form;
	}

	/**
	 * Loads a form's currency setting into this class when an entry is viewed
	 * in the dashboard.
	 *
	 * @param  array $form Form meta array.
	 * @return array
	 */
	public function admin_entry_detail( $form ) {
		if ( isset( $form['currency'] ) && $form['currency'] ) {
			$this->currency = $form['currency'];
		}

		return $form;
	}

	/**
	 * Loads a form's currency setting into this class for the front-end.
	 *
	 * @param  array $form Form meta array.
	 * @return array
	 */
	public function pre_render( $form ) {
		if ( isset( $form['currency'] ) && $form['currency'] ) {
			$this->currency = $form['currency'];
		}

		return $form;
	}

	/**
	 * Returns the global currency setting stored in the rg_gforms_currency
	 * option.
	 *
	 * @return string
	 */
	protected function gf_get_default_currency() {
		$currency = get_option( 'rg_gforms_currency' );
		$currency = empty( $currency ) ? 'USD' : $currency;

		return $currency;
	}

	/**
	 * True or false, Gravity Forms is running.
	 *
	 * @return bool
	 */
	private function is_gravityforms_supported() {
		return class_exists( 'GFCommon' );
	}

	/**
	 * Stores a currency code and form ID pair.
	 *
	 * @param  int    $form_id Form ID.
	 * @param  string $currency ISO currency code.
	 * @return void
	 */
	private function set_currency( $form_id, $currency ) {
		$this->currency[ $form_id ] = $currency;
	}

	/**
	 * Retrieves a currency code provided the form ID.
	 *
	 * @param  int $form_id Form ID.
	 * @return string
	 */
	private function get_currency( $form_id ) {
		return $this->currency[ $form_id ];
	}
}

