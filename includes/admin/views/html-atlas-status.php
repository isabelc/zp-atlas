<?php
/**
 * Admin View: Atlas Status setting
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$table_exists = ZP_Atlas_DB::table_exists();
$installing = get_option( 'zp_atlas_db_installing' );
$pending_msg = get_option( 'zp_atlas_db_pending' );
$status = __( 'error', 'zodiacpress' );
$class = 'atlas-error';
$checkmark = '';

if ( $pending_msg ) {
	$status = $pending_msg;
} else {
	$status = ( $installing ? zp_string( 'installing' ) : __( 'none', 'zodiacpress' ) );
}

if ( ! $table_exists ) {
	$status = __( 'none', 'zodiacpress' );
} else {
	if ( 'db' !== zp_atlas_option() ) {
		$status = __( 'not in use', 'zodiacpress' );
	} else {

		if ( ! $installing && ! $pending_msg ) {

		    // check if table installation is complete
			if ( ZP_Atlas_DB::use_db() ) {
		    	$status = zp_string( 'active' );
		    	$class = 'success';
		    	$checkmark = ' &#x2713; &nbsp; ';
			}

		}

	}
}

// Show installer only if the db has not been installed and a custom one is not being used, and it's not currently installing.

if ( ! ZP_Atlas_DB::is_installed() && ! ZP_Atlas_DB::is_separate_db() && ! $installing ) {
	?>
	<div id="zp-atlas-installer">
		<p><?php echo __( 'To create your atlas inside your WordPress database, run the Atlas Installer.', 'zodiacpress' ); ?>
			<strong><?php printf( __( 'Skip this to use a <a href="%s" target="_blank" rel="noopener">separate database</a>.', 'zodiacpress' ), 'https://isabelcastillo.com/docs/atlas-separate-database' ); ?></strong></p>
		<p><button id="zp-atlas-install" class="button-primary"><?php _e( 'Run the Atlas Installer', 'zodiacpress' ); ?></button></p>
	</div>
<?php } ?>

<div id="zp-atlas-status" class="stuffbox">
	<div class="inside">
		<h2><?php _e( 'Atlas Status', 'zodiacpress' ); ?></h2>
		<table class="widefat">

			<tr>
				<td><label><?php _e( 'Status', 'zodiacpress' ); ?></label></td>
				<td>
					<span class="zp-<?php echo $class; ?>"> <?php echo $checkmark; ?>
						<?php echo $status; ?>
					</span>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'City records count', 'zodiacpress' ); ?></label></td>
				<td id="zp-atlas-status-rows">
					<?php 
					if ( $table_exists && ! $installing ) {
						echo number_format( ZP_Atlas_DB::row_count() );
					}
					?>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'Database table size', 'zodiacpress' ); ?></label></td>
				<td id="zp-atlas-status-size">
					<?php
					if ( $table_exists && ! $installing ) {
						// @todo put back echo ( $size = zp_atlas_get_size() ) ? ( number_format( $size / 1048576, 1 ) . ' MB' ) : $size;
					}
					?>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'Database table primary key', 'zodiacpress' ); ?></label></td>
				<td id="zp-atlas-status-key">
					<?php 
					if ( $table_exists && ! $installing ) {

						echo ZP_Atlas_DB::key_exists( 'PRIMARY' ) ? __( 'okay', 'zodiacpress' ) : __( 'missing', 'zodiacpress' );

					}
					?>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'Database table index', 'zodiacpress' ); ?></label></td>
				<td id="zp-atlas-status-index">
					<?php 
					if ( $table_exists && ! $installing ) {

						echo ZP_Atlas_DB::key_exists( 'ix_name_country' ) ? __( 'okay', 'zodiacpress' ) : __( 'missing', 'zodiacpress' );

					}
					?>
				</td>
			</tr>

		</table>

	</div>
</div>