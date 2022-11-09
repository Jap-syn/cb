<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_SmbcpaBranch(Smbcpa支店マスター)テーブルへのアダプタ
 */
class TableSmbcpaBranch {

    protected $_name = 'M_SmbcpaBranch';
    protected $_primary = array('SmbcpaBranchCode');
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
     * Smbcpa支店マスターデータを取得する
     *
     * @param string $smbcpaBranchCode Smbcpa支店コード
     * @return ResultInterface
     */
    public function find($smbcpaBranchCode)
    {
        $sql  = " SELECT * FROM M_SmbcpaBranch WHERE SmbcpaBranchCode = :SmbcpaBranchCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcpaBranchCode' => $smbcpaBranchCode,
        );

        return $stm->execute($prm);
    }

    /**
     * 削除保護対象の支店コード一覧を取得する
     *
     * @static
     * @return array
     * @see JNB版と違い削除保護対象は設置しない
     */
    public static function getProtectedBranchCodes() {
        $results = array();
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
        $ri = $this->_adapter->query(" SELECT * FROM M_SmbcpaBranch ")->execute(null);
        foreach($ri as $row) {
            $results[$row['SmbcpaBranchCode']] = array(
                    'code' => $row['SmbcpaBranchCode'],
                    'name' => $row['SmbcpaBranchName'],
                    'protected' => in_array($row['SmbcpaBranchCode'], $protected) ? 1 : 0
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

        return $this->saveNew(array('SmbcpaBranchCode' => $code, 'SmbcpaBranchName' => $name));
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
        if (!$row) throw new \Exception('invalid SmbcpaBranchCode specified');

        return $this->saveUpdate(array('SmbcpaBranchName' => $name), $code);
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
            $this->_adapter->query(" DELETE FROM M_SmbcpaBranch WHERE SmbcpaBranchCode = :SmbcpaBranchCode ")->execute(array(':SmbcpaBranchCode' => $code));
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
        if(!strlen($code)) throw new \Exception('SmbcpaBranchCode is empty.');
        if(strlen($code) > 3) throw new \Exception('SmbcpaBranchCode is greater than 3 characters long.');
        if(!preg_match('/^\d+$/', $code)) {
            throw new \Exception('SmbcpaBranchCode contains not only digit characters.');
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
        if(!strlen($name)) throw new \Exception('SmbcpaBranchName is empty.');
        if(strlen($name) > 255) throw new \Exception('SmbcpaBranchName is greater than 255 characters long.');
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
        $sql  = " INSERT INTO M_SmbcpaBranch (SmbcpaBranchCode, SmbcpaBranchName) VALUES (";
        $sql .= "   :SmbcpaBranchCode ";
        $sql .= " , :SmbcpaBranchName ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcpaBranchCode' => $data['SmbcpaBranchCode'],
                ':SmbcpaBranchName' => $data['SmbcpaBranchName'],
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

    /**
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param @param string $smbcpaBranchCode Smbcpa支店コード
     * @return ResultInterface
     */
    public function saveUpdate($data, $smbcpaBranchCode)
    {
        $row = $this->find($smbcpaBranchCode)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_SmbcpaBranch ";
        $sql .= " SET ";
        $sql .= "     SmbcpaBranchName = :SmbcpaBranchName ";
        $sql .= " WHERE SmbcpaBranchCode = :SmbcpaBranchCode ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcpaBranchCode' => $smbcpaBranchCode,
                ':SmbcpaBranchName' => $row['SmbcpaBranchName'],
        );

        return $stm->execute($prm);
    }
}
