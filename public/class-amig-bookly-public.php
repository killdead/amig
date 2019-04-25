<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AmigBookly
 * @subpackage AmigBookly/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    AmigBookly
 * @subpackage AmigBookly/public
 * @author     Your Name <email@example.com>
 */
class AmigBookly_Public {

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
	 * @param      string    $AmigBookly       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $AmigBookly, $version ) {

		$this->AmigBookly = $AmigBookly;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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


		wp_enqueue_style( $this->AmigBookly.'-fancy', plugin_dir_url( __FILE__ ) . 'css/jquery.fancybox.css', array('bookly-main'),	$this->version, 'all' );
		wp_enqueue_style( $this->AmigBookly, plugin_dir_url( __FILE__ ) . 'css/amig-bookly-public.css', array($this->AmigBookly.'-fancy'),$this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->AmigBookly, plugin_dir_url( __FILE__ ) . 'js/amig-bookly-public.js', array( 'jquery' ),	$this->version, true);
		wp_enqueue_script( $this->AmigBookly.'-fancy', plugin_dir_url( __FILE__ ) . 'js/jquery.fancybox.js', array( 'jquery'), $this->version, true);
		wp_enqueue_script( $this->AmigBookly.'-spin', plugin_dir_url( __FILE__ ) . 'js/spin.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script( $this->AmigBookly.'-ext', plugin_dir_url( __FILE__ ) . 'js/amig-bookly-ext.js', array('bookly'), $this->version, true);
		

}

}
