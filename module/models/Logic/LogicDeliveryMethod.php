<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableEnterprise;
use models\Table\TableDeliMethod;
use models\Table\TableOemDeliMethodList;

/**
 * 配送方法を管理するロジッククラス
 */
class LogicDeliveryMethod
{
	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
	protected $_adapter = null;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 *
	 */
	public function __construct(Adapter $adapter)
	{
        $this->_adapter = $adapter;
	}

	/**
	 * 指定の事業者データを取得する
	 *
	 * @param int $entId 事業者ID
	 * @return ResultInterface
	 */
	public function getEnterpriseData($entId)
	{
        $mdl = new TableEnterprise(_adapter);
        return $mdl->findEnterprise($entId);
	}

	/**
	 * 全配送方法を配送順で取得する
	 * @param int $oemId
	 * @return ResultInterface
	 */
	public function getAllDeliMethodList($oemId = 0)
	{
        //全配送方法を取得。ただしT_OemDeliveryMethodListに設定されているものはその順で表示し、その後M_DeliveryMethodの情報を表示する
        $sql = <<<EOQ
SELECT md.*
FROM   M_DeliveryMethod md LEFT OUTER JOIN T_OemDeliveryMethodList ol ON (ol.DeliMethodId = md.DeliMethodId AND IFNULL(ol.OemId,  0) = :OemId)
ORDER BY (CASE WHEN ol.OemId IS NOT NULL THEN ol.ListNumber ELSE md.DeliMethodId + 1000 END)
EOQ;
        return $this->_adapter->query($sql)->execute(array(':OemId' => $oemId));
	}

	/**
	 * 指定事業者向けの全配送方法を配送設定順で取得する
	 *
	 * @param int $entId 事業者ID
	 * @return ResultInterface
	 */
	public function getAllDeliMethodListByEnterpriseId($entId)
	{
        $ent = $this->getEnterpriseData($entId)->current();
        return $this->getAllDeliMethodList((int)$ent['OemId']);
	}

	/**
	 * 配送方法を配送順で取得する
	 * @param int $oemId
	 * @param boolean $strict 厳密モード指定
	 *                        trueを指定すると、T_OemDeliveryMethodListにデータがないOEM IDの場合は結果をnullとして返す
	 *                        省略時はfalse
	 * @return null | ResultInterface
	 */
	public function getDeliMethodList($oemId = 0, $strict = false)
	{
        $delmaster = new TableDeliMethod($this->_adapter);
        $odml = new TableOemDeliMethodList($this->_adapter);

        // 配送方法を取得。ただしT_OemDeliveryMethodListに設定されているものはその順で表示、なければM_DeliveryMethodの情報を表示する
        if($oemId != null) {
            // OemIdに紐づく配送順を取得
            $deliMethodIds = $odml->findDeliMethodIds($oemId);

            // 対象OemIdで配送順が設定されていれば配送先を取得する
            if(!empty($deliMethodIds)) {
                return $delmaster->findByDeliMethodIds($deliMethodIds);
            }
        }

        return $strict ? null : $delmaster->getValidAll();
	}

	/**
	 * 指定事業者向けの配送方法を配送設定順で取得する
	 *
	 * @param int $entId 事業者ID
	 * @param boolean $stric 厳密モード
	 *                        trueを指定すると、T_OemDeliveryMethodListにデータがないOEM IDの場合は結果を0件として返す
	 *                        省略時はfalse
	 * @return null | ResultInterface
	 */
	public function getDeliMethodListByEnterpriseId($entId, $strict = false)
	{
        $ent = $this->getEnterpriseData($entId)->current();
        return $this->getDeliMethodList((int)$ent['OemId'], $strict);
	}


	/**
	 * 指定の配送方法を取得する。返却値はArray。
	 * @param int $oemId、$deliMethodId
	 * @return ResultInterface
	 */
	public function getDeliMethod($oemId = null, $deliMethodId)
	{
        $delmaster = new TableDeliMethod($this->_adapter);
        $odml = new TableOemDeliMethodList($this->_adapter);

        // 配送方法を取得
        if($oemId != null) {
            // OEMに紐づく配送順を取得
            $alldata = $odml->findDeliMethodIds($oemId);

            // 指定OEMIDで配送順が設定されていた場合のみ
            if(!empty($alldata)) {
                return $odml->findDeliMethodId($oemId, $deliMethodId);
            }
        }

        return $delmaster->getValidDeliMethod($deliMethodId);
	}

	/**
	 * 指定OEM先配下の配送伝票自動仮登録が有効な事業者の、自動登録時の配送方法を補正する。
	 * 補正対象は、本処理実行時点で設定されている配送方法がOEM先に設定されている配送方法に含まれない事業者で、
	 * その時点で設定されているOEM毎配送方法の最初の方法に置き換える
	 *
	 * @param int $oemId OEM ID
	 * @return 補正された事業者の数
	 */
	public function fixEnterpriseAutoJournalDeliMethod($oemId)
	{
        $odml = new TableOemDeliMethodList($this->_adapter);
        $ent = new TableEnterprise($this->_adapter);

        $valid_method_ids = $odml->findDeliMethodIds($oemId);

        // カスタム配送先未登録の場合は何もしない
        if(empty($valid_method_ids)) return;

        // 更新する配送方法IDはリストの先頭とする
        $target_deli_method = $valid_method_ids[0];

        // 補正対象の事業者を抽出
        $sql = <<<EOQ
SELECT ent.EnterpriseId
FROM   T_Enterprise ent INNER JOIN T_Site sit ON (sit.EnterpriseId = ent.EnterpriseId)
WHERE  IFNULL(ent.OemId, 0) = :OemId
AND    sit.AutoJournalIncMode = 1
AND    sit.AutoJournalDeliMethodId NOT IN ( :AutoJournalDeliMethodId )
ORDER BY EnterpriseId
EOQ;

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
                ':AutoJournalDeliMethodId' => implode(',', $valid_method_ids),
        );

        $ri = $stm->execute($prm);

        $count = 0;
        foreach ($ri as $row) {
            $ent->saveUpdate(array('AutoJournalDeliMethodId' => $target_deli_method), $row['EnterpriseId']);
            $count++;
        }
        return $count;
	}

    /**
     * 加盟店向け配送方法を取得する。返却値は連想配列
     *
     * @param int $enterpriseId 加盟店ID
	 * @param boolean $isDummyOn ダミーを必要とするか？(初期値:true)
	 * @return array 配送方法
     */
	public function getEnterpriseDeliveryMethodList($enterpriseId, $isDummyOn = true)
    {
        // 1. [加盟店別配送方法マスター]検索
        $ri = $this->_adapter->query($this->_getBaseSeqEnterpriseDeliveryMethod())->execute(array(':EnterpriseId' => $enterpriseId));
        if ($ri->count() > 0) {
            // 結果が得られる場合は配列化して戻す
            $result = ($isDummyOn) ? array(0 => '---') : array();
            foreach ($ri as $row) {
                $result[$row['DeliMethodId']] = $row['DeliMethodName'];
            }
            return $result;
        }

        // 2. [OEM配送先順序]検索
        $sql = " SELECT IFNULL(OemId,0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $oemId = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current()['OemId'];

        $ri = $this->_adapter->query($this->_getBaseSeqOemDeliveryMethod())->execute(array(':OemId' => $oemId));
        if ($oemId > 0 && $ri->count() > 0) {
            // 結果が得られる場合は配列化して戻す
            $result = ($isDummyOn) ? array(0 => '---') : array();
            foreach ($ri as $row) {
                $result[$row['DeliMethodId']] = $row['DeliMethodName'];
            }
            return $result;
        }

        // 3. [配送方法]検索
        $sql = " SELECT DeliMethodId, DeliMethodName FROM M_DeliveryMethod WHERE ValidFlg = 1 AND ProductServiceClass = 0 ORDER BY ListNumber ";
        $ri = $this->_adapter->query($sql)->execute(null);
        $result = ($isDummyOn) ? array(0 => '---') : array();
        foreach ($ri as $row) {
            $result[$row['DeliMethodId']] = $row['DeliMethodName'];
        }
        return $result;
    }

    /**
     * 配送方法を取得する。返却値は連想配列
     * (基本全ての配送方法を取得するが表示順番が[加盟店設定 > OEM設定 > 配送マスター]となる)
     *
     * @param int $enterpriseId 加盟店ID
     * @return array 配送方法
     */
    public function getEnterpriseDeliveryMethodListAllOrderBy($enterpriseId)
    {
        $result = array(-99 => '-');

        // 1. [加盟店別配送方法マスター]検索
        $ri = $this->_adapter->query($this->_getBaseSeqEnterpriseDeliveryMethod())->execute(array(':EnterpriseId' => $enterpriseId));
        if ($ri->count() > 0) {
            foreach ($ri as $row) {
                $result[$row['DeliMethodId']] = $row['DeliMethodName'];
            }
        }

        // 2. [OEM配送先順序]検索
        $sql = " SELECT IFNULL(OemId,0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $oemId = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current()['OemId'];

        $ri = $this->_adapter->query($this->_getBaseSeqOemDeliveryMethod())->execute(array(':OemId' => $oemId));
        if ($oemId > 0 && $ri->count() > 0) {
            foreach ($ri as $row) {
                if (array_key_exists($row['DeliMethodId'], $result)) { continue; }
                $result[$row['DeliMethodId']] = $row['DeliMethodName'];
            }
        }

        // 3. [配送方法]検索
        $sql = " SELECT DeliMethodId, DeliMethodName FROM M_DeliveryMethod WHERE ValidFlg = 1 AND ProductServiceClass = 0 ORDER BY ListNumber ";
        $ri = $this->_adapter->query($sql)->execute(null);
        foreach ($ri as $row) {
            if (array_key_exists($row['DeliMethodId'], $result)) { continue; }
            $result[$row['DeliMethodId']] = $row['DeliMethodName'];
        }

        return $result;
    }

    private function _getBaseSeqEnterpriseDeliveryMethod() {
        return <<<EOQ
SELECT edm.DeliMethodId
,      dm.DeliMethodName
FROM   T_EnterpriseDelivMethod edm
       INNER JOIN M_DeliveryMethod dm ON (dm.DeliMethodId = edm.DeliMethodId)
WHERE  edm.EnterpriseId = :EnterpriseId
AND    edm.ValidFlg = 1
AND    dm.ValidFlg = 1
AND    dm.ProductServiceClass = 0
ORDER BY edm.ListNumber
EOQ;
    }

    private function _getBaseSeqOemDeliveryMethod() {
        return <<<EOQ
SELECT odml.DeliMethodId
,      dm.DeliMethodName
FROM   T_OemDeliveryMethodList odml
       INNER JOIN M_DeliveryMethod dm ON (dm.DeliMethodId = odml.DeliMethodId)
WHERE  odml.OemId = :OemId
AND    odml.ValidFlg = 1
AND    dm.ValidFlg = 1
AND    dm.ProductServiceClass = 0
ORDER BY odml.ListNumber
EOQ;
    }
}


