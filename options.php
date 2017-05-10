<?php
/**
 * 七牛存储选项设置页面
 * 
 * @author  Yang,junlong at 2017-05-08 18:28:05 build.
 * @version $Id$
 */

$qiniu_options = get_option('qiniu_options');
?>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2><?php echo $title;?></h2>

    <form name="form1" method="post" action="<?php echo wp_nonce_url('./options-general.php?page=' . $plugin_page);  ?>">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="ak">AccessKey</label></th>
                    <td>
                        <input name="ak" type="text" id="ak" class="regular-text" value="<?php echo $qiniu_options['ak'];?>">
                        <p class="description">请填写七牛AccessKey</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="sk">SecretKey</label></th>
                    <td>
                        <input name="sk" type="text" id="sk" class="regular-text" value="<?php echo $qiniu_options['sk'];?>">
                        <p class="description">请填写七牛SecretKey</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="bucket">存储空间名称</label></th>
                    <td>
                        <input name="bucket" type="text" id="bucket" class="regular-text" value="<?php echo $qiniu_options['bucket'];?>">
                        <p class="description">请填写七牛中设置的Bucket识别符</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="domain">外链默认域名</label></th>
                    <td>
                        <input name="domain" type="text" id="domain" class="regular-text" value="<?php echo $qiniu_options['domain'];?>">
                        <p class="description">请填写七牛外链默认域名</p>
                    </td>
                </tr>
            </tbody>

        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改"></p>
    </form>

    <div id="icon-options-general" class="icon32"><br></div>
    <h2>同步历史文件</h2>
    <p>如果您需要上传之前的文件到七牛云，请进行如下操作：(<strong>操作之前一定要备份数据库！</strong>)</p>
	
	<style type="text/css">
		.sync-info-wrap {
			height: 150px;
			width: 500px;
			border: 1px solid #ccc;
			overflow: auto;
			background: #f1f1f1;
		}
		.sync-info-wrap p {
			margin: 0;
		}
	</style>
	<div class="sync-info-wrap">
		<div class="sync-info-in">
			
		</div>
	</div>

	<p class="sync"><input type="submit" name="sync" id="sync" class="button button-success" value="同步历史文件"></p>
</div>

<script type="text/javascript">
	var $ = jQuery;

	// 同步历史文件脚本
	$('#sync').click(function(event) {
		event.stopPropagation();
		var total = 0;

		(function(post_id) {
			var args = arguments;
			
			$.post('', {
				qiniu_sync: 1,
				post_id: post_id
			}, function(data) {
				var post_ids = data['data'];
				var post_id = data['post'];

				var oldfile = data['oldfile'];
				var newfile = data['newfile'];

				var curnum = total - post_ids.length;

				if(post_ids.length == 0) {
					$('.sync-info-in').html('您已同步完所有文件...');
					return;
				}

				args.callee(post_id);



				if (oldfile) {
					$('.sync-info-in').append('<p>'+oldfile+' to '+newfile+' 同步成功...('+curnum+'/'+total+')</p>');
					$('.sync-info-wrap').scrollTop($('.sync-info-in').height());
				} else {
					$('.sync-info-in').html('开始同步文件...');
					total = post_ids.length;
				}
			}, 'json');
		})();
	});
</script>