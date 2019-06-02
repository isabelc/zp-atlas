/**
 * Toggles the Atlas settings
 */
var zpAtlasDB = document.getElementById( 'zodiacpress_settings_atlas_db' );
var zpAtlasGN = document.getElementById( 'zodiacpress_settings_atlas_geonames' );

if ( null !== zpAtlasDB && null !== zpAtlasGN ) {
  
	if ( zpAtlasDB.checked ) {
		zpAtlasDBSettings();
	} else if( zpAtlasGN.checked ) {
		zpAtlasGNSettings();
	}
  
	zpAtlasDB.addEventListener( 'click', zpAtlasDBSettings );

	zpAtlasGN.addEventListener( 'click', zpAtlasGNSettings );
}

function zpAtlasDBSettings() {

	/* hide geonames settings */
	document.querySelector( '.zp-setting-geonames_user' ).style.display = 'none';

	/* show db settings */
	document.querySelector( '.zp-setting-atlas-status' ).style.display = 'table-row';
}

function zpAtlasGNSettings() {

	/* show Geonames settings */
	document.querySelector( '.zp-setting-geonames_user' ).style.display = 'table-row';

	/* hide db settings */
	document.querySelector( '.zp-setting-atlas-status' ).style.display = 'none';
  
}
