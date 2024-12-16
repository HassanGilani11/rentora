<?php
/**
 * About Animation Block template.
 *
 * @param array $block The block settings and attributes.
 */

// Load values and assign defaults.
$title   = get_field( 'heading' );
$bgimage   = get_field( 'background_image' );
$style = '';

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'companies';
if ( ! empty( $block['className'] ) ) {
    $class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $class_name .= ' align' . $block['align'];
}

// Build a valid style attribute for background and text colors.
if( ! empty($bgimage) ){
    $style = "background-image: url(". wp_get_attachment_url($bgimage['ID']) .');';
}
?>








<section class="section" style="<?php echo $style ?>">
      <div class="w-layout-blockcontainer container w-container">
        <div id="w-node-a9765bee-f467-c6e5-11f9-78f554c521db-fc02dae8" class="w-layout-layout quick-stack-5 wf-layout-layout">
          <div id="w-node-a9765bee-f467-c6e5-11f9-78f554c521dc-fc02dae8" class="w-layout-cell">
            <h1 class="heading-4 animate-scroll"><?php echo esc_html( $title ); ?></h1>
          </div>
          <div id="w-node-a9765bee-f467-c6e5-11f9-78f554c521dd-fc02dae8" class="w-layout-cell"></div>
        </div>
      </div>
      <div class="fog">
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog1.png" style="--i: 1;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog2.png" style="--i: 2;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog3.png" style="--i: 3;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog4.png" style="--i: 4;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog5.png" style="--i: 5;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog1.png" style="--i: 10;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog2.png" style="--i: 9;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog3.png" style="--i: 8;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog4.png" style="--i: 7;" />
        <img src="https://morgenenergydv.wpenginepowered.com/wp-content/uploads/2024/09/fog5.png" style="--i: 6;" />
      </div>
</section>