/*
 * admin-atlas-install.js is loaded only during the atlas installation in the admin,
 * only if atlas has not been installed and a custom db (via filter) is not being used.
 */

window.wp.heartbeat.interval( 15 );

/* Show spinner if the atlas is currently installing */

if ( '1' === zpAtlasStrings.installingNow ) {
	zpSpinner();
}

/* Run Installer upon button click */

var zpAtlasInstall = document.getElementById( 'zp-atlas-install' );
if ( zpAtlasInstall !== null ) {
	zpAtlasInstall.addEventListener( 'click', function (e) {
	    e.preventDefault();

		// show the 'installing in background...' admin notice
		zpAtlasNotice( zpAtlasStrings.installingNotice );
		window.scroll(0,0);

		// remove installer
		var el = document.getElementById( 'zp-atlas-installer' );
		if ( el !== null ) { el.parentNode.removeChild( el ); }

		jQuery.post(ajaxurl, 'action=zp_atlas_install&_ajax_nonce=' + zpAtlasStrings.nonce, function(response) {
				
				/* Replace 'none' status with 'installing...' */
				document.querySelector( '.zp-atlas-error' ).textContent = zpAtlasStrings.installing;

				/* Start spinner */
				zpSpinner();

		});

	});
}

/**
 * Sends a flag to notify server that we want atlas install status
 */
jQuery( document ).on( 'heartbeat-send', function ( event, data ) {
	data.zpatlas_status = true;
});

/**
 * Receives atlas install status on heartbeat tick
 */
jQuery( document ).on( 'heartbeat-tick', function ( event, data ) {
    if ( ! data.zpatlas_status_field ) {
        return;
    }
    var message = data.zpatlas_status_field.trim();

	/* Show admin notice if there is one */

	var notice = ! data.zpatlas_status_notice ? '' : data.zpatlas_status_notice.trim();
	if ( notice ) {

		/* Remove any old zp atlas admin notice */
		var el = document.querySelector( '.zp-atlas-message' );
		if ( el !== null ) {
			el.parentNode.removeChild( el );
		}
		zpAtlasNotice( notice );
	}

    /* Update the status on Atlas Status field, if on that page. */

	var status = document.querySelector( '.zp-atlas-error' );

	if ( status !== null ) {
	
		/* If installation is now complete, make it green instead of red */

		if ( data.zpatlas_status_db ) {
			
			/* Set the status to "Active" */
			status.textContent = ' \u2713 ' + message;
			status.classList.add( 'zp-success' );
			status.classList.remove( 'zp-atlas-error' );

			/* Fill in database details */
			document.getElementById( 'zp-atlas-status-rows' ).textContent = data.zpatlas_status_db.rows;
			document.getElementById( 'zp-atlas-status-size' ).textContent = data.zpatlas_status_db.size;
			document.getElementById( 'zp-atlas-status-key' ).textContent = data.zpatlas_status_db.key;
			document.getElementById( 'zp-atlas-status-index' ).textContent = data.zpatlas_status_db.index;

		} else {
			
			/* update the status text */
			status.textContent = message;

			/* restart spinner only if status is Inserting or 'Creating table keys' */
			if ( message === zpAtlasStrings.inserting || message === zpAtlasStrings.creatingKeys ) {
				zpSpinner();
			}

		}

	}

});

/**
 * Create and show an admin notice
 */
function zpAtlasNotice( msg ) {
	/* create notice div */
	var div = document.createElement( 'div' );
	div.classList.add( 'notice', 'zp-atlas-message' );
	/* create paragraph element to hold message */
	var p = document.createElement( 'p' );
	/* Add message heading */
    var strong = document.createElement( 'strong' );
    strong.appendChild( document.createTextNode( zpAtlasStrings.statusHeading ) );
	p.appendChild( strong );
    /* Add message text */
    p.appendChild( document.createTextNode( ' \u2014 ' + msg ) );
    
    /* Add link to check status if not on status page */
	if ( window.location.href.indexOf( 'page=zodiacpress-settings&tab=misc' ) === -1 ) {
	 	var a = document.createElement( 'a' );
		a.setAttribute( 'href', zpAtlasStrings.adminurl + 'admin.php?page=zodiacpress-settings&tab=misc' );
	    a.appendChild( document.createTextNode( zpAtlasStrings.checkStatus ) );
	    /* spaces */
		p.appendChild( document.createTextNode( '\xa0 \xa0' ) );
		p.appendChild( a );
	}

	/* Add the whole message to notice div */
    div.appendChild( p );

	/* Add dismiss button */
	var b = document.createElement( 'button' );
	b.setAttribute( 'type', 'button' );
	b.setAttribute( 'class', 'notice-dismiss' );

	/* Add screen reader text to button */
	var bSpan = document.createElement( 'span' );
	bSpan.setAttribute( 'class', 'screen-reader-text' );
	bSpan.appendChild( document.createTextNode( zpAtlasStrings.dismiss ) );
	b.appendChild( bSpan );
	div.appendChild( b );

	/* Add notice after the first h1 */
	var h1 = document.getElementsByTagName( 'h1' )[0];
	h1.parentNode.insertBefore( div, h1.nextSibling);

	/* Make the notice dismissible */
	b.addEventListener( 'click', function () {
		div.parentNode.removeChild( div );
	});

}

/**
 * Start or stop spinner for Status field
 */
function zpSpinner( action = 'start' ) {

	if ( 'stop' === action) {

		/* stop spinner */

		var spinner = document.getElementById( 'zp-spinner' );
		if ( spinner !== null ) {
			spinner.parentNode.removeChild( spinner );
		}


	} else {

		/* start spinner */

		/* status field */
		var el = document.querySelector( '.zp-atlas-error' );
		if ( el !== null ) {

			/* Create span element for spinner */
		    var s = document.createElement( 'span' );
		    s.id = 'zp-spinner';

		    /* make it spin */
		    s.setAttribute( 'class', 'zp-spinner' );

		    /* Prepend it to status field */
			el.insertBefore( s, el.firstChild );

		}

	}
}
