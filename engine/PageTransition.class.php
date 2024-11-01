<?php 
namespace ZeroWpOneClickPresets;

class PageTransition {

	// Return `true` if a page with this ID already exists. 
	public function pageExists( $id ){
		return get_post_type( $id ) == 'page';
	}

	// Return `true` if the ID is already in use.
	public function idInUse( $id ){
		return get_post_type( $id ) !== false;
	}

	// Return `true` if a page with this ID can be created
	public function canCreatePage( $id ){
		return ( ! $this->pageExists( $id ) && ! $this->idInUse( $id ) );
	}

	// Get all pages based on $args. 
	// https://developer.wordpress.org/reference/functions/get_pages/
	public function getPages( $args = array() ){
		$pages = get_pages( $args );
		$new_pages = array();
		
		if( !empty( $pages ) ) {
			foreach ($pages as $key => $page) {
				$new_pages[ $page->ID ] = $page;
			}
		}

		return $new_pages;
	}

	// Get all meta data of one or more pages by their IDs
	public function getPagesMeta( $page_ids ){
		$meta = array();
		
		if( ! empty( $page_ids ) ){
			if( ! is_array( $page_ids ) ){
				$page_ids = ( array ) $page_ids;
			}

			foreach ($page_ids as $key => $page_id) {
				$meta[ $page_id ] = $this->arrayUnserializeDeep( get_post_meta( $page_id ) );
			}
		}

		return $meta;
	}

	public function arrayUnserializeDeep( $this_meta ){
		$new_meta = $this_meta;
		
		foreach ($this_meta as $meta_key => $meta_value) {
			$new_meta[ $meta_key ][0] = maybe_unserialize( $meta_value[0] );
		}

		return $new_meta;
	}

}