<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Memberarea
 * @subpackage Tmsm_Aquatonic_Memberarea/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tmsm_Aquatonic_Memberarea
 * @subpackage Tmsm_Aquatonic_Memberarea/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Memberarea {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Tmsm_Aquatonic_Memberarea_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TMSM_AQUATONIC_MEMBERAREA_VERSION' ) ) {
			$this->version = TMSM_AQUATONIC_MEMBERAREA_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tmsm-aquatonic-memberarea';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tmsm_Aquatonic_Memberarea_Loader. Orchestrates the hooks of the plugin.
	 * - Tmsm_Aquatonic_Memberarea_i18n. Defines internationalization functionality.
	 * - Tmsm_Aquatonic_Memberarea_Admin. Defines all hooks for the admin area.
	 * - Tmsm_Aquatonic_Memberarea_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * ResaCours API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/ResaCours_API.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquatonic-memberarea-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquatonic-memberarea-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tmsm-aquatonic-memberarea-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tmsm-aquatonic-memberarea-public.php';

		/**
		 * The class responsible for sanitizing user input
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tmsm-aquatonic-memberarea-sanitize.php';

		$this->loader = new Tmsm_Aquatonic_Memberarea_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tmsm_Aquatonic_Memberarea_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tmsm_Aquatonic_Memberarea_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tmsm_Aquatonic_Memberarea_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Settings
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_sections' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_fields' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'options_page_menu' );
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_'.$plugin_basename, $plugin_admin, 'settings_link' );

	}



	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tmsm_Aquatonic_Memberarea_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );

		$this->loader->add_action( 'wp_ajax_tmsm-aquatonic-memberarea-realtime', $plugin_public, 'ajax_realtime' );
		$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquatonic-memberarea-realtime', $plugin_public, 'ajax_realtime' );


		$this->loader->add_filter( 'authenticate', $plugin_public, 'authenticate', 100, 3 );
		$this->loader->add_filter( 'login_form_bottom', $plugin_public, 'login_form_bottom', 100, 2 );

		// Ajax
		//$this->loader->add_action( 'wp_ajax_tmsm-aquatonic-memberarea-calculatetotal', $plugin_public, 'ajax_calculate_totalprice' );
		//$this->loader->add_action( 'wp_ajax_nopriv_tmsm-aquatonic-memberarea-calculatetotal', $plugin_public, 'ajax_calculate_totalprice' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Tmsm_Aquatonic_Memberarea_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
