<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Logic\LogicHaipon;
use models\Logic\Haipon\Exporter\LogicHaiponExporterFormatter;
use Coral\Base\IO\BaseIOUtility;

ini_set('xdebug.var_display_max_data',-1);

/**
 * 配送でポン連携コントローラ
 */
class HaiponController extends CoralControllerAction {
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

    /** @var LogicHaipon */
    protected $logic;

    /**
     * コントローラ初期化
     */
    protected function _init() {
        $this
            ->addStyleSheet('../css/default02.css')
            ->addJavaScript('../js/prototype.js')
            ->addJavaScript('../js/json+.js')
            ->addJavaScript('../js/corelib.js');

        $this->setPageTitle("後払い.com - 着荷確認 - 配送でポン連携");

        $this->app = Application::getInstance();

        // 連携ロジック初期化
        $this->logic = new LogicHaipon($this->app->dbAdapter);
    }

//     /**
//      * 未定義のアクションがコールされた
//      */
//     public function __call($method, $args) {
//         // indexActionに委譲
//         $this->_forward('index');
//     }

    /**
     * 配ポン連携トップページアクション
     */
    public function indexAction() {
        $kinds = $this->logic->getDeliMethodKinds();
        $deliMethods = array();
        foreach($kinds as $kind => $label) {
            $deliMethods[$kind] = $this->logic->getDeliMethodsByKind($kind);
        }
        $this->view->assign('deliMethods', $deliMethods);
        $this->view->assign('impFileField', self::UPLOAD_FIELD_NAME);

        $this
            ->addJavaScript('../js/base.ui.js')
            ->addJavaScript('../js/base.ui.datepicker.js')
            ->addStyleSheet('../css/base.ui.datepicker.css');

        return $this->view;
    }

    /**
     * 配ポン連携 エクスポートアクション
     */
    public function exportAction() {
        $paramters = $this->getParams();

        $mode = isset( $paramters['mode'] ) ? $paramters['mode'] : 'prepare';
        switch($mode) {
            case 'prepare':
                // トップのフォームからsubmitされたらパラメータをエンコードして中継自動サブミットフォームを実行
                $params = $paramters['exp'];
                $this->view->assign('params', base64_encode(serialize($params)));
                $this->setTemplate('preexport');
                break;
            case 'exec':
                // 中継自動サブミットフォームからsubmitされたらダウンロード実行フォームを実行
                $this->view->assign('params', $paramters['exp-params']);
                break;
            case 'download':
                // ダウンロード実行フォームからサブミットされたのでダウンロード本処理を実行
                $params = unserialize(base64_decode($paramters['exp-params']));
                if($params === false) {
                    // パラメータ復元に失敗したらエラーメッセージを表示
                    $this->setTemplate('exporterror');

                    return $this->view;
                }

                // パラメータからデータを抽出
                $list = $this->logic->createExportData($params['deli_id'], $params['journal_date']);

                // ダウンロード設定
                $file_name = sprintf('haipon_export_%s.%s', date('Ymd-His'), $params['format']);
                header('Content-Type: application/octet-stream');
                header(sprintf('Content-Disposition: attachment; filename="%s"', $file_name));

                // エクスポート実行
                $this->logic->execExport($list, $params['format']);

                return $this->response;
            default:
                // これら以外は不正遷移なので連携トップへリダイレクト
                return $this->_redirect('haipon/index');
                break;
        }

        return $this->view;
    }

    /**
     * 配ポン連携 インポート内容確認アクション
     */
    public function impconfirmAction() {
        // アップロードファイルの文字エンコードを補正
        $file = $_FILES[ self::UPLOAD_FIELD_NAME ];
        $fileName = $file['tmp_name'];
        BaseIOUtility::convertFileEncoding($fileName, null, null, true);

        // データパース実行
        $results = $this->logic->createImportData($fileName);

        $this->view->assign('valid_data', $results['valid']);
        $this->view->assign('invalid_data', $results['invalid']);
            // importActionへのsubmitはエンコードしたvalidデータ
        $this->view->assign('imp_data', base64_encode(serialize($results['valid'])));

        return $this->view;
    }

    /**
     * 配ポン連携 インポート実行アクション
     */
    public function importAction() {
        $opId = $this->app->authManagerAdmin->getUserInfo()->OpId;

        // postされたデータを復元
        $imp_data = unserialize(base64_decode($this->params()->fromPost('imp', '')));
        if(!$imp_data) throw new \Exception('invalid data posted !!');

        // count関数対策
        $rest = 0;
        if (!empty($imp_data)){
            $rest = count($imp_data);
        }

        // 累計件数等を初期化→復元
        $total_counts = array(
                              'imported' => 0,
                              'processed' => 0,
                              'rest' => $rest,
                              'total' => $rest );
        $total_counts = $this->params()->fromPost('total_counts', $total_counts);

        $db = $this->logic->getAdapter();
        $db->getDriver()->getConnection()->beginTransaction();
        try {
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
//throw new Exception('oops !!');
            $db->getDriver()->getConnection()->commit();

            // 累計件数や残件数を更新
            // count関数対策
            $processed = 0;
            if (!empty($current_list)){
                $processed = count($current_list);
            }	
            // count関数対策
            $rest = 0;
            if (!empty($imp_data)){
                $rest = count($imp_data);
            }

            $total_counts['imported'] += $updated;
            $total_counts['processed'] += $processed;
            $total_counts['rest'] = $rest;

            // 件数情報をアサイン
            $this->view->assign('imported_count', $updated);
            $this->view->assign('processed_count', $processed);
            $this->view->assign('total_counts', $total_counts);
            // count関数対策
            if(!empty($imp_data)) {
                // 未処理データが残っている場合は残データをアサインして継続画面を表示
                $this->view->assign('imp_data', base64_encode(serialize($imp_data)));
                $this->setTemplate('impcontinue');
            }
        } catch(\Exception $err) {
            $db->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        return $this->view;
    }

    /**
     * 配ポン連携 インポート完了アクション
     */
    public function impdoneAction() {
        $this->view->assign('info', $this->params()->fromPost('total_counts'));

        return $this->view;
    }

}
