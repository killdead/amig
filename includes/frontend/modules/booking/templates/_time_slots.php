<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<button class="bookly-day" value="<?php echo esc_attr( $group ) ?>" data-datelong="<?php echo ucwords(date_i18n( ( $duration_in_days ? 'M' : 'd F Y' ), strtotime( $group ) )) ?>">
    <?php echo ucwords(date_i18n( ( $duration_in_days ? 'M' : 'l' ), strtotime( $group ) )) ?><br><?php echo ucwords(date_i18n( ( $duration_in_days ? 'M' : 'd F' ), strtotime( $group ) )) ?>
    
</button>
<?php



$salle1 = get_option('amig-bookly-settings-salle1');
$salle1_hc = get_option('amig-bookly-settings-salle1-staff-hc');
$salle1_hp = get_option('amig-bookly-settings-salle1-staff-hp');
$salle1_off = get_option('amig-bookly-settings-salle1-staff-off');
$salle2 = get_option('amig-bookly-settings-salle2');
$salle2_hc = get_option('amig-bookly-settings-salle2-staff-hc');
$salle2_hp = get_option('amig-bookly-settings-salle2-staff-hp');
$salle2_off = get_option('amig-bookly-settings-salle2-staff-off');
?>
<?php foreach ( $slots as $slot ) {
	/** @var \Bookly\Lib\Slots\Range $slot */
	$data = $slot->buildSlotData();
	
	$service_id   = $data[0][0];
	$service      = $services[ $service_id ];
	$categorie_id = $service['category_id'];
	$servicesids  = array();

	foreach ( $services as $servicekey => $serviceitem ) {
		if ( $serviceitem['category_id'] == $categorie_id ) {
			$servicesids[] = array($servicekey,$serviceitem['price'], $serviceitem['min_capacity']);
		}
	}
	$categorie   = $categories[ $categorie_id ];
	$creaneautxt = '';
	$creneautype = '';
	
	$hour = $slot->start()->toWpTz()->formatI18n( 'H' );
	
	
	switch ( $data[0][1] ) {
		
		//case 3: case 6: $creaneautxt='(Tarif heures pleines)';$creneautype='hp';break;
		//case 4: case 7: $creaneautxt='(Tarif heures creuses)';$creneautype='hc';break;
		
		case $salle1_hp:
		case $salle2_hp:
		case $salle1_off:
		case $salle2_off:
			$creaneautxt = '(Tarif heures pleines)';
			$creneautype = 'hp';
			break;
		case $salle1_hc:
		case $salle2_hc:
			$creaneautxt = '(Tarif heures creuses)';
			$creneautype = 'hc';
			break;
	}
	
	$tags = sprintf( '<button value="%s" data-salle="%s" data-salleid="%s" data-creneautxt="%s" data-creneautype="%s" data-datetxt="%s" data-group="%s" class="bookly-hour%s" data-hour="%s" data-available="%s" data-serviceid="%s" data-staff="%s" %s><span class="ladda-label %s %s"></span></button>',
        esc_attr( json_encode( $data ) ),
        $categorie['name'],
        $service['category_id'],
        $creaneautxt,
        $creneautype,
        ucwords( date_i18n( 'l d F', strtotime( $group ) ) ),
        $group,
        $slot->fullyBooked() ? ' booked' : '',
        $slot->start()->toWpTz()->formatI18n( $duration_in_days ? 'D, M d' : get_option( 'time_format' ) ),
        (int) $slot->start()->toClientTz()->formatI18n( 'U' ) > (int) date( 'U' ) ? 1 : 0,
        esc_attr( json_encode( $servicesids ) ),
        $data[0][1],
		disabled( $slot->fullyBooked(), true, false ),
        $data[0][2] == $selected_date ? ' bookly-bold' : '',
        $slot->fullyBooked() ? 'reserve' : $creneautype	);

	// On vérifie que la categorie n'est pas inactive
	if(in_array($categorie_id, array($salle1,$salle2))) {
		if ( ! key_exists( 'tags', $categories[ $categorie_id ] ) ) {
			$categories[ $categorie_id ]['tags'] = array( $hour => $tags );
			//echo "First Add $categorie_id $hour $service_id<br>";
		} else {
			if ( ! key_exists( $hour, $categories[ $categorie_id ]['tags'] ) ) {
				//   echo "Add $categorie_id $hour $service_id<br>";
				$categories[ $categorie_id ]['tags'][ $hour ] = $tags;
			} else {
				/// si un des slots de l'heure est booked alors on écrase
				if ( $slot->fullyBooked() ) {
					$categories[ $categorie_id ]['tags'][ $hour ] = $tags;
				}
				// echo "$categorie_id $hour $service_id exist <br>";
			}
		}
	}


}

// Affichage des slots valides
foreach ($categories as $categorie_id => $categorie) {
    if($categorie_id != 0 && isset($categorie_id)) {
        foreach ( $categorie['tags'] as $i => $tag ) {
            echo $tag;
        }
    }
}
?>

