<?php 
new ZeroWpOneClickPresets\Ajax;
new ZeroWpOneClickPresets\Filter;
new ZeroWpOneClickPresets\Cookie;

/* Make sure that file uploader accepts zip files
------------------------------------------------------*/
add_filter('upload_mimes', 'zwpocp_presets_add_zip_mime_type', 1, 1);
function zwpocp_presets_add_zip_mime_type( $mime_types ){
    $mime_types[ 'zip' ] = 'application/zip';
    return $mime_types;
}

/* Setup the Presets section in customizer
-----------------------------------------------*/
add_action( 'customize_register', 'zwpocp_presets_customize_register', 999 );
function zwpocp_presets_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'zwpocp_presets_oneclick_section', array(
		'title'          => __( 'Presets', 'zerowp-oneclick-presets' ),
		// 'description'    => '',
		'priority'       => 999,
		'capability'     => 'edit_theme_options',
	) );

	$wp_customize->add_setting( 'zwpocp_presets_oneclick_setting', array(
		'type'       => 'theme_mod',
		'capability' => 'edit_theme_options',
		'transport'  => 'postMessage',
	) );

	$wp_customize->add_control( new ZeroWpOneClickPresets\TheControl( $wp_customize, 'zwpocp_presets_oneclick_setting', array(
		// 'label'   => __( 'Presets', 'zerowp-oneclick-presets' ),
		'section' => 'zwpocp_presets_oneclick_section',
	) ) );
}