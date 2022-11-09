<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableOrder;
use models\Table\TableCustomer;
use models\Table\TableDeliveryDestination;
use models\Table\TableOrderItems;
use models\Table\TableOrderSummary;
use models\Table\TableEnterprise;
use models\Table\TableManagementCustomer;
use models\Table\TableEnterpriseCustomer;
use models\Logic\classes\LogicclassesOrderInputInfo;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Coral\History\CoralHistoryOrder;
use Coral\Base\BaseGeneralUtils;
use models\Table\TablePostalCode;
use models\Table\TableSite;
use models\Table\TableOrderHistory;
use models\Table\ATableOrder;

/**
 * 注文登録クラス
 */
class LogicOrderRegister
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
	 * 注文データ一式を格納した連想配列を元にデータをDBへ保存する.<br>
	 *
	 * @param $data 注文データ
	 * @return int 注文シーケンス番号
	 */
	public function register($data, $datastatus = 11)
	{
		try
		{
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            $orderTable = new TableOrder( $this->_adapter );
            $custoTable = new TableCustomer( $this->_adapter );
            $delivTable = new TableDeliveryDestination( $this->_adapter );
            $itemsTable = new TableOrderItems( $this->_adapter );
            $summaries = new TableOrderSummary( $this->_adapter );
            $mdltmc = new TableManagementCustomer( $this->_adapter );
            $mdltec = new TableEnterpriseCustomer( $this->_adapter );

            // 購入者情報と配送先情報の郵便番号を整形 (2008.02.08 追加 eda)
            $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode'] =
                CoralValidateUtility::fixPostalCode( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode'] );
            $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['PostalCode'] =
                CoralValidateUtility::fixPostalCode( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['PostalCode'] );

            // 購入者情報と配送先情報の電話番号およびメールアドレスを半角に統一 (2008.02.20 追加 eda)
            $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['Phone'] =
                BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['Phone'] );
            $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['MailAddress'] =
                BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['MailAddress'] );
            $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['Phone'] =
                BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['Phone'] );

            // 購入者の住所カナ設定
            $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['AddressKn'] = $this->getAddressKn(
                                                $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode']);

            // 購入者情報と配送先情報の住所中の数字をすべて全角に統一 (2008.02.20 追加 eda)
            foreach( array( 'PrefectureName', 'City', 'Town', 'Building', 'UnitingAddress' ) as $key ) {
                $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO][$key] =
                    BaseGeneralUtils::convertNumberWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO][$key] );
                $data[LogicclassesOrderInputInfo::ARRKEY_DELIV][$key] =
                    BaseGeneralUtils::convertNumberWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV][$key] );
            }

            // 注文データ生成(注文シーケンス確定)
            // 別配送先チェックありも、郵便番号･住所･氏名･電話番号が[ご注文者情報]と同じ時は、[AnotherDeliFlg]を[0]にする
            if (($data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['AnotherDeliFlg'] == 1)
                &&
                (($data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode'] == $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['PostalCode']) &&
                 ($data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['UnitingAddress'] == $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['UnitingAddress']) &&
                 ($data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['NameKj'] == $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['DestNameKj']) &&
                 ($data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['NameKn'] == $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['DestNameKn']) &&
                 ($data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['Phone'] == $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['Phone']))) {

                 $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['AnotherDeliFlg'] = 0;
            }

            // INSERT(T_Order)
            $orderSeq = $orderTable->newRow(
                $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['ReceiptOrderDate'],
                $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['EnterpriseId'],
                $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['SiteId'],
                array_merge($data[LogicclassesOrderInputInfo::ARRKEY_ORDER],
                    array(
                        'ServiceExpectedDate' => (($data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['ServiceExpectedDate'] == '') ? null : $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['ServiceExpectedDate']),
                    )
                ),
                $datastatus
            );

            // OEM IDのアサイン
            $oemId = $this->getOemIdByEntId($data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['EnterpriseId']);
            $sql = " UPDATE T_Order SET OemId = :OemId WHERE OrderSeq = :OrderSeq ";
            $this->_adapter->query($sql)->execute(array(':OemId' => $oemId, ':OrderSeq' => $orderSeq));

            // 注文者データ作成
            $customerId = $custoTable->newRow(
                $orderSeq,
                $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]
            );

            $totalAmount = 0;

            // 加盟店の[利用額端数計算設定]取得
            $row_ent = $this->_adapter->query(" SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
                )->execute(array(':EnterpriseId' => $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['EnterpriseId']))->current();
            $useAmountFractionClass = (int)$row_ent['UseAmountFractionClass'];

            // 商品数分だけ商品と配送先データを生成
            foreach( $data[LogicclassesOrderInputInfo::ARRKEY_ITEMS] as $item ) {
                // 先に配送先確定
                $delivId = ( $item['DataClass'] == 1 ) ? $delivTable->newRow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV] ) : -1;

                $sumMoney = ($item['UnitPrice'] * (double)$item['ItemNum']);

                //消費税設定
                if($item['TaxRate'] != NULL ){
                    $item['TaxrateNotsetFlg'] = 0;
                }else{
                    $item['TaxRate'] = NULL;
                    $item['TaxrateNotsetFlg'] = 1;
                }

                if ($useAmountFractionClass == 0) { $sumMoney = floor( $sumMoney ); }
                if ($useAmountFractionClass == 1) { $sumMoney = round( $sumMoney ); }
                if ($useAmountFractionClass == 2) { $sumMoney = ceil(  $sumMoney ); }

                $orderItemId = $itemsTable->newRow( $orderSeq, $delivId, $item['DataClass'], array_merge($item, array('SumMoney' => $sumMoney)) );
                $sql = " SELECT SumMoney FROM T_OrderItems WHERE OrderItemId = :OrderItemId ";
                $totalAmount += (int)($this->_adapter->query($sql)->execute(array(':OrderItemId' => $orderItemId))->current()['SumMoney']);
            }

            // 合計金額を注文データに設定して再度保存
            $sql = " UPDATE T_Order SET UseAmount = :UseAmount WHERE OrderSeq = :OrderSeq ";
            $this->_adapter->query($sql)->execute(array(':UseAmount' => $totalAmount, ':OrderSeq' => $orderSeq));

            // 今作成した注文データのサマリーを作成する
            $summaries->updateSummary( $orderSeq, $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['UpdateId'] );

            // 登録者
            $userId = $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['RegistId'];

            // 管理顧客、加盟店顧客の設定
            $this->updateCustomer($orderSeq, $userId);

            // 返却用
            $sql = " SELECT OrderId FROM T_Order WHERE OrderSeq = :OrderSeq ";
            $orderId = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['OrderId'];

            // 注文履歴の登録
            $history = new CoralHistoryOrder($this->_adapter);
            $userId = $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['RegistId'];
            $history->InsOrderHistory($orderSeq, 13, $userId);

            $this->_adapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        return $orderId;
	}

	public function getOrderSeqByOrderId($orderId) {
        $orderId = trim((string)$orderId);
        if( empty($orderId) ) return null;

        $orderTable = new TableOrder($this->_adapter);
        $sql = " SELECT OrderSeq FROM T_Order WHERE OrderId = :OrderId ";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderId' => $orderId));
        if (!($ri->count() > 0)) {
            return null;
        }
        return $ri->current()['OrderSeq'];
	}

	public function getOemIdByEntId($entId) {
        $entTable = new TableEnterprise($this->_adapter);
        $ri = $entTable->findEnterprise($entId);
        if (!($ri->count() > 0)) {
            return null;
        }
        return $ri->current()['OemId'];
	}

	/**
	 * 注文データ一式を格納した連想配列を元にデータをDBへ保存する.<br>
	 *
	 * @param $data 注文データ
	 * @return int 注文シーケンス番号
	 */
	public function modify($data, $datastatus = 12)
	{
	    try
	    {
	        $this->_adapter->getDriver()->getConnection()->beginTransaction();

	        $orderTable = new TableOrder( $this->_adapter );
	        $custoTable = new TableCustomer( $this->_adapter );
	        $delivTable = new TableDeliveryDestination( $this->_adapter );
	        $itemsTable = new TableOrderItems( $this->_adapter );
	        $summaries = new TableOrderSummary( $this->_adapter );
	        $enterprise = new TableEnterprise( $this-> _adapter);

	        // 購入者情報と配送先情報の郵便番号を整形
	        $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode'] =
	        CoralValidateUtility::fixPostalCode( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode'] );
	        $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['PostalCode'] =
	        CoralValidateUtility::fixPostalCode( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['PostalCode'] );

	        // 購入者情報と配送先情報の電話番号およびメールアドレスを半角に統一
	        $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['Phone'] =
	        BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['Phone'] );
	        $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['MailAddress'] =
	        BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['MailAddress'] );
	        $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['Phone'] =
	        BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['Phone'] );

	        // 購入者情報と配送先情報の住所中の数字をすべて全角に統一
	        foreach( array( 'PrefectureName', 'City', 'Town', 'Building', 'UnitingAddress' ) as $key ) {
	            $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO][$key] =
	            BaseGeneralUtils::convertNumberWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO][$key] );
	            $data[LogicclassesOrderInputInfo::ARRKEY_DELIV][$key] =
	            BaseGeneralUtils::convertNumberWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV][$key] );
	        }

	        // 再与信を行う場合、与信用項目を初期化する
	        if ($datastatus == 12) {
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['DataStatus'] = 12;
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['Chg_Status'] = 0;
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['Rct_Status'] = 0;
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['Rct_MailFlg'] = 0;
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['Cnl_CantCancelFlg'] = 0;
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['Cnl_Status'] = 0;
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['Dmg_DecisionFlg'] = 0;
	            $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['Deli_ConfirmArrivalFlg'] = 0;
	        }

	        // 注文データを保存
	        $condition = array(
	                'EnterpriseId' => $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['EnterpriseId'],
	                'OrderId' => $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['OrderId']
	        );

            $orderTable->saveUpdateWhere(
                array_merge($data[LogicclassesOrderInputInfo::ARRKEY_ORDER],
                    array(
                        'ServiceExpectedDate' => (($data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['ServiceExpectedDate'] == '') ? null : $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['ServiceExpectedDate']),
                    )
                )
                , $condition);

            $sql = ' SELECT OrderSeq FROM T_Order WHERE OrderId = :OrderId ';
            $orderSeq = $this->_adapter->query($sql)->execute(array(
                ':OrderId' => $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['OrderId']
            ))->current()['OrderSeq'];

	        // OEM IDのアサイン
	        $oemId = $this->getOemIdByEntId($data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['EnterpriseId']);
	        $sql = " UPDATE T_Order SET OemId = :OemId WHERE OrderSeq = :OrderSeq ";
	        $this->_adapter->query($sql)->execute(array(':OemId' => $oemId, ':OrderSeq' => $orderSeq));

	        // 郵便番号より、住所カナを取得し、注文先配列に設定
	        $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['AddressKn'] = $this->getAddressKn($data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode']);
	        // 注文者データ作成
	        $custoTable->saveUpdateWhere($data[LogicclassesOrderInputInfo::ARRKEY_CUSTO], array(
                'OrderSeq' => $orderSeq
	        ));

	        // 配送先を削除
	        $sql = 'SELECT DeliDestId FROM T_OrderItems WHERE DeliDestId <> -1 AND OrderSeq =:OrderSeq';
	        $ary_delidestid = array();
	        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));
	        foreach ($ri as $row) {
	            $ary_delidestid[] = $row['DeliDestId'];
	        }
	        $sql = 'DELETE FROM T_DeliveryDestination WHERE DeliDestId IN (' . implode(',', $ary_delidestid) . ')';
	        $this->_adapter->query($sql)->execute(null);

	        // 注文商品を削除
	        $sql = 'DELETE FROM T_OrderItems WHERE OrderSeq = :OrderSeq';
	        $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq));

	        $totalAmount = 0;

	        // 加盟店の[利用額端数計算設定]取得
	        $row_ent = $this->_adapter->query(" SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId "
    	        )->execute(array(':EnterpriseId' => $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['EnterpriseId']))->current();
	        $useAmountFractionClass = (int)$row_ent['UseAmountFractionClass'];

	        // 商品数分だけ商品と配送先データを生成
	        foreach( $data[LogicclassesOrderInputInfo::ARRKEY_ITEMS] as $item ) {
	            // 先に配送先確定
	            $delivId = ( $item['DataClass'] == 1 ) ? $delivTable->newRow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV] ) : -1;

	            $sumMoney = ($item['UnitPrice'] * (double)$item['ItemNum']);
	            if ($useAmountFractionClass == 0) { $sumMoney = floor( $sumMoney ); }
	            if ($useAmountFractionClass == 1) { $sumMoney = round( $sumMoney ); }
	            if ($useAmountFractionClass == 2) { $sumMoney = ceil(  $sumMoney ); }

	            //消費税設定
	            if($item['TaxRate'] != NULL ){
	                $item['TaxrateNotsetFlg'] = 0;
	            }else{
	                $item['TaxRate'] = NULL;
	                $item['TaxrateNotsetFlg'] = 1;
	            }

	            $orderItemId = $itemsTable->newRow( $orderSeq, $delivId, $item['DataClass'], array_merge($item, array('SumMoney' => $sumMoney)) );
	            $sql = " SELECT SumMoney FROM T_OrderItems WHERE OrderItemId = :OrderItemId ";
	            $totalAmount += (int)($this->_adapter->query($sql)->execute(array(':OrderItemId' => $orderItemId))->current()['SumMoney']);
	        }

	        // 合計金額を注文データに設定して再度保存
	        $sql = " UPDATE T_Order SET UseAmount = :UseAmount WHERE OrderSeq = :OrderSeq ";
	        $this->_adapter->query($sql)->execute(array(':UseAmount' => $totalAmount, ':OrderSeq' => $orderSeq));

	        // 今作成した注文データのサマリーを作成する
	        $summaries->updateSummary( $orderSeq , $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['UpdateId']);

	        // 加盟店顧客、管理顧客の紐付け
	        $userId = $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['UpdateId'];
	        $this->updateCustomer( $orderSeq , $userId );

	        // 返却用
	        $sql = " SELECT OrderId FROM T_Order WHERE OrderSeq = :OrderSeq ";
	        $orderId = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['OrderId'];

	        // 注文_会計テーブルの更新
	        $mdlao = new ATableOrder($this->_adapter);
	        $creditTransferRequestFlg = isset($data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['CreditTransferRequestFlg']) ? $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['CreditTransferRequestFlg'] : 0;
	        $udata = array(
	                'DefectFlg' => 0,
	                'DefectNote' => null,
	                'DefectCancelPlanDate' => null,
	                'CreditTransferRequestFlg' => $creditTransferRequestFlg,
	        );
	        $mdlao->saveUpdate($udata, $orderSeq);

	        // 注文履歴の登録
	        $reasonCode = 0;
	        if ($datastatus == 12) {
	            $reasonCode = 14;
	        }
	        else {
	            $reasonCode = 15;
	        }
	        $history = new CoralHistoryOrder($this->_adapter);
	        $history->InsOrderHistory($orderSeq, $reasonCode, $userId);

	        $this->_adapter->getDriver()->getConnection()->commit();
	    }
	    catch(\Exception $e)
	    {
	        $this->_adapter->getDriver()->getConnection()->rollBack();
	        throw $e;
	    }

	    return $orderId;
	}

	/**
	 * 郵便番号より、住所カナを取得する
	 * @param string $postalCode
	 * @return string
	 */
	private function getAddressKn($postalCode) {
	    $mdlpc = new TablePostalCode($this->_adapter);
	    $postData = $mdlpc->findPostalCode7($postalCode);
	    $postDataCount = 0;
	    if(!empty($postData)) {
	        $postDataCount = count($postData);
	    }
	    if ($postDataCount > 1) {
	        return mb_convert_kana($postData[0]->PrefectureKana . $postData[0]->CityKana, 'K');
	    }
	    return mb_convert_kana($postData[0]->PrefectureKana . $postData[0]->CityKana . $postData[0]->TownKana, 'K');
	}

    /**
     *
     */
    public function judgeAuthExec($data) {
        // 現在の注文データ
        $sql = <<<EOQ
SELECT O.OrderSeq AS OrderSeq
,      O.DataStatus AS DataStatus
,      O.OrderId AS OrderId
,      O.UseAmount AS UseAmount
,      C.NameKj AS NameKj
,      C.NameKn AS NameKn
,      C.PostalCode AS PostalCode
,      C.UnitingAddress AS UnitingAddress
,      C.Phone AS Phone
,      C.MailAddress AS MailAddress
,      S.DestNameKj AS DestNameKj
,      S.DestNameKn AS DestNameKn
,      S.DestPostalCode AS DestPostalCode
,      S.DestUnitingAddress AS DestUnitingAddress
,      S.DestPhone AS DestPhone
FROM   T_Order O
       STRAIGHT_JOIN T_Customer C ON (C.OrderSeq = O.OrderSeq)
       STRAIGHT_JOIN T_OrderSummary S ON (S.OrderSeq = O.OrderSeq)
WHERE  O.OrderId = :OrderId
EOQ;

        $order = $this->_adapter->query($sql)->execute(array(
                ':OrderId' => $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['OrderId']
        ))->current();

        // APIデータ
        // 購入者情報と配送先情報の郵便番号を整形
        $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode'] =
        CoralValidateUtility::fixPostalCode( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['PostalCode'] );
        $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['PostalCode'] =
        CoralValidateUtility::fixPostalCode( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['PostalCode'] );

        // 購入者情報と配送先情報の電話番号およびメールアドレスを半角に統一
        $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['Phone'] =
        BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['Phone'] );
        $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['MailAddress'] =
        BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO]['MailAddress'] );
        $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['Phone'] =
        BaseGeneralUtils::convertWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV]['Phone'] );

        // 購入者情報と配送先情報の住所中の数字をすべて全角に統一
        foreach( array( 'PrefectureName', 'City', 'Town', 'Building', 'UnitingAddress' ) as $key ) {
            $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO][$key] =
            BaseGeneralUtils::convertNumberWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO][$key] );
            $data[LogicclassesOrderInputInfo::ARRKEY_DELIV][$key] =
            BaseGeneralUtils::convertNumberWideToNarrow( $data[LogicclassesOrderInputInfo::ARRKEY_DELIV][$key] );
        }

        $input =array();

        // 購入者情報
        $input['Customer'] = $data[LogicclassesOrderInputInfo::ARRKEY_CUSTO];

        // 配送先情報
        $input['Destination'] = $data[LogicclassesOrderInputInfo::ARRKEY_DELIV];

        // サイト情報取得
        $siteTable = new TableSite($this->_adapter);
        $siteId = $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['SiteId'];
        $site = $siteTable->findSite($siteId)->current();
        $mail_required = $site ? $site['ReqMailAddrFlg'] : false;

        // 再与信となるかどうか、変更をチェック
        $has_changed = false;
        $change_part = array();

        //正規化する際のfilter
        $map = array(
                'NameKj' => LogicNormalizer::FILTER_FOR_NAME,
                'UnitingAddress' => LogicNormalizer::FILTER_FOR_ADDRESS,
                'Phone' => LogicNormalizer::FILTER_FOR_TEL,
                'MailAddress' => LogicNormalizer::FILTER_FOR_MAIL,
                'DestNameKj' => LogicNormalizer::FILTER_FOR_NAME,
                'DestUnitingAddress' => LogicNormalizer::FILTER_FOR_ADDRESS,
                'DestPhone' => LogicNormalizer::FILTER_FOR_TEL,
        );
        // 購入者情報チェック
        // 特定の項目に変更があった場合に再与信
        foreach(array('NameKj', 'PostalCode', 'UnitingAddress', 'Phone', 'MailAddress') as $key) {
            if($key == 'MailAddress' && !$mail_required) continue;
            //正規化
            if(in_array($key,array_keys($map))){
                $regOrderVal = LogicNormalizer::create($map[$key])->normalize($order[$key]);
                $regInputVal = LogicNormalizer::create($map[$key])->normalize($input['Customer'][$key]);
            } else {
                $regOrderVal = $order[$key];
                $regInputVal = $input['Customer'][$key];
            }
            //正規化後の値を比較
            if($regOrderVal != $regInputVal) {
                $has_changed = true;
                break;
            }
        }

        // 配送先情報チェック
        // 特定の項目に変更があった場合に再与信
        foreach(array('DestNameKj', 'DestPostalCode', 'DestUnitingAddress', 'DestPhone') as $key) {
            $sub_key = (!in_array($key, array('DestNameKj', 'DestNameKn'))) ? preg_replace('/^Dest/', '', $key) : $key;
            //正規化
            if(in_array($key,array_keys($map))){
                $regOrderVal = LogicNormalizer::create($map[$key])->normalize($order[$key]);
                $regInputVal = LogicNormalizer::create($map[$key])->normalize($input['Destination'][$sub_key]);
            } else {
                $regOrderVal = $order[$key];
                $regInputVal = $input['Destination'][$sub_key];
            }
            //正規化後の値を比較
            if($regOrderVal != $regInputVal) {
                $has_changed = true;
                break;
            }
        }

        // 金額チェック
        // 合計金額が3,000以上増加していたら再与信
         $sum = $data[LogicclassesOrderInputInfo::ARRKEY_ORDER]['UseAmount'];
         $ohis = new TableOrderHistory($this->_adapter);
         $lastCreditJudgeAmount = $ohis->getLastCreditJudgeAmount($order['OrderSeq']);
         if (is_null($lastCreditJudgeAmount)) {
             // 過去に与信金額がない場合は現在の金額で判定
             $lastCreditJudgeAmount = $order['UseAmount'];
         }
         if($sum - $lastCreditJudgeAmount > 2999) {
             $has_changed = true;
         }

        return $has_changed;
    }


    /**
     * 購入者の紐付けを行います
     * @param unknown $oseq
     * @param unknown $userId
     */
    public function updateCustomer($oseq, $userId) {

        $mdlmc = new TableManagementCustomer( $this->_adapter );
        $mdlec = new TableEnterpriseCustomer( $this->_adapter );
        $mdlo = new TableOrder( $this->_adapter );
        $mdle = new TableEnterprise($this->_adapter);

        $enterpriseId = $mdlo->getEnterpriseId($oseq);
        $isEcNew = false;

        // 顧客の情報を取得
        $sql = " SELECT * FROM T_Customer WHERE OrderSeq = " . $oseq;
        $custRow = $this->_adapter->query($sql)->execute(null)->current();

        $enterprise = $mdle->findEnterprise($enterpriseId)->current();

        if (!empty($custRow['EntCustId']) && ($enterprise['CreditTransferFlg'] != 0)) {
            $entCustSeq = $mdlec->getMaxManEntCustSeq2($oseq, $enterpriseId, $custRow['EntCustId'], $custRow['RegNameKj']);
            $entCustSeq2 = $mdlec->getMaxManEntCustSeq3($oseq, $enterpriseId, $custRow['EntCustId']);
            if (($entCustSeq == -1) || ($entCustSeq2 == -1)) {
                // 加盟店顧客番号と顧客名で一致データなし
                $entCustSeq = $entCustSeq2;
                if ($entCustSeq == -1) {
                    // 加盟店顧客番号で一致データなし
                    $entCustSeq = $this->createEnterpriseCustomer($oseq, $enterpriseId, $userId);
                    $this->updateEnterpriseCustomer($oseq, $entCustSeq, $userId);
                } else {
                    // 加盟店顧客番号で一致データあり
                    $work = $entCustSeq;
                    $entCustSeq = $this->createEnterpriseCustomer($oseq, $enterpriseId, $userId);
                    $this->updateEnterpriseCustomer($oseq, $entCustSeq, $userId);
//                    $enterprise = $mdle->findEnterprise($enterpriseId)->current();
                    if ($enterprise['CreditTransferFlg'] != 0) {
                        $this->createCreditTransferAlert($oseq, $enterpriseId, $work, $userId);
                    }
                }
            } else {
                // 加盟店顧客番号と顧客名で一致データあり
                $sql = " SELECT * FROM T_Customer WHERE OrderSeq = " . $oseq;
                $custRow = $this->_adapter->query($sql)->execute(null)->current();
                $data = array(
                    'PostalCode' => $custRow['PostalCode'],
                    'PrefectureCode' => $custRow['PrefectureCode'],
                    'PrefectureName' => $custRow['PrefectureName'],
                    'City' => $custRow['City'],
                    'Town' => $custRow['Town'],
                    'Building' => $custRow['Building'],
                    'UnitingAddress' => $custRow['UnitingAddress'],
                    'Phone' => $custRow['Phone'],
                    'MailAddress' => $custRow['MailAddress'],
                    'RegUnitingAddress' => $custRow['RegUnitingAddress'],
                    'RegPhone' => $custRow['RegPhone'],
                    'SearchPhone' => $custRow['SearchPhone'],
                    'SearchUnitingAddress' => $custRow['SearchUnitingAddress'],
                    'UpdateId' => $userId,
                );
                $mdlec->saveUpdate($data, $entCustSeq);
            }
        } else {
            // T_EnterpriseCustomer登録
            // (加盟店顧客の存在チェック)
            $sql = " SELECT * FROM T_Customer WHERE OrderSeq = " . $oseq;
            $custRow = $this->_adapter->query($sql)->execute(null)->current();
            $entCustSeq = $mdlec->getMaxManEntCustSeq($oseq, $enterpriseId, $custRow['RegNameKj'], $custRow['RegPhone'], $custRow['RegUnitingAddress']);
            // (存在しない時は新規登録)
            if ($entCustSeq == -1) {
                $entCustSeq = $this->createEnterpriseCustomer($oseq, $enterpriseId, $userId);
                $isEcNew = true;
            }

            // T_ManagementCustomer登録
            if ( $isEcNew ) {
                // 加盟店顧客を新規作成しているのであれば、管理顧客の紐付けを行う
                $this->updateEnterpriseCustomer($oseq, $entCustSeq, $userId);
            }
        }

        // T_Customer更新
        $sql = " UPDATE T_Customer SET EntCustSeq = :EntCustSeq WHERE OrderSeq = :OrderSeq ";
        $this->_adapter->query($sql)->execute(array(':EntCustSeq' => $entCustSeq, ':OrderSeq' => $oseq));

    }

    private function createEnterpriseCustomer($oseq, $enterpriseId, $userId)
    {
        $mdlec = new TableEnterpriseCustomer( $this->_adapter );

        // (T_Customerの情報を取得)
        $sql = " SELECT * FROM T_Customer WHERE OrderSeq = " . $oseq;
        $custRow = $this->_adapter->query($sql)->execute(null)->current();
        // (INSERT)
        return $mdlec->saveNew(
            array(
                'EnterpriseId' => $enterpriseId,
                'ManCustId' => null,        // 後続処理でセットする
                'NameKj' => $custRow['NameKj'],
                'NameKn' => $custRow['NameKn'],
                'PostalCode' => $custRow['PostalCode'],
                'PrefectureCode' => $custRow['PrefectureCode'],
                'PrefectureName' => $custRow['PrefectureName'],
                'City' => $custRow['City'],
                'Town' => $custRow['Town'],
                'Building' => $custRow['Building'],
                'UnitingAddress' => $custRow['UnitingAddress'],
                'Phone' => $custRow['Phone'],
                'MailAddress' => $custRow['MailAddress'],
                'RegNameKj' => $custRow['RegNameKj'],
                'RegUnitingAddress' => $custRow['RegUnitingAddress'],
                'RegPhone' => $custRow['RegPhone'],
                'SearchNameKj' => $custRow['SearchNameKj'],
                'SearchNameKn' => $custRow['SearchNameKn'],
                'SearchPhone' => $custRow['SearchPhone'],
                'SearchUnitingAddress' => $custRow['SearchUnitingAddress'],
                'RegistId' => $userId,
                'UpdateId' => $userId,
            )
        );
    }

    private function updateEnterpriseCustomer($oseq, $entCustSeq, $userId)
    {
        $mdlec = new TableEnterpriseCustomer( $this->_adapter );
        $mdlmc = new TableManagementCustomer( $this->_adapter );

        // 加盟店顧客を新規作成しているのであれば、管理顧客の紐付けを行う
        $manCustId = $mdlmc->getMaxManCustId($oseq);

        // (存在しない時は新規登録)
        if ($manCustId == -1) {

            // (T_Customerの情報を取得)
            $sql = " SELECT * FROM T_Customer WHERE OrderSeq = " . $oseq;
            $custRow = $this->_adapter->query($sql)->execute(null)->current();
            // (INSERT)
            $manCustId = $mdlmc->saveNew(
                array(
                    'NameKj' => $custRow['NameKj'],
                    'NameKn' => $custRow['NameKn'],
                    'PostalCode' => $custRow['PostalCode'],
                    'PrefectureCode' => $custRow['PrefectureCode'],
                    'PrefectureName' => $custRow['PrefectureName'],
                    'City' => $custRow['City'],
                    'Town' => $custRow['Town'],
                    'Building' => $custRow['Building'],
                    'UnitingAddress' => $custRow['UnitingAddress'],
                    'Phone' => $custRow['Phone'],
                    'MailAddress' => $custRow['MailAddress'],
                    'RegNameKj' => $custRow['RegNameKj'],
                    'RegUnitingAddress' => $custRow['RegUnitingAddress'],
                    'RegPhone' => $custRow['RegPhone'],
                    'SearchNameKj' => $custRow['SearchNameKj'],
                    'SearchNameKn' => $custRow['SearchNameKn'],
                    'SearchPhone' => $custRow['SearchPhone'],
                    'SearchUnitingAddress' => $custRow['SearchUnitingAddress'],
                    'RegistId' => $userId,
                    'UpdateId' => $userId,
                )
            );
        }

        // T_EnterpriseCustomerに管理顧客番号を設定
        $data = array(
            'ManCustId' => $manCustId,
            'UpdateId' => $userId,
        );
        $mdlec->saveUpdate($data, $entCustSeq);
    }

    private function createCreditTransferAlert($oseq, $enterpriseId, $entCustSeq, $userId)
    {
        $sql = " INSERT INTO T_CreditTransferAlert(OrderSeq, EnterpriseId, EntCustSeq, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (:OrderSeq, :EnterpriseId, :EntCustSeq, :RegistDate, :RegistId, :UpdateDate, :UpdateId, :ValidFlg)";
        $data = array(
            ':OrderSeq' => $oseq,
            ':EnterpriseId' => $enterpriseId,
            ':EntCustSeq' => $entCustSeq,
            ':RegistDate' => date('Y-m-d H:i:s'),
            ':RegistId' => $userId,
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $userId,
            ':ValidFlg' => 1
        );
        $this->_adapter->query($sql)->execute($data);

        $sql = " UPDATE T_Order SET LetterClaimStopFlg=1, MailClaimStopFlg=1, UpdateDate=:UpdateDate, UpdateId=:UpdateId WHERE OrderSeq=:OrderSeq ";
        $data = array(
            ':OrderSeq' => $oseq,
            ':UpdateDate' => date('Y-m-d H:i:s'),
            ':UpdateId' => $userId
        );
        $this->_adapter->query($sql)->execute($data);
    }

}
