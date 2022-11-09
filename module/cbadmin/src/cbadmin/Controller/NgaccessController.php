<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use cbadmin\Application;
use models\Table\TableNgAccessClear;

class NgaccessController extends CoralControllerAction
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

        $userInfo = $this->app->authManagerAdmin->getUserInfo();
        $this->view->assign('userInfo', $userInfo );

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 不正アクセス解除指示");
    }

    /**
     * 不正アクセス登録リスト表示
     */
    public function listAction()
    {
        $params = $this->getParams();
        $mode = isset($params['mode']) ? $params['mode'] : 'enterprise';

        $this->view->assign("mode", $mode);
        $this->view->assign("ipdatas", $this->_getNgaccessIpList($mode));
        $this->view->assign("logindatas", $this->_getNgaccessLoginList($mode));

        return $this->view;
    }

    /**
     * 不正アクセス解除指示実施
     */
    public function upAction()
    {
        $params = $this->getParams();

        $mdl = new TableNgAccessClear($this->app->dbAdapter);

        // 不正アクセスIPアドレス側
        $i = 0;
        while(isset($params['iploginid' . $i])) {

            // チェックオン未通知時は、$iのインクリメントのみ実施
            if (!isset($params['ipchk' . $i])) { $i++; continue; }

            // 新規or更新(Typeは0固定)
            $row = $mdl->findLoginidType($params['iploginid' . $i], 0)->current();
            if ($row) {
                $mdl->saveUpdate(array('Status' => 1, 'ServerStatus' => '0,0', 'IndicateDate' => date('Y-m-d H:i:s')), $row['Seq']);
            }
            else {
                $mdl->saveNew(array('LoginId' => $params['iploginid' . $i], 'Type' => 0, 'Status' => 1, 'ServerStatus' => '0,0'));
            }

            $i++;
        }

        // 不正アクセスログイン側
        $i = 0;
        while(isset($params['loginid' . $i])) {

            // チェックオン未通知時は、$iのインクリメントのみ実施
            if (!isset($params['chk' . $i])) { $i++; continue; }

            // 新規or更新
            $row = $mdl->findLoginidType($params['loginid' . $i], $params['type' . $i])->current();
            if ($row) {
                $mdl->saveUpdate(array('Status' => 1, 'ServerStatus' => '0,0', 'IndicateDate' => date('Y-m-d H:i:s')), $row['Seq']);
            }
            else {
                $mdl->saveNew(array('LoginId' => $params['loginid' . $i], 'Type' => $params['type' . $i], 'Status' => 1, 'ServerStatus' => '0,0'));
            }

            $i++;
        }

        $_SESSION['NGACCESSCLEAR_UPDATED'] = true;
        return $this->_redirect('ngaccess/list/mode/' . $params['mode']);
    }

    /**
     * 不正アクセスリスト(IPアドレス)取得
     *
     * @return array 不正アクセスリスト
     */
    protected function _getNgaccessIpList($mode)
    {
        // IP表示でない場合は空の配列を返却
        if ($mode != 'ip') {
            return array();
        }

        // 規定値取得
        $ngAccessLimit = (int)$this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessIpLimit' "
            )->execute(null)->current()['PropValue'];

        $mypageNgAccessLimit = (int)$this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessIpLimit' "
            )->execute(null)->current()['PropValue'];

        $sql = <<<EOQ
SELECT t.LoginId, t.Type, IFNULL(nac.Status,0) AS Status
FROM   (SELECT IpAddress AS LoginId, 0 AS Type FROM T_NgAccessIp WHERE Count >= :Count
        UNION
        SELECT IpAddress, 0 FROM MPV_NgAccessIp WHERE Count >= :MypageCount
       ) t
       LEFT OUTER JOIN T_NgAccessClear nac ON (t.LoginId = nac.LoginId AND t.Type = nac.Type)
WHERE  1 = 1
ORDER BY LoginId
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':Count' => $ngAccessLimit, ':MypageCount' => $mypageNgAccessLimit));

        return ResultInterfaceToArray($ri);
    }

    /**
     * 不正アクセスリスト(ログイン)取得
     *
     * @return array 不正アクセスリスト
     */
    protected function _getNgaccessLoginList($mode)
    {
        $type = 0;
        if ($mode == 'enterprise') {
            $type = 1;
        }elseif ($mode == 'orderpage') {
            $type = 5;
        }elseif ($mode == 'mypage') {
            $type = 4;
        }elseif ($mode == 'cbadmin') {
            $type = 2;
        }elseif ($mode == 'oemadmin') {
            $type = 3;
        }else {
            // 上記以外のmodeが通知された場合は空の配列を返却
            return array();
        }

        // 規定値取得
        $ngAccessLimit = (int)$this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];
        $mypageNgAccessLimit = (int)$this->app->dbAdapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        $sql = <<<EOQ
SELECT t.LoginId, t.Type, IFNULL(nac.Status,0) AS Status, t.NameKj, IFNULL(oem.OemNameKj, '直営') AS OemNameKj
FROM   (SELECT e.LoginId, 1 AS Type, e.EnterpriseNameKj AS NameKj, e.OemId FROM T_Enterprise e,T_NgAccessEnterprise nge WHERE e.EnterpriseId = nge.EnterpriseId AND nge.NgAccessCount >= :NgAccessCount
        UNION ALL
        SELECT LoginId, 2, NameKj, 0 FROM T_Operator WHERE NgAccessCount >= :NgAccessCount
        UNION ALL
        SELECT LoginId, 3, NameKj, OemId FROM T_OemOperator WHERE NgAccessCount >= :NgAccessCount
        UNION ALL
        SELECT LoginId, 4, RegNameKj, OemId FROM MPV_MypageCustomer WHERE NgAccessCount >= :MypageNgAccessCount
        UNION ALL
        SELECT CONCAT(namo.OemId, '@', namo.Phone) AS LoginId, 5, MAX(c.NameKj), namo.OemId
        FROM   MPV_NgAccessMypageOrder namo
               INNER JOIN T_MypageOrder mo ON ( (CASE WHEN mo.OemId = 2 THEN 0 ELSE mo.OemId END) = namo.OemId AND mo.Phone = namo.Phone)
               INNER JOIN T_Customer c ON (c.OrderSeq = mo.OrderSeq)
        WHERE  namo.NgAccessCount >= :MypageNgAccessCount
        GROUP BY CONCAT(namo.OemId, '@', namo.Phone)
       ) t
       LEFT OUTER JOIN T_NgAccessClear nac ON (t.LoginId = nac.LoginId AND t.Type = nac.Type)
       LEFT OUTER JOIN T_Oem oem ON (t.OemId = oem.OemId)
WHERE  1 = 1
AND    t.Type = :Type
ORDER BY Type, LoginId
EOQ;

        $ri = $this->app->dbAdapter->query($sql)->execute(array(':NgAccessCount' => $ngAccessLimit,':MypageNgAccessCount' => $mypageNgAccessLimit, ':Type' => $type));

        return ResultInterfaceToArray($ri);
    }
}

