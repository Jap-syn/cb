<?php
namespace cbadmin\Controller;

use Zend\Json\Json;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Logic\Smbcpa\LogicSmbcpaConfig;
use models\Table\TableSmbcpa;
use models\Table\TableSmbcpaAccount;
use models\Table\TableSmbcpaAccountGroup;

/**
 * SMBCバーチャル口座グループおよび口座を管理するコントローラ
 */
class SmbcpagrpController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';
    /**
     * アプリケーションインスタンス
     *
     * @access protected
     * @var Application
     */
    protected $app;

    /**
     * SMBCバーチャル口座テーブル
     *
     * @access protected
     * @var TableSmbcpaAccount
     */
    protected $_accTable;

    /**
     * SMBCバーチャル口座設定
     *
     * @access protected
     * @var LogicSmbcpaConfig
     */
    protected $smbcpaConfig;

    /**
     * コントローラ初期化
     */
    protected function _init()
    {
        $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');
        $this->addStyleSheet('../css/cbadmin/smbcpa/main.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');
        $this->addJavaScript('../js/bytefx.js');
        $this->addJavaScript('../js/bytefx_scroll_patch.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/base.ui.js');

        $this->view->assign('current_action', $this->getActionName());
        $this->smbcpaConfig = new LogicSmbcpaConfig($this->app->dbAdapter);
    }

    /**
     * indexAction
     * smbcpa/listへリダイレクト
     */
    public function indexAction()
    {
        return $this->_redirect('smbcpa/list');
    }

    /**
     * detailAction
     * SMBCバーチャル口座グループ詳細
     */
    public function detailAction()
    {
        $this->setPageTitle('後払い.com - SMBCバーチャル口座グループ詳細');

        $params = $this->getParams();

        $ipp = 500;

        $grpTable = new TableSmbcpaAccountGroup($this->app->dbAdapter);
        $accTable = new TableSmbcpaAccount($this->app->dbAdapter);
        $smbcpaConfig = new LogicSmbcpaConfig($this->app->dbAdapter);

        $groupId = isset($params['gid']) ? $params['gid'] : -1;
        $grp = $grpTable->getGroupDetail($groupId);
        $validStatuses = TableSmbcpaAccount::getAvailableStatuses();
        if($grp) {

            $page = isset($params['page']) ? $params['page'] : 1;
            $page = is_numeric($page) ? $page : 1;
            if($page < 1) $page = 1;
            if($page < 1) $page = 1;
            $filters = array();

            $prm_filter = isset($params['filter']) ? $params['filter'] : '';
            foreach(explode(',', $prm_filter) as $v) {
                if(strlen($v) && is_numeric($v) && in_array($v, $validStatuses)) $filters[] = $v;
            }

            $totalCount = $accTable->countAccountsByGroupId($groupId, $filters);
            $maxPage = ceil($totalCount / $ipp);
            if(!$maxPage) $maxPage = 1;
            if($page > $maxPage) $page = $maxPage;
            $list = $accTable->getAccountsByGroupId($groupId, $filters, $page, $ipp);

            $prevGroup = null;
            $nextGroup = null;
            foreach($grpTable->getSummaryBySmbcpaId($grp['SmbcpaId']) as $grpInfo) {
                if($grpInfo['ReturnedFlg']) continue;
                if($grpInfo['AccountGroupId'] < $grp['AccountGroupId']) $prevGroup = $grpInfo;
                if($nextGroup == null && $grpInfo['AccountGroupId'] > $grp['AccountGroupId']) $nextGroup = $grpInfo;
            }

            $this->view->assign('page', $page);
            $this->view->assign('maxPage', $maxPage);
            $this->view->assign('ipp', $ipp);
            $this->view->assign('group', $grp);
            $this->view->assign('total', $totalCount);
            $this->view->assign('list', ResultInterfaceToArray($list));
            $this->view->assign('filters', $filters);
            $this->view->assign('releaseInterval', $smbcpaConfig->getReleaseAfterReceiptInterval());
            $this->view->assign('nextAccount', $accTable->fetchNextAccountByOemId($grp['OemId']));
            if(!$grp['ReturnedFlg']) {
                $this->view->assign('prevGroup', $prevGroup);
                $this->view->assign('nextGroup', $nextGroup);
            }
        }
        else {
            $this->view->assign('error', '口座グループの指定が不正です');
        }

        return $this->view;
    }

    /**
     * retAction
     * SMBCバーチャル口座返却画面
     */
    public function retAction()
    {
        $this->setPageTitle('後払い.com - SMBCバーチャル口座返却');

        $params = $this->getParams();

        $gid = isset($params['gid']) ? $params['gid'] : -1;

        $smbcpaTable = new TableSmbcpa($this->app->dbAdapter);
        $grpTable = new TableSmbcpaAccountGroup($this->app->dbAdapter);
        $accTable = new TableSmbcpaAccount($this->app->dbAdapter);
        $map = TableSmbcpaAccount::getStatusMap();
        $sts_claiming = TableSmbcpaAccount::ACCOUNT_STATUS_CLAIMING;
        $sts_closed = TableSmbcpaAccount::ACCOUNT_STATUS_CLOSED;
        $this->view->assign('map', $map);

        try {
            $grp = $grpTable->find($gid)->current();
            if(!$grp) {
                // グループ指定不正
                $this->view->assign('msg_only', true);
                throw new \Exception('指定の口座グループは存在しません');
            }

            $usage = $accTable->getAccountUsageByGroupId($gid);
            $total = 0;
            foreach($usage as $sts => $data) {
                $total += $data['count'];
            }
            $oid = $smbcpaTable->find($grp['SmbcpaId'])->current()['OemId'];

            $this->view->assign('group', $grp);
            $this->view->assign('summary', $this->_getSmbcpaSummary($oid));
            $this->view->assign('usage', $usage);
            $this->view->assign('total', $total);

            if($usage[$sts_claiming]['count'] > 0 || $usage[$sts_closed]['count'] > 0) {
                // 返却不可状態
                $this->view->assign('msg_only', true);
                throw new \Exception(sprintf('%s または %sの状態の口座が残っているので現在返却できません', $map[$sts_claiming], $map[$sts_closed]));
            }

            if($grp['ReturnedFlg']) {
                // 返却済み
                $this->view->assign('mgs_only', true);
                throw new \Exception('指定の口座グループはすでに返却済みです');
            }
        }
        catch(\Exception $err) {
            $this->view->assign('error', $err->getMessage());
        }

        return $this->view;
    }

    /**
     * doretAction
     * SMBCバーチャル口座返却処理
     */
    public function doretAction()
    {
        $params = $this->getParams();

        $gid = isset($params['gid']) ? $params['gid'] : -1;

        $smbcpaTable = new TableSmbcpa($this->app->dbAdapter);
        $grpTable = new TableSmbcpaAccountGroup($this->app->dbAdapter);
        $accTable = new TableSmbcpaAccount($this->app->dbAdapter);
        $map = TableSmbcpaAccount::getStatusMap();
        $sts_claiming = TableSmbcpaAccount::ACCOUNT_STATUS_CLAIMING;
        $sts_closed = TableSmbcpaAccount::ACCOUNT_STATUS_CLOSED;


        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $grp = $grpTable->find($gid)->current();
            if(!$grp) {
                // グループ指定不正
                $this->view->assign('msg_only', true);
                throw new \Exception('指定の口座グループは存在しません');
            }

            $usage = $accTable->getAccountUsageByGroupId($gid);
            $total = 0;
            foreach($usage as $sts => $data) {
                $total += $data['count'];
            }
            $oid = $smbcpaTable->find($grp['SmbcpaId'])->current()['OemId'];

            $this->view->assign('group', $grp);
            $this->view->assign('summary', $this->_getSmbcpaSummary($oid));
            $this->view->assign('usage', $usage);

            if($usage[$sts_claiming]['count'] > 0 || $usage[$sts_closed]['count'] > 0) {
                // 返却不可状態
                $this->view->assign('msg_only', true);
                throw new \Exception(sprintf('%s または %sの状態の口座が残っているので現在返却できません', $map[$sts_claiming], $map[$sts_closed]));
            }

            if($grp['ReturnedFlg']) {
                // 返却済み
                $this->view->assign('mgs_only', true);
                throw new \Exception('指定の口座グループはすでに返却済みです');
            }

            // 返却実行
            $grpTable->saveUpdate(array('ReturnedFlg' => 1, 'ReturnedDate' => date('Y-m-d H:i:s')), $gid);

            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            // 登録内容詳細へリダイレクト
            return $this->_redirect(sprintf('smbcpa/detail/oid/%d', $oid));
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $this->view->assign('error', $err->getMessage());
        }

        return $this->view;
    }

    /**
     * restoreAction
     * 返却済み口座グループを復活
     */
    public function restoreAction()
    {
        $params = $this->getParams();

        $gid = isset($params['gid']) ? $params['gid'] : -1;

        $smbcpaTable = new TableSmbcpa($this->app->dbAdapter);
        $grpTable = new TableSmbcpaAccountGroup($this->app->dbAdapter);
        $accTable = new TableSmbcpaAccount($this->app->dbAdapter);
        $map = TableSmbcpaAccount::getStatusMap();
        $sts_claiming = TableSmbcpaAccount::ACCOUNT_STATUS_CLAIMING;
        $sts_closed = TableSmbcpaAccount::ACCOUNT_STATUS_CLOSED;

        $allow_restore = $this->jmbcpaConfig->getAllowRestoreReturnedAccounts();
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $grp = $grpTable->find($gid)->current();
            if(!$grp) {
                // 復活機能が許可されていない場合はSmbcpa契約一覧へリダイレクト
                if(!$allow_restore) {
                    return $this->_redirect('smbcpa/list');
                }
                // グループ指定不正
                $this->view->assign('msg_only', true);
                throw new \Exception('指定の口座グループは存在しません');
            }

            $oid = $smbcpaTable->find($grp['SmbcpaId'])->current()['OemId'];
            $redirect_to = sprintf('smbcpa/detail/oid/%d', $oid);

            // 復活機能が許可されていない場合は登録内容詳細へリダイレクト
            if(!$allow_restore) {
                return $this->_redirect($redirect_to);
            }

            if($grp['ReturnedFlg']) {
                // 返却実行
                $grpTable->saveUpdate(array('ReturnedFlg' => 0, 'ReturnedDate' => null), $gid);
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();

            // 登録内容詳細へリダイレクト
            return $this->_redirect($redirect_to);
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }
    }

    /**
     * 指定OEMに関連付けられた、SMBCバーチャル口座契約情報サマリーを取得する。
     * @param int $oemId OEM ID
     * @return array
     */
    protected function _getSmbcpaSummary($oid)
    {
        $smbcpaTable = new TableSmbcpa($this->app->dbAdapter);
        return $smbcpaTable->findSummaryByOemId($oid);
    }

    /**
     * SMBCバーチャル口座登録(入力)
     */
    public function acceditAction()
    {
        $params = $this->getParams();

        // (基本情報)
        $this->setPageTitle('後払い.com - SMBCバーチャル口座登録');
        $this->view->assign('master_map', $this->_getMasterAccedit());
        $this->view->assign('oid', $params['oid']);

        return $this->view;
    }

    /**
     * SMBCバーチャル口座登録(DB登録)
     */
    public function accsaveAction()
    {
        $params = $this->getParams();

        // (基本情報)
        $this->setPageTitle('後払い.com - SMBCバーチャル口座登録');

        // (入力値)
        $this->view->assign('selBranch', $params['selBranch']);
        $this->view->assign('sttAccno', $params['sttAccno']);
        $this->view->assign('endAccno', $params['endAccno']);
        $this->view->assign('accType', $params['accType']);
        $this->view->assign('manageKey', $params['manageKey']);
        $this->view->assign('manageKeyLabel', $params['manageKeyLabel']);
        $this->view->assign('accountHolder', $params['accountHolder']);
        $this->view->assign('oid', $params['oid']);

        // バリデーション
        $errors = $this->_validateAccsave($params);
        if (count($errors) > 0) {
            $this->view->assign('validateError', $errors);
            $this->view->assign('master_map', $this->_getMasterAccedit());
            $this->setTemplate('accedit');
            return $this->view;
        }

        // (OemIdよりSmbcpaId取得)
        $smbcpaId = $this->app->dbAdapter->query(" SELECT SmbcpaId FROM T_Smbcpa WHERE OemId = :OemId ")->execute(array(':OemId' => $params['oid']))->current()['SmbcpaId'];

        // 登録処理
        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {

            // SMBC口座グループ(T_SmbcpaAccountGroup)登録
            $mdlSmbcpaAccountGroup = new TableSmbcpaAccountGroup($this->app->dbAdapter);
            $data = array(
                    'SmbcpaId' => $smbcpaId
                ,   'RegistDate' => date('Y-m-d H:i:s')
                ,   'TotalAccounts' => ((int)$params['endAccno'] - (int)$params['sttAccno'] + 1)
                ,   'ManageKey' => $params['manageKey']
                ,   'ManageKeyLabel' => $params['manageKeyLabel']
                ,   'DepositClass' => (int)$params['accType']
                ,   'ReturnedFlg' => 0
                ,   'ReturnedDate' => null
            );
            $accountGroupId = $mdlSmbcpaAccountGroup->saveNew($data);

            // SMBC口座(T_SmbcpaAccount)登録
            $mdlSmbcpaAccount = new TableSmbcpaAccount($this->app->dbAdapter);

            for ($i = (int)$params['sttAccno']; $i <= (int)$params['endAccno']; $i++) {

                $data = array(
                        'SmbcpaId' => $smbcpaId
                    ,   'AccountGroupId' => $accountGroupId
                    ,   'RegistDate' => date('Y-m-d H:i:s')
                    ,   'BranchCode' => $params['selBranch']
                    ,   'AccountNumber' => sprintf('%07d', $i)
                    ,   'AccountHolder' => $params['accountHolder']
                    ,   'Status' => 0
                    ,   'LastStatusChanged' => date('Y-m-d H:i:s')
                    ,   'NumberingDate' => null
                    ,   'EffectiveDate' => null
                    ,   'ModifiedDate' => null
                    ,   'SmbcpaStatus' => null
                    ,   'ExpirationDate' => null
                    ,   'LastReceiptDate' => null
                    ,   'ReleasedDate' => null
                );
                $accountSeq = $mdlSmbcpaAccount->saveNew($data);
            }

            //登録件数をビューへ通知
            $this->view->assign('regAcount', ((int)$params['endAccno'] - (int)$params['sttAccno'] + 1));

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            $this->view->assign('regError', $err->getMessage());
        }

        $this->view->assign('master_map', $this->_getMasterAccedit());
        $this->setTemplate('accedit');
        return $this->view;
    }

    /**
     * (SMBCバーチャル口座登録専用)マスター情報取得
     *
     * @return array
     */
    protected function _getMasterAccedit()
    {
        $branchList = array();
        $ri = $this->app->dbAdapter->query(" SELECT SmbcpaBranchCode, SmbcpaBranchName, (SELECT COUNT(1) FROM T_SmbcpaAccount WHERE BranchCode = SmbcpaBranchCode) AS AccountCount FROM M_SmbcpaBranch ORDER BY SmbcpaBranchCode ")->execute(null);
        if ($ri->count() > 0) {
            foreach ($ri as $row) {
                $branchList[$row['SmbcpaBranchCode']] = "支店コード : " . $row['SmbcpaBranchCode'] . ' ／ 支店名 : ' . $row['SmbcpaBranchName'] . ' ／ 登録済口座数 : ' . $row['AccountCount'];
            }
        }
        else {
            $branchList['0'] = "（バーチャル口座支店登録なし）";
        }

        $masters = array(
                'accType' => array(0 => '普通', 1 => '当座'),
                'branchList' => $branchList,
        );

        return $masters;
    }

    /**
     * (SMBCバーチャル口座登録専用)入力検証
     *
     * @param array $data
     * @return array
     */
    protected function _validateAccsave($data = array())
    {
        $errors = array();

        // (SMBCバーチャル口座支店)
        $key = 'selBranch';
        if (!isset($errors[$key]) && ((int)$data[$key] == 0)) {
            $errors[$key] = array("バーチャル口座支店登録を先に行ってください");
        }

        // (口座開始番号)
        $key = 'sttAccno';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座開始番号'は必須です");
        }
        if (!isset($errors[$key]) && !(preg_match("/^[0-9]+$/", $data[$key]))) {
            $errors[$key] = array("'口座開始番号'は半角数字で入力してください");
        }

        // (口座終了番号)
        $key = 'endAccno';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座終了番号'は必須です");
        }
        if (!isset($errors[$key]) && !(preg_match("/^[0-9]+$/", $data[$key]))) {
            $errors[$key] = array("'口座終了番号'は半角数字で入力してください");
        }

        // (口座番号 : 範囲指定チェック)
        if (!isset($errors['sttAccno']) && !isset($errors['endAccno']) && (!((int)$data['sttAccno'] < (int)$data['endAccno']))) {
            $errors['endAccno'] = array("'口座番号の範囲指定(開始⇔終了)'に誤りがあります");
        }

        // (口座番号 : 重複チェック)
        if (!isset($errors['sttAccno']) && !isset($errors['endAccno'])) {
            $sql = " SELECT COUNT(1) AS cnt FROM T_SmbcpaAccount WHERE BranchCode = :BranchCode AND (AccountNumber >= :AccountNumberStt AND AccountNumber <= :AccountNumberEnd) ";
            $cnt = $this->app->dbAdapter->query($sql)->execute(array(':BranchCode' => $data['selBranch'], ':AccountNumberStt' => sprintf('%07d', $data['sttAccno']), ':AccountNumberEnd' => sprintf('%07d', $data['endAccno'])))->current()['cnt'];
            if ($cnt > 0) {
                $errors['endAccno'] = array("既に登録済みの口座番号（支店コードに対して）が指定されています");
            }
        }

        // (口座種別)※評価不要
        $key = 'accType';

        // (管理グループキー(表示用))
        $key = 'manageKey';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'管理グループキー'は必須です");
        }

        // (管理グループ名(表示用))
        $key = 'manageKeyLabel';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'管理グループ名'は必須です");
        }

        // (口座名義カナ)
        $key = 'accountHolder';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座名義カナ'は必須です");
        }

        return $errors;
    }
}
