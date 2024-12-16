<?php
/**
 * Vendor Data
 *
 * Displays the Vendor data box, tabbed, with several panels covering commission, payment etc.
 *
 * @package  Multi-Vendor\Admin\Meta Boxes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MVR_Meta_Box_Vendor_Data' ) ) {
	/**
	 * MVR_Meta_Box_Vendor_Data Class.
	 */
	class MVR_Meta_Box_Vendor_Data {

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output( $post ) {
			global $post, $vendor_obj;

			if ( ! is_object( $vendor_obj ) ) {
				$vendor_obj = new MVR_Vendor( $post->ID );
			}

			wp_nonce_field( 'mvr_save_data', 'mvr_save_meta_nonce' );

			include __DIR__ . '/views/html-vendor-data-panel.php';
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_action( $post ) {
			global $post, $vendor_obj;

			if ( ! is_object( $vendor_obj ) ) {
				$vendor_obj = new MVR_Vendor( $post->ID );
			}

			include 'views/html-vendor-action.php';
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_tac( $post ) {
			global $post, $vendor_obj;

			if ( ! is_object( $vendor_obj ) ) {
				$vendor_obj = new MVR_Vendor( $post->ID );
			}

			wp_editor(
				htmlspecialchars_decode( $vendor_obj->get_tac(), ENT_QUOTES ),
				'excerpt',
				array(
					'textarea_name' => 'excerpt',
				)
			);
		}

		/**
		 * Output of the vendor amount.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_vendor_amount( $post ) {
			global $post, $vendor_obj;

			if ( ! is_object( $vendor_obj ) ) {
				$vendor_obj = new MVR_Vendor( $post->ID );
			}

			?>
			<div class="mvr-vendor-amount-wrapper">
				<p class="mvr-total-amount-wrapper">
					<label><?php esc_html_e( 'Total Amount', 'multi-vendor-marketplace' ); ?></label>
					<?php echo wp_kses_post( wc_price( $vendor_obj->get_total_amount() ) ); ?>
				</p>
				<p class="mvr-available-amount-wrapper">
					<label><?php esc_html_e( 'Available Amount', 'multi-vendor-marketplace' ); ?></label>
					<?php echo wp_kses_post( wc_price( $vendor_obj->get_amount() ) ); ?>
				</p>
				<p class="mvr-locked-amount-wrapper">
					<label><?php esc_html_e( 'Locked Amount', 'multi-vendor-marketplace' ); ?></label>
					<?php echo wp_kses_post( wc_price( $vendor_obj->get_locked_amount() ) ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_note( $post ) {
			global $post, $vendor_obj;

			if ( ! is_object( $vendor_obj ) ) {
				$vendor_obj = new MVR_Vendor( $post->ID );
			}

			$notes = ( 'auto-draft' !== $post->post_status ) ? mvr_get_vendor_notes( array( 'vendor_id' => $vendor_obj->get_id() ) ) : array();

			include 'views/html-vendor-notes.php';
			?>
			<div class="mvr-add-note">
				<p class="mvr-note-area">
					<label for="mvr_add_vendor_note"><?php esc_html_e( 'Add note: ', 'multi-vendor-marketplace' ); ?> <?php echo wp_kses_post( wc_help_tip( esc_html__( 'Add a note for your reference, or add a customer note.', 'multi-vendor-marketplace' ) ) ); ?></label>
					<textarea type="text" name="_vendor_note" id="mvr_add_vendor_note" class="input-text" cols="20" rows="10"></textarea>
				</p>
				<p class="mvr-note-actions">
					<button type="button" class="mvr-add-vendor-note button"><?php esc_html_e( 'Add', 'multi-vendor-marketplace' ); ?></button>
				</p>
			</div>
			<?php
		}

		/**
		 * Output the metabox.
		 *
		 * @since 1.0.0
		 * @param WP_Post $post Post object.
		 */
		public static function output_banner( $post ) {
			global $post, $vendor_obj;

			if ( ! is_object( $vendor_obj ) ) {
				$vendor_obj = new MVR_Vendor( $post->ID );
			}

			$banner = $vendor_obj->get_banner_id() ? wp_get_attachment_url( $vendor_obj->get_banner_id() ) : MVR_PLUGIN_URL . '/assets/images/placeholder-800x400.png';

			include 'views/html-store-banner.php';
		}

		/**
		 * Show tab content/settings.
		 *
		 * @since 1.0.0
		 */
		public static function output_tabs() {
			global $post, $vendor_id, $vendor_obj;

			include __DIR__ . '/views/vendor-tab/html-vendor-data-profile.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-address.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-social-links.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-commission.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-payment.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-withdraw.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-payout.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-staff.php';
			include __DIR__ . '/views/vendor-tab/html-vendor-data-capability.php';
		}

		/**
		 * Return array of tabs to show.
		 *
		 * @since 1.0.0
		 * @return Array
		 */
		public static function get_vendor_data_tabs() {
			/**
			 * Vendor Data Tab.
			 *
			 * @since 1.0.0
			 */
			$tabs = apply_filters(
				'mvr_vendor_data_tabs',
				array(
					'profile'      => array(
						'label'    => __( 'Profile', 'multi-vendor-marketplace' ),
						'target'   => 'profile_vendor_data',
						'class'    => array(),
						'priority' => 10,
					),
					'address'      => array(
						'label'    => __( 'Address', 'multi-vendor-marketplace' ),
						'target'   => 'address_vendor_data',
						'class'    => array(),
						'priority' => 20,
					),
					'social_link'  => array(
						'label'    => __( 'Social Links', 'multi-vendor-marketplace' ),
						'target'   => 'social_link_vendor_data',
						'class'    => array(),
						'priority' => 30,
					),
					'commission'   => array(
						'label'    => __( 'Commission', 'multi-vendor-marketplace' ),
						'target'   => 'commission_vendor_data',
						'class'    => array(),
						'priority' => 40,
					),
					'payment'      => array(
						'label'    => __( 'Payment', 'multi-vendor-marketplace' ),
						'target'   => 'payment_vendor_data',
						'class'    => array(),
						'priority' => 50,
					),
					'withdraw'     => array(
						'label'    => __( 'Withdraw', 'multi-vendor-marketplace' ),
						'target'   => 'withdraw_vendor_data',
						'class'    => array(),
						'priority' => 60,
					),
					'payout'       => array(
						'label'    => __( 'Payout', 'multi-vendor-marketplace' ),
						'target'   => 'payout_vendor_data',
						'class'    => array(),
						'priority' => 70,
					),
					'staff'        => array(
						'label'    => __( 'Staff', 'multi-vendor-marketplace' ),
						'target'   => 'staff_vendor_data',
						'class'    => array(),
						'priority' => 80,
					),
					'capabilities' => array(
						'label'    => __( 'Capabilities', 'multi-vendor-marketplace' ),
						'target'   => 'capabilities_vendor_data',
						'class'    => array(),
						'priority' => 100,
					),
				)
			);

			// Sort tabs based on priority.
			uasort( $tabs, array( __CLASS__, 'vendor_data_tabs_sort' ) );

			return $tabs;
		}

		/**
		 * Callback to sort product data tabs on priority.
		 *
		 * @since 1.0.0
		 * @param Integer $a First item.
		 * @param Integer $b Second item.
		 * @return Boolean
		 */
		private static function vendor_data_tabs_sort( $a, $b ) {
			if ( ! isset( $a['priority'], $b['priority'] ) ) {
				return -1;
			}

			if ( $a['priority'] === $b['priority'] ) {
				return 0;
			}

			return $a['priority'] < $b['priority'] ? -1 : 1;
		}

		/**
		 * Save meta box data.
		 *
		 * @since 1.0.0
		 * @param Integer $post_id Post ID.
		 * @param WP_Post $post Post object.
		 * @param Array   $posted Posted Data.
		 * @throws WC_Data_Exception When invalid data is returned.
		 */
		public static function save( $post_id, $post, $posted ) {
			try {
				$vendor_args = array(
					'name'                           => isset( $posted['post_title'] ) ? wp_unslash( $posted['post_title'] ) : '',
					'logo_id'                        => isset( $posted['_thumbnail_id'] ) ? wp_unslash( $posted['_thumbnail_id'] ) : '',
					'banner_id'                      => isset( $posted['_banner_id'] ) ? wp_unslash( $posted['_banner_id'] ) : '',
					'shop_name'                      => isset( $posted['_shop_name'] ) ? wp_unslash( $posted['_shop_name'] ) : '',
					'slug'                           => isset( $posted['_slug'] ) ? sanitize_title( $posted['_slug'] ) : '',
					'description'                    => isset( $posted['post_content'] ) ? wp_unslash( $posted['post_content'] ) : '',
					'tac'                            => isset( $posted['post_excerpt'] ) ? wp_unslash( $posted['post_excerpt'] ) : '',
					'first_name'                     => isset( $posted['_first_name'] ) ? wp_unslash( $posted['_first_name'] ) : '',
					'last_name'                      => isset( $posted['_last_name'] ) ? wp_unslash( $posted['_last_name'] ) : '',
					'address1'                       => isset( $posted['_address1'] ) ? wp_unslash( $posted['_address1'] ) : '',
					'address2'                       => isset( $posted['_address2'] ) ? wp_unslash( $posted['_address2'] ) : '',
					'city'                           => isset( $posted['_city'] ) ? wp_unslash( $posted['_city'] ) : '',
					'state'                          => isset( $posted['_state'] ) ? wp_unslash( $posted['_state'] ) : '',
					'country'                        => isset( $posted['_country'] ) ? wp_unslash( $posted['_country'] ) : '',
					'zip_code'                       => isset( $posted['_zip_code'] ) ? wp_unslash( $posted['_zip_code'] ) : '',
					'phone'                          => isset( $posted['_phone'] ) ? wp_unslash( $posted['_phone'] ) : '',
					'facebook'                       => isset( $posted['_facebook'] ) ? esc_url_raw( wp_unslash( $posted['_facebook'] ) ) : '',
					'twitter'                        => isset( $posted['_twitter'] ) ? esc_url_raw( wp_unslash( $posted['_twitter'] ) ) : '',
					'youtube'                        => isset( $posted['_youtube'] ) ? esc_url_raw( wp_unslash( $posted['_youtube'] ) ) : '',
					'instagram'                      => isset( $posted['_instagram'] ) ? esc_url_raw( wp_unslash( $posted['_instagram'] ) ) : '',
					'linkedin'                       => isset( $posted['_linkedin'] ) ? esc_url_raw( wp_unslash( $posted['_linkedin'] ) ) : '',
					'pinterest'                      => isset( $posted['_pinterest'] ) ? esc_url_raw( wp_unslash( $posted['_pinterest'] ) ) : '',
					'commission_from'                => isset( $posted['_commission_from'] ) ? wp_unslash( $posted['_commission_from'] ) : '',
					'commission_criteria'            => isset( $posted['_commission_criteria'] ) ? wp_unslash( $posted['_commission_criteria'] ) : '',
					'commission_criteria_value'      => isset( $posted['_commission_criteria_value'] ) ? wp_unslash( $posted['_commission_criteria_value'] ) : '',
					'commission_type'                => isset( $posted['_commission_type'] ) ? wp_unslash( $posted['_commission_type'] ) : '',
					'commission_value'               => isset( $posted['_commission_value'] ) ? wp_unslash( $posted['_commission_value'] ) : '',
					'tax_to'                         => isset( $posted['_tax_to'] ) ? wp_unslash( $posted['_tax_to'] ) : '',
					'commission_after_coupon'        => isset( $posted['_commission_after_coupon'] ) ? wp_unslash( $posted['_commission_after_coupon'] ) : '',
					'commission_after_vendor_coupon' => isset( $posted['_commission_after_vendor_coupon'] ) ? wp_unslash( $posted['_commission_after_vendor_coupon'] ) : '',
					'payment_method'                 => isset( $posted['_payment_method'] ) ? wp_unslash( $posted['_payment_method'] ) : '',
					'bank_account_name'              => isset( $posted['_bank_account_name'] ) ? wp_unslash( $posted['_bank_account_name'] ) : '',
					'bank_account_number'            => isset( $posted['_bank_account_number'] ) ? wp_unslash( $posted['_bank_account_number'] ) : '',
					'bank_account_type'              => isset( $posted['_bank_account_type'] ) ? wp_unslash( $posted['_bank_account_type'] ) : '',
					'bank_name'                      => isset( $posted['_bank_name'] ) ? wp_unslash( $posted['_bank_name'] ) : '',
					'iban'                           => isset( $posted['_iban'] ) ? wp_unslash( $posted['_iban'] ) : '',
					'swift'                          => isset( $posted['_swift'] ) ? wp_unslash( $posted['_swift'] ) : '',
					'paypal_email'                   => isset( $posted['_paypal_email'] ) ? wp_unslash( $posted['_paypal_email'] ) : '',
					'payout_type'                    => isset( $posted['_payout_type'] ) ? wp_unslash( $posted['_payout_type'] ) : '',
					'payout_schedule'                => isset( $posted['_payout_schedule'] ) ? wp_unslash( $posted['_payout_schedule'] ) : '',
					'enable_withdraw_charge'         => isset( $posted['_enable_withdraw_charge'] ) ? wp_unslash( $posted['_enable_withdraw_charge'] ) : '',
					'withdraw_charge_type'           => isset( $posted['_withdraw_charge_type'] ) ? wp_unslash( $posted['_withdraw_charge_type'] ) : '',
					'withdraw_charge_value'          => isset( $posted['_withdraw_charge_value'] ) ? wp_unslash( $posted['_withdraw_charge_value'] ) : '',
					'enable_product_management'      => isset( $posted['_enable_product_management'] ) ? wp_unslash( $posted['_enable_product_management'] ) : '',
					'product_creation'               => isset( $posted['_product_creation'] ) ? wp_unslash( $posted['_product_creation'] ) : '',
					'product_modification'           => isset( $posted['_product_modification'] ) ? wp_unslash( $posted['_product_modification'] ) : '',
					'published_product_modification' => isset( $posted['_published_product_modification'] ) ? wp_unslash( $posted['_published_product_modification'] ) : '',
					'manage_inventory'               => isset( $posted['_manage_inventory'] ) ? wp_unslash( $posted['_manage_inventory'] ) : '',
					'product_deletion'               => isset( $posted['_product_deletion'] ) ? wp_unslash( $posted['_product_deletion'] ) : '',
					'enable_order_management'        => isset( $posted['_enable_order_management'] ) ? wp_unslash( $posted['_enable_order_management'] ) : '',
					'order_status_modification'      => isset( $posted['_order_status_modification'] ) ? wp_unslash( $posted['_order_status_modification'] ) : '',
					'commission_info_display'        => isset( $posted['_commission_info_display'] ) ? wp_unslash( $posted['_commission_info_display'] ) : '',
					'enable_coupon_management'       => isset( $posted['_enable_coupon_management'] ) ? wp_unslash( $posted['_enable_coupon_management'] ) : '',
					'coupon_creation'                => isset( $posted['_coupon_creation'] ) ? wp_unslash( $posted['_coupon_creation'] ) : '',
					'published_coupon_modification'  => isset( $posted['_published_coupon_modification'] ) ? wp_unslash( $posted['_published_coupon_modification'] ) : '',
					'coupon_modification'            => isset( $posted['_coupon_modification'] ) ? wp_unslash( $posted['_coupon_modification'] ) : '',
					'coupon_deletion'                => isset( $posted['_coupon_deletion'] ) ? wp_unslash( $posted['_coupon_deletion'] ) : '',
					'enable_commission_withdraw'     => isset( $posted['_enable_commission_withdraw'] ) ? wp_unslash( $posted['_enable_commission_withdraw'] ) : '',
					'commission_transaction'         => isset( $posted['_commission_transaction'] ) ? wp_unslash( $posted['_commission_transaction'] ) : '',
					'commission_transaction_info'    => isset( $posted['_commission_transaction_info'] ) ? wp_unslash( $posted['_commission_transaction_info'] ) : '',
				);

				if ( empty( $vendor_args['shop_name'] ) || strlen( $vendor_args['shop_name'] ) < 3 ) {
					throw new Exception( __( 'Please Enter the Shop name 3 or more characters', 'multi-vendor-marketplace' ) );
				}

				if ( mvr_check_shop_name_exists( $vendor_args['shop_name'], $post_id ) ) {
					throw new Exception( __( 'Shop Name already exists. Please try another', 'multi-vendor-marketplace' ) );
				}

				if ( empty( $vendor_args['slug'] ) || strlen( $vendor_args['slug'] ) < 3 ) {
					throw new Exception( __( 'Please enter 3 or more characters for the shop slug', 'multi-vendor-marketplace' ) );
				}

				if ( mvr_check_shop_slug_exists( $vendor_args['slug'], $post_id ) ) {
					throw new Exception( __( 'Shop slug already exists. Please try another', 'multi-vendor-marketplace' ) );
				}

				$vendor_obj = new MVR_Vendor( $post_id );
				$errors     = $vendor_obj->set_props( $vendor_args );

				if ( is_wp_error( $errors ) ) {
					throw new Exception( $errors->get_error_message() );
				}

				/**
				 * Vendor Before Save.
				 *
				 * @since 1.0.0
				 */
				do_action( 'mvr_admin_vendor_before_save', $vendor_obj, $posted );

				$vendor_obj->update_status( wc_clean( wp_unslash( $posted['_status'] ) ) );
				$vendor_obj->save();
			} catch ( Exception $e ) {
				MVR_Admin::add_error( $e->getMessage() );

				add_filter(
					'redirect_post_location',
					function ( $location ) {
						return add_query_arg( 'message', 0, $location );
					}
				);
			}
		}
	}
}
