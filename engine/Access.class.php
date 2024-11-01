<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\FileManager;

class Access {

	public $main_option_key;
	public $theme_option_key;
	public $theme_slug;
	
	public function __construct(){
		$this->theme_slug = get_stylesheet();
		$this->main_option_key = 'zwpocp_oneclick_presets';
		$this->theme_option_key = $this->main_option_key . '_' . $this->theme_slug;
		$this->file_manager = new FileManager;
	}

	public function getAllPresets(){
		$presets = get_option( $this->theme_option_key );
		
		if( empty( $presets ) ){
			$presets = array();
		}

		return $presets;
	}

	public function getPreset( $preset_id ){
		$preset = get_option( $this->getPresetOptionName( $preset_id ) );

		if( !empty( $preset ) ){
			return $preset;
		}
		else{
			return false;
		}
	}

	public function presetExists( $preset_id ){
		return false !== $this->getPreset( $preset_id );
	}

	public function getPresetOptionName( $preset_id ){
		return $this->theme_option_key . '_' . $preset_id;
	}

	public function getPresetName( $preset_id ){
		$preset = $this->getPreset( $preset_id );
		return !empty($preset['name']) ? $preset['name'] : $preset['id'];
	}

	public function getPresetMods( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['mods'] ) ){
			return $mods['mods'];
		}
		else{
			return false;
		}
	}

	public function getPresetOtherMods( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['other_mods'] ) ){
			return $mods['other_mods'];
		}
		else{
			return false;
		}
	}

	public function getPresetPages( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['pages'] ) ){
			return $mods['pages'];
		}
		else{
			return false;
		}
	}

	public function getPresetPagesMeta( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['pages_meta'] ) ){
			return $mods['pages_meta'];
		}
		else{
			return false;
		}
	}

	public function getPresetSidebars( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['sidebars'] ) ){
			return $mods['sidebars'];
		}
		else{
			return false;
		}
	}

	public function getPresetWidgets( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['widgets'] ) ){
			return $mods['widgets'];
		}
		else{
			return false;
		}
	}

	// Include current theme option key name in the list of backups
	protected function _addCurrentThemeToTheListInMainOption(){
		$list = get_option( $this->main_option_key, array() );
		if( ! array_key_exists( $this->theme_option_key, $list ) ){
			$list[ $this->theme_option_key ] = $this->theme_option_key;
			update_option( $this->main_option_key, $list );
		}
	}

	public function createPreset( $preset_id, $preset_data ){
		// Update presets list of IDs
		$presets = $this->getAllPresets();
		$presets[ $preset_id ] = $preset_id;
		update_option( $this->theme_option_key, $presets );

		// Update preset option data
		update_option( $this->getPresetOptionName( $preset_id ), $preset_data );

		// Read the method name :)
		$this->_addCurrentThemeToTheListInMainOption();

		do_action( 'zwpocp_preset:create_preset', $preset_id, $this );
	}

	public function deletePreset( $preset_id ){
		// Delete the ID from presets list
		$presets = $this->getAllPresets();
		unset( $presets[ $preset_id ] );
		update_option( $this->theme_option_key, $presets );

		// Remove the ZIP file if it exists(cleaning the garbage)
		if( file_exists( $this->zipFilePath( $preset_id ) ) ){
			unlink( $this->zipFilePath( $preset_id ) );
		}

		// Remove the ZIP file if it exists(cleaning the garbage)
		if( file_exists( $this->zipFilePath( $preset_id ) ) ){
			unlink( $this->zipFilePath( $preset_id ) );
		}

		// Remove the preset directory if it exists(cleaning the garbage)
		if( file_exists( $this->staticPresetPath( $preset_id ) ) ){
			$this->file_manager->removeDir( $this->staticPresetPath( $preset_id ) );
		}

		do_action( 'zwpocp_preset:delete_preset', $preset_id );
	}

	/*
	-------------------------------------------------------------------------------
	Paths
	-------------------------------------------------------------------------------
	*/
	public function staticPresetPath( $preset_id, $file_name = '' ){
		return $this->staticPresetsPath() . $preset_id .'/'. $file_name;
	}

	public function staticPresetsPath(){
		return $this->file_manager->getUploadPath() . $this->theme_slug . '-presets/';
	}

	public function zipFilePath( $preset_id ){
		return $this->staticPresetsPath() . 'preset-' . sanitize_title( $this->getPresetName( $preset_id ) ) . '-' . $preset_id .'.zip';
	}

	/*
	-------------------------------------------------------------------------------
	A single preset template from list
	-------------------------------------------------------------------------------
	*/
	public function getPresetItemTemplate( $preset_id, $preset_data = null ){
		$preset_data = $preset_data ? $preset_data : $this->getPreset( $preset_id );
		$name        = !empty( $preset_data[ 'name' ] ) ? $preset_data[ 'name' ] : $preset_id;
		$id          = esc_attr( $preset_id );
		$time        = !empty( $preset_data[ 'time' ] ) ? date_i18n( 'Y-m-d H:i:s', $preset_data[ 'time' ] ) : '';
		$url         = esc_url_raw( add_query_arg( array( 'zwpocp_preset' => $id ), home_url() ) );
		$image       = !empty( $preset_data[ 'image' ] ) ? '<div class="preset-image"><img src="'. $preset_data[ 'image' ] .'" /></div>' : '';

		return '<li id="zwpocp-preset-'. $id .'"><div class="preset" data-preset-id="'. $id .'">
			<a href="'. $url .'" target="_blank" class="preset-preview-main">
				'. $image .'
				<div class="preset-title">'. $name .' </div>
				<div class="preset-creation-date">'. $time .'</div>
			</a>
			<div class="preset-actions">
				<span data-preset-id="'. $id .'" class="zwpocp_preset_use preset-use">
					'. __( 'Use preset', 'zerowp-oneclick-presets' ) .'
				</span>
				<a href="'. $url .'" target="_blank" class="preset-preview" title="'. __( 'Preview', 'zerowp-oneclick-presets' ) .'">
					<span class="dashicons dashicons-welcome-view-site"></span>
				</a>
				<span data-preset-id="'. $id .'" class="zwpocp_preset_download preset-download" title="'. __( 'Download', 'zerowp-oneclick-presets' ) .'">
					<span class="dashicons dashicons-download"></span>
				</span>
				<span data-preset-id="'. $id .'" class="zwpocp_preset_delete preset-delete" title="'. __( 'Delete', 'zerowp-oneclick-presets' ) .'">
					<span class="dashicons dashicons-trash"></span>
				</span>
			</div>
		</div></li>';
	}

}