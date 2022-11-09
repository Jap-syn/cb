<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableCreditTransfer;
use cbadmin\Application;
use models\Table\TableEnterpriseCustomer;
use models\Table\TableUser;

class CreditTransferController extends CoralControllerAction{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Controllerを初期化する
     */
    public function _init(){
        $this->app = Application::getInstance();
        $userInfo = $this->app->authManagerAdmin->getUserInfo();
        $this->view->assign('userInfo', $userInfo);

        $this->addStyleSheet('../css/default02.css')
             ->addJavaScript( '../js/prototype.js' );

        $this->setPageTitle("後払い.com - 口座振替期間設定");

        $masters = array(
            'CreditTransferSpanFromMonth' => array(-2 => '前々月', -1 => '前月', 0 => '当月'),
            'CreditTransferSpanToTypeMonth' => array(-1 => '前月', 0 => '当月'),
            'CreditTransferLimitDayType' => array(1 => '〇営業日後', 2 => '〇月〇日'),
            'CreditTransferAfterLimitDayType' => array(0 => '当月', 1 => '翌月'),
        );
        $this->view->assign('master_map', $masters);
    }

    /**
     * 口座振替期間設定　表示
     */
    public function indexAction()
    {
        $prm_get = $this->params()->fromRoute();
        $id  = (isset($prm_get['pk'])) ? $prm_get['pk'] : '1';
        $mdlct = new TableCreditTransfer($this->app->dbAdapter);
        $tag = BaseHtmlUtils::SelectTag(
            'pk',
            $mdlct->getTemplatesArray(),
            $id,
            ' onChange="javascript:navi();"'
        );

        $data = $mdlct->find($id)->current();

        $this->view->assign("tag", $tag);
        $this->view->assign("data", $data);

        return $this->view;
    }

    /**
     * 口座振替期間設定　保存
     */
    public function saveAction(){
        $mdlct = new TableCreditTransfer($this->app->dbAdapter);

        //POSTデータ取得
        $data = $this->params()->fromPost();

        $errors = $this->validate($data);
        if(!empty($errors)) {
            $id  = (isset($data['CreditTransferId'])) ? $data['CreditTransferId'] : '1';
            $tag = BaseHtmlUtils::SelectTag(
                'pk',
                $mdlct->getTemplatesArray(),
                $id,
                ' onChange="javascript:navi();"'
            );

            $this->view->assign("data", $data);
            $this->view->assign("tag", $tag);
            $this->view->assign('error', $errors);     // 2015/11/23 Y.Suzuki 会計対応 Mod
            $this->setTemplate('index');
            return $this->view;
        }

        // ユーザーIDの取得
        $mdluser = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $data['UpdateId'] = $userId;
        if ($data['CreditTransferLimitDayType'] == 1) {
            $data['CreditTransferAfterLimitDayType'] = null;
        }
        $mdlct->saveUpdate($data, $data['CreditTransferId']);

        return $this->_redirect("credittransfer/index/pk/" . $data['CreditTransferId']);
    }

    private function validate($data = array()) {

        $errors = array();

        // CreditTransferSpanFromMonth: 口座振替対象期間－From種別
        $key = 'CreditTransferSpanFromMonth';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座振替対象期間－From種別'は必須です");
        }
        if (!isset($errors[$key]) && ($data[$key] != -2) && ($data[$key] != -1) && ($data[$key] != 0)) {
            $errors[$key] = array("'口座振替対象期間－From種別'はプルダウンから選択してください");
        }

        // CreditTransferSpanFromDay: 口座振替対象期間－From日付
        $key = 'CreditTransferSpanFromDay';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座振替対象期間－From日付'は必須です");
        }
        if (!isset($errors[$key]) && !is_numeric($data[$key])) {
            $errors[$key] = array("'口座振替対象期間－From日付'は数値で入力してください");
        }
        if (!isset($errors[$key]) && (($data[$key] > 31) || ($data[$key] < 1))) {
            $errors[$key] = array("'口座振替対象期間－From日付'は1から31迄の数値で入力してください");
        }

        // CreditTransferSpanToTypeMonth: 口座振替対象期間－To種別
        $key = 'CreditTransferSpanToTypeMonth';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座振替対象期間－To種別'は必須です");
        }
        if (!isset($errors[$key]) && ($data[$key] != -1) && ($data[$key] != 0)) {
            $errors[$key] = array("'口座振替対象期間－To種別'はプルダウンから選択してください");
        }

        // CreditTransferSpanToDay: 口座振替対象期間－To日付
        $key = 'CreditTransferSpanToDay';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座振替対象期間－To日付'は必須です");
        }
        if (!isset($errors[$key]) && !is_numeric($data[$key])) {
            $errors[$key] = array("'口座振替対象期間－To日付'は数値で入力してください");
        }
        if (!isset($errors[$key]) && (($data[$key] > 31) || ($data[$key] < 1))) {
            $errors[$key] = array("'口座振替対象期間－To日付'は1から31迄の数値で入力してください");
        }
        if ((!isset($errors[$key]) && $data['CreditTransferSpanFromMonth'] > $data['CreditTransferSpanToTypeMonth'])) {
            $errors[$key] = array("'口座振替対象期間－To日付'は口座振替対象期間－From日付より未来の値で入力してください");
        }
        if (!isset($errors[$key]) && ($data['CreditTransferSpanFromMonth'] == $data['CreditTransferSpanToTypeMonth'])) {
            if ($data['CreditTransferSpanFromDay'] >= $data[$key]) {
                $errors[$key] = array("'口座振替対象期間－To日付'は口座振替対象期間－From日付より未来の値で入力してください");
            }
        }

        // CreditTransfeLimitDayType: 口座振替支払期限条件種別
        $key = 'CreditTransferLimitDayType';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座振替支払期限条件種別'は必須です");
        }
        if (!isset($errors[$key]) && ($data[$key] != 1) && ($data[$key] != 2)) {
            $errors[$key] = array("'口座振替支払期限条件種別'はラジオボタンから選択してください");
        }

        // CreditTransfeDay: 口座振替日
        $key = 'CreditTransferDay';
        if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
            $errors[$key] = array("'口座振替日'は必須です");
        }
        if (!isset($errors[$key]) && !is_numeric($data[$key])) {
            $errors[$key] = array("'口座振替日'は数値で入力してください");
        }
        if (!isset($errors[$key]) && (($data[$key] > 31) || ($data[$key] < 1))) {
            $errors[$key] = array("'口座振替日'は1から31迄の数値で入力してください");
        }
        if ($data['CreditTransferSpanToTypeMonth'] == 0) {
            if ($data['CreditTransferSpanToDay'] >= $data[$key]) {
                $errors[$key] = array("'口座振替日'は口座振替対象期間－To日付より未来の値で入力してください");
            }
        }

        if ($data['CreditTransferLimitDayType'] == 1) {
            // CreditTransfeAfterLimitDay: 口座振替支払期限日
            $key = 'CreditTransferAfterLimitDay';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'口座振替支払期限日'は必須です");
            }
            if (!isset($errors[$key]) && !is_numeric($data[$key])) {
                $errors[$key] = array("'口座振替支払期限日'は数値で入力してください");
            }
            if (!isset($errors[$key]) && ($data[$key] < 0)) {
                $errors[$key] = array("'口座振替支払期限日'は0以上の数値で入力してください");
            }
        }

        if ($data['CreditTransferLimitDayType'] == 2) {
            // CreditTransfeAfterLimitDayType: 口座振替支払期限種別
            $key = 'CreditTransferAfterLimitDayType';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'口座振替支払期限種別'は必須です");
            }
            if (!isset($errors[$key]) && ($data[$key] != 0) && ($data[$key] != 1)) {
                $errors[$key] = array("'口座振替支払期限種別'はプルダウンから選択してください");
            }

            // CreditTransfeAfterLimitDay: 口座振替支払期限日
            $key = 'CreditTransferAfterLimitDay';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'口座振替支払期限日'は必須です");
            }
            if (!isset($errors[$key]) && !is_numeric($data[$key])) {
                $errors[$key] = array("'口座振替支払期限日'は数値で入力してください");
            }
            if (!isset($errors[$key]) && (($data[$key] > 31) || ($data[$key] < 1))) {
                $errors[$key] = array("'口座振替支払期限日'は1から31迄の数値で入力してください");
            }
            if ($data['CreditTransferAfterLimitDayType'] == 0) {
                if ($data['CreditTransferDay'] > $data[$key]) {
                    $errors[$key] = array("'口座振替支払期限日'は口座振替日以上の値で入力してください");
                }
            }
            if ($data['CreditTransferAfterLimitDayType'] == 0) {
                if ($data['CreditTransferAfterLimitDay'] >= $data[$key]) {
                    $errors[$key] = array("'口座振替支払期限日'は口座振替日より未来の値で入力してください");
                }
            }
        }

        return $errors;
    }

    public function alertlistAction(){
        $sql = <<<EOQ
SELECT	
	cta.Seq,
	o.OrderSeq,
	o.OrderId,
	e.EnterpriseId,
	e.EnterpriseNameKj,
	c.EntCustId,
	c.NameKj,
	c2.NameKj AS '比較対象',
	'顧客名が誤っている' AS 'アラート内容',
	max( c.OrderSeq ),
	o2.OrderId AS '補足情報',
    o2.OrderSeq AS '補足情報seq' ,
	cta.EntCustSeq 
FROM	
	T_CreditTransferAlert cta
	INNER JOIN T_Order o ON cta.OrderSeq = o.OrderSeq
	INNER JOIN T_Enterprise e ON cta.EnterpriseId = e.EnterpriseId
	INNER JOIN T_Customer c ON cta.EntCustSeq = c.EntCustSeq
	INNER JOIN T_Customer c2 ON o.OrderSeq = c2.OrderSeq
	INNER JOIN T_Order o2 ON c.OrderSeq = o2.OrderSeq 
WHERE	
	cta.ValidFlg = 1 
GROUP BY	
	cta.Seq,
	o.OrderSeq,
	e.EnterpriseId
EOQ;
        //$sql = " SELECT DISTINCT cta.Seq,o.OrderSeq,o.OrderId,e.EnterpriseNameKj,cta.EntCustSeq,c.EntCustId FROM T_CreditTransferAlert cta INNER JOIN T_Order o ON cta.OrderSeq=o.OrderSeq INNER JOIN T_Enterprise e ON cta.EnterpriseId=e.EnterpriseId INNER JOIN T_Customer c ON cta.EntCustSeq=c.EntCustSeq AND c.EntCustId <> '' WHERE cta.ValidFlg=1 ";
        $datas = $this->app->dbAdapter->query($sql)->execute();
        $seq = $_GET['seq'];
        if(!is_null($seq)){
          $sql = " UPDATE T_CreditTransferAlert SET ValidFlg= 0, UpdateDate=:UpdateDate WHERE Seq=:Seq ";
          $data = array(
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':Seq' => $seq,
          );
          $this->app->dbAdapter->query($sql)->execute($data);
          $this->setPageTitle("後払い.com - 口座振替アラート一覧");
          $this->view->assign("datas", $datas);
          return $this->_redirect("credittransfer/alertlist");
        }

        $csv = $_GET['csv'];
        if(!is_null($csv)){
            $date = date('Y-m-d');
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=口座振替アラート一覧_{$date}.csv");

            $head = ['注文ID', '事業者ID', '加盟店名', '加盟店顧客番号', '顧客名', '比較対象', 'アラート内容', '補足情報'];
            mb_convert_variables('SJIS','UTF-8',$head); 

            $stream = fopen('php://output', 'w');

            fputcsv($stream, $head);

            foreach ($datas as $data){
              $list = array(
              $data['OrderId'],
              $data['EnterpriseId'],
              $data['EnterpriseNameKj'],
              $data['EntCustId'],
              $data['比較対象'],
              $data['NameKj'],
              "顧客名が誤っている",
              $data['補足情報'],
//              $data['補足情報seq'],
              );
            mb_convert_variables('SJIS','UTF-8',$list); 

            fputcsv($stream, $list);
            }
		    exit();
        }

        $this->setPageTitle("後払い.com - 口座振替アラート一覧");
        $this->view->assign("datas", $datas);
        return $this->view;
    }

    public function mergeAction(){
        try {
            $prm_get = $this->params()->fromRoute();
            $mdlec = new TableEnterpriseCustomer( $this->app->dbAdapter );

            // ユーザーIDの取得
            $obj = new TableUser( $this->app->dbAdapter );
            $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            $seq = $prm_get['seq'];
            $sql = " SELECT * FROM T_CreditTransferAlert WHERE ValidFlg=1 AND Seq=:Seq ";
            $ctaRow = $this->app->dbAdapter->query($sql)->execute(array(':Seq' => $seq))->current();

            $sql = " SELECT * FROM T_Customer WHERE OrderSeq = :OrderSeq ";
            $custRow = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $ctaRow['OrderSeq']))->current();

            $sql = " UPDATE T_Customer SET EntCustSeq=:EntCustSeq, UpdateDate=:UpdateDate, UpdateId=:UpdateId WHERE OrderSeq=:OrderSeq ";
            $data = array(
                ':EntCustSeq' => $ctaRow['EntCustSeq'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
                ':OrderSeq' => $ctaRow['OrderSeq'],
            );
            $this->app->dbAdapter->query($sql)->execute($data);

            $sql = " UPDATE T_EnterpriseCustomer SET NameKj=:NameKj, NameKn=:NameKn, RegNameKj=:RegNameKj, SearchNameKj=:SearchNameKj, SearchNameKn=:SearchNameKn, PostalCode=:PostalCode, PrefectureCode=:PrefectureCode, PrefectureName=:PrefectureName, City=:City, Town=:Town, Building=:Building, UnitingAddress=:UnitingAddress, Phone=:Phone, MailAddress=:MailAddress, RegUnitingAddress=:RegUnitingAddress, RegPhone=:RegPhone, SearchPhone=:SearchPhone, SearchUnitingAddress=:SearchUnitingAddress, UpdateDate=:UpdateDate, UpdateId=:UpdateId WHERE EntCustSeq=:EntCustSeq ";
            $data = array(
                ':NameKj' => $custRow['NameKj'],
                ':NameKn' => $custRow['NameKn'],
                ':RegNameKj' => $custRow['RegNameKj'],
                ':SearchNameKj' => $custRow['SearchNameKj'],
                ':SearchNameKn' => $custRow['SearchNameKn'],
                ':PostalCode' => $custRow['PostalCode'],
                ':PrefectureCode' => $custRow['PrefectureCode'],
                ':PrefectureName' => $custRow['PrefectureName'],
                ':City' => $custRow['City'],
                ':Town' => $custRow['Town'],
                ':Building' => $custRow['Building'],
                ':UnitingAddress' => $custRow['UnitingAddress'],
                ':Phone' => $custRow['Phone'],
                ':MailAddress' => $custRow['MailAddress'],
                ':RegUnitingAddress' => $custRow['RegUnitingAddress'],
                ':RegPhone' => $custRow['RegPhone'],
                ':SearchPhone' => $custRow['SearchPhone'],
                ':SearchUnitingAddress' => $custRow['SearchUnitingAddress'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
                ':EntCustSeq' => $ctaRow['EntCustSeq'],
            );
//            $mdlec->saveUpdate($data, $ctaRow['EntCustSeq']);
            $this->app->dbAdapter->query($sql)->execute($data);

            $sql = " UPDATE T_Order SET LetterClaimStopFlg=0, MailClaimStopFlg=0, UpdateDate=:UpdateDate, UpdateId=:UpdateId WHERE OrderSeq=:OrderSeq ";
            $data = array(
                ':OrderSeq' => $ctaRow['OrderSeq'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId
            );
            $this->app->dbAdapter->query($sql)->execute($data);

            $sql = " UPDATE T_CreditTransferAlert SET ValidFlg=0, UpdateDate=:UpdateDate, UpdateId=:UpdateId WHERE Seq=:Seq ";
            $data = array(
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $userId,
                ':Seq' => $seq
            );
            $this->app->dbAdapter->query($sql)->execute($data);

        } catch(Exception $e) {
            ;
        }
        return $this->_redirect("credittransfer/alertlist");
    }
}

