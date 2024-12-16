<?php
/**
 * Add Staff
 *
 * @package Multi-Vendor for WooCommerce/Staff
 * */

defined( 'ABSPATH' ) || exit;
?>
<div class="wc-backbone-modal mvr-add-staff-wrapper">
	<div class="wc-backbone-modal-content">
		<section class="wc-backbone-modal-main" role="main">
			<header class="wc-backbone-modal-header">
				<h1><?php esc_html_e( 'Add Staff', 'multi-vendor-marketplace' ); ?></h1>
			</header>
			<article>
				<p class="mvr-fields">
					<label for="_staff_selection_type"><?php esc_html_e( 'Add Staff from:', 'multi-vendor-marketplace' ); ?></label>
					<select class="mvr-staff-selection-type" name="_staff_selection_type">
						<?php
						$options = array(
							'1' => esc_html__( 'Existing User', 'multi-vendor-marketplace' ),
							'2' => esc_html__( 'Add New User', 'multi-vendor-marketplace' ),
						);

						foreach ( $options as $key => $value ) :
							?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option> 
							<?php
						endforeach;
						?>
					</select>
				</p>	
				<p class="mvr-add-staff-fields mvr-new-user-field">
					<label for="_user_name"><?php esc_html_e( 'Username:', 'multi-vendor-marketplace' ); ?></label>
					<input type="text" class="mvr-user-name input-text mvr-required-field" name="_user_name" id="_user_name"/>
				</p>

				<p class="mvr-add-staff-fields mvr-new-user-field">
					<label for="_user_email"><?php esc_html_e( 'User Email:', 'multi-vendor-marketplace' ); ?></label>
					<input type="email" class="mvr-user-email input-text mvr-required-field" name="_user_email" id="_user_email"/>
				</p>

				<p class="mvr-add-staff-fields mvr-new-user-field">
					<label for="_password"><?php esc_html_e( 'Create Password:', 'multi-vendor-marketplace' ); ?></label>
					<input type="password" class="mvr-user-password input-text mvr-required-field" name="_password" id="_password"/>
				</p>

				<p class="mvr-add-staff-fields mvr-new-user-field">
					<label for="_confirm_password"><?php esc_html_e( 'Confirm Password:', 'multi-vendor-marketplace' ); ?></label>
					<input type="password" class="mvr-user-confirm-password input-text mvr-required-field" name="_confirm_password" id="_confirm_password"/>
				</p>

				<p class="mvr-add-staff-fields mvr-existing-user-field">
					<label for="_selected_user"><?php esc_html_e( 'Select User:', 'multi-vendor-marketplace' ); ?></label>
					<?php
					mvr_select2_html(
						array(
							'id'           => '_selected_user',
							'class'        => 'mvr-select2-search mvr-required-field mvr-selected-user',
							'placeholder'  => esc_html__( 'Search User(s)', 'multi-vendor-marketplace' ),
							'type'         => 'user',
							'action'       => 'mvr_json_search_users',
							'multiple'     => false,
							'user_role_in' => get_option( 'mvr_settings_become_a_vendor_roles', array( 'customer' ) ),
						)
					);
					?>
				</p>
				<span class="mvr-inside"></span>
				<span class="mvr-error" style="font-weight:bold;"></span>
			</article>
			<footer>                
				<div class="inner">
					<button class="mvr-add-staff button button-primary" style="display:none;"><?php esc_html_e( 'Add Staff', 'multi-vendor-marketplace' ); ?></button>
					<a href="<?php echo esc_url( get_admin_url( null, 'edit.php?post_type=mvr_staff' ) ); ?>" class="mvr-cancel-staff-adding button"><?php esc_html_e( 'Cancel', 'multi-vendor-marketplace' ); ?></a>
				</div>
			</footer>
		</section>
	</div>
</div>
<div class="wc-backbone-modal-backdrop"></div>
