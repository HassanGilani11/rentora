<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/multi-vendor-marketplace/form-vendor-login.php.
 *
 * @package Multi Vendor Marketplace\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before Register Form
 *
 * @since 1.0.0
 */
do_action( 'mvr_before_vendor_login_form' ); ?>

<div class="mvr-vendor-login-form-wrapper">

	<h2><?php esc_html_e( 'Login', 'multi-vendor-marketplace' ); ?></h2>

	<form class="woocommerce-form woocommerce-form-login login" method="post">

		<?php
		/**
		 * Login Form Start
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_login_form_start' );
		?>

		<p class="mvr-form-row form-row form-row-wide">
			<label for="username"><?php esc_html_e( 'Username or email address', 'multi-vendor-marketplace' ); ?>&nbsp;<span class="required">*</span></label>
			<input type="text" class="mvr-input mvr-input-text input-text" name="username" id="username" autocomplete="username" value="<?php echo esc_attr( $form_fields['username'] ); ?>" />
		</p>

		<p class="mvr-form-row form-row form-row-wide">
			<label for="password"><?php esc_html_e( 'Password', 'multi-vendor-marketplace' ); ?>&nbsp;<span class="required">*</span></label>
			<input class="mvr-input mvr-input-text input-text" type="password" name="password" id="password" autocomplete="current-password" value="<?php echo esc_attr( $form_fields['password'] ); ?>"/>
		</p>

		<?php
		/**
		 * Login Form
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_login_form' );
		?>

		<p class="form-row">
			<label class="mvr-form-label mvr-form-login-rememberme">
				<input class="mvr-form-input mvr-form-input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'multi-vendor-marketplace' ); ?></span>
			</label>
			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>" />
			<button type="submit" class="mvr-button button mvr-form-login-submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'multi-vendor-marketplace' ); ?>"><?php esc_html_e( 'Log in', 'multi-vendor-marketplace' ); ?></button>
		</p>

		<p class="mvr-vendor-register vendor_register">
			<a href="<?php echo esc_url( mvr_get_page_permalink( 'vendor_register' ) ); ?>"><?php esc_html_e( 'Create Vendor', 'multi-vendor-marketplace' ); ?></a>
		</p>

		<p class="mvr-LostPassword lost_password">
			<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'multi-vendor-marketplace' ); ?></a>
		</p>

		<?php
		/**
		 * Login Form End
		 *
		 * @since 1.0.0
		 */
		do_action( 'mvr_vendor_login_form_end' );
		?>

	</form>

</div>

<?php
/**
 * After Login Form
 *
 * @since 1.0.0
 */
do_action( 'mvr_after_vendor_login_form' );
?>
