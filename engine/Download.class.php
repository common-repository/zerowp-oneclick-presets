<?php 
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;

class Download extends Access {

	public function preset( $preset_id ){
		$preset = $this->getPreset( $preset_id );
		
		// unset( $preset['other_mods'] ); // When exported, the front page will return 404. Better to remove this.

		$serialized_data = maybe_serialize( $preset );
		$json_data = wp_json_encode( $preset );
		$zip_file = $this->zipFilePath( $preset_id );

		// if( ! file_exists( $zip_file ) ){
			
			/* Get all images
			We get the images from a serialized array, so it treats it all together as one big string
			----------------------*/
			$images = $this->file_manager->getAllImagesFromString( $serialized_data );

			/* Copy all images
			-----------------------*/
			$image_paths = array();
			if( !empty($images[0]) ){
				foreach ($images[0] as $img) {
					$img_path = $this->file_manager->urlToPath( $img );
					$this->file_manager->copyFile( 
						$img_path, 
						$this->staticPresetPath( 
							$preset_id, 
							'img/'. str_ireplace( $this->file_manager->getUploadPath(), '', $img_path ) 
						) 
					);
				}
			}

			/* Replace the wp uploads URL
			----------------------------------*/
			$json_data = str_ireplace( 
				str_replace( 
					'/', 
					'\/', 
					$this->file_manager->getUploadUrl() 
				), 
				'{{ZWPOCP_PRESET_URL}}/img/', 
				$json_data 
			);

			/* Replace the site URL
			----------------------------------*/
			$json_data = str_ireplace( 
				str_replace( 
					'/', 
					'\/', 
					home_url() 
				), 
				'{{ZWPOCP_HOME_URL}}', 
				$json_data 
			);

			/* Save preset data
			-----------------------------------------*/
			$this->file_manager->putContents( 
				$this->staticPresetPath( $preset_id, 'preset.json' ),
				$json_data
			);

			do_action( 'zwpocp_presets:before_zip_preset', $preset_id, $this );

			/* Create the zip archive of this preset
			---------------------------------------------*/
			$this->file_manager->zipDir( $this->staticPresetPath( $preset_id ), $zip_file );

			/* Finally, remove the temp dir
			------------------------------------*/
			// $this->file_manager->removeDir( $this->staticPresetPath( $preset_id ) );
			
		// }

		do_action( 'zwpocp_preset:download_preset', $preset_id );

		return $zip_file;
	}

}