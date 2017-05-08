<?php
/**
 * 七牛存储选项设置页面
 * 
 * @author  Yang,junlong at 2017-05-08 18:28:05 build.
 * @version $Id$
 */


// submit form
if(!empty($_POST['submit'] )) {
    // todo something

    $options = array(
        'ak'            => trim(views_options_parse('ak')),
        'sk'            => trim(views_options_parse('sk'))
    );

    update_option('qiniu_options', $options);
}

$qiniu_options = get_option('qiniu_options');

?>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2><?php echo $title;?></h2>
    <div id="setting-error-settings_updated" class="updated settings-error"> 
    <p><strong>设置已保存。</strong></p></div>

    <form name="form1" method="post" action="<?php echo wp_nonce_url('./options-general.php?page=' . $plugin_page);  ?>">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="ak">AccessKey</label></th>
                    <td>
                        <input name="ak" type="text" id="ak" class="regular-text" value="<?php echo $qiniu_options['ak'];?>">
                        <p class="description">这里填写七牛AccessKey</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="sk">SecretKey</label></th>
                    <td>
                        <input name="sk" type="text" id="sk" class="regular-text" value="<?php echo $qiniu_options['sk'];?>">
                        <p class="description">这里填写七牛SecretKey</p>
                    </td>
                </tr>
            </tbody>

        </table>

        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改"></p>
    </form>
</div>