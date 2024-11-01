<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;

class Cookie {
	
	public function __construct(){
		add_action( 'after_setup_theme', array( $this, '_setupCookie' ), 9 );
		add_action( 'after_setup_theme', array( $this, '_removeCookie' ), 5 );
		add_action( 'template_redirect', array( $this, '_removeCookieRedirect' ) );
		add_action( 'admin_bar_menu', array( $this, 'notice' ), 499 );
		add_action( 'wp_footer', array( $this, 'floatingStyle' ), 489 );
		add_action( 'wp_footer', array( $this, 'noticeFooter' ), 499 );
	}

	public function _setupCookie(){
		if( ! is_customize_preview() && ! is_admin() && ! empty($_GET[ 'zwpocp_preset' ]) ){
			setcookie( 'zwpocp_preset', sanitize_key( $_GET[ 'zwpocp_preset' ] ), time()*60*60*3 ); // 3 hours
		}
	}

	public function _removeCookie(){
		if( isset($_GET[ 'zwpocp_preset_remove' ]) ){
			setcookie( 'zwpocp_preset', '', time() - 60*60*3 ); // - 3 hours
		}
	}

	public function _removeCookieRedirect(){
		if( isset($_GET[ 'zwpocp_preset_remove' ]) ){
			wp_redirect( home_url() );
			exit();
		}
	}

	public static function current(){
		if( !empty( $_GET[ 'zwpocp_preset' ] ) ){
			$cookie = sanitize_key( $_GET[ 'zwpocp_preset' ] );
		}

		else if( !empty( $_COOKIE[ 'zwpocp_preset' ] ) ){
			$cookie = sanitize_key( $_COOKIE[ 'zwpocp_preset' ] );
		}

		else{
			$cookie = false;
		}

		return $cookie;
	}

	public function notice(){
		global $wp_admin_bar;

		$access = new Access;

		if( !empty( self::current() ) && $access->presetExists( self::current() ) ){
			$preset_name = $access->getPresetName( self::current() );
			$preset_name = esc_html( $preset_name );

			$wp_admin_bar->add_node(array(
				'id' => 'zerowp-presets-adminbar-notice',
				'parent' => null,
				'href' => $this->getPresetRemoveUrl(),
				'title' => $this->getPresetNotice( $preset_name ),
			));
		}

	}

	public function noticeFooter(){

		if( ! is_admin_bar_showing() ) {
			$access = new Access;

			if( !empty( self::current() ) && $access->presetExists( self::current() ) ){
				$preset_name = $access->getPresetName( self::current() );
				$preset_name = esc_html( $preset_name );
				
				echo '<a href="'. $this->getPresetRemoveUrl() .'" class="zwpocp-preset-floating-notice">'. $this->getPresetNotice( $preset_name ) .'</a>';
			}

		} // ! is_admin_bar_showing()
	}

	public function floatingStyle(){
		if( !empty( self::current() ) ){
			echo '<style>';
				echo '
				#wpadminbar .zwpocp-preset-notice,
				.zwpocp-preset-notice{
					display: inline-block;
					background: #d73c2c;
					padding: 5px 8px 6px;
					margin: 0;
					height: auto;
					line-height: 1;
					color: #fff;
				}';
				if( ! is_admin_bar_showing() ) {
					echo '.zwpocp-preset-floating-notice{
						display: block; 
						position: fixed; 
						z-index: 10000; 
						white-space: nowrap; 
						top: 20px; 
						right: 30px;
						width: 15px;
						height: 15px;
						background: rgba(215,60,44,0.8);
						border-radius: 50%;
						box-shadow: 0px 0px 24px 0px rgba(215,60,44,0.56);
					}
					.zwpocp-preset-floating-notice:hover{
						box-shadow: 0px 0px 24px 0px rgba(215,60,44,1);
					}
					.zwpocp-preset-floating-notice .zwpocp-preset-notice{
						position: absolute;
						right: 100%;
						top: -8px;
						border-radius: 3px;
						visibility: hidden;
						opacity: 0;
						transition: all 0.15s;
					}
					.zwpocp-preset-floating-notice:hover .zwpocp-preset-notice{
						right: 150%;
						visibility: visible;
						opacity: 1;
					}';
				}
			echo '</style>';
		}
	}

	public function getPresetRemoveUrl(){
		return esc_url_raw( 
			add_query_arg( 
				array( 'zwpocp_preset_remove' => 1 ), 
				remove_query_arg( 'zwpocp_preset', home_url() )
			) 
		);
	}

	public function getPresetNotice( $preset_name ){
		return '<span class="zwpocp-preset-notice">'. 
			sprintf( __( 'Preset "%s" is active: Turn OFF', 'zerowp-oneclick-presets' ), $preset_name ) 
		.'</span>';
	}

}