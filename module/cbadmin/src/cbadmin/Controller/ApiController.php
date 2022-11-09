<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Db\Adapter\Adapter;
use Zend\Json\Json;
use Coral\Coral\CoralUploadManager;
use models\Logic\LogicThreadPool;

/**
 * Webブラウザ以外からコールされるAPIを提供するコントローラ
 */
class ApiController extends CoralControllerAction {
	/**
	 * JSONでレスポンスを返す場合のContent-Type
	 *
	 */
	const CONTENT_TYPE_JSON = 'application/json';

	/**
	 * XMLでレスポンスを返す場合のContent-Type
	 *
	 */
	const CONTENT_TYPE_XML = 'application/xml';

	/**
	 * JSONでレスポンスを返すことを示すレスポンス種別キー
	 *
	 */
	const RESPONSE_MODE_JSON = 'json';

	/**
	 * XMLでレスポンスを返すことを示すレスポンス種別キー
	 *
	 */
	const RESPONSE_MODE_XML = 'xml';

	/**
	 * XMLレスポンスのルート要素の要素名
	 *
	 */
	const RESPONSE_XML_ROOT_ELEMENT_NAME = 'apiResponse';

	/**
	 * レスポンスを出力するメソッド情報を格納した連想配列
	 *
	 * @var array
	 */
	protected $_responseWriter;

	/**
	 * レスポンスモード
	 *
	 * @var string
	 */
	protected $_responseMode;

	/**
	 * レスポンスモードごとのContent-Typeヘッダ値
	 *
	 * @var array
	 */
	protected $_responseContentType;

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
	}

	/**
	 * 各アクション実行前に実行されます
	 */
	public function onBeforeAction()
	{
        $res = $this->getResponse();

        // レスポンスモードに対応するメソッド情報
        $this->_responseWriter = array(
                self::RESPONSE_MODE_JSON => array( $this, 'writeJsonResponse' ),
                self::RESPONSE_MODE_XML => array( $this, 'writeXmlResponse' )
        );

        // レスポンスモードとContent-Typeのマッピング
        $this->_responseContentType = array(
                self::RESPONSE_MODE_JSON => self::CONTENT_TYPE_JSON,
                self::RESPONSE_MODE_XML => self::CONTENT_TYPE_XML
        );

        // リクエスト情報からレスポンスモードを決定
        $params = $this->getParams();
        $this->_responseMode = (isset($params['mode'])) ? isset($params['mode']) : self::RESPONSE_MODE_JSON;

        // レスポンスモードに合わせてContent-Typeヘッダを出力
        // （→ rev 344時になぜかコメントアウトしていたが、当該リビジョンで追加したparsePostalAction()で
        //  その必要があったわけではないのでコード復旧 08.7.28 江田）
        $res->getHeaders()->addHeaderLine( 'Content-Type', $this->_responseContentType[ $this->_responseMode ] );
	}

	/**
	 * このアプリケーションが使用するデータベースへの接続情報を取得するAPIメソッド
	 */
	public function dbInfoAction() {
//***************************
	    $configPath = Application::getInstance()->configPath;
		$ini = new Zend_Config_Ini($configPath, 'database');
		$ini_array = $ini->toArray();
		try {
			$new_ini = new Zend_Config_Ini($configPath, 'db_for_printing');
			$new_ini_array = $new_ini->toArray();

			// 2階層目までマージする必要があるため、単純なarray_mergeではなく
			// ループ処理を実施
			$merged_array = array();
			foreach($ini_array as $key => $value) {
				if(isset($new_ini_array[$key])) {
					if(is_array($value) && is_array($new_ini_array[$key])) {
						$merged_array[$key] = array_merge($value, $new_ini_array[$key]);
					} else {
						$merged_array[$key] = $new_ini_array[$key];
					}
				} else {
					$merged_array[$key] = $value;
				}
			}
			$ini_array = $merged_array;
		} catch(Exception $err) {
			// nop
			// セクションが未定義の場合はここに来る
		}
		$this->writeResponse($ini_array);
	}

	/**
	 * 印刷ツールの外部設定を取得するためのAPIメソッド
	 */
	public function printingConfigAction() {
//***************************
	    $configPath = Application::getInstance()->configPath;
		$result = array(
			"enable_export" => false
		);
		try {
			$ini = new Zend_Config_Ini($configPath, 'printing');
			if( isset( $ini->client ) ) {
				foreach( $ini->client->toArray() as $key => $value ) {
					$result[$key] = $value;
				}
			}
		} catch(Exception $err) {
		}
		$this->writeResponse( $result );
	}

	/**
	 * 印刷ツールのエクスポートファイルアップロード受付アクション
	 */
	public function saveFileAction() {
//***************************
	    $file_key = 'exported_file';
		$account = Application::getInstance()->authManagerAdmin->getUserInfo();

		$result = array();
		// 設定取得
		$config = new Zend_Config_Ini( Application::getInstance()->configPath, null );
		try {
			$upload_info = $_FILES[$file_key];
			if( ! $upload_info ) throw new Exception('ファイルがPOSTされていません');

			// POSTデータ取得
			$params = $this->getRequest()->getParams();
			//保存時ファイル名を確定させる
			$type = isset( $params['type'] ) ? trim( "{$params['type']}" ) : 'wellnet';
			$file_name = isset( $config->printing->prefix->$type ) ?
				"{$config->printing->prefix->$type}_" . Zend_Date::now()->toString('yyyyMMdd_HHmmss') :
				'uploaded_file_' . Zend_Date::now()->toString('yyyyMMdd_HHmmss');
			// メモ
			$memo = "{$params['memo']}";

			// アップロードマネージャ
			$mgr = $this->getUploadManager();
			$result = array(
				'result' => 'OK',
				'saved_name' => $mgr->addFile(
					$upload_info['tmp_name'],
					$file_name,
					rawurldecode($upload_info['name']),
					$memo )
				);
			Application::getInstance()->logger->crit(
				"CSV export completed. operator = {$account->OpId}:{$account->NameKj}, file = {$result['saved_name']}"
			);
		} catch(Exception $err) {
			$result = array(
				'result' => 'NG',
				'reason' => $err->getMessage()
			);
			Application::getInstance()->logger->err(
				"CSV export faild. operator = {$account->OpId}:{$account->NameKj}, reason = {$result['reason']}"
			);
		}
		$this->writeResponse( $result );
	}

	/**
	 * アップロード済みのすべてのファイルのメタ情報を取得する
	 */
	public function fileListAction() {

        $result = array();

		try {
			$result = array(
				'result' => 'OK',
				'count' => 0,
				'list' => $this->getUploadManager()->getAllFileInfo()
			);

			// Count関数対策
			$list_count = 0;
			if(!empty($result['list'])){	
                $list_count = count($result['list']);	
            }
			$result['count'] = $list_count;
		} catch(\Exception $err) {
			$result = array(
				'result' => 'NG',
				'reason' => $err->getMessage()
			);
		}
		$this->writeResponse( $result );

		return $this->response;
	}

	/**
	 * 指定のアップロード済みファイルを削除する
	 */
	public function deleteFileAction() {

        $params = $this->getParams();
        $file = (isset($params['file'])) ? $params['file'] : -1;

        $mgr = $this->getUploadManager();
        $result = $mgr->removeFile($file);

        $this->writeResponse( $result ? array( 'result' => 'OK' ) : array( 'result' => 'NG' ) );

        return $this->response;
	}

	/**
	 * 郵便番号から住所を検索する
	 */
	public function parsePostalAction() {
//***************************
	    $app = Application::getInstance();
		$app
			->addClass('Coral_Validate_Utility')
			->addClass('Table_PostalCode');

		$results = array(
			'result' => 'OK',
			'count' => 0,
			'list' => array()
		);
		try {
			$db = $app->dbAdapter;

			$postal_code = preg_replace( '/-/', '', Coral_Validate_Utility::fixPostalCode($this->getRequest()->getParam('postalcode')) );
			$where = $db->select()
				->from( 'M_PostalCode' )
				->where( "PostalCode7 LIKE '" . $postal_code . "%'" )
				->order( array( 'PostalCode7', 'PrefectureKana', 'CityKana', 'TownKana' ) );

			foreach($db->fetchAll($where) as $row) {
				// Count関数対策
				$list_count = 0;
				if(!empty($results['list'])){
               		$list_count = count($results['list']);
				}
			
				if( $list_count > 20 ) {
					$results['list'][] = array( 'postal_code' => '---', 'address' => '(一致件数が多いため中断しました)' );
					break;
				}
				$results['list'][] = array(
					'postal_code' => Coral_Validate_Utility::fixPostalCode($row['PostalCode7']),
					'address' => join('', array($row['PrefectureKanji'], $row['CityKanji'], $row['TownKanji']))
				);
			}

			// Count関数対策
			$list_count = 0;
			if(!empty($results['list'])){
				   $list_count = count($results['list']);
			}

			$results['count'] = $list_count;
			if( $results['count'] < 1 ) {
				$results['list'][] = array( 'postal_code' => '---', 'address' => '(一致する住所はありません)' );
			}
		} catch(Exception $err) {
			$results['result'] = 'NG';
			$results['reason'] = $err->getMessage();
		}
		$this->writeResponse( $results );
	}

	/**
	 * 印紙代適用設定データを取得する
	 * ※：JSONフォーマット限定
	 */
	public function stampfeesettingsAction() {
//***************************
	    if($this->_responseMode != self::RESPONSE_MODE_JSON) {
			// モード指定がJSONでない場合はJSONに付け替えてこのアクションにフォワード
			$this->getRequest()->setParam('mode', self::RESPONSE_MODE_JSON);
			$this->_forward('stampfeesettings');
			return;
		}

		$app = Application::getInstance();
		$app->addClass('Logic_StampFee');
		/** @var Logic_StampFee */
		$stampFee = new Logic_StampFee($app->stampFeeLogicSettings);
		$this->writeResponse($stampFee->exportSettings());
	}

	/**
	 * 請求書発行履歴を注文のOEM先備考に追加する
	 * ※：JSONフォーマット限定
	 */
	public function appendcplogAction() {
//***************************
	    $app = Application::getInstance();
		if($this->_responseMode != self::RESPONSE_MODE_JSON) {
			// モード指定がJSONでない場合はJSONに付け替えてこのアクションにフォワード
			$this->getRequest()->setParam('mode', self::RESPONSE_MODE_JSON);
			$this->_forward('appendcplog');
			return;
		}

		$src = $this->getRequest()->getPost('update_params', '[]');
		// 印刷ツール側の仕様で単一パラメータ値のカンマがスペースに置換されているので受信側で再置換する
		$src = preg_replace('/ /u', ',', $src);
		$params = array();
		try {
			$params = Zend_Json::decode($src);
			$app->addClass('Table_Order');
			$orderTable = new Table_Order($app->dbAdapter);
			foreach($params as $oseq) {
				try {
					$orderTable->appendPrintedInfoToOemNote($oseq);
				} catch(Exception $appendError) {
					// エラーはロギングのみ
					$app->logger->debug(sprintf('[api/appendcplog] cannot append claim-printed-log: reason = %s', $appendError->getMessage()));
				}
			}
		} catch(Exception $err) {
			// nop
		}
		$this->writeResponse(array('result' => 'OK'));
	}

	/**
	 * API注文登録処理中の一覧を取得する
	 */
	public function getapiordersAction() {

        $ini = $this->loadThreadPoolConfig();

        $sql = <<<EOQ
SELECT
	o.OrderSeq,
	o.OrderId,
	o.RegistDate,
	o.DataStatus,
	(
		UNIX_TIMESTAMP(:curtime) -
		UNIX_TIMESTAMP(o.RegistDate)
	) AS ProcessTime,
	t.ThreadId,
	t.Status
FROM
	T_Order o LEFT OUTER JOIN
	T_ThreadPool t ON (
		t.UserData = o.OrderId AND
		t.ThreadGroup = :grp AND
		t.Status IN (0, 1)
	)
WHERE
	o.DataStatus = 12 AND
	o.Cnl_Status = 0
UNION ALL
SELECT
	o.OrderSeq,
	o.OrderId,
	o.RegistDate,
	o.DataStatus,
	(
		UNIX_TIMESTAMP(:curtime) -
		UNIX_TIMESTAMP(o.RegistDate)
	) AS ProcessTime,
	t.ThreadId,
	t.Status
FROM
	T_Order o INNER JOIN
	T_ThreadPool t ON (
		t.UserData = o.OrderId AND
		t.ThreadGroup = :grp AND
		t.Status IN (0, 1)
	)
WHERE
	o.DataStatus <> 12 AND
	o.Cnl_Status = 0
ORDER BY
	(CASE WHEN ThreadId IS NULL THEN -1 ELSE 1 END),
	OrderSeq
EOQ;
        $params = array('curtime' => date('Y-m-d H:i:s'), 'grp' => $ini['order']['group_name']);
        $ri = $this->app->dbAdapter->query($sql)->execute($params);
        $ary = ($ri->count() > 0) ? ResultInterfaceToArray($ri) : array();
        $this->writeResponse($ary);

        return $this->response;
	}

	/**
	 * API注文登録処理中件数を取得する
	 */
	public function countapiordersAction() {

        $ini = $this->loadThreadPoolConfig();

        $sql = <<<EOQ
SELECT
	COUNT(*) AS TotalCount,
	IFNULL(SUM(CASE
		WHEN ProcessTime >= 60 THEN 1
		ELSE 0
	END), 0) AS Count
FROM
	(
		SELECT
			o.OrderSeq,
			o.OrderId,
			o.RegistDate,
			o.DataStatus,
			(
				UNIX_TIMESTAMP(:curtime) -
				UNIX_TIMESTAMP(o.RegistDate)
			) AS ProcessTime,
			t.ThreadId,
			t.Status
		FROM
			T_Order o LEFT OUTER JOIN
			T_ThreadPool t ON (
				t.UserData = o.OrderId AND
				t.ThreadGroup = :grp AND
				t.Status IN (0, 1)
			)
		WHERE
			o.DataStatus = 12 AND
			o.Cnl_Status = 0
		UNION ALL
		SELECT
			o.OrderSeq,
			o.OrderId,
			o.RegistDate,
			o.DataStatus,
			(
				UNIX_TIMESTAMP(:curtime) -
				UNIX_TIMESTAMP(o.RegistDate)
			) AS ProcessTime,
			t.ThreadId,
			t.Status
		FROM
			T_Order o INNER JOIN
			T_ThreadPool t ON (
				t.UserData = o.OrderId AND
				t.ThreadGroup = :grp AND
				t.Status IN (0, 1)
			)
		WHERE
			o.DataStatus <> 12 AND
			o.Cnl_Status = 0
	) v
EOQ;
        $params = array('curtime' => date('Y-m-d H:i:s'), 'grp' => $ini['order']['group_name']);
        $row = $this->app->dbAdapter->query($sql)->execute($params)->current();
        if ($row) {
            $this->writeResponse($row);
            return $this->response;
        }

        $this->writeResponse(array('Count' => -1));
        return $this->response;
	}

	/**
	 * 指定注文のDataStatusとロック状態を解除する
	 */
	public function releaseapiorderAction() {

        $params = $this->getParams();
        $oseq = (isset($params['oseq'])) ? $params['oseq'] : -1;
        $ini = $this->loadThreadPoolConfig();

        try {
            $table = new \models\Table\TableOrder($this->app->dbAdapter);
            $order = $table->find($oseq)->current();

            if(!$order) {
                throw new \Exception('order not found !!');
            }
            if($order['DataStatus'] == 12) {
                $table->saveUpdate(array('DataStatus' => 11), $order['OrderSeq']);
            }

            $pool = LogicThreadPool::getPool($ini['order']['group_name'], array(LogicThreadPool::OPTION_DB_ADAPTER => $this->app->dbAdapter));

            foreach($pool->getRunningItemsByUserData($order['OrderId']) as $item) {
                $item->abend('force terminate');
            }
            $this->writeResponse(array('status' => 'OK'));
        } catch(\Exception $err) {
            $this->writeJsonResponse(array('status' => 'NG', 'error' => $err->getMessage()));
        }

        return $this->response;
	}

	/**
	 * 注文登録時のDataStatusのまま浮いている注文をすべてバッチ与信実行待ちにロールバックする
	 */
	public function rollbackapiordersAction() {

        $ini = $this->loadThreadPoolConfig();

        $sql = <<<EOQ
SELECT
	o.OrderSeq,
	o.OrderId,
	o.RegistDate,
	o.DataStatus,
	(
		UNIX_TIMESTAMP(:curtime) -
		UNIX_TIMESTAMP(o.RegistDate)
	) AS ProcessTime,
	t.ThreadId
FROM
	T_Order o LEFT OUTER JOIN
	T_ThreadPool t ON (
		t.UserData = o.OrderId AND
		t.ThreadGroup = :grp AND
		t.Status IN (0, 1)
	)
WHERE
	o.DataStatus = 12 AND
	o.Cnl_Status = 0 AND
	t.ThreadId IS NULL
EOQ;
        $params = array('curtime' => date('Y-m-d H:i:s'), 'grp' => $ini['order']['group_name']);
        $ri = $this->app->dbAdapter->query($sql)->execute($params);
        $stm = $this->app->dbAdapter->query(" UPDATE T_Order SET DataStatus = 11 WHERE OrderSeq = :OrderSeq AND DataStatus = 12 ");
        $count = 0;
        foreach ($ri as $row) {
            try {
                $stm->execute(array(':OrderSeq' => $row['OrderSeq']));
                $count++;
            }
            catch(\Exception $err) {
            }
        }

        $this->writeResponse(array('status' => 'OK', 'processed-count' => $count));

        return $this->response;
	}

	/**
	 * 指定のデータを現在のレスポンスモードで出力する
	 *
	 * @param array $data 出力するデータ
	 */
	public function writeResponse($data) {
	    call_user_func( $this->_responseWriter[ $this->_responseMode ], $data );
	}

	/**
	 * 指定のデータをJSONで出力する
	 *
	 * @param array $data 出力するデータの連想配列
	 */
	public function writeJsonResponse($data) {
        echo Json::encode( $data );
	}

	/**
	 * 指定のデータをXMLで出力する
	 *
	 * @pararm array $data 出力するデータの連想配列
	 */
	public function writeXmlResponse($data, $rootName = self::RESPONSE_XML_ROOT_ELEMENT_NAME) {
	    echo '<?xml version="1.0" encoding="' . mb_http_output() . '" standalone="yes"?>';
		$this->_writeXmlResponse($data, $rootName);
	}

	public function _writeXmlResponse($data, $rootName = self::RESPONSE_XML_ROOT_ELEMENT_NAME) {
	    if( ! is_string($rootName) ) {
			$rootName = self::RESPONSE_XML_ROOT_ELEMENT_NAME;
		}

		echo "<$rootName>";
		foreach( $data as $key => $value ) {
			if( is_array( $value ) ) {
				if( is_int( $key ) ) {
					$this->_writeXmlResponse( $value, "item" );
				} else {
					$this->_writeXmlResponse( $value, $key );
				}
			} else {
				echo "<$key>$value</$key>";
			}
		}
		echo "</$rootName>";
	}

	private function getUploadManager() {

        $config = $this->app->getApplicationiInfo($this->app->dbAdapter, 'cbadmin');
        if( ! isset( $config['printing'] ) ) return null;
        if( empty( $config['printing']['upload_directory'] ) ) return null;

        $account = $this->app->authManagerAdmin->getUserInfo();
        $options = array(
            CoralUploadManager::OPTION_AUTHOR_NAME => "{$account->OpId}:{$account->NameKj}",
            CoralUploadManager::OPTION_CLIENT_INFO => f_get_client_address()        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
        );

        return new CoralUploadManager( $config['printing']['upload_directory'], $options );
	}

	private function decodeURIComponent($s) {
        $s = preg_replace( '/%(25)/', '\\', $s );
        return Json::decode( '"' . $s . '"', Json::TYPE_ARRAY );
	}

	private function loadThreadPoolConfig() {
        $ini = $this->app->getApplicationiInfo($this->app->dbAdapter, 'api')['thread_pool'];
        return array_merge(array('order' => array('group_name' => 'api-order-rest')), $ini);
	}
}
