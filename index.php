<?php
/**
 * 七牛云存储SDK
 *
 * Plugin Name: 七牛云存储
 * Plugin URI: http://sobird.me/wp-qiniu.html
 * Description: QINIU Plugin for Wordpress
 * Version: 1.0.0
 * Author: sobird
 * Author URI: http://sobird.me/
 * 
 * @author  Yang,junlong at 2017-05-08 10:29:17 build.
 * @version $Id$
 */

if(is_admin()) {
	require_once('wp-qiniu.php');
}