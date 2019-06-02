<?php
/**
 * Sanitize subtext fields
 *
 * @param string $input The field value
 * @return string $input Sanitizied value
 */
function zpa_sanitize_subtext_field( $input, $key ) {
	return sanitize_text_field( $input );
}
add_filter( 'zp_settings_sanitize_subtext', 'zpa_sanitize_subtext_field', 10, 2 );
/**
 * Callback function that renders alternate text settings.
 *
 * @param array $args Arguments passed by the setting
 * @return void
 */
function zp_subtext_callback( $args ) {// @test flex-container
	$name = isset( $args['name'] ) ? $args['name'] : '';
	?>
	<div class="zp-flex-container stuffbox"><div><strong><?php echo $name; ?></strong></div>
	<div><?php zp_text_callback( $args ); ?></div></div>
	<?php
}
/**
 * Callback that renders radio input setting
 *
 * @param array $args Arguments passed by the setting
 * @return void
 */
function zp_radio_callback( $args ) {
	$options = get_option( 'zodiacpress_settings' );
	if ( isset( $options[ $args['id'] ] ) ) {
		$value = $options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}
	$html = '<label for="zodiacpress_settings[' . esc_attr( $args['id'] ) . ']"> ' . wp_kses_post( $args['desc'] ) . '</label>';
	foreach ( $args['options'] as $option => $name ) {
		$checked = ( $option === $value ) ? ' checked' : '';
		$html .= '<div><input type="radio" name="zodiacpress_settings[' . esc_attr( $args['id'] ) . ']" id="zodiacpress_settings_' . esc_attr( $args['id'] ) . '_' . esc_attr( $option ) . '" value="' . esc_attr( $option ) . '"' . $checked . '>' . esc_html( $name ) . '</div>';
	}
	echo $html;
}
/**
 * Callback that renders Atlas status box
 *
 * @param array $args Arguments passed by the setting
 * @return void
 */
function zp_atlas_callback( $args ) {
	include ZPATLAS_PATH . 'includes/admin/views/html-atlas-status.php';
}
/**
 * Add settings to the ZP settings Misc tab.
 */
function zpa_settings_misc($settings) {

	// remove the core geonames_user settings
	unset( $settings['main']['geonames_user'] );// @test

	// add these to top of Misc tab
	$new = array(
			'atlas'	=> array(
				'id'	=> 'atlas',
				'name'	=> __( 'Choose Atlas', 'zodiacpress' ),
				'desc'	=> sprintf( __( 'You need an atlas to get city coordinates and timezones. Do you want to use GeoNames.org or create your own atlas database? (<a href="%1$s" target="_blank" rel="noopener">Help with this decision</a>)', 'zodiacpress' ), 'https://isabelcastillo.com/docs/choose-atlas' ),
				'type'	=> 'radio',
				'options' => array(
					'geonames' => __( 'Use GeoNames', 'zodiacpress' ),
					'db' => __( 'Use my own atlas database', 'zodiacpress' ),
				),
				'std'	=> 'geonames',
				'class' => 'zp-setting-atlas'
			),
			'geonames_user'	=> array(
				'id'	=> 'geonames_user',
				'name'	=> __( 'GeoNames Username', 'zodiacpress' ),
				'desc'	=> sprintf( __( 'Your username from GeoNames.org is needed to get timezone info from their webservice. (%1$screate free account%2$s)', 'zodiacpress' ), '<a href="http://www.geonames.org/login" target="_blank" rel="noopener">', '</a>' ),
				'type'	=> 'subtext',
				'size'	=> 'medium',
				'std'	=> '',
				'class' => 'zp-setting-geonames_user'
			),
			'atlas_status'	=> array(
				'id'	=> 'atlas_status',
				'name'	=> __( 'Atlas Status', 'zodiacpress' ),
				'type'	=> 'atlas',
				'class' => 'zp-setting-atlas-status'
			)
	);

	// insert the new settings after the 1st header
	$header = array_shift($settings['main']);
	$settings['main'] = array_merge($header, $new, $settings['main']);
	return $settings;
}
