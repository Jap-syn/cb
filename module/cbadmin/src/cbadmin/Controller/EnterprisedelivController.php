<?php
namespace cbadmin\Controller;

use Zend\Json\Json;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Table\TableEnterprise;
use models\Table\TableEnterpriseDelivMethod;
use models\Logic\LogicDeliveryMethod;

/**
 * 加盟店別配送先カスタマイズコントローラ
 */
class EnterprisedelivController extends CoralControllerAction {

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

        $this->setPageTitle("後払い.com - 加盟店別配送先設定");

        $this->view->assign( 'current_action', $this->getActionName() );
	}

	/**
	 * editAction
	 * 指定加盟店の配送方法設定編集画面を表示する
	 */
    public function editAction()
    {
        $enterpriseId = $this->getEnterpriseId();

        $obj = new TableEnterprise($this->app->dbAdapter);

        $this->view->assign('eid', $enterpriseId);
        $this->view->assign('enterprise', $obj->findEnterprise($enterpriseId)->current());

        return $this->view;
    }

	/**
	 * saveAction
	 * 指定加盟店の配送方法設定を保存する
	 */
    public function saveAction()
    {
        $enterpriseId = $this->getEnterpriseId();

        // 加盟店IDが不正な場合は直ちに戻る
        if ($enterpriseId < 1) {
            return $this->_redirect(sprintf('enterprisedeliv/edit/eid/%s', $enterpriseId));
        }

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $params = $this->getParams();

            // 送信内容を展開
            $data = Json::decode( (isset($params['data']) ? $params['data'] : '[]') , Json::TYPE_ARRAY);
            if (!is_array($data)) {
                throw new \Exception('不正なデータが送信されました');
            }

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser($this->app->dbAdapter);
            $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

            $tbl = new TableEnterpriseDelivMethod($this->app->dbAdapter);

            // 現在のデータを全削除
            $tbl->deleteByEnterpriseId($enterpriseId);

            // 登録されたデータをすべて挿入
            foreach($data as $index => $value) {
                $tbl->saveNew( array('DeliMethodId' => $value, 'EnterpriseId' => $enterpriseId, 'ListNumber' => $index + 1, 'RegistId' => $userId, 'UpdateId' => $userId ));
            }

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        }
        catch (\Exception $err) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();

            // エラーが発生したらエラーメッセージ付きで編集画面へ
            $obj = new TableEnterprise($this->app->dbAdapter);

            $this->view->assign('eid', $enterpriseId);
            $this->view->assign('enterprise', $obj->findEnterprise($enterpriseId)->current());
            $this->view->assign('error', $err->getMessage());

            $this->setTemplate('edit');
            return $this->view;
        }

        // 保存が成功したので編集画面へリダイレクト
        return $this->_redirect(sprintf('enterprisedeliv/edit/eid/%s', $enterpriseId));
    }

	/**
	 * masterAction
	 * 配送方法マスターデータをJSON形式で返すAjax向けアクション
	 */
    public function masterAction()
    {
        $this->prepare();
        echo Json::encode( $this->getDelivListMaster($this->getEnterpriseId()) );
        return $this->getResponse();
    }

	/**
	 * currentAction
	 * 指定加盟店の現在の配送方法データをJSON形式で返すAjax向けアクション
	 */
    public function currentAction()
    {
        $this->prepare();
        echo Json::encode( $this->getDelivListUse($this->getEnterpriseId()) );
        return $this->getResponse();
    }

	/**
	 * 要求された加盟店IDを取得する
	 *
	 * @access protected
	 * @return int
	 */
	protected function getEnterpriseId()
	{
        $params = $this->getParams();

        return isset($params['eid']) ? $params['eid'] : 0;
	}

    /**
     * 指定加盟店IDの[配送方法マスター]一覧を取得する。
     * 戻り値の要素は連想配列で、以下のキーを持つ
     * - DeliMethodId
     * - DeliMethodName
     * - ValidFlg
     *
     * @access protected
     * @param int $enterpriseId 加盟店ID
     * @return array
     */
    protected function getDelivListMaster($enterpriseId)
    {
        $conf = array(
            'DeliMethodId' => true,
            'DeliMethodName' => false,
            'ValidFlg' => true
        );

        // 加盟店の持つOemIDの取得
        $sql = " SELECT IFNULL(OemId,0) AS OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $oemid = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current()['OemId'];

        if ($oemid > 0) {
           $sql =<<<EOQ
SELECT dm.*
FROM   T_OemDeliveryMethodList odml
	   INNER JOIN M_DeliveryMethod dm ON (dm.DelimethodId = odml.DelimethodId)
WHERE  odml.ValidFlg = 1
AND    dm.ValidFlg = 1
AND    dm.ProductServiceClass = 0
AND    odml.OemId = :OemId
ORDER BY odml.ListNumber
EOQ;
            $list = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':OemId' => $oemid)));
        }
        else {
            $sql = " SELECT * FROM M_DeliveryMethod WHERE ValidFlg = 1 AND ProductServiceClass = 0 ORDER BY DeliMethodId ";
            $list = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));
        }

        // 共通
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
     * 指定加盟店IDの[使用する配送方法]一覧を取得する。
     * 戻り値の要素は連想配列で、以下のキーを持つ
     * - DeliMethodId
     * - DeliMethodName
     * - ValidFlg
     *
     * @access protected
     * @param int $enterpriseId 加盟店ID
     * @return array
     */
    protected function getDelivListUse($enterpriseId)
    {
        $conf = array(
                'DeliMethodId' => true,
                'DeliMethodName' => false,
                'ValidFlg' => true
        );

        $sql =<<<EOQ
SELECT dm.*
FROM   T_EnterpriseDelivMethod edm
	   INNER JOIN M_DeliveryMethod dm ON (dm.DeliMethodId = edm.DeliMethodId)
WHERE  edm.ValidFlg = 1
AND    dm.ValidFlg = 1
AND    dm.ProductServiceClass = 0
AND    edm.EnterpriseId = :EnterpriseId
ORDER BY edm.ListNumber
EOQ;
        $list = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId)));

        // 共通
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

}
