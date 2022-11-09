<?php
namespace cbadmin\Controller;

use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Base\BaseHtmlUtils;
use Coral\Base\BaseGeneralUtils;
use cbadmin\Application;
use models\Table\TableMailTemplate;
use models\Table\TableOem;
use models\Table\TableCode;

class GpController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	const SES_UPDATE = 'generalpurpose_notice';
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

        $this->addStyleSheet('../css/default02.css')->addJavaScript( '../js/prototype.js' );

        $this->setPageTitle("後払い.com - 各種マスター管理");
	}

// Del By Takemasa(NDC) 20141224 Stt マジックメソッド廃止
// 	/**
// 	 * 未定義のアクションがコールされた
// 	 */
// 	public function __call($method, $args)
// 	{
// 		// 無条件にlistへinvoke
// 		//$this->_forward('pointform');
// 	}
// Del By Takemasa(NDC) 20141224 End マジックメソッド廃止

	/**
	 * お知らせ設定フォームの表示
	 */
	public function noticeAction()
	{
        if (isset($_SESSION[self::SES_UPDATE])) {
            unset($_SESSION[self::SES_UPDATE]);
            $this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        $mdl = new \models\Table\TableCode($this->app->dbAdapter);
        for ($i=0; $i<3; $i++) {
            $code[$i] = $mdl->getMasterDescription(5, $i);
        }

        $this->view->assign('notice', $code);
        return $this->view;
	}

	/**
	 * お知らせ設定更新
	 */
	public function upAction()
	{
        $prm_post = $this->params()->fromPost();
        $keycode = (int)$prm_post['code'];
        $notice  = (isset($prm_post['notice' . $keycode])) ? $prm_post['notice' . $keycode] : '未設定';
        $eData['Note'] = $notice;
        $eData['ValidFlg'] = 1;

        // ユーザーIDの取得
        $mdluser = new \models\Table\TableUser($this->app->dbAdapter);
        $eData['UpdateId'] = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $mdl = new \models\Table\TableCode($this->app->dbAdapter);

        // 更新実行
        $mdl->saveUpdate($eData, 5, $keycode);// Class=5 Code=1 が「お知らせ」のデータ。

        $_SESSION[self::SES_UPDATE] = "updated";
        return $this->_redirect("gp/notice");
	}

	/**
	 * メールテンプレート編集フォーム
	 */
	public function mailtfAction()
	{
	    if (isset($_SESSION[self::SES_UPDATE]))
        {
            unset($_SESSION[self::SES_UPDATE]);
            $this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }
        $riCodes = array();
        $prm_get = $this->params()->fromRoute();
        $class  = (isset($prm_get['cls'])) ? $prm_get['cls'] : '1';
        $mdlmt = new TableMailTemplate($this->app->dbAdapter);
        $clsTag = BaseHtmlUtils::SelectTag(
            'cls',
            $mdlmt->getTemplatesArray(),
            $class,
            ' onChange="javascript:navi();"'
        );

        $oemId  = (isset($prm_get['OemId'])) ? $prm_get['OemId'] : '0';
        $mdloem = new TableOem($this->app->dbAdapter);
        $oemTag = BaseHtmlUtils::SelectTag(
            'OemId',
            $mdloem->getOemIdList(),
            $oemId,
            ' onChange="javascript:navi();"'
        );

        $data = $mdlmt->findMailTemplate($class, $oemId)->current();
        $mode = ($oemId == 0) ? 'cb' : 'oem';

        // コードマスターから使用可能なパラメーターを取得
        $sql = ' SELECT * FROM M_Code WHERE CodeId = :CodeId AND ( Class2 = :Class2 OR Class3 = :Class3 ) ORDER BY KeyCode ';
        $prm = array(
            ':CodeId' => 72,
            ':Class2' => $data['Class'],
            ':Class3' => $data['Class'],
        );

        $riCodeBase = $this->app->dbAdapter->query($sql)->execute($prm);

        foreach ($riCodeBase as $key => $riCode) {
            $temp['KeyContent']  = $riCode['KeyContent'];
            $temp['Class1']  = $riCode['Class1'];
            $riCodes[] = $temp;
        }

        if (is_null($oemId) or $oemId == 0) {
            $sqlServer = ' SELECT * FROM M_Code WHERE CodeId = :CodeId AND ( KeyCode  = :KeyCode1 OR KeyCode  = :KeyCode2 ) ORDER BY KeyCode ';
            $prmServer = array(
                ':CodeId' => 72,
                ':KeyCode1' => 428,
                ':KeyCode2' => 429,
            );
            $riCodeServer = $this->app->dbAdapter->query($sqlServer)->execute($prmServer);
            foreach ($riCodeServer as $key => $riCode) {
                $temp['KeyContent']  = $riCode['KeyContent'];
                $temp['Class1']  = $riCode['Class1'];
                $riCodes[] = $temp;
            }
        }

        $sqlOrder = ' SELECT * FROM M_Code WHERE CodeId = :CodeId AND ( KeyCode  = :KeyCode1 OR KeyCode  = :KeyCode2 ) ORDER BY KeyCode ';
        $prmOrder = array(
            ':CodeId' => 72,
            ':KeyCode1' => 430,
            ':KeyCode2' => 430,
        );
        $orderId = array();
        $riCodeOrder = $this->app->dbAdapter->query($sqlOrder)->execute($prmOrder);
        foreach ($riCodeOrder as $key => $riCode) {
            $orderId['KeyContent']  = $riCode['KeyContent'];
            $orderId['Class1']  = $riCode['Class1'];
        }
        $flagAdd = true;
        foreach ($riCodes as $riCode) {
            if ($riCode['Class1'] == '{OrderId}') {
                $flagAdd = false;
                break;
            }
        }
        if ($flagAdd) {
            $riCodes[] = $orderId;
        }

        $this->view->assign('clsTag', $clsTag);
        $this->view->assign('oemTag', $oemTag);
        $this->view->assign('mode', $mode);
        $this->view->assign('data', $data);
        $this->view->assign('code', $riCodes);

        return $this->view;
	}

	/**
	 * メールテンプレート設定
	 */
	public function mailupAction()
	{
	    // リファラーがなければリダイレクト
        if(!isset($_SERVER['HTTP_REFERER']))
        {
            return $this->_redirect("gp/mailtf");
        }

        $params = $this->params()->fromPost();

        // ユーザーIDの取得
        $mdluser = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $udata['FromTitle'] = $params['FromTitle'];
        $udata['FromTitleMime'] = BaseGeneralUtils::toMailCharMime($params['FromTitle']);
        $udata['FromAddress'] = $params['FromAddress'];
        $udata['Subject'] = $params['Subject'];
        $udata['SubjectMime'] = mb_encode_mimeheader($params['FromTitle'], 'UTF-8');
        $udata['Body'] = $params['Body'];
        $udata['Class'] = $params['cls'];
        $udata['UpdateId'] = $userId;
        $udata['ValidFlg'] = 1;

        if($params['OemId'] == 0) {
            $redirectUrl = '';
        } else {
            $redirectUrl = '/OemId/'.$params['OemId'];
        }

        $mdlmt = new TableMailTemplate($this->app->dbAdapter);

        // IDがなければ新規にレコード作成
        if(is_null($params['Id']) || $params['Id'] == ''){
            $udata['OemId'] = $params['OemId'];
            $udata['RegistId'] = $userId;
            $mdlmt->saveNew($udata);

        // IDがあれば更新
        } else {
            $mdlmt->saveUpdate($udata, $params['Id']);
        }

        $_SESSION[self::SES_UPDATE] = "updated";

        return $this->_redirect("gp/mailtf/cls/" .$params['cls'].$redirectUrl);
	}

	/**
	 * 汎用マスター編集フォーム
	 */
	public function gpmstrfAction()
	{
	    if (isset($_SESSION[self::SES_UPDATE]))
        {
            unset($_SESSION[self::SES_UPDATE]);
            $this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        /**
         * マスタークラス
         * マッピングはデータベース上に保持されていないので、気持ち悪いが
         * ここでマッピングしておく。
         *
         * 18:督促分類
         * 19:キャリア
         * 20:最終回収手段
         */
        $captions = array(0 => 'クラス番号未定義', 18 => '督促分類', 19 => 'キャリア', 20 => '最終回収手段');

        $prm_get = $this->params()->fromRoute();
        $codeId  = (isset($prm_get['cls'])) ? $prm_get['cls'] : 0;
        $mdlcode = new \models\Table\TableCode($this->app->dbAdapter);
        $masterDatas = $mdlcode->getMasterByClassAll($codeId);

        $this->view->assign('list', $masterDatas);
        $this->view->assign('cls', $codeId);
        $this->view->assign('caption', $captions[$codeId]);

        return $this->view;
	}

	/**
	 * 汎用マスター設定
	 */
	public function gpmstruAction()
	{
        $params = $this->params()->fromPost();
        $mdlcode = new \models\Table\TableCode($this->app->dbAdapter);

        // ユーザーIDの取得
        $mdluser = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        // 更新部分
        $i = 0;
        $maxCode = 0;
        while(isset($params['KeyCode' . $i]))
        {
            unset($udata);
            $udata['KeyContent'] = $params['KeyContent' . $i];
            if (array_key_exists("ValidFlg" . $i, $params) && $params["ValidFlg" . $i] == "on")
            {
                $udata['ValidFlg'] = 1;
            }
            else
            {
                $udata['ValidFlg'] = 0;
            }

            $udata['UpdateId'] = $userId;
            $mdlcode->saveUpdate($udata, $params['cls'], $params['KeyCode' . $i]);
            $maxCode = $params['KeyCode' . $i];

            $i++;
        }

        // 新規登録部分
        if ($params['KeyContent'] != '')
        {
            unset($udata);
            $udata['CodeId'] = $params['cls'];
            $udata['KeyCode'] = $maxCode + 1;				// 気持ち悪いロジックだが容認する。
            $udata['KeyContent'] = $params['KeyContent'];
            if (array_key_exists("ValidFlg", $params) && $params["ValidFlg"] == "on")
            {
                $udata['ValidFlg'] = 1;
            }
            else
            {
                $udata['ValidFlg'] = 0;
            }

            $udata['RegistId'] = $userId;
            $udata['UpdateId'] = $userId;
            $mdlcode->saveNew($udata);
        }

        $_SESSION[self::SES_UPDATE] = "updated";
        return $this->_redirect("gp/gpmstrf/cls/" . $params['cls']);
	}

	/**
	 * 備考マスタフォームの表示
	 */
	public function noteAction()
	{
        if (isset($_SESSION[self::SES_UPDATE])) {
            unset($_SESSION[self::SES_UPDATE]);
            $this->view->assign("updated", sprintf('<font color="red"><b>更新しました。　%s</b></font>', date("Y-m-d H:i:s")));
        }

        $mdl = new \models\Table\TableCode($this->app->dbAdapter);
        for ($i=0; $i<3; $i++) {
            $code[$i] = $mdl->getMasterDescription(85, $i);
        }

        $this->view->assign('note', $code);
        return $this->view;
	}

	/**
	 * 備考マスタ設定更新
	 */
	public function noteupAction()
	{
        $prm_post = $this->params()->fromPost();
        $keycode = (int)$prm_post['code'];
        $note  = (isset($prm_post['note' . $keycode])) ? $prm_post['note' . $keycode] : '';
        $eData['Note'] = $note;

        // ユーザーIDの取得
        $mdluser = new \models\Table\TableUser($this->app->dbAdapter);
        $eData['UpdateId'] = $mdluser->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $mdl = new \models\Table\TableCode($this->app->dbAdapter);

        // 更新実行
        $mdl->saveUpdate($eData, 85, $keycode);

        $_SESSION[self::SES_UPDATE] = "updated";
        return $this->_redirect("gp/note");
	}

    /**
     * 定型コメント選択画面
     */
    public function selfixednoteAction()
    {
        $this->addJavaScript( '../js/fixednote.js' );
        $this->setPageTitle("定型コメント選択");

        $params = $this->getParams();

        // グループ項目
        $sql =<<<EOQ
SELECT DISTINCT fnd.Seq
,      fnd.Note
FROM   T_FixedNoteRelate fnr
       INNER JOIN T_FixedNoteDefine fnd ON (fnd.Seq = fnr.HeaderSeq)
WHERE  fnd.Type = 0
AND    fnd.ValidFlg = 1
%s
ORDER BY fnd.ListNumber
EOQ;
        if ($params['usetype'] == 1) {
            $sql = sprintf($sql, " AND fnd.UseType1 = 1 ");
        }
        else if ($params['usetype'] == 2) {
            $sql = sprintf($sql, " AND fnd.UseType2 = 1 ");
        }
        else {
            $sql = sprintf($sql, " AND 1 = 1 ");
        }
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $this->view->assign('hdata', ResultInterfaceToArray($ri));

        // コメント項目
        $sql =<<<EOQ
SELECT fnr.HeaderSeq
,      fnr.DetailSeq
,      fnd.Note
FROM   T_FixedNoteRelate fnr
       INNER JOIN T_FixedNoteDefine fnd ON (fnd.Seq = fnr.DetailSeq)
WHERE  fnr.HeaderSeq IN (SELECT Seq FROM T_FixedNoteDefine WHERE Type = 0 AND ValidFlg = 1 %s)
AND    fnd.ValidFlg = 1
ORDER BY HeaderSeq, fnr.ListNumber
EOQ;
        if ($params['usetype'] == 1) {
            $sql = sprintf($sql, " AND UseType1 = 1 ");
        }
        else if ($params['usetype'] == 2) {
            $sql = sprintf($sql, " AND UseType2 = 1 ");
        }
        else {
            $sql = sprintf($sql, " AND 1 = 1 ");
        }
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $this->view->assign('ddata', ResultInterfaceToArray($ri));

        // その他のアサイン
        $this->view->assign('usetype', $params['usetype']);
        if (isset($params['noteno'])) {
            $this->view->assign('noteno', $params['noteno']);
        }

        return $this->view;
    }
}

