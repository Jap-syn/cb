<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Text\Table\Table;

/**
 * 加盟店キャンペーンクラス
 */
class LogicCampaign
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
     * キャンペーン情報取得
     *
     * @param int $eid 加盟店ID
     * @param int $sid サイトID
     * @return array 取得データの配列
     */
    public function getCampaignInfo($eid, $sid)
    {
        // 取得したい項目を設定
        $getCol = array(
            'AppPlan',
            'MonthlyFee',
            'IncludeMonthlyFee',
            'ApiMonthlyFee',
            'CreditNoticeMonthlyFee',
            'NCreditNoticeMonthlyFee',
            'ReserveMonthlyFee',
            'OemMonthlyFee',
            'OemIncludeMonthlyFee',
            'OemApiMonthlyFee',
            'OemCreditNoticeMonthlyFee',
            'OemNCreditNoticeMonthlyFee',
            'OemReserveMonthlyFee',
            'SettlementAmountLimit',
            'SettlementFeeRate',
            'ClaimFeeDK',
            'ClaimFeeBS',
            'ReClaimFee',
            'OemSettlementFeeRate',
            'OemClaimFee',
            'SelfBillingOemClaimFee',
            'SystemFee',
        );

        $getData = array();
        // 項目分ループして該当項目のデータを取得し、配列へ格納する。
        foreach ($getCol as $key => $value) {
            $sql = "SELECT F_GetCampaignVal(:pi_enterprise_id, :pi_site_id, :pi_today, :pi_type) AS :type_name;";
            $data = $this->_adapter->query($sql)->execute(array( ':pi_enterprise_id' => $eid, ':pi_site_id' => $sid, ':pi_today' => date('Y-m-d'), ':pi_type' => $value , ':type_name' => $value ))->current();
            // 取得したデータを配列へ格納
            if ($value == 'AppPlan') {
                $getData['Plan'] = $data[$value];
            } else {
                $getData[$value] = $data[$value];
            }
        }

        return $getData;
    }

}
