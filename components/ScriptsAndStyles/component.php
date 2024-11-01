<?php 
/*
-------------------------------------------------------------------------------
Customizer scripts and styles
-------------------------------------------------------------------------------
*/
add_action( 'customize_controls_enqueue_scripts', function(){
	
	zwpocp_presets()->addStyle( zwpocp_presets_config('id') . '-styles-admin', array(
		'src'     =>zwpocp_presets()->assetsURL( 'css/styles-admin.css' ),
		'enqueue' => true,
	));
	
	zwpocp_presets()->addScript( zwpocp_presets_config('id') . '-config-admin', array(
		'src'     => zwpocp_presets()->assetsURL( 'js/config-admin.js' ),
		'deps'    => array( 'jquery' ),
		'enqueue' => true,
		'zwpocp_presets' => array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'customizer_url' => admin_url( 'customize.php' ),
			'error_save_before_create_preset' => __( 'Please save current settings before you create a new preset!', 'zerowp-oneclick-presets' ),
			'select_screenshot' => __( 'Select screenshot', 'zerowp-oneclick-presets' ),
			'select_preset' => __( 'Select preset', 'zerowp-oneclick-presets' ),
		),
	));

});

/*
-------------------------------------------------------------------------------
Front-end scripts and styles
-------------------------------------------------------------------------------
*/
add_action( 'wp_enqueue_scripts', function(){
	
	zwpocp_presets()->addStyle( zwpocp_presets_config('id') . '-styles', array(
		'src'     =>zwpocp_presets()->assetsURL( 'css/styles.css' ),
		'enqueue' => false,
	));
	
	zwpocp_presets()->addScript( zwpocp_presets_config('id') . '-config', array(
		'src'     => zwpocp_presets()->assetsURL( 'js/config.js' ),
		'deps'    => array( 'jquery' ),
		'enqueue' => false,
	));

});