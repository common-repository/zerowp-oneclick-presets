<?php 
namespace ZeroWpOneClickPresets;

class FileManager {
	
	public function zipDir( $folder_path, $zip_name ){
		$folder_path = realpath( $folder_path );

		// Initialize archive object
		$zip = new \ZipArchive();
		$zip->open( $zip_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE );

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		// $files = glob_recursive( '/*' );
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($folder_path),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file){
			// Skip directories (they would be added automatically)
			if (!$file->isDir()){
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($folder_path) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();
		return true;
	}

	public function unzip( $zip_path, $extract_to_path ){
		$zip = new \ZipArchive;

		if ( ! is_dir( $extract_to_path ) ) {
			mkdir( $extract_to_path, 0755, true );
		}
		
		if ( $zip->open( $zip_path ) === true ) {
			$zip->extractTo( $extract_to_path );
			$zip->close();
			return true;
		} else {
			return false;
		}
	}

	public function removeDir($dir) { 
		$files = array_diff( scandir( $dir ), array( '.', '..' ) ); 
		
		foreach ( $files as $file ) { 
			if( is_dir( "$dir/$file" ) ){
				$this->removeDir( "$dir/$file" ); 
			}
			else{
				unlink( "$dir/$file" );
			}
		} 
		
		return rmdir($dir); 
	}

	/**
	 * Copy a file, or recursively copy a folder and its contents
	 * @author      Aidan Lister <aidan@php.net>
	 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @param       int      $permissions New folder creation permissions
	 * @return      bool     Returns true on success, false on failure
	 */
	protected function _copyDir($source, $dest, $permissions = 0755){
		// Check for symlinks
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}

		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}

		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest, $permissions);
		}

		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			$this->_copyDir( "$source/$entry", "$dest/$entry", $permissions );
		}

		// Clean up
		$dir->close();
		return true;
	}

	// Alias to _copyDir
	public function copyDir( $source, $dest, $permissions = 0755 ){
		$dest_basedir = dirname( $dest );
		
		if ( ! is_dir( $dest_basedir ) ) {
			mkdir( $dest_basedir, 0755, true );
		}
		
		return $this->_copyDir( $source, $dest, $permissions );
	}

	// Alias to _copyDir
	public function copyFile( $source, $dest, $permissions = 0755 ){
		$dest_basedir = dirname( $dest );
		
		if ( ! is_dir( $dest_basedir ) ) {
			mkdir( $dest_basedir, 0755, true );
		}
		
		return $this->_copyDir( $source, $dest, $permissions );
	}

	// public function globRecursive($pattern, $flags = 0){
	// 	$messages = array();

	// 	$files = glob( $pattern, $flags );
		
	// 	foreach ( glob( dirname( $pattern ) .'/*', GLOB_ONLYDIR|GLOB_NOSORT ) as $dir ) {
	// 		$files = array_merge( $files, $this->globRecursive( 
	// 			$dir.'/'.basename( $pattern ), 
	// 			$flags
	// 		));
	// 	}

	// 	return $files;
	// }

	// public function replaceInFile( $file_path, $find, $replace ){
	// 	if( file_exists( $file_path ) ){
	// 		$temp = file_get_contents( $file_path );
	// 		$temp = str_replace( $find, $replace, $temp );

	// 		return $this->putContents( $file_path, $temp );
	// 	}
	// }

	public function putContents( $file_path, $data ){
		$basedir = dirname( $file_path );
	
		if ( ! is_dir( $basedir ) ) {
			mkdir( $basedir, 0755, true );
		}

		return file_put_contents( $file_path, $data );
	}

	public function getAllImagesFromString( $string ){
		$matches = array();
		preg_match_all( '!'. wp_slash( $this->getUploadUrl() ) . '*[\w\-\.\/]+(?:jpe?g|png|gif)!Ui' , $string , $matches);

		return $matches;
	}

	public function getAllImagesFromFile( $file_path ){
		$contents = '';
		
		if( file_exists( $file_path ) ){
			$contents = file_get_contents( $file_path );
		}

		return $this->getAllImagesFromString( $contents );
	}

	public function unserializeFile( $file_path ){
		$contents = '';
		
		$file_path = $this->urlToPath( $file_path );

		if( file_exists( $file_path ) ){
			$contents = file_get_contents( $file_path );
		}

		return maybe_unserialize( $contents );
	}

	public function urlToPath( $url ){
		return str_ireplace( $this->getUploadUrl(), $this->getUploadPath(), $url );
	}

	public function pathToUrl( $path ){
		return str_ireplace( $this->getUploadPath(), $this->getUploadUrl(), wp_normalize_path( $path ) );
	}

	public function getUploadPath(){
		$upload_dir = wp_upload_dir();
		return trailingslashit( wp_normalize_path( $upload_dir[ 'basedir' ] ) );
	}

	public function getUploadUrl(){
		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir[ 'baseurl' ] );
	}



}