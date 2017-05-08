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
	echo __FILE__;
}

add_action( 'plugins_loaded', 'postviews_textdomain2' );
function postviews_textdomain2() {
    echo '232323';
    load_plugin_textdomain( 'wp-postviews', false, dirname( plugin_basename( __FILE__ ) ) );
}

add_action('admin_menu', 'qiniu_add_setting_page');
function qiniu_add_setting_page() {
    /**
     * 函数是在“设置”菜单中添加一个管理子菜单，方便主题和插件的设置。
     * 
     */
    add_options_page('七牛存储选项', '七牛存储', 'manage_options', 'wp-qiniu/options.php');
}

function qiniu_setting_page() {

}