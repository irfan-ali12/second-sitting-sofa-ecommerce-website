/* Second Sitting front end: postcode check, reserve gating, gallery, menu, filters. */
( function () {
	'use strict';

	var data = window.ssData || {};
	var lastResult = data.saved || null;

	/* ---------- Reserve gating on product pages ---------- */
	function updateGates() {
		var ok = lastResult && ( lastResult.status === 'inside' || lastResult.status === 'outside' );
		document.querySelectorAll( '[data-ss-needs-postcode]' ).forEach( function ( wrap ) {
			wrap.querySelectorAll( 'button[type="submit"], .single_add_to_cart_button' ).forEach( function ( btn ) {
				btn.disabled = ! ok;
				btn.classList.toggle( 'is-locked', ! ok );
			} );
			var hint = wrap.querySelector( '[data-ss-hint]' );
			if ( hint ) {
				if ( ! ok ) {
					hint.textContent = hint.getAttribute( 'data-default' ) || hint.textContent;
				} else if ( lastResult.status === 'outside' ) {
					hint.textContent = 'You can reserve for collection from Harrow. No payment until we confirm.';
				} else {
					hint.textContent = 'We’ll confirm your delivery slot before taking payment.';
				}
			}
		} );
	}

	document.querySelectorAll( '[data-ss-hint]' ).forEach( function ( h ) {
		h.setAttribute( 'data-default', h.textContent );
	} );

	/* ---------- Postcode forms ---------- */
	function setAll( result, html ) {
		document.querySelectorAll( '[data-ss-postcode]' ).forEach( function ( f ) {
			f.querySelector( '.ss-pc-output' ).innerHTML = html;
			var input = f.querySelector( 'input[name="postcode"]' );
			if ( input && result.postcode ) {
				input.value = result.postcode;
			}
		} );
	}

	document.querySelectorAll( '[data-ss-postcode]' ).forEach( function ( form ) {
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var input = form.querySelector( 'input[name="postcode"]' );
			var out = form.querySelector( '.ss-pc-output' );
			var btn = form.querySelector( 'button' );
			var value = ( input.value || '' ).trim();
			if ( ! value ) {
				input.focus();
				return;
			}
			btn.disabled = true;
			out.innerHTML = '<div class="ss-pc-result is-pending">' + data.i18n.checking + '</div>';

			var body = new FormData();
			body.append( 'action', 'ss_check_postcode' );
			body.append( 'nonce', data.nonce );
			body.append( 'postcode', value );

			fetch( data.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( res ) {
					btn.disabled = false;
					if ( ! res || ! res.success ) {
						throw new Error( 'bad' );
					}
					if ( res.data.status === 'invalid' ) {
						out.innerHTML = '<div class="ss-pc-result is-error" role="alert">' + data.i18n.invalid + '</div>';
						return;
					}
					lastResult = res.data;
					setAll( res.data, res.data.html );
					updateGates();
				} )
				.catch( function () {
					btn.disabled = false;
					out.innerHTML = '<div class="ss-pc-result is-error" role="alert">' + data.i18n.error + '</div>';
				} );
		} );
	} );

	updateGates();

	/* ---------- Checkout: re-run shipping when postcode changes ---------- */
	if ( window.jQuery && document.querySelector( 'form.checkout' ) ) {
		window.jQuery( document.body ).on( 'change', '#billing_postcode, #shipping_postcode', function () {
			window.jQuery( document.body ).trigger( 'update_checkout' );
		} );
	}

	/* ---------- Gallery ---------- */
	document.querySelectorAll( '[data-ss-gallery]' ).forEach( function ( g ) {
		var slides = g.querySelectorAll( '.ss-slide' );
		var thumbs = g.querySelectorAll( '.ss-thumb' );
		var counter = g.querySelector( '[data-ss-current]' );
		var i = 0;
		function go( n ) {
			if ( ! slides.length ) {
				return;
			}
			i = ( n + slides.length ) % slides.length;
			slides.forEach( function ( s, k ) { s.classList.toggle( 'is-active', k === i ); } );
			thumbs.forEach( function ( t, k ) { t.classList.toggle( 'is-active', k === i ); } );
			if ( counter ) {
				counter.textContent = i + 1;
			}
		}
		thumbs.forEach( function ( t ) {
			t.addEventListener( 'click', function () { go( parseInt( t.getAttribute( 'data-index' ), 10 ) ); } );
		} );
		var prev = g.querySelector( '.ss-gallery-prev' );
		var next = g.querySelector( '.ss-gallery-next' );
		if ( prev ) { prev.addEventListener( 'click', function () { go( i - 1 ); } ); }
		if ( next ) { next.addEventListener( 'click', function () { go( i + 1 ); } ); }

		// Swipe on phones.
		var x0 = null;
		var main = g.querySelector( '.ss-gallery-main' );
		main.addEventListener( 'touchstart', function ( e ) { x0 = e.touches[0].clientX; }, { passive: true } );
		main.addEventListener( 'touchend', function ( e ) {
			if ( x0 === null ) { return; }
			var dx = e.changedTouches[0].clientX - x0;
			if ( Math.abs( dx ) > 40 ) { go( dx < 0 ? i + 1 : i - 1 ); }
			x0 = null;
		} );
	} );

	/* ---------- Mobile menu ---------- */
	var toggle = document.querySelector( '.ss-menu-toggle' );
	if ( toggle ) {
		toggle.addEventListener( 'click', function () {
			var open = toggle.getAttribute( 'aria-expanded' ) === 'true';
			toggle.setAttribute( 'aria-expanded', String( ! open ) );
			document.getElementById( 'ss-nav' ).classList.toggle( 'is-open', ! open );
		} );
	}

	/* ---------- Contact form: date/time only for viewings ---------- */
	document.querySelectorAll( '.ss-form' ).forEach( function ( form ) {
		var rows = form.querySelectorAll( '[data-ss-viewing-only]' );
		function sync() {
			var v = form.querySelector( 'input[name="ss_type"]:checked' );
			rows.forEach( function ( r ) { r.hidden = v && v.value !== 'viewing'; } );
		}
		form.querySelectorAll( 'input[name="ss_type"]' ).forEach( function ( r ) { r.addEventListener( 'change', sync ); } );
		sync();
	} );

	/* ---------- Shop price filter ---------- */
	var price = document.querySelector( '[data-ss-price]' );
	if ( price ) {
		price.addEventListener( 'change', function () {
			var form = price.form;
			var parts = price.value.split( '-' );
			form.elements.min_price.value = parts[0] || '';
			form.elements.max_price.value = parts[1] || '';
			Array.prototype.forEach.call( form.elements, function ( el ) {
				if ( el.name && ! el.value ) { el.disabled = true; }
			} );
			form.submit();
		} );
	}
} )();
