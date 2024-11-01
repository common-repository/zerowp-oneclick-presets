<?php 
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;

class SidebarsTransition extends Access {

	public function getOptions(){
		return wp_load_alloptions();
	}

	public function getSidebarsConfig(){
		return get_option( 'sidebars_widgets', array() );
	}
	
	public function getWidgetsConfig(){
		$all_options = $this->getOptions();
		$filtered_options = array();

		foreach ( $all_options as $name => $value ) {
			if ( stristr( $name, 'widget_' ) ) {
				$filtered_options[ $name ] = maybe_unserialize( $value );
			}
		}

		return $filtered_options;
	}

}