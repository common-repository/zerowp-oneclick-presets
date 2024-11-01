<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;
use ZeroWpOneClickPresets\Cookie;

class Filter {
	
	public $access = null;

	public function __construct(){
		$this->access = new Access;

		add_action( 'after_setup_theme', array( $this, '_setupOptionFilters' ), 99 );
	}

	public function _setupOptionFilters(){
		if( ! is_customize_preview() && ! is_admin() && ! empty( Cookie::current() ) && $this->access->presetExists( Cookie::current() ) ){

			/* Modify theme mods
			-------------------------*/
			$theme_slug = get_stylesheet();
			add_filter( 'option_theme_mods_' . $theme_slug, array( $this, 'filterThemeMods' ), 99 );

			// Modify other theme mods
			add_filter( 'option_show_on_front', array( $this, 'filter_show_on_front' ), 99 );
			add_filter( 'option_page_on_front', array( $this, 'filter_page_on_front' ), 99 );

			add_filter( 'option_sidebars_widgets', array( $this, 'filter_sidebars_widgets' ), 99 );

		}
	}

	public function filterThemeMods( $theme_mods_option ){
		$preset = $this->access->getPresetMods( sanitize_key( Cookie::current() ) );
		
		if( !empty( $preset ) && is_array( $preset ) ){
			$theme_mods_option = wp_parse_args( $preset, $theme_mods_option );
		}

		return $theme_mods_option;
	}

	public function filter_show_on_front( $option ){
		return $this->_filterOption( 'show_on_front', $option );
	}

	public function filter_page_on_front( $option ){
		return $this->_filterOption( 'page_on_front', $option );
	}

	public function filter_page_for_posts( $option ){
		return $this->_filterOption( 'page_for_posts', $option );
	}

	protected function _filterOption( $option_name, $option_value ){
		$preset = $this->access->getPresetOtherMods( sanitize_key( Cookie::current() ) );
		
		if( !empty( $preset[ $option_name ] ) ){
			$option_value = $preset[ $option_name ];
		}

		return $option_value;
	}

	public function filter_sidebars_widgets( $option ){
		$sidebars = $this->access->getPresetSidebars( sanitize_key( Cookie::current() ) );
		
		if( !empty( $sidebars ) ){
			$option = $sidebars;
		}

		return $option;
	}

}