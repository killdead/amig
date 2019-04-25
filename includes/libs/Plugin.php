<?php
namespace AmigBookly\Lib;

//use BooklyCustomFields\Backend;
//use BooklyCustomFields\Frontend;

/**
 * Class Plugin
 * @package AmigBookly\Lib
 */
abstract class Plugin extends \Bookly\Lib\Base\Plugin
{
    protected static $prefix = 'bookly_';

    /**
     * Register hooks.
     */
    public static function registerHooks()
    {
        parent::registerHooks();

        // Register proxy methods.
        ProxyProviders\Local::registerMethods();
        ProxyProviders\Shared::registerMethods();

     /*   if ( is_admin() ) {
            Backend\Modules\CustomFields\Controller::getInstance();
            Frontend\Modules\Booking\Controller::getInstance();
        }
     */
    }

}