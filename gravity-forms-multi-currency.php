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
			add_filter( 'gform_form_settings', array( &$this, 'custom_form_settings' ) );
			add_filter( 'gform_pre_form_settings_save', array( &$this, 'save_custom_form_settings' ) );
			add_action( 'gform_editor_js', array( &$this, 'admin_editor_js' ) );

			add_action( 'gform_entry_detail_content_before', array( &$this, 'admin_entry_detail' ) );
		} else {
			add_filter( 'gform_pre_render', array( &$this, 'pre_render' ) );
		}
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
			$form_info     = RGFormsModel::get_form( $form_id );
			$is_valid_form = $form_info && $form_info->is_active;

			if ( $is_valid_form ) {
				$form = RGFormsModel::get_form_meta( $form_id );
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
	 * Outputs the setting controls we add to each form.
	 *
	 * @param  array $settings Array of form settings.
	 * @return array
	 */
	public function custom_form_settings( $settings ) {
		ob_start();
		include 'tpl/custom_form_settings.php';
		$settings['Form Basics']['form_currency_setting'] = ob_get_contents();
		ob_end_clean();

		return $settings;
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
	 * Outputs an inline script in the form editor to update our custom setting.
	 *
	 * @return void
	 */
	public function admin_editor_js() {
		?>
		<script type='text/javascript'>
		jQuery(function($) {
			$("#form_currency").change(function() {
				form.currency = this.value;
			});
			$("#form_currency").val(form.currency);
		});
		</script>
		<?php
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

