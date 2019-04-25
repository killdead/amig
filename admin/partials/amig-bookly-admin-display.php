<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AmigBookly
 * @subpackage AmigBookly/admin/partials
 */

use Bookly\Lib;


if ( ! empty ( $_POST ) ) {
	update_option( 'amig-bookly-settings-salle1', $_POST['amig-bookly-settings-salle1']);
	update_option( 'amig-bookly-settings-salle1-staff-hc', $_POST['amig-bookly-settings-salle1-staff-hc']);
	update_option( 'amig-bookly-settings-salle1-staff-hp', $_POST['amig-bookly-settings-salle1-staff-hp']);
	update_option( 'amig-bookly-settings-salle1-staff-off', $_POST['amig-bookly-settings-salle1-staff-off']);
	update_option( 'amig-bookly-settings-salle2', $_POST['amig-bookly-settings-salle2']);
	update_option( 'amig-bookly-settings-salle2-staff-hc', $_POST['amig-bookly-settings-salle2-staff-hc']);
	update_option( 'amig-bookly-settings-salle2-staff-hp', $_POST['amig-bookly-settings-salle2-staff-hp']);
	update_option( 'amig-bookly-settings-salle2-staff-off', $_POST['amig-bookly-settings-salle2-staff-off']);
}


$salle1 = get_option('amig-bookly-settings-salle1');
$salle1_hc = get_option('amig-bookly-settings-salle1-staff-hc');
$salle1_hp = get_option('amig-bookly-settings-salle1-staff-hp');
$salle1_off = get_option('amig-bookly-settings-salle1-staff-off');

$salle2 = get_option('amig-bookly-settings-salle2');
$salle2_hc = get_option('amig-bookly-settings-salle2-staff-hc');
$salle2_hp = get_option('amig-bookly-settings-salle2-staff-hp');
$salle2_off = get_option('amig-bookly-settings-salle2-staff-off');
$casest = Lib\Config::getCaSeSt();

?>


<div class="container-fluid">
	<div id="amig-header" class="row wrap"><div class="col-md-12"><h1>A maze in game Paramétrages</h1></div></div>
	<div id="amig-content" class="row" style="margin-top:30px;">
		<div class="col-md-4">

			<form method="post">
				<div class="form-group">
					<label for="amig-bookly-settings-salle1">Première salle</label>
					<select class="form-control" id="amig-bookly-settings-salle1"
							name="amig-bookly-settings-salle1">
						<option value="0" <?php echo $salle1 == "0"	?'selected':'';?>> -- Fermée --</option>
						<?php foreach ($casest['categories'] as $id => $categorie) : ?>
						<option value="<?=$id;?>" <?php echo $salle1 == $id ? 'selected':'';?>><?=$categorie['name'].' (id:'.$id.')';
						?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="form-group">
					<label for="amig-bookly-settings-salle1-staff-hc">Heure creuse</label>
					<select class="form-control" id="amig-bookly-settings-salle1-staff-hc"
							name="amig-bookly-settings-salle1-staff-hc">
						<option value="0" <?php echo $salle1 == "0"	?'selected':'';?>></option>
						<?php foreach ($casest['staff'] as $id => $staff) : ?>
						<option value="<?=$id;?>" <?php echo $salle1_hc == $id ? "selected":"";
						?>><?=$staff['name'].' (id:'.$id.')';
						?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="form-group">
					<label for="amig-bookly-settings-salle1-staff-hp">Heure pleine</label>
					<select class="form-control" id="amig-bookly-settings-salle1-staff-hp"
					        name="amig-bookly-settings-salle1-staff-hp">
						<option value="0" <?php echo $salle1_hp == "0"	?'selected':'';?>></option>
						<?php foreach ($casest['staff'] as $id => $staff) : ?>
							<option value="<?=$id;?>" <?php echo $salle1_hp == $id ? 'selected':'';
							?>><?=$staff['name'].' (id:'.$id.')';
								?></option>
						<?php endforeach ?>
					</select>
				</div>

				<div class="form-group">
					<label for="amig-bookly-settings-salle1-staff-off">Jour férié</label>
					<select class="form-control" id="amig-bookly-settings-salle1-staff-off"
					        name="amig-bookly-settings-salle1-staff-off">
						<option value="0" <?php echo $salle1_off == "0"	?'selected':'';?>></option>
						<?php foreach ($casest['staff'] as $id => $staff) : ?>
							<option value="<?=$id;?>" <?php echo $salle1_off == $id ? 'selected':'';
							?>><?=$staff['name'].' (id:'.$id.')';
								?></option>
						<?php endforeach ?>
					</select>
				</div>


				<div class="form-group">
					<label for="amig-bookly-settings-salle2">Seconde salle</label>
					<select class="form-control" id="amig-bookly-settings-salle2" name="amig-bookly-settings-salle2">
						<option value="0" <?php echo $salle2 == "0"	?'selected':'';?>> -- Fermée --</option>
						<?php foreach ($casest['categories'] as $id => $categorie) : ?>
							<option value="<?=$id;?>" <?php echo $salle2 == $id	?'selected':'';?>><?=$categorie['name'].' (id:'.$id.')'; ?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="form-group">
					<label for="amig-bookly-settings-salle2-staff-hc">Heure creuse</label>
					<select class="form-control" id="amig-bookly-settings-salle2-staff-hc"
					        name="amig-bookly-settings-salle2-staff-hc">
						<option value="0" <?php echo $salle2 == "0"	?'selected':'';?>></option>
						<?php foreach ($casest['staff'] as $id => $staff) : ?>
							<option value="<?=$id;?>" <?php echo $salle2_hc == $id ? 'selected':'';
							?>><?=$staff['name'].' (id:'.$id.')';
								?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="form-group">
					<label for="amig-bookly-settings-salle2-staff-hp">Heure pleine</label>
					<select class="form-control" id="amig-bookly-settings-salle2-staff-hp"
					        name="amig-bookly-settings-salle2-staff-hp">
						<option value="0" <?php echo $salle2_hp == "0"	?'selected':'';?>></option>
						<?php foreach ($casest['staff'] as $id => $staff) : ?>
							<option value="<?=$id;?>" <?php echo $salle2_hp == $id ? 'selected':'';
							?>><?=$staff['name'].' (id:'.$id.')';
								?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="form-group">
					<label for="amig-bookly-settings-salle2-staff-off">Jour férié</label>
					<select class="form-control" id="amig-bookly-settings-salle2-staff-off"
					        name="amig-bookly-settings-salle2-staff-off">
						<option value="0" <?php echo $salle2_off == "0"	?'selected':'';?>></option>
						<?php foreach ($casest['staff'] as $id => $staff) : ?>
							<option value="<?=$id;?>" <?php echo $salle2_off == $id ? 'selected':'';
							?>><?=$staff['name'].' (id:'.$id.')';
								?></option>
						<?php endforeach ?>
					</select>
				</div>

				
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
		</div>
	</div>
</div>