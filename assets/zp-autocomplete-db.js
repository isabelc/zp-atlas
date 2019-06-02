/**
 * Autocomplete city field from atlas database
 */
jQuery( '#place' ).autocomplete({
	source: function( request, response ) {
			jQuery.get( zp_js_strings.ajaxurl, 'action=zp_atlas_get_cities&c=' + request.term, response, 'json' );
	},
	minLength: 2,
	select: function( event, ui ) {

		/* Insert hidden inputs with timezone ID and birthplace coordinates */
		var hiddenInputs = {
			'geo_timezone_id': ui.item.tz,
			'zp_lat_decimal': ui.item.lat,
			'zp_long_decimal': ui.item.long
		}

		for ( var elID in hiddenInputs ) {
			/* Remove any previous in case they're changing the city */
			var exists = document.getElementById( elID );
			if ( null !== exists ) {
				exists.remove();
			}
			/* Insert hidden inputs */
			elInput = document.createElement( 'input' );
		    elInput.type = 'hidden';
		    elInput.id = elID;
			elInput.name = elID;
			elInput.value = hiddenInputs[ elID ];
			document.getElementById( 'zp-timezone-id' ).appendChild( elInput );
		}
		zpGetOffset();
	}
});
