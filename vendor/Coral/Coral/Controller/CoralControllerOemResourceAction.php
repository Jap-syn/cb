<?php
namespace Coral\Coral\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use oemadmin;

/**
 * oemadmin、oemmemberで画像などの固有リソースを取得するための抽象コントローラ
 */
abstract class CoralControllerOemResourceAction extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
     * @access protected
	 * @var Application
	 */
	protected $app;

    /**
     * 認証後のみアクセスできるリソース名のアクション別リスト
     * @access protected
     * @var array
     */
    protected $_need_auth_resources;

	/**
	 * IndexControllerを初期化する
	 */
	public function _init() {
        $this->app = Application::getInstance();

        // アクションごとに認証なしでのアクセスを禁止するリソース名を定義
        $this->_need_auth_resources = array(
            'image' => array('Imprint')
        );
	}

    /**
     * いま現在認証済みであるかを判断する
     * @abstract
     * @return boolean
     */
    public abstract function isAuthenticated();


	/**
	 * 画像リソース取得アクション
	 */
	public function imageAction() {
        $map = array(
            'n/a' => '',
            'logo-l' => 'LargeLogo',
            'logo-s' => 'SmallLogo',
            'imprint' => 'Imprint'
        );

        $params = $this->getParams();

        $key = $map[(isset($params['type'])) ? $params['type'] : 'n/a'];
        if(in_array($key, $this->_need_auth_resources['image']) && !$this->isAuthenticated()) {
            $key = '';
        }

        $data = '';
        $info = $this->_getOemInfo();
        if(isset($info[$key])) {
            $data = base64_decode($info[$key]);
        }

        // SMBCかつ、OEM管理画面以外の場合は、CBロゴに上書き
        if ($info['AccessId'] == 'smbcfs') { // SMBC
            if ( !$this->app instanceof oemadmin\Application) { // OEM管理画面以外
                if ($params['type'] == 'logo-l') {
                    $data = file_get_contents('./public/images/Atobarai_logo_2.gif');
                } elseif ($params['type'] == 'logo-s') {
                    $data = file_get_contents('./public/images/Atobarai_logo_3.gif');
                }
            }
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'image/jpeg' );
        $res->getHeaders()->addHeaderLine( 'Content-Length', strlen($data) );
        echo $data;

        return $this->getResponse();
	}

    /**
     * 現在ログイン中アカウントに関連付けられたOEM情報を取得する
     *
     * @access protected
     * @return array
     */
    protected function _getOemInfo() {
        $accId = $this->app->getOemAccessId();
        $table = new \models\Table\TableOem($this->app->dbAdapter);
        $row = $table->findByAccessId($accId)->current();
        return ($row) ? $row : array();
    }

    /**
     *  cssリソース取得アクション
     */
    public function cssAction(){
        // この実装では404を返す。必要に応じて派生コントローラでオーバーライドすること
		Application::getInstance()->return404Error();
	}

    /**
     * favicon取得アクション。
     */
    public function faviconAction() {
        // 16x16px、完全透過のfaviconデータを返す
        $info = array(
                'data' => base64_decode(join('', array(
                        'AAABAAEAEBAAAAEAGABoAwAAFgAAACgAAAAQAAAAIAAAAAEAGAAAAAAAAAAAAGAAAABgAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAP//',
                        'AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA'
                        ))),
                        'type' => 'image/vnd.microsoft.icon'
                        );
        $oem_info = $this->_getOemInfo();
        if(isset($oem_info['FavIcon'])) {
            // アイコンがアップロードされていた場合のみそのデータで上書き
            if(strlen($oem_info['FavIconType']) && $oem_info['FavIconType'] != 'url') {
                $info['data'] = base64_decode($oem_info['FavIcon']);
                $info['type'] = $oem_info['FavIconType'];
            }
        }
        $size = strlen($info['data']);

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', $info['type'] );
        $res->getHeaders()->addHeaderLine( 'Content-Length', $size );
        echo $info['data'];

        return $this->getResponse();
    }

    /**
     * 追加イメージ取得アクション
     */
    public function imageexAction() {
        $params = $this->getParams();

        $type = strtolower((isset($params['type'])) ? $params['type'] : '');
        $ext = $this->getExtraImageExtension($type);
        $info = $this->_getOemInfo();

        $data = null;
        if(isset($info['OemId'])) {
            // OEM向けの追加イメージをT_SystemPropertyから取得
            $sysProps = new \models\Table\TableSystemProperty($this->app->dbAdapter);

            $data = $sysProps->getExtraImageByOemId($info['OemId'], $type);
        }

        // ここまで画像データがなかった場合は画像ディレクトリからファイルを読み込む
        if($data == null) {
            $data = file_get_contents(sprintf('../images/%s.%s', $type, $ext));
        }

        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', sprintf('image/%s', $ext) );
        $res->getHeaders()->addHeaderLine( 'Content-Length', strlen($data) );
        echo $data;
    }

    /**
     * 指定の追加イメージタイプに対応する画像拡張子を取得する
     *
     * @access protected
     * @param string $imageType 追加イメージタイプ。ファイル名のベース名と見なされる
     * @return string 対応する拡張子
     */
    protected function getExtraImageExtension($imageType) {
        $map = array(
                'submenu_arrow' => 'gif'
        );
        if(isset($map[$imageType])) return $map[$imageType];

        // マップ未定義の場合はgifとする
        return 'gif';
    }
}
