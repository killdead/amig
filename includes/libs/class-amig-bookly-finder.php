<?php
namespace AmigBookly\Lib\Slots;

use Bookly\Lib;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Slots;

/**
 * Class Finder
 * @package AmigBookly\Lib\Slots
 */
class Finder extends \Bookly\Lib\Slots\Finder
{

	public function __construct( Lib\UserBookingData $userData, $callback_group = null, $callback_stop = null )
	{
		parent::__construct($userData, $callback_group , $callback_stop );

	}


	public function prepare()
	{
		$this->_prepareDates();
		$this->_prepareStaffData();
	//	$this->_prepareServiceExtrasIds();

		return $this;
	}
	

	
    public function load_adapter( $callback_break = null ) {
		
		
		die(__CLASS__.' load adapter ');
		
	}

	private function _generate()
	{
		//print_r('******* execute '.get_called_class().' : '.__FUNCTION__.'<br>');
		$generators = null;

		/** @var Lib\ChainItem $chain_item */
		foreach ( array_reverse( $this->userData->chain->getItems() ) as $chain_item ) {
			//var_dump($chain_item);
			//$compound_service_id = $chain_item->getService()->isCompound() ? $chain_item->getService()->getId() : null;
			$chain_extras = $chain_item->getExtras();
			$compound_service_id = null;
			for ( $q = 0; $q < $chain_item->getQuantity(); ++ $q ) {
				$sub_services = \Bookly\Lib\Entities\Service::query( 's' )->select( 's.*' )->whereIn( 's.id', array_values( $chain_item->getServiceId() ) )->find();
				$services_count = count( $sub_services ) - 1;
				$spare_time     = 0;
				foreach ( array_reverse( $sub_services ) as $key => $sub_service ) {
					if ( $sub_service instanceof Lib\Entities\Service ) {
						
						$extras_duration = 0;
						$sub_service_id  = $sub_service->getId();
						//print_r('sub_service : '.$sub_service_id.'<br>');
						

						$generator = new \AmigBookly\Lib\Slots\Generator(
							$this->staff,
							isset ( $this->service_schedule[ $compound_service_id ][ $sub_service_id ] )
								? $this->service_schedule[ $compound_service_id ][ $sub_service_id ]
								: null,
							$this->srv_duration_as_slot_length ? $sub_service->getDuration() : $this->slot_length,
							$chain_item->getLocationId(),
							$sub_service_id,
							$sub_service->getDuration(),
							$sub_service->getPaddingLeft(),
							$sub_service->getPaddingRight(),
							$chain_item->getNumberOfPersons(),
							$extras_duration,
							$this->start_dp,
							$this->userData->getTimeFrom(),
							$this->userData->getTimeTo(),
							$spare_time,
							$this->waiting_list_enabled,
							null
						);
						$generators[] = $generator;
						$nb = count($generators);
						//print_r('sub_service : '.$sub_service_id.' nb after :'.$nb.'<br>');
						$spare_time = 0;
					} else {
						/** @var Lib\Entities\SubService $sub_service */
						$spare_time += $sub_service->getDuration();
					}
				}
			}
		}
		
	

	//	$this->srv_duration_days = $generator->serviceDurationInDays();
		
		//print_r('******* end generator<br>');
		return $generators;
	}

	public function load( $callback_break = null )
	{
		//print_r('***** execute '.get_called_class().' : '.__FUNCTION__.' callback:'.$callback_break.'<br>');
		$this->slots = array();
		$this->has_more_slots = false;

		// Prepare break callback.
		if ( ! is_callable( $callback_break ) ) {
			$callback_break = array( $this, '_breakDefault' );
		}

		// Do search.
		$slots_count = 0;
		$do_break    = false;
		$weekdays    = $this->userData->getDays();
		$generators   = $this->_generate();

		foreach ($generators as $key => $generator) {
			$slots_count = 0;
			$do_break    = false;
			//print_r( "START generators $key<br>" );
			/** @var Slots\RangeCollection $slots */
			foreach ( $generator as $slots ) {
				
				// Workaround for PHP < 5.5.
				$dp = $generator->key();
			
				// For empty slots check client end date here.
				if ( call_user_func( $callback_break, $dp, $this->srv_duration_days, $slots_count ) ) {
					break;
				}
				//	////print_r('slots : '.$dp->format('d/m/Y').'<br>');
				//	var_dump($slots);
				//print_r( 'START Rangecollection <br>' );
				
				/** @var Range $slot */
				foreach ( $slots->all() as $slot ) {
					//print_r('start slot:'.$slot->data()->serviceId().' start:'.$slot->start()->format('d/m/Y').'<br>');
					
					if ( $do_break ) {
						//print_r('do break 2<br>');
						// Flag there are more slots.
						$this->has_more_slots = true;
						break 2;
					}
					/** @var DatePoint $client_dp */
					$client_dp = $slot->start()->toClientTz();
					if ( $client_dp->lt( $this->client_start_dp ) ) {
						// Skip slots earlier than requested time.
						continue;
					}
					if ( ! in_array( (int) $client_dp->format( 'w' ) + 1, $weekdays ) ) {
						// Skip slots outside of requested weekdays.
						continue;
					}
					
					// Decide how to group slots.
					$group = call_user_func( $this->callback_group, $client_dp );
					
					// Decide when to stop.
					if ( ! isset ( $this->slots[ $group ] ) ) {
						switch ( call_user_func( $this->callback_stop, $client_dp, count( $this->slots ), $slots_count ) ) {
							case 0:  // Continue search.
								//print_r('Continue search<br>');
								break;
							case 1:  // Immediate stop.
								//print_r('immediate stop<br>');
								break 3;
							case 2:  // Check whether there are more slots and then stop.
								//print_r('Check whether there are more slots and then stop<br>');
								$do_break = true;
								continue 2;
						}
					}
					
					
					if ( $slot->notFullyBooked() || $this->show_blocked_slots ) {
						// Add slot to result.
						$this->slots[ $group ][] = $slot;
						//print_r('write<br>');
//					   ////print_r("\n  $slots_count) FOREACH GENERATOR ADD SLOT ".$slot->staffId()." in group $group\n");
						++ $slots_count;
					}
					else {
						
						//print_r('not writing slot<br>');
						die('not writing');
					}
				}
				
				//print_r( 'End Rangecollection <br>' );
			}
		}
		
		
		
		//print_r('***** end load <br>');
	//	die();
		// }
	}
	
	/**
	 * Callback for making decision whether to break generator loop.
	 *
	 * @param DatePoint $dp
	 * @param int $srv_duration_days
	 * @param int $slots_count
	 * @return bool
	 */
	private function _breakDefault( Lib\Slots\DatePoint $dp, $srv_duration_days, $slots_count )
	{
		return $dp->modify( - ( $srv_duration_days > 1 ? $srv_duration_days - 1 : 0 ) . ' days' )->gte( $this->client_end_dp );
	}

	/**
	 * Callback for computing slot's group.
	 *
	 * @param DatePoint $client_dp
	 * @return string
	 */
	private function _groupDefault(  Lib\Slots\DatePoint $client_dp )
	{
		return $client_dp
			->modify( $this->srv_duration_days && ! $this->show_calendar ? 'first day of this month' : null )
			->format('Y-m-d' );
	}

	/**
	 * Callback for making decision whether to stop when calendar is enabled.
	 *
	 * @param DatePoint $client_dp
	 * @param int $groups_count
	 * @param int $slots_count
	 * @return bool
	 */
	private function _stopCalendar(  Lib\Slots\DatePoint $client_dp, $groups_count, $slots_count  )
	{
		return (int) $client_dp->gte( $this->client_end_dp );
	}

	/**
	 * Callback for making decision whether to stop when days are displayed in one column.
	 *
	 * @param DatePoint $client_dp
	 * @param int $groups_count
	 * @param int $slots_count
	 * @return int
	 */
	private function _stopDayPerColumn(  Lib\Slots\DatePoint $client_dp, $groups_count, $slots_count )
	{
		// Stop when groups count has reached 10.
		return $groups_count >= 7 ? 2 : 0;
	}

	/**
	 * Callback for making decision whether to stop for default mode.
	 *
	 * @param DatePoint $client_dp
	 * @param int $groups_count
	 * @param int $slots_count
	 * @return int
	 */
	private function _stopDefault(  Lib\Slots\DatePoint $client_dp, $groups_count, $slots_count )
	{
		return $slots_count >= 100 ? 2 : 0;
	}

	/**
	 * Find start and end dates.
	 */
	private function _prepareDates()
	{
		//////print_r('***** execute '.get_called_class().' : '.__FUNCTION__.'<br>');
		// Initial constraints in WP time zone.
		$now       = Lib\Slots\DatePoint::now();
		$min_start = $now->modify( Lib\Config::getMinimumTimePriorBooking() );
		$max_end   = $now->modify( Lib\Config::getMaximumAvailableDaysForBooking() . ' days midnight' );

        //var_dump($this->last_fetched_slot,$now,$min_start,$max_end, $this->userData->getDateFrom());

		// Find start date.
		if ( $this->last_fetched_slot ) {
			
			// Set start date to the next day after last fetched slot.
			$this->client_start_dp = Lib\Slots\DatePoint::fromStr( $this->last_fetched_slot )->toClientTz()->modify( 'tomorrow' );
//	        var_dump( $this->client_start_dp);
		} else {

			// Requested date.
			$this->client_start_dp = Lib\Slots\DatePoint::fromStrInClientTz( $this->selected_date ?: $this->userData->getDateFrom() );

	      //  var_dump( $this->client_start_dp);

			if ( $this->show_calendar ) {
				$this->client_start_dp = $this->client_start_dp->modify( 'first day of this month midnight' );
			}
			/* if ( $this->client_start_dp->lt( $min_start ) ) {
				 $this->client_start_dp = $min_start->toClientTz();
			 }*/
		}

		// Find end date.
		$this->client_end_dp = $max_end->toClientTz();
		if ( $this->show_calendar ) {
			$client_next_month = $this->client_start_dp->modify( 'first day of next month midnight' );
			if ( $this->client_end_dp->gt( $client_next_month ) ) {
				$this->client_end_dp = $client_next_month;
			}
		}
		
		// Start and end dates in WP time zone.
		$this->start_dp = $this->client_start_dp->toWpTz();
		$this->end_dp   = $max_end;

        //var_dump($this->start_dp,$this->end_dp);
	}
	
	/**
	 * Prepare data for staff.
	 */
	private function _prepareStaffData()
	{
		////print_r('***** execute '.get_called_class().' : '.__FUNCTION__.'<br>');
		
		$staff_services = Lib\Entities\StaffService::query()
		                                           ->select( 'service_id, staff_id, price, capacity_min, capacity_max' )
		                                           ->order('ASC')
			//   ->whereRaw( implode( ' OR ', $where ), array() )
			                                       ->fetchArray();
		
		  
		foreach ( $staff_services as $staff_service ) {
			$staff_id = $staff_service['staff_id'];
			if ( ! isset ( $this->staff[ $staff_id ] ) ) {
				$this->staff[ $staff_id ] = new Lib\Slots\Staff();
			}
			$this->staff[ $staff_id ]->addService(
				$staff_service['service_id'],
				$staff_service['price'],
				$staff_service['capacity_min'],
				$staff_service['capacity_max'],
				1,
                Lib\Entities\Service::PREFERRED_MOST_EXPENSIVE
                );
		}
		
		
		// Holidays.
		$holidays = Lib\Entities\Holiday::query( 'h' )
		                                ->select( 'IF(h.repeat_event, DATE_FORMAT(h.date, \'%%m-%%d\'), h.date) as date, h.staff_id' )
		                                ->whereIn( 'h.staff_id', array_keys( $this->staff ) )
		                                ->whereRaw( 'h.repeat_event = 1 OR h.date >= %s', array( $this->start_dp->format( 'Y-m-d' ) ) )
		                                ->fetchArray();
		foreach ( $holidays as $holiday ) {
			$this->staff[ $holiday['staff_id'] ]->getSchedule()->addHoliday( $holiday['date'] );
		}
		
		
		
		// Working schedule.
		$working_schedule = Lib\Entities\StaffScheduleItem::query( 'ssi' )
		                                                  ->select( 'ssi.*, break.start_time AS break_start, break.end_time AS break_end' )
		                                                  ->leftJoin( 'ScheduleItemBreak', 'break', 'break.staff_schedule_item_id = ssi.id' )
		                                                  ->whereIn( 'ssi.staff_id', array_keys( $this->staff ) )
		                                                  ->whereNot( 'ssi.start_time', null )
		                                                  ->fetchArray();
		foreach ( $working_schedule as $item ) {
			$weekday  = $item['day_index'] - 1;
			$schedule = $this->staff[ $item['staff_id'] ]->getSchedule();
			if ( ! $schedule->hasDay( $weekday ) ) {
				$schedule->addDay( $weekday, $item['start_time'], $item['end_time'] );
			}
			if ( $item['break_start'] ) {
				$schedule->addBreak( $item['day_index'] - 1, $item['break_start'], $item['break_end'] );
			}
		}


		
		
		
		// Special days.
		$special_days = (array) Lib\Proxy\SpecialDays::getSchedule( array_keys( $this->staff ), $this->start_dp->value(), $this->end_dp->value() );
		foreach ( $special_days as $day ) {
			$schedule = $this->staff[ $day['staff_id'] ]->getSchedule();
			if ( ! $schedule->hasSpecialDay( $day['date'] ) ) {
				$schedule->addSpecialDay( $day['date'], $day['start_time'], $day['end_time'] );
			}
			if ( $day['break_start'] ) {
				$schedule->addSpecialBreak( $day['date'], $day['break_start'], $day['break_end'] );
			}
		}
		
		
		
		// Prepare padding_left for first service
		$chain         = $this->userData->chain->getItems();
	
		$first_item    = $chain[0];
		//var_dump($first_item->getServiceId());
		

		
		if(is_array($first_item->getServiceId()) ) {
			$services = \Bookly\Lib\Entities\Service::query( 's' )->select( 's.*' )->whereIn( 's.id', array_values( $first_item->getServiceId() ) )->find();
		}
		else
		{
			$services      = $first_item->getSubServices();
		}
		
		$first_service = $services[0];
		$padding_left  = $first_service->getPaddingLeft();
	
		// Take into account the statuses.
		$statuses = array(
			Lib\Entities\CustomerAppointment::STATUS_PENDING,
			Lib\Entities\CustomerAppointment::STATUS_APPROVED,
		);
		if ( Lib\Config::waitingListActive() ) {
			$statuses[] = Lib\Entities\CustomerAppointment::STATUS_WAITLISTED;
		}

		// Bookings.
		$bookings = Lib\Entities\Appointment::query( 'a' )
			->select( sprintf(
				'`a`.`id`,
                `a`.`location_id`,
                `a`.`staff_id`,
                `a`.`service_id`,
                `a`.`start_date`,
                DATE_ADD(`a`.`end_date`, INTERVAL `a`.`extras_duration` SECOND) AS `end_date`,
                `a`.`extras_duration`,
                COALESCE(`s`.`padding_left`,0) AS `padding_left`,
                COALESCE(`s`.`padding_right`,0) AS `padding_right`,
                SUM(`ca`.`number_of_persons`) AS `number_of_bookings`,
                SUM(IF(`ca`.`status` = "%s", `ca`.`number_of_persons`, 0)) AS `waitlisted`',
				Lib\Entities\CustomerAppointment::STATUS_WAITLISTED
			) )
			->leftJoin( 'CustomerAppointment', 'ca', '`ca`.`appointment_id` = `a`.`id`' )
			->leftJoin( 'Service', 's', '`s`.`id` = `a`.`service_id`' )
			->whereIn( 'a.staff_id', array_keys( $this->staff ) )
			->whereRaw( sprintf( 'ca.status IN ("%s") OR ca.status IS NULL', implode('","', $statuses ) ), array() )
			->whereRaw( 'DATE_ADD( `a`.`end_date`, INTERVAL (COALESCE(`s`.`padding_right`,0) + %d) SECOND) >= %s', array( $padding_left, $this->start_dp->format( 'Y-m-d' ) ) )
			->groupBy( 'a.id' )
			->fetchArray();
		foreach ( $bookings as $booking ) {
			$this->staff[ $booking['staff_id'] ]->addBooking( new Lib\Slots\Booking(
				$booking['location_id'],
				$booking['service_id'],
				$booking['number_of_bookings'],
				$booking['waitlisted'],
				$booking['start_date'],
				$booking['end_date'],
				$booking['padding_left'],
				$booking['padding_right'],
				$booking['extras_duration'],
				false
			) );
		}


		// Cart bookings.
		$this->handleCartBookings();
		
		// Google Calendar events.
		if ( get_option( 'bookly_gc_two_way_sync' ) ) {
			$query = Lib\Entities\Staff::query( 's' )
			                           ->whereIn( 's.id', array_keys( $this->staff ) )
			                           ->whereNot( 'google_data', null );
			foreach ( $query->find() as $staff ) {
				$google = new Lib\Google();
				if ( $google->loadByStaff( $staff ) ) {
					foreach ( $google->getCalendarEvents( $this->start_dp->value() ) ?: array() as $booking ) {
						$this->staff[ $staff->getId() ]->addBooking( $booking );
					}
				}
			}
		}
		//var_dump($this->staff);
		////print_r('***** end<br>');

	}
	
	/**
	 * Prepare service schedule.
	 *
	 * @param int $service_id
	 */
	private function _prepareServiceSchedule( $service_id )
	{
		$schedule = new Schedule();
		// Working schedule.
		$working_schedule = (array) Lib\Proxy\ServiceSchedule::getSchedule( $service_id );
		foreach ( $working_schedule as $item ) {
			$weekday = $item['day_index'] - 1;
			if ( ! $schedule->hasDay( $weekday ) ) {
				$schedule->addDay( $weekday, $item['start_time'], $item['end_time'] );
			}
			if ( $item['break_start'] ) {
				$schedule->addBreak( $weekday, $item['break_start'], $item['break_end'] );
			}
		}
		// Service special days.
		$special_days = (array) Lib\Proxy\SpecialDays::getServiceSchedule( $service_id, $this->start_dp->value(), $this->end_dp->value() );
		foreach ( $special_days as $day ) {
			if ( ! $schedule->hasSpecialDay( $day['date'] ) ) {
				$schedule->addSpecialDay( $day['date'], $day['start_time'], $day['end_time'] );
			}
			if ( $day['break_start'] ) {
				$schedule->addSpecialBreak( $day['date'], $day['break_start'], $day['break_end'] );
			}
		}
		// Add schedule to array.
		$this->service_schedule[ $service_id ] = $schedule;
//        var_dump($this->service_schedule);
	}
	
	/**
	 * Add cart items to staff bookings arrays.
	 */
	public function handleCartBookings()
	{
		foreach ( $this->userData->cart->getItems() as $cart_key => $cart_item ) {
			if ( ! in_array( $cart_key, $this->userData->get( 'edit_cart_keys' ) ) ) {
				$extras_duration = (int) Lib\Proxy\ServiceExtras::getTotalDuration( $cart_item->get( 'extras' ) );
				foreach ( $cart_item->get( 'slots' ) as $slot ) {
					list ( $service_id, $staff_id, $datetime ) = $slot;
					if ( isset ( $this->staff[ $staff_id ] ) ) {
						$service = Lib\Entities\Service::find( $service_id );
						$range   = Range::fromDates( $datetime, $datetime );
						$range   = $range->resize( $service->get( 'duration' ) + $extras_duration );
						$extras_duration = 0;
						$booking_exists = false;
						foreach ( $this->staff[ $staff_id ]->getBookings() as $booking ) {
							// If such booking exists increase number_of_bookings.
							if ( $booking->isFromGoogle() == false
							     && $booking->getServiceId() == $service_id
							     && $booking->getRange()->wraps( $range )
							) {
								$booking->incNop( $cart_item->get( 'number_of_persons' ) );
								$booking_exists = true;
								break;
							}
						}
						if ( ! $booking_exists ) {
							// Add cart item to staff bookings array.
							$this->staff[ $staff_id ]->addBooking( new Booking(
								$service_id,
								$cart_item->get( 'number_of_persons' ),
								$range->start()->format( 'Y-m-d H:i:s' ),
								$range->end()->format( 'Y-m-d H:i:s' ),
								$service->get( 'padding_left' ),
								$service->get( 'padding_right' ),
								$extras_duration,
								false
							) );
						}
					}
				}
			}
		}
	}
	
	/**
	 * Get disabled days in Pickadate format.
	 *
	 * @return array
	 */
	public function getDisabledDaysForPickadate()
	{
		$one_day = new \DateInterval( 'P1D' );
		$result = array();
		$date = new \DateTime( $this->selected_date ?: $this->userData->get( 'date_from' ) );
		$date->modify( 'first day of this month' );
		$end_date = clone $date;
		$end_date->modify( 'first day of next month' );
		$Y = (int) $date->format( 'Y' );
		$n = (int) $date->format( 'n' ) - 1;
		while ( $date < $end_date ) {
			if ( ! array_key_exists( $date->format( 'Y-m-d' ), $this->slots ) ) {
				$result[] = array( $Y, $n, (int) $date->format( 'j' ) );
			}
			$date->add( $one_day );
		}
		
		return $result;
	}
	
	/**
	 * Set last fetched slot.
	 *
	 * @param string $last_fetched_slot
	 * @return $this
	 */
	public function setLastFetchedSlot( $last_fetched_slot )
	{
		$slots = json_decode( $last_fetched_slot, true );
		$this->last_fetched_slot = array( $slots[0] );
		$this->last_fetched_slot = $last_fetched_slot;
//        ////print_r('setLastFetchedSlot '.$this->last_fetched_slot);
		
		return $this;
	}
	
	/**
	 * Set selected date.
	 *
	 * @param string $selected_date
	 * @return $this
	 */
	public function setSelectedDate( $selected_date )
	{
		$this->selected_date = $selected_date;
		
		return $this;
	}
	
	public function getSelectedDateForPickadate()
	{
		if ( $this->selected_date ) {
			foreach ( $this->slots as $group => $slots ) {
				if ( $group >= $this->selected_date ) {
					return $group;
				}
			}
			
			if ( empty( $this->slots ) ) {
				return $this->selected_date;
			} else {
				reset( $this->slots );
				return key( $this->slots );
			}
		}
		
		if ( ! empty ( $this->slots ) ) {
			reset( $this->slots );
			return key( $this->slots );
		}
		
		return $this->userData->get( 'date_from' );
	}
	
	/**
	 * @return array
	 */
	public function getSlots()
	{
		return $this->slots;
	}
	
	/**
	 * @return bool
	 */
	public function hasMoreSlots()
	{
		return $this->has_more_slots;
	}
	
	/**
	 * Whether the first service in chain has duration in days.
	 *
	 * @return bool
	 */
	public function isServiceDurationInDays()
	{
		return $this->srv_duration_days >= 1;
	}

	/**
	 * Recuperation de la liste des staffs
	 *
	 * @return Staff[]
	 */
	public function getStaffs() {
		return $this->staff;
	}
}