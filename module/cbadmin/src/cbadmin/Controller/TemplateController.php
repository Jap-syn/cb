<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableOem;
use models\Table\TableTemplateField;
use models\Table\TableTemplateHeader;
use models\Table\TableForm;
use models\Table\TableUser;

class TemplateController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Adapter
     *
     * @var Adapter
     */
    private $db;

    /**
     * クラス固有の初期化処理
     */
    protected function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign( 'userInfo', $this->app->authManagerAdmin->getUserInfo() );
        $this->db = $this->app->dbAdapter;

        $this
            ->addStyleSheet( '../css/default02.css' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/json.js' );

        $this->setPageTitle( "後払い.com - テンプレート管理" );
    }

    /**
     * indexアクション
     *
     */
    public function indexAction()
    {
        // 一覧に表示する情報を取得
$sql = <<<EOQ
SELECT th.*
      , t.ClassName
      , t.NameKj
FROM M_TemplateHeader th
     LEFT OUTER JOIN (
						SELECT 0 AS TemplateClass , 'CB' AS ClassName , 0 AS Seq, 0 AS TemplatePattern , 'キャッチボール' AS NameKj
						UNION ALL
						SELECT 1 AS TemplateClass , 'OEM' AS ClassName , OemId AS Seq , 0 AS TemplatePattern , OemNameKj AS NameKj FROM T_Oem
						UNION ALL
						SELECT 2 AS TemplateClass , '加盟店' AS ClassName , EnterpriseId AS Seq, 0 AS TemplatePattern , EnterpriseNameKj AS NameKj FROM T_Enterprise
						UNION ALL
						SELECT 2 AS TemplateClass , '加盟店(サイト)' AS ClassName , e.EnterpriseId AS Seq, SiteId AS TemplatePattern , concat(EnterpriseNameKj, '(',SiteNameKj,')') AS NameKj FROM T_Enterprise e, T_Site s WHERE e.EnterpriseId = s.EnterpriseId
                        ) t
			      ON t.TemplateClass = th.TemplateClass
                 AND t.Seq = th.Seq
                 AND t.TemplatePattern = th.TemplatePattern
WHERE th.ValidFlg = 1
ORDER BY th.TemplateId, th.TemplateClass, th.Seq, th.TemplatePattern
EOQ;

        $stm = $this->db->query($sql);

        $list = ResultInterfaceToArray( $stm->execute(null) );

        // 加盟店の TableTemplateHeader は除く
        // 加盟店単位の設定もCBから可能にする(20150722_suzuki_h)
        $templateHeaderList = array();
        foreach( $list as $templateHeader ) {
                $templateHeaderList[] = $templateHeader;
        }

        //TableTemplateHeaderに FormName 追加
        $templateForm = new TableForm( $this->db );
        $formList = $templateForm->getFormIdList();

        $templateFormList = array();
        foreach( $templateHeaderList as $templateHeader ) {
            foreach( $formList as $form ) {
                if( $templateHeader['FormId'] == $form['FormId'] ) {
                    $templateHeader['FormName'] = $form['FormName'];
                }
            }
            $templateFormList[] = $templateHeader;
        }

        // OEM一覧
        $oem = new TableOem( $this->db );
        $ri = $oem->getAllValidOem();
        foreach($ri as $value) {
            $oemIdList[$value['OemId']] = $value['OemNameKj'];
        }
        ksort($oemIdList);

        $this->view->assign( 'templateList', $templateFormList );
        $this->view->assign( 'oemList', $oemIdList );

        return $this->view;
    }

    /**
     * editアクション
     *
     */
    public function editAction()
    {
        $this
            ->addStyleSheet( '../css/cbadmin/template/edit/default.css' )
            ->addJavaScript( '../js/cbadmin/template/edit/columneditor.js' );

        $params = array_merge( $this->params()->fromRoute(), $this->params()->fromPost() );
        $mode = $params['mode'];

        // mode が add の場合
        if( $mode == 'add' ) {
            try
            {
                // トランザクション開始
                $this->db->getDriver()->getConnection()->beginTransaction();

                $copyTemplateSeq = $params['copytemplateseq'];
                $oemid = $params['oemid'];

                // コピー元の TemplateHeader を取り出す
                $templateHeader = new TableTemplateHeader( $this->db );
                $copyTemplateHeader = $templateHeader->find( $copyTemplateSeq )->current();

                // ユーザーIDの取得
                $user = new TableUser( $this->db );
                $userId = $user->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

                // 新規登録する TemplateHeader の TemplateClass = 1、Seq = OemId、TemplateClass = 0 にする
                $newTemplateHeader = $copyTemplateHeader;
                $newTemplateHeader['TemplateClass'] = 1;    // OEM
                $newTemplateHeader['Seq'] = $oemid;         // OEMId
                $newTemplateHeader['RegistId'] = $userId;
                $newTemplateHeader['UpdateId'] = $userId;

                $templateSeq = $templateHeader->saveNew( $newTemplateHeader );

                // コピー元の TemlateField を取り出す
                $templateField = new TableTemplateField( $this->db );
                $copyTemplateFieldList = ResultInterfaceToArray( $templateField->get( $copyTemplateSeq ) );

                // 新規登録する TemplateField の TemplateSeq を新規登録した TemplateHeader の TemplateSeq にする
                foreach( $copyTemplateFieldList as $copyTemplateField ) {
                    $newTemplateField = $copyTemplateField;
                    $newTemplateField['TemplateSeq'] = $templateSeq;
                    $newTemplateField['RegistId'] = $userId;
                    $newTemplateField['UpdateId'] = $userId;
                    $newTemplateFieldList[] = $newTemplateField;
                }

                foreach( $newTemplateFieldList as $newTemplateField ) {
                    $templateField->saveNew( $newTemplateField );
                }
                $this->db->getDriver()->getConnection()->commit();
            }
            catch(\Exception $e)
            {
                $this->db->getDriver()->getConnection()->rollBack();
                throw $e;
            }

            // F5対策のためリダイレクト
            return $this->_redirect('template/edit/mode/edit/templateseq/'.$templateSeq);
        }
        // mode が edit の 場合
        elseif( $mode == 'edit' ) {
            $templateSeq = $params['templateseq'];

            $templateHeader = new TableTemplateHeader( $this->db );

            // ListNumber順にTemplateFieldを取り出す
            $templateField = new TableTemplateField( $this->db );
            $templateFieldList = ResultInterfaceToArray( $templateField->get( $templateSeq ) );

            $validList = array();
            $invalidList = array();

            foreach( $templateFieldList as $templateField ) {
                if( $templateField['ValidFlg'] == 1 ) {
                    $validList[] = $templateField;
                }
                elseif( $templateField['ValidFlg'] == 0 ) {
                    $invalidList[] = $templateField;
                }
            }

            $this->view->assign( 'validList', $validList );
            $this->view->assign( 'invalidList', $invalidList );
            $this->view->assign( 'templateSeq', $templateSeq );
            $this->view->assign( 'templateRow' , $templateHeader->find($templateSeq)->current());
        }
        elseif( $mode == 'del' ) {
            $templateSeq = $params['templateseq'];

            try
            {
                $templateHeader = new TableTemplateHeader( $this->db );
                $templateField = new TableTemplateField( $this->db );
                $this->db->getDriver()->getConnection()->beginTransaction();

                // TemplateHeader と TemplateField を削除
                $templateHeader->delete( $templateSeq );
                $templateField->delete( $templateSeq );

                $this->db->getDriver()->getConnection()->commit();
            }
            catch(\Exception $e)
            {
                $this->db->getDriver()->getConnection()->rollBack();
                throw $e;
            }

            $this->view->assign( 'contentsTitle', 'テンプレート削除完了' );
            $this->setTemplate( 'complete' );
        }

        return $this->view;
    }

    /**
     * confirmアクション
     *
     */
    public function confirmAction()
    {
        $params = $this->params()->fromRoute();

        $templateHeader = new TableTemplateHeader( $this->db );
        $list = $templateHeader->find($params['templateseq'])->current();

        $this->view->assign( 'templateseq', $params['templateseq'] );
        $this->view->assign( 'templateid', $list['TemplateId'] );
        return $this->view;
    }

    /**
     * saveアクション
     *
     */
    public function saveAction()
    {
        $params = $this->params()->fromPost();

        $templateSeq = $params['templateseq'];
        $validList = explode( ',', $params['validlistData']);
        $invalidList = explode( ',', $params['invalidlistData']);

        // ListNumber順にTemplateFieldを取り出す
        $templateField = new TableTemplateField( $this->db );
        $oldTemplateFieldList = ResultInterfaceToArray( $templateField->get( $templateSeq ) );

        // ユーザーIDの取得
        $user = new TableUser( $this->db );
        $userId = $user->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        $newTemplateField = array();
        $newTemplateFieldList = array();

        // validListのアイテム詰め直し
        for( $i = 1; $i < count( $validList ); $i++ ) {
            foreach( $oldTemplateFieldList as $oldTemplateField ) {
                if( $validList[$i] == $oldTemplateField['PhysicalName'] ) {
                    $newTemplateField = $oldTemplateField;
                    $newTemplateField['ListNumber'] = $i;
                    $newTemplateField['UpdateId'] = $userId;
                    $newTemplateField['ValidFlg'] = 1;
                    $newTemplateFieldList[] = $newTemplateField;
                }
            }
        }

        // invalidListのアイテム詰め直し
        $validItems = count( $validList ) - 1;
        for( $i = 1; $i < count( $invalidList); $i++ ) {
            foreach( $oldTemplateFieldList as $oldTemplateField ) {
                if( $invalidList[$i] == $oldTemplateField['PhysicalName'] ) {
                    $newTemplateField = $oldTemplateField;
                    $newTemplateField['ListNumber'] = $i + $validItems;
                    $newTemplateField['UpdateId'] = $userId;
                    $newTemplateField['ValidFlg'] = 0;
                    $newTemplateFieldList[] = $newTemplateField;
                }
            }
        }

        try
        {
            // トランザクション開始
            $this->db->getDriver()->getConnection()->beginTransaction();

            foreach( $newTemplateFieldList as $newTemplateField ) {
                $templateField->saveUpdate( $newTemplateField, $templateSeq, $newTemplateField['ListNumber'] );
            }
            $this->db->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->db->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        $this->view->assign( 'contentsTitle', 'テンプレート登録完了' );
        $this->setTemplate( 'complete' );

        return $this->view;
    }
}
