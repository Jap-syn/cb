<?php
namespace models\Sequence;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Ddl\Column\Boolean;

/**
 * S_Generalへのアダプタ。シーケンスオブジェクトとして利用する。
 *
 */
class SequenceGeneral {
	/**
	 * シーケンス名を格納するカラムの名前
	 *
	 * @var string
	 */
	const NAME_COLUMN_NAME = 'SeqName';

	/**
	 * シーケンス値を格納するカラムの名前
	 *
	 * @var string
	 */
	const VALUE_COLUMN_NAME = 'Value';

	/**
	 * 新規シーケンスの初期値
	 *
	 * @var int
	 */
	const DEFAULT_VALUE = 0;

	/**
	 * @static
	 * @private
	 *
	 * 優先的に使用するアダプタ種別
	 *
	 * @var string|null
	 */
	private static $_adapter_name;

	/**
	 * @static
	 * @private
	 *
	 * 優先的に使用する接続パラメータ配列
	 *
	 * @var array|null
	 */
	private static $_connection_params;

	/**
	 * @static
	 *
	 * 優先的に使用するアダプタ名を取得する。
	 * このメソッドが有効な文字列を返す場合、コンストラクタパラメータに
	 * 設定されているAdapterよりもこちらの設定を優先して割り当てられる
	 *
	 * @return string|null
	 */
	public static function getDbAdapterName() {
		return self::$_adapter_name;
	}

	/**
	 * @static
	 *
	 * 優先的に使用するアダプタ名を設定する。
	 *
	 * @param string|null $adapterName 設定するアダプタ名
	 */
	public static function setDbAdapterName($adapterName) {
		self::$_adapter_name = "$adapterName";
	}

	/**
	 * @static
	 *
	 * 優先的に使用する接続パラメータを取得する。
	 * このメソッドが有効な連想配列を返す場合、コンストラクタパラメータに
	 * 設定されているAdapterよりもこちらの設定を優先して割り当てられる
	 *
	 * @return array|null
	 */
	public static function getDbConnectionParams() {
		return self::$_connection_params;
	}

	/**
	 * @static
	 *
	 * 優先的に使用する接続パラメータを連想配列で設定する
	 *
	 * @param array|null $params 接続パラメータの連想配列
	 */
	public static function setDbConnectionParams($params) {
		if( ! is_array( $params ) ) {
			$params = null;
		}

		self::$_connection_params = $params;
	}

	/**
	 * シーケンステーブル名
	 *
	 * @var string
	 */
	protected $_name = 'S_General';

	/**
	 * プライマリキー名
	 *
	 * @var string
	 */
	protected $_primary = 'SeqName';

	/**
	 * シーケンス名に対応するカラムの名前
	 *
	 * @var string
	 */
	protected $_nameColName;

	/**
	 * シーケンス値に対応するカラムの名前
	 *
	 * @var string
	 */
	protected $_valueColName;

	/**
	 * 新規シーケンスのデフォルト値
	 *
	 * @var int
	 */
	protected $_defaultValue;

	/**
	 * DBアダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * トランザクションを行ってもよいか
	 * クラス用にアダプターを設定した場合は、個別のトランザクションを設定する
	 * @var Boolean
	 */
	protected $_isTran = false;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct(Adapter $adapter)
	{
//	    $this->_adapter = $adapter;
        // メタパラメータを初期化
        $this->setNameColumnName()
             ->setValueColumnName()
             ->setDefaultSequenceValue();

        // クラス変数にアダプタ情報が設定されている場合はそちらを採用する
        $params = self::getDbConnectionParams();
        if( is_array( $params ) ) {
            $this->_adapter = new Adapter($params);
            $this->_isTran = true; // アダプターを専属で使用する場合はトランザクションを行う
        }
        else {
            $this->_adapter = $adapter;
        }
	}

	/**
	 * デストラクタ
	 */
	public function __destruct()
    {
        if ($this->_adapter && $this->_isTran) {
            $this->_adapter->getDriver()->getConnection()->disconnect();
            $this->_adapter = null;
            $this->_isTran = false;
        }
    }

// Del By Takemasa(NDC) 20141224 Stt 廃止(使用禁止)
// 	/**
// 	 * テーブルクラスをセットアップする
// 	 */
// 	protected function _setup() {
// 		// 親クラスの_setupを呼び出しておく
// 		parent::_setup();
//
// 		// メタパラメータを初期化
// 		$this->setNameColumnName()
// 			->setValueColumnName()
// 			->setDefaultSequenceValue();
//
// 		// クラス変数にアダプタ情報が設定されている場合はそちらを採用する
// 		$adpName = self::getDbAdapterName();
// 		$params = self::getDbConnectionParams();
// 		if( is_string( $adpName ) && is_array( $params ) ) {
// 			$this->_setAdapter( Zend_Db::factory( $adpName, $params ) );
// 		}
// 	}
// Del By Takemasa(NDC) 20141224 End 廃止(使用禁止)

	/**
	 * シーケンス名に対応するカラム名を取得する
	 *
	 * @return string
	 */
	public function getNameColumnName() {
		return $this->_nameColName;
	}

	/**
	 * シーケンス名に対応するカラム名を設定する
	 *
	 * @param string $name シーケンス名に対応するカラムの名前
	 * @return SequenceGeneral
	 */
	public function setNameColumnName($name = null) {
		if( empty( $name ) ) $name = self::NAME_COLUMN_NAME;

		$this->_nameColName = $name;

		return $this;
	}

	/**
	 * シーケンス値に対応するカラム名を取得する
	 *
	 * @return string
	 */
	public function getValueColumnName() {
		return $this->_valueColName;
	}

	/**
	 * シーケンス値に対応するカラム名を設定する
	 *
	 * @param string $columnName シーケンス値に対応するカラムの名前
	 * @return SequenceGeneral
	 */
	public function setValueColumnName($columnName = null) {
		if( empty($columnName) ) $columnName = self::VALUE_COLUMN_NAME;

		$this->_valueColName = (string)$columnName;

		return $this;
	}

	/**
	 * 新規のシーケンスのデフォルト値を取得する
	 *
	 * @return int
	 */
	public function getDefaultSequenceValue() {
		return $this->_defaultValue;
	}

	/**
	 * 新規のシーケンスのデフォルト値を設定する
	 *
	 * @param int $value 新しく作成するシーケンスのデフォルト値
	 * @return SequenceGeneral
	 */
	public function setDefaultSequenceValue($value = null) {
		if( empty($value) ) $value = self::DEFAULT_VALUE;

		$this->_defaultValue = (int)$value;

		return $this;
	}

	/**
	 * 指定のシーケンス名に一致するシーケンス行を取得する
	 *
	 * @param string $name シーケンス名
	 * @param bool $isLock 行ロックするか否か
	 * @return ResultInterface
	 */
	public function getSequenceRow($name, $isLock = false) {

        if( empty($name) ) throw new \Exception( 'need sequence name' );

        $sql = " SELECT * FROM S_General WHERE SeqName = :SeqName ";
        if ($isLock) {
            $sql .= " FOR UPDATE ";
        }
        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SeqName' => $name,
        );

        $ri = $stm->execute($prm);
        if (!($ri->count() > 0)) {

            // INSERT
            $sql  = " INSERT INTO S_General (SeqName, Value) VALUES (";
            $sql .= "   :SeqName ";
            $sql .= " , :Value ";
            $sql .= " )";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':SeqName' => $name,
                    ':Value' => $this->getDefaultSequenceValue(),
            );

            $stm->execute($prm);

            // SELECT
            $sql = " SELECT * FROM S_General WHERE SeqName = :SeqName ";
            if ($isLock) {
                $sql .= " FOR UPDATE ";
            }

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':SeqName' => $name,
            );

            $ri = $stm->execute($prm);
        }

        return $ri;
	}

	/**
	 * 指定のシーケンスの現在の値を取得する
	 *
	 * @param string $name シーケンス名
	 * @return int
	 */
	public function currentValue($name) {
        $ri = $this->getSequenceRow($name);
        return (int)$ri->current()['Value'];
	}

	/**
	 * 指定のシーケンスの次の値を取得する
	 *
	 * @param string $name シーケンス名
	 * @return int
	 */
	public function nextValue($name) {

	    // 個別にアダプターを設定している場合は、トランザクションを貼って行ロックを行うことで
	    // 厳密にシーケンスを採番可能とする
	    if ($this->_isTran) {
	        $this->_adapter->getDriver()->getConnection()->beginTransaction();
	    }

	    try {

            // 指定のシーケンス名に一致するシーケンス行を取得する
            $ri = $this->getSequenceRow($name, $this->_isTran);
            $row = $ri->current();

            // 次値の決定
            $nextval = (int)$row['Value'] + 1;

            // UPDATE
            $sql = " UPDATE S_General SET Value = :Value WHERE SeqName = :SeqName ";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':Value' => $nextval,
                    ':SeqName' => $name,
            );

            $stm->execute($prm);

            if ($this->_isTran) {
                $this->_adapter->getDriver()->getConnection()->commit();
            }

	    } catch(\Exception $e){
            if ($this->_isTran) {
                $this->_adapter->getDriver()->getConnection()->rollback();
            }
	        throw $e;
        }

        return $nextval;
	}

}