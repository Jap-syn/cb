<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\BaseUtility;

/**
 * T_SelfBillingPropertyテーブルへのアダプタ
 */
class TableSelfBillingProperty
{
	protected $_name = 'T_SelfBillingProperty';
	protected $_primary = array('Seq');
	protected $_adapter = null;

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
	 * 指定事業者向けの拡張プロパティデータを取得する
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql  = " SELECT * FROM T_SelfBillingProperty WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定事業者向けの拡張プロパティデータを取得する
	 *
	 * @param int $ent_id 事業者ID
	 * @return ResultInterface
	 */
	public function findByEnterpriseId($ent_id)
	{
        $sql = " SELECT * FROM T_SelfBillingProperty WHERE EnterpriseId = :EnterpriseId ORDER BY Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $ent_id,
        );

        return $stm->execute($prm);
	}

	/**
	 * 同梱ツールによる請求書発行向けのアクセスキーを生成する。
	 * このメソッドはテーブル内に重複キーがないかのチェックを行う
	 *
	 * @return string 32バイトのアクセスキー
	 */
	public function generateAccessKey()
	{
	    $count = 100;
        while($count-- > 0) {
            // 16バイト長のデータをタネとする
            $source = BaseUtility::createRandomString(16);

            // MD5でハッシュした結果をキーとする
            $key = hash('MD5', $source);

            $sql = " SELECT * FROM T_SelfBillingProperty WHERE AccessKey = :AccessKey ";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':AccessKey' => $key,
            );

            if ($stm->execute($prm)->count() == 0) {
                // 重複がない場合は現在のキーで確定
                return $key;
            }
        }

        // 一定回数トライしてユニークキーを生成できない場合は例外を投げる
        // TODO: 重複頻度を検討する必要あり
        throw new \Exception('cannot generate unique key');
	}

// Del By Takemasa(NDC) 20141216 Stt 廃止(使用禁止)
// 	/**
// 	 * オーバーライド。このテーブル向けの新しい行オブジェクトを生成する。
// 	 * このメソッドで生成した行データは、AccessKeyとCreatedDateTimeが
// 	 * 初期化された状態となる
// 	 *
// 	 * @param null|array $data 初期データ。プライマリキーとLastLoginDateTime/LastLogoutDateTimeはクリアされる
// 	 * @return 新しい行データ
// 	 */
// 	public function createRow(array $data = array()) {
// 		$data = array_merge($data, array(
// 			'AccessKey' => $this->generateAccessKey(),
// 			'CreatedDateTime' => date('Y-m-d H:i:s')
// 		));
// 		unset($data['LastLoginDateTime']);
// 		unset($data['LastLogoutDateTime']);
// 		return parent::createRow($data);
// 	}
// Del By Takemasa(NDC) 20141216 End 廃止(使用禁止)

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
	    $sql  = " INSERT INTO T_SelfBillingProperty (EnterpriseId, AccessKey, CreatedDateTime, LastLoginDateTime, LastLogoutDateTime) VALUES (";
	    $sql .= "   :EnterpriseId ";
	    $sql .= " , :AccessKey ";
	    $sql .= " , :CreatedDateTime ";
	    $sql .= " , :LastLoginDateTime ";
	    $sql .= " , :LastLogoutDateTime ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':EnterpriseId' => $data['EnterpriseId'],
	            ':AccessKey' => $data['AccessKey'],
	            ':CreatedDateTime' => $data['CreatedDateTime'],
	            ':LastLoginDateTime' => $data['LastLoginDateTime'],
	            ':LastLogoutDateTime' => $data['LastLogoutDateTime'],
	    );

	    $ri = $stm->execute($prm);

	    return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

}
