<?php
/**
Plugin name: WP Obscurity
Description: Obscure your WordPress installation.
Author: wells
Version: 0.0.1
*/

require 'class.wp-obscurity.php';

if ( defined("MAX_OBSCURITY") && MAX_OBSCURITY ){
	$wp_obscurity = WP_Obscurity::instance('max');
} else {
	$wp_obscurity = WP_Obscurity::instance( get_option('obscurity_settings', array()) );
}

$wp_obscurity->run();

unset( $wp_obscurity );

add_action('init', '_obscurity_admin_init', 20);
	
	function _obscurity_admin_init(){
		
		if ( !is_admin() ) return;
		
		add_action('admin_menu', '_wp_obscurity_admin_menu');
		
		function _wp_obscurity_admin_menu(){
		
			$page = add_submenu_page('options-general.php', 'WP Obscurity', 'Obscurity', 'manage_options', 'wp-obscurity', '_wp_obscurity_admin_page');
		
			// Add print scripts and styles action based off the option page hook
			#add_action( 'admin_print_scripts-' . $page, array( $this, 'admin_scripts' ) );
			#add_action( 'admin_print_styles-' . $page, array( $this, 'admin_styles' ) );	
		}
		
		function _wp_obscurity_admin_page(){
			require 'admin-page.php';	
		}
	}	

function _wp_obscurity_page_request(){
	
	$info = get_obscurity_settings_info();
	$option = array();
	
	foreach( $_POST['obscurity'] as $key => $value ){
		
		if ( !isset( $info[ $key ] ) ) continue;
		
		$option[ $key ] = wp_filter_kses( $value );
	}
	
	update_option( 'obscurity_settings', $option );
	
	return true;
}

function wp_obscurity_get_option(){
	
	$opt = get_option( 'obscurity_settings', array() );	
	
	if ( empty($opt) ){
		$keys = array_keys( get_obscurity_settings_info() );
		$opt = array_fill_keys( $keys, 'display' );
	}
	
	return $opt;
}

function get_obscurity_setting( $name ){
	
	$option = wp_obscurity_get_option();
	
	return isset( $option[ $name ] ) ? $option[ $name ] : 'display';
}

function get_obscurity_settings_info( $name = null ){
	
	$settings = array(
		'generator' => array(
			'name' 			=> 'WP Generator',
			'actions'		=> array( 'display' => 'Display', 'remove' => 'Remove', 'hash' => 'Hash' ),
			'description'	=> 'Remove or hash the WP Generator tag. This conceals the version of WordPress you are using.',
		),
		'version-urls' => array(
			'name' 			=> 'WP Version in URLs',
			'actions' 		=> array( 'display' => 'Display', 'remove' => 'Remove', ),
			'description' 	=> 'Remove the <code>?ver=</code> from the end of scripts and stylesheets.',
		),
		'head-tags' => array(
			'name' 			=> 'Extra &lt;head&gt; Tags',
			'actions'		=> array( 'display' => 'Display', 'remove' => 'Remove' ),
			'description'	=> 'Remove extra meta tags from the page &lt;head&gt;.',
		),
		'xpingback' => array(
			'name' 			=> 'X-Pingback Header',
			'actions' 		=> array( 'display' => 'Display', 'remove' => 'Remove', ),
			'description' 	=> 'Remove the X-Pingback header.',
		),
		'login-errors' => array(
			'name' 			=> 'Login Error Messages',
			'actions'		=> array( 'display' => 'Display', 'obscure' => 'Obscure' ), 
			'description'	=> 'Show less descriptive login errors. E.g. if a user enters an incorrect password, don\'t tell them the username exists.',
		),
	);
	
	if ( !empty( $name ) ){
		if ( isset( $settings[ $name ] ) ){
			return $settings[ $name ];
		} else {
			return null;	
		}
	}
	
	return $settings;	
}

function get_obscurity_setting_preview( $setting_slug ){

	$value = get_obscurity_setting( $setting_slug );

	switch($value) {
			
		case 'remove':
			echo '<i>None</i>';	
			return;
			break;
		
		case 'obscure':
			
			if ( 'login-errors' === $setting_slug ){
				echo '"Invalid login credentials."';
				return;
			}
			
			break;
		
		case 'hash':
			
			if ( 'generator' === $setting_slug ){
				echo '<code>&lt;meta name="generator" content="' . md5( 'wordpress-version-' . get_bloginfo( 'version' ) ) . '" /&gt;</code>';	
				return;
			}
			
			break;
			
		case 'display':
		case '':
		default:
			
			if ( 'generator' === $setting_slug ){
				echo '<code>' . get_the_generator('xhtml') . '</code>';
				return;
			}
			elseif ( 'xpingback' === $setting_slug ){
				echo '<code>X-Pingback: ' . get_bloginfo('pingback_url') . '</code>';	
				return;
			}
			elseif ( 'login-errors' === $setting_slug ){
				echo '"You have entered an incorrect password."';
				return;
			}
			elseif ( 'head-tags' === $setting_slug ){
				$url = untrailingslashit(get_site_url());
				echo '&lt;link href="' . $url . '/xmlrpc.php?rsd" title="RSD" type="application/rsd+xml" rel="EditURI"&gt;<p>
&lt;link href="' . $url . '/wp-includes/wlwmanifest.xml" type="application/wlwmanifest+xml" rel="wlwmanifest"&gt;</p><p>
&lt;link href="' . $url . '/goodbye-world/" title="Goodbye World" rel="prev"&gt;</p><p>
&lt;link href="' . $url . '/hello-world/feed/" title="' . get_bloginfo('name') . ' &raquo; Hello World Comments Feed" type="application/rss+xml" rel="alternate"&gt;</p>';	
				return;
			}
			elseif ( 'version-urls' === $setting_slug ){
				global $wp_version;
				echo '<code>?ver=' . $wp_version . '</code>';	
				return;
			}
			else {
				echo '<i>Preview not available</i>';	
				return;
			}
			break;
	}
		
}