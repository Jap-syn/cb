<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Log\Logger;

use Coral\Base\BaseLog;

use models\Table\TableClaimHistory;
use models\Table\TableOrder;
use models\Table\TableOem;
use models\Table\TableEnterprise;
use models\Table\TableSystemProperty;
use models\Table\TableOemClaimAccountInfo;
use models\Table\TableSmbcRelationLog;

use models\Logic\BarcodeData\LogicBarcodeDataCvs;
use models\Logic\Exception\LogicClaimException;
use models\Logic\Smbcpa\LogicSmbcpaAccount;
use models\Table\TableClaimError;
use models\Table\TableSite;
use models\Table\TableSubscriberCode;


class LogicOemClaimAccount2 {

    /** 閾値定数：コンビニ支払可能上限額 @var int */
    const CLAIM_AMOUNT_LIMIT_AMOUNT = 300000;

    /** メッセージ定数：請求金額がCLAIM_AMOUNT_LIMIT_AMOUNT以上の場合のバーコード代替メッセージ @var string */
    const CLAIM_AMOUNT_OVER_LIMIT_MESSAGE = 'コンビニエンスストアでは30万円を超えるお支払いはできません。';

    /** 口座サービス区分定数：仮想口座（SMBC決済ステーション） @var string */
    const SERVICE_KIND_SMBC = 1;

    /** 口座サービス区分定数：仮想口座（ジャパンネットバンク） @var string */
    const SERVICE_KIND_JNB = 2;

    /** 口座サービス区分定数：仮想口座（SMBCバーチャル口座） @var string */
    const SERVICE_KIND_SMBCPA = 3;

    /** 口座サービス区分ラベル定数：3 - 仮想口座（SMBCバーチャル口座） @var string */
    const SERVICE_KIND_LABEL_SMBCPA = '仮想口座（SMBCバーチャル口座）';

    /** 口座サービス区分ラベル定数（ショート）：3 - SMBCバーチャル口座 @var string */
    const SERVICE_KIND_LABEL_S_SMBCPA = 'SMBCEB';

    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
     * ロガーインスタンス
     *
     * @var BaseLog
     */
    protected $_logger;

    /**
     * LogicOemClaimAccount2の新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     * @param BaseLog $logger ロガー
     */
    public function __construct(Adapter $adapter, $logger) {
        $this->_adapter = $adapter;
        $this->_logger = $logger;
    }

    /**
     * 請求口座登録処理
     *
     * @param array $data
     * @param array $exparams
     */
    public function SaveNewForBatch2($data, $exparams)
    {
        $mdlch = new TableClaimHistory($this->_adapter);

        // 01.請求履歴新規登録
        $data['ClaimSeq'] = (int)$exparams['cntClaimHistory2'] + 1;
        $claimHistorySeq = $mdlch->saveNewEx($data);

        // 02.請求口座データ登録
        $this->insertClaimAccountInfo2($claimHistorySeq, $data['OrderSeq'], $exparams);

        return $claimHistorySeq;
    }

    /**
     * 指定請求履歴を元に請求口座情報をT_OemClaimAccountInfoへ新規追加する(2019-10-01 以降)
     *
     * @param int $chSeq 請求履歴SEQ
     * @param int $orderSeq 注文SEQ
     * @param array $exparams
     * @param boolean $is_strict 印刷済み請求履歴は許容しない「厳密モード」かの指定。省略時はtrue
     * @return int T_OemClaimAccountInfo.ClaimAccountSeq
     */
    protected function insertClaimAccountInfo2($chSeq, $orderSeq, $exparams, $is_strict = true)
    {
        $data = $this->createClaimAccountInfoData2($chSeq, $exparams, $is_strict);
        $data['Status'] = 0;
        try {
             $caSeq = (new TableOemClaimAccountInfo($this->_adapter))->saveNewEx($chSeq, $data, $orderSeq, $exparams['ocaiNextInnerSeq']);

             $update_data = array(
                     'Status' => 1
             );

             $smbc_registered = false;
             $smbcpa_used = false;
             switch($data['Bk_ServiceKind']) {
                 case self::SERVICE_KIND_SMBC :
                     $smbc_update_data = $this->registerToSmbcRelationService($chSeq, $caSeq);
                     if(!empty($smbc_update_data)) {
                         $update_data = array_merge($update_data, $smbc_update_data);
                         $update_data['Bk_ServiceKind'] = self::SERVICE_KIND_SMBC;
                         $smbc_registered = true;
                     } else {
                         // SMBC連携に失敗した場合、エラーメッセージを例外として投げる
                         $message = null;
                         if (!$this->isSmbcSucceed($caSeq, $message)) {
                             throw new LogicClaimException($message, LogicClaimException::ERR_CODE_SMBC);
                         }
                     }
                     break;
             }
             if(!$smbc_registered) {
                 // SMBC決済ステーションを利用しない／利用できない場合はSMBCバーチャル口座を払い出す
                 $smbcpa_update_data = $this->openSmbcpaAccount($chSeq, $exparams);
                 if(!empty($smbcpa_update_data)) {
                     $update_data = array_merge($update_data, $smbcpa_update_data);
                     $update_data['Bk_ServiceKind'] = self::SERVICE_KIND_SMBCPA;
                     $smbcpa_used = true;
                 }
             }

             //ペイジー収納番号発番
             $sql = "SELECT OemId FROM T_Order WHERE OrderSeq = :OrderSeq";
             $oemid = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orderSeq))->current()['OemId'];

             $claimPattern = isset($exparams['ClaimPattern']) ? $exparams['ClaimPattern'] : 8; //指定が無い場合は連携を行わない

             $logicpayeasy = new LogicPayeasy($this->_adapter, $this->_logger);
             if($logicpayeasy->isPayeasyOem($oemid) && $claimPattern <= 7){

$this->_logger->info('ペイジー収納番号発番処理 ClaimHistorySeq:'.$chSeq.' OrderSeq:'.$orderSeq.' ClaimPattern:'.$claimPattern );

                 $responseBody = '';
                 $message = null;

                 //収納番号発番処理
                 $isSuccess = $logicpayeasy->getBkNumber($chSeq, $responseBody, $message);
                 if($isSuccess == false){
                     //ClaimErrorにエラー情報を登録
                     $mdlce = new TableClaimError($this->_adapter);
                     $mdlce->saveNew ( array (
                             'OrderSeq' => $orderSeq,
                             'ErrorCode' => LogicClaimException::ERR_CODE_PAYEASY,
                             'ErrorMsg' => $message
                     ) );
                     throw new LogicClaimException($message, LogicClaimException::ERR_CODE_PAYEASY);
                 }
                 //更新データに収納機関受付番号と加盟店取引番号を追加する
                 $update_data['ConfirmNumber'] = $responseBody['stran'];
                 $update_data['CustomerNumber'] = $responseBody['bktrans'];
             }
             // 請求口座情報を更新する
             (new TableOemClaimAccountInfo($this->_adapter))->saveUpdate($update_data, $caSeq);

        }
        catch(\Exception $err) {
            // DBインサート時の例外のみハンドルしてログ出力
            $this->info(sprintf('[insertClaimAccountInfo : chSeq = %s, strict = %s] ERROR !!! message = %s (%s)', $chSeq, $is_strict ? 'YES' : 'NO', $err->getMessage(), get_class($err)));
            throw $err;
        }
    }

    /**
     * 指定請求履歴を元に請求口座情報をT_OemClaimAccountInfoへ新規追加する(2019-10-01 以降)
     *
     * @param int $chSeq 請求履歴SEQ
     * @param array $exparams
     * @param boolean $is_strict 印刷済み請求履歴は許容しない「厳密モード」かの指定。省略時はtrue
     * @return int T_OemClaimAccountInfo.ClaimAccountSeq
     */
    protected function createClaimAccountInfoData2($chSeq, $exparams, $is_strict = true)
    {
        try {
            $mdlch = new TableClaimHistory($this->_adapter);
            $mdlo = new TableOrder($this->_adapter);
            $mdloem = new TableOem($this->_adapter);
            $mdlent = new TableEnterprise($this->_adapter);
            $mdlsysp = new TableSystemProperty($this->_adapter);
            $mdlsite = new TableSite($this->_adapter);
            $mdlssc = new TableSubscriberCode($this->_adapter);

            // 請求履歴データを取得 (親記述のありえないtrapは除外)
            $his = $mdlch->find($chSeq)->current();

            // 注文データを取得 (親記述のありえないtrapは除外)
            $ord = $mdlo->find($his['OrderSeq'])->current();

            // OEM ID確定
            $oemId = (int)$ord['OemId'];

            // OEMデータを取得 (親記述のありえないtrapは除外)
            $oemData = $mdloem->find($oemId)->current();

            // 今回の請求に使用する口座関連情報確定
            // キャッチボール分や初回請求、OEM先設定で再請求を自社印刷にするポリシーの場合は確定しているOEM ID、
            // そうでない場合（＝再請求をCBにする設定のOEM先の再請求）はキャッチボールの情報で構築する
            $accData = $this->findClaimAccountsByOemId(($oemId == 0 || $his['ClaimPattern'] == 1 || !$oemData['ReclaimAccountPolicy']) ? $oemId : 0)->current();

            // ゆうちょ請求口座情報をコードマスターから取得
            $row = $this->_adapter->query(" SELECT Class2, Class3 FROM M_Code WHERE CodeId = 180 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => ($oemId * 10) + $his['ClaimPattern']))->current();
            if ($row) {
                $accData['Yu_SubscriberName'] = $row['Class3'];
                $accData['Yu_AccountNumber']  = $row['Class2'];
            }

            // 加盟店データを取得 (親記述のありえないtrapは除外)
            $ent = $mdlent->find($ord['EnterpriseId'])->current();
            if (strlen($his['EnterpriseBillingCode']) > 0 && $ent['ChargeClass'] == 2) {
                $row = $this->_adapter->query(" SELECT Class2, Class3 FROM M_Code WHERE CodeId = 193 AND KeyCode = :KeyCode ")->execute(array(':KeyCode' => ($oemId * 10) + 1))->current();
                if ($row) {
                    $accData['Yu_SubscriberName'] = $row['Class3'];
                    $accData['Yu_AccountNumber']  = $row['Class2'];
                    $accData['Yu_ChargeClass']    = 2;
                }
            }

            // サイトデータを取得
            $ri = $mdlsite->findSite($ord['SiteId']);
            // サイトデータが存在しない場合はエラー
            if (!($ri->count() > 0)) {
            	throw new \Exception('サイト情報が見つかりません');
            }
            $siteData = $ri->current();
//            $mSubscriberCodeData = array();
//            if(!empty($siteData['SubscriberCode'])){
//            	//
//            	$ri = $mdlssc->findReceiptAgentIdSubscriberCode($siteData['ReceiptAgentId'],$siteData['SubscriberCode']);
//                if (count($ri) > 0) {
//            		$mSubscriberCodeData = $ri;
//            	}
//            }

            // 不要カラムを除去
            $accData = $this->trimAccountInfoData($accData);

            // 返却データ構築開始、以下のカラムを追加
            //   請求履歴SEQ
            //   請求用紙モード
            $data = array_merge(
            array(
                    'ClaimHistorySeq' => $chSeq,
                    'ClaimLayoutMode' => $this->calcClaimLayoutMode($chSeq)
            ), $accData);

            // 有効なアカウントデータが設定されていない場合は例外
            if(empty($data['Bk_AccountNumber']) || empty($data['Yu_AccountNumber']) || empty($data['Cv_SubscriberCode'])) {
                throw new \Exception('請求口座設定が設定されていないか不完全な状態です');
            }

            // 請求履歴.請求パターンが1 or 2 かつ、加盟店.NTTスマートトレード利用フラグが1：利用するの場合、以下の3項目を書き換える。
            if ( ($his['ClaimPattern'] == '1' || $his['ClaimPattern'] == '2') && $ent['NTTSmartTradeFlg'] == '1' ) {
                $data['Cv_BarcodeLogicName'] = 'NTTSmartTrade';
                $data['Cv_ReceiptAgentCode'] = $mdlsysp->getValue('[DEFAULT]', 'systeminfo', 'ReceiptAgentCode');
                $data['Cv_SubscriberCode'] = $mdlsysp->getValue('[DEFAULT]', 'systeminfo', 'SubscriberCode');
            }
            // 請求履歴.請求パターンが1以外、且つ、バーコードロジックが決済ナビの場合、バーコードロジックを＠ペイメントに変更する
            if ($his['ClaimPattern'] != 1 && $data['Cv_BarcodeLogicName'] == 'KessaiNavi') {
                $data['Cv_BarcodeLogicName'] = 'AtPayment';
            }
            //加盟店.LINE Pay利用（＠ペイメント用）が1：利用する かつ コンビニ収納代行会社.バーコード生成ロジッククラス名がAtPaymentの場合
            if( $ent['NTTSmartTradeFlg'] == '0' &&  $data['Cv_BarcodeLogicName'] == 'AtPayment'){
            	$data['Cv_SubscriberCode'] = str_pad($siteData['SubscriberCode'],5,'0');
            	$data['Cv_SiteId'] = sprintf("%06d",$siteData['SiteId']);
            	/*
                if(!empty($siteData['SubscriberCode']) && $mSubscriberCodeData['LinePayUseFlg']==1 ){
            		$data['Cv_SiteId'] = sprintf("%06d",$siteData['SiteId']);
            	}else{
            		$data['Cv_SiteId'] = sprintf("%06d",0);
            	}
                */
            }
            // サイト.三菱UFJ＝ON、且つ、初回請求の場合、CvsNetバーコードロジックを利用する
            if (($his['ClaimPattern'] == 1) && ($siteData['MufjBarcodeUsedFlg'] == '1')) {
                $cvs = $this->getCvsNetRecord();
                if(!$cvs) {
                    ;
                } else {
                    $data['Cv_ReceiptAgentName'] = $cvs['ReceiptAgentName'];
                    $data['Cv_BarcodeLogicName'] = $cvs['BarcodeLogicName'];
                    $data['Cv_ReceiptAgentCode'] = $cvs['ReceiptAgentCode'];
                    $data['Cv_SubscriberCode'] = $siteData['MufjBarcodeSubscriberCode'];
                }
            }

            // ゆうちょMT/DTデータを補完
            $data = $this->fillYuchoMtData($data, $exparams);

            // 消費税額等 算出
            $data = $this->calcShareTaxAmount($data, $exparams);

            $data['Ent'] = $ord['EnterpriseId'];

            // CVSバーコードデータを補完
            $data = $this->fillBarcodeData($data, $exparams);

            return $data;
        }
        catch(\Exception $err) {
            $msg = $err->getMessage() . '%s';
            $info = '';
            try {
                if($oemId != null) {
                    $info = sprintf(' (OemId = %s, OrderSeq = %s, Seq = %s)', $oemId, $ord['OrderSeq'], $chSeq);
                } else
                    if($ord != null) {
                        $info = sprintf(' (OrderSeq = %s, Seq = %s)', $ord['OrderSeq'], $chSeq);
                    }
            }
            catch(\Exception $innerError) {
                $info = sprintf(' (Seq = %s)', $chSeq);
            }
            // エラーをロギングするために例外をハンドル
            $this->info(sprintf('[createClaimAccountInfoData chSeq = %s] ERROR !!! messege = %s (%s)', $chSeq, sprintf($msg, $info), get_class($err)));
            throw $err;
        }
    }

    /**
     * findClaimAccountsByOemIdで取得したデータから、
     * T_OemClaimAccountInfoに不要なカラムを除去する
     *
     * @param array $data findClaimAccountsByOemIdで取得したアカウントデータ
     * @return array
     */
    protected function trimAccountInfoData(array $data) {
        $cols = array(
                'Bk_AccountId',
                'Bk_ModifiedDate',
                'Yu_AccountId',
                'Yu_ModifiedDate',
                'Cv_AccountId',
                'Cv_ReceiptAgentId',
                'Cv_ModifiedDate',
                'Cv_InvalidFlg',

                // T_SmbcRelationAccount由来の情報はすべて不要
                'Smbc_DisplayName',
                'Smbc_ApiVersion',
                'Smbc_BillMethod',
                'Smbc_KessaiId',
                'Smbc_ShopCd',
                'Smbc_SyunoCoCd1',
                'Smbc_SyunoCoCd2',
                'Smbc_SyunoCoCd3',
                'Smbc_SyunoCoCd4',
                'Smbc_SyunoCoCd5',
                'Smbc_SyunoCoCd6',
                'Smbc_ShopPwd1',
                'Smbc_ShopPwd2',
                'Smbc_ShopPwd3',
                'Smbc_ShopPwd4',
                'Smbc_ShopPwd5',
                'Smbc_ShopPwd6',
                'Smbc_SeikyuuName',
                'Smbc_SeikyuuKana',
                'Smbc_HakkouKbn',
                'Smbc_YuusousakiKbn',
                'Smbc_Yu_SubscriberName',
                'Smbc_Yu_AccountNumber',
                'Smbc_Yu_ChargeClass',
                'Smbc_Yu_SubscriberData',
                'Smbc_Cv_ReceiptAgentName',
                'Smbc_Cv_ReceiptAgentCode',
                'Smbc_Cv_SubscriberName',
                'Smbc_RegistDate',
                'Smbc_UpdateDate'
        );
        foreach($cols as $col) {
            if(isset($data[$col])) {
                unset($data[$col]);
            }
        }
        return $data;
    }

    /**
     * 指定請求履歴の請求用紙モードを算出する
     *
     * @param int $chSeq 請求履歴SEQ
     * @return 請求用紙モード（0：通常、1：封書、2：同梱）
     */
    protected function calcClaimLayoutMode($chSeq) {
        // 同梱請求の場合は2
        // 初回請求の場合はサイトの初回請求用紙モード（0または1）
        // 再１の場合はサイトの初回請求用紙モード（0または1）
        // それ以外は0
        $q = <<<EOQ
SELECT
    (CASE
        WHEN his.EnterpriseBillingCode IS NOT NULL THEN 2
        ELSE
            (CASE
                WHEN his.ClaimPattern = 1 THEN
                    (CASE sit.FirstClaimLayoutMode
                        WHEN 1 THEN 1
                        ELSE 0
                    END)
                WHEN his.ClaimPattern = 2 THEN
                    (CASE sit.FirstClaimLayoutMode
                        WHEN 1 THEN 1
                        ELSE 0
                    END)
                ELSE 0
            END)
    END) AS ClaimLayoutMode
FROM
    T_ClaimHistory his INNER JOIN
    T_Order ord ON ord.OrderSeq = his.OrderSeq INNER JOIN
    T_Site sit ON sit.SiteId = ord.SiteId INNER JOIN
    T_Enterprise ent ON ent.EnterpriseId = ord.EnterpriseId LEFT OUTER JOIN
    T_Oem oem ON oem.OemId = ent.OemId LEFT OUTER JOIN
    T_OemBankAccount oba ON oba.OemId = oem.OemId
WHERE
    his.Seq = :Seq
EOQ;
        // 当該行が見つからない場合は0
        $row = $this->_adapter->query($q)->execute(array(':Seq' => $chSeq))->current();
        return ($row) ? (int)$row['ClaimLayoutMode'] : 0;
    }

    /**
     * 指定OEM向けの請求口座設定を取得する
     *
     * @param int $oemId OEM-ID
     * @return ResultInterface | null
     */
    protected function findClaimAccountsByOemId($oemId = 0) {
        $oemId = (int)$oemId;
        $sql = $this->_buildFetchQuery(array('v.OemId = ' . $oemId));
        $ri = $this->_adapter->query($sql)->execute(null);
        if (!($ri->count() > 0)) {
            return null;
        }
        return $ri;
    }

    /**
     * 指定条件で請求口座設定向けデータを問い合わせるSQLを組み立てる
     *
     * @param null | array $expressions 検索条件のリスト
     * @return string
     */
    protected function _buildFetchQuery(array $expressions = array()) {
        $q = <<<EOQ
SELECT
    v.OemId,
    v.NameKj,
    v.ReclaimAccountPolicy,

    b.BankAccountId     AS  Bk_AccountId,
    b.ServiceKind       AS  Bk_ServiceKind,
    b.BankCode          AS  Bk_BankCode,
    b.BranchCode        AS  Bk_BranchCode,
    b.BankName          AS  Bk_BankName,
    b.BranchName        AS  Bk_BranchName,
    b.DepositClass      AS  Bk_DepositClass,
    b.AccountNumber     AS  Bk_AccountNumber,
    b.AccountHolder     AS  Bk_AccountHolder,
    b.AccountHolderKn   AS  Bk_AccountHolderKn,
    b.RegistDate        AS  Bk_RegistDate,
    b.UpdateDate        AS  Bk_UpdateDate,

    y.YuchoAccountId    AS  Yu_AccountId,
    y.SubscriberName    AS  Yu_SubscriberName,
    y.AccountNumber     AS  Yu_AccountNumber,
    y.ChargeClass       AS  Yu_ChargeClass,
    y.SubscriberData    AS  Yu_SubscriberData,
    y.Option1           AS  Yu_Option1,
    y.Option2           AS  Yu_Option2,
    y.Option3           AS  Yu_Option3,
    y.RegistDate        AS  Yu_RegistDate,
    y.UpdateDate        AS  Yu_UpdateDate,

    c.CvsAccountId      AS  Cv_AccountId,
    c.ReceiptAgentId    AS  Cv_ReceiptAgentId,
    c.SubscriberCode    AS  Cv_SubscriberCode,
    c.SubscriberName    AS  Cv_SubscriberName,
    c.Option1           AS  Cv_Option1,
    c.Option2           AS  Cv_Option2,
    c.Option3           AS  Cv_Option3,
    c.RegistDate        AS  Cv_RegistDate,
    c.UpdateDate        AS  Cv_UpdateDate,

    r.ReceiptAgentName  AS  Cv_ReceiptAgentName,
    r.ReceiptAgentCode  AS  Cv_ReceiptAgentCode,
    r.BarcodeLogicName  AS  Cv_BarcodeLogicName,
    r.ValidFlg          AS  Cv_ValidFlg,

    s.DisplayName           AS  Smbc_DisplayName,
    s.ApiVersion            AS  Smbc_ApiVersion,
    s.BillMethod            AS  Smbc_BillMethod,
    s.KessaiId              AS  Smbc_KessaiId,
    s.ShopCd                AS  Smbc_ShopCd,
    s.SyunoCoCd1            AS  Smbc_SyunoCoCd1,
    s.SyunoCoCd2            AS  Smbc_SyunoCoCd2,
    s.SyunoCoCd3            AS  Smbc_SyunoCoCd3,
    s.SyunoCoCd4            AS  Smbc_SyunoCoCd4,
    s.SyunoCoCd5            AS  Smbc_SyunoCoCd5,
    s.SyunoCoCd6            AS  Smbc_SyunoCoCd6,
    s.ShopPwd1              AS  Smbc_ShopPwd1,
    s.ShopPwd2              AS  Smbc_ShopPwd2,
    s.ShopPwd3              AS  Smbc_ShopPwd3,
    s.ShopPwd4              AS  Smbc_ShopPwd4,
    s.ShopPwd5              AS  Smbc_ShopPwd5,
    s.ShopPwd6              AS  Smbc_ShopPwd6,
    s.SeikyuuName           AS  Smbc_SeikyuuName,
    s.SeikyuuKana           AS  Smbc_SeikyuuKana,
    s.HakkouKbn             AS  Smbc_HakkouKbn,
    s.YuusousakiKbn         AS  Smbc_YuusousakiKbn,
    s.Yu_SubscriberName     AS  Smbc_Yu_SubscriberName,
    s.Yu_AccountNumber      AS  Smbc_Yu_AccountNumber,
    s.Yu_ChargeClass        AS  Smbc_Yu_ChargeClass,
    s.Yu_SubscriberData     AS  Smbc_Yu_SubscriberData,
    s.Cv_ReceiptAgentName   AS  Smbc_Cv_ReceiptAgentName,
    s.Cv_ReceiptAgentCode   AS  Smbc_Cv_ReceiptAgentCode,
    s.Cv_SubscriberName     AS  Smbc_Cv_SubscriberName,
    s.RegistDate            AS  Smbc_RegistDate,
    s.UpdateDate            AS  Smbc_UpdateDate,

    jnb.JnbId               AS  Jnb_Id,
    jnb.DisplayName         AS  Jnb_DisplayName,
    jnb.BankCode            AS  Jnb_BankCode,
    jnb.BankName            AS  Jnb_BankName,
    jgr.GroupCount          AS  Jnb_GroupCount,
    jgr.TotalAccounts       AS  Jnb_TotalAccounts,
    smbcpa.SmbcpaId         AS  Smbcpa_Id,
    smbcpa.DisplayName      AS  Smbcpa_DisplayName,
    smbcpa.BankCode         AS  Smbcpa_BankCode,
    smbcpa.BankName         AS  Smbcpa_BankName,
    sgr.GroupCount          AS  Smbcpa_GroupCount,
    sgr.TotalAccounts       AS  Smbcpa_TotalAccounts

FROM
    (
        SELECT OemId, OemNameKj AS NameKj, ReclaimAccountPolicy FROM T_Oem
        UNION ALL
        SELECT 0 AS OemId, 'キャッチボール' AS NameKj, 0 AS ReclaimAccountPolicy
    ) v LEFT OUTER JOIN
    T_OemBankAccount b ON b.OemId = v.OemId
        LEFT OUTER JOIN
    T_OemYuchoAccount y ON y.OemId = v.OemId
        LEFT OUTER JOIN
    T_OemCvsAccount c ON c.OemId = v.OemId
        LEFT OUTER JOIN
    M_CvsReceiptAgent r ON r.ReceiptAgentId = c.ReceiptAgentId
        LEFT OUTER JOIN
    T_SmbcRelationAccount s ON s.OemId = v.OemId
        LEFT OUTER JOIN
    T_Jnb jnb ON (jnb.OemId = b.OemId AND IFNULL(jnb.ValidFlg, 0) = 1)
        LEFT OUTER JOIN
    (
        SELECT JnbId, COUNT(*) AS GroupCount, SUM(TotalAccounts) AS TotalAccounts
        FROM T_JnbAccountGroup
        GROUP BY JnbId
    ) jgr ON jgr.JnbId = jnb.JnbId
        LEFT OUTER JOIN
    T_Smbcpa smbcpa ON (smbcpa.OemId = b.OemId AND IFNULL(smbcpa.ValidFlg, 0) = 1)
        LEFT OUTER JOIN
    (
        SELECT SmbcpaId, COUNT(*) AS GroupCount, SUM(TotalAccounts) AS TotalAccounts
        FROM T_SmbcpaAccountGroup
        GROUP BY SmbcpaId
    ) sgr ON sgr.SmbcpaId = smbcpa.SmbcpaId
%s
ORDER BY
    v.OemId
EOQ;
        $where = '';
        if(!empty($expressions)) {
            $where = 'WHERE ' . join(' AND ', $expressions) . PHP_EOL;
        }
        return sprintf($q, $where);
    }

    /**
     * 指定のデータにゆうちょMT/DT用データを補完する
     *
     * @param array $data
     * @param array $exparams
     * @return array
     */
    protected function fillYuchoMtData(array $data, $exparams) {
        $util = new LogicYuchoUtility();

        // バーコード生成に必要な注文SEQ、請求日、バーコード用の再請求回数、支払期限、支払金額を取得する
        $subData = $this->getSubDataForBarcode($data['ClaimHistorySeq'], $exparams['cntClaimHistory'])->current();

        // 上段データ
        if(strlen($data['Yu_AccountNumber']) != 11) {
            // 登録桁数が11桁でない場合は12桁に補完した上で6桁目を除去
            $accNumber = substr(sprintf('%012d', 0).$data['Yu_AccountNumber'], -12);
            $accNumber = join('', array(substr($accNumber, 0, 5), substr($accNumber, -6)));
        } else {
            // 登録桁数が11桁の場合はそのまま使用
            $accNumber = $data['Yu_AccountNumber'];
        }
        $data1_part = array(
                $accNumber,                                                 // 口座番号11桁
                sprintf('%011d', (int)$subData['ClaimAmount']),             // 請求金額ゼロ詰11桁
                $data['Yu_ChargeClass'],                                    // 払込負担区分
                '00000',                                                    // 予備ゼロ詰5桁
                substr(sprintf('%09d', 0).$data['Yu_SubscriberData'], -9)   // 加入者固有データ9桁
        );
        $data1 = join('', $data1_part);

        // 下段データ
        $data2 = sprintf('%042s', $subData['OrderSeq']);                                // 注文SEQゼロ詰42桁

        $cd1 = $util->calcMtCode($data1);
        $cd2 = $util->calcMtCode($data2);

        return array_merge(
        $data,
        array(
                'Yu_MtOcrCode1' => sprintf('%s%s   X', $cd1, $data1),   // 上段は末尾に「   X」（4桁）を付与する
                'Yu_MtOcrCode2' => $cd2 . $data2
        ) );
    }

    /**
     * 指定の請求履歴SEQから、バーコード生成に必要な各種データを取得する
     *
     * @param int $chSeq 請求履歴SEQ
     * @param int $chCnt 請求履歴件数
     * @return ResultInterface
     */
    protected function getSubDataForBarcode($chSeq, $chCnt) {
        $q = <<<EOQ
SELECT
    h.OrderSeq,
    h.ClaimDate,
    (:cntClaimHistory - 1) AS ReIssueCount,
	CASE WHEN s.BarcodeLimitDays=999 THEN '999999'
	ELSE  DATE_FORMAT(DATE_ADD(h.LimitDate, INTERVAL s.BarcodeLimitDays DAY), '%y%m%d')
	END  AS Bc_LimitDate,
    h.ClaimAmount - IFNULL(c.ReceiptAmountTotal,  0) AS ClaimAmount
FROM
    T_ClaimHistory h
    LEFT OUTER JOIN T_ClaimControl c ON h.ClaimId = c.ClaimId
    LEFT OUTER JOIN T_Order o ON h.OrderSeq = o.OrderSeq
    LEFT OUTER JOIN T_Site s ON o.SiteId = s.SiteId
WHERE
    h.Seq = :Seq
EOQ;

        return $this->_adapter->query($q)->execute(array(':Seq' => $chSeq, ':cntClaimHistory' => $chCnt));
    }

    /**
     * 指定請求履歴の商品代金から内消費税額を算出する(2019-10-01 以降)
     *
     * @param array $data
     * @param array $exparams
     * @return array
     * TODO: 消費税算出基準額を請求金額合計に変更する可能性あり → getClaimAmount()に切り替える
     */
    protected function calcShareTaxAmount(array $data, array $exparams) {
        $sysProps = new TableSystemProperty($this->_adapter);

        $hisTable = new TableClaimHistory($this->_adapter);
        $seq = $data['ClaimHistorySeq'];

        $sql = " SELECT * FROM T_ClaimHistory WHERE Seq = :Seq ";
        $his = $this->_adapter->query($sql)->execute(array(':Seq' => $seq))->current();
        $price = (int)$hisTable->getClaimAmount($seq);

        $rows = $exparams['oiLists'];

        // 変数初期化
        $total = 0;
        $ClaimAmount_0 = 0;
        $ClaimAmount_8 = 0;
        $ClaimAmount_10 = 0;
        $discount = 0;

        foreach($rows as $datas) {
            if ($datas['DataClass'] == 1) {
                if($datas['TaxRate'] == 0 && $datas['SumMoney'] > 0) { // 0％対象商品
                    $ClaimAmount_0 += $datas['SumMoney'];
                } else if($datas['TaxRate'] == 8 && $datas['SumMoney'] > 0) { // 8％対象商品
                    $ClaimAmount_8 += $datas['SumMoney'];
                } elseif($datas['TaxRate'] == 10 && $datas['SumMoney'] > 0) { // 10％対象商品
                    $ClaimAmount_10 += $datas['SumMoney'];
                } elseif($datas['SumMoney'] < 0) {  // 値引き額
                    $discount += -1 * $datas['SumMoney'];
                }
            } else {
                $ClaimAmount_10 += $datas['SumMoney'];
            }
        }


        $total = $ClaimAmount_0 + $ClaimAmount_8 + $ClaimAmount_10;

        // 注文に外税(DataClass:4)がある時の対応
        $outtax = 0;
        $taxrow = $exparams['oiTaxrow'];
        if((int)$taxrow['cnt'] > 0) {
            $outtax = $taxrow['SumMoney'];
        }

        // 督促時の再請求手数料を算出
        $reclaimfee = $price - $total + $discount - $outtax;

        // 再請求手数料を10％対象商品に加算
        $ClaimAmount_10 = $ClaimAmount_10 + $reclaimfee;
        //消費税率0%の合計値がHigh:1,Middle:2,Low:3
        $taxFlag0 = 0;
        //消費税率8%の合計値がHigh:1,Middle:2,Low:3
        $taxFlag8 = 0;
        // 金額が大きい税率を判定
        if($ClaimAmount_8 >= $ClaimAmount_10){
            if($ClaimAmount_8 >= $ClaimAmount_0){
                $high = $ClaimAmount_8;
                $taxrateHigh = 8;
                $taxFlag8 = 1;
                if($ClaimAmount_0 >= $ClaimAmount_10){
                    $middle = $ClaimAmount_0;
                    $taxrateMiddle = 0;
                    $low = $ClaimAmount_10;
                    $taxrateLow = 10;
                    $taxFlag0 = 2;
                }else{
                    $middle = $ClaimAmount_10;
                    $taxrateMiddle = 10;
                    $low = $ClaimAmount_0;
                    $taxrateLow = 0;
                    $taxFlag0 = 3;
                }
            } else {
                $high = $ClaimAmount_0;
                $taxrateHigh = 0;
                $middle = $ClaimAmount_8;
                $taxrateMiddle = 8;
                $low = $ClaimAmount_10;
                $taxrateLow = 10;
                $taxFlag0 = 1;
                $taxFlag8 = 2;
            }
        } else{
            if($ClaimAmount_0 >= $ClaimAmount_10){
                $high = $ClaimAmount_0;
                $taxrateHigh = 0;
                $taxFlag0 = 1;
                $middle = $ClaimAmount_10;
                $taxrateMiddle = 10;
                $low = $ClaimAmount_8;
                $taxrateLow = 8;
                $taxFla8 = 3;
            } else {
                $high = $ClaimAmount_10;
                $taxrateHigh = 10;
                if($ClaimAmount_0 >= $ClaimAmount_8){
                    $middle = $ClaimAmount_0;
                    $taxrateMiddle = 0;
                    $low = $ClaimAmount_8;
                    $taxrateLow = 8;
                    $taxFlag0 = 2;
                    $taxFlag8 = 3;
                }else{
                    $middle = $ClaimAmount_8;
                    $taxrateMiddle = 8;
                    $low = $ClaimAmount_0;
                    $taxrateLow = 0;
                    $taxFlag0 = 3;
                    $taxFlag8 = 2;
                }
            }
        }

        // 入金の有無
        $sql = " SELECT * FROM T_ClaimControl WHERE ClaimId = :ClaimId ";
        $cc = $this->_adapter->query($sql)->execute(array(':ClaimId' => $his['ClaimId']))->current();
        if ($cc !== false) {
            $discount += $cc['ReceiptAmountTotal'];
        }

        // 値引き計算（按分）
        if ($discount > 0) {
            $discountHigh = round($discount * $high / ($total + $reclaimfee));
            $discountMiddle = round($discount * $middle / ($total + $reclaimfee));
            $discountLow  = $discount - ($discountHigh + $discountMiddle);

            $amountHigh = $high - $discountHigh;
            $amountMiddle = $middle - $discountMiddle;
            $amountLow  = $low  - $discountLow;
        } else {
            $amountHigh = $high;
            $amountMiddle  = $middle;
            $amountLow  = $low;
        }

        //消費税率0%の合計値がHigh:1の場合
        if($taxFlag0 == 1){
            $amountHigh = $amountMiddle;
            if($taxFlag8 == 2){
                $taxrateHigh = 8;
            } else {
                $taxrateHigh = 10;
            }

            //消費税率0%の合計値がLow:3の場合
        } else if($taxFlag0 == 3){
            $amountLow = $amountMiddle;
            if($taxFlag8 == 2){
                $taxrateLow = 8;
            } else {

                $taxrateLow = 10;
            }
        }

        // 消費税額の計算
        if ((int)$taxrow['cnt'] > 0) { // 外税時
            $taxHigh = ceil($amountHigh * $taxrateHigh / 100);
            if ($taxHigh > $outtax){
                $taxHigh = $outtax;
            }
            $taxLow  = $outtax - $taxHigh ;
        } else {

            $taxHigh = ceil($amountHigh / (100 + $taxrateHigh) * $taxrateHigh);
            $taxLow  = ceil($amountLow  / (100 + $taxrateLow ) * $taxrateLow );
        }

        if ($taxrateHigh == 8){
            $ClaimAmount_8  = $amountHigh;
            $TaxAmount_8    = $taxHigh;
            $ClaimAmount_10 = $amountLow;
            $TaxAmount_10   = $taxLow;
            $TaxAmount      = $taxHigh + $taxLow;
        }else if($taxrateHigh == 10){
            $ClaimAmount_8  = $amountLow;
            $TaxAmount_8    = $taxLow;
            $ClaimAmount_10 = $amountHigh;
            $TaxAmount_10   = $taxHigh;
            $TaxAmount      = $taxHigh + $taxLow;
        }else if($taxFlag8 == 2){
            $ClaimAmount_8  = $amountHigh;
            $TaxAmount_8    = $taxHigh;
            $ClaimAmount_10 = $amountLow;
            $TaxAmount_10   = $taxLow;
            $TaxAmount      = $taxHigh + $taxLow;
        }else if($taxFlag8 == 3){
            $ClaimAmount_8  = $amountLow;
            $TaxAmount_8    = $taxLow;
            $ClaimAmount_10 = $amountHigh;
            $TaxAmount_10   = $taxHigh;
            $TaxAmount      = $taxHigh + $taxLow;
        }

        // 外税額設定が無しは、内税計算時に請求金額が1円のものは内税額を0円
        if ((int)$taxrow['cnt'] == 0) {
            if ($ClaimAmount_8 == 1) {
                $TaxAmount = $TaxAmount - $TaxAmount_8;
                $TaxAmount_8 = 0;
            }
            if ($ClaimAmount_10 == 1) {
                $TaxAmount = $TaxAmount - $TaxAmount_10;
                $TaxAmount_10 = 0;
            }
        }

        return array_merge(
        array(
                'TaxAmount'      => $TaxAmount,
                'SubUseAmount_1' => $ClaimAmount_8,
                'SubTaxAmount_1' => $TaxAmount_8,
                'SubUseAmount_2' => $ClaimAmount_10,
                'SubTaxAmount_2' => $TaxAmount_10
        ), $data);
    }

    /**
     * 利用可能なバーコード生成ロジック名とロジッククラス名をマッピングした
     * マスター連想配列を取得する
     *
     * @static
     * @return array
     */
    public static function getBarcodeLogicClasses() {
        return array(
                'Aplus' => 'LogicBarcodeDataCvsAplus',
                'AtPayment' => 'LogicBarcodeDataCvsAtPayment',
                'NTTSmartTrade' => 'LogicBarcodeDataCvsNTTSmartTrade',
                'IndividualPay' => 'LogicBarcodeDataCvsIndividualPay',
                'KessaiNavi' => 'LogicBarcodeDataCvsKessaiNavi',
                'CvsNet' => 'LogicBarcodeDataCvsCvsNet',
        );
    }

    /**
     * 指定のバーコード生成ロジック名に対応するロジッククラス名を取得する
     *
     * @static
     * @param string $cbLogicName バーコード生成ロジック名
     * @return string
     */
    public static function getBarcodeLogicClassName($bcLogicName) {
        $map = self::getBarcodeLogicClasses();
        return isset($map[$bcLogicName]) ? $map[$bcLogicName] : null;
    }

    /**
     * 指定のデータにコンビニバーコードデータを補完する
     *
     * @param array $data
     * @param array $exparams
     * @return array
     */
    protected function fillBarcodeData(array $data, $exparams) {
        $chSeq = $data['ClaimHistorySeq'];

        /** @var LogicStampFee 印紙代関連ユーティリティ */
        $stampFeeSettings = (new TableSystemProperty($this->_adapter))->getStampFeeSettings();
        $sfUtil = LogicBarcodeDataCvs::createStampFeeLogic($stampFeeSettings);

        // バーコード生成に必要な注文SEQ、請求日、バーコード用の再請求回数、支払期限、支払金額を取得する
        $subData = $this->getSubDataForBarcode($chSeq, $exparams['cntClaimHistory'])->current();

        // 支払金額がコンビニ支払上限額以内ならバーコードデータを生成
        if(((int)$subData['ClaimAmount']) <= self::CLAIM_AMOUNT_LIMIT_AMOUNT) {
            /** @var LogicBarcodeDataCvsAbstract バーコードデータ生成ロジック */
            $bcGen = LogicBarcodeDataCvs::createGenerator(
                self::getBarcodeLogicClassName($data['Cv_BarcodeLogicName']),
                $data['Cv_ReceiptAgentCode'],
                $data['Cv_SubscriberCode']
            );

            if(date('Y-m-d') > '2019-09-30'){
                $hisTable = new TableClaimHistory($this->_adapter);
                $claimtotal = (int)$hisTable->getClaimAmount($data['ClaimHistorySeq']);
                $ClaimAmount = $claimtotal - $data['TaxAmount'];

                if ($ClaimAmount < 50000 ){
                    $claimtotal += 1;
                }

                $bcGen
                    ->setUniqueSequence($subData['OrderSeq'])
                    ->setReIssueCount($subData['ReIssueCount'])
                    ->setLimitDate($subData['Bc_LimitDate'])
                    ->setPaymentMoney($subData['ClaimAmount'])
                    ->setStampFlagThresholdPrice($claimtotal);

            } else {
                // バーコードロジックをセットアップ
                $bcGen
                    ->setUniqueSequence($subData['OrderSeq'])
                    ->setReIssueCount($subData['ReIssueCount'])
                    ->setLimitDate($subData['Bc_LimitDate'])
                    ->setPaymentMoney($subData['ClaimAmount'])
                    ->setStampFlagThresholdPrice($sfUtil->getStampFeeThresholdAt($subData['ClaimDate']));
            }
            // CB店子管理コード
            $bcGen->setSiteId($data['Cv_SiteId']);

            // バーコードデータを生成
            $bcData = $bcGen->generate();
            $bcStrings = $bcGen->generateString();
        } else {
            // 支払金額が30万超の場合はメッセージを入れておく
            $bcData = self::CLAIM_AMOUNT_OVER_LIMIT_MESSAGE;
            $bcStrings = array('', ''); // 表示文字列は空にしておく
        }

        // 元データにマージして返却
        return array_merge(
        array(
                'Cv_BarcodeData' => $bcData,
                'Cv_BarcodeString1' => $bcStrings[0],
                'Cv_BarcodeString2' => $bcStrings[1]
        ), $data);
    }

    /**
     * 指定の請求履歴の注文情報を元にSMBC決済ステーションへ請求情報登録する
     *
     * @access protected
     * @var int $chSeq 請求履歴SEQ
     * @var int $caSeq OEM請求口座SEQ
     * @return array 請求情報登録で獲得できた口座情報
     */
    protected function registerToSmbcRelationService($chSeq, $caSeq)
    {
        $his = (new TableClaimHistory($this->_adapter))->find($chSeq)->current();
        if(!in_array($his['ClaimPattern'], array(1, 2))) {
            // 初回請求、再請求1以外は決済ステーション連携は行わない
            return array();
        }

        /** @var LogicSmbcRelationServiceRegister */
        $service = LogicSmbcRelation::openRegisterService($this->_adapter, $this->_logger);

        $dep_map = array(
                '1' => 0,
                '2' => 1
        );

        try {
            // 請求情報登録を実行
            $result = $service->sendTo($chSeq);

            // 受信データから各種口座情報を抽出する準備
            $logTable = new TableSmbcRelationLog($this->_adapter);

            // コンビニ情報の反映は必須
            $update_values = $logTable->extractCvsDataByClaimAccount($caSeq);

            // 銀行口座情報の反映
            $update_values = array_merge($update_values, $logTable->extractBankAccountDataByClaimAccount($caSeq));

            // 用紙レイアウトが封書または同梱の場合はゆうちょ口座情報も反映
            $oca = (new TableOemClaimAccountInfo($this->_adapter))->find($caSeq)->current();
            if(!$oca) throw new \Exception ('claim account info not found !!!');
            if(in_array($oca['ClaimLayoutMode'], array(1, 2))) {
                $update_values = array_merge($update_values, $logTable->extractYuchoDataByClaimAccount($caSeq));
            }

            return $update_values;

        } catch(\Exception $err) {
            $this->info(sprintf('[registerToSmbcRelationService chSeq = %s, caSeq = %s] ERROR !!! messege = %s (%s)', $chSeq, $caSeq, $err->getMessage(), get_class($err)));
            // エラー情報はすでに決済ステーションログに永続化されているので、
            // ここでは戻り値を空の配列にして処理終了
            return array();
        }
    }

    /**
     * 指定の請求履歴の注文情報向けに、SMBCバーチャル口座を払い出す。
     * 対象の注文でSMBCバーチャル口座が利用できない場合、このメソッドの戻り値は空の配列となる
     *
     * @access protected
     * @var int $chSeq 請求履歴SEQ
     * @var array $exparams
     * @return array 払い出された銀行口座更新データ
     */
    protected function openSmbcpaAccount($chSeq, $exparams)
    {
        $his = (new TableClaimHistory($this->_adapter))->find($chSeq)->current();
        if(!$his) throw new \Exception('claim history not found !!!');

        // 再請求以降の場合はOEM設定から再請求時の名義ポリシーを取得し、CB名義だったらSMBCバーチャル口座を利用しないよう制限する
        if($his['ClaimPattern'] > 1) {
            $order = (new TableOrder($this->_adapter))->find($his['OrderSeq'])->current();
            $oem = (new TableOem($this->_adapter))->find($order['OemId'])->current();
            if($oem && $oem['ReclaimAccountPolicy']) {
                return array();
            }
        }

        $smbcpaAccountLogic = new LogicSmbcpaAccount($this->_adapter);
        try
        {
            $smbcpaAccount = $smbcpaAccountLogic->openAccountEx($his['OrderSeq'], $exparams);
            return $smbcpaAccountLogic->getAccountDataForOemClaimAccountInfo($smbcpaAccount['AccountSeq']);
        } catch(\Exception $err) {
            //NOTE. "invalid SmbcpaId specified."時は、ﾛｸﾞ出力しない(20201028)
            if ($err->getMessage() != "invalid SmbcpaId specified.") {
                $this->info(sprintf('[openSmbcpaAccount chSeq = %s] ERROR !!! messege = %s (%s)', $chSeq, $err->getMessage(), get_class($err)));
            }
            // 例外はロギングのみにして空データを返す
            return array();
        }
    }

    /**
     * SMBC連携が成功したか判断する
     * @param int $caSeq OEM請求口座SEQ
     * @param string $message エラーメッセージ(本関数がfalseを返すときのみ設定する)
     */
    protected function isSmbcSucceed($caSeq, &$message) {

        // SQL実行
        $sql = <<<EOQ
SELECT   *
FROM   T_SmbcRelationLog
WHERE  1 = 1
AND    ClaimAccountSeq = :ClaimAccountSeq
AND    TargetFunction = 1
EOQ;

        $prm = array(
            ':ClaimAccountSeq' => $caSeq,
        );

        $ri = $this->_adapter->query($sql)->execute($prm);

        if ($ri->count() <= 0) {
            // データが取得出来ない場合はtrueを返却(SMBC連携自体行う必要がなかったと判断)
            return true;
        }

        $row = $ri->current();
        if ($row['Status'] == 2) {
            // 連携に成功したのでtrueを返却
            return true;
        }

        // 後続処理でロールバックされる可能性があるので、T_SmbcRelationLogはテキストログへ出力しておく。
        $this->info("SMBC連携エラー情報：" . var_export($row, true));

        // 結果メッセージがあれば結果メッセージを設定、なければ受信エラー情報を設定
        $message = (strlen($row['ResponseMessage']) > 0) ? $row['ResponseMessage'] : $row['ErrorReason'];
        return false;

    }

    /**
     * 指定の優先度でログメッセージを出力する
     *
     * @param string $message ログメッセージ
     * @param int $priority 優先度
     */
    public function log($message, $priority) {
        $logger = $this->_logger;
        $message = sprintf('[%s] %s', get_class($this), $message);
        if($logger) {
            $logger->log($priority, $message);
        }
    }

    /**
     * DEBUGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function debug($message) {
        $this->log($message, Logger::DEBUG);
    }

    /**
     * INFOレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function info($message) {
        $this->log($message, Logger::INFO);
    }

    /**
     * NOTICEレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function notice($message) {
        $this->log($message, Logger::NOTICE);
    }

    /**
     * WARNレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function warn($message) {
        $this->log($message, Logger::WARN);
    }

    /**
     * ERRレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function err($message) {
        $this->log($message, Logger::ERR);
    }

    /**
     * CRITレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function crit($message) {
        $this->log($message, Logger::CRIT);
    }

    /**
     * ALERTレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function alert($message) {
        $this->log($message, Logger::ALERT);
    }

    /**
     * EMERGレベルでログメッセージを出力する
     *
     * @param string $message ログメッセージ
     */
    public function emerg($message) {
        $this->log($message, Logger::EMERG);
    }

    private function getCvsNetRecord()
    {
        $sql = <<<EOQ
SELECT  *
FROM    M_CvsReceiptAgent
WHERE   BarcodeLogicName = 'CvsNet'
AND     ValidFlg = 1
ORDER BY ReceiptAgentId
EOQ;

        $ri = $this->_adapter->query($sql)->execute()->current();
        return $ri;
    }
}
