<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<button class="bookly-day" value="<?php echo esc_attr( $group ) ?>">
    <?php echo date_i18n( ( $duration_in_days ? 'M' : 'D, M d' ), strtotime( $group ) ) ?>
</button>
<?php for ( $i = 0; $i < 6; $i++ ) : ?>
    <button class="bookly-hour booked">
        <span class="ladda-label"><i class="bookly-hour-icon"><span></span></i>Indis</span>
    </button>
<?php endfor ?>