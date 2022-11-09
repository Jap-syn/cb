<?php
namespace Coral\Base\Application;

use Coral\Base\Application\BaseApplicationAbstract;

/**
 * OEM機能向け抽象アプリケーションクラスです
 */
abstract class BaseApplicationOem extends BaseApplicationAbstract {
    // アプリケーションパッケージの起点ディレクトリ名
    protected $_rootDirName;

    // アプリケーションディレクトリにアクセスするURLのサブディレクトリ名
    protected $_subDirName;

    /**
     * アプリケーションディレクトリにアクセスする、URLのサブディレクトリ名を取得する。
     * 例えばoemadminへアクセスするhttpdのエイリアスパスを「/oem-access-id/admin/」とすると
     * このメソッドは「admin」を返す必要がある。（※：oem-access-idはgetOemAccessId()の戻りに一致）
     *
     * @return string
     */
    public function getSubDirectoryName() {
        return $this->_subDirName;
    }
    /**
     * アプリケーションディレクトリにアクセスする、URLのサブディレクトリ名を設定する。
     * 例えばoemadminへアクセスするhttpdのエイリアスパスを「/oem-access-id/admin/」とすると
     * このメソッドで「admin」を設定する必要がある。（※：oem-access-idはgetOemAccessId()の戻りに一致）
     *
     * @param string $dirName サブディレクトリ名
     * @return BaseApplicationOem このインスタンス
     */
    public function setSubDirectoryName($dirName) {
        $this->_subDirName = $dirName;
        return $this;
    }

    /**
     * アプリケーションパッケージの起点ディレクトリを取得する。
     * 例えばOEM先管理画面パッケージでは「oemadmin」が返される。
     *
     * @return string
     */
    public function getRootDirectoryName() {
        if($this->_rootDirName === null) {
            // 未設定の場合は自動セットアップを行う
            $this->setRootDirectoryName();
        }
        return $this->_rootDirName;
    }
    /**
     * アプリケーションパッケージの起点ディレクトリを設定する。
     * 引数を省略した場合は、起動スクリプトパスから自動的にセットアップされる。
     *
     * @param null | string $dirName 起点ディレクトリ名
     * @return BaseApplicationOem このインスタンス
     */
    public function setRootDirectoryName($dirName = null) {
        if($dirName === null) {
            // 引数がnullの場合は起動スクリプトのパスから自動セットアップ
            $dirName = basename(realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
        }
        $this->_rootDirName = $dirName;
        return $this;
    }

	/**
	 * リクエストURIからOEM識別IDを抽出します
	 *
	 * @return string
	 */
	public function getOemAccessId() {
		preg_match('/^\/([^\/]+)\/?/', $_SERVER['REQUEST_URI'], $matches);
		if (nvl($matches[1], $this->getRootDirectoryName()) == 'sf') {
		    return 'smbcfs';
		}
		return nvl($matches[1], $this->getRootDirectoryName());
	}

	/**
	 * オーバーライド。アプリケーションIDを取得します。
	 *
	 * @return string
	 */
	public function getApplicationId() {
        // OEM識別IDを付与
		return sprintf('%s_%s', parent::getApplicationId(), $this->getOemAccessId());
	}

//     /**
//      * オーバーライド。フロントコントローラを取得します。
//      *
//      * @access public
//      * @return Zend_Controller_Front
//      */
//     public function getFrontController() {
//     	$front = $this->_frontController;
//         // baseUrlを固定設定してフロントコントローラを返却
// 		return $front->setBaseUrl(sprintf('/%s/%s', $this->getOemAccessId(), $this->getSubDirectoryName()));
//     }

    /**
     * OEM機能へのアクセスURLが定義済みエイリアス（＝正しいOEM識別IDを含むパス）であるかを
     * チェックし、アプリケーションパッケージのローカルディレクトリ名でのアクセスの場合はアクセス禁止を行う。
     *
     * @access protected
     */
	protected function checkInvalidOemId() {
//		if($this->getOemAccessId() == $this->getRootDirectoryName()) {
//			// OEM識別IDがパッケージディレクトリ名と一致（＝エイリアス経由でないアクセス）の場合は
//			// 403で終了
//			$this->return403Error();
//		}
        // エイリアス経由でなくなるので固定値比較
	    if($this->getOemAccessId() == "oemadmin" || $this->getOemAccessId() == "oemmember" || $this->getOemAccessId() == "oemmypage" || $this->getOemAccessId() == "oemorderpage") {
			// OEM識別IDがパッケージディレクトリ名と一致の場合は
			// 403で終了
			$this->return403Error();
		}
	}
}
