<?php 
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;

class Import extends Access {

	public function preset( $zip_url ){
		$zip_path = $this->file_manager->urlToPath( $zip_url );
		$response = array( 'status' => 'not_imported' );
		
		if( file_exists( $zip_path ) ){
			$temp_path = $this->staticPresetsPath() . '/temp';

			// Remove the temporary dir if it already exists to avoid colisions
			$this->file_manager->removeDir( $temp_path );

			// Unzip preset to a temporary directory
			$unziped = $this->file_manager->unzip( $zip_path, $temp_path );

			// Get preset json data
			$preset_data = file_get_contents( $temp_path .'/preset.json' );

			// Extract the preset ID
			$preset_data_array = json_decode( $preset_data, true );
			$preset_id = is_array( $preset_data_array ) && !empty( $preset_data_array['id'] ) 
							? sanitize_key( $preset_data_array['id'] ) 
							: false;

			// Check if a preset with this name exists. If it does, then change the ID
			$new_preset_id = false;
			
			$base_preset_path = $this->staticPresetsPath() . $preset_id;
			$preset_temp_path = $base_preset_path;
			$i = 1;
			while( file_exists( $preset_temp_path )) {
				$preset_temp_path = $base_preset_path . $i;
				$new_preset_id = basename( $preset_temp_path );
				$i++;
			}

			// Replace the ID in preset array
			if( $new_preset_id ){
				$preset_id = $new_preset_id;
				$preset_data_array['id'] = $preset_id;
				$preset_data = wp_json_encode( $preset_data_array );
			}

			// Replace the preset URL
			$data_with_current_server = str_ireplace( 
				'{{ZWPOCP_PRESET_URL}}', 
				str_replace( 
					'/', 
					'\/', 
					untrailingslashit( $this->file_manager->pathToUrl( $this->staticPresetPath( $preset_id ) ) )
				), 
				$preset_data 
			);

			// Replace the site URL
			$data_with_current_server = str_ireplace( 
				'{{ZWPOCP_HOME_URL}}', 
				str_replace( 
					'/', 
					'\/', 
					untrailingslashit( home_url() )
				), 
				$data_with_current_server 
			);

			// I think this is not required here. Why put data back with current site url?????
			// TODO: Maybe remove...
			// $this->file_manager->putContents( 
			// 	$temp_path .'/preset.json',
			// 	$data_with_current_server
			// );

			if( $preset_id ){
				// Copy preset from temp dir to permanent dir
				$this->file_manager->copyDir( $temp_path, $this->staticPresetsPath() . $preset_id );

				// Copy dynamic CSS if it exists(Support for 'ZeroWP LESS CSS Compiler' plugin)
				// $this->file_manager->copyDir( $temp_path .'/dynamic-css', $this->file_manager->getUploadPath() );

				// TODO: I must serialize back the widgets and post(page) meta. 
				// Because it is currently unserialize, but this is not accesible from DB

				// Add this preset to DB
				$this->createPreset( $preset_id, json_decode( $data_with_current_server, true ) );

				$response['status'] = 'imported';
				$response['template']  = $this->getPresetItemTemplate( $preset_id, false );
			}
			else{
				error_log( "Invalid preset ID: {$preset_id}" );
			}
		}

		return $response;
	}

}