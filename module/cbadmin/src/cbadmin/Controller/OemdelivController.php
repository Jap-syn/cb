<?php
namespace cbadmin\Controller;

use Zend\Json\Json;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Table\TableOemDeliMethodList;
use models\Logic\LogicDeliveryMethod;

/**
 * OEM別配送先カスタマイズコントローラ
 */
class OemdelivController extends CoralControllerAction {

    protected $_componentRoot = './application/views/components';
	/**
	 * アプリケーションインスタンス
	 *
     * @access protected
	 * @var Application
	 */
	protected $app;

	/**
	 * コントローラ初期化
	 */
	protected function _init()
	{
        $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');
        $this->addJavaScript('../js/corelib.js');

        $this->setPageTitle("後払い.com - OEM別配送先設定");

        $this->view->assign( 'current_action', $this->getActionName() );
	}

	/**
	 * indexAction
	 * OEM先一覧を表示する
	 */
	public function indexAction()
	{
        $this->view->assign('list', ResultInterfaceToArray($this->fetchOemList()));

        return $this->view;
	}

	/**
	 * oemAction
	 * 指定OEMの配送方法設定編集画面を表示する
	 */
    public function oemAction()
    {
        $oemId = $this->getOemId();
        if($oemId < 1) {
            return $this->_forward('index');
        }

        $this->view->assign('oid', $oemId);
        $this->view->assign('oem', ResultInterfaceToArray($this->getOemInfo($oemId)));

        return $this->view;
    }

	/**
	 * saveAction
	 * 指定OEMの配送方法設定を保存する
	 */
    public function saveAction()
    {
        $oid = $this->getOemId();

        // OEM先指定が不正な場合は一覧へ
        if($oid < 1) {
            return $this->_forward('index');
        }

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $params = $this->getParams();

            // 送信内容を展開
            $data = Json::decode( (isset($params['data']) ? $params['data'] : '[]') , Json::TYPE_ARRAY);
            if(!is_array($data)) {
                throw new \Exception('不正なデータが送信されました');
            }

            $tbl = new TableOemDeliMethodList($this->app->dbAdapter);
$this->app->logger->debug( '[saveAction(0)]' );
            // 現在のデータを全削除
            $tbl->deleteByOemId($oid);
$this->app->logger->debug( '[saveAction(1)]' );
            // 登録されたデータをすべて挿入
            foreach($data as $index => $value) {
                $tbl->saveNew( array('DeliMethodId' => $value, 'OemId' => $oid, 'ListNumber' => $index + 1) );
            }
$this->app->logger->debug( '[saveAction(2)]' );

            // 配送伝票自動仮登録が有効な事業者の配送方法を補正
            $lgc = new LogicDeliveryMethod($this->app->dbAdapter);
            $lgc->fixEnterpriseAutoJournalDeliMethod($oid);
$this->app->logger->debug( '[saveAction(3)]' );
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();

            // エラーが発生したらエラーメッセージ付きで編集画面へ
            $this->view->assign('oid', $oid);
            $this->view->assign('oem', $this->getOemInfo($oid)->current());
            $this->view->assign('error', $err->getMessage());

            $this->setTemplate('oem');
            return $this->view;
        }

        // 保存が成功したので編集画面へリダイレクト
        return $this->_redirect(sprintf('oemdeliv/oem/oid/%s', $oid));
    }

	/**
	 * masterAction
	 * 配送方法マスターデータをJSON形式で返すAjax向けアクション
	 */
    public function masterAction()
    {
        $this->prepare();
        echo Json::encode( $this->getDelivList() );
        return $this->getResponse();
    }

	/**
	 * currentAction
	 * 指定OEMの現在の配送方法データをJSON形式で返すAjax向けアクション
	 */
    public function currentAction()
    {
        $this->prepare();
        echo Json::encode( $this->getDelivList($this->getOemId()) );
        return $this->getResponse();
    }

	/**
	 * entinfoAction
	 * 指定OEM配下で配送伝票自動仮登録が有効な事業者の情報をJSON形式で返すAjax向けアクション
	 */
	public function entinfoAction()
	{
        $this->prepare();
        echo Json::encode( ResultInterfaceToArray($this->getEntDelivInfo($this->getOemId())) );
        return $this->getResponse();
	}

	/**
	 * 要求されたOEM先のOEM IDを取得する
	 *
	 * @access protected
	 * @return int
	 */
    protected function getOemId()
    {
        $params = $this->getParams();

        return isset($params['oid']) ? $params['oid'] : 0;
    }

	/**
	 * 指定OEM先のデータを取得する
	 *
	 * @access protected
	 * @param int $oemId OEM ID
	 * @return ResultInterface
	 */
	protected function getOemInfo($oemId = null)
	{
        $tbl = new \models\Table\TableOem($this->app->dbAdapter);
        return $tbl->findOem($oemId);
	}

	/**
	 * 指定OEM先向けの配送方法一覧を取得する。
	 * 戻り値の要素は連想配列で、以下のキーを持つ
	 * - DeliMethodId
	 * - DeliMethodName
	 * - ValidFlg
	 *
	 * @access protected
	 * @param int $oemId OEM ID
	 * @return array
	 */
    protected function getDelivList($oemId = -1)
    {
        $conf = array(
            'DeliMethodId' => true,
            'DeliMethodName' => false,
            'ValidFlg' => true
        );

        $lgc = new LogicDeliveryMethod($this->app->dbAdapter);

        // OEM IDが-1（＝引数がnull）の場合は全配送方法を、
        // OEM IDが0の場合は空のリストを、
        // それ以外は指定OEMの配送方法リストを返す

        $list = ((int)$oemId) < 1 ?
            ($oemId == 0 ? array() : $lgc->getAllDeliMethodList()) :
             $lgc->getDeliMethodList($oemId, true);

        $result = array();
        foreach($list as $row) {
            $data = array();
            foreach($conf as $col => $is_int) {
                $data[$col] = $is_int ? ((int)$row[$col]) : $row[$col];
            }
            $result[] = $data;
        }

        return $result;
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

	/**
	 * このコントローラ向けのOEM先一覧を取得する
	 *
	 * @access protected
	 * @return ResultInterface
	 */
    protected function fetchOemList()
    {
        $q = <<<EOQ
SELECT
	oem.*,
	(SELECT
	 COUNT(*)
	 FROM T_OemDeliveryMethodList
	 WHERE OemId = oem.OemId
	) AS DelivCount
FROM
	T_Oem oem
ORDER BY
	oem.OemId
EOQ;
        return $this->app->dbAdapter->query($q)->execute(null);
    }

	/**
	 * 指定OEM先配下の配送伝票自動仮登録設定事業者の情報を取得する
	 *
	 * @access protected
	 * @var int $oemId OEM ID
	 * @return ResultInterface
	 */
	protected function getEntDelivInfo($oemId = -1)
	{
        $q = <<<EOQ
SELECT ent.EnterpriseId
,      ent.EnterpriseNameKj
,      site.AutoJournalDeliMethodId AS DeliMethodId
,      md.DeliMethodName
FROM   T_Enterprise ent
,      T_Site site
,      M_DeliveryMethod md
WHERE  ent.EnterpriseId = site.EnterpriseId
AND    site.AutoJournalDeliMethodId = md.DeliMethodId
AND    IFNULL(ent.OemId, 0) = :OemId
AND    site.AutoJournalIncMode = 1
ORDER BY ent.EnterpriseId
EOQ;
        return $this->app->dbAdapter->query($q)->execute(array(':OemId' => $oemId));
	}
}
