<?php
namespace Coral\Base\Auth;

/**
 * ハッシュ化パスワードを取り扱うためのユーティリティクラス
 */
class BaseAuthUtility {
	/**
	 * パスワードのハッシュ化時に適用するストレッチング回数
	 *
	 * @var int
	 */
	const STRETCH_COUNT = 1000;

	/**
	 * コンストラクタで省略された場合の固定ソルト初期値。
	 * ソルト生成時にこの値が採用された場合は例外になるように設計されている
	 *
	 * @static
	 * @access protected
	 * @var string
	 */
	protected static $__defaultFixedSalt = null;

	/**
	 * このクラスでパスワードのハッシュ化を行う際に使用される固定ソルトの
	 * 規定値を取得する
	 *
	 * @static
	 * @return string
	 */
	public static function getDefaultFixedSalt() {
		return self::$__defaultFixedSalt;
	}

	/**
	 * このクラスでパスワードのハッシュ化を行う際に使用される固定ソルトの
	 * 規定値を設定する。
	 * このメソッドで設定された値は、インスタンス初期化時のコンストラクタで
	 * ソルト指定が省略された場合に利用される。
	 *
	 * @static
	 * @param string $fixedSalt 規定値として設定する固定ソルト
	 */
	public static function setDefaultFixedSalt($fixedSalt) {
		self::$__defaultFixedSalt = $fixedSalt;
	}

	/**
	 * インスタンスで使用する固定ソルト
	 *
	 * @access protected
	 * @var string
	 */
	protected $_fixedSalt = null;

    /**
     * パスワードハッシュの生成を停止するかのフラグ
     *
     * @access protected
     * @var boolean
     */
    protected $_hashDisabled = false;

	/**
	 * ハッシュ化で使用する固定ソルトを指定して新しいインスタンスを
	 * 初期化する
	 *
	 * @param string $fixedSalt このインスタンスのハッシュ化処理で使用する固定ソルト。
	 *                          省略時はクラスの規定値が採用される
	 */
	public function __construct($fixedSalt = null) {
		if($fixedSalt == null) $fixedSalt = self::getDefaultFixedSalt();
		$this->setFixedSalt($fixedSalt);
	}

	/**
	 * ハッシュ化で使用する固定ソルトを取得する
	 *
	 * @return string
	 */
	public function getFixedSalt() {
		return $this->_fixedSalt;
	}

	/**
	 * ハッシュ化で使用する固定ソルトを設定する
	 *
	 * @param string $salt ハッシュ化処理で使用する固定ソルト
	 * @return BaseAuthUtility このインスタンス自身
	 */
	public function setFixedSalt($salt) {
		$salt = (string)$salt;
		$this->_fixedSalt = $salt;
		return $this;
	}

    /**
     * 現在パスワードハッシュ演算を利用停止にしているかを取得する
     *
     * @return boolean
     */
    public function getHashDisabled() {
        return $this->_hashDisabled ? true : false;
    }

    /**
     * パスワードハッシュ演算の利用停止状態を設定する。
     * このメソッドによる設定は一時的なもので、trueを指定した場合は
     * generatePasswordHashメソッドを一度実行すると利用停止状態は解除される。
     *
     * @param boolean $disabled 利用停止にする場合はtrue、それ以外はfalseを指定する
     * @return BaseAuthUtility このインスタンス
     */
    public function setHashDisabled($disabled) {
        $this->_hashDisabled = $disabled ? true : false;
        return $this;
    }

	/**
	 * ソルト用プレフィックスと、インスタンスに設定されている固定ソルトを使用して
	 * パスワードのハッシュ化に使用するソルトを生成する
	 *
	 * @param string $prefix ソルト生成に使用する任意のプレフィックス
	 * @return string
	 */
	public function applySalt($prefix) {
		$fixedSalt = $this->getFixedSalt();
		return sprintf('%s%s', nvl($prefix), pack('H*', $fixedSalt));
	}

	/**
	 * 指定のIDとパスワードの組み合わせからソルトを適用したパスワードハッシュを生成する
	 *
	 * @param mixed $id アカウントID
	 * @param string $password ハッシュ対象のパスワード
	 * @return string パスワードハッシュ
	 */
	public function generatePasswordHash($id, $password) {
        if($this->getHashDisabled()) {
            // ハッシュ利用停止中は利用停止を解除して生パスワードをそのまま返す
            $this->setHashDisabled(false);
            return $password;
        }

		$salt = $this->applySalt(strtoupper($id));	// プレフィックスに使用するアカウントIDはレターケースをそろえる
		$hash = '';
		$stretchCount = self::STRETCH_COUNT;
		for($i = 0; $i < $stretchCount; $i++) {
			$hash = hash('sha256', sprintf('%s%s%s', $hash, $password, $salt));
		}
		return $hash;
	}
}
