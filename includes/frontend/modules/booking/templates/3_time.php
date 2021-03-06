<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
echo $progress_tracker;
?>
<div class="bookly-box ">
    <div class="hide"><?php echo $info_text ?></div>
        <div class="bookly-holder bookly-label-error bookly-bold"></div>
    </div>
    <?php //if ( \Bookly\Lib\Config::showCalendar() ) : ?>
    <style type="text/css">
        /* .picker__holder{top: 0;left: 0;}*/
        /*.bookly-time-step {margin-left: 0;margin-right: 0;}*/
    </style>

    <?php //endif ?>
    <?php if ( $has_slots ) : ?>
    <div class="bookly-current-week-text">
        Semaine du <span class="startweek"></span> au <span class="endweek"></span>
    </div>
    <div class="bookly-box bookly-nav-steps bookly-clear top">

        <button class="bookly-time-prev bookly-btn ladda-button" data-style="zoom-in" style="display: none;" data-spinner-size="40">
            <span class="ladda-label fa fa-chevron-circle-left"></span>
        </button>

        <button class="bookly-btn bookly-btn-submit bookly-calendar-trigger" ><span class="fa fa-calendar"></span>&nbsp;<span class="fa  fa-angle-down"></span></button>

        <button class="bookly-time-next bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label fa fa-chevron-circle-right"></span>
        </button>


        <div class="bookly-input-wrap bookly-slot-calendar bookly-js-slot-calendar" style="display: none;">
            <input style="display: none" class="bookly-js-selected-date" type="hidden" value="" data-value="<?php echo esc_attr( $date ) ?>" />
        </div>

    </div>
    <div class="bookly-time-step">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td width="130" valign="bottom">
                    <div class="bookly-js-first-column">
                        <div style="height: 102px"></div>
                        <div class="bookly-column-legend">
                            <div class="bookly-row-legend"><span>10 h 00</span></div>
                            <div class="bookly-row-legend"><span>12 h 00</span></div>
                            <div class="bookly-row-legend"><span>14 h 00</span></div>
                            <div class="bookly-row-legend"><span>16 h 00</span></div>
                            <div class="bookly-row-legend"><span>18 h 00</span></div>
                            <div class="bookly-row-legend"><span>20 h 00</span></div>
                            <div class="bookly-row-legend"><span>22 h 00</span></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="bookly-columnizer-wrap">
                        <div class="bookly-columnizer">
                            <?php /* here _time_slots */ ?>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

    </div>
    <div class="bookly-box bookly-nav-steps bookly-clear">
        <?php else : ?>
        <div class="bookly-not-time-screen<?php if ( ! \Bookly\Lib\Config::showCalendar() ) : ?> bookly-not-calendar<?php endif ?>">
            <?php _e( 'No time is available for selected criteria.', 'bookly' ) ?>
        </div>
        <div class="bookly-box bookly-nav-steps">
            <?php endif ?>
            <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button hide" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
            </button>
            <?php if ( $show_cart_btn ) : ?>
                <button class="bookly-go-to-cart bookly-js-go-to-cart bookly-round bookly-round-md ladda-button" data-style="zoom-in" data-spinner-size="30">
                    <span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/cart.png' ) ?>" /></span>
                </button>
            <?php endif ?>
        </div>
