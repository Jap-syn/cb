<?php
namespace cbadmin\Controller;

use Coral\Base\IO\BaseIOUtility;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use models\Logic\LogicRwarvlData;

/**
 * 着荷確認データインポートエクスポートコントローラ
 */
class RwarvldataController extends CoralControllerAction {
	/**
	 * 1回のimportActionで処理するブロック件数の定義
	 * @var int
	 */
	const IMPORT_BLOCK_SIZE = 250;

	/**
	 * 一括登録時のファイルフィールドのname属性
	 *
	 * @var string
	 */
	const UPLOAD_FIELD_NAME = 'Csv_File';


	protected $_componentRoot = './application/views/components';

	/** @var Application */
	protected $app;

	/** @var LogicRwarvlData */
	protected $logic;

	/**
	 * コントローラ初期化
	 */
	protected function _init() {

		$this->addStyleSheet('../css/default02.css');
		$this->addJavaScript('../js/prototype.js');
		$this->addJavaScript('../js/json+.js');
		$this->addJavaScript('../js/corelib.js');

		$this->setPageTitle("後払い.com - 着荷確認 - 着荷確認データ");

		$this->app = Application::getInstance();

		$this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
		// 連携ロジック初期化
		$this->logic = new LogicRwarvlData($this->app->dbAdapter);
	}

	/**
	 * 着荷確認データ連携トップページアクション
	 */
	public function indexAction() {

	    $this->view->assign('impFileField', self::UPLOAD_FIELD_NAME);

		$this->addJavaScript('../js/base.ui.js');
		$this->addJavaScript('../js/base.ui.datepicker.js');
		$this->addStyleSheet('../css/base.ui.datepicker.css');

		return $this->view;
	}

	/**
	 * 着荷確認データ連携 エクスポートアクション
	 */
	public function exportAction() {

        $req_params = $this->getParams();
        $mode = (isset($req_params['mode'])) ? $req_params['mode'] : 'prepare';

		switch($mode) {
			case 'prepare':
			    // トップのフォームからsubmitされたらパラメータをエンコードして中継自動サブミットフォームを実行
			    $params = $req_params['exp'];
			    $this->view->assign('params', base64_encode(serialize($params)));
			    $this->setTemplate('preexport');
			    return $this->view;
			case 'exec':
			    // 中継自動サブミットフォームからsubmitされたらダウンロード実行フォームを実行
			    $this->view->assign('params', $req_params['exp-params']);
			    return $this->view;
			case 'download':
			    // ダウンロード実行フォームからサブミットされたのでダウンロード本処理を実行
			    $params = unserialize(base64_decode($req_params['exp-params']));
			    if($params === false) {
			        // パラメータ復元に失敗したらエラーメッセージを表示
			        $this->setTemplate('exporterror');
			        return $this->view;
			    }

			    // パラメータからデータを抽出
			    $list = $this->logic->createExportData();

				// ダウンロード設定
			    $file_name = sprintf('%s.csv', date('YmdHis'));
			    header('Content-Type: application/octet-stream');
			    header(sprintf('Content-Disposition: attachment; filename="%s"', $file_name));

			    // エクスポート実行
			    $this->logic->execExport($list);

			    return $this->response;
			default:
				// これら以外は不正遷移なので連携トップへリダイレクト
				return $this->_redirect('rwarvldata/index');
		}
	}

	/**
	 * 着荷確認データ連携 インポート内容確認アクション
	 */
	public function impconfirmAction() {
        // アップロードファイル確認
        $file = $_FILES[ self::UPLOAD_FIELD_NAME ];
        $fileName = $file['tmp_name'];
        if (isset($file['error']) && is_int($file['error'])) {
            try {
                // ファイルアップロードエラーチェック
                switch ($file['error']) {
                    case UPLOAD_ERR_OK:
                        // エラーなし
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // ファイル未選択
                        throw new \Exception("ファイルを選択してください。");
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        // 許可サイズを超過
                        throw new \Exception("ファイルサイズが大きすぎます。");
                    default:
                        throw new \Exception("その他エラーが発生しました。");
                }

                // アップロードファイルの文字エンコードを補正
                BaseIOUtility::convertFileEncoding($fileName, null, null, true);

                // データパース実行
                $results = $this->logic->createImportData($fileName);

                $this->view->assign('valid_data', $results['valid']);
                $this->view->assign('invalid_data', $results['invalid']);
                // importActionへのsubmitはvalidデータからorderseqsを取り出したエンコードデータ
                $this->view->assign('imp_data', base64_encode(serialize($this->array_column($results['valid'], "orderseqs"))));

                return $this->view;

            } catch (\Exception $e) {
                $errMsg = $e->getMessage();
            }
        }
        else {
            $errMsg = 'ファイルのアップロードに失敗しました。';
        }

        // エラーが発生した場合
        $this->setTemplate('index');
        $this->view->assign('impFileField', self::UPLOAD_FIELD_NAME);
        $this->view->assign('errorMessage', $errMsg);
        return $this->view;
	}

	/**
	 * PHP5.5のarray_column
	 */
	private function array_column($input = null, $columnKey = null, $indexKey = null) {
	    // Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc = func_num_args();
		$params = func_get_args();
		if ($argc < 2) {
		    trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
		    return null;
		}
		if (!is_array($params[0])) {
		    trigger_error(
			'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
			E_USER_WARNING
		    );
		    return null;
		}
		if (!is_int($params[1])
		    && !is_float($params[1])
		    && !is_string($params[1])
		    && $params[1] !== null
		    && !(is_object($params[1]) && method_exists($params[1], '__toString'))
		) {
		    trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
		    return false;
		}
		if (isset($params[2])
		    && !is_int($params[2])
		    && !is_float($params[2])
		    && !is_string($params[2])
		    && !(is_object($params[2]) && method_exists($params[2], '__toString'))
		) {
		    trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
		    return false;
		}
		$paramsInput = $params[0];
		$paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
		$paramsIndexKey = null;
		if (isset($params[2])) {
		    if (is_float($params[2]) || is_int($params[2])) {
			$paramsIndexKey = (int) $params[2];
		    } else {
			$paramsIndexKey = (string) $params[2];
		    }
		}
		$resultArray = array();
		foreach ($paramsInput as $row) {
		    $key = $value = null;
		    $keySet = $valueSet = false;
		    if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
			$keySet = true;
			$key = (string) $row[$paramsIndexKey];
		    }
		    if ($paramsColumnKey === null) {
			$valueSet = true;
			$value = $row;
		    } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
			$valueSet = true;
			$value = $row[$paramsColumnKey];
		    }
		    if ($valueSet) {
			if ($keySet) {
			    $resultArray[$key] = $value;
			} else {
			    $resultArray[] = $value;
			}
		    }
		}
		return $resultArray;
	}


	/**
	 * 着荷確認データ連携 インポート実行アクション
	 */
	public function importAction() {

        ini_set('max_execution_time', 0); //実行タイムアウトを無効にする

        $opId = $this->app->authManagerAdmin->getUserInfo()->OpId;

        $params = $this->getParams();

        // postされたデータを復元
        $imp_data = unserialize(base64_decode( isset($params['imp']) ? $params['imp'] : '' ));
		if(!$imp_data) throw new \Exception('invalid data posted !!');

		// count関数対策
        $imp_dataLen = 0;
        if(!empty($imp_data)) {
            $imp_dataLen = count($imp_data);
        }

        // 累計件数等を初期化→復元
        $total_counts = array(
                'imported' => 0,
                'processed' => 0,
                'rest' => $imp_dataLen,
                'total' => $imp_dataLen );
        $total_counts = isset($params['total_counts']) ? $params['total_counts'] : $total_counts;

        // ブロックサイズ（1度にインポートする件数）を定数から取得
        $block_size = self::IMPORT_BLOCK_SIZE;

        // 一番最初のリクエストはブロックサイズを1件にする
        if($total_counts['total'] && !$total_counts['processed']) $block_size = 1;
        // 2回目のリクエストは定数 - 1件にする
        if($total_counts['total'] > 1 && $total_counts['processed'] == 1) {
            $block_size = self::IMPORT_BLOCK_SIZE - 1;
        }

        // 確定したブロックサイズでインポート対象データを抽出
        $current_list = array_splice($imp_data, 0, $block_size);
        // インポート実行
		$updated = $this->logic->execImport($current_list, $opId);
		
		// count関数対策
        $imp_dataLen = 0;
        if(!empty($imp_data)) {
            $imp_dataLen = count($imp_data);
		}

		// count関数対策
		$current_listLen = 0;
        if(!empty($current_list)) {
            $current_listLen = count($current_list);
        }

        // 累計件数や残件数を更新
        $total_counts['imported'] += $updated;
        $total_counts['processed'] += $current_listLen;
        $total_counts['rest'] = $imp_dataLen;

        // 件数情報をアサイン
        $this->view->assign('imported_count', $updated);
        $this->view->assign('processed_count', $current_listLen);
        $this->view->assign('total_counts', $total_counts);

        if(!empty($imp_data)) {
            // 未処理データが残っている場合は残データをアサインして継続画面を表示
            $this->view->assign('imp_data', base64_encode(serialize($imp_data)));
            $this->setTemplate('impcontinue');
        }

        return $this->view;
    }

	/**
	 * 着荷確認データ連携 インポート完了アクション
	 */
    public function impdoneAction() {
        $params = $this->getParams();
        $this->view->assign('info', $params['total_counts']);
        return $this->view;
    }
}
