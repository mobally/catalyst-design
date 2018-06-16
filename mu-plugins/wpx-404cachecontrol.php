<?php
/*
Plugin Name: wpX 404 Cache
Plugin URI: 
Description: 404ページをキャッシュするためのプラグインです。
Version: 1.0.0
Author: XSERVER,Inc.
Author URI: www.wpx.ne.jp
License: GPL2
*/

function remove_nocache_404(){
    if( is_404() ){
    	header_remove("Pragma");
    	header_remove("Cache-Control");
    	header_remove("Expires");
    }
}
add_action('wp','remove_nocache_404');
?>