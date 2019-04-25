<?php

/**
 * Register all actions and filters for the plugin
 *
 * @since      1.0.0
 *
 * @package    AmigBookly
 * @subpackage AmigBookly/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    AmigBookly
 * @subpackage AmigBookly/includes
 * @author     Your Name <email@example.com>
 */
class AmigBookly_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Surcharge du controller avec celui de Amazeingame
	 *
	 * @var
	 */
	protected $bookingController;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();
		
		/*$path = plugin_dir_path( __FILE__ ). 'wcfm/controllers/';
		
		require_once $path . 'class-AmigBookly-wcfm-controller-agences.php';
		
		require_once $path . 'class-AmigBookly-wcfm-controller-agences-manage.php';*/

		$this->add_action('plugins_loaded', $this, 'amigbookly_loaded');
		
	}
	
	function stop_plugin_update( $value ) {
		unset( $value->response['wc_frontend_manager/wc_frontend_manager.php'] );
		return $value;
	}
	
	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}
	
	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}
	
	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);
		
		return $hooks;
		
	}
	
	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		
		
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
		
		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	
	}


	function amigbookly_loaded() {

		/** OVERRIDING Bookly Class Finder and Frontend Controller */
		require_once  plugin_dir_path( __FILE__ ).'frontend/modules/booking/class-amig-bookly-frontcontroller.php';
		require_once  plugin_dir_path( __FILE__ ).'frontend/modules/booking/class-amig-bookly-frontcontroller.php';
		require_once  plugin_dir_path( __FILE__ ).'libs/Updater.php';
		require_once  plugin_dir_path( __FILE__ ).'libs/Plugin.php';
		require_once  plugin_dir_path( __FILE__ ).'libs/class-amig-bookly-finder.php';
		require_once  plugin_dir_path( __FILE__ ).'libs/class-amig-bookly-generator.php';
		$this->bookingController         = \AmigBookly\Frontend\Modules\Booking\Controller::getInstance();

		remove_shortcode( 'bookly-form' );
		add_shortcode( 'bookly-form', array( $this->bookingController, 'renderShortCode' ) );



		//add_action('wp_ajax_bookly_render_time', array( $this->bookingController, 'executeRenderTime' ),999 );
	}


	
	function return__false() {
		return false;
	}
	function return__true() {
		return true;
	}
	
	function return__emptyarray() {
		return array();
	}
	
	function return__emptystring() {
		return '';
	}
}
