<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;

/**
 * データベースの状態を確認・管理するコントローラ
 */
class DbstatusController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';
	/**
	 * アプリケーションインスタンス
	 * @var Application
	 */
	protected $app;

	/**
	 * コントローラ初期化
	 */
	protected function _init() {

        $this->app = Application::getInstance();

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json+.js');
        $this->addJavaScript('../js/corelib.js');
        $this->addJavaScript('../js/cookiemanager.js');

        $this->setPageTitle("後払い.com - データベースの状態");

        $this->view->assign('ignore_other_menu', false);
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
	}

	/**
	 * DB状況表示アクション
	 */
	public function indexAction() {

        $this->addStyleSheet('../css/netb.ui.modaldialog.css');

        $this->addJavaScript('../js/bytefx.js');
        $this->addJavaScript('../js/base.ui.js');
        $this->addJavaScript('../js/base.ui.modaldialog.js');

        return $this->view;
	}

	/**
	 * 本日の注文登録状況アクション
	 */
	public function orderAction() {

        $this->setPageTitle('後払い.com - 本日の注文登録状況');
        $this->view->assign('data', $this->_getOrderCountTemplate());

        return $this->view;
	}

	/**
	 * 本日の時間帯別注文登録状況アクション
	 */
	public function orderhAction() {

        $this->setPageTitle('後払い.com - 本日の時間帯別登録状況');
        $tmpl = array();
        for($i = 0; $i < 24; $i++) {
            $tmpl[sprintf('%02d', $i)] = 0;
        }
        $tmpl['ttl'] = 0;
        $this->view->assign('data', $tmpl);

        return $this->view;
	}

	/**
	 * 社内与信実行待ち状況アクション
	 */
	public function waitordersAction() {

        $this->setPageTitle('後払い.com - 社内与信実行待ち状況');

        return $this->view;
	}

	/**
	 * 現在起動中のMySQLスレッド情報を取得するAjaxアクション
	 */
	public function proclistAction() {
//***************************
	    $this->_setResponseMode();

		$result = array();
		foreach($this->db->fetchAll('show full processlist') as $row) {
			// クエリ以外のコマンドは無視
			if(nvl($row['Command']) != 'Query') continue;

			// 別DBの状態は無視
			if($row['db'] != $this->app->dbInfo->params->dbname) continue;

			// ホスト情報を加工
			$host_sign = sprintf('%s / %s', $row['db'], $row['Host']);

			$result[] = array(
				'thread_id' => (int)$row['Id'],
				'host_sign' => $host_sign,
				'time' => (int)$row['Time'],
				'state' => nvl($row['State']),
				'info' => nvl($row['Info'])
			);
		}
		// スレッドIDでソート
		usort($result, array($this, 'sortProcList'));

		// 結果を出力
		echo Zend_Json::encode($result);
	}

	/**
	 * 指定のMySQLスレッドをkillするAjaxアクション
	 */
	public function killprocAction() {
//***************************
	    $thread_id = (int)$this->getRequest()->getPost('thread_id', 0);
		if(!$thread_id) {
			// スレッドID指定が不正な場合はスレッドリストを返して終了
			$this->_forward('proclist');
			return;
		}
		try {
			$this->db->query('kill query ?', $thread_id);

			// killが成功したらスレッドリストを返して終了
			$this->_forward('proclist');
		} catch(Exception $err) {

			// 例外レスポンス
			$this->_helper->viewRenderer->setNoRender();
			$this->getResponse()->setHeader('Content-Type', 'application/json', true);
			echo Zend_Json::encode(array(
				'error' => $err->getMessage()
			));
		}
	}

	/**
	 * proclistAction内で生成されたスレッドリストを
	 * ソートするためのソートメソッド
	 *
	 * @param mixed $a ソート対象の左側要素
	 * @param mixed $b ソート対象の右側要素
	 * @return $aが優先の場合は-1、そうでない場合は1
	 */
	public function sortProcList($a, $b) {

        if($a['thread_id'] == $b['thread_id']) {
            return $a['time'] > $b['time'] ? -1 : 1;
        }
        return $a['thread_id'] < $b['thread_id'] ? -1 : 1;
	}

	/**
	 * ordercountActionが返すデータ構造のテンプレートを取得する
	 *
	 * @access private
	 * @return array
	 */
	private function _getOrderCountTemplate() {
	    return array(
			'11' => array(
				'label' => '社内与信実行待ち',
				'count' => 0
			),
			'12' => array(
				'label' => '社内与信実行待ち（リアルタイム）',
				'count' => 0
			),
			'15' => array(
				'label' => '社内与信確定待ち',
				'count' => 0
			),
			'21' => array(
				'label' => '社内与信保留',
				'count' => 0
			),
			'31' => array(
				'label' => '伝票番号登録待ち',
				'count' => 0
			),
			'41' => array(
				'label' => '請求書印刷待ち',
				'count' => 0
			),
			'51' => array(
				'label' => '入金確認待ち',
				'count' => 0
			),
			'61' => array(
				'label' => '一部入金',
				'count' => 0
			),
	        '91' => array(
				'label' => 'クローズ',
				'count' => 0
			),
			'cnl' => array(
				'label' => '(キャンセル)',
				'count' => 0
			),
			'ttl' => array(
				'label' => '合計',
				'count' => 0
			),
			'cur' => array(
				'label' => 'この時間',
				'count' => 0
			),
			'api' => array(
				'label' => '(内API登録)',
				'count' => 0
			)
		);
	}

	/**
	 * 本日登録分の注文の最小OrderSeqを取得するAjaxアクション
	 */
	public function todayminseqAction() {
//***************************
	    $this->_setResponseMode();

		$q = <<<EOQ
SELECT MIN(OrderSeq) AS seq
FROM T_Order
WHERE RegistDate >= CURDATE()
EOQ;
		try {
			echo Zend_Json::encode( $this->_setResponseMode()->db->fetchRow($q) );
		} catch(Exception $err) {
			echo Zend_Json::encode(array('error' => $err->getMessage()));
		}
	}

	/**
	 * 本日のDataStatus別注文登録数を取得するAjaxアクション
	 */
	public function ordercountAction() {
//***************************
	    $this->_setResponseMode();

		$seq = $this->fixOrderSeq($this->getRequest()->getPost('seq'));

		$result = $this->_getOrderCountTemplate();
		$q = <<<EOQ
SELECT
	COALESCE(DataStatus, 'ttl') AS DataStatus,
	COUNT(*) AS OrderCount
FROM
	T_Order
WHERE
	OrderSeq >= :oseq
GROUP BY
	DataStatus WITH ROLLUP
UNION ALL
SELECT
	'cnl' AS DataStatus,
	COUNT(*) AS OrderCount
FROM
	T_Order
WHERE
	OrderSeq >= :oseq AND
	Cnl_Status IN (1, 2)
UNION ALL
SELECT
	'cur' AS DataStatus,
	COUNT(*) AS OrderCount
FROM
	T_Order
WHERE
	OrderSeq >= :oseq AND
	DATE_FORMAT(RegistDate, '%Y-%m-%d %H') = :hour
UNION ALL
SELECT
	'autook' AS DataStatus,
	COUNT(*) AS OrderCount
FROM
	T_Order
WHERE
	OrderSeq >= :oseq AND
	Cnl_Status = 0 AND
	DataStatus NOT IN (11, 15, 21, 25) AND
	(
		Incre_Note LIKE '%(与信自動ＯＫ%' OR
		Incre_Note LIKE '%（与信自動ＯＫ%' OR
		Incre_Note LIKE '%（APIリアルタイム与信自動ＯＫ%'
	)
UNION ALL
SELECT
	'autong' AS DataStatus,
	COUNT(*) AS OrderCount
FROM
	T_Order
WHERE
	OrderSeq >= :oseq AND
	Cnl_Status = 0 AND
	DataStatus NOT IN (11, 15, 21, 25) AND
	(
		Incre_Note LIKE '%(与信自動ＮＧ%' OR
		Incre_Note LIKE '%（与信自動ＮＧ%' OR
		Incre_Note LIKE '%（APIリアルタイム与信自動ＮＧ%'
	)
UNION ALL
SELECT
	'api' AS DataStatus,
	COUNT(*) AS OrderCount
FROM
	T_Order
WHERE
	OrderSeq >= :oseq AND
	Cnl_Status = 0 AND
	ApiUserId IS NOT NULL
ORDER BY
	DataStatus
EOQ;
		$params = array(
			'oseq' => $seq,
			'hour' => date('Y-m-d H')
		);
		try {
			foreach($this->db->fetchAll($q, $params) as $row) {
				$result[$row['DataStatus']]['count'] = (int)$row['OrderCount'];
			}

			echo Zend_Json::encode($result);
		} catch(Exception $err) {
			echo Zend_Json::encode(array('error' => $err->getMessage()));
		}
	}

	/**
	 * 本日の時間帯別注文登録数を取得するAjaxアクション
	 */
	public function ordercounthAction() {
//***************************
	    $this->_setResponseMode();

		$seq = $this->fixOrderSeq($this->getRequest()->getParam('seq'));

		$result = array();
		for($i = 0; $i < 24; $i++) {
			$result[sprintf('%02d', $i)] = 0;
		}
		$result['ttl'] = 0;
		$q = <<<EOQ
SELECT
	 DATE_FORMAT(RegistDate, '%H') AS RegistHour,
	 COUNT(*) AS OrderCount
FROM
	 T_Order
WHERE
	 OrderSeq >= ?
GROUP BY
	 DATE_FORMAT(RegistDate, '%H') WITH ROLLUP
EOQ;
		try {
			foreach($this->db->fetchAll($q, $seq) as $row) {
				$key = nvl($row['RegistHour'], 'ttl');
				$result[$key] = (int)$row['OrderCount'];
			}

			echo Zend_Json::encode($result);
		} catch(Exception $err) {
			echo Zend_Json::encode(array('error' => $err->getMessage()));
		}
	}

	/**
	 * 社内与信実行待ち状況を取得するAjaxアクション
	 */
	public function waitorderscountAction() {
//***************************
	    $this->_setResponseMode();

		$result = array();
		$q = <<<EOQ
SELECT
	CONCAT(
		DATE_FORMAT(RegistDate, '%Y-%m-%d %H'), ':',
		LPAD(
			CAST(DATE_FORMAT(RegistDate, '%i') AS UNSIGNED) -
			(CAST(DATE_FORMAT(RegistDate, '%i') AS UNSIGNED) % 15),
			2, '0'
		), ':00'
	) AS RegistDate,
	COUNT(*) AS RegistCount,
	SUM(CASE WHEN DataStatus = 11 AND Cnl_Status = 0 THEN 1 ELSE 0 END) AS WaitCount
FROM
	T_Order
WHERE
	OrderSeq >= (SELECT MIN(OrderSeq) FROM T_Order WHERE DataStatus = 11 AND Cnl_Status = 0)
GROUP BY
	CONCAT(
		DATE_FORMAT(RegistDate, '%Y-%m-%d %H'), ':',
		LPAD(
			CAST(DATE_FORMAT(RegistDate, '%i') AS UNSIGNED) -
			(CAST(DATE_FORMAT(RegistDate, '%i') AS UNSIGNED) % 15),
			2, '0'
		), ':00'
	) WITH ROLLUP
HAVING
	SUM(CASE WHEN DataStatus = 11 AND Cnl_Status = 0 THEN 1 ELSE 0 END) > 0
EOQ;
		try {
			foreach($this->db->fetchAll($q) as $row) {
				$result[] = array(
					'date' => nvl($row['RegistDate'], 'total'),
					'count' => (int)$row['RegistCount'],
					'wait' => (int)$row['WaitCount']
				);
			}
			echo Zend_Json::encode($result);
		} catch(Excetion $err) {
			echo Zend_Json::encode(array('error' => $err->getMessage()));
		}
	}

	/**
	 * API経由の登録完了待ち注文数を管理するアクション
	 */
	public function waitapiordersAction() {

	    $this->setPageTitle("後払い.com - 処理中API注文登録一覧");
        $this->view->assign('ignore_other_menu', true);
        // すべてAjaxでオペレーションするのでアクションではなにもしない

        return $this->view;
	}

	/**
	 * レスポンスのContent-Typeを変更する
	 *
	 * @access private
	 * @param null|string $contentType 変更するContent-Typeヘッダの値。省略時は application/json
	 * @param null|boolean $noRender ビューレンダリングを行わないかのフラグ。省略時はtrue
	 * @return DbstatusController このインスタンス
	 */
	private function _setResponseMode($contentType = 'application/json', $noRender = true) {
//***************************
	    if($noRender) $this->_helper->viewRenderer->setNoRender();
		$this->getResponse()->setHeader('Content-Type', $contentType, true);
		return $this;
	}

	/**
	 * postされたOrderSeqが、当日以降の適切な値であるかを判断する
	 *
	 * @access private
	 * @param int $seq テストするOrderSeq
	 * @return int テスト後の適切な値に変換されたOrderSeq
	 */
	private function fixOrderSeq($seq) {
//***************************
	    $seq = (int)$seq;
		$q = <<<EOQ
SELECT COUNT(*) AS cnt
FROM T_Order
WHERE OrderSeq = :oseq AND RegistDate >= CURDATE()
EOQ;
		$order = $this->db->fetchRow($q, array('oseq' => $seq));
		return ($order['cnt']) ? $seq : 99999999;
	}

	/**
	 * 与信バッチ実行状況を取得するAjaxアクション
	 */
	public function judgestatusAction() {
//***************************
		$start = microtime(true);
		$this->_setResponseMode();
		$this->app->addClass('Table_SystemStatus');
		$db = $this->app->dbAdapter;
		try {
			$ss = new Table_SystemStatus($db);
			$result = array(
				'date' => $this->db->fetchOne('select current_timestamp'),
				'status' => $ss->isProcessing() ? 'running' : 'idle'
			);
			$result['estimated'] = (microtime(true) - $start) / 1000;
			echo Zend_Json::encode($result);
		} catch(Exception $err) {

			// 例外レスポンス
			$this->_helper->viewRenderer->setNoRender();
			$this->getResponse()->setHeader('Content-Type', 'application/json', true);
			echo Zend_Json::encode(array(
				'error' => $err->getMessage()
			));
		}
	}
}