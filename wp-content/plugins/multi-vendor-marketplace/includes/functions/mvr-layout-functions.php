<?php
/**
 * Layout functions
 *
 * @package Name Your Own Price/Layout Functions.
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'mvr_select2_html' ) ) {

	/**
	 * Return or display Select2 HTML.
	 *
	 * @since 1.0.0
	 * @param Array   $args Array of arguments.
	 * @param Boolean $echo Print Field to display.
	 * */
	function mvr_select2_html( $args, $echo = true ) {
		$args = wp_parse_args(
			$args,
			array(
				'class'             => '',
				'id'                => '',
				'name'              => '',
				'type'              => '',
				'action'            => '',
				'placeholder'       => '',
				'css'               => '',
				'multiple'          => true,
				'allow_clear'       => true,
				'selected'          => true,
				'limit'             => '-1',
				'include'           => array(),
				'exclude'           => array(),
				'exclude_type'      => array(),
				'user_role_in'      => array(),
				'options'           => array(),
				'custom_attributes' => array(),
			)
		);

		// Custom attribute handling.
		$custom_attributes = array();

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		ob_start();
		?>
		<select 
			id="<?php echo esc_attr( $args['id'] ); ?>" 
			class="<?php echo esc_attr( $args['class'] ); ?>" 
			name="<?php echo esc_attr( '' !== $args['name'] ? $args['name'] : $args['id'] ); ?><?php echo ( $args['multiple'] ) ? '[]' : ''; ?>" 
			data-action="<?php echo esc_attr( $args['action'] ); ?>" 
			data-placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" 
			data-limit="<?php echo esc_attr( $args['limit'] ); ?>" 
			data-include="<?php echo esc_attr( wc_esc_json( wp_json_encode( $args['include'] ) ) ); ?>" 
			data-exclude="<?php echo esc_attr( wc_esc_json( wp_json_encode( $args['exclude'] ) ) ); ?>" 
			data-exclude_type="<?php echo esc_attr( wc_esc_json( wp_json_encode( $args['exclude_type'] ) ) ); ?>" 
			data-user_role_in="<?php echo esc_attr( wc_esc_json( wp_json_encode( $args['user_role_in'] ) ) ); ?>" 
			<?php echo ( $args['allow_clear'] ) ? 'data-allow_clear="true"' : ''; ?> 
			<?php echo ( $args['multiple'] ) ? 'multiple="multiple"' : ''; ?> 
			<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
			style="<?php echo esc_attr( $args['css'] ); ?>">
				<?php
				if ( ! is_array( $args['options'] ) ) {
					$args['options'] = (array) $args['options'];
				}

				$args['options'] = array_filter( $args['options'] );

				foreach ( $args['options'] as $id ) {
					$option_value = '';

					switch ( $args['type'] ) {
						case 'product':
							$product = wc_get_product( $id );

							if ( $product ) {
								$option_value = wp_kses_post( $product->get_formatted_name() );
							}
							break;
						case 'vendor':
							$vendor_obj = mvr_get_vendor( $id );

							if ( mvr_is_vendor( $vendor_obj ) ) {
								$option_value = wp_kses_post( '(#' . absint( $vendor_obj->get_id() ) . ') &ndash; ' . $vendor_obj->get_shop_name() . ' - ' . $vendor_obj->get_name() );
							}
							break;
						case 'user':
							$user = get_user_by( 'id', $id );

							if ( $user instanceof WP_User ) {
								$option_value = ( esc_html( $user->display_name ) . '(#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')' );
							}
							break;
						default:
							$post = get_post( $id );

							if ( $post ) {
								$option_value = sprintf( '(#%s) %s', $post->ID, wp_kses_post( $post->post_title ) );
							}

							break;
					}

					if ( $option_value ) {
						?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php echo ( $args['selected'] ) ? 'selected="selected"' : ''; ?>><?php echo wp_kses_post( $option_value ); ?></option>
						<?php
					}
				}
				?>
		</select>
		<?php
		if ( $echo ) {
			ob_end_flush();
		} else {
			return ob_get_clean();
		}
	}
}

if ( ! function_exists( 'mvr_format_custom_attributes' ) ) {

	/**
	 * Format Custom Attributes.
	 *
	 * @since 1.0
	 * @param Array $value The value to custom attribute.
	 * @return Array
	 * */
	function mvr_format_custom_attributes( $value ) {
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value ) . '';
			}
		}

		return $custom_attributes;
	}
}

if ( ! function_exists( 'mvr_get_datepicker_html' ) ) {

	/**
	 * Return or display Datepicker/DateTimepicker HTML.
	 *
	 * @since 1.0
	 * @param Array   $args Array of arguments.
	 * @param Boolean $echo Print Field to display.
	 * @return String
	 * */
	function mvr_get_datepicker_html( $args, $echo = true ) {
		$args              = wp_parse_args(
			$args,
			array(
				'class'             => '',
				'id'                => '',
				'name'              => '',
				'placeholder'       => '',
				'custom_attributes' => array(),
				'value'             => '',
				'wp_zone'           => true,
				'with_time'         => false,
				'error'             => '',
			)
		);
		$name              = ( '' !== $args['name'] ) ? $args['name'] : $args['id'];
		$allowed_html      = array(
			'input' => array(
				'id'          => array(),
				'type'        => array(),
				'placeholder' => array(),
				'class'       => array(),
				'value'       => array(),
				'name'        => array(),
				'min'         => array(),
				'max'         => array(),
				'data-error'  => array(),
				'style'       => array(),
			),
		);
		$class_name        = ( $args['with_time'] ) ? 'mvr_datetimepicker ' : 'mvr_datepicker ';
		$format            = ( $args['with_time'] ) ? 'Y-m-d H:i' : 'date';
		$custom_attributes = mvr_format_custom_attributes( $args ); // Custom attribute handling.
		$value             = ! empty( $args['value'] ) ? MVR_Date_Time::get_wp_format_datetime( $args['value'], $format, $args['wp_zone'] ) : '';
		ob_start();
		$name;
		?>
		<input type = "hidden" class="mvr_alter_datepicker_value" name="<?php echo esc_attr( $name ); ?>" value = "<?php echo esc_attr( $args['value'] ); ?>"/> 
		<input type = "text" id="<?php echo esc_attr( $args['id'] ); ?>" value = "<?php echo esc_attr( $value ); ?>" class="<?php echo esc_attr( $class_name . $args['class'] ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" data-error="<?php echo esc_attr( $args['error'] ); ?>"  <?php echo wp_kses( implode( ' ', $custom_attributes ), $allowed_html ); ?> />
		<?php
		$html = ob_get_clean();

		if ( $echo ) {
			echo wp_kses( $html, $allowed_html );
		}

		return $html;
	}
}

if ( ! function_exists( 'mvr_price' ) ) {

	/**
	 *  Display Price based wc_price function
	 *
	 * @since 1.0
	 * @param Array   $price Price Value.
	 * @param Boolean $echo Print Field to display.
	 *  @return string
	 */
	function mvr_price( $price, $echo = false ) {
		$allowed_html = array(
			'span' => array(
				'class' => array(),
			),
		);

		if ( $echo ) {
			echo wp_kses( wc_price( $price ), $allowed_html );
		}

		return wc_price( $price );
	}
}
