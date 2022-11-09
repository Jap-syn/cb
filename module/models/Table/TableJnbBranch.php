<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_JnbBranch(JNB支店マスター)テーブルへのアダプタ
 */
class TableJnbBranch {

    protected $_name = 'M_JnbBranch';
	protected $_primary = array('JnbBranchCode');
	protected $_adapter = null;

	/**
	 * テーブルのシーケンス設定。このテーブルは自然キーを使用する
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_sequence = false;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct(Adapter $adapter)
	{
	    $this->_adapter = $adapter;
	}

	/**
	 * JNB支店マスターデータを取得する
	 *
	 * @param string $jnbBranchCode JNB支店コード
	 * @return ResultInterface
	 */
	public function find($jnbBranchCode)
	{
        $sql  = " SELECT * FROM M_JnbBranch WHERE JnbBranchCode = :JnbBranchCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':JnbBranchCode' => $jnbBranchCode,
        );

        return $stm->execute($prm);
	}

	/**
	 * 削除保護対象の支店コード一覧を取得する
	 *
	 * @static
	 * @return array
	 */
	public static function getProtectedBranchCodes() {
	    $results = array();
		for($i = 701; $i <= 710; $i++) $results[] = (string)$i;
		return $results;
	}

	/**
	 * すべての支店情報を連想配列で取得する。
	 * 戻りはキーが支店コードで、値は支店コード、支店名、削除保護フラグを格納した連想配列となる
	 *
	 * @return array
	 */
	public function getAllBranchInfo() {

        $protected = self::getProtectedBranchCodes();

        $results = array();
        $ri = $this->_adapter->query(" SELECT * FROM M_JnbBranch ")->execute(null);
        foreach($ri as $row) {
            $results[$row['JnbBranchCode']] = array(
                    'code' => $row['JnbBranchCode'],
                    'name' => $row['JnbBranchName'],
                    'protected' => in_array($row['JnbBranchCode'], $protected) ? 1 : 0
            );
        }
        return $results;
	}

	/**
	 * 新しい支店名を追加する。
	 * このメソッドは、支店コード／支店名の形式不正や支店コードの重複登録に対して常に例外をスローする
	 *
	 * @param string $code 支店コード
	 * @param string $name 支店名
	 * @return プライマリキーのバリュー
	 */
	public function addBranchName($code, $name) {

        $code = $this->_checkBranchCode($code);
        $name = $this->_checkBranchName($name);

        return $this->saveNew(array('JnbBranchCode' => $code, 'JnbBranchName' => $name));
	}

	/**
	 * 指定の支店の支店名を更新する。
	 * このメソッドは、支店コード／支店名の形式不正時に例外をスローする
	 *
	 * @param string $code 支店コード
	 * @param string $name 支店名
	 * @return ResultInterface
	 */
	public function modifyBranchName($code, $name) {

        $code = $this->_checkBranchCode($code);
        $name = $this->_checkBranchName($name);

        $row = $this->find($code)->current();
        if (!$row) throw new \Exception('invalid JnbBranchCode specified');

        return $this->saveUpdate(array('JnbBranchName' => $name), $code);
	}

	/**
	 * 指定の支店をマスターから削除する
	 * 対象の支店が削除保護対象の場合、このメソッドはなにも実行しない
	 *
	 * @param string $code
	 */
	public function removeBranchName($code) {

        $code = $this->_checkBranchCode($code);

        $map = $this->getAllBranchInfo();
        if(isset($map[$code]) && !$map[$code]['protected']) {
            $this->_adapter->query(" DELETE FROM M_JnbBranch WHERE JnbBranchCode = :JnbBranchCode ")->execute(array(':JnbBranchCode' => $code));
        }
	}

	/**
	 * 支店コードをチェックし、整備する
	 *
	 * @access protected
	 * @param string $code 支店コード
	 * @return string 整備された支店コード
	 */
	protected function _checkBranchCode($code) {
	    $code = trim(nvl($code));
		if(!strlen($code)) throw new \Exception('JnbBranchCode is empty.');
		if(strlen($code) > 3) throw new \Exception('JnbBranchCode is greater than 3 characters long.');
		if(!preg_match('/^\d+$/', $code)) {
			throw new \Exception('JnbBranchCode contains not only digit characters.');
		}
		return $code;
	}

	/**
	 * 支店名をチェックし、整備する
	 *
	 * @access protected
	 * @param string $name 支店名
	 * @return string 整備された支店名
	 */
	protected function _checkBranchName($name) {
	    $name = trim(nvl($name));
		if(!strlen($name)) throw new \Exception('JnbBranchName is empty.');
		if(strlen($name) > 255) throw new \Exception('JnbBranchName is greater than 255 characters long.');
		return $name;
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO M_JnbBranch (JnbBranchCode, JnbBranchName) VALUES (";
        $sql .= "   :JnbBranchCode ";
        $sql .= " , :JnbBranchName ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':JnbBranchCode' => $data['JnbBranchCode'],
                ':JnbBranchName' => $data['JnbBranchName'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param @param string $jnbBranchCode JNB支店コード
	 * @return ResultInterface
	 */
	public function saveUpdate($data, $jnbBranchCode)
	{
        $row = $this->find($jnbBranchCode)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_JnbBranch ";
        $sql .= " SET ";
        $sql .= "     JnbBranchName = :JnbBranchName ";
        $sql .= " WHERE JnbBranchCode = :JnbBranchCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':JnbBranchCode' => $jnbBranchCode,
                ':JnbBranchName' => $row['JnbBranchName'],
        );

        return $stm->execute($prm);
	}
}
