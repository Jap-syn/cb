<?php
namespace oemadmin\Controller;

use Zend\Json\Json;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use oemadmin\Application;

class RworderhistController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet($this->app->getOemCss())
        ->addJavaScript('../../js/prototype.js');

        $this->setPageTitle($this->app->getOemServiceName()." - 履歴照会");
	}

	/**
	 * 履歴照会（一覧）
	 */
	public function listAction()
	{
        $params = $this->getParams();

        $oseq = isset($params['oseq']) ? $params['oseq'] : 0;

        // 注文情報取得
        $sql = " SELECT ORD_OrderId, ORD_Ent_OrderId, ORD_RegistDate, ORD_ReceiptOrderDate FROM T_OrderHistory WHERE OrderSeq = :OrderSeq LIMIT 1 ";
        $odrRow = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

        // 履歴情報取得
        $sql  = " SELECT HistorySeq ";
        $sql .= " ,      RegistDate ";
        $sql .= " ,      (SELECT KeyContent FROM M_Code WHERE CodeId = 97 AND KeyCode = HistoryReasonCode) AS HistoryReasonStr ";
        $sql .= " FROM   T_OrderHistory ";
        $sql .= " WHERE  OrderSeq = :OrderSeq ";
        $sql .= " ORDER BY HistorySeq DESC ";

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq));
        $odrList = ResultInterfaceToArray($ri);

        $this->view->assign('odrRow', $odrRow);
        $this->view->assign('odrList', $odrList);

        return $this->view;
	}

	/**
	 * 履歴照会（詳細）
	 */
	public function detailAction()
	{
        // 注文ステータスによる色分け用CSSのアサイン
        $this->addStyleSheet( '../../css/cbadmin/orderstatus/detail_' .
        ( $this->app->tools['orderstatus']['style'] ? $this->app->tools['orderstatus']['style'] : 'default' ) .
        '.css' );

        $params = $this->getParams();

        $hseq = isset($params['hseq']) ? $params['hseq'] : 0;

        // 履歴情報取得
        $sql = " SELECT * FROM T_OrderHistory WHERE HistorySeq = :HistorySeq ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':HistorySeq' => $hseq))->current();

        // 注文日の期間チェック
        $this->view->assign('isInTerm', $this->isValidOrderdate($row['ORD_ReceiptOrderDate'], $row['ORD_RegistDate']));

        // その他の情報
        $others = array();
        // (キャリア)
        $Carrier = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 19 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['CUS_Carrier']))->current()['KeyContent'];
        $others['Carrier'] = $Carrier;
        // (購入品目)
        $row_OrderItemInfo = str_replace(array("\x0d\x0a", "\x0a", "\x0d"), ' ', $row['OrderItemInfo']);
        $others['OrderItemInfo'] = Json::decode($row_OrderItemInfo, Json::TYPE_ARRAY);
        // (与信クラス)
        $others['IncreArClass'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 4 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['IncreArClass']))->current()['KeyContent'];
        // (配送方法)
        $others['DeliMethodName'] = $this->app->dbAdapter->query(" SELECT DeliMethodName FROM M_DeliveryMethod WHERE DeliMethodId = :DeliMethodId ")->execute(array(':DeliMethodId' => $row['ITM_Deli_DeliveryMethod']))->current()['DeliMethodName'];
        // (送信メール履歴)
        $row['MailInfo'] = null;
        $others['MailInfo'] = Json::decode($row['MailInfo'], Json::TYPE_ARRAY);
        if (is_null($others['MailInfo'])){ $others['MailInfo'] = array(); }

        // (督促分類)
        $others['RemindClass'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 18 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['ORD_RemindClass']))->current()['KeyContent'];
        // (状態)
        $others['CinfoStatus1'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 25 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['CUS_CinfoStatus1']))->current()['KeyContent'];
        // (状態)
        $others['CinfoStatus2'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 25 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['CUS_CinfoStatus2']))->current()['KeyContent'];
        // (状態)
        $others['CinfoStatus3'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 25 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['CUS_CinfoStatus3']))->current()['KeyContent'];
        // (理由コード)
        $others['CancelReasonCode'] = $row['CNL_CancelReasonCode'];
        // (最終改修手段)
        $others['FinalityCollectionMean'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 20 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['ORD_FinalityCollectionMean']))->current()['KeyContent'];
        // (住民票)
        $others['ResidentCard'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 24 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['CUS_ResidentCard']))->current()['KeyContent'];
        // (手書き手紙)
        $others['LonghandLetter'] = $this->app->dbAdapter->query(" SELECT KeyContent FROM M_Code WHERE CodeId = 25 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => $row['ORD_LonghandLetter']))->current()['KeyContent'];
        // (再請求)
        $ary = Json::decode($row['ReClaimInfo'], Json::TYPE_ARRAY);

        $aryCount = 0;
        if(!empty($ary)){
            $aryCount = count($ary);
        }
        for ($i=0; $i<$aryCount; $i++) {
            $pno = $ary[$i]['ClaimPattern'];
            if ($pno >= 2 && $pno <= 9) {
                // 既登録の[ClaimPattern]がある場合、Seqがより大きくない場合は更新不要
                if (isset($others['reclaim' . ($pno - 1) . 's']) && ($others['reclaim' . ($pno - 1) . 's'] > $ary[$i]['Seq'])) {
                    continue;
                }
                $others['reclaim' . ($pno - 1) . 's'] = $ary[$i]['Seq'];
                $others['reclaim' . ($pno - 1) . 'a'] = $ary[$i]['ClaimDate'];
                $others['reclaim' . ($pno - 1) . 'b'] = $ary[$i]['LimitDate'];
                $others['reclaim' . ($pno - 1) . 'c'] = $ary[$i]['Additional'];
            }
        }
        // (請求件数)
        $others['claimCount'] = (is_null($row['CLM_F_ClaimDate']) ? 0 : 1) + $aryCount;
        // (ステータス)
        $others['Status'] = Json::decode($row['StatusCaption'], Json::TYPE_ARRAY);

        // (マスター関連:メール)
        $ri = $this->app->dbAdapter->query(" SELECT Id, ClassName FROM T_MailTemplate ORDER BY Id ")->execute(null);
        foreach ($ri as $rirow) {
             $others['master']['mail'][$rirow['Id']] = $rirow['ClassName'];
        }
        // (予約項目:Reserve)
        $others['Reserve'] = Json::decode($row['Reserve'], Json::TYPE_ARRAY);

        // 住所カナ(全角化)
        $row['CUS_AddressKn'] = BaseGeneralUtils::convertNarrowToWideEx( $row['CUS_AddressKn'] );

        // 過剰入金色分けしきい値
        $excessPaymentColorThreshold = $this->app->dbAdapter->query(
            " SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'ExcessPaymentColorThreshold' ")->execute(null)->current()['PropValue'];
        $others['Status']['ExcessPaymentColorThreshold'] = intval($excessPaymentColorThreshold);
        $others['Status']['Rct_DifferentialAmount'] = $row['CLM_ClaimedBalance'];

        $this->view->assign('row', $row);
        $this->view->assign('others', $others);

        // ページング
        // (前ページ)
        $sql = " SELECT HistorySeq FROM T_OrderHistory WHERE OrderSeq = (SELECT OrderSeq FROM T_OrderHistory WHERE HistorySeq = :HistorySeq) AND HistorySeq < :HistorySeq ORDER BY HistorySeq DESC ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':HistorySeq' => $hseq))->current();
        if ($row) {
            $this->view->assign('link_previous', $this->getBaseUrl() . '/rworderhist/detail/hseq/' . $row['HistorySeq']);
        }
        else {
            $this->view->assign('link_previous', null);
        }
        // (次ページ)
        $sql = " SELECT HistorySeq FROM T_OrderHistory WHERE OrderSeq = (SELECT OrderSeq FROM T_OrderHistory WHERE HistorySeq = :HistorySeq) AND HistorySeq > :HistorySeq ORDER BY HistorySeq ASC ";
        $row = $this->app->dbAdapter->query($sql)->execute(array(':HistorySeq' => $hseq))->current();
        if ($row) {
            $this->view->assign('link_next', $this->getBaseUrl() . '/rworderhist/detail/hseq/' . $row['HistorySeq']);
        }
        else {
            $this->view->assign('link_next', null);
        }

        return $this->view;
	}

    /**
     * 注文日が妥当か？
     *
     * @param string $odrDate 注文日 yyyy-MM-dd形式で通知
     * @param string $regDate 登録日 yyyy-MM-dd形式で通知
     * @return boolean
     */
    private function isValidOrderdate($odrDate, $regDate)
    {
        // 注文登録標準期間日数、の取得
        $obj = new \models\Table\TableSystemProperty($this->app->dbAdapter);
        // (過去日:デフォルト 60日)
        $daysPast   = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysPast'  );
        $daysPast   = ($daysPast > 0) ? $daysPast : 60;
        // (未来日:デフォルト180日)
        $daysFuture = (int)$obj->getValue('[DEFAULT]', 'systeminfo', 'OrderDefaultDaysFuture');
        $daysFuture = ($daysFuture > 0) ? $daysFuture : 180;

        if ($regDate < $odrDate) {
            // 未来日が指定されている時
            $diffDate = BaseGeneralUtils::CalcSpanDays($regDate, $odrDate);
            return ($diffDate < $daysFuture) ? true : false;
        }
        else if ($regDate > $odrDate) {
            // 過去日が指定されている時
            $diffDate = BaseGeneralUtils::CalcSpanDays($odrDate, $regDate);
            return ($diffDate < $daysPast) ? true : false;
        }

        // ($odrDate == $regDate) 注文日＝登録日
        return true;
    }
}

