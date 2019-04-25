<?php
namespace AmigBookly\Frontend\Modules\Booking;

use Bookly\Lib;
use Bookly\Frontend\Modules\Booking\Lib\Errors;
use Bookly\Frontend\Modules\Booking\Lib\Steps;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\Booking
 */
class Controller extends \Bookly\Frontend\Modules\Booking\Controller
{

	public function __construct()
	{

		parent::__construct();

	}

		


	protected function registerWpAjaxActions( $with_nopriv = true )
	{
		if ( defined( 'DOING_AJAX' ) ) {
			////print_r('----'.get_called_class().'<br>');
			$plugin_class = Lib\Base\Plugin::getPluginFor( $this );
			////print_r("$plugin_class<br>");
			// Prefixes for auto generated add_action() $tag parameter.
			$prefix = sprintf( 'wp_ajax_%s', $plugin_class::getPrefix() );
			if ( $with_nopriv ) {
				$nopriv_prefix = sprintf( 'wp_ajax_nopriv_%s', $plugin_class::getPrefix() );
			}

			//print_r("$with_nopriv ".$prefix.' and '.$nopriv_prefix.'<br>');

			foreach ( $this->reflection->getMethods( \ReflectionMethod::IS_PUBLIC ) as $method ) {
				if ( preg_match( '/^execute(.*)/', $method->name, $match ) && $this->reflection->getName() == $method->class) {
					$action   = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $match[1] ) );

				//	print_r($prefix.$action.' => '.$match[0].'<br>');
					remove_all_actions($prefix.$action);
					add_action( $prefix . $action, array($this,$match[0]),1 );
					if ( $with_nopriv ) {
						//print_r($nopriv_prefix.$action.' => '.$match[0].'<br>');
						remove_all_actions($nopriv_prefix . $action);
						add_action( $nopriv_prefix . $action, array($this,$match[0]),1 );
					}
				}
			}
		}
	}

	/**
	 * Render Bookly shortcode.
	 *
	 * @param $attributes
	 * @return string
	 */
	public function renderShortCode( $attributes )
	{

		global $sitepress;

		// Disable caching.
		Lib\Utils\Common::noCache();

		$assets = '';

		if ( get_option( 'bookly_gen_link_assets_method' ) == 'print' ) {
			$print_assets = ! wp_script_is( 'bookly', 'done' );
			if ( $print_assets ) {
				ob_start();

				// The styles and scripts are registered in Frontend.php
				wp_print_styles( 'bookly-intlTelInput' );
				wp_print_styles( 'bookly-ladda-min' );
				wp_print_styles( 'bookly-picker' );
				wp_print_styles( 'bookly-picker-date' );
				wp_print_styles( 'bookly-main' );

				wp_print_scripts( 'bookly-spin' );
				wp_print_scripts( 'bookly-ladda' );
				wp_print_scripts( 'bookly-picker' );
				wp_print_scripts( 'bookly-picker-date' );
				wp_print_scripts( 'bookly-hammer' );
				wp_print_scripts( 'bookly-jq-hammer' );
				wp_print_scripts( 'bookly-intlTelInput' );
				// Android animation.
				if ( stripos( strtolower( $_SERVER['HTTP_USER_AGENT'] ), 'android' ) !== false ) {
					wp_print_scripts( 'bookly-jquery-animate-enhanced' );
				}
				Lib\Proxy\Shared::printBookingAssets();
				wp_print_scripts( 'bookly' );

				$assets = ob_get_clean();
			}
		} else {
			$print_assets = true; // to print CSS in template.
		}

		// Generate unique form id.
		$form_id = uniqid();

		// Find bookings with any of payment statuses ( PayPal, 2Checkout, PayU Latam ).
		$status = array( 'booking' => 'new' );
		foreach ( Lib\Session::getAllFormsData() as $saved_form_id => $data ) {
			if ( isset ( $data['payment'] ) ) {
				if ( ! isset ( $data['payment']['processed'] ) ) {
					switch ( $data['payment']['status'] ) {
						case 'success':
						case 'processing':
							$form_id = $saved_form_id;
							$status = array( 'booking' => 'finished' );
							break;
						case 'cancelled':
						case 'error':
							$form_id = $saved_form_id;
							end( $data['cart'] );
							$status = array( 'booking' => 'cancelled', 'cart_key' => key( $data['cart'] ) );
							break;
					}
					// Mark this form as processed for cases when there are more than 1 booking form on the page.
					$data['payment']['processed'] = true;
					Lib\Session::setFormVar( $saved_form_id, 'payment', $data['payment'] );
				}
			} elseif ( $data['last_touched'] + 30 * MINUTE_IN_SECONDS < time() ) {
				// Destroy forms older than 30 min.
				Lib\Session::destroyFormData( $saved_form_id );
			}
		}

		// Handle shortcode attributes.
		$hide_date_and_time = (bool) @$attributes['hide_date_and_time'];
		$fields_to_hide = isset ( $attributes['hide'] ) ? explode( ',', $attributes['hide'] ) : array();
		$staff_member_id = (int) ( @$_GET['staff_id'] ?: @$attributes['staff_member_id'] );
		$salle1 = get_option('amig-bookly-settings-salle1');
		$salle2 = get_option('amig-bookly-settings-salle2');
        $salles = array($salle1, $salle2);
		$casest = Lib\Config::getCaSeSt();
		$services = array();
		$num = 0;
        foreach ($casest['services'] as $id => $service) {
        	if(in_array($service['category_id'],$salles )) {
        		$services[++$num] = $id;
	        }
        }

		$query = \Bookly\Lib\Entities\Service::query( 's' )
		                                     ->select( 's.id, s.category_id, s.title, s.position, s.duration, s.price, s.capacity_min, cat.name' )
		                                     ->innerJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
		                                     ->innerJoin( 'Category', 'cat', 'cat.id = s.category_id' )
		                                     ->where( 's.type',  \Bookly\Lib\Entities\Service::TYPE_SIMPLE )
		                                     ->where( 's.visibility', \Bookly\Lib\Entities\Service::VISIBILITY_PUBLIC )
		                                     ->groupBy( 's.id' );

		$query = \Bookly\Lib\Proxy\CustomerGroups::prepareCaSeStQuery( $query );

		foreach ( $query->fetchArray() as $row ) {
			$casest['services'][ $row['id'] ] = array(
				'id'          => (int) $row['id'],
				'category_id' => (int) $row['category_id'],
				'category_name' => (int) $row['name'],
				'name'        => $row['title'] == ''
					? __( 'Untitled', 'bookly' )
					: \Bookly\Lib\Utils\Common::getTranslatedString( 'service_' . $row['id'], $row['title'] ),
				'min_capacity' => (int) $row['capacity_min'],
				'price' =>  $row['price'], //html_entity_decode( \Bookly\Lib\Utils\Price::format( $row['price'] )) ,
				'pos'          => (int) $row['position'],
			);
		}
        
		$attrs = array(
			'location_id'            => (int) ( @$_GET['loc_id']     ?: @$attributes['location_id'] ),
			'category_id'            => (int) ( @$_GET['cat_id']     ?: @$attributes['category_id'] ),
			'service_id'             => $services,
			'staff_member_id'        => $staff_member_id,
			'hide_categories'        => in_array( 'categories',      $fields_to_hide ) ? true : (bool) @$attributes['hide_categories'],
			'hide_services'          => in_array( 'services',        $fields_to_hide ) ? true : (bool) @$attributes['hide_services'],
			'hide_staff_members'     => ( in_array( 'staff_members', $fields_to_hide ) ? true : (bool) @$attributes['hide_staff_members'] )
				&& ( get_option( 'bookly_app_required_employee' ) ? $staff_member_id : true ),
			'hide_date'              => $hide_date_and_time ? true : in_array( 'date',       $fields_to_hide ),
			'hide_week_days'         => $hide_date_and_time ? true : in_array( 'week_days',  $fields_to_hide ),
			'hide_time_range'        => $hide_date_and_time ? true : in_array( 'time_range', $fields_to_hide ),
			'show_number_of_persons' => (bool) @$attributes['show_number_of_persons'],
			'show_service_duration'  => (bool) get_option( 'bookly_app_service_name_with_duration' ),
			// Add-ons.
			'hide_locations'         => true,
			'hide_quantity'          => true,
		);


		// Set service step attributes for Add-ons.
		if ( Lib\Config::locationsEnabled() ) {
			$attrs['hide_locations'] = in_array( 'locations', $fields_to_hide );
		}
		if ( Lib\Config::multiplyAppointmentsEnabled() ) {
			$attrs['hide_quantity']  = in_array( 'quantity',  $fields_to_hide );
		}

		$service_part1 = (
			! $attrs['show_number_of_persons'] &&
			$attrs['hide_categories'] &&
			$attrs['hide_services'] &&
			$attrs['service_id'] &&
			$attrs['hide_staff_members'] &&
			$attrs['hide_locations'] &&
			$attrs['hide_quantity']
		);
		$service_part2 = (
			$attrs['hide_date'] &&
			$attrs['hide_week_days'] &&
			$attrs['hide_time_range']
		);
		if ( $service_part1 && $service_part2 ) {
			// Store attributes in session for later use in Time step.
			Lib\Session::setFormVar( $form_id, 'attrs', $attrs );
			Lib\Session::setFormVar( $form_id, 'last_touched', time() );
		}
		$skip_steps = array(
			'service_part1' => (int) $service_part1,
			'service_part2' => (int) $service_part2,
			'extras' => (int) ( ! Lib\Config::serviceExtrasEnabled() ||
				$service_part1 && ! Lib\Proxy\ServiceExtras::findByServiceId( $attrs['service_id'] ) ),
			'repeat' => (int) ( ! Lib\Config::recurringAppointmentsEnabled() ),
		);
		// Prepare URL for AJAX requests.
		$ajax_url = admin_url( 'admin-ajax.php' );
		// Support WPML.
		if ( $sitepress instanceof \SitePress ) {
			$ajax_url .= ( strpos( $ajax_url, '?' ) ? '&' : '?' ) . 'lang=' . $sitepress->get_current_language();
		}
		$woocommerce_enabled = (int) Lib\Config::wooCommerceEnabled();
		$options = array(
			'intlTelInput' => array( 'enabled' => 0 ),
			'woocommerce'  => array( 'enabled' => $woocommerce_enabled, 'cart_url' => $woocommerce_enabled ? WC()->cart->get_cart_url() : '' ),
			'cart'         => array( 'enabled' => $woocommerce_enabled ? 0 : (int) Lib\Config::showStepCart() ),
		);
		if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) {
			$options['intlTelInput']['enabled'] = 1;
			$options['intlTelInput']['utils']   = is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' );
			$options['intlTelInput']['country'] = get_option( 'bookly_cst_phone_default_country' );
		}
		$required = array(
			'staff' => (int) get_option( 'bookly_app_required_employee' )
		);
		if ( Lib\Config::locationsEnabled() ) {
			$required['location'] = (int) get_option( 'bookly_app_required_location' );
		}


		// Custom CSS.
		$custom_css = get_option( 'bookly_app_custom_styles' );

		$errors = array(
			Errors::SESSION_ERROR               => __( 'Session error.', 'bookly' ),
			Errors::FORM_ID_ERROR               => __( 'Form ID error.', 'bookly' ),
			Errors::CART_ITEM_NOT_AVAILABLE     => Lib\Utils\Common::getTranslatedOption( Lib\Config::showStepCart() ? 'bookly_l10n_step_cart_slot_not_available' : 'bookly_l10n_step_time_slot_not_available' ),
			Errors::PAY_LOCALLY_NOT_AVAILABLE   => __( 'Pay locally is not available.', 'bookly' ),
			Errors::INVALID_GATEWAY             => __( 'Invalid gateway.', 'bookly' ),
			Errors::PAYMENT_ERROR               => __( 'Error.', 'bookly' ),
			Errors::INCORRECT_USERNAME_PASSWORD => __( 'Incorrect username or password.' ),
		);

		$errors = \Bookly\Lib\Proxy\Shared::prepareBookingErrorCodes($errors);
        $services = $casest['services'];
		$categories = $casest['categories'];
		$staffs = $casest['staff'];

		return $assets . $this->render(
			'short_code',
			compact( 'attrs', 'options', 'required', 'print_assets', 'form_id', 'ajax_url', 'status', 'skip_steps',
				'custom_css','errors', 'services', 'categories', 'salle1', 'salle2', 'staffs'),
			false
		);
	}


	/**
	 * 3. Step time.
	 *
	 * response JSON
	 */
	public function executeRenderTime()
	{
		//print_r('***** execute '.get_called_class().' : '.__FUNCTION__.'<br>');
		

		$response = null;
		$userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
		$loaded   = $userData->load();
		$casest = Lib\Config::getCaSeSt();
		$query = \Bookly\Lib\Entities\Service::query( 's' )
		                         ->select( 's.id, s.category_id, s.title, s.position, s.duration, s.price, s.capacity_min, cat.name' )
		                         ->innerJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
								 ->innerJoin( 'Category', 'cat', 'cat.id = s.category_id' )
		                         ->where( 's.type',  \Bookly\Lib\Entities\Service::TYPE_SIMPLE )
		                         ->where( 's.visibility', \Bookly\Lib\Entities\Service::VISIBILITY_PUBLIC )
		                         ->groupBy( 's.id' );
		
		$query = \Bookly\Lib\Proxy\CustomerGroups::prepareCaSeStQuery( $query );
		
		foreach ( $query->fetchArray() as $row ) {
			$casest['services'][ $row['id'] ] = array(
				'id'          => (int) $row['id'],
				'category_id' => (int) $row['category_id'],
				'category_name' => (int) $row['name'],
				'name'        => $row['title'] == ''
					? __( 'Untitled', 'bookly' )
					: \Bookly\Lib\Utils\Common::getTranslatedString( 'service_' . $row['id'], $row['title'] ),
				'min_capacity' => (int) $row['capacity_min'],
				'price' =>  $row['price'], //html_entity_decode( \Bookly\Lib\Utils\Price::format( $row['price'] )) ,
				'pos'          => (int) $row['position'],
			);
		}
		

		$salle1 = get_option('amig-bookly-settings-salle1');
		$salle1_css = $salle1 == "0" ? 'salle1_inactive' : 'salle1_active';
		$salle2 = get_option('amig-bookly-settings-salle2');
		$salle2_css = $salle2 == "0" ? 'salle2_inactive' : 'salle2_active';

		$salles_active = $salle1_css.' '.$salle2_css;
		//var_dump($userData->chain->getItems()[0]);


		if ( ! $loaded && Lib\Session::hasFormVar( $this->getParameter( 'form_id' ), 'attrs' ) ) {
			$loaded = true;
		}

		if ( $loaded ) {

			$this->_handleTimeZone( $userData );

			if ( $this->hasParameter( 'new_chain' ) ) {
				$this->_setDataForSkippedServiceStep( $userData );
			}

			if ( $this->hasParameter( 'edit_cart_item' ) ) {
				$cart_key = $this->getParameter( 'edit_cart_item' );
				$userData
					->setEditCartKeys( array( $cart_key ) )
					->setChainFromCartItem( $cart_key );
			}




   
			
			$finder = new \AmigBookly\Lib\Slots\Finder( $userData );
			
		

			if ( $this->hasParameter( 'selected_date' ) ) {
				$finder->setSelectedDate( $this->getParameter( 'selected_date' ) );
			} else {
				$finder->setSelectedDate( $userData->getDateFrom() );

			}

			//die();
			$finder->prepare()->load();;
			

			//die();
			
			////print_r(' ** render slot<br>');
			$progress_tracker = $this->_prepareProgressTracker( Steps::TIME, $userData );
			$info_text = '';//$this->components->prepareInfoText( Steps::TIME, Lib\Utils\Common::getTranslatedOption(				'bookly_l10n_info_time_step' ), $userData );
			
			// Render slots by groups (day or month).
			$slots = $userData->getSlots();
			
			$selected_date = isset ( $slots[0][2] ) ? $slots[0][2] : null;
			
			$slots = array();
			
			//$salle_groups = array();
//			$nb = 0;
			foreach ( $finder->getSlots() as $group => $group_slots ) {
//				$nb++;
				$salle_groups[$group] = array();
				$slots[ $group ] = preg_replace( '/>\s+</', '><', $this->render( '_time_slots', array(
					'group' => $group,
					'slots' => $group_slots,
					'duration_in_days' => $finder->isServiceDurationInDays(),
					'selected_date' => $selected_date,
					'services' => $casest['services'],
					'categories' => $casest['categories']
				), false ) );
				/*if($nb == 2)
					break;*/
				
				/*foreach ( $group_slots as $group_slot ) {
					$group_data = $group_slot->buildSlotData();
					$service_id   = $group_data[0][0];
					$service      = $group_data[ $service_id ];
					$categorie_id = $service['category_id'];
					$hour = $group_slot->start()->toClientTz()->formatI18n( 'h' );
					
					if(!key_exists($categorie_id, $salle_groups[$group])) {
						$salle_groups[$group][$categorie_id] = array();
					}
					
					if(!key_exists($hour, $salle_groups[$group]))
						$salle_groups[$categorie_id][$categorie_id][$hour] = $tags;
				}*/

			
			}

			//var_dump($casest['categories']);

			// Time zone switcher.
			$time_zone_options = '';
			if ( Lib\Config::showTimeZoneSwitcher() ) {
				$time_zone = Lib\Slots\DatePoint::$client_timezone;
				if ( $time_zone{0} == '+' || $time_zone{0} == '-' ) {
					$parts = explode( ':', $time_zone );
					$time_zone = sprintf(
						'UTC%s%d%s',
						$time_zone{0},
						abs( $parts[0] ),
						(int) $parts[1] ? '.' . rtrim( $parts[1] * 100 / 60 , '0' ) : ''
					);
				}
				$time_zone_options = wp_timezone_choice( $time_zone, get_user_locale() );
				if ( strpos( $time_zone_options, 'selected' ) === false ) {
					$time_zone_options .= sprintf(
						'<option selected="selected" value="%s">%s</option>',
						esc_attr( $time_zone ),
						esc_html( $time_zone )
					);
				}
			}

			// Set response.
			$response = array(
				'success'        => true,
				'csrf_token'     => Lib\Utils\Common::getCsrfToken(),
				'has_slots'      => ! empty ( $slots ),
				'has_more_slots' => $finder->hasMoreSlots(),
				'day_one_column' => Lib\Config::showDayPerColumn(),
				'slots'          => $slots,
				'staffs' => array_keys($finder->getStaffs()),
				'html'           => $this->render( '3_time', array(
					'progress_tracker'  => $progress_tracker,
					'info_text'         => $info_text,
					'date'              => Lib\Config::showCalendar() ? $finder->getSelectedDateForPickadate() : null,
					'has_slots'         => ! empty ( $slots ),
					'show_cart_btn'     => $this->_showCartButton( $userData ),
					'time_zone_options' => $time_zone_options,
				), false ),
				'salles_active_class' => $salles_active,
				'salle1' => $salle1,
				'salle2' => $salle2,
			);

			if ( Lib\Config::showCalendar() ) {
				$bounding = Lib\Config::getBoundingDaysForPickadate();
				$response['date_max'] = $bounding['date_max'];
				$response['date_min'] = $bounding['date_min'];
				$response['disabled_days'] = $finder->getDisabledDaysForPickadate();
			}
		} else {
			$response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
		}

		//print_r(' ---- end render time<br>');
		
		// Output JSON response.
		wp_send_json( $response );
	}
	
	
	/**
	 * Save booking data in session.
	 */
	public function executeSessionSave()
	{
		$form_id = $this->getParameter( 'form_id' );
		$errors  = array();
		if ( $form_id ) {
			$userData = new Lib\UserBookingData( $form_id );
			$userData->load();
			$parameters = $this->getParameters();
			$errors = $userData->validate( $parameters );
			if ( empty ( $errors ) ) {
				if ( $this->hasParameter( 'extras' ) ) {
					$parameters['chain'] = $userData->chain->getItemsData();
					foreach ( $parameters['chain'] as $key => &$item ) {
						// Decode extras.
						$item['extras'] = json_decode( $parameters['extras'][ $key ], true );
					}
				} elseif ( $this->hasParameter( 'slots' ) ) {
					// Decode slots.
					$parameters['slots'] = json_decode( $parameters['slots'], true );
					$parameters['slots'][0][3] = $parameters['nop'];
					$parameters['slots'][0][4] = $parameters['variationid'];
				} elseif ( $this->hasParameter( 'cart' ) ) {
					$parameters['captcha_ids'] = json_decode( $parameters['captcha_ids'], true );
					foreach ( $parameters['cart'] as &$service ) {
						// Remove captcha from custom fields.
						$custom_fields = array_filter( json_decode( $service['custom_fields'], true ), function ( $field ) use ( $parameters ) {
							return ! in_array( $field['id'], $parameters['captcha_ids'] );
						} );
						// Index the array numerically.
						$service['custom_fields'] = array_values( $custom_fields );
					}
					// Copy custom fields to all cart items.
					$cart           = array();
					$cf_per_service = Lib\Config::customFieldsPerService();
					$merge_cf       = Lib\Config::customFieldsMergeRepeating();
					foreach ( $userData->cart->getItems() as $cart_key => $_cart_item ) {
						$cart[ $cart_key ] = $cf_per_service
							? $parameters['cart'][ $merge_cf ? $_cart_item->getService()->getId() : $cart_key ]
							: $parameters['cart'][0];
					}
					$parameters['cart'] = $cart;
				}
				$userData->fillData( $parameters );
			}
		}
		$errors['success'] = empty( $errors );
		$errors['stepcart'] = Lib\Config::showStepCart();
		$errors['chainitem'] = $userData->chain->getItemsData();
		
		wp_send_json( $errors );
	}
	
	/**
	 * 6. Step details.
	 *
	 * @throws
	 */
	public function executeRenderDetails()
	{
		
		$form_id  = $this->getParameter( 'form_id' );
		
		$userData = new Lib\UserBookingData( $form_id );
		
		if ( $userData->load() ) {
			
			if ( ! Lib\Config::showStepCart() ) {
				$userData->addChainToCart();
			}

			$info_text       = Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_details_step' );
			
			$info_text_guest = ! get_current_user_id() ? Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_details_step_guest' ) : '';


			$progress_tracker = $this->_prepareProgressTracker( Steps::DETAILS, $userData );

			$info_text        = $this->components->prepareInfoText( Steps::DETAILS, $info_text, $userData );

			$info_text_guest  = $this->components->prepareInfoText( Steps::DETAILS, $info_text_guest, $userData );

			// Render main template.
			$html = $this->render( '6_details', array(
				'progress_tracker' => $progress_tracker,
				'info_text'        => $info_text,
				'info_text_guest'  => $info_text_guest,
				'userData'         => $userData,
			), false );
			
			// Render additional templates.
			$html .= $this->render( '_customer_duplicate_msg', array(), false );
			
			if (
				! get_current_user_id() && (
					get_option( 'bookly_app_show_login_button' ) ||
					strpos( $info_text . $info_text_guest, '{login_form}' ) !== false
				)
			) {
				$html .= $this->render( '_login_form', array(), false );
			}
			
			$response = array(
				'success' => true,
				'html'    => $html,
			);
		} else {
			$response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
		}
		
		// Output JSON response.
		wp_send_json( $response );
	}
	/**
	 * Render next time for step Time.
	 *
	 * response JSON
	 */
	public function executeRenderNextTime()
	{
		
		$userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );
		$casest = Lib\Config::getCaSeSt();
		$query = \Bookly\Lib\Entities\Service::query( 's' )
		                                     ->select( 's.id, s.category_id, s.title, s.position, s.duration, s.price, s.capacity_min, cat.name' )
		                                     ->innerJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
		                                     ->innerJoin( 'Category', 'cat', 'cat.id = s.category_id' )
		                                     ->where( 's.type',  \Bookly\Lib\Entities\Service::TYPE_SIMPLE )
		                                     ->where( 's.visibility', \Bookly\Lib\Entities\Service::VISIBILITY_PUBLIC )
		                                     ->groupBy( 's.id' );

		$query = \Bookly\Lib\Proxy\CustomerGroups::prepareCaSeStQuery( $query );

		foreach ( $query->fetchArray() as $row ) {
			$casest['services'][ $row['id'] ] = array(
				'id'          => (int) $row['id'],
				'category_id' => (int) $row['category_id'],
				'category_name' => (int) $row['name'],
				'name'        => $row['title'] == ''
					? __( 'Untitled', 'bookly' )
					: \Bookly\Lib\Utils\Common::getTranslatedString( 'service_' . $row['id'], $row['title'] ),
				'min_capacity' => (int) $row['capacity_min'],
				'price' =>  $row['price'], //html_entity_decode( \Bookly\Lib\Utils\Price::format( $row['price'] )) ,
				'pos'          => (int) $row['position'],
			);
		}


		$salle1 = get_option('amig-bookly-settings-salle1');
		$salle1_css = $salle1 == "0" ? 'salle1_inactive' : 'salle1_active';
		$salle2 = get_option('amig-bookly-settings-salle2');
		$salle2_css = $salle2 == "0" ? 'salle2_inactive' : 'salle2_active';

		$salles_active = $salle1_css.' '.$salle2_css;
		//var_dump($userData->chain->getItems()[0]);
		if ( $userData->load() ) {
			$finder =new \AmigBookly\Lib\Slots\Finder( $userData );
			$finder->setLastFetchedSlot( $this->getParameter( 'last_slot' ) );
			$finder->prepare()->load();
			
			$slots = $userData->getSlots();
			$selected_date = isset ( $slots[0][2] ) ? $slots[0][2] : null;
			$html = '';
			foreach ( $finder->getSlots() as $group => $group_slots ) {
				
				$html .= $this->render( '_time_slots', array(
					'group' => $group,
					'slots' => $group_slots,
					'duration_in_days' => $finder->isServiceDurationInDays(),
					'selected_date' => $selected_date,
					'services' => $casest['services'],
					'categories' => $casest['categories'],
				), false );
			}
			
			// Set response.
			$response = array(
				'success'        => true,
				'html'           => preg_replace( '/>\s+</', '><', $html ),
				'has_slots'      => $html != '',
				'has_more_slots' => $finder->hasMoreSlots(), // show/hide the next button
				'salles_active_class' => $salles_active,
				'salle1' => $salle1,
				'salle2' => $salle2,
				'staffs' => array_keys($finder->getStaffs()),
			);
		} else {
			$response = array( 'success' => false, 'error_code' => 1, 'error' => __( 'Session error.', 'bookly' ) );
		}
		
		// Output JSON response.
		wp_send_json( $response );
	}

	/**
	 * Handle time zone parameters.
	 *
	 * @param Lib\UserBookingData $userData
	 */
	private function _handleTimeZone( Lib\UserBookingData $userData )
	{
		$time_zone        = null;
		$time_zone_offset = null;  // in minutes

		if ( $this->hasParameter( 'time_zone_offset' ) ) {
			// Browser values.
			$time_zone        = $this->getParameter( 'time_zone' );
			$time_zone_offset = $this->getParameter( 'time_zone_offset' );
		} else if ( $this->hasParameter( 'time_zone' ) ) {
			// WordPress value.
			$time_zone = $this->getParameter( 'time_zone' );
			if ( preg_match( '/^UTC[+-]/', $time_zone ) ) {
				$offset           = preg_replace( '/UTC\+?/', '', $time_zone );
				$time_zone        = null;
				$time_zone_offset = - $offset * 60;
			} else {
				$time_zone_offset = - timezone_offset_get( timezone_open( $time_zone ), new \DateTime() ) / 60;
			}
		}

		if ( $time_zone !== null || $time_zone_offset !== null ) {
			// Client time zone.
			$userData
				->setTimeZone( $time_zone )
				->setTimeZoneOffset( $time_zone_offset )
				->applyTimeZone();
		}
	}


	/**
	 * Add data for the skipped Service step.
	 *
	 * @param Lib\UserBookingData $userData
	 */
	private function _setDataForSkippedServiceStep( Lib\UserBookingData $userData )
	{
		// Staff ids.
		$attrs = Lib\Session::getFormVar( $this->getParameter( 'form_id' ), 'attrs' );
		if ( $attrs['staff_member_id'] == 0 ) {
			$staff_ids = array_map( function ( $staff ) { return $staff['id']; }, Lib\Entities\StaffService::query()
				->select( 'staff_id AS id' )
				->where( 'service_id', $attrs['service_id'] )
				->fetchArray()
			);
		} else {
			$staff_ids = array( $attrs['staff_member_id'] );
		}
		// Date.
		$date_from = Lib\Slots\DatePoint::now()->modify( Lib\Config::getMinimumTimePriorBooking() );
		// Days and times.
		$days_times = Lib\Config::getDaysAndTimes();
		$time_from  = key( $days_times['times'] );
		end( $days_times['times'] );

		$userData->chain->clear();
		$chain_item = new Lib\ChainItem();
		$chain_item
			->setNumberOfPersons( 1 )
			->setQuantity( 1 )
			->setServiceId( $attrs['service_id'] )
			->setStaffIds( $staff_ids )
			->setLocationId( $attrs['location_id'] ?: null );
		$userData->chain->add( $chain_item );

		$userData->fillData( array(
			'date_from'      => $date_from->toClientTz()->format( 'Y-m-d' ),
			'days'           => array_keys( $days_times['days'] ),
			'edit_cart_keys' => array(),
			'slots'          => array(),
			'time_from'      => $time_from,
			'time_to'        => key( $days_times['times'] ),
		) );
	}

	/**
	 * Render progress tracker into a variable.
	 *
	 * @param int $step
	 * @param Lib\UserBookingData $userData
	 * @return string
	 */
	private function _prepareProgressTracker( $step, Lib\UserBookingData $userData )
	{
		//print_r('***** execute '.get_called_class().' : '.__FUNCTION__.'<br>');
		$result = '';

		if ( get_option( 'bookly_app_show_progress_tracker' ) ) {

			$payment_disabled = Lib\Config::paymentStepDisabled();
			if ( ! $payment_disabled && $step > Steps::SERVICE ) {
				if ( $step < Steps::CART ) {  // step Cart.
					// Assume that payment is disabled and check chain items.
					// If one is incomplete or its price is more than zero then the payment step should be displayed.
					$payment_disabled = true;
					foreach ( $userData->chain->getItems() as $item ) {
					//	//print_r($item);
						if ( $item->hasPayableExtras() ) {
							$payment_disabled = false;
							break;
						} else {
							if ( $item->getService()->getType() == Lib\Entities\Service::TYPE_SIMPLE ) {
								$staff_ids = $item->getStaffIds();
								$staff     = null;
								if ( count( $staff_ids ) == 1 ) {
									$staff = Lib\Entities\Staff::find( $staff_ids[0] );
								}
								if ( $staff ) {
									$staff_service = new Lib\Entities\StaffService();
									$staff_service->loadBy( array(
										'staff_id'   => $staff->getId(),
										'service_id' => $item->getService()->getId(),
									) );
									if ( $staff_service->getPrice() > 0 ) {
										$payment_disabled = false;
										break;
									}
								} else {
									$payment_disabled = false;
									break;
								}
							} else {    // Service::TYPE_COMPOUND
								if ( $item->getService()->getPrice() > 0 ) {
									$payment_disabled = false;
									break;
								}
							}
						}
					}
				} else {
					list( , $deposit ) = $userData->cart->getInfo( true );
					if ( $deposit == 0 ) {
						$payment_disabled = true;
					}
				}
			}
			////print_r('END');
			$result = $this->render( '_progress_tracker', array(
				'step' => $step,
				'show_cart' => Lib\Config::showStepCart(),
				'payment_disabled' => $payment_disabled,
				'skip_service_step' => Lib\Session::hasFormVar( $this->getParameter( 'form_id' ), 'attrs' )
			), false );
		}

		return $result;
	}


	private function _showCartButton( Lib\UserBookingData $userData )
	{
		return Lib\Config::showStepCart() && count( $userData->cart->getItems() );
	}


}


