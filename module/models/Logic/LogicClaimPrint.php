<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Logic\LogicPayeasy;
use models\Table\TableSitePayment;
use models\Table\TableSiteSbpsPayment;
use models\Table\TableEnterprise;
use models\Table\TableClaimPrintPattern;
use models\Table\TableOemYuchoAccount;
use models\Table\TableCode;
use models\Table\TableClaimPrintCheck;
use models\Table\TablePaymentCheck;
use models\Table\TableSmbcRelationAccount;
use models\Table\TableOem;
use models\Table\TablePayment;
use models\Table\TableSbpsPayment;
use models\Logic\LogicCreditTransfer;

/**
 * 請求書印刷共通クラス
 */
class LogicClaimPrint
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
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * 印刷パターンチェックマスタ用発行回数算出
     * @param $claimPattern 請求パターン
     * @return int
     */
    public function changePrintIssueCountCd($claimPattern)
    {
        switch ($claimPattern) {
            case 0: // 0：口振
                return 0;
            case 1: // 1：初回請求
                return 1;
            case 2: // 2：再請求１
                return 2;
            case 4: // 4：再請求３
                return 3;
            case 6: // 6：再請求４
                return 4;
            case 7: // 7：再請求５
                return 5;
            case 8: // 8：再請求６
                return 6;
            case 9: // 9：再請求７
                return 7;
        }
        return 9;
    }

    /**
     * 支払方法チェック
     *
     * @param $printPatternCd 印字パターン
     * @param $spPaymentCd スマホ決済
     * @return bool|mixed
     */
    public function paymentCheck($printPatternCd, $spPaymentCd) {
        $mdl_pc = new TablePaymentCheck($this->_adapter);
        $datas = ResultInterfaceToArray($mdl_pc->find($printPatternCd));
        foreach ($datas as $data) {
            $match = true;
            for ($i=0; $i<30; $i++) {
                $def = substr($data['SpPaymentCd'], $i, 1);
                $val = substr($spPaymentCd, $i, 1);
                if ($def == 2) {
                    continue;
                }
                if ($def != $val) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $data['PaymentCheckSeq'];
            }
        }
        return false;
    }

    /**
     * @param $oid OEM ID
     * @param $eid 加盟店ID
     * @param $sid サイトID
     * @param $claimPattern 請求パターン
     * @param $paymentAfterArrivalFlg 届いてから決済フラグ
     * @param $firstClaimLayoutMode 初回請求用紙モード
     * @param $mufjBarcodeUsedFlg 三菱UFバーコード利用フラグ
     * @param $claimMypagePrint 請求書マイページ印字
     * @return array
     */
    public function create($oid, $eid, $sid, $claimPattern, $paymentAfterArrivalFlg, $firstClaimLayoutMode, $mufjBarcodeUsedFlg, $claimMypagePrint)
    {
        $mdl_c = new TableCode($this->_adapter);
        $is_saved = $mdl_c->find2(214, $eid)->count();
        if ($is_saved > 0) {
            $mdl_cpp = new TableClaimPrintPattern($this->_adapter);
            $printIssueCountCd = $this->changePrintIssueCountCd($claimPattern);
            $work = $mdl_cpp->find($eid, $sid, $printIssueCountCd);
            if ($work->count() == 0) {
                $result = array();
                $result['PrintFormCd'] = $this->createPrintFormCd($oid, $eid, $claimPattern, $firstClaimLayoutMode);
                $result['PrintPatternCd'] = $this->createPrintPatternCd($oid, $eid, $sid, $claimPattern, $paymentAfterArrivalFlg, $firstClaimLayoutMode);
                $result['PrintTypeCd'] = $this->createPrintTypeCd($oid, $eid, $claimPattern, $paymentAfterArrivalFlg, $firstClaimLayoutMode);
                $result['EnclosedSpecCd'] = $this->createEnclosedSpecCd($eid, $claimPattern);
                $result['PrintIssueCd'] = $this->createPrintIssueCd($oid, $eid, $claimPattern, $paymentAfterArrivalFlg, $mufjBarcodeUsedFlg);
                $result['SpPaymentCd'] = $this->createSpPaymentCd($oid, $sid, $claimPattern, $claimMypagePrint);
                $result['PrintIssueCountCd'] = $this->createPrintIssueCountCd($claimPattern);
                $result['AdCd'] = $this->createAdCd($claimPattern);
                $result['EnclosedAdCd'] = $this->createEnclosedAdCd($claimPattern);
            } else {
                $result = $mdl_cpp->find($eid, $sid, $printIssueCountCd)->current();
            }
        } else {
            $result = array();
            $result['PrintFormCd'] = $this->createPrintFormCd($oid, $eid, $claimPattern, $firstClaimLayoutMode);
            $result['PrintPatternCd'] = $this->createPrintPatternCd($oid, $eid, $sid, $claimPattern, $paymentAfterArrivalFlg, $firstClaimLayoutMode);
            $result['PrintTypeCd'] = $this->createPrintTypeCd($oid, $eid, $claimPattern, $paymentAfterArrivalFlg, $firstClaimLayoutMode);
            $result['EnclosedSpecCd'] = $this->createEnclosedSpecCd($eid, $claimPattern);
            $result['PrintIssueCd'] = $this->createPrintIssueCd($oid, $eid, $claimPattern, $paymentAfterArrivalFlg, $mufjBarcodeUsedFlg);
            $result['SpPaymentCd'] = $this->createSpPaymentCd($oid, $sid, $claimPattern, $claimMypagePrint);
            $result['PrintIssueCountCd'] = $this->createPrintIssueCountCd($claimPattern);
            $result['AdCd'] = $this->createAdCd($claimPattern);
            $result['EnclosedAdCd'] = $this->createEnclosedAdCd($claimPattern);
        }

        $result['ErrorCd'] = 0;
        $result['ErrorMsg'] = '印刷帳票:'.$result['PrintFormCd']
            .', 印字パターン:'.$result['PrintPatternCd']
            .', 版下:'.$result['PrintTypeCd']
            .', 請求種別:'.$result['EnclosedSpecCd']
            .', 発行元:'.$result['PrintIssueCd']
            .', スマホ決済:'.$result['SpPaymentCd']
            .', 発行回数:'.$result['PrintIssueCountCd']
            .', はがき広告:'.$result['AdCd']
            .', 封書広告:'.$result['EnclosedAdCd']
        ;

        // パターンチェック
        $mdl_cpc = new TableClaimPrintCheck($this->_adapter);
        $data_cpc = $mdl_cpc->find($result['PrintFormCd'], $result['PrintTypeCd'], $result['PrintIssueCd'], $result['PrintIssueCountCd']);
        if ($data_cpc->count() == 0) {
            // 印刷パターンチェックエラー
            $result['ErrorCd'] = 1;
        }
        $check = $this->paymentCheck($result['PrintPatternCd'], $result['SpPaymentCd']);
        if ($check === false) {
            // 支払方法チェックエラー
            $result['ErrorCd'] = 2;
        }

        return $result;
    }

    /**
     * 印刷帳票生成
     *
     * @param $oid OEM ID
     * @param $eid 加盟店ID
     * @param $claimPattern 請求パターン
     * @param $firstClaimLayoutMode 初回請求用紙モード
     * @return string
     */
    private function createPrintFormCd($oid, $eid, $claimPattern, $firstClaimLayoutMode)
    {
        $printIssueCountCd = $this->changePrintIssueCountCd($claimPattern);
        $mdl_e = new TableEnterprise($this->_adapter);
        $edata = $mdl_e->find($eid)->current();

        if ($printIssueCountCd >= 6) {
            return '102';
        }
        if ($printIssueCountCd >= 2) {
            return '001';
        }

        if ($printIssueCountCd == 0) {
            if ($edata['AppFormIssueCond'] == 2) {
                return '101';
            }

            if ($edata['AppFormIssueCond'] == 1) {
                $mdl_c = new TableCode($this->_adapter);
                $note = $mdl_c->find(207, 1)->current()['Note'];
                if ($note == $oid) {
                    return '104';
                }

                if ($oid != 2) {
                    $mdl_oya = new TableOemYuchoAccount($this->_adapter);
                    $chargeClass = $mdl_oya->findByOemId($oid)->current()['ChargeClass'];
                } else {
                    $mdl_oya = new TableSmbcRelationAccount($this->_adapter);
                    $chargeClass = $mdl_oya->findByOemId($oid)->current()['Yu_ChargeClass'];
                }
                if ($chargeClass == 2) {
                    return '103';
                } elseif ($chargeClass == 0) {
                    return '102';
                }
            }
        }

        if ($printIssueCountCd == 1) {
            if ($firstClaimLayoutMode != 1) {
                return '001';
            } else {
                $mdl_c = new TableCode($this->_adapter);
                $note = $mdl_c->find(207, 1)->current()['Note'];
                if ($note == $oid) {
                    return '104';
                }

                if ($oid != 2) {
                    $mdl_oya = new TableOemYuchoAccount($this->_adapter);
                    $chargeClass = $mdl_oya->findByOemId($oid)->current()['ChargeClass'];
                } else {
                    $mdl_oya = new TableSmbcRelationAccount($this->_adapter);
                    $chargeClass = $mdl_oya->findByOemId($oid)->current()['Yu_ChargeClass'];
                }
                if ($chargeClass == 2) {
                    return '103';
                } elseif ($chargeClass == 0) {
                    return '102';
                }
            }
        }

        return '999';
    }

    /**
     * 印字パターン生成
     *
     * @param $oid OEM-ID
     * @param $eid 加盟店ID
     * @param $sid サイトID
     * @param $claimPattern 請求パターン
     * @param $paymentAfterArrivalFlg 届いてから決済フラグ
     * @param $firstClaimLayoutMode 初回請求用紙モード
     * @return string
     */
    private function createPrintPatternCd($oid, $eid, $sid, $claimPattern, $paymentAfterArrivalFlg, $firstClaimLayoutMode)
    {
        $printIssueCountCd = $this->changePrintIssueCountCd($claimPattern);

        $payeasy = new LogicPayeasy($this->_adapter);
        $mdl_e = new TableEnterprise($this->_adapter);
        $edata = $mdl_e->find($eid)->current();

        if ($printIssueCountCd >= 6) {
            return '208';
        }
        if (($printIssueCountCd >= 2) && ($printIssueCountCd <= 5)) {
            if ($payeasy->isPayeasyOem($oid)) {
                return '104';
            }
            return '101';
        }
        if ($printIssueCountCd == 0) {
            if ($edata['AppFormIssueCond'] == 2) {
                return '210';
            } elseif ($edata['AppFormIssueCond'] == 1) {
                $mdl_c = new TableCode($this->_adapter);
                $note = $mdl_c->find(207, 1)->current()['Note'];
                if ($note == $oid) {
                    return '209';
                }
                return '206';
            }
        }
        if ($printIssueCountCd == 1) {
            if ($firstClaimLayoutMode == 1) {
                $mdl_c = new TableCode($this->_adapter);
                $note = $mdl_c->find(207, 1)->current()['Note'];
                if ($note == $oid) {
                    return '209';
                }
                return '206';
            } else {
                if ($payeasy->isPayeasyOem($oid)) {
                    return '104';
                } elseif ($paymentAfterArrivalFlg == 1) {
                    return '103';
                } elseif ($edata['BillingAgentFlg'] == 1) {
                    return '102';
                }
                return '101';
            }
        }
        return '999';
    }

    /**
     * 版下コード生成
     *
     * @param $oid OEM-ID
     * @param $eid 加盟店ID
     * @param $claimPattern 請求パターン
     * @param $paymentAfterArrivalFlg 届いてから決済フラグ
     * @param $firstClaimLayoutMode 初回請求用紙モード
     * @return string　版下コード
     */
    private function createPrintTypeCd($oid, $eid, $claimPattern, $paymentAfterArrivalFlg, $firstClaimLayoutMode)
    {
        $printIssueCountCd = $this->changePrintIssueCountCd($claimPattern);
        $mdl_e = new TableEnterprise($this->_adapter);
        $edata = $mdl_e->find($eid)->current();
        $payeasy = new LogicPayeasy($this->_adapter);
        $mdl_c = new TableCode($this->_adapter);
        $class6 = $mdl_c->find(160, $oid)->current()['Class6'];

        // 初期デザイン
        $result_a = '999';
        if ($printIssueCountCd == 0) {
            if ($edata['AppFormIssueCond'] == 2) {
                $result_a = '007';
            } elseif ($edata['AppFormIssueCond'] == 1) {
                $mdl_c = new TableCode($this->_adapter);
                $note = $mdl_c->find(207, 1)->current()['Note'];
                if ($note == $oid) {
                    $result_a = '010';
                } else {
                    $result_a = '009';
                }
            }
        } elseif ($printIssueCountCd >= 6) {
            $result_a = '005';
//        } elseif ($payeasy->isPayeasyOem($oid)) {
//            $result_a = '003';
        } elseif ($printIssueCountCd == 1) {
            if ($edata['BillingAgentFlg'] == 1) {
                $result_a = '011';
            } elseif ($paymentAfterArrivalFlg == 1) {
                $result_a = '012';
            } elseif ($firstClaimLayoutMode == 1) {
                $note = $mdl_c->find(207, 1)->current()['Note'];
                if ($note == $oid) {
                    $result_a = '010';
                } else {
                    $result_a = '009';
                }
            } elseif ($payeasy->isPayeasyOem($oid)) {
                $result_a = '003';
            } elseif ($class6 == '001') {
                $result_a = '001';
            } elseif ($class6 == '002') {
                $result_a = '002';
                if ($printIssueCountCd >= 3) {
                    $result_a = '001';
                }
            }
        } elseif ($payeasy->isPayeasyOem($oid)) {
            $result_a = '003';
        } else {
            if ($class6 == '001') {
                $result_a = '001';
            } elseif ($class6 == '002') {
                $result_a = '002';
                if ($printIssueCountCd >= 3) {
                    $result_a = '001';
                }
            }
        }

        // 可変デザイン
        $result_b = '001';

        // 負担人区分
        $result_c = 9;
        if ($oid != 2) {
            $mdl_oya = new TableOemYuchoAccount($this->_adapter);
            $chargeClass = $mdl_oya->findByOemId($oid)->current()['ChargeClass'];
        } else {
            if ($printIssueCountCd >= 3) {
                $mdl_oya = new TableOemYuchoAccount($this->_adapter);
                $chargeClass = $mdl_oya->findByOemId($oid)->current()['ChargeClass'];
            }else{
                $mdl_oya = new TableSmbcRelationAccount($this->_adapter);
                $chargeClass = $mdl_oya->findByOemId($oid)->current()['Yu_ChargeClass'];
            }
        }
        if ($chargeClass == 2) {
            $result_c = 1;
        } elseif ($chargeClass == 0) {
            $result_c = 0;
        }

        return $result_a.$result_b.$result_c;
    }

    /**
     * 封入仕様コード生成
     *
     * @param $eid 加盟店ID
     * @param $claimPattern 請求パターン
     * @return string 封入仕様コード
     */
    private function createEnclosedSpecCd($eid, $claimPattern)
    {
        $printIssueCountCd = $this->changePrintIssueCountCd($claimPattern);
        if ($printIssueCountCd > 0) {
            return '00000';
        }

        $mdl_e = new TableEnterprise($this->_adapter);
        $edata = $mdl_e->find($eid)->current();
        if ($edata['ClaimPamphletPut'] == 1) {
            switch ($edata['CreditTransferFlg']) {
                case 1:
                    return '00004';
                case 2:
                    return '00006';
                case 3:
                    return '00005';
            }
        } else {
            switch ($edata['CreditTransferFlg']) {
                case 1:
                    return '00001';
                case 2:
                    return '00003';
                case 3:
                    return '00002';
            }
        }
        return '99999';
    }

    /**
     * 発行元コード生成
     *
     * @param $oid OEM-ID
     * @param $eid 加盟店ID
     * @param $claimPattern 請求パターン
     * @param $paymentAfterArrivalFlg 届いてから決済フラグ
     * @param $mufjBarcodeUsedFlg 三菱UFバーコード利用フラグ
     * @return string 発行元コード
     */
    private function createPrintIssueCd($oid, $eid, $claimPattern, $paymentAfterArrivalFlg, $mufjBarcodeUsedFlg)
    {
        $printIssueCountCd = $this->changePrintIssueCountCd($claimPattern);
        $mdl_c = new TableCode($this->_adapter);
//        $mdl_oem = new TableOem($this->_adapter);
//        $odata = $mdl_oem->find($oid)->current();

        // 直販/OEM
        $field = 'KeyContent';
        if ($printIssueCountCd > 0) {
            $field = 'Class'.$printIssueCountCd;
        }
        $result_a = $mdl_c->find(216, $oid)->current()[$field];
//        $result_a = substr('0'.$oid, -2);
//        if (($oid == 2) && ($printIssueCountCd >= 3)) {
//            $result_a = '00';
//        } elseif (($odata['ReclaimAccountPolicy'] == 1) && ($printIssueCountCd >= 2)) {
//            $result_a = '00';
//        }

        // 収納代行会社
        $result_b = '01';
        if (($oid == 2) && ($printIssueCountCd <= 2)) {
            $result_b = '02';
        } elseif (($oid == 6) && ($printIssueCountCd <= 1)) {
            $result_b = '03';
        } elseif (($mufjBarcodeUsedFlg == 1) && ($printIssueCountCd <= 1)) {
            $result_b = '04';
        }

        // サービス
        $result_c = '001';
        if ($printIssueCountCd == 1) {
            if ($paymentAfterArrivalFlg == 1) {
                $result_c = '002';
            } else {
                $mdl_e = new TableEnterprise($this->_adapter);
                $billingAgentFlg = $mdl_e->find($eid)->current()['BillingAgentFlg'];
                if ($billingAgentFlg == 1) {
                    $result_c = '003';
                }
            }
        }
        if (($printIssueCountCd >= 2) && ($printIssueCountCd <= 5)) {
            if ($paymentAfterArrivalFlg == 1) {
                $result_c = '002';
            }
        }

        return $result_a.$result_b.$result_c;
    }

    /**
     * スマホ決済コード生成
     *
     * @param $oid OEM-ID
     * @param $sid サイトID
     * @param $claimPattern 請求パターン
     * @param $claimMypagePrint 請求書マイページ印字
     * @return string スマホ決済コード
     */
    private function createSpPaymentCd($oid, $sid, $claimPattern, $claimMypagePrint)
    {
        $mdi_mp = new TablePayment($this->_adapter);
        $mdi_msp = new TableSbpsPayment($this->_adapter);
        $mdi_sp = new TableSitePayment($this->_adapter);
        $mdi_ssp = new TableSiteSbpsPayment($this->_adapter);
        $mp_datas = ResultInterfaceToArray($mdi_mp->fetchAllSubscriberCodeAll($oid));
        $msp_datas = ResultInterfaceToArray($mdi_msp->findOemAll($oid));
        $sp_datas = ResultInterfaceToArray($mdi_sp->getAll($sid));
        $ssp_datas = ResultInterfaceToArray($mdi_ssp->getAll($sid));

        // マイページ
        $result_1 = $claimMypagePrint;

        // 広告用白紙
        $result_2 = '1';
        if ($this->createAdCd($claimPattern) == '0000') {
            $result_2 = '0';
        }

        $result_3 = '';
        $i = 1;
        // SitePaymentの定義分
        foreach ($mp_datas as $master) {
            $sw = false;
            if ($master['SortId'] > $i) {
                do {
                    $result_3 .= '9';
                    $i++;
                } while ($master['SortId'] != $i);
            }
            if (sizeof($sp_datas) == 0) {
                $result_3 .= '0';
                $i++;
            } else {
                foreach ($sp_datas as $date) {
                    if ($date['PaymentId'] == $master['PaymentId']) {
                        if (($date['UseFlg'] == 1) && ($date['UseStartFixFlg'] == 1) && ($date['ValidFlg'] == 1)) {
                            $result_3 .= '1';
                        } else {
                            $result_3 .= '0';
                        }
                        $i++;
                        $sw = true;
                        break;
                    }
                }
                if (!$sw) {
                    $result_3 .= '0';
                    $i++;
                }
            }
            if ($i > 10) {
                break;
            }
        }

        // SitePaymentの未定義分
        if ($i < 10) {
            for ($j=$i; $j <= 10; $j++) {
                $result_3 .= '9';
            }
        }

        $result_4 = '';
        $i = 1;
        // SiteSbpsPaymentの定義分
        foreach ($msp_datas as $master) {
            $sw = false;
            if ($master['SortId'] > $i) {
                do {
                    $result_4 .= '9';
                    $i++;
                } while ($master['SortId'] != $i);
            }
            if (sizeof($ssp_datas) == 0) {
                $result_4 .= '0';
                $i++;
            } else {
                foreach ($ssp_datas as $data) {
                    if ($data['PaymentId'] == $master['SbpsPaymentId']) {
                        if (($data['UseStartDate'] < date('Y-m-d H:i:s')) && ($data['ValidFlg'] == 1)) {
                            $result_4 .= '1';
                        } else {
                            $result_4 .= '0';
                        }
                        $i++;
                        $sw = true;
                        break;
                    }
                }
                if (!$sw) {
                    $result_4 .= '0';
                    $i++;
                }
            }
            if ($i > 18) {
                break;
            }
        }

        // T_SitePaymentの未定義分
        if ($i < 18) {
            for ($j=$i; $j <= 18; $j++) {
                $result_4 .= '9';
            }
        }

        return $result_1.$result_2.$result_3.$result_4;
    }

    /**
     * 発行回数コード生成
     *
     * @param $claimPattern 請求パターン
     * @return string 発行回数コード
     */
    private function createPrintIssueCountCd($claimPattern)
    {
        return substr('0' . $this->changePrintIssueCountCd($claimPattern), -2);
    }

    /**
     * 発行回数コード生成（注文考慮）
     *
     * @param $oseq 注文SEQ
     * @param $claimPattern 請求パターン
     * @return string
     */
    public function createPrintIssueCountCdReal($oseq, $claimPattern) {
        $result = $this->changePrintIssueCountCd($claimPattern);
        if ($result != 1) {
            return '0'.$result;
        }
        $lgc = new LogicCreditTransfer($this->_adapter);
        $creditTransferMethod = $lgc->getCreditTransferMethod($oseq);
        if ($creditTransferMethod == 0) {
            return '01';
        }
        return '00';
        /*

                $sql = <<<EOQ
        SELECT SUM(UseAmount) as amt
          FROM T_Order o
         WHERE o.Cnl_Status = 0
           AND o.P_OrderSeq = :OrderSeq
        EOQ;
                $prm = array(
                    ':OrderSeq' => $oseq,
                );
                $amt = $this->_adapter->query($sql)->execute($prm)->current()['amt'];
                if ($amt <= 0) {
                    $sql = ' SELECT e.CreditTransferFlg,e.AppFormIssueCond,ao.CreditTransferRequestFlg FROM T_Order o LEFT JOIN T_Enterprise e ON o.EnterpriseId=e.EnterpriseId LEFT JOIN AT_Order ao ON o.OrderSeq=ao.OrderSeq WHERE o.OrderSeq = :OrderSeq ';
                    $ent = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();
                    if ((($ent['CreditTransferFlg'] == 1) || ($ent['CreditTransferFlg'] == 2) || ($ent['CreditTransferFlg'] == 3)) && (($ent['AppFormIssueCond'] == 0) || ($ent['AppFormIssueCond'] == 2))) {
                        return '00';
                    }
                }

                return '01';
        */
    }

    /**
     * 広告コード生成
     *
     * @param $claimPattern 請求パターン
     * @return string 広告コード
     */
    private function createAdCd($claimPattern)
    {
        if ($claimPattern == 1) {
            return '00000';
        }
        return '00000';
    }

    /**
     * 封書広告コード生成
     *
     * @return string 封書広告コード
     */
    private function createEnclosedAdCd($claimPattern)
    {
        if ($claimPattern == 1) {
            return '00000';
        }
        return '00000';
    }
}
