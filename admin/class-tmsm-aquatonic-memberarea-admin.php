<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Memberarea
 * @subpackage Tmsm_Aquatonic_Memberarea/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquatonic_Memberarea
 * @subpackage Tmsm_Aquatonic_Memberarea/admin
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Memberarea_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The plugin options.
	 *
	 * @since 		1.0.0
	 * @access 		private
	 * @var 		string 			$options    The plugin options.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		$this->set_options();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquatonic-memberarea-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-memberarea-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Health Check Test
	 *
	 * @param $tests
	 *
	 * @return mixed
	 */
	function test_cron_schedule_exists( $tests ) {
		$tests['direct']['tmsm_aquatonic_memberarea'] = array(
			'label' => __('TMSM Aquatonic Memberarea Cron Schedule Exists', 'tmsm-aquatonic-memberarea'),
			'test'  => 'tmsm_aquatonic_memberarea_test_schedule_exists',
		);
		return $tests;
	}

	/**
	 * Add item to the admin menu.
	 *
	 * @uses add_dashboard_page()
	 * @uses __()
	 *
	 * @return void
	 */
	public function action_admin_menu() {
		$critical_issues = 0;
		$issue_counts    = get_transient( 'health-check-site-status-result' );

		if ( false !== $issue_counts ) {
			$issue_counts = json_decode( $issue_counts );

			$critical_issues = absint( $issue_counts->critical );
		}

		$critical_count = sprintf(
			'<span class="update-plugins count-%d"><span class="update-count">%s</span></span>',
			esc_attr( $critical_issues ),
			sprintf(
				'%d<span class="screen-reader-text"> %s</span>',
				esc_html( $critical_issues ),
				esc_html_x( 'Critical issues', 'Issue counter label for the admin menu', 'health-check' )
			)
		);

		$menu_title = __( 'Site Health' ) . ' '.( ! $issue_counts || $critical_issues < 1 ? '' : $critical_count );

		remove_submenu_page( 'tools.php', 'site-health.php' );

		add_submenu_page(
			'tools.php',
			__( 'Site Health' ),
			$menu_title,
			'view_site_health_checks',
			'site-health.php'
		);
	}


	/**
	 * Register the Settings page.
	 *
	 * @since    1.0.0
	 */
	public function options_page_menu() {
		add_options_page( __('Aquatonic Memberarea', 'tmsm-aquatonic-memberarea'), __('Aquatonic Memberarea', 'tmsm-aquatonic-memberarea'), 'manage_options', $this->plugin_name.'-settings', array($this, 'options_page_display'));

	}

	/**
	 * Plugin Settings Link on plugin page
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	function settings_link( $links ) {
		$setting_link = array(
			'<a href="' . admin_url( 'options-general.php?page='.$this->plugin_name.'-settings' ) . '">'.__('Settings', 'tmsm-aquatonic-memberarea').'</a>',
		);
		return array_merge( $setting_link, $links );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function options_page_display() {
		include_once( 'partials/' . $this->plugin_name . '-admin-options-page.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_tiers( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-tiers.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_webservice( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-webservice.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_timeslots( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-timeslots.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_api( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-api.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_filters( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-filters.php' );
	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @param 		array 		$params 		Array of parameters for the section
	 * @return 		mixed 						The settings section
	 */
	public function section_desc( $params ) {
		include_once( plugin_dir_path( __FILE__ ) . 'partials/'. $this->plugin_name.'-admin-section-desc.php' );
	}

	/**
	 * Registers settings fields with WordPress
	 */
	public function register_fields() {

		add_settings_field(
			'webservicecounturl',
			esc_html__( 'Web Service Count URL', 'tmsm-aquatonic-memberarea' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-webservice',
			array(
				'description' 	=> __( 'URL of the Web Service for Count Method', 'tmsm-aquatonic-memberarea' ),
				'id' => 'webservicecounturl',
			)
		);

		add_settings_field(
			'pageid',
			esc_html__( 'Memberarea Page ID', 'tmsm-aquatonic-memberarea' ),
			array( $this, 'field_text' ),
			$this->plugin_name,
			$this->plugin_name . '-tiers',
			array(
				'description' 	=> __( 'Page ID of the memberarea page', 'tmsm-aquatonic-memberarea' ),
				'id' => 'pageid',
			)
		);
		// Befin 5 tiers
		for ($tier = 1; $tier <= 5; $tier++) {
			add_settings_field(
				"tier{$tier}_value",
				esc_html( sprintf( __( 'Tier %s Value', 'tmsm-aquatonic-memberarea' ), $tier ) ),
				array( $this, 'field_text' ),
				$this->plugin_name,
				$this->plugin_name . '-tiers',
				array(
					//'description' 	=> 'This message displays on the page if no job postings are found.',
					'id' => "tier{$tier}_value",
					//'value' 		=> 'Thank you for your interest! There are no job openings at this time.',
				)
			);

			add_settings_field(
				"tier{$tier}_color",
				esc_html( sprintf( __( 'Tier %s Color', 'tmsm-aquatonic-memberarea' ), $tier ) ),
				array( $this, 'field_text' ),
				$this->plugin_name,
				$this->plugin_name . '-tiers',
				array(
					//'description' 	=> 'This message displays on the page if no job postings are found.',
					'id' => "tier{$tier}_color",
					//'value' 		=> 'Thank you for your interest! There are no job openings at this time.',
					'description' => esc_html__( 'Hexadecimal', 'tmsm-aquatonic-memberarea' ),
				)
			);

			add_settings_field(
				"tier{$tier}_shortdesc",
				esc_html( sprintf( __( 'Tier %s Short Description', 'tmsm-aquatonic-memberarea' ), $tier ) ),
				array( $this, 'field_text' ),
				$this->plugin_name,
				$this->plugin_name . '-tiers',
				array(
					//'description' 	=> 'This message displays on the page if no job postings are found.',
					'id' => "tier{$tier}_shortdesc",
					//'value' 		=> 'Thank you for your interest! There are no job openings at this time.',
				)
			);

			add_settings_field(
				"tier{$tier}_longdesc",
				esc_html( sprintf( __( 'Tier %s Long Description', 'tmsm-aquatonic-memberarea' ), $tier ) ),
				array( $this, 'field_textarea' ),
				$this->plugin_name,
				$this->plugin_name . '-tiers',
				array(
					'id' => "tier{$tier}_longdesc",
					'description' => esc_html__( 'Html accepted.', 'tmsm-aquatonic-memberarea' ),
				)
			);
		}
		// End 5 tiers

		add_settings_field(
			'timeslots',
			esc_html( __( 'Timeslots', 'tmsm-aquatonic-memberarea' )),
			array( $this, 'field_textarea' ),
			$this->plugin_name,
			$this->plugin_name . '-timeslots',
			array(
				'id' => 'timeslots',
				'description' => esc_html__( 'Format: Day Number=09:00-14:00,15:30-17:30 serapated by a line break. Day Number is: 0 for Sunday, 1 for Monday, etc.', 'tmsm-aquatonic-memberarea' ),
			)
		);



	}

	/**
	 * Registers settings sections with WordPress
	 */
	public function register_sections() {

		add_settings_section(
			$this->plugin_name . '-webservice',
			esc_html__( 'Web Service', 'tmsm-aquatonic-memberarea' ),
			array( $this, 'section_webservice' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-tiers',
			esc_html__( 'Tiers', 'tmsm-aquatonic-memberarea' ),
			array( $this, 'section_tiers' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->plugin_name . '-timeslots',
			esc_html__( 'Time Slots', 'tmsm-aquatonic-memberarea' ),
			array( $this, 'section_timeslots' ),
			$this->plugin_name
		);


	}

	/**
	 * Registers plugin settings
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function register_settings() {
		// register_setting( $option_group, $option_name, $sanitize_callback );
		register_setting(
			$this->plugin_name . '-options',
			$this->plugin_name . '-options',
			array( $this, 'validate_options' )
		);
	}

	/**
	 * Sanitize fields
	 *
	 * @param $type
	 * @param $data
	 *
	 * @return string|void
	 */
	private function sanitizer( $type, $data ) {
		if ( empty( $type ) ) { return; }
		if ( empty( $data ) ) { return; }
		$return 	= '';
		$sanitizer 	= new Tmsm_Aquatonic_Memberarea_Sanitize();
		$sanitizer->set_data( $data );
		$sanitizer->set_type( $type );
		$return = $sanitizer->clean();
		unset( $sanitizer );
		return $return;
	}

	/**
	 * Sets the class variable $options
	 */
	private function set_options() {
		$this->options = get_option( $this->plugin_name . '-options' );
	}

	/**
	 * Validates saved options
	 *
	 * @since 		1.0.0
	 * @param 		array 		$input 			array of submitted plugin options
	 * @return 		array 						array of validated plugin options
	 */
	public function validate_options( $input ) {
		//wp_die( print_r( $input ) );
		$valid 		= array();
		$options 	= $this->get_options_list();
		foreach ( $options as $option ) {
			$name = $option[0];
			$type = $option[1];

			$valid[$option[0]] = $this->sanitizer( $type, $input[$name] );

		}
		return $valid;
	}

	/**
	 * Creates a checkbox field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_checkbox( $args ) {
		$defaults['class'] 			= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['value'] 			= 0;
		apply_filters( $this->plugin_name . '-field-checkbox-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-checkbox.php' );
	}

	/**
	 * Creates an editor field
	 *
	 * NOTE: ID must only be lowercase letter, no spaces, dashes, or underscores.
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_editor( $args ) {
		$defaults['description'] 	= '';
		$defaults['settings'] 		= array( 'textarea_name' => $this->plugin_name . '-options[' . $args['id'] . ']' );
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-editor-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-editor.php' );
	}

	/**
	 * Creates a set of radios field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_radios( $args ) {
		$defaults['class'] 			= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['value'] 			= 0;
		apply_filters( $this->plugin_name . '-field-radios-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-radios.php' );
	}

	public function field_repeater( $args ) {
		$defaults['class'] 			= 'repeater';
		$defaults['fields'] 		= array();
		$defaults['id'] 			= '';
		$defaults['label-add'] 		= 'Add Item';
		$defaults['label-edit'] 	= 'Edit Item';
		$defaults['label-header'] 	= 'Item Name';
		$defaults['label-remove'] 	= 'Remove Item';
		$defaults['title-field'] 	= '';
		/*
				$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		*/
		apply_filters( $this->plugin_name . '-field-repeater-options-defaults', $defaults );
		$setatts 	= wp_parse_args( $args, $defaults );
		$count 		= 1;
		$repeater 	= array();
		if ( ! empty( $this->options[$setatts['id']] ) ) {
			$repeater = maybe_unserialize( $this->options[$setatts['id']][0] );
		}
		if ( ! empty( $repeater ) ) {
			$count = count( $repeater );
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-repeater.php' );
	}

	/**
	 * Creates a select field
	 *
	 * Note: label is blank since its created in the Settings API
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_select( $args ) {
		$defaults['aria'] 			= '';
		$defaults['blank'] 			= '';
		$defaults['class'] 			= 'widefat';
		$defaults['context'] 		= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['selections'] 	= array();
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-select-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		if ( empty( $atts['aria'] ) && ! empty( $atts['description'] ) ) {
			$atts['aria'] = $atts['description'];
		} elseif ( empty( $atts['aria'] ) && ! empty( $atts['label'] ) ) {
			$atts['aria'] = $atts['label'];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-select.php' );
	}

	/**
	 * Creates a text field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_text( $args ) {
		$defaults['class'] 			= 'regular-text';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['placeholder'] 	= '';
		$defaults['type'] 			= 'text';
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-text-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}

		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-text.php' );
	}

	/**
	 * Creates a textarea field
	 *
	 * @param 	array 		$args 			The arguments for the field
	 * @return 	string 						The HTML field
	 */
	public function field_textarea( $args ) {
		$defaults['class'] 			= 'large-text';
		$defaults['cols'] 			= 50;
		$defaults['context'] 		= '';
		$defaults['description'] 	= '';
		$defaults['label'] 			= '';
		$defaults['name'] 			= $this->plugin_name . '-options[' . $args['id'] . ']';
		$defaults['rows'] 			= 10;
		$defaults['value'] 			= '';
		apply_filters( $this->plugin_name . '-field-textarea-options-defaults', $defaults );
		$atts = wp_parse_args( $args, $defaults );
		if ( ! empty( $this->options[$atts['id']] ) ) {
			$atts['value'] = $this->options[$atts['id']];
		}
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-admin-field-textarea.php' );
	}

	/**
	 * Returns an array of options names, fields types, and default values
	 *
	 * @return 		array 			An array of options
	 */
	public static function get_options_list() {
		$options   = array();

		$options[] = array( 'webservicecounturl', 'text', '' );
		$options[] = array( 'pageid', 'text', '' );
		$options[] = array( 'timeslots', 'textarea', '' );
		for ($tier = 1; $tier <= 5; $tier++) {
			$options[] = array( "tier${tier}_value", 'text', '' );
			$options[] = array( "tier${tier}_color", 'text', '' );
			$options[] = array( "tier${tier}_shortdesc", 'text', '' );
			$options[] = array( "tier${tier}_longdesc", 'textarea', '' );
		}

		return $options;
	}

}

/**
 * Health Check Test
 * @return array
 */
function tmsm_aquatonic_memberarea_test_schedule_exists() {
	$result = array(
		'label'       => __('TMSM Aquatonic Memberarea Cron Schedule Exists', 'tmsm-aquatonic-memberarea'),
		'status'      => 'good',
		'badge'       => array(
			'label' => __( 'Performance' ),
			'color' => 'green',
		),
		'description' => sprintf(
			'<p>%s</p>',
			__( 'Cron Schedule is not fired', 'tmsm-aquatonic-memberarea' )
		),
		'actions'     => '',
		'test'        => 'tmsm-aquatonic-memberarea',
	);

	if ( ! wp_next_scheduled( 'tmsm_aquatonic_memberarea_cronaction' ) ) {
		$result['status'] = 'critical';
		$result['badge']['color'] = 'red';
		$result['label'] = __('TMSM Aquatonic Memberarea Cron Schedule is not added', 'tmsm-aquatonic-memberarea');
		$result['description'] = sprintf(
			'<p>%s</p>',
			__('Cron schedule is not fired, prices will not update from Aquatonic Memberarea.', 'tmsm-aquatonic-memberarea')
		);
		$result['actions'] .= sprintf(
			'<p><a href="%s">%s</a></p>',
			esc_url( admin_url( 'plugins.php' ) ),
			__( 'Disable and enable again the plugin', 'tmsm-aquatonic-memberarea' )
		);
	}

	return $result;
}