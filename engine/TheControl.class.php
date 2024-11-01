<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;

class TheControl extends ControlBase {

	public function presetsConfig(){
		return array(
			'presets' => array(
				'name' =>  __( 'Presets', 'zerowp-oneclick-presets' ),
				'active' => true,
				'callback' => array( $this, '_tabPresets' ),
			),
			'create' => array(
				'name' =>  __( 'Create', 'zerowp-oneclick-presets' ),
				'callback' => array( $this, '_tabCreate' ),
			),
			'import' => array(
				'name' =>  __( 'Import', 'zerowp-oneclick-presets' ),
				'callback' => array( $this, '_tabImport' ),
			),
		);
	}

	public function fieldContent() {
		$tabs = $this->presetsConfig();

		$nav_output  = '';
		$tabs_output  = '';
		foreach ($tabs as $tab_id => $tab) {
			$active = !empty( $tab['active'] ) ? ' active' : '';
			$nav_output .= '<span class="zwpocp-preset-tab'. $active .'" data-preset-tab-id="'. $tab_id .'">'. $tab[ 'name' ] .'</span>';
			$tabs_output .= '<div class="zwpocp-presets-tab '. $tab_id . $active .'">'. call_user_func( $tab[ 'callback' ] ) .'</div>';
		}

		echo '<div class="zwpocp-presets-tabs-nav">' . $nav_output . '</div>';
		echo '<div class="zwpocp-presets-tabs">' . $tabs_output . '</div>';
	}

	public function _tabPresets(){
		$access  = new Access;
		$presets = $access->getAllPresets();
		$presets = array_reverse( $presets, true );
		
		$output = '';

		$output .= '<ul id="zwpocp-presets-list" class="zwpocp-presets-list">';
			foreach ($presets as $preset_id) {
				$output .= $access->getPresetItemTemplate( $preset_id );
			}
		$output .= '</ul>';

		return $output;
	}

	public function _tabCreate(){
		$output = '';

		$output .= '<div class="zwpocp-preset-create-block">';

			$output .= '<div class="zwpocp-preset-option-row">';
				$output .= '<span class="zwpocp-label">'. __( 'Preset name', 'zerowp-oneclick-presets' ) .'</span>';
				$output .= '<input id="zwpocp_preset_name" type="text" class="fullwidth" value="" />';
			$output .= '</div>';
		
			$output .= '<div class="zwpocp-preset-other-options">';
			
				$output .= '<div class="zwpocp-preset-option-row">';
					$output .= '<span class="zwpocp-label">'. __( 'Include pages', 'zerowp-oneclick-presets' ) .'</span>';
					$output .= '<select id="zwpocp_preset_include_pages">';
						$output .= '<option value="front">'. __( 'Front page only', 'zerowp-oneclick-presets' ) .'</option>';
						$output .= '<option value="front_and_blog">'. __( 'Front and blog pages', 'zerowp-oneclick-presets' ) .'</option>';
						$output .= '<option value="all">'. __( 'All pages', 'zerowp-oneclick-presets' ) .'</option>';
					$output .= '</select>';
				$output .= '</div>';
			
				$output .= '<div class="zwpocp-preset-option-row">';
					$output .= '<span class="zwpocp-label">'. __( 'Screenshot', 'zerowp-oneclick-presets' ) .'</span>';
					$output .= '<input id="zwpocp_preset_image" type="hidden" value="" />';
					$output .= '<span class="zwpocp_preset_uploader add_image add-image zwpocp-uploader-holder">
						'. __( 'Select screenshot', 'zerowp-oneclick-presets' ) .'
					</span>';
				$output .= '</div>';
			
			$output .= '</div>';

			$output .= '<div class="zwpocp-preset-option-row">';
				$output .= '<span id="zwpocp_preset_create" class="button">
					'. __( 'Create a new preset', 'zerowp-oneclick-presets' ) .'
				</span>';
			$output .= '</div>';
		
		$output .= '</div>';

		return $output;
	}

	public function _tabImport(){
		$output = '';

		$output .= '<div class="zwpocp-preset-import-block">';
			$output .= '<input class="zip-url" type="hidden" value="" />';
			$output .= '<span class="zwpocp_preset_uploader zwpocp-preset-select-zip add-zip zwpocp-uploader-holder">'. __( 'Select preset', 'zerowp-oneclick-presets' ) .'</span>';
			$output .= '<span class="zwpocp_preset_ready_for_import button">'. __( 'Import preset', 'zerowp-oneclick-presets' ) .'</span>';
		$output .= '</div>';

		return $output;
	}

}