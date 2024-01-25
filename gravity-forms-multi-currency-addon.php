<?php
/**
 * Gravity Forms add-on implementation.
 *
 * @package gravity-forms-multi-currency
 */

defined( 'ABSPATH' ) || exit;

GFForms::include_feed_addon_framework();

/**
 * Gravity_Forms_Multi_Currency_Addon
 */
class Gravity_Forms_Multi_Currency_Addon extends GFAddOn {
	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since 1.0
	 * @var   GF_HubSpot_Deal_Creator $_instance If available, contains an instance of this class
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Gravity Forms HubSpot Add-On.
	 *
	 * @since 1.0
	 * @var   string $_version Contains the version.
	 */
	protected $_version = GF_MC_VERSION;

	/**
	 * Defines the plugin slug.
	 *
	 * @since 1.0
	 * @var   string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravity-forms-multi-currency';

	/**
	 * Defines the main plugin file.
	 *
	 * @since 1.0
	 * @var   string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravity-forms-multi-currency/gravity-forms-multi-currency.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since 1.0
	 * @var   string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the title of this add-on.
	 *
	 * @since 1.0
	 * @var   string $_title The title of the add-on.
	 */
	protected $_title = 'Gravity Forms Multi Currency';

	/**
	 * Defines the short title of the add-on.
	 *
	 * @since 1.0
	 * @var   string $_short_title The short title.
	 */
	protected $_short_title = 'Multi Currency';

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @since 1.0
	 *
	 * @return Gravity_Forms_Multi_Currency_Addon $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new Gravity_Forms_Multi_Currency_Addon();
		}

		return self::$_instance;
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function scripts() {
		$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
		$scripts = array(
			array(
				'handle'  => 'gravity-forms-multi-currency',
				'src'     => plugins_url( "form-settings{$min}.js", GF_MC_MAINFILE ),
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array(
						'query' => 'page=gf_edit_forms&view=settings&id=_notempty_',
					),
				),
			),
		);
		return array_merge( parent::scripts(), $scripts );
	}
}
