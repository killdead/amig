<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


use Bookly\Lib\Config;
use Bookly\Lib\Utils\DateTime;


$salle1_hc = get_option('amig-bookly-settings-salle1-staff-hc');
$salle1_hp = get_option('amig-bookly-settings-salle1-staff-hp');

$salle2_hc = get_option('amig-bookly-settings-salle2-staff-hc');
$salle2_hp = get_option('amig-bookly-settings-salle2-staff-hp');


?>
<?php if ( $print_assets ) include '_css.php' ?>
<div id="bookly-form-<?php echo $form_id ?>" class="bookly-form" data-form_id="<?php echo $form_id ?>">
    
</div>
<a id="fancybox" rel="group" href="#booklyPopup" style="display: none;"></a>
<div id="booklyPopup" style="display: none;">
    <h2 id="book-popup-title"><span class="salle">Staff1</span><br><span class="creneau">(Tarif heures creuses)</span></h2>
    <div id="book-popup-choose">Vous avez choisi le créneau du</div>
    <div id="book-popup-date">Jeudi 1 Mars à 14:00</div>
    <div id="book-popup-choose2">Combien de joueurs serez-vous ?</div>
    <div id="book-popup-select">
        <!-- SALLE 1 -->
    <select id="book-popup-select-pers-salle-<?=$salle1?>-hc" class="book-popup-select-pers form-control"
            style="display: none;">
        <?php
        foreach ($services  as $service) {

            if($service['category_id'] == (int) $salle1) {
                foreach ($staffs as $staff) {

                    if($staff['id'] == $salle1_hc && key_exists($service['id'], $staff['services'])) {

                        ?>
                        <option value="<?php echo $service['min_capacity'].','.$service['id'].','.$staff['id']; ?>"><?php echo
                        $service['min_capacity']; ?>
                            joueurs (<?php
                            echo $service['price']; ?>€/pers.)
                        </option>
                        <?php
                    }
                }
                }


       }
            ?>
    </select>
    <select id="book-popup-select-pers-salle-<?=$salle1?>-hp" class="book-popup-select-pers form-control"
            style="display: none;">
            <?php
            foreach ($services  as $service) {

                if($service['category_id'] == (int) $salle1) {
                    foreach ($staffs as $staff) {

                        if($staff['id'] == $salle1_hp && key_exists($service['id'], $staff['services'])) {

                            ?>
                            <option value="<?php echo $service['min_capacity'].','.$service['id'].','.$staff['id']; ?>"><?php echo
                                $service['min_capacity']; ?>
                                joueurs (<?php
                                echo $service['price']; ?>€/pers.)
                            </option>
                            <?php
                        }
                    }
                }


            }
            ?>
     </select>
 <!-- SALLE 2 -->
        <select id="book-popup-select-pers-salle-<?=$salle2?>-hc" class="book-popup-select-pers form-control"
                style="display: none;">
            <?php
            foreach ($services  as $service) {

                if($service['category_id'] == (int) $salle2) {
                    foreach ($staffs as $staff) {

                        if($staff['id'] == $salle2_hc && key_exists($service['id'], $staff['services'])) {

                            ?>
                            <option value="<?php echo $service['min_capacity'].','.$service['id'].','.$staff['id'];
                            ?>"><?php echo
                                $service['min_capacity']; ?>
                                joueurs (<?php
                                echo $service['price']; ?>€/pers.)
                            </option>
                            <?php
                        }
                    }
                }


            }
            ?>
        </select>
        <select id="book-popup-select-pers-salle-<?=$salle2?>-hp" class="book-popup-select-pers form-control"
                style="display: none;">
            <?php
            foreach ($services  as $service) {

                if($service['category_id'] == (int) $salle2) {
                    foreach ($staffs as $staff) {

                        if($staff['id'] == $salle2_hp && key_exists($service['id'], $staff['services'])) {

                            ?>
                            <option value="<?php echo $service['min_capacity'].','.$service['id'].','.$staff['id']; ?>"><?php echo
                                $service['min_capacity']; ?>
                                joueurs (<?php
                                echo $service['price']; ?>€/pers.)
                            </option>
                            <?php
                        }
                    }
                }


            }
            ?>
        </select>

    </div>
    <div id="book-popup-price"></div>
    <input type="hidden" id="book-variation-id" value="">
    <input type="hidden" id="book-nop" value="">
    <input type="hidden" id="book-slot" value="">
    <input type="hidden" id="book-service" value="">
    <input type="hidden" id="book-staff" value="">
    <div class="form-group" id="book-popup-btn-group">
        <div class="input-group input-group-lg icon-addon addon-lg">
			<span class="input-group-btn">
				<button id="book-popup-cancel" class="btn" title="Annuler">
                    Annuler
                </button>
				<button id="book-popup-book" class="btn btn-primary ladda-button" title="Réserver">
                    Réserver
                </button>
			</span>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function (win, fn) {
        var done = false, top = true,
            doc = win.document,
            root = doc.documentElement,
            modern = doc.addEventListener,
            add = modern ? 'addEventListener' : 'attachEvent',
            rem = modern ? 'removeEventListener' : 'detachEvent',
            pre = modern ? '' : 'on',
            init = function(e) {
                if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                if (!done) { done = true; fn.call(win, e.type || e); }
            },
            poll = function() {
                try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                init('poll');
            };
        if (doc.readyState == 'complete') fn.call(win, 'lazy');
        else {
            if (!modern) if (root.doScroll) {
                try { top = !win.frameElement; } catch(e) { }
                if (top) poll();
            }
            doc[add](pre + 'DOMContentLoaded', init, false);
            doc[add](pre + 'readystatechange', init, false);
            win[add](pre + 'load', init, false);
        }
    })(window, function() {
        window.bookly({
            ajaxurl                 : <?php echo json_encode( $ajax_url ) ?>,
            form_id                 : <?php echo json_encode( $form_id ) ?>,
            attributes              : <?php echo json_encode( $attrs ) ?>,
            status                  : <?php echo json_encode( $status ) ?>,
            errors                  : <?php echo json_encode( $errors ) ?>,
            start_of_week           : <?php echo (int) get_option( 'start_of_week' ) ?>,
            show_calendar           : <?php echo (int) Config::showCalendar() ?>,
            use_client_time_zone    : <?php echo (int) Config::useClientTimeZone() ?>,
            required                : <?php echo json_encode( $required ) ?>,
            skip_steps              : <?php echo json_encode( $skip_steps ) ?>,
            date_format             : <?php echo json_encode( DateTime::convertFormat( 'date', DateTime::FORMAT_PICKADATE ) ) ?>,
            final_step_url          : <?php echo json_encode( get_option( 'bookly_url_final_step_url' ) ) ?>,
            intlTelInput            : <?php echo json_encode( $options['intlTelInput'] ) ?>,
            woocommerce             : <?php echo json_encode( $options['woocommerce'] ) ?>,
            update_details_dialog   : <?php echo (int) get_option( 'bookly_cst_show_update_details_dialog' ) ?>,
            cart                    : <?php echo json_encode( $options['cart'] ) ?>,
            is_rtl                  : <?php echo (int) is_rtl() ?>
        });
    });
    
    

    jQuery(document).ready(function($){
        $('#fancybox').fancybox({
            autoSize: false,
            width : '400px',
            height : '340px',
            title: '',
            beforeClose: function() {
                Ladda.stopAll();
            }
        });



        $('#book-popup-book').on('click', function() {
           // jQuery('.bookly-form').addClass('bookly-spin-overlay').spin();
            window.bookly({
                ajaxurl: <?php echo json_encode($ajax_url) ?>,
                form_id: <?php echo json_encode($form_id) ?>,
                status:  { 'booking': 'savesession' },
                start_of_week  : <?php echo (int) get_option( 'start_of_week' ) ?>,
                show_calendar  : <?php echo (int) get_option( 'bookly_app_show_calendar' ) ?>,
                required       : <?php echo json_encode( $required ) ?>,
                skip_steps     : <?php echo json_encode( $skip_steps ) ?>,
                date_format    : <?php echo json_encode( \Bookly\Lib\Utils\DateTime::convertFormat( 'date', \Bookly\Lib\Utils\DateTime::FORMAT_PICKADATE ) ) ?>,
                final_step_url : <?php echo json_encode( get_option( 'bookly_gen_final_step_url' ) ) ?>,
                intlTelInput   : <?php echo json_encode( $options['intlTelInput'] ) ?>,
                woocommerce    : <?php echo json_encode( $options['woocommerce'] ) ?>,
                cart           : <?php echo json_encode( $options['cart'] ) ?>,
                is_rtl         : <?php echo (int) is_rtl() ?>
            });
        });

        

       // jQuery('.bookly-form').addClass('bookly-spin-overlay').spin();


    });

</script>

<?php if ( trim( $custom_css ) ): ?>
    <style type="text/css">
        <?php echo $custom_css; ?>
    </style>
<?php endif; ?>
