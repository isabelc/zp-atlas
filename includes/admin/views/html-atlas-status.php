<?php
/**
 * Admin View: Atlas Status setting
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$table_exists = ZPAtlas_DB::table_exists();
$installing = get_option( 'zp_atlas_db_installing' );
$pending_msg = get_option( 'zp_atlas_db_pending' );
$status = __( 'error', 'zp-atlas' );
$class = 'atlas-error';
$checkmark = '';

if ( $pending_msg ) {
	$status = $pending_msg;
} else {
	$status = ( $installing ? zp_string( 'installing' ) : __( 'none', 'zp-atlas' ) );
}

if ( ! $table_exists ) {
	$status = __( 'none', 'zp-atlas' );
} else {
	if ( 'db' !== zpatlas_option() ) {
		$status = __( 'not in use', 'zp-atlas' );
	} else {

		if ( ! $installing && ! $pending_msg ) {

		    // check if table installation is complete
			if ( ZPAtlas_DB::use_db() ) {
		    	$status = zp_string( 'active' );
		    	$class = 'success';
		    	$checkmark = ' &#x2713; &nbsp; ';
			}

		}

	}
}

// Show installer only if the db has not been installed and a custom one is not being used, and it's not currently installing.

if ( ! ZPAtlas_DB::is_installed() && ! ZPAtlas_DB::is_separate_db() && ! $installing ) {
	?>
	<div id="zp-atlas-installer">
		<p><?php echo __( 'To create your atlas inside your WordPress database, run the Atlas Installer.', 'zp-atlas' ); ?>
			<strong><?php printf( __( 'Skip this to use a <a href="%s" target="_blank" rel="noopener">separate database</a>.', 'zp-atlas' ), 'https://isabelcastillo.com/docs/atlas-separate-database' ); ?></strong></p>
		<p><button id="zp-atlas-install" class="button-primary"><?php _e( 'Run the Atlas Installer', 'zp-atlas' ); ?></button></p>
	</div>
<?php } elseif(get_option('zp_atlas_db_try_again')) {
	?><button id="zp-atlas-try-again" class="button-secondary"><?php _e( 'Try installing atlas again', 'zp-atlas' ); ?></button><br><br><?php
} ?>
<div id="zp-atlas-status" class="stuffbox">
	<div class="inside">
		<h2><?php _e( 'Atlas Status', 'zp-atlas' ); ?></h2>
		<table class="widefat">

			<tr>
				<td><label><?php _e( 'Status', 'zp-atlas' ); ?></label></td>
				<td>
					<span class="zp-<?php echo $class; ?>"> <?php echo $checkmark; ?>
						<?php echo $status; ?>
					</span>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'City records count', 'zp-atlas' ); ?></label></td>
				<td id="zp-atlas-status-rows">
					<?php 
					if ( $table_exists && ! $installing ) {
						echo number_format( ZPAtlas_DB::row_count() );
					}
					?>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'Database table size', 'zp-atlas' ); ?></label></td>
				<td id="zp-atlas-status-size">
					<?php
					if ( $table_exists && ! $installing ) {
						echo ( $size = zpatlas_get_size() ) ? ( number_format( $size / 1048576, 1 ) . ' MB' ) : $size;
					}
					?>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'Database table primary key', 'zp-atlas' ); ?></label></td>
				<td id="zp-atlas-status-key">
					<?php 
					if ( $table_exists && ! $installing ) {

						echo ZPAtlas_DB::key_exists( 'PRIMARY' ) ? __( 'okay', 'zp-atlas' ) : __( 'missing', 'zp-atlas' );

					}
					?>
				</td>
			</tr>

			<tr>
				<td><label><?php _e( 'Database table index', 'zp-atlas' ); ?></label></td>
				<td id="zp-atlas-status-index">
					<?php 
					if ( $table_exists && ! $installing ) {

						echo ZPAtlas_DB::key_exists( 'ix_name_country' ) ? __( 'okay', 'zp-atlas' ) : __( 'missing', 'zp-atlas' );

					}
					?>
				</td>
			</tr>

		</table>

	</div>
</div>