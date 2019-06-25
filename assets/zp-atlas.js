const zpForm = document.getElementById( 'zp-birthreport-form' );
var cityIn = document.getElementById('placein'),
	zpSubmit = document.getElementById( 'zp-fetch-birthreport' );

/* Disable Submit button until form is filled */
zpSubmit.setAttribute( 'disabled', true );

/* autoComplete.js 6.0 by Tarek Raafat */
var a,b;a=this,b=function(){"use strict";function s(e,t){for(var n=0;n<t.length;n++){var s=t[n];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(e,s.key,s)}}function l(e){return"string"==typeof e?document.querySelector(e):e()}function a(e){return e.innerHTML=""}function e(e,t){t=t||{bubbles:!1,cancelable:!1,detail:void 0};var n=document.createEvent("CustomEvent");return n.initCustomEvent(e,t.bubbles,t.cancelable,t.detail),n}var u="data-result",n="autoComplete_results_list",c="autoComplete_result",t="autoComplete_highlighted",o=l,i=function(e){var t=document.createElement(e.element);return t.setAttribute("id",n),e.container&&e.container(t),e.destination.insertAdjacentElement(e.position,t),t},h=function(e){return"<span class=".concat(t,">").concat(e,"</span>")},r=function(i,r,o){r.forEach(function(e,t){var n=document.createElement(o.element),s=r[t].value[e.key]||r[t].value;n.setAttribute(u,s),n.setAttribute("class",c),n.setAttribute("tabindex","1"),o.content?o.content(e,n):n.innerHTML=e.match||e,i.appendChild(n)})},d=function(e,n){var s=l(e),i=n.firstChild;document.onkeydown=function(ev){if(!ev.target.matches(e))return;var t=document.activeElement;switch(ev.keyCode){case 38:t!==i&&t!==s?t.previousSibling.focus():t===i&&s.focus();break;case 40:t===s&&0<n.childNodes.length?i.focus():t!==n.lastChild&&t.nextSibling.focus()}}},f=a,m=function(n,s,i,r){var o=s.querySelectorAll(".".concat(c));Object.keys(o).forEach(function(t){["mousedown","keydown"].forEach(function(e){o[t].addEventListener(e,function(t){"mousedown"!==e&&13!==t.keyCode&&39!==t.keyCode||(i({event:t,query:l(n)instanceof HTMLInputElement?l(n).value:l(n).innerHTML,matches:r.matches,results:r.list.map(function(e){return e.value}),selection:r.list.find(function(e){return(e.value[e.key]||e.value)===t.target.closest(".".concat(c)).getAttribute(u)})}),a(s))})})})};e.prototype=window.Event.prototype;var p={CustomEventWrapper:"function"==typeof window.CustomEvent&&window.CustomEvent||e,initElementClosestPolyfill:function(){Element.prototype.matches||(Element.prototype.matches=Element.prototype.msMatchesSelector||Element.prototype.webkitMatchesSelector),Element.prototype.closest||(Element.prototype.closest=function(e){var t=this;do{if(t.matches(e))return t;t=t.parentElement||t.parentNode}while(null!==t&&1===t.nodeType);return null})}};return function(){function t(e){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),this.selector=e.selector||"#autoComplete",this.data={src:function(){return"function"==typeof e.data.src?e.data.src():e.data.src},key:e.data.key,cache:void 0===e.data.cache||e.data.cache},this.query=e.query,this.searchEngine="loose"===e.searchEngine?"loose":"strict",this.threshold=e.threshold||0,this.debounce=e.debounce||0,this.resultsList={render:!(!e.resultsList||!e.resultsList.render)&&e.resultsList.render,view:e.resultsList&&e.resultsList.render?i({container:!(!e.resultsList||!e.resultsList.container)&&e.resultsList.container,destination:e.resultsList&&e.resultsList.destination?e.resultsList.destination:o(this.selector),position:e.resultsList&&e.resultsList.position?e.resultsList.position:"afterend",element:e.resultsList&&e.resultsList.element?e.resultsList.element:"ul"}):null},this.sort=e.sort||!1,this.placeHolder=e.placeHolder,this.maxResults=e.maxResults||5,this.resultItem={content:!(!e.resultItem||!e.resultItem.content)&&e.resultItem.content,element:e.resultItem&&e.resultItem.element?e.resultItem.element:"li"},this.noResults=e.noResults,this.highlight=e.highlight||!1,this.onSelection=e.onSelection,this.dataSrc,this.init()}return function(e,t,n){t&&s(e.prototype,t),n&&s(e,n)}(t,[{key:"search",value:function(e,t){var n=this.highlight,s=t.toLowerCase();if("loose"===this.searchEngine){e=e.replace(/ /g,"");for(var i=[],r=0,o=0;o<s.length;o++){var l=t[o];r<e.length&&s[o]===e[r]&&(l=n?h(l):l,r++),i.push(l)}return r===e.length&&i.join("")}if(s.includes(e))return e=new RegExp("".concat(e),"i").exec(t),n?t.replace(e,h(e)):t}},{key:"listMatchedResults",value:function(n){var u=this;return new Promise(function(e){var a=[];n.filter(function(n,s){function e(e){var t=u.search(u.queryValue,n[e]||n);t&&e?a.push({key:e,index:s,match:t,value:n}):t&&!e&&a.push({index:s,match:t,value:n})}if(u.data.key){var t=!0,i=!1,r=void 0;try{for(var o,l=u.data.key[Symbol.iterator]();!(t=(o=l.next()).done);t=!0){e(o.value)}}catch(e){i=!0,r=e}finally{try{t||null==l.return||l.return()}finally{if(i)throw r}}}else e()});var t=u.sort?a.sort(u.sort).slice(0,u.maxResults):a.slice(0,u.maxResults);return u.resultsList.render&&(r(u.resultsList.view,t,u.resultItem),d(u.selector,u.resultsList.view)),e({matches:a.length,list:t})})}},{key:"ignite",value:function(){var a=this,u=this.selector,c=o(u),h=this.query,e=this.placeHolder;e&&c.setAttribute("placeholder",e);function n(t){function n(e,t){c.dispatchEvent(new p.CustomEventWrapper("autoComplete",{bubbles:!0,detail:{event:e,input:s,query:i,matches:t?t.matches:null,results:t?t.list:null},cancelable:!0}))}var s=c instanceof HTMLInputElement?c.value.toLowerCase():c.innerHTML.toLowerCase(),i=a.queryValue=h&&h.manipulate?h.manipulate(s):s,e=a.resultsList.render,r=i.length>a.threshold&&i.replace(/ /g,"").length;if(e){var o=a.onSelection,l=a.resultsList.view;f(l);r?a.listMatchedResults(a.dataSrc).then(function(e){n(t,e),0===e.list.length&&a.noResults&&a.resultsList.render?a.noResults():o&&m(u,l,o,e)}):n(t)}else!e&&r?a.listMatchedResults(a.dataSrc).then(function(e){n(t,e)}):n(t)}var s,i,r;c.addEventListener("keyup",(s=function(t){if(a.data.cache)n(t);else{var e=a.data.src();e instanceof Promise?e.then(function(e){a.dataSrc=e,n(t)}):(a.dataSrc=e,n(t))}},i=this.debounce,function(){var e=this,t=arguments;clearTimeout(r),r=setTimeout(function(){return s.apply(e,t)},i)}))}},{key:"init",value:function(){var t=this,e=this.data.src();e instanceof Promise?e.then(function(e){t.dataSrc=e,t.ignite()}):(this.dataSrc=e,this.ignite()),p.initElementClosestPolyfill()}}]),t}()},"object"==typeof exports&&"undefined"!=typeof module?module.exports=b():"function"==typeof define&&define.amd?define(b):a.autoComplete=b();

/* Autocomplete city field from GeoNames webservice */

const autoCompletejs = new autoComplete({
	data: {
		src: async function() {
			if (cityIn.value.length < 2) {
				return;
			} else {
				const query = cityIn.value;// User search query
				const url = zpastr.ajaxurl;

				// Fetch External Data Source
				const source = await fetch(`${url}?action=zp_atlas_get_cities&c=${query}`);
				const data = await source.json();
				var arr = [];
				/* Hide the geonames error message, if any, in case it is a 2nd try */
				zpRemoveError();
				/* check for GeoNames exceptions */
				if ( data.status !== undefined ) {			
					/* show new error */
					zpShowError( 'ERROR ' + data.status.value + ' - ' + data.status.message );
				} else {

					// Grab only the geonames fields that i need 
					for (var i = 0; i<data.length; i++) {
					  arr[i] = {
					  			name: data[i].value,
					  			lat: data[i].lat,
					  			lng: data[i].long,
					  			zone: data[i].tz
					  		}
					}

				}

				// Return Fetched data
				return arr;

			}
		},
		key: ['name'],
		cache: false,
	},
	selector: "#placein",
	threshold: 1,// Minimum characters length before engine starts rendering results
	searchEngine: "loose",// Search Engine Type/Mode default strict
	maxResults: 20,
	resultsList: {
		render: true,
		container: function(source) {
		      source.setAttribute("id", "autoComplete_results_list");
		},
		destination: cityIn,
		position: "afterend",
		element: "ul",
	},
	resultItem: {
	    content: function(data, source) {
	      source.innerHTML = data.match;
	    },
	    element: "li",
	},
	onSelection: function(feedback) {
		// Clear Input			
		cityIn.value = '';

		// Change placeholder with the selected value
		cityIn.setAttribute( 'placeholder', feedback.selection.value.name );

		/* Update values for hidden inputs for timezone ID and birthplace coordinates */
		document.getElementById( 'place' ).value = feedback.selection.value.name;
		document.getElementById( 'geo_timezone_id' ).value = feedback.selection.value.zone;
		document.getElementById( 'zp_lat_decimal' ).value = feedback.selection.value.lat;
		document.getElementById( 'zp_long_decimal' ).value = feedback.selection.value.lng;
		zpGetOffset();
	},
});

/**
 * Ajax request to get time offset
 */
function zpGetOffset() {
	const form = (Array.from(new FormData(zpForm), e => e.map(encodeURIComponent).join('=')).join('&')) + '&action=zp_tz_offset';
	const xhr = new XMLHttpRequest();
	xhr.open( 'POST', zpastr.ajaxurl );
	xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	xhr.responseType = 'json';
		
	xhr.onload = function() {

		if (xhr.status === 200 && xhr.response) {
			if ( xhr.response.error ) {

				/* remove previous errors if any */
				zpRemoveError();
					
				/* show new error */
				zpShowError( xhr.response.error );

			} else {

				/* if not null, blank, nor false, but 0 is okay  */
				if ( null !== xhr.response.offset_geo && '' !== xhr.response.offset_geo && 'false' != xhr.response.offset_geo) {

					/* remove previous errors if any */
					zpRemoveError();
								
					/* Display offset. */
					document.getElementById( 'zp-offset-wrap' ).style.display = 'block';
					document.getElementById( 'zp_offset_geo' ).value = xhr.response.offset_geo;
					document.getElementById( 'zp-form-tip' ).style.display = 'block';

					/* Enable submit button */
					zpSubmit.removeAttribute( 'disabled' );

				}

			}

		}
	};

	xhr.send( form );
}

/* Fetch birth report upon clicking submit */

zpSubmit.addEventListener( 'click', function( e ) {
	e.preventDefault();
	const form = Array.from(new FormData(zpForm), e => e.map(encodeURIComponent).join('=')).join('&');
	const xhr = new XMLHttpRequest();
	xhr.open( 'POST', zpastr.ajaxurl );
	xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	xhr.responseType = 'json';
	xhr.onload = function() {
		if (xhr.status === 200 && xhr.response) {
			if ( xhr.response.error ) {

				/* remove previous errors if any */
				zpRemoveError();
							
				/* show new error */
				zpShowError( xhr.response.error );

			} else {

				/* if neither null, blank, nor false */
				var zpReport = xhr.response.report.trim();
				if ( zpReport && 'false' != zpReport ) {
					var content = document.getElementById( 'zp-report-content' ),
						wrap = document.getElementById( 'zp-report-wrap' );

					/* remove previous errors if any */
					zpRemoveError();

					/* Display report. */
					wrap.style.display = 'block'; 
					content.insertAdjacentHTML( 'afterbegin', xhr.response.report );
					document.getElementById( 'zp-form-wrap' ).style.display = 'none';

					/* Insert the chart image. */
					switch ( zpastr.draw ) {
						case 'top':

							/* Show image at top */
							document.querySelector( '.zp-report-header' ).insertAdjacentHTML( 'afterend', xhr.response.image );
						break;
						case 'bottom':

							/* show image at end of report */
							content.insertAdjacentHTML( 'beforeend', xhr.response.image );
						break;
					}

					/* Scroll to top of report */
					window.scrollTo({ top: wrap.offsetTop + 70, behavior: 'smooth' });// @test on safari

				}

			}

		}
	};

	xhr.send( form );
});

// Redo the Offset if date or time is changed.

zpForm.addEventListener( 'change', redoOffset );

function redoOffset( e ) {
	// Get offset after unknown_time checkbox is checked after notice of missing time
	if ( e.target.id === 'unknown_time' ) {

		if ( e.target.checked ) {
			// Only do ajax (get offset) if (partial) required fields are entered.
			if ( zpFieldsFilled() ) {
				zpGetOffset();
			}
		}

	} else {

		// Redo the Offset if date or time is changed.

		var selects = ['month', 'day', 'year', 'hour', 'minute'];

		// If the changed element doesn't have the right selector, bail
		if ( ! selects.includes( e.target.id ) ) return;

		var changed = ! e.target.options[e.target.selectedIndex].defaultSelected;
		if ( changed ) {

			// Only do ajax (get offset) if (partial) required fields are entered.
			if ( zpFieldsFilled() ) {
				zpGetOffset();
			}
		}
	}
}

/**
 * Check that the fields required to get offset are entered.
 */
function zpFieldsFilled() {
	var ids = ['geo_timezone_id','zp_long_decimal','zp_lat_decimal','place','minute','hour','year','day','month'];

	for ( var i of ids ) {

		var el = document.getElementById( i );

		if ( null === el ) {
			return false;
		}

        if ( el.value.length === 0 || ! el.value.trim() ) {

        	// if minute or hour are blank, pass if unknown time is checked
        	if ( 'minute' === i || 'hour' === i ) {
        		if ( document.getElementById( 'unknown_time' ).checked ) {
        			continue;
        		}
        	}

        	return false;
        }
	}
	return true;
}

function zpRemoveError() {
	var el = document.querySelector( '.ui-state-error' );
	if ( el !== null ) { el.parentNode.removeChild( el ); }
}

function zpShowError( msg ) {
	var span = document.createElement( 'span' );
	span.setAttribute( 'class', 'ui-state-error' );
	span.textContent = msg;
	document.getElementById( 'zp-birthplace' ).appendChild( span );
}
