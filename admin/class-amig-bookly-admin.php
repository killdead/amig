<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AmigBookly
 * @subpackage AmigBookly/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    AmigBookly
 * @subpackage AmigBookly/admin
 * @author     Your Name <email@example.com>
 */
class AmigBookly_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $AmigBookly    The ID of this plugin.
	 */
	private $AmigBookly;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $AmigBookly       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $AmigBookly, $version ) {

		$this->AmigBookly = $AmigBookly;
		$this->version = $version;
		
		add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
		//add_action( 'wp_loaded',  array( $this, 'init' ) );
	}
	
	public function addAdminMenu() {
		add_menu_page( 'Amazeingame Bookly', 'Amazeingame', 'read', 'amig-bookly-settings', array($this,'amigBooklySettings'),
			'dashicons-calendar-alt', '10.001');
	}

	public function amigBooklySettings() {
		include 'partials/amig-bookly-admin-display.php';
	}
	
	public function init() {
		
		// Load the theme/plugin options
		/*if ( file_exists( dirname( __FILE__ ) . '/partials/amig-bookly-admin-display.php' ) ) {
			require_once dirname( __FILE__ ) . '/partials/amig-bookly-admin-display.php';
		}

		// Load Redux extensions
		if ( file_exists( dirname( __FILE__ ) . '/redux-extensions/extensions-init.php' ) ) {
			require_once dirname( __FILE__ ) . '/redux-extensions/extensions-init.php';
		}*/
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AmigBookly_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AmigBookly_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->AmigBookly.'-boostrap', plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array
		(),
			$this->version, 'all' );
		wp_enqueue_style( $this->AmigBookly, plugin_dir_url( __FILE__ ) . 'css/amig-bookly-admin.css', array(),
			$this->version,	'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in AmigBookly_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The AmigBookly_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		/*wp_enqueue_script( $this->AmigBookly.'-boostrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap.js', array
		('jquery'), $this->version, false );*/
		wp_enqueue_script( $this->AmigBookly, plugin_dir_url( __FILE__ ) . 'js/amig-bookly-admin.js', array(
			'jquery'	), $this->version, false );

	}

}
