<?php
/**
* class WP_Obscurity
*/
class WP_Obscurity {
	
	public $default_preset = 'min';
		
	public $presets = array(
		'max' => array(
			'login-errors' => 'obscure',
			'version-urls' => 'remove',
			'xpingback' => 'remove',
			'generator' => 'remove',
			'head-tags' => 'remove',
		),
		'min' => array(				
			'login-errors' => 'display',
			'version-urls' => 'display',
			'xpingback' => 'display',
			'generator' => 'display',
			'head-tags' => 'display',
		),
	);

	protected static $_ran = false;
	
	static protected $_instance;
	
	static function instance( $settings = null ){
		if ( !isset(self::$_instance) )
			self::$_instance = new self( $settings );
		return self::$_instance;
	}
	
	private function __construct( $settings = null ){
		
		if ( is_string($settings) && isset($this->presets[$settings]) ){
			$settings = $this->presets[$settings];	
		}
		if ( is_array($settings) ){
			$this->import_settings( $settings );
		}
	}
	
	public function import_settings($settings){
		foreach($settings as $key => $val){
			$this->$key = $val;
		}
		return $this;
	}
	
	public function setting_is($setting, $value){
		return ( isset($this->$setting) && $value == $this->$setting ) ? true : false;	
	}
	
	public function run(){
		
		if (self::$_ran) return;
		
		// obscure error mesage in login
		if ( $this->setting_is('login-errors', 'obscure') ){
			add_filter( 'login_errors', array($this, 'obscure_login_errors') );	
		}
			
		// remove WP version appended to script & style urls
		if ( $this->setting_is('version-urls', 'remove') ){
			add_filter( 'script_loader_src', array($this, 'remove_version_urls') );
			add_filter( 'style_loader_src', array($this, 'remove_version_urls') );
		}
		
		// remove xpingback header
		if ( $this->setting_is('xpingback', 'remove') ){
			add_filter( 'wp_headers', array($this, 'remove_headers') );
		}
		
		// hash the generator, or just remove it
		if ( $this->setting_is('generator', 'remove') ){
			remove_action( 'wp_head', 'wp_generator' );
		} elseif ( $this->setting_is('generator', 'hash') ){
			add_filter( 'the_generator', array($this, 'hash_wp_generator'), 5, 2 );
		}
		
		// Remove extra head items
		if ( $this->setting_is('head-tags', 'remove') ){
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'start_post_rel_link' );
			remove_action( 'wp_head', 'index_rel_link' );
			remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			remove_action( 'wp_head', 'rsd_link' );
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}
		
		self::$_ran = true;			
	}
		
	function remove_headers($headers){
		
		unset($headers['X-Pingback']);
		
		if ( function_exists('header_remove') ){
			@header_remove('X-Powered-By');	
		} else {
			@header('X-Powered-By: ', true);	
		}
		
		unset($headers['X-Powered-By']);
		
		return $headers;	
	}
	
	function obscure_login_errors(){
		return 'Invalid login credentials.';	
	}
	
	function remove_version_urls( $src ){
		global $wp_version;
		$pattern = "/([\?|\&]+ver=$wp_version)/";
		return preg_replace($pattern, "", $src);
	}
	
	// Not using wp_hash() because its slower
	function hash_wp_generator( $generator, $type ){
		switch ( $type ) {
			case 'html':
				return '<meta name="generator" content="' . hash_hmac('sha1', get_bloginfo('version'), NONCE_KEY) . '">';
			case 'xhtml':
				return '<meta name="generator" content="' . hash_hmac('sha1', get_bloginfo('version'), NONCE_KEY) . '" />';
			case 'export': // retain export generator
				return '<!-- generator="WordPress/' . get_bloginfo_rss('version') . '" created="'. date('Y-m-d H:i') . '" -->';
			default:
				return '';
		}
	}
	
}
