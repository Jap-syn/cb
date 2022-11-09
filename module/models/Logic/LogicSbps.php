<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableEnterprise;
use models\Table\TableSite;

/**
 * 届いてから払いクラス
 */
class LogicSbps
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
     * Check Todo by ent id
     */
    public function checkSettingTodo($enterpriseId) {
        // get ent data
        $mdlEnterprise = new TableEnterprise($this->_adapter);
        $enterpriseData = $mdlEnterprise->findEnterprise($enterpriseId)->current();

        // get list sites
        $mdlSite = new TableSite($this->_adapter);
        $sites = ResultInterfaceToArray($mdlSite->getAll($enterpriseId));

        // get OemId for checking
        $sql = " SELECT * FROM M_Code WHERE ValidFlg = 1 AND CodeId = :CodeId AND Class4 = :Class4 ";
        $stm = $this->_adapter->query($sql);
        $prm = array(
            ':CodeId' => 160,
            ':Class4' => 0,
        );
        $rows = ResultInterfaceToArray($stm->execute($prm));
        $oemIds = array();
        if ($rows) {
            foreach ($rows as $row) {
                $oemIds[] = $row['KeyCode'];
            }
        }

        // check whether show link Todo Regist
        $disableLink = false;
        if ($enterpriseData['CombinedClaimMode'] != 0
                || $enterpriseData['BillingAgentFlg'] != 0 || $enterpriseData['CreditTransferFlg'] != 0
                ) {
            $disableLink = true;
        }

        // check whether regist Todo
        $isValid = false;
        $text = array();
        foreach ($sites as $site) {
            $sql = " SELECT SiteId, ValidFlg FROM T_SiteSbpsPayment WHERE SiteId = :SiteId GROUP BY SiteId ";
            $siteData = $this->_adapter->query($sql)->execute( array(':SiteId' => $site['SiteId']) )->current();
            if ($disableLink) {
                $text[$site['SiteId']] = '無効';
            } else { // enable link
                if ($siteData) { // registered
                    if ($site['PaymentAfterArrivalFlg'] == 0) {
                        $text[$site['SiteId']] = '無効';
                    } else {
                        $text[$site['SiteId']] = '有効';
                        $isValid = true;
                    }
                } else { // unregist
                    $text[$site['SiteId']] = '(未設定)';
                }
            }
        }

        return array('disableLink' => $disableLink, 'text' => $text, 'isValid' => $isValid);
    }

    public function checkHasPaymentAfterArrivalFlg($enterpriseId, $table) {

        // 注文商品と配送先取得
        $sql = " SELECT COUNT(*) cnt FROM $table s WHERE  s.EnterpriseId = :EnterpriseId AND s.PaymentAfterArrivalFlg = 1";

        $prm = array( ':EnterpriseId' => $enterpriseId );
        $stm = $this->_adapter->query( $sql )->execute($prm)->current();

        return (int) $stm['cnt'];
    }

}
