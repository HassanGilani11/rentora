/* global mvr_admin_params */

jQuery(
	function ($) {
		'use strict';

		if (typeof mvr_admin_meta_boxes_vendor === 'undefined') {
			return false;
		}

		function is_blocked($node) {
			return $node.is( '.processing' ) || $node.parents( '.processing' ).length;
		}

		function block($node) {
			$.blockUI.defaults.overlayCSS.cursor = 'wait';

			if ( ! is_blocked( $node )) {
				$node.addClass( 'processing' ).block(
					{
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					}
				);
			}
		}

		function unblock($node) {
			$node.removeClass( 'processing' ).unblock();
		}

		/**
		 * Vendor Data Panel
		 */
		var mvr_vendor_desc_meta_box = {
			init: function () {
				$(
					function () {
						let editorWrapper = $( '#postdivrich' );

						if (editorWrapper.length) {
							editorWrapper.addClass( 'postbox mvr-vendor-description' );
							editorWrapper.prepend( '<h2 class="postbox-header"><label>' + mvr_admin_meta_boxes_vendor.vendor_description_title + '</label></h2>' );
						}
					}
				);
			},
		}

		/**
		 * Vendor Data Panel
		 */
		var mvr_meta_boxes_vendor = {
			$wrapper: $( '#mvr_vendor_data' ),
			init: function () {
				this.$wrapper.on( 'click', '.mvr_vendor_data_tabs li.active', this.activeTab )
				.find( '.mvr_vendor_data_tabs li.active' ).click();
			},

			activeTab( e ) {
				e.preventDefault();

				if ($( e.currentTarget ).is( '.profile_tab' )) {
					mvr_meta_boxes_vendor.profile.init();
				} else if ($( e.currentTarget ).is( '.address_tab' )) {
					mvr_meta_boxes_vendor.address.init();
				} else if ($( e.currentTarget ).is( '.commission_tab' )) {
					mvr_meta_boxes_vendor.commission.init();
				} else if ($( e.currentTarget ).is( '.withdraw_tab' )) {
					mvr_meta_boxes_vendor.withdraw.init();
				} else if ($( e.currentTarget ).is( '.payout_tab' )) {
					mvr_meta_boxes_vendor.payout.init();
				} else if ($( e.currentTarget ).is( '.payment_tab' )) {
					mvr_meta_boxes_vendor.payment.init();
				} else if ($( e.currentTarget ).is( '.staff_tab' )) {
					mvr_meta_boxes_vendor.staff.init();
				} else if ($( e.currentTarget ).is( '.capabilities_tab' )) {
					mvr_meta_boxes_vendor.capabilities.init();
				}
			},

			profile: {
				$wrapper: $( '#profile_vendor_data .options_group' ),
				init() {
					this.$wrapper.on( 'blur', '.mvr-shop', this.vendorShopValidation );
					this.$wrapper.on( 'blur', '.mvr-slug', this.vendorSlugValidation );
				},
				vendorShopValidation( e ) {
					e.preventDefault();

					let shopName = $( this ).val(),
					description  = $( this ).closest( 'p' ).find( 'span.description' ),
					vendorID     = $( this ).data( 'vendor_id' );

					description.html( '' );

					if (shopName.length < 3) {
						description.prepend( '<span class="mvr-error">' + mvr_admin_meta_boxes_vendor.min_char_shop_txt + '</span>' );
						return false;
					}

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_validate_vendor_shop',
								security: mvr_admin_meta_boxes_vendor.vendor_shop_nonce,
								shop_name: shopName,
								vendor_id: vendorID,
							},
							success( response ) {
								if (description.length) {
									description.html( '' );

									if ('available' === response.data.status) {
										description.append( '<span class="mvr-store-name-status mvr-store-name-available">' + mvr_admin_meta_boxes_vendor.shop_available_txt + '</span>' );
									} else {
										description.append( '<span class="mvr-store-name-status mvr-store-name-unavailable">' + mvr_admin_meta_boxes_vendor.shop_unavailable_txt + '</span>' );
									}
								}
							},
						}
					);
				},
				vendorSlugValidation( e ) {
					e.preventDefault();

					let slug    = $( this ).val(),
					description = $( this ).closest( 'p' ).find( 'span.description' ),
					vendorID    = $( this ).data( 'vendor_id' );

					if (description.find( 'span.mvr-error' ).length) {
						description.find( 'span.mvr-error' ).remove();
					}

					if (slug.length < 3) {
						if (description.find( 'span.mvr-store-slug-status' ).length) {
							description.find( 'span.mvr-store-slug-status' ).remove();
						}

						description.find( 'span.mvr-store-url' ).html( mvr_admin_meta_boxes_vendor.default_store_url );
						description.prepend( '<span class="mvr-error">' + mvr_admin_meta_boxes_vendor.min_char_slug_txt + '</span>' );
						vendorRegister.wrapper.find( '.mvr-become-vendor-submit' ).addClass( 'mvr-disabled' );

						return false;
					}

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_validate_vendor_slug',
								security: mvr_admin_meta_boxes_vendor.vendor_slug_nonce,
								slug: slug,
								vendor_id: vendorID,
							},
							success( response ) {
								if (description.length) {

									description.find( 'span.mvr-store-url' ).html( response.data.url );

									if (description.find( 'span.mvr-store-slug-status' ).length) {
										description.find( 'span.mvr-store-slug-status' ).remove();
									}

									if ('available' === response.data.status) {
										description.append( '<span class="mvr-store-slug-status mvr-store-slug-available">' + mvr_admin_meta_boxes_vendor.available_txt + '</span>' );
									} else {
										description.append( '<span class="mvr-store-slug-status mvr-store-slug-unavailable">' + mvr_admin_meta_boxes_vendor.unavailable_txt + '</span>' );
									}
								}
							},
						}
					);
				},
			},

			address: {
				states: null,
				$wrapper: $( '#address_vendor_data .options_group' ),
				init() {
					if (
					! (
						typeof mvr_admin_meta_boxes_vendor === 'undefined' ||
						typeof mvr_admin_meta_boxes_vendor.countries === 'undefined'
					)
				) {
					/* State/Country select boxes */
					this.states = JSON.parse( mvr_admin_meta_boxes_vendor.countries.replace( /&quot;/g, '"' ) );
					}

					this.$wrapper.find( '.js_field-country' ).selectWoo()
					.on( 'change', this.changeCountry );
					this.$wrapper.find( '.js_field-country' ).trigger( 'change', [true] )
					.on( 'change', 'select.js_field-state', this.changeState );
				},

				changeCountry( e, stickValue ) {
					// Check for stickValue before using it.
					if (typeof stickValue === 'undefined') {
						stickValue = false;
					}

					// Prevent if we don't have the metabox data.
					if (mvr_meta_boxes_vendor.address.states === null) {
						return;
					}

					var $this   = $( this ),
					country     = $this.val(),
					$state      = mvr_meta_boxes_vendor.address.$wrapper.find( ':input.js_field-state' ),
					$parent     = $state.parent(),
					stateValue  = $state.val(),
					input_name  = $state.attr( 'name' ),
					input_id    = $state.attr( 'id' ),
					value       = $this.data( 'woocommerce.stickState-' + country ) ? $this.data( 'woocommerce.stickState-' + country ) : stateValue,
					placeholder = $state.attr( 'placeholder' ),
					$newstate;

					if (stickValue) {
						$this.data( 'woocommerce.stickState-' + country, value );
					}

					// Remove the previous DOM element
					$parent.show().find( '.select2-container' ).remove();

					if ( ! $.isEmptyObject( mvr_meta_boxes_vendor.address.states[country] )) {
						var state      = mvr_meta_boxes_vendor.address.states[country],
						$defaultOption = $( '<option value=""></option>' )
							.text( mvr_admin_meta_boxes_vendor.i18n_select_state_text );

						$newstate = $( '<select></select>' )
						.prop( 'id', input_id )
						.prop( 'name', input_name )
						.prop( 'placeholder', placeholder )
						.addClass( 'js_field-state select short' )
						.append( $defaultOption );

						$.each(
							state,
							function (index) {
								var $option = $( '<option></option>' )
								.prop( 'value', index )
								.text( state[index] );
								if (index === stateValue) {
									$option.prop( 'selected' );
								}
								$newstate.append( $option );
							}
						);

						$newstate.val( value );

						$state.replaceWith( $newstate );

						$newstate.show().selectWoo().hide().trigger( 'change' );
					} else {
						$newstate = $( '<input type="text" />' )
						.prop( 'id', input_id )
						.prop( 'name', input_name )
						.prop( 'placeholder', placeholder )
						.addClass( 'js_field-state' )
						.val( stateValue );
						$state.replaceWith( $newstate );
					}

					$( document.body ).trigger( 'country-change.vendor', [country, $( this ).closest( 'div' )] );
				},

				changeState: function () {
					// Here we will find if state value on a select has changed and stick it to the country data
					var $this = $( this ),
					state     = $this.val(),
					$country  = mvr_meta_boxes_vendor.address.$wrapper.find( ':input.js_field-country' ),
					country   = $country.val();

					$country.data( 'woocommerce.stickState-' + country, state );
				},

			},

			commission: {
				$wrapper: $( '#commission_vendor_data .options_group' ),
				init() {
					this.$wrapper.on( 'change', '.mvr-vendor-commission-from', this.changeCommissionFrom )
					.find( '.mvr-vendor-commission-from' ).trigger( 'change' );
					this.$wrapper.on( 'change', '.mvr-commission-criteria', this.changeCommissionCriteria )
					.find( '.mvr-commission-criteria' ).trigger( 'change' );
				},

				changeCommissionFrom( e ) {
					e.preventDefault();
					let commissionField = mvr_meta_boxes_vendor.commission.$wrapper.find( '.mvr-commission-field' );

					if ('1' === $( this ).val()) {
						commissionField.closest( 'p' ).hide();
					} else {
						commissionField.closest( 'p' ).show();
						mvr_meta_boxes_vendor.commission.$wrapper.find( '.mvr-commission-criteria' ).trigger( 'change' );
					}
				},

				changeCommissionCriteria( e ) {
					e.preventDefault();

					let commissionCriteriaField = mvr_meta_boxes_vendor.commission.$wrapper.find( '.mvr-commission-criteria-field' );

					if ('1' === $( this ).val()) {
						commissionCriteriaField.closest( 'p' ).hide();
					} else {
						commissionCriteriaField.closest( 'p' ).show();
					}
				}
			},

			withdraw: {
				$wrapper: $( '#withdraw_vendor_data .options_group' ),
				init() {
					this.$wrapper.on( 'change', '.mvr-withdraw-from', this.changeWithdrawFrom )
					.find( '.mvr-withdraw-from' ).trigger( 'change' );
					this.$wrapper.on( 'change', '.mvr-enable-withdraw-charge', this.enableWithdrawCharge )
				},

				changeWithdrawFrom( e ) {
					e.preventDefault();
					let withdrawField = mvr_meta_boxes_vendor.withdraw.$wrapper.find( '.mvr-withdraw-field' );

					if ('1' === $( this ).val()) {
						withdrawField.closest( 'p' ).hide();
					} else {
						withdrawField.closest( 'p' ).show();
						mvr_meta_boxes_vendor.withdraw.$wrapper.find( '.mvr-enable-withdraw-charge' ).trigger( 'change' );
					}
				},

				enableWithdrawCharge( e ) {
					if ($( this ).is( ':checked' )) {
						mvr_meta_boxes_vendor.withdraw.$wrapper.find( '.mvr-withdraw-charge-field' ).closest( 'p' ).show()
					} else {
						mvr_meta_boxes_vendor.withdraw.$wrapper.find( '.mvr-withdraw-charge-field' ).closest( 'p' ).hide()
					}
				},
			},

			payment: {
				$wrapper: $( '#payment_vendor_data .options_group' ),
				init() {
					this.$wrapper.on( 'change', '.mvr-payment-method', this.changePaymentMethod )
					.find( '.mvr-payment-method' ).change();
				},

				changePaymentMethod( e ) {
					if ('2' === $( this ).val()) {
						mvr_meta_boxes_vendor.payment.$wrapper.find( '.mvr-bank-payment-field' ).closest( 'p' ).hide();
						mvr_meta_boxes_vendor.payment.$wrapper.find( '.mvr-paypal-payment-field' ).closest( 'p' ).show();
					} else {
						mvr_meta_boxes_vendor.payment.$wrapper.find( '.mvr-bank-payment-field' ).closest( 'p' ).show();
						mvr_meta_boxes_vendor.payment.$wrapper.find( '.mvr-paypal-payment-field' ).closest( 'p' ).hide();
					}
				}
			},

			payout: {
				$wrapper: $( '#payout_vendor_data .options_group' ),
				init() {
					this.$wrapper.on( 'change', '.mvr-payout-type', this.changePayoutType )
					.find( '.mvr-payout-type' ).change();
				},

				changePayoutType( e ) {
					if ('2' === $( this ).val()) {
						mvr_meta_boxes_vendor.payout.$wrapper.find( '.mvr-auto-payout-field' ).closest( 'p' ).show();
					} else {
						mvr_meta_boxes_vendor.payout.$wrapper.find( '.mvr-auto-payout-field' ).closest( 'p' ).hide();
					}
				},
			},

			staff: {
				$wrapper: $( '#staff_vendor_data' ),
				init() {
					this.$wrapper.on( 'click', '.mvr-add-staff', this.addStaff );
					this.$wrapper.on( 'click', '.mvr-remove-staff', this.removeStaff );
				},

				addStaff( e ) {
					e.preventDefault();
					var $this = $( e.currentTarget ),
					staffID   = mvr_meta_boxes_vendor.staff.$wrapper.find( '.mvr-vendor-staff' ).val(),
					vendorID  = mvr_meta_boxes_vendor.staff.$wrapper.find( '.mvr-vendor-id' ).val();

					block( $( $this ).closest( 'div.mvr-vendor-staff-add' ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_add_vendor_staff',
								staff_id: staffID,
								vendor_id: vendorID,
								security: mvr_admin_meta_boxes_vendor.add_vendor_staff_nonce
							},
							success( response ) {

								unblock( $( $this ).closest( 'div.mvr-vendor-staff-add' ) );

								if (response.success) {
									mvr_meta_boxes_vendor.staff.$wrapper.find( '.mvr-vendor-staff' ).val( '' );
									mvr_meta_boxes_vendor.staff.$wrapper.find( '.mvr-vendor-staff-list' ).append( response.data.html );
									mvr_meta_boxes_vendor.staff.$wrapper.find( '.mvr-no-staff-data' ).remove();
								}
							}
						}
					);
				},

				removeStaff( e ) {
					e.preventDefault();
					var $this = $( e.currentTarget ),
					staffID   = $( this ).data( 'staff_id' ),
					vendorID  = mvr_meta_boxes_vendor.staff.$wrapper.find( '.mvr-vendor-id' ).val();

					block( $( $this ).closest( 'div.mvr-vendor-staff-data' ) );

					$.ajax(
						{
							type: 'POST',
							url: ajaxurl,
							dataType: 'json',
							data: {
								action: 'mvr_remove_vendor_staff',
								staff_id: staffID,
								vendor_id: vendorID,
								security: mvr_admin_meta_boxes_vendor.remove_vendor_staff_nonce
							},
							success( response ) {
								block( $( $this ).closest( 'div.mvr-vendor-staff-data' ) );

								if (response.success) {
									$( $this ).closest( 'div.mvr-vendor-staff-data' ).remove();

									if ('' !== response.data.html) {
										mvr_meta_boxes_vendor.staff.$wrapper.find( '.mvr-vendor-staff-list' ).append( response.data.html );
									}
								}
							}
						}
					);
				},
			},

			capabilities: {
				$wrapper: $( '#capabilities_vendor_data' ),
				init() {
					this.$wrapper.on( 'change', '.mvr-enable-product-management', this.enableProductManagement )
					.find( '.mvr-enable-product-management' ).change();

					this.$wrapper.on( 'change', '.mvr-enable-order-management', this.enableOrderManagement )
					.find( '.mvr-enable-order-management' ).change();

					this.$wrapper.on( 'change', '.mvr-enable-coupon-management', this.enableCouponManagement )
					.find( '.mvr-enable-coupon-management' ).change();

					this.$wrapper.on( 'change', '.mvr-enable-withdraw-management', this.enableWithdrawManagement )
					.find( '.mvr-enable-withdraw-management' ).change();
				},

				enableProductManagement( e ){
					e.preventDefault();

					if (true === $( this ).is( ':checked' )) {
						$( '.mvr-product-management-field' ).closest( 'p' ).show();
					} else {
						$( '.mvr-product-management-field' ).closest( 'p' ).hide();
					}
				},

				enableOrderManagement( e ){
					e.preventDefault();

					if (true === $( this ).is( ':checked' )) {
						$( '.mvr-order-management-field' ).closest( 'p' ).show();
					} else {
						$( '.mvr-order-management-field' ).closest( 'p' ).hide();
					}
				},

				enableCouponManagement( e ){
					e.preventDefault();

					if (true === $( this ).is( ':checked' )) {
						$( '.mvr-coupon-management-field' ).closest( 'p' ).show();
					} else {
						$( '.mvr-coupon-management-field' ).closest( 'p' ).hide();
					}
				},

				enableWithdrawManagement( e ){
					e.preventDefault();

					if (true === $( this ).is( ':checked' )) {
						$( '.mvr-withdraw-management-field' ).closest( 'p' ).show();
					} else {
						$( '.mvr-withdraw-management-field' ).closest( 'p' ).hide();
					}
				},
			},
		};

		var mvr_store_banner_meta_box = {
			$wrapper: $( '#mvr_store_banner' ),
			init: function () {
				this.$wrapper.on( 'click', 'a.mvr-add-store-banner', this.addStoreBanner )
				.on( 'click', 'a.mvr-remove-store-banner', this.removeStoreBanner )
			},

			addStoreBanner( e ) {
				e.preventDefault();
				var image_uploader;

				if (image_uploader) {
					image_uploader.open();
					return;
				}

				image_uploader = wp.media.frames.file_frame = wp.media(
					{
						title: mvr_admin_meta_boxes_vendor.choose_image_title,
						button: { text: mvr_admin_meta_boxes_vendor.add_image_title },
						multiple: false
					}
				);

			image_uploader.on(
				'select',
				function () {
					var attachment = image_uploader.state().get( 'selection' ).first().toJSON();

					$( '.mvr-vendor-banner-container' ).find( 'img' ).remove();
					$( '.mvr-vendor-banner-container' ).append( '<img src="' + attachment.url + '" width="800" height="200" />' );
					$( '.mvr-vendor-banner-container' ).find( '.mvr-vendor-banner-id' ).val( attachment.id );

					if ($( '.mvr-vendor-banner-container' ).find( 'img' ).length > 0) {
						$( 'a.mvr-add-store-banner' ).hide();
						$( 'a.mvr-remove-store-banner' ).show();
					} else {
						$( 'a.mvr-add-store-banner' ).show();
						$( 'a.mvr-remove-store-banner' ).hide();
					}
				}
			);

			image_uploader.open();
			},

			removeStoreBanner( e ) {
				e.preventDefault();
				$( '.mvr-vendor-banner-container' ).find( 'img' ).remove();
				$( '.mvr-vendor-banner-container' ).find( '.mvr-vendor-banner-id' ).val( '' );
				$( 'a.mvr-add-store-banner' ).show();
				$( this ).hide();
			},
		};

		mvr_vendor_desc_meta_box.init();
		mvr_meta_boxes_vendor.init();
		mvr_store_banner_meta_box.init();
	}
);
