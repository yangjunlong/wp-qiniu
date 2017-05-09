<?php
/**
 * 七牛云存储
 * 
 * @author  Yang,junlong at 2017-05-08 10:29:17 build.
 * @version $Id$
 */

require_once 'qiniu-sdk-7.1.3/autoload.php';
// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;

// 文件上传方法
function qiniu_upload_file ($file, $key) {
	if (!file_exists($file)) {
		return false;
	}
    static $uploadMgr = null;
    static $token = '';
    if (!$uploadMgr) {
        $qiniu_options = get_option('qiniu_options');

        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = $qiniu_options['ak'];
        $secretKey = $qiniu_options['sk'];

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);

        // 要上传的空间
        $bucket = $qiniu_options['bucket'];;

        // 生成上传 Token
        $token = $auth->uploadToken($bucket);

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
    }
    list($ret, $err) = $uploadMgr->putFile($token, $key, $file);
    if ($err !== null) {
        return $err;
    } else {
        return $ret;
    }
}

// 上传文件到七牛服务器
add_filter('wp_handle_upload', 'qiniu_handle_upload');
function qiniu_handle_upload ($data) {
    $file = $data['file'];
    
    $qiniu_options = get_option('qiniu_options');
    $domain = trim($qiniu_options['domain'], '/') . '/';

    $fixfile = qiniu_fixfile($file);
    
    $key = md5($fixfile['file']) . '.' . $fixfile['exte'];

    $result = qiniu_upload_file($file, $key);

    $data['url'] = $domain . $key;
    //$data['file'] = $key;

    return $data;
}

/**
 * 生成缩略图后立即上传
 *
 * @see  https://developer.qiniu.com/dora/api/1279/basic-processing-images-imageview2
 */
add_filter('wp_generate_attachment_metadata', 'qiniu_update_attachment_meta', 999);
function qiniu_update_attachment_meta ($metadata, $upload = false) {
    if(empty($metadata)) {
        return $metadata;
    }

    $file = $metadata['file'];

    $wp_upload_dir = wp_upload_dir();
	$basedir = $wp_upload_dir['basedir'];

	$filename = $basedir . '/'. $file;

    $fixfile = qiniu_fixfile($file);

    $key = md5($fixfile['file']) . '.' . $fixfile['exte'];

    if($upload) {
		if (qiniu_upload_file($filename, $key) == false) {
			return;
		}
	}

    //$metadata['file'] = $key;
    $metadata['file'] = $key;

    $sizes = $metadata['sizes'];
    //上传小尺寸文件
    if (isset($sizes) && count($sizes) > 0){
        foreach ($sizes as $k => $img){
        	if(empty($img['width'])) {
        		return;
        	}
        	$w = $img['width'];
        	$h = $img['height'];

        	switch ($k) {
        		case 'thumbnail':
        			$sizes[$k]['file'] = $key . '?imageView2/1/w/'.$w.'/h/'.$h.'/';
        			break;
        		case 'medium':
        			$sizes[$k]['file'] = $key . '?imageView2/2/w/'.$w.'/h/'.$h.'/';
        		case 'large':
        			$sizes[$k]['file'] = $key . '?imageView2/2/w/'.$w.'/h/'.$h.'/';
        		default:

        	}
        	$metadata['sizes'] = $sizes;       
        }
    }

    $metadata['qiniu'] = true;

    return $metadata;
}

// 更新附件信息
add_filter('update_attached_file', 'qiniu_update_attachment');
function qiniu_update_attachment($file) {
	$fixfile = qiniu_fixfile($file);

	$key = md5($fixfile['file']) . '.' . $fixfile['exte'];

    $result = qiniu_upload_file($file, $key);
	return $file;
}

add_filter('wp_get_attachment_url', 'qiniu_format_attachment_url');
function qiniu_format_attachment_url($url) {
	$urls = parse_url($url);
	$path = trim($urls['path'], '/');
	$info = pathinfo($path);
	$extension = $info['extension'];

	$key = md5($path) . '.' . $extension;

	$qiniu_options = get_option('qiniu_options');
    $domain = trim($qiniu_options['domain'], '/');

	return $domain . '/'. $key;
}

function qiniu_fixfile($file) {
	$wp_upload_dir = wp_upload_dir();

    $info = pathinfo($file);
    $basename = $info['basename'];
    $extension = $info['extension'];

    $path = trim($wp_upload_dir['subdir'], '/');

    return array(
    	'file' => $path . '/' . $basename,
    	'exte' => $extension
    );
}

add_action('plugins_loaded', 'qiniu_textdomain');
function qiniu_textdomain() {
    load_plugin_textdomain('wp-qiniu', false, dirname(plugin_basename( __FILE__ )) . '/lang');
}

add_action('admin_menu', 'qiniu_add_setting_page');
function qiniu_add_setting_page() {
    // 在 "设置" 菜单中添加一个管理子菜单，方便主题和插件的设置。
    add_options_page('七牛存储选项', '七牛存储', 'manage_options', 'wp-qiniu/options.php');
}

// 表单提交提示信息
add_action('admin_notices', 'qiniu_warning');
function qiniu_warning($data) {
    if ($_GET['page'] == 'wp-qiniu/options.php') {
        // submit form
        if(!empty($_POST['submit'])) {
            // todo something

            $options = array(
                'ak'            => trim($_POST['ak']),
                'sk'            => trim($_POST['sk']),
                'bucket'        => trim($_POST['bucket']),
                'domain'        => trim($_POST['domain'])
            );

            if (empty($options['domain'])) {
                echo "<div id='qiniu-warning' class='updated fade'><p>你需要填写外链默认域名</p></div>";
                return;
            }

            $result = update_option('qiniu_options', $options);

            if ($result) {
                echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>设置已保存。</strong></p></div>';
            }
        }
    }
}

add_action('init', 'qiniu_sync_handle');
function qiniu_sync_handle() {
	global $wpdb;

	// 同步历史文件操作
	if(!empty($_POST['qiniu_sync'])) {

		if(!empty($_POST['post_id'])) {
			$post_id = $_POST['post_id'];
			$postmeta = get_post_meta($post_id, '_wp_attachment_metadata');
			$postmeta = $postmeta[0];

			if(!empty($postmeta['file'])) {
				$oldfile = $postmeta['file'];

				$metadata = qiniu_update_attachment_meta($postmeta, true);

				$newfile = $metadata['file'];

				update_post_meta($post_id, '_wp_attachment_metadata', $metadata);
			}
		}

		$posts = $wpdb->get_results("SELECT ID, post_date, post_title, post_parent FROM $wpdb->posts where post_type='attachment'");
		$post_ids = array();
		foreach ($posts as $post) {
			$postmeta = get_post_meta($post->ID, '_wp_attachment_metadata');
			$postmeta = $postmeta[0];

			if(empty($postmeta['qiniu']) && !empty($postmeta['file'])) {
				// 新的meta data
				//$metadata = qiniu_update_attachment_meta($postmeta[0]);

				array_push($post_ids, $post->ID);
			}
		}

		echo json_encode(array(
			'data' => $post_ids,
			'post' => $post_ids[0],
			'oldfile' => $oldfile,
			'newfile' => $newfile
		));
		die();
	}
}
