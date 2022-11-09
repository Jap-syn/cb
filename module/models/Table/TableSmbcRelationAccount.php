<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * SMBC決済ステーションアカウント情報をCBまたはOEMへ関連付けて管理する
 * T_SmbcRelationAccountテーブルへのアダプタ
 */
class TableSmbcRelationAccount
{
	protected $_name = 'T_SmbcRelationAccount';
	protected $_primary = array('SmbcAccountId');
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
	 * SMBC決済ステーションアカウント情報を取得する
	 *
	 * @param int $smbcAccountId 連携アカウントID
	 * @return ResultInterface
	 */
	public function find($smbcAccountId)
	{
        $sql  = " SELECT * FROM T_SmbcRelationAccount WHERE SmbcAccountId = :SmbcAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcAccountId' => $smbcAccountId,
        );

        return $stm->execute($prm);
	}

    /**
	 * 新しいレコードをインサートする。
	 *
	 * @param int $oemId 関連付けるOEM ID
	 * @param array $data インサートする連想配列
	 * @return プライマリキー値
	 */
	public function saveNew($oemId, array $data = array())
	{
        $tmpl = array(
                'ApiVersion' => '21F',                    // 決済ステーションAPIバージョン
                'BillMethod' => '20',                     // 決済ステーション決済手段区分
                'KessaiId' => '2001',                     // 決済ステーション決済種別コード
                'SeikyuuName' => '立替払い代金',          // 決済ステーション請求内容
                'SeikyuuKana' => 'タテカエバライダイキン',// 決済ステーション請求内容カナ
                'HakkouKbn' => 2,                         // 払込票発行区分
                'YuusousakiKbn' => 2,                     // 払込票郵送先区分
                'Yu_ChargeClass' => 0                     // ゆうちょ払込負担区分
        );
        $data = array_merge($tmpl, $data);

        $oemId = (int)$oemId;
        $data['OemId'] = $oemId;

        $sql  = " INSERT INTO T_SmbcRelationAccount (OemId, DisplayName, ApiVersion, BillMethod, KessaiId, ShopCd, SyunoCoCd1, SyunoCoCd2, SyunoCoCd3, SyunoCoCd4, SyunoCoCd5, SyunoCoCd6, ShopPwd1, ShopPwd2, ShopPwd3, ShopPwd4, ShopPwd5, ShopPwd6, SeikyuuName, SeikyuuKana, HakkouKbn, YuusousakiKbn, Yu_SubscriberName, Yu_AccountNumber, Yu_ChargeClass, Yu_SubscriberData, Cv_ReceiptAgentName, Cv_SubscriberName, Cv_ReceiptAgentCode, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OemId ";
        $sql .= " , :DisplayName ";
        $sql .= " , :ApiVersion ";
        $sql .= " , :BillMethod ";
        $sql .= " , :KessaiId ";
        $sql .= " , :ShopCd ";
        $sql .= " , :SyunoCoCd1 ";
        $sql .= " , :SyunoCoCd2 ";
        $sql .= " , :SyunoCoCd3 ";
        $sql .= " , :SyunoCoCd4 ";
        $sql .= " , :SyunoCoCd5 ";
        $sql .= " , :SyunoCoCd6 ";
        $sql .= " , :ShopPwd1 ";
        $sql .= " , :ShopPwd2 ";
        $sql .= " , :ShopPwd3 ";
        $sql .= " , :ShopPwd4 ";
        $sql .= " , :ShopPwd5 ";
        $sql .= " , :ShopPwd6 ";
        $sql .= " , :SeikyuuName ";
        $sql .= " , :SeikyuuKana ";
        $sql .= " , :HakkouKbn ";
        $sql .= " , :YuusousakiKbn ";
        $sql .= " , :Yu_SubscriberName ";
        $sql .= " , :Yu_AccountNumber ";
        $sql .= " , :Yu_ChargeClass ";
        $sql .= " , :Yu_SubscriberData ";
        $sql .= " , :Cv_ReceiptAgentName ";
        $sql .= " , :Cv_SubscriberName ";
        $sql .= " , :Cv_ReceiptAgentCode ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $data['OemId'],
                ':DisplayName' => $data['DisplayName'],
                ':ApiVersion' => $data['ApiVersion'],
                ':BillMethod' => $data['BillMethod'],
                ':KessaiId' => $data['KessaiId'],
                ':ShopCd' => $data['ShopCd'],
                ':SyunoCoCd1' => $data['SyunoCoCd1'],
                ':SyunoCoCd2' => $data['SyunoCoCd2'],
                ':SyunoCoCd3' => $data['SyunoCoCd3'],
                ':SyunoCoCd4' => $data['SyunoCoCd4'],
                ':SyunoCoCd5' => $data['SyunoCoCd5'],
                ':SyunoCoCd6' => $data['SyunoCoCd6'],
                ':ShopPwd1' => $data['ShopPwd1'],
                ':ShopPwd2' => $data['ShopPwd2'],
                ':ShopPwd3' => $data['ShopPwd3'],
        		':ShopPwd4' => $data['ShopPwd4'],
                ':ShopPwd5' => $data['ShopPwd5'],
                ':ShopPwd6' => $data['ShopPwd6'],
                ':SeikyuuName' => $data['SeikyuuName'],
                ':SeikyuuKana' => $data['SeikyuuKana'],
                ':HakkouKbn' => $data['HakkouKbn'],
                ':YuusousakiKbn' => $data['YuusousakiKbn'],
                ':Yu_SubscriberName' => $data['Yu_SubscriberName'],
                ':Yu_AccountNumber' => $data['Yu_AccountNumber'],
                ':Yu_ChargeClass' => $data['Yu_ChargeClass'],
                ':Yu_SubscriberData' => $data['Yu_SubscriberData'],
                ':Cv_ReceiptAgentName' => $data['Cv_ReceiptAgentName'],
                ':Cv_SubscriberName' => $data['Cv_SubscriberName'],
                ':Cv_ReceiptAgentCode' => $data['Cv_ReceiptAgentCode'],
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
	 * 指定されたレコードを更新する。
	 *
	 * @param array $data 更新内容
	 * @param int $smbcAccountId 連携アカウントID
	 */
	public function saveUpdate($data, $smbcAccountId)
	{
        $row = $this->find($smbcAccountId)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_SmbcRelationAccount ";
        $sql .= " SET ";
        $sql .= "     OemId = :OemId ";
        $sql .= " ,   DisplayName = :DisplayName ";
        $sql .= " ,   ApiVersion = :ApiVersion ";
        $sql .= " ,   BillMethod = :BillMethod ";
        $sql .= " ,   KessaiId = :KessaiId ";
        $sql .= " ,   ShopCd = :ShopCd ";
        $sql .= " ,   SyunoCoCd1 = :SyunoCoCd1 ";
        $sql .= " ,   SyunoCoCd2 = :SyunoCoCd2 ";
        $sql .= " ,   SyunoCoCd3 = :SyunoCoCd3 ";
        $sql .= " ,   SyunoCoCd4 = :SyunoCoCd4 ";
        $sql .= " ,   SyunoCoCd5 = :SyunoCoCd5 ";
        $sql .= " ,   SyunoCoCd6 = :SyunoCoCd6 ";
        $sql .= " ,   ShopPwd1 = :ShopPwd1 ";
        $sql .= " ,   ShopPwd2 = :ShopPwd2 ";
        $sql .= " ,   ShopPwd3 = :ShopPwd3 ";
        $sql .= " ,   ShopPwd4 = :ShopPwd4 ";
        $sql .= " ,   ShopPwd5 = :ShopPwd5 ";
        $sql .= " ,   ShopPwd6 = :ShopPwd6 ";
        $sql .= " ,   SeikyuuName = :SeikyuuName ";
        $sql .= " ,   SeikyuuKana = :SeikyuuKana ";
        $sql .= " ,   HakkouKbn = :HakkouKbn ";
        $sql .= " ,   YuusousakiKbn = :YuusousakiKbn ";
        $sql .= " ,   Yu_SubscriberName = :Yu_SubscriberName ";
        $sql .= " ,   Yu_AccountNumber = :Yu_AccountNumber ";
        $sql .= " ,   Yu_ChargeClass = :Yu_ChargeClass ";
        $sql .= " ,   Yu_SubscriberData = :Yu_SubscriberData ";
        $sql .= " ,   Cv_ReceiptAgentName = :Cv_ReceiptAgentName ";
        $sql .= " ,   Cv_SubscriberName = :Cv_SubscriberName ";
        $sql .= " ,   Cv_ReceiptAgentCode = :Cv_ReceiptAgentCode ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE SmbcAccountId = :SmbcAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SmbcAccountId' => $smbcAccountId,
                ':OemId' => $row['OemId'],
                ':DisplayName' => $row['DisplayName'],
                ':ApiVersion' => $row['ApiVersion'],
                ':BillMethod' => $row['BillMethod'],
                ':KessaiId' => $row['KessaiId'],
                ':ShopCd' => $row['ShopCd'],
                ':SyunoCoCd1' => $row['SyunoCoCd1'],
                ':SyunoCoCd2' => $row['SyunoCoCd2'],
                ':SyunoCoCd3' => $row['SyunoCoCd3'],
                ':SyunoCoCd4' => $row['SyunoCoCd4'],
                ':SyunoCoCd5' => $row['SyunoCoCd5'],
                ':SyunoCoCd6' => $row['SyunoCoCd6'],
                ':ShopPwd1' => $row['ShopPwd1'],
                ':ShopPwd2' => $row['ShopPwd2'],
                ':ShopPwd3' => $row['ShopPwd3'],
        		':ShopPwd4' => $row['ShopPwd4'],
                ':ShopPwd5' => $row['ShopPwd5'],
                ':ShopPwd6' => $row['ShopPwd6'],
                ':SeikyuuName' => $row['SeikyuuName'],
                ':SeikyuuKana' => $row['SeikyuuKana'],
                ':HakkouKbn' => $row['HakkouKbn'],
                ':YuusousakiKbn' => $row['YuusousakiKbn'],
                ':Yu_SubscriberName' => $row['Yu_SubscriberName'],
                ':Yu_AccountNumber' => $row['Yu_AccountNumber'],
                ':Yu_ChargeClass' => $row['Yu_ChargeClass'],
                ':Yu_SubscriberData' => $row['Yu_SubscriberData'],
                ':Cv_ReceiptAgentName' => $row['Cv_ReceiptAgentName'],
                ':Cv_SubscriberName' => $row['Cv_SubscriberName'],
                ':Cv_ReceiptAgentCode' => $row['Cv_ReceiptAgentCode'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}

    /**
     * 指定OEM IDに関連付けられているレコードを検索する
     *
     * @param int $oemId OEM ID
     * @return ResultInterface
     */
    public function findByOemId($oemId)
    {
        $sql = " SELECT * FROM T_SmbcRelationAccount WHERE OemId = :OemId ORDER BY SmbcAccountId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OemId' => $oemId,
        );

        return $stm->execute($prm);
    }

}
