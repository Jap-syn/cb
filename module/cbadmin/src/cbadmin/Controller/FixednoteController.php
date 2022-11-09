<?php
namespace cbadmin\Controller;

use Zend\Json\Json;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Table\TableFixedNoteDefine;
use models\Table\TableFixedNoteRelate;

/**
 * 定型備考コントローラ
 */
class FixednoteController extends CoralControllerAction
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
        $this->addJavaScript('../js/json.js');
        $this->addJavaScript('../js/corelib.js');

        $this->setPageTitle("後払い.com - 定型備考管理");
    }

    /**
     * 定型備考リスト表示
     */
    public function listAction()
    {
        $params = $this->getParams();

        $sql =<<<EOQ
SELECT fnd1.Note AS NoteHeader
,      fnd2.Note AS NoteDetail
,      fnd1.Seq AS HeaderSeq
,      fnr.DetailSeq
,      fnd1.ListNumber AS ListNumberHeader
,      fnr.ListNumber
,      IFNULL(fnd1.UseType1, 0) AS UseType1
,      IFNULL(fnd1.UseType2, 0) AS UseType2
,      (IFNULL(fnd1.ValidFlg, 0) AND IFNULL(fnd2.ValidFlg, 0)) AS ValidFlg
FROM   T_FixedNoteDefine fnd1
       LEFT OUTER JOIN T_FixedNoteRelate fnr ON (fnr.HeaderSeq = fnd1.Seq)
       LEFT OUTER JOIN T_FixedNoteDefine fnd2 ON (fnd2.Seq = fnr.DetailSeq AND fnd2.Type = 1)
WHERE  fnd1.Type = 0
ORDER BY ListNumberHeader, ListNumber
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(null);

        $this->view->assign('data', ResultInterfaceToArray($ri));
        return $this->view;
    }

    /**
     * 定型備考定義編集画面表示
     */
    public function editAction()
    {
        $params = $this->getParams();

        // グループ項目
        $sql =<<<EOQ
SELECT Seq
,      Note
,      ListNumber
,      UseType1
,      UseType2
,      ValidFlg
,      CASE WHEN (SELECT COUNT(1) FROM T_FixedNoteRelate WHERE HeaderSeq = Seq) > 0 THEN '○'
            ELSE '（なし）'
       END AS IsRelated
FROM   T_FixedNoteDefine
WHERE  Type = 0
ORDER BY Seq
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $this->view->assign('dataHeader', ResultInterfaceToArray($ri));

        // コメント項目
        $sql =<<<EOQ
SELECT Seq
,      Note
,      ValidFlg
,      CASE WHEN (SELECT COUNT(1) FROM T_FixedNoteRelate WHERE DetailSeq = Seq) > 0 THEN '○'
            ELSE '（なし）'
       END AS IsRelated
FROM   T_FixedNoteDefine
WHERE  Type = 1
ORDER BY Seq
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $this->view->assign('dataDetail', ResultInterfaceToArray($ri));

        // 表示順
        $listNumber = array();
        for ($i=1; $i<100; $i++) {
            $listNumber[] = array('Key' => $i, 'Value' => $i);
        }
        $this->view->assign('listNumberList', $listNumber);

        return $this->view;
    }

    /**
     * 定型備考定義更新
     */
    public function editdoneAction()
    {
        $params = $this->getParams();

        // ユーザーIDの取得
        $obj = new \models\Table\TableUser($this->app->dbAdapter);
        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

        $mdl = new TableFixedNoteDefine($this->app->dbAdapter);

        // 既存分
        // (データタイプ(0:ヘッダー項目))
        $i = 0;
        while (isset($params["HSeq" . $i])) {
            $savedata = array(
                    'Type'      => 0,
                    'Note'      => $params['HNote' . $i],
                    'ListNumber'=> $params['HListNumber' . $i],
                    'UseType1'  => (isset($params['HUseType1' . $i])) ? 1 : 0,
                    'UseType2'  => (isset($params['HUseType2' . $i])) ? 1 : 0,
                    'RegistId'  => $userId,
                    'UpdateId'  => $userId,
                    'ValidFlg'  => (isset($params['HValidFlg' . $i])) ? 1 : 0,
            );
            $mdl->saveUpdate($savedata, $params["HSeq" . $i]);
            $i++;
        }
        // (データタイプ(1:明細項目))
        $i = 0;
        while (isset($params["DSeq" . $i])) {
            $savedata = array(
                    'Type'      => 1,
                    'Note'      => $params['DNote' . $i],
                    'RegistId'  => $userId,
                    'UpdateId'  => $userId,
                    'ValidFlg'  => (isset($params['DValidFlg' . $i])) ? 1 : 0,
            );
            $mdl->saveUpdate($savedata, $params["DSeq" . $i]);
            $i++;
        }

        // 新規分(Noteが指定されていれば新規登録と見なす)
        // (データタイプ(0:ヘッダー項目))
        if (trim($params['HNote']) != '') {
            $savedata = array(
                    'Type'      => 0,
                    'Note'      => $params['HNote'],
                    'ListNumber'=> $params['HListNumber'],
                    'UseType1'  => (isset($params['HUseType1'])) ? 1 : 0,
                    'UseType2'  => (isset($params['HUseType2'])) ? 1 : 0,
                    'RegistId'  => $userId,
                    'UpdateId'  => $userId,
                    'ValidFlg'  => (isset($params['HValidFlg'])) ? 1 : 0,
            );
            $mdl->saveNew($savedata);
        }
        // (データタイプ(1:明細項目))
        if (trim($params['DNote']) != '') {
            $savedata = array(
                    'Type'      => 1,
                    'Note'      => $params['DNote'],
                    'RegistId'  => $userId,
                    'UpdateId'  => $userId,
                    'ValidFlg'  => (isset($params['DValidFlg'])) ? 1 : 0,
            );
            $mdl->saveNew($savedata);
        }

        // 保存が成功したので編集画面へリダイレクト
        $_SESSION['SUCCESS_EDITDONE'] = true;
        return $this->_redirect('fixednote/edit');
    }

    /**
     * 定型備考関連付け画面表示
     */
    public function relateAction()
    {
        $params = $this->getParams();

        $this->view->assign('seq', $params['seq']);
        $row = $this->app->dbAdapter->query(" SELECT Note FROM T_FixedNoteDefine WHERE Type = 0 AND Seq = :Seq "
                )->execute(array(':Seq' => $params['seq']))->current();
        $this->view->assign('seqnote', ($row) ? $row['Note'] : '');

        return $this->view;
    }

    /**
     * 定型備考関連付け更新
     */
    public function relatedoneAction()
    {
        $params = $this->getParams();

        // SEQが不正な場合は直ちに戻る
        $seq = isset($params['seq']) ? $params['seq'] : -1;
        if ($seq == -1) {
            return $this->_redirect(sprintf('fixednote/relate/seq/%s', $seq));
        }

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 送信内容を展開
            $data = Json::decode( (isset($params['data']) ? $params['data'] : '[]') , Json::TYPE_ARRAY);

            if (!is_array($data)) {
                throw new \Exception('不正なデータが送信されました');
            }

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $mdl = new TableFixedNoteRelate($this->app->dbAdapter);

            // 現在のデータを全削除
            $mdl->deleteByHeaderSeq($seq);

            // 登録されたデータをすべて挿入
            foreach($data as $index => $value) {
                $mdl->saveNew(array('HeaderSeq' => $seq, 'DetailSeq' => $value, 'ListNumber' => $index + 1, 'RegistId' => $userId, 'UpdateId' => $userId));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();

            // エラーが発生したらエラーメッセージ付きで編集画面へ
            $this->view->assign('seq', $seq);
            $row = $this->app->dbAdapter->query(" SELECT Note FROM T_FixedNoteDefine WHERE Type = 0 AND Seq = :Seq "
                    )->execute(array(':Seq' => $seq))->current();
            $this->view->assign('seqnote', ($row) ? $row['Note'] : '');
            $this->view->assign('error', $err->getMessage());

            $this->setTemplate('relate');
            return $this->view;
        }

        // 保存が成功したので編集画面へリダイレクト
        return $this->_redirect(sprintf('fixednote/relate/seq/%s', $seq));
    }

    /**
     * masterAction
     * 定型備考定義データをJSON形式で返すAjax向けアクション
     */
    public function masterAction()
    {
        $this->prepare();

        // データタイプ(1:明細項目)のデータを全取得
        $sql = " SELECT Seq, Note, ValidFlg FROM T_FixedNoteDefine WHERE Type = 1 ORDER BY Seq ";
        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        echo Json::encode( ResultInterfaceToArray($ri) );
        return $this->getResponse();
    }

    /**
     * currentAction
     * 指定グループSEQの現在の関連付けデータをJSON形式で返すAjax向けアクション
     */
    public function currentAction()
    {
        $params = $this->getParams();

        $this->prepare();

        $sql =<<<EOQ
SELECT fnr.DetailSeq AS Seq
,      fnd.Note
,      fnd.ValidFlg
FROM   T_FixedNoteRelate fnr
       INNER JOIN T_FixedNoteDefine fnd ON (fnd.Seq = fnr.DetailSeq)
WHERE  fnr.HeaderSeq = :HeaderSeq
ORDER BY fnr.ListNumber
EOQ;
        $ri = $this->app->dbAdapter->query($sql)->execute(array(':HeaderSeq' => $params['seq']));
        echo Json::encode( ResultInterfaceToArray($ri) );
        return $this->getResponse();
    }

    /**
     * ビューレンダラ―を使用しないレスポンスの準備を実行する(applicaiont/json)
     *
     * @access protected
     */
    protected function prepare()
    {
        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'application/json; charset=utf-8' );
    }
}

