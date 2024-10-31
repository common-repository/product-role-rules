/**
 * uses admin localized variable | proler
 */

(function ($) {
	$( document ).ready( function () {
		var key = false;
		
		$( window ).on( 'scroll', function () {
			if ( $( window ).scrollTop() > 60 ) {
				$( '.proler-admin-wrap' ).addClass( 'proler-stick-heading' );
			} else {
				if ( $( '.proler-admin-wrap' ).hasClass( 'proler-stick-heading' ) ) {
					$( '.proler-admin-wrap' ).removeClass( 'proler-stick-heading' );
				}
			}
		});

		$( 'body' ).on( 'click', '.wfl-nopro', function (e) {
			e.preventDefault();

			var txt = $( this ).attr( 'data-protxt' );
			$( '.wfl-popup .focus span' ).text( txt );
			$( '.wfl-popup' ).show();
		});

		$( 'body' ).on( 'input', '.wfl-nopro', function (e) {
			e.preventDefault();

			var txt = $( this ).attr( 'data-protxt' );
			$( '.wfl-popup .focus span' ).text( txt );
			$( '.wfl-popup' ).show();
			
			$( this ).val( '' );
		});

		$( 'body' ).on( 'click', '.wfl-popup .close', function () {
			$( '.wfl-popup' ).hide();
		});

		$( 'body' ).on( 'click', 'input[name="proler_stype"]', function () {
			var v = $( this ).val();
			$( '.prs-notice' ).remove();

			if ( v == 'default' ) {
				$( '.role-settings-content' ).hide( 'slow' );
			} else if ( v == 'proler-based' ) {
				$( '.role-settings-content' ).show( 'slow' );
			} else {
				$( '.role-settings-content' ).hide( 'slow' );
			}
		});

		// convert json to string - for using as input field value
		function json_populate_input( field, data ){
			var s = JSON.stringify( data );
			s     = s.replaceAll( '\"', '\"' );
			$( 'input[name="' + field + '"]' ).val( s );
		}
		function validate_input( val ){
			val = val.replaceAll( '\'', ':*snglqt*:' ).replaceAll( '\"', ':*dblqt*:' );
			return val;
		}
		function get_range_data( row ){
			var data = [];
			row.find( '.disrange-item' ).each(function(){
				var row = $(this);
				var rd  = {};

				if ( row.find( 'select[name="discount_type"]' ).val().length > 0 ) {
					var discount_type = validate_input( row.find( 'select[name="discount_type"]' ).val() );
					rd['discount_type'] = discount_type;
				}

				if ( row.find( 'input[name="min_value"]' ).val().length > 0 ) {
					var min_value = validate_input( row.find( 'input[name="min_value"]' ).val() );
					rd['min'] = min_value;
				}

				if ( row.find( 'input[name="max_value"]' ).val().length > 0 ) {
					var max_value = validate_input( row.find( 'input[name="max_value"]' ).val() );
					rd['max'] = max_value;
				}

				if ( row.find( 'input[name="discount_value"]' ).val().length > 0 ) {
					var discount_value = validate_input( row.find( 'input[name="discount_value"]' ).val() );
					rd['discount'] = discount_value;
				}

				data.push( rd );
			});

			return data;
		}
		function get_role_settings( row ){
			var data = {};

			if ( row.find( 'input[name="discount_text"]' ).is( ':checked' ) ) {
				data[ 'discount_text' ] = true;
			}

			if ( row.find( 'input[name="hide_price"]' ).is( ':checked' ) ) {
				data[ 'hide_price' ] = true;
			}

			if ( row.find( 'textarea[name="hide_txt"]' ).val().length > 0 ) {
				data[ 'hide_txt'] = encodeURIComponent( row.find( 'textarea[name="hide_txt"]' ).val() );
			}

			var discount = '';
			if ( row.find( 'input[name="discount"]' ).val().length > 0 ) {
				discount = validate_input( row.find( 'input[name="discount"]' ).val() );
			}
			data['discount'] = discount;

			var discount_type = '';
			if ( row.find( 'select[name="discount_type"]' ).val().length > 0 ) {
				discount_type = validate_input( row.find( 'select[name="discount_type"]' ).val() );
			}
			data['discount_type'] = discount_type;

			var min_qty = '';
			if ( row.find( 'input[name="min_qty"]' ).val().length > 0 && proler.has_pro == true ) {
				min_qty = validate_input( row.find( 'input[name="min_qty"]' ).val() );
			}
			data['min_qty'] = min_qty;

			var max_qty = '';
			if ( row.find( 'input[name="max_qty"]' ).val().length > 0 && proler.has_pro == true ) {
				max_qty = validate_input( row.find( 'input[name="max_qty"]' ).val() );
			}
			data['max_qty'] = max_qty;

			if ( row.find( 'input[name="pr_enable"]' ).is( ':checked' ) ) {
				data[ 'pr_enable' ] = true;
			}

			var category = '';
			if ( row.find( 'select[name="category"]' ).val() && row.find( 'select[name="category"]' ).val().length > 0 ) {
				category = validate_input( row.find( 'select[name="category"]' ).val() );
			}
			data['category'] = category;

			var product_type = '';
			if ( row.find( 'select[name="product_type"]' ).val() && row.find( 'select[name="product_type"]' ).val().length > 0 ) {
				product_type = validate_input( row.find( 'select[name="product_type"]' ).val() );
			}
			data['product_type'] = product_type;
			
			if ( row.find( 'input[name="schedule_start"]' ).val().length > 0 ) {
				data['schedule'] = {};

				var start = validate_input( row.find( 'input[name="schedule_start"]' ).val() );
				data['schedule']['start'] = start;
			}
			if ( row.find( 'input[name="schedule_end"]' ).val().length > 0 ) {
				var end = validate_input( row.find( 'input[name="schedule_end"]' ).val() );
				data['schedule']['end'] = end;
			}
			
			data['hide_regular_price'] = false;
			if ( row.find( 'input[name="hide_regular_price"]' ).is( ':checked' ) ) {
				data[ 'hide_regular_price' ] = true;
			}

			data['ranges'] = get_range_data( row );

			var additional_discount_display = '';
			if ( row.find( 'select[name="additional_discount_display"]' ).val().length > 0 ) {
				additional_discount_display = validate_input( row.find( 'select[name="additional_discount_display"]' ).val() );
			}
			data['additional_discount_display'] = additional_discount_display;
			// console.log( data );

			return data;
		}
		function get_settings(){
			var data = {};

			if ( typeof $( 'input[name="proler_stype"]:checked' ).val() != 'undefined' ) {
				data['proler_stype'] = $( 'input[name="proler_stype"]:checked' ).val();
			}

			data['roles'] = {};

			// per role data
			$( '.pr-settings' ).find( '.pr-item' ).each( function () {
				var role = $( this ).find( '.proler-roles' ).val();
				if ( role.length > 0 ) {
					data['roles'][role] = get_role_settings( $( this ) );
				}
			});

			return data;
		}
		function set_input_val( data ){
			if ( typeof data == 'object' && ! $.isEmptyObject( data ) ) {
				json_populate_input( 'proler_data', data );
			} else {
				$( 'input[name="proler_data"]' ).val( '' );
			}
		}

		function show_diable_msg( btn ){
			var input = btn.closest( '.mpcdp_settings_option_field' ).find( 'input[name="pr_enable"]' );

			if ( typeof input == 'undefined' || input.length == 0 ) {
				return;
			}

			btn.closest( '.mpcdp_row' ).find( '.prdis-msg' ).toggle( 'slow' );
		}
		function toggle_button( btn ){
			var wrap = btn.closest( '.hurkanSwitch-switch-box' );

			wrap.find( '.hurkanSwitch-switch-item' ).each( function () {
				if ( $( this ).hasClass( 'active' ) ) {
					$( this ).removeClass( 'active' );
				} else {
					$( this ).addClass( 'active' );
				}
			});

			btn.closest( '.mpcdp_settings_option_field' ).find( 'input[type="checkbox"]' ).trigger( 'click' );
			show_diable_msg( btn );
		}


		function checkRanges(){
			var allOK = true;

			$( '.pr-settings' ).find( '.pr-item' ).each( function () {
				var role = $( this ).find( '.proler-roles' ).val();
				if ( role.length > 0 ) {
					var row = $(this);
					row.find( '.disrange-item' ).each(function(){
						var min = $(this).find( 'input[name="min_value"]' ).val();
						var max = $(this).find( 'input[name="max_value"]' ).val();

						min = min ? parseFloat( min ) : 0;
						max = max ? parseFloat( max ) : 0;
						// console.log( min, max );

						if( min && min > max ){
							allOK = false;

							// console.log( 'min > max' );
							$([document.documentElement, document.body]).animate({
								scrollTop: $( '.discount-ranges-main' ).offset().top - 100
							}, 2000);

							alert( 'Range error: Minimum value cannot be more than maximum.' );
						}
					});
				}
			});

			return allOK;
		}



		$( '.pr-settings' ).on( 'click', '.hurkanSwitch-switch-item', function (e) {
			toggle_button( $( this ) );
		});

		$( '.pr-settings' ).on( 'click', '.proler-arrow img', function () {
			var item = $( this ).closest( '.pr-item' );

			if ( item.find( '.proler-option-content' ).is( ':visible' ) ) {
				$( this ).attr( 'src', proler.right_arrow );
			} else {
				$( this ).attr( 'src', proler.down_arrow );
			}

			item.find( '.proler-option-content' ).toggle( 'slow' );
		});

		$( '.pr-settings' ).on( 'click', '.proler-delete img', function () {
			$( this ).closest( '.pr-item' ).hide( 'slow', function () {
				$( this ).closest( '.pr-item' ).remove();
			});
		});

		$( '.mpc-opt-sc-btn.add-new' ).on( 'click', function () {
			$( '.pr-settings' ).append( '<div class="mpcdp_settings_toggle pr-item">' + $( '.demo-item' ).html() + '</div>' );
		});

		// init - frontend on load.
		if ( typeof $( '.pr-settings' ).find( '.pr-item' ) == 'undefined' || $( '.pr-settings' ).find( '.pr-item' ).length == 0 ) {
			$( '.mpc-opt-sc-btn.add-new' ).trigger( 'click' );
		}

		$( 'body' ).on( 'click', '.add-new-disrange', function(){
			var wrap = $(this).closest( '.discount-ranges-main' );
			wrap.find( '.discount-range-wrap' ).append( '<div class="mpcdp_row disrange-item">' + $( '.discount-range-demo' ).html() + '</div>' );
		});

		// init - frontend on load.
		$( '.discount-ranges-main' ).each(function(){
			var discount_ranges = $(this).find( '.discount-range-wrap .mpcdp_row' );

			if ( discount_ranges.length === 0 ) {
				$(this).find( '.add-new-disrange' ).trigger( 'click' );
			}
		});

		$( 'body' ).on( 'click', '.delete-disrange', function(){
			$(this).closest( '.disrange-item' ).hide( 'slow', function(){
				$(this).remove();
			});
		});

		// init - admin product edit page.
		if ( typeof $( 'input[name="proler_stype"]:checked' ).val() != 'undefined' ) {
			var val = $( 'input[name="proler_stype"]:checked' ).val();

			if ( val != 'proler-based' && $( '.role-settings-content' ).is( ':visible' ) ) {
				$( '.role-settings-content' ).hide();
			}
		}

		// delete user role.
		$( '.proler-delete-role' ).on( 'click', function(e){
			if( ! confirm( proler.delete_role_msg ) ){
				e.preventDefault();
			}
		});

		// single product update/save button clicked event
		$( 'input[type="submit"],button.mpcdp_submit_button' ).on( 'click', function (e) {
			if( checkRanges() === false ){
				e.preventDefault();
			}

			var data = get_settings();
			// console.log( data );

			set_input_val( data );
		});

		// disbale hide price text field until hide price toggle button is checked.
		$( '.pr-settings' ).on( 'click', 'input[name="hide_price"]', function(){
			var section = $(this).closest( '.mpcdp_settings_section' );
			var textarea = section.find( 'textarea' );

			if( $(this).is( ':checked' ) ){
				textarea.closest( '.mpcdp_row' ).find( '.mpcdp_settings_option_description' ).removeClass( 'disabled' );
				textarea.prop( 'disabled', false );
			}else{
				textarea.closest( '.mpcdp_row' ).find( '.mpcdp_settings_option_description' ).addClass( 'disabled' );
				textarea.prop( 'disabled', true );
			}
		});

		var expanded = false;
		$( 'body' ).find( '.pr-item' ).each(function(){
			// console.log( 'expanded?', expanded, 'item hidden?', $(this).find( '.proler-option-content' ).is( ':hidden' ) );
			if( expanded === false && $(this).find( '.proler-option-content' ).is( ':hidden' ) ){
				$(this).find( '.proler-arrow img' ).trigger( 'click' );
				expanded = true;
			}
		});
	});
})( jQuery );