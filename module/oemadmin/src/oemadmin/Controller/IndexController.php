<?php
namespace oemadmin\Controller;

use oemadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableOrder;
use models\Table\TablePayingAndSales;
use models\Table\TableEnterprise;
use models\Table\TableSystemStatus;
use models\View\ViewOrderCustomer;
use models\View\ViewWaitForCancelConfirm;
use models\View\ViewWaitForFirstClaim;
use models\View\ViewArrivalConfirm;
use models\View\ViewChargeConfirm;
use Coral\Coral\Mail\CoralMail;

class IndexController extends CoralControllerAction
{
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * IndexControllerを初期化する
     */
    public function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet($this->app->getOemCss())
             ->addStyleSheet('../../oemadmin/css/top.css');

        $this->setPageTitle($this->app->getOemServiceName()." - トップ");
    }

//    /**
//     * 未定義のアクションがコールされた
//     */
//    public function __call($method, $args)
//    {
//        // 無条件にlistへinvoke
//        return $this->_forward('index');
//    }

    /**
     * Indexアクション
     */
    public function indexAction()
    {
        $mdlo = new TableOrder($this->app->dbAdapter);
        //$mdlss = new TableSystemStatus($this->app->dbAdapter);
        $mdlvcc = new ViewChargeConfirm($this->app->dbAdapter);
        $oemId = $this->app->authManagerAdmin->getUserInfo()->OemId;
        $count_by_ds = $mdlo->getCountDsForTop(array(11, 15, 21, 31, 51, 61), (int)$oemId);
        $highlight['rw11count'] = $count_by_ds[11];     // 社内与信実行待ち件数
        $highlight['rw15count'] = $count_by_ds[15];     // 社内与信確定待ち件数
        $highlight['rw21count'] = $count_by_ds[21];     // DMI EXP/IMP待ち件数

        // 伝票番号登録中件数取得
        $highlight['rw31count'] = $count_by_ds[31];

        // 請求書発行中件数取得
        $highlight['rwToPrintcount'] = $mdlo->getToPrintCountOem((int)$oemId);

        // 着荷確認中件数取得
        $highlight['rwArrivalcount'] = $mdlo->getArrivalCountOem((int)$oemId);

        // 入金確認中件数取得
        $highlight['rw51count'] = $count_by_ds[51];     // 入金確認待ち件数
        $highlight['rw61count'] = $count_by_ds[61];     // 一部入金済件数

        //if ($mdlss->isProcessing())
        //{
            //$highlight['isJudgeProcessing'] = '自動与信実行中・・・　<a href="index/unlock">[実行ロック解除]</a>';
        //}
        //else
        //{
            //$highlight['isJudgeProcessing'] = '';
        //}

        $this->view->assign('highlight', $highlight);

        if ($this->app->authManagerAdmin->getUserInfo()->RoleCode > 1)
        {
            // 管理者またはスーパーユーザー
            $this->view->assign('isAdmin', 'yes');
        }
        else
        {
            // 一般ユーザー
            $this->view->assign('isAdmin', 'no');
        }

        // JS割り当て
        $this
            ->addJavaScript( '../../js/prototype.js' )
            ->addJavaScript( '../../js/corelib.js' );

        // OEM向けお知らせ
        $notice = $this->app->dbAdapter->query(" SELECT IFNULL(Note,'') AS Note FROM M_Code WHERE CodeId = 5 AND KeyCode = 1 "
            )->execute(null)->current()['Note'];
        if ($notice != '') {
            $this->view->assign('notice', $notice);
        }

        return $this->view;
    }
}

