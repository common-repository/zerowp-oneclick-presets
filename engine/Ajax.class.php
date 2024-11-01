<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;
use ZeroWpOneClickPresets\Import;
use ZeroWpOneClickPresets\Download;
use ZeroWpOneClickPresets\FileManager;
use ZeroWpOneClickPresets\PageTransition;
use ZeroWpOneClickPresets\SidebarsTransition;

class Ajax {
	
	public $access = null;

	public function __construct(){
		$this->access = new Access;
		$this->import = new Import;
		$this->download = new Download;
		$this->file_manager = new FileManager;
		$this->page_transition = new PageTransition;
		$this->sidebars_transition = new SidebarsTransition;

		add_action( 'wp_ajax_zwpocp_presets_use_preset', array( $this, '_ajaxUsePreset' ) );
		add_action( 'wp_ajax_zwpocp_presets_create_preset', array( $this, '_ajaxCreatePreset' ) );
		add_action( 'wp_ajax_zwpocp_presets_delete_preset', array( $this, '_ajaxDeletePreset' ) );
		add_action( 'wp_ajax_zwpocp_presets_import_preset', array( $this, '_ajaxImportPreset' ) );
		add_action( 'wp_ajax_zwpocp_presets_download_preset', array( $this, '_ajaxDownloadPreset' ) );
	}

	public function _ajaxCreatePreset(){

		$id     = uniqid( true );
		$data   = $_POST;
		$mods   = get_theme_mods();

		unset( $data['action'] );
		
		$data['name'] = wp_unslash( $data['name'] );
		$data['image'] = !empty( $data['image'] ) ? esc_url_raw( $data['image'] ) : false;
		$data['id']   = sanitize_key( $id );
		$data['time'] = time();
		$data['mods'] = $mods;

		// Setup other mods(actually these are not mods)
		$data['other_mods'] = array(
			'show_on_front' => get_option( 'show_on_front' ), 
			'page_on_front' => get_option( 'page_on_front' ), 
			'page_for_posts' => get_option( 'page_for_posts' ), 
		);

		// Include pages
		$pages_include = !empty( $data[ 'pages_include' ] ) ? $data[ 'pages_include' ] : false;

		// Determine the pages to export
		$front_page_id = get_option( 'page_on_front', false );
		$posts_page_id = get_option( 'page_for_posts', false );
		if( 'front' == $pages_include ){
			$data[ 'pages' ] = $this->page_transition->getPages( array(
				'include' => array( $front_page_id ),
			) );
		}
		elseif( 'front_and_blog' == $pages_include ){
			$data[ 'pages' ] = $this->page_transition->getPages( array(
				'include' => array( $front_page_id, $posts_page_id ),
			) );
		}
		elseif( 'all' == $pages_include ){
			$data[ 'pages' ] = $this->page_transition->getPages();
		}

		// Get the meta for exported pages
		if( !empty( $data[ 'pages' ] ) ) {
			$data[ 'pages_meta' ] = $this->page_transition->getPagesMeta( array_keys( $data[ 'pages' ] ) );
		}

		// Include all active sidebars
		$data[ 'sidebars' ] = $this->sidebars_transition->getSidebarsConfig();

		// Include all active widgets
		$data[ 'widgets' ] = $this->sidebars_transition->getWidgetsConfig();

		// Create the preset
		$this->access->createPreset( $id, $data );

		$data['template']  = $this->access->getPresetItemTemplate( $id, $data );
		
		echo wp_json_encode( $data );
		die();
	}

	public function _ajaxDeletePreset(){

		$data   = $_POST;
		$id     = sanitize_key( $data['preset_id'] );

		$this->access->deletePreset( $id );

		echo 'preset_deleted';
		die();
	}

	public function _ajaxDownloadPreset(){

		$data   = $_POST;
		$id     = sanitize_key( $data['preset_id'] );

		$zip_file = $this->download->preset( $id );

		$response = array( 'status' => 'not_ready_for_download' );
		if( $zip_file ){
			$response[ 'status' ] = 'ready_for_download';
			$response[ 'file' ] = $this->file_manager->pathToUrl( $zip_file );
		}

		echo wp_json_encode( $response );
		die();
	}

	public function _ajaxImportPreset(){
		$data   = $_POST;
		$zip_url = $data['zip_url'];

		$import_response = $this->import->preset( $zip_url );

		echo wp_json_encode( $import_response );
		die();
	}

	public function _ajaxUsePreset(){

		$data     = $_POST;
		$id       = sanitize_key( $data['preset_id'] );
		$preset   = $this->access->getPreset( $id );
		$response = array( 'status' => 'not_ready' );

		/* Set theme mods
		----------------------*/
		if( !empty( $preset[ 'mods' ] ) ){
			$mods = maybe_unserialize( $preset[ 'mods' ] );
			foreach ($mods as $mod_key => $mod_value) {
				set_theme_mod( $mod_key, $mod_value );
			}
		}

		/* Set other mods
		----------------------*/
		$other_mods = array(
			'show_on_front',
			'page_on_front',
			'page_for_posts',
		);

		foreach ($other_mods as $mod_key) {
			if( !empty( $preset['other_mods'][ $mod_id ] ) ){
				update_option( $mod_key, $preset['other_mods'][ $mod_id ] );
			}
		}

		/* Create sidebars
		-----------------------*/
		if( !empty( $preset[ 'sidebars' ] ) ){
			$current_sidebars = get_option( 'sidebars_widgets', array() );
			$current_sidebars = maybe_unserialize( $current_sidebars );
			$new_sidebars = $preset[ 'sidebars' ];

			// No need for this
			unset( $current_sidebars[ 'wp_inactive_widgets' ] );
			unset( $current_sidebars[ 'array_version' ] );

			$sidebars = wp_parse_args( $new_sidebars, $current_sidebars );

			update_option( 'sidebars_widgets', $sidebars );

		}

		/* Create widgets
		----------------------*/
		if( !empty( $preset[ 'widgets' ] ) ){
			foreach ($preset[ 'widgets' ] as $widget_id_base => $widget_settings) {
				$current_settings = get_option( $widget_id_base, array() );
				// Using '+' operator, so the keys are preserved. `array_merge` does not preserve keys.
				update_option( $widget_id_base, $current_settings + $widget_settings );
			}
		}

		do_action( 'zwpocp_preset:use_preset', $id, $this );

		$response[ 'status' ] = 'ready';


		echo wp_json_encode( $response );
		die();
	}

}