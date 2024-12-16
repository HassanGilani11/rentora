/* global mvr_register_params */

jQuery(
	function ($) {
		'use strict';

		// mvr_register_params is required to continue, ensure the object exists.
		if (typeof mvr_register_params === 'undefined') {
			return false;
		}

		var is_blocked = function ($node) {
			return $node.is('.processing') || $node.parents('.processing').length;
		};

		var block = function ($node) {
			$.blockUI.defaults.overlayCSS.cursor = 'wait';

			if (!is_blocked($node)) {
				$node.addClass('processing').block(
					{
						message: null,
						overlayCSS: {
							background: '#fff',
							opacity: 0.6
						}
					}
				);
			}
		};

		var unblock = function ($node) {
			$node.removeClass('processing').unblock();
		};

		var formatUrl = function (url) {
			if (-1 === url.indexOf('https://') || -1 === url.indexOf('http://')) {
				return url;
			} else {
				return decodeURI(url);
			}
		}

		var vendorRegister = {
			becomeVendor: $('div.mvr-become-vendor-wrapper'),
			registerForm: $('.mvr-vendor-register-form-wrapper'),
			dashboard: $('div.mvr-dashboard-content'),
			init() {
				if (0 !== this.becomeVendor.length) {
					// Become Vendor Button.
					if (this.becomeVendor.find('.mvr-become-vendor-button-wrapper').length) {
						this.becomeVendor.find('.mvr-become-vendor-button-wrapper').on('click', 'button.mvr-become-vendor-btn', this.becomeVendorFormToggle);
					}

					// Become Vendor Form.
					if (this.becomeVendor.find('.mvr-become-vendor-form-wrapper').length) {
						this.becomeVendor.find('.mvr-become-vendor-form-wrapper').on('blur', '.mvr-required-field', this.validateVendorSubmit);
						this.becomeVendor.find('.mvr-become-vendor-form-wrapper').on('blur', 'input.mvr-slug', this.vendorSlugValidation);
						this.becomeVendor.find('.mvr-become-vendor-form-wrapper').on('blur', 'input.mvr-shop', this.vendorShopValidation);
						this.becomeVendor.find('.mvr-become-vendor-form-wrapper').on('change', '.mvr-tac-cb', this.termsAndConditionsToggle);
						this.becomeVendor.find('.mvr-become-vendor-form-wrapper').on('change', '.mvr-privacy-cb', this.privacyPolicyToggle);
						this.becomeVendor.find('.mvr-become-vendor-form-wrapper').on('click', 'p.mvr-form-submit', this.vendorRegisterSubmit);
					}
				}

				if (0 !== this.registerForm.length) {
					this.registerForm.on('blur', '.mvr-required-field', this.validateVendorSubmit);
					this.registerForm.on('blur', 'input.mvr-shop', this.vendorShopValidation);
					this.registerForm.on('blur', 'input.mvr-slug', this.vendorSlugValidation);
					this.registerForm.on('change', '.mvr-tac-cb', this.termsAndConditionsToggle);
					this.registerForm.on('change', '.mvr-privacy-cb', this.privacyPolicyToggle);
					this.registerForm.on('click', 'p.mvr-form-submit', this.vendorRegisterSubmit);
				}

				if (0 !== this.dashboard.length) {
					// Profile Tab.
					if (this.dashboard.find('.mvr-profile-form-wrapper').length) {
						this.dashboard.find('.mvr-profile-form-wrapper').on('blur', 'input.mvr-slug', this.vendorSlugValidation);
						this.dashboard.find('.mvr-profile-form-wrapper').on('blur', 'input.mvr-shop', this.vendorShopValidation);
					}
				}
			},

			validateVendorSubmit() {
				let wrapper, display = true;

				if (vendorRegister.registerForm.length > 0) {
					wrapper = vendorRegister.registerForm;
				} else if (vendorRegister.becomeVendor.length > 0) {
					wrapper = vendorRegister.becomeVendor;
				}

				if (null === wrapper || '' === wrapper || undefined === wrapper) {
					return false;
				}

				if (wrapper.length > 0) {
					wrapper.find('.mvr-required-field').filter(':visible').each(
						function () {
							if ('checkbox' === $(this).attr('type')) {
								if (false === $(this).is(':checked')) {
									display = false;
								} else {
									$(this).removeClass('mvr-form-field-empty');
								}
							} else {
								if ('' === $(this).val() || null === $(this).val()) {
									display = false;
								} else {
									if ($(this).hasClass('mvr-form-field-empty') && (!$(this).hasClass('mvr-slug') || !$(this).hasClass('mvr-shop'))) {
										$(this).removeClass('mvr-form-field-empty');
									}
								}
							}

							if ($(this).hasClass('mvr-form-field-empty')) {
								display = false;
							}
						}
					);

					if (true === display) {
						wrapper.find('.mvr-vendor-register-submit').removeClass('mvr-disabled');
					} else {
						wrapper.find('.mvr-vendor-register-submit').addClass('mvr-disabled');
					}
				}
			},

			termsAndConditionsToggle(e) {
				e.preventDefault();

				if ($(this).is(':checked')) {
					$(this).val('yes');
				} else {
					$(this).val('');
				}

				vendorRegister.validateVendorSubmit();
			},

			privacyPolicyToggle(e) {
				e.preventDefault();

				if ($(this).is(':checked')) {
					$(this).val('yes');
				} else {
					$(this).val('');
				}

				vendorRegister.validateVendorSubmit();
			},

			vendorRegisterSubmit(e) {
				if ($(this).find('.mvr-form-vendor-register__submit').hasClass('mvr-disabled')) {
					e.preventDefault();
					let wrapper;

					if (vendorRegister.registerForm.length > 0) {
						wrapper = vendorRegister.registerForm;
					} else if (vendorRegister.becomeVendor.length > 0) {
						wrapper = vendorRegister.becomeVendor;
					}

					if (null === wrapper || '' === wrapper) {
						return false;
					}

					if (wrapper.length > 0) {
						wrapper.find('.mvr-required-field').filter(':visible').each(
							function () {
								if ('checkbox' === $(this).attr('type')) {
									if (false === $(this).is(':checked')) {
										$(this).addClass('mvr-form-field-empty');
									}
								} else {
									if ('' === $(this).val() || null === $(this).val()) {
										$(this).addClass('mvr-form-field-empty');
									}
								}
							}
						);
					}

					vendorRegister.validateVendorSubmit();
				}
			},

			becomeVendorFormToggle(e) {
				e.preventDefault();

				block($(this));
				vendorRegister.becomeVendor.find('div.mvr-become-vendor-form-wrapper').toggle();
				unblock($(this));
			},

			vendorShopValidation(e) {
				e.preventDefault();

				var $this = $(e.currentTarget),
					shopName = $(this).val(),
					description = $(this).closest('p').find('span.mvr-description'),
					vendorID = $(this).data('vendor_id'),
					wrapper;

				if (vendorRegister.registerForm.length > 0) {
					wrapper = vendorRegister.registerForm;
				} else if (vendorRegister.becomeVendor.length > 0) {
					wrapper = vendorRegister.becomeVendor;
				}

				description.html('');

				if (shopName.length < 3) {
					description.prepend('<span class="mvr-error">' + mvr_register_params.min_char_shop_txt + '</span>');
					return false;
				}

				$.ajax(
					{
						type: 'POST',
						url: mvr_register_params.ajax_url,
						dataType: 'json',
						data: {
							action: 'mvr_validate_vendor_shop',
							security: mvr_register_params.vendor_shop_nonce,
							shop_name: shopName,
							vendor_id: vendorID,
						},
						success(response) {
							if (description.length) {
								description.html('');

								if ('available' === response.data.status) {
									if ($($this).hasClass('mvr-form-field-empty')) {
										$($this).removeClass('mvr-form-field-empty');
									}

									description.append('<span class="mvr-store-name-status mvr-store-name-available">' + mvr_register_params.shop_available_txt + '</span>');
									vendorRegister.validateVendorSubmit();
								} else {
									description.append('<span class="mvr-store-name-status mvr-store-name-unavailable">' + mvr_register_params.shop_unavailable_txt + '</span>');

									if (vendorRegister.becomeVendor.length > 0) {
										vendorRegister.becomeVendor.find('.mvr-become-vendor-submit').addClass('mvr-disabled');
									} else if (vendorRegister.registerForm.length > 0) {
										vendorRegister.registerForm.find('.mvr-form-vendor-register__submit').addClass('mvr-disabled');
									}

									$($this).addClass('mvr-form-field-empty');
								}
							}
						},
					}
				);
			},

			vendorSlugValidation(e) {
				e.preventDefault();

				var $this = $(e.currentTarget),
					slug = $(this).val(),
					description = $(this).closest('p').find('span.mvr-description'),
					vendorID = $(this).data('vendor_id'),
					wrapper;

				if (vendorRegister.registerForm.length > 0) {
					wrapper = vendorRegister.registerForm;
				} else if (vendorRegister.becomeVendor.length > 0) {
					wrapper = vendorRegister.becomeVendor;
				}

				if (description.find('span.mvr-error').length) {
					description.find('span.mvr-error').remove();
				}

				if (slug.length < 3) {
					if (description.find('span.mvr-store-slug-status').length) {
						description.find('span.mvr-store-slug-status').remove();
					}

					description.find('span.mvr-store-url').html(mvr_register_params.default_store_url);
					description.prepend('<span class="mvr-error">' + mvr_register_params.min_char_slug_txt + '</span>');

					return false;
				}

				$.ajax(
					{
						type: 'POST',
						url: mvr_register_params.ajax_url,
						dataType: 'json',
						data: {
							action: 'mvr_validate_vendor_slug',
							security: mvr_register_params.vendor_slug_nonce,
							slug: slug,
							vendor_id: vendorID,
						},
						success(response) {
							if (description.length) {
								description.find('span.mvr-store-url').html(response.data.url);

								if (description.find('span.mvr-store-slug-status').length) {
									description.find('span.mvr-store-slug-status').remove();
								}

								if ('available' === response.data.status) {
									if ($($this).hasClass('mvr-form-field-empty')) {
										$($this).removeClass('mvr-form-field-empty');
									}

									description.append('<span class="mvr-store-slug-status mvr-store-slug-available">' + mvr_register_params.available_txt + '</span>');
									vendorRegister.validateVendorSubmit();
								} else {
									description.append('<span class="mvr-store-slug-status mvr-store-slug-unavailable">' + mvr_register_params.unavailable_txt + '</span>');

									if (vendorRegister.becomeVendor.length > 0) {
										vendorRegister.becomeVendor.find('.mvr-become-vendor-submit').addClass('mvr-disabled');
									} else if (vendorRegister.registerForm.length > 0) {
										vendorRegister.registerForm.find('.mvr-form-vendor-register__submit').addClass('mvr-disabled');
									}

									$($this).addClass('mvr-form-field-empty');
								}
							}
						},
					}
				);
			},
		};

		vendorRegister.init();
	}
);
