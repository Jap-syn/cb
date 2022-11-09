<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\CoralValidate;
use models\Logic\LogicTemplate;
use Zend\Db\Adapter\Adapter;
use Zend\Http\Header\Vary;

class CustomerAdrsListController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    const SESS_SAVEAFTER_CID = "SESS_UNIFI_SAVEAFTER_CID";
    const SESS_SAVEAFTER_SKEY = "SESS_UNIFI_SAVEAFTER_SKEY";

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * 管理者権限フラグ
     * @var boolean
     */
    private $isAdmin;

    /**
     * Controllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $userInfo = $this->app->authManagerAdmin->getUserInfo();

        // ログイン中アカウントの権限を確認して、利用可能なロールで無い場合はエラーにする
        $this->isAdmin = $userInfo->RoleCode > 1;

        $this->view->assign('userInfo', $userInfo);

        $this
            ->addStyleSheet('../css/default02.css')
            ->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 顧客統合管理");

        // 統合指示画面用のセッションを初期化する
        // 名寄せリストID
        unset($_SESSION[self::SESS_SAVEAFTER_CID]);
        // 検索条件
        unset($_SESSION[self::SESS_SAVEAFTER_SKEY]);
    }

    /**
     * 顧客名寄せ候補一覧を表示
     */
    public function listAction()
    {
        // 権限チェック
        $this->checkAdminPermission();

        // 顧客統合 名寄せリスト取得
        $sql  = " SELECT TBL.CombinedListId ";
        $sql .= "      , TBL.ManCustId ";
        $sql .= "      , MC.NameKj ";
        $sql .= "      , MC.NameKn ";
        $sql .= "      , MC.UnitingAddress ";
        $sql .= "      , MC.Phone ";
        $sql .= "      , MC.MailAddress ";
        $sql .= "      , TBL.Likeness ";
        $sql .= "      , TBL.CombinedDictate ";
        $sql .= "   FROM (SELECT CL.CombinedListId ";
        $sql .= "              , MAX(MC.ManCustId) AS ManCustId ";
        $sql .= "              , SUM(CL.LikenessFlg) AS Likeness ";
        $sql .= "              , SUM(CL.CombinedDictateFlg) AS CombinedDictate ";
        $sql .= "           FROM T_CombinedList CL ";
        $sql .= "                INNER JOIN T_ManagementCustomer MC ON MC.ManCustId = CL.ManCustId ";
        $sql .= "          WHERE CL.ValidFlg = 1 ";
        $sql .= "            AND MC.ValidFlg = 1 ";
        $sql .= "          GROUP BY CL.CombinedListId ";
        $sql .= "        ) TBL ";
        $sql .= "        INNER JOIN T_ManagementCustomer MC ON TBL.ManCustId = MC.ManCustId ";
        $sql .= "  ORDER BY MC.ManCustId DESC ";
        $sql .= "         , TBL.CombinedListId DESC ";

        $stm = $this->app->dbAdapter->query($sql);

        $ar = ResultInterfaceToArray($stm->execute(null));

        // 件数取得
        // count関数対策
        $listCnt = 0;
        if (!empty($ar)){
            $listCnt = count($ar);
        }

        // 取得データを反映
        $this->view->assign('listCnt', $listCnt);
        $this->view->assign('list', $ar);

        return $this->view;
    }

    /**
     * 顧客名寄せ候補一覧CSVをダウンロード
     */
    public function dcsvAction()
    {
        // 顧客名寄せ候補一覧CSVデータ取得
        $sql  = " SELECT a.CombinedListId ";
        $sql .= "      , b.ManCustId ";
        $sql .= "      , b.NameKj ";
        $sql .= "      , b.NameKn ";
        $sql .= "      , b.PostalCode ";
        $sql .= "      , b.UnitingAddress ";
        $sql .= "      , b.Phone ";
        $sql .= "      , b.MailAddress ";
        $sql .= "      , CASE b.GoodFlg WHEN 1 THEN '優良' ELSE '' END AS GoodFlg ";
        $sql .= "      , CASE b.BlackFlg WHEN 1 THEN 'ブラック' ELSE '' END AS BlackFlg ";
        $sql .= "      , CASE b.ClaimerFlg WHEN 1 THEN 'クレーマー' ELSE '' END AS ClaimerFlg ";
        $sql .= "      , CASE b.RemindStopFlg WHEN 1 THEN '督促ストップ' ELSE '' END AS RemindStopFlg ";
        $sql .= "      , CASE b.IdentityDocumentFlg WHEN 1 THEN 'アップ済' ELSE '' END AS IdentityDocumentFlg ";
        $sql .= "      , CASE a.LikenessFlg WHEN 1 THEN '' ELSE '類似' END AS LikenessFlg ";
        $sql .= "      , CASE a.CombinedDictateFlg WHEN 1 THEN '指示' ELSE '' END AS CombinedDictateFlg ";
        $sql .= "      , DATE_FORMAT(a.CombinedDictateDate, '%Y/%m/%d') AS CombinedDictateDate ";
        $sql .= "      , DATE_FORMAT(a.RegistDate, '%Y/%m/%d') AS RegistDate ";
        $sql .= "      , a.RegistId ";
        $sql .= "      , DATE_FORMAT(a.UpdateDate, '%Y/%m/%d') AS UpdateDate ";
        $sql .= "      , a.UpdateId ";
        $sql .= "   FROM T_CombinedList a ";
        $sql .= "        INNER JOIN T_ManagementCustomer b ON a.ManCustId = b.ManCustId ";
        $sql .= "  WHERE a.ValidFlg = 1 ";
        $sql .= "    AND b.ValidFlg = 1 ";
        $sql .= "  ORDER BY a.CombinedListId ASC ";
        $sql .= "         , b.ManCustId DESC ";

        $stm = $this->app->dbAdapter->query($sql);

        $ar = ResultInterfaceToArray($stm->execute(null));

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI21171_1';     // テンプレートID       顧客名寄せ候補一覧
        $templateClass = 0;             // 区分                 CB
        $seq = 0;                       // シーケンス           区分CBのため0
        $templatePattern = 0;           // テンプレートパターン 区分CBのため0

        $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( $ar, sprintf( 'Cust_CombinedList_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    private function checkAdminPermission() {
        // 権限により機能制約を設けるなら以下のコメントアウトを解除する
        // TODO: 抽象アクションクラスをもう1層設けるなりして、もう少し共通化したい（09.07.17 eda）
        //if( ! $this->isAdmin ) throw new Exception('権限がありません');
    }
}

