<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\LogicCampaign;

/**
 * T_OemClaimFeeテーブルへのアダプタ
 */
class TableOemClaimFee
{
	protected $_name = 'T_OemClaimFee';
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
     * OEM請求手数料を登録
     *
     * @param  $orderSeq オーダー
     * @param $opId 担当者
     * @param  $bundledFeeFlag　請求手数料同梱フラグ
     * @return プライマリキーのバリュー
     *
     */
    public function saveOemClaimFee($orderSeq, $opId, $bundledFeeFlag = false)
    {
        $mdlo = new TableOrder($this->_adapter);
        $mdls = new TableSite($this->_adapter);

        //OEMID取得
        $oemId= $mdlo->getOemId($orderSeq);

        //OEMID取得できなかった場合例外
        if(is_null($oemId)){
            throw new \Exception('oemId not found !! orderSeq='.$orderSeq);
        }

        //事業者ID取得
        $enterpriseId = $mdlo->getEnterpriseId($orderSeq);

        //事業者ID取得できなかった場合例外
        if(is_null($enterpriseId)){
            throw new \Exception('enterpriseId not found !! orderSeq='.$orderSeq);
        }

        //事業者のOEM請求手数料取得
        $entMdl = new TableEnterprise($this->_adapter);
        // ↓2015.3.4 バグ修正 - 請求手数料は同梱／別送により変動する
        //$oemClaimFee = $entMdl->getOemClaimFee($enterpriseId);

        // 注文に紐づくサイトの情報を取得
        $sid = $mdlo->find($orderSeq)->current()['SiteId'];

        //請求手数料種別判別
        if(!$bundledFeeFlag){
            // 別送
            $claimFeeType = 1;
            // キャンペーン期間中はキャンペーン情報を取得する
            // 詳細情報を取得
            $logic = new LogicCampaign($this->_adapter);
            $campaign = $logic->getCampaignInfo($enterpriseId, $sid);


            // 2015/10/14 Y.Suzuki Mod 会計対応 Stt
            // マスタから取得した請求手数料は税抜金額のため、消費税額を算出後、足しこむ。
            $mdlsys = new TableSystemProperty($this->_adapter);
            $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);

            // 請求手数料
            $oemClaimFee = (int)($campaign['OemClaimFee'] + ($campaign['OemClaimFee'] * $taxRate));
            // 2015/10/14 Y.Suzuki Mod 会計対応 End
        }else{
            // 同梱
            $claimFeeType = 2;
            // キャンペーン期間中はキャンペーン情報を取得する
            // 詳細情報を取得
            $logic = new LogicCampaign($this->_adapter);
            $campaign = $logic->getCampaignInfo($enterpriseId, $sid);

            // 2015/10/16 Y.Suzuki Mod 会計対応 Stt
            $mdlsys = new TableSystemProperty($this->_adapter);
            $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);

            // OEM同梱請求手数料
            $oemClaimFee = (int)($campaign['SelfBillingOemClaimFee'] + ($campaign['SelfBillingOemClaimFee'] * $taxRate));
            // 2015/10/16 Y.Suzuki Mod 会計対応 End
        }

        //事業者ID取得できなかった場合例外
        if(is_null($oemClaimFee)){
            throw new \Exception("OemClaimFee is null !! orderSeq=".$orderSeq);
        }

        //登録データ作成
        $data['OrderSeq'] = $orderSeq;
        $data['OemId'] = $oemId;
        $data['OccDate'] = date('Y-m-d');
        $data['ClaimFeeType'] = $claimFeeType;
        $data['ClaimFee'] = $oemClaimFee;
        $data['AddUpFlg'] = 0;
        $data['AddUpFixedMonth'] = null;
        $data['OemClaimedSeq'] = null;
        $data['CancelFlg'] = 0;
        $data['RegistId'] = $opId;
        $data['UpdateId'] = $opId;

        return $this->saveNew($data);
    }

    /**
     * OEM請求手数料を登録(OEM口振紙初回登録手数料（税抜）,OEM口振WEB初回登録手数料（税抜）,OEM口振引落手数料：（税抜）追加)
     *
     * @param  $orderSeq オーダー
     * @param $opId 担当者
     * @param  $bundledFeeFlag　請求手数料同梱フラグ
     * @return プライマリキーのバリュー
     *
     */
    public function saveOemClaimFee2($orderSeq, $opId, $creditTransferMethod,$subDatas,$mDataClaimFee)
    {
        $mdlo = new TableOrder($this->_adapter);
        $mdls = new TableSite($this->_adapter);

        //OEMID取得
        $oemId= $mdlo->getOemId($orderSeq);

        //OEMID取得できなかった場合例外
        if(is_null($oemId)){
            throw new \Exception('oemId not found !! orderSeq='.$orderSeq);
        }

        //事業者ID取得
        $enterpriseId = $mdlo->getEnterpriseId($orderSeq);

        //事業者ID取得できなかった場合例外
        if(is_null($enterpriseId)){
            throw new \Exception('enterpriseId not found !! orderSeq='.$orderSeq);
        }

        //事業者のOEM請求手数料取得
        $entMdl = new TableEnterprise($this->_adapter);
        // ↓2015.3.4 バグ修正 - 請求手数料は同梱／別送により変動する
        //$oemClaimFee = $entMdl->getOemClaimFee($enterpriseId);

        // 注文に紐づくサイトの情報を取得
        $sid = $mdlo->find($orderSeq)->current()['SiteId'];

            // 別送
            $claimFeeType = 1;
            // キャンペーン期間中はキャンペーン情報を取得する
            // 詳細情報を取得
            $logic = new LogicCampaign($this->_adapter);
            $campaign = $logic->getCampaignInfo($enterpriseId, $sid);


            // 2015/10/14 Y.Suzuki Mod 会計対応 Stt
            // マスタから取得した請求手数料は税抜金額のため、消費税額を算出後、足しこむ。
            $mdlsys = new TableSystemProperty($this->_adapter);
            $taxRate = ($mdlsys->getTaxRateAt(date('Y-m-d')) / 100);

            if ($creditTransferMethod == 2) {
                    $claimFee = (int)$subDatas['OemFirstCreditTransferClaimFeeWeb'];
            } elseif ($creditTransferMethod == 1) {
                    $claimFee = (int)$subDatas['OemFirstCreditTransferClaimFee'];
            } elseif ($creditTransferMethod == 3) {
                    $claimFee = (int)$subDatas['OemCreditTransferClaimFee'];
            } else {
                    $claimFee = $mDataClaimFee;
                }

            // 請求手数料
            //$oemClaimFee = (int)($campaign['OemClaimFee'] + ($campaign['OemClaimFee'] * $taxRate) + $claimFee + ($claimFee * $taxRate));
            $oemClaimFee = (int)($claimFee + ($claimFee * $taxRate));
            // 2015/10/14 Y.Suzuki Mod 会計対応 End

        //事業者ID取得できなかった場合例外
        if(is_null($oemClaimFee)){
            throw new \Exception("OemClaimFee is null !! orderSeq=".$orderSeq);
        }

        //登録データ作成
        $data['OrderSeq'] = $orderSeq;
        $data['OemId'] = $oemId;
        $data['OccDate'] = date('Y-m-d');
        $data['ClaimFeeType'] = $claimFeeType;
        $data['ClaimFee'] = $oemClaimFee;
        $data['AddUpFlg'] = 0;
        $data['AddUpFixedMonth'] = null;
        $data['OemClaimedSeq'] = null;
        $data['CancelFlg'] = 0;
        $data['RegistId'] = $opId;
        $data['UpdateId'] = $opId;

        $sql  = " UPDATE T_OemClaimFee ";
        $sql .= " SET ";
        $sql .= "    ClaimFee = :ClaimFee ";
        $sql .= " WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':ClaimFee' => $data['ClaimFee'],
        );

        return $stm->execute($prm);
    }

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
     */
    public function saveNew($data)
    {
        $sql  = " INSERT INTO T_OemClaimFee (OrderSeq, OemId, OccDate, ClaimFeeType, ClaimFee, AddUpFlg, AddUpFixedMonth, OemClaimedSeq, CancelFlg, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :OemId ";
        $sql .= " , :OccDate ";
        $sql .= " , :ClaimFeeType ";
        $sql .= " , :ClaimFee ";
        $sql .= " , :AddUpFlg ";
        $sql .= " , :AddUpFixedMonth ";
        $sql .= " , :OemClaimedSeq ";
        $sql .= " , :CancelFlg ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':OemId' => $data['OemId'],
                ':OccDate' => $data['OccDate'],
                ':ClaimFeeType' => $data['ClaimFeeType'],
                ':ClaimFee' => $data['ClaimFee'],
                ':AddUpFlg' => $data['AddUpFlg'],
                ':AddUpFixedMonth' => $data['AddUpFixedMonth'],
                ':OemClaimedSeq' => $data['OemClaimedSeq'],
                ':CancelFlg' => $data['CancelFlg'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
    }

	/**
	 * 指定OEMIDのOEM請求手数料データを取得する。
	 *
	 * @param string $OemId OEMID
	 * @return ResultInterface
	 */
	public function findOem($OemId)
	{
        $sql = " SELECT * FROM T_OemClaimFee WHERE OemId = :OemId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $OemId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定OEMIDのOEM請求手数料データを取得する。
	 *
	 * @param string $oemId OEMID
	 * @return ResultInterface
	 */
	public function findOem2($oemId)
	{
        return $this->findOem($oemId);
	}

	protected function isPrimaryKey($colName) {
		$primaries = $this->_primary;
		if(is_array($primaries)) {
			return in_array($colName, $primaries);
		} else {
			return $colName == $primaries;
		}
	}

	/**
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $eid 更新するOemId
	 */
	public function saveUpdate($data, $eid)
	{
        $sql = " SELECT * FROM T_OemClaimFee WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $eid,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OemClaimFee ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   OccDate = :OccDate ";
        $sql .= " ,   ClaimFeeType = :ClaimFeeType ";
        $sql .= " ,   ClaimFee = :ClaimFee ";
        $sql .= " ,   AddUpFlg = :AddUpFlg ";
        $sql .= " ,   AddUpFixedMonth = :AddUpFixedMonth ";
        $sql .= " ,   OemClaimedSeq = :OemClaimedSeq ";
        $sql .= " ,   CancelFlg = :CancelFlg ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $eid,
                ':OrderSeq' => $row['OrderSeq'],
                ':OemId' => $row['OemId'],
                ':OccDate' => $row['OccDate'],
                ':ClaimFeeType' => $row['ClaimFeeType'],
                ':ClaimFee' => $row['ClaimFee'],
                ':AddUpFlg' => $row['AddUpFlg'],
                ':AddUpFixedMonth' => $row['AddUpFixedMonth'],
                ':OemClaimedSeq' => $row['OemClaimedSeq'],
                ':CancelFlg' => $row['CancelFlg'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

    /**
     * OEM請求手数料データをキャンセル中にする。
     *
     * @param int $oseq 注文SEQ
	 * @param $opId 担当者
     */
    public function toCanceling($oseq, $opId)
    {
        $this->setCancelFlg($oseq, 1, $opId);
    }

    /**
     * OEM請求手数料データをキャンセルにする。
     *
     * @param int $oseq 注文SEQ
	 * @param $opId 担当者
     */
    public function toCanceled($oseq, $opId)
    {
        $this->setCancelFlg($oseq, 2, $opId);
    }

    /**
     * OEM請求手数料データのキャンセルフラッグを設定する。
     *
     * @param int $oseq 注文SEQ
     * @param int $flg キャンセルフラッグ
	 * @param $opId 担当者
     */
    public function setCancelFlg($oseq, $flg, $opId)
    {
        $sql = " UPDATE T_OemClaimFee SET CancelFlg = :CancelFlg, UpdateDate = :UpdateDate, UpdateId = :UpdateId WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CancelFlg' => $flg,
                ':OrderSeq' => $oseq,
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $opId,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定された条件でレコードを更新する。
     *
     * @param array $data 更新内容
     * @param array $conditionArray
     */
    public function saveUpdateWhere($data, $conditionArray)
    {
        $prm = array();
        $sql  = " SELECT * FROM T_OemClaimFee WHERE 1 = 1 ";
        foreach ($conditionArray as $key => $value) {
            $sql .= (" AND " . $key . " = :" . $key);
            $prm += array(':' . $key => $value);
        }

        $stm = $this->_adapter->query($sql);

        $ri = $stm->execute($prm);

        foreach ($ri AS $row) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = $value;
                }
            }

            // 指定されたレコードを更新する
            $this->saveUpdate($row, $row['Seq']);
        }
    }
}
