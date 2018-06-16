<?php
/*
Plugin Name: wpX Cache Controller
Plugin URI: 
Description: wpX のサーバーキャッシュをWordPressの更新にあわせてクリアするプラグインです。
Version: 1.0.0
Author: XSERVER,Inc.
Author URI: http://www.wpx.ne.jp/
License: (GPL2)
*/

class WpxAccelerator
{
    protected $cacheClearUrl = array();
    
    protected $resisterEvent = array(
    		'publish_post',
            'edit_post',
            'deleted_post',
        	'switch_theme',
    	);
    
    public function init()
    {
        foreach ($this->resisterEvent as $event)
        {
            add_action($event, array($this, 'purgePost'));
        }

        add_action('shutdown', array($this, 'executePurge'));
        add_action('switch_theme', array(&$this, 'changeThemes'));
    }

	public function changeThemes(){
         $path = ABSPATH. 'wp-content/cache/supercache';

        if (file_exists($path) && is_dir($path)){
            $this->remove_directory($path);
        }
    }

    public function executePurge()
    {
        $cacheClearUrl = array_unique($this->cacheClearUrl);

        foreach($cacheClearUrl as $url)
        {
            $this->cacheClearByUrl($url);
        }
        
        if (!empty($cacheClearUrl))
        {
            $this->cacheClearByUrl(home_url());
        }        
    }

    protected function cacheClearByUrl($url)
    {
        $url = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_ADDR'];
        $host = $_SERVER['SERVER_NAME'];
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_HTTPHEADER, ['Host: ".$host."']);
        curl_setopt($c, CURLOPT_NOBODY, true);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PURGE');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($c, CURLOPT_HEADER, true);
        curl_setopt($c, CURLOPT_TIMEOUT, 2);
        $res = curl_exec($c);
        curl_close($c);
        $ret = (strpos($res, "200 OK") !== false);
        
        return $ret;
    }

    public function purgePost($postId)
    {
        array_push($this->cacheClearUrl, get_permalink($postId));
    }
    /* ディレクトリ削除メソッド */
    public function remove_directory($dir) {
      if ($handle = opendir("$dir")) {
       while (false !== ($item = readdir($handle))) {
         if ($item != "." && $item != "..") {
           if (is_dir("$dir/$item")) {
             $this->remove_directory("$dir/$item");
           } else {
             unlink("$dir/$item");
           }
         }
       }
       closedir($handle);
       rmdir($dir);
      }
    }
}

$wpxAcceleratorObj = new WpxAccelerator();
$wpxAcceleratorObj->init();