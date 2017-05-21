/**
 * fis编译打包工具配置文件
 * 
 * @author  Yang,junlong at 2017-05-21 22:26:09 build.
 * @version $Id$
 */
var DEPLOYS = {
    remote: {
        host: 'http://sobird.me',
        path: '/home/sobird/domains/sobird.me/public_html/wp-content/plugins/wp-qiniu'
    }
};

fis.util.map(DEPLOYS, function(key, item) {
    var host = item['host'];
    var path = item['path'];
    var receiver = host + '/receiver.php';

    var replaceFrom = /http:\/\/sobird.me/g;
    var replaceTo = function(domain) {
        switch (domain) {
            case '':
                break;
            case '':
                break
            default:

        }
    };

    fis.config.set('deploy.' + key, [
        {
            receiver: receiver,
            from: '/',
            subOnly: true,
            to: path
        }
    ])
});

// static cdn domian
fis.config.set('roadmap.domain', 'http://ohwfdg6ch.bkt.clouddn.com/');
