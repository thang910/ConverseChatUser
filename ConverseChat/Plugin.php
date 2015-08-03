<?php
require_once 'xmpp-prebind/lib/XmppPrebind.php';
class Atmail_ConverseChat_Plugin extends Atmail_Controller_Plugin
{
    

    protected $_pluginFullName = 'Secure Chat';
    protected $_pluginAuthor = 'Nguyen Manh Thang<thang@linex.vn>';
    protected $_pluginDescription = 'Connecting people with the highest security';
    protected $_pluginCopyright = 'Copyright PRMAIL';
    protected $_pluginUrl = '';
    protected $_pluginNotes = '';
    protected $_pluginVersion = '1.0.0';
    protected $_pluginCompat = '7.0.0';
    protected $_pluginModule = 'mail';

    public function __construct()
    {
        parent::__construct();
        $this->_pluginDescription = <<<EOF
Adds the Converse Chat functionality to webmail only installs.
EOF;

    }
//    public function endHeadTag()
//    {
//        $version = Zend_registry::get('config')->global['version'];
//
//        $nav = <<<EOF
//
//            <link rel="stylesheet" type="text/css" media="screen, print" href="/mail/css/converse.css?{$version}">
//EOF;
//        echo $nav;
//    }
    public function endHeadTag()
    {
        $version = Zend_registry::get('config')->global['version'];
        $tipsySrc = $this->_getModuleBaseUrl() . "/plugininterface/index/Atmail_ConverseChat_Plugin/getAsset/file/" . base64_encode("js/converse/converse.js");
        $tipsyCss = $this->_getModuleBaseUrl() . "/plugininterface/index/Atmail_ConverseChat_Plugin/getAsset/file/" . base64_encode("css/converse.css");

        $fonticon1 = $this->_getModuleBaseUrl() . "/plugininterface/index/Atmail_ConverseChat_Plugin/getAsset/file/" . base64_encode("fonticons/fonts/icomoon.eot");
        $fonticon2 = $this->_getModuleBaseUrl() . "/plugininterface/index/Atmail_ConverseChat_Plugin/getAsset/file/" . base64_encode("fonticons/fonts/icomoon.woff");
        $fonticon3 = $this->_getModuleBaseUrl() . "/plugininterface/index/Atmail_ConverseChat_Plugin/getAsset/file/" . base64_encode("fonticons/fonts/icomoon.tff");
        $fonticon4 = $this->_getModuleBaseUrl() . "/plugininterface/index/Atmail_ConverseChat_Plugin/getAsset/file/" . base64_encode("fonticons/fonts/icomoon.svg");

        $nav = <<<EOF
    <script type="text/javascript" src="$tipsySrc?$version"></script>
    <link rel="stylesheet" href="$tipsyCss?$version" type="text/css" media="screen, print"/>
    <style type="text/css">
        @font-face {
            font-family: 'Converse-js';
            src: url("$fonticon1?-mnoxh0");
            src: url("$fonticon1?#iefix-mnoxh0") format("embedded-opentype"), url("$fonticon2?-mnoxh0")        format("woff"), url("$fonticon3?-mnoxh0") format("truetype"),url("$fonticon4?-mnoxh0#icomoon") format("svg");
            font-weight: normal;
            font-style: normal;
      }
    </style>


EOF;
        echo $nav;


    }
    public function getAsset($params)
    {
        $file = base64_decode($params['file']);

        $ext = strrchr($file, '.');
        if ($ext == '.css') {
            $contentType = 'text/css';
        } elseif ($ext == '.gif') {
            $contentType = 'image/gif';
        } elseif ($ext == '.js') {
            $contentType = 'text/javascript';
        }

        header("Content-Type: $contentType");
        $path = dirname(__FILE__);
        readfile("$path/$file");
    }

    public function setup() {


    }

    public function databind()
    {
        $db = Zend_Registry::get("dbAdapter");
        $db_values = $db->fetchAll("SELECT * FROM `#__converseChat`");
        return $settings = current($db_values);
    }
    public function XMPPPrebind(){

        $auth = Zend_Auth::getInstance();
        $userData = $auth->getIdentity();

        $email = explode('@',($userData['Account']));
        $password = $userData['password'];
        $username = $email[0];
        $domain = $email[1];
        $data = $this->databind();
        $prebind = $data['prebindURL'];

//        $xmppPrebind = new XmppPrebind($domain, 'https://apps.prmail.vn/http-bind/', '', false, false);
//        $xmppPrebind = new XmppPrebind($domain, 'http://talk.linex.co:5280/http-bind/', '', false, false);
        $xmppPrebind = new XmppPrebind($domain, $prebind, '', false, false);
        $xmppPrebind->connect($username, $password);
        $xmppPrebind->auth();
        $sessionInfo = $xmppPrebind->getSessionInfo(); // array containing sid, rid and jids
        header('Content-Type: application/json');
        echo json_encode($sessionInfo);
    }

    public function endBodyTag()
    {

        $url = $this->_getModuleBaseUrl();
        $server = $_SERVER[HTTP_HOST];
        $data = $this->databind();
        $prebind = $data['prebindURL'];
        $nav = <<<EOF

        <script language="JavaScript" type="text/javascript"> require(['converse'], function (converse) {
        converse.initialize({
            auto_list_rooms: false,
            auto_subscribe: false,          
            bosh_service_url: '$prebind',
            hide_muc_server: false,
            i18n: locales.en, // Refer to ./locale/locales.js to see which locales are supported
            prebind: true,
            keepalive: true,
            prebind_url: 'http://$server$url/plugininterface/index/Atmail_ConverseChat_Plugin/XMPPPrebind',
            show_controlbox_by_default: false,
            roster_groups: true,
            hide_muc_server:true,
            allow_registration:false,
            chatroom_password_form:true,
        });
    });
</script>

EOF;

        echo $nav;
    }

}

