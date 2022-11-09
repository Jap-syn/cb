<?php
namespace oemmember\Controller;

use oemmember\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableUser;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use models\Logic\LogicTemplate;
use models\Logic\LogicCancel;
use Coral\Coral\CsvHandler\CoralCsvHandlerLine;
use Zend\Config\Reader\Ini;
use Zend\Session\Container;
use models\Logic\OrderCancelException;
use Coral\Coral\Mail\CoralMail;

class OrderCancelController extends CoralControllerAction {
    const UPLOAD_FIELD_NAME = 'Csv_File';

    /**
     * ビューコンポーネントのルートディレクトリパス
     *
     * @var string
     */
    protected $_componentRoot = './application/views/components';

    /**
     * DBアダプタ
     *
     * @var Abstract
     */
    protected $dbAdapter;

    /**
     * ログイン中の事業者アカウント情報
     *
     * @var mixed
     */
    protected $userInfo;

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * テンプレートSEQ
     * @var TemplateSeq
     */
    private $templateSeq;


    /**
     * クラス固有の初期化処理
     */
    protected function _init() {
        $this->app = Application::getInstance();

        $this->dbAdapter = $this->app->dbAdapter;

        // ビューへスタイルシートとJSを追加
        $this->addStyleSheet( '../../oemmember/css/members.css' )
            ->addStyleSheet( '../../oemmember/css/index.css' )
            ->addStyleSheet( '../../oemmember/css/ordercancel.css' );

        // メニュー情報をマスタから取得
        $menu_info = $this->app->getMenuInfo();

        foreach( $menu_info as $key => $info ) {
            $this->view->assign( $key, $info );
        }

        // ログイン中のアカウント情報取得
        $this->userInfo = $this->app->authManager->getUserInfo();

        $this->view->assign( 'cssName', "ordercancel" );
    }

    /**
     * registCsvアクション。一括注文キャンセルフォームを表示する
     *
     */
    public function registCsvAction() {
        $this->setCommonScripts();
        $this->setPageTitle('一括注文キャンセル（CSV）');
        $this->view->assign( 'field_name', self::UPLOAD_FIELD_NAME );

        return $this->view;
    }

    /**
     * confirmCsvアクション。一括注文キャンセルCSVの内容確認画面を表示する
     *
     */
    public function confirmCsvAction() {
        $this->setCommonScripts();
        $this
        ->addStyleSheet( '../../oemmember/css/csv_table.css' )
        ->addStyleSheet( '../../oemmember/css/ordercancel_confirm_csv.css' );

        $csv = $_FILES[ self::UPLOAD_FIELD_NAME ]['tmp_name'];

        if( ! preg_match( '/\.xl.$/i', $_FILES[ self::UPLOAD_FIELD_NAME ]['name'] ) && $csv != "" ) {
            $templateId = 'CKA01009_1';    // 注文キャンセル申請（CSV）
            $templateClass = 2;
            $seq = $this->userInfo->EnterpriseId;
            $templatePattern = 0;

            // CSV解析実行
            $logicTemplate = new LogicTemplate( $this->dbAdapter );
            $rows = $logicTemplate->convertFiletoArray( $csv, $templateId, $templateClass, $seq, $templatePattern );

            $success = $rows == false ? false : true;

            // 検証実施
            if( $success != false ) {
                // TemplateFieldを取得
                $obj = new TableTemplateHeader( $this->dbAdapter );
                $templateSeq = $obj->getTemplateSeq( $templateId, $templateClass, $seq );
                $templateHeader = $obj->find( $templateSeq )->current();

                $obj = new TableTemplateField( $this->dbAdapter );
                $templateField = ResultInterfaceToArray( $obj->get( $templateSeq ) );

                $results = $this->validate( $rows );

                if( $templateHeader['TitleClass']  == 1 ) {
                    foreach( $templateField as $template ) {
                        $headerRow[] = $template['LogicalName'];
                    }
                }
                else if( $templateHeader['TitleClass'] == 2 ) {
                    foreach( $templateField as $template ) {
                        $headerRow[] = $template['PhysicalName'];
                    }
                }
                else {
                    $headerRow = null;
                }

                // ビューのタイトルを設定
                if(empty($results['error'])) {
                    $success = true;
                    $this->setPageTitle( '一括注文キャンセル　CSV登録確認' );
                }
                else {
                    $success = false;
                    $this->setPageTitle( '一括注文キャンセル　CSV登録エラー' );
                }

                // 解析結果をビューにアサイン
                $rowsCount = 0;
                if (!empty($rows)) {
                    $rowsCount = count($rows);
                }
                $this->view->assign( 'templateField', $templateField );
                $this->view->assign( 'headerRow', $headerRow );
                $this->view->assign( 'validRows', $results['valid'] );
                $this->view->assign( 'errorRows', $results['error'] );
                $this->view->assign( 'allData', $rows );
                $this->view->assign( 'totalCount', $rowsCount );
                $this->view->assign( 'isToDo', 0);
            }
            else {
                $success = false;
                $this->setPageTitle( '一括注文キャンセル　CSV登録エラー' );
                $this->view->assign( 'templateField', new \stdClass() );
                $this->view->assign( 'isToDo', 0);
                $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                        array( 0 => array( 'ファイル形式' => $logicTemplate->getErrorMessage() ) ),
                        0,
                        CoralCsvHandlerLine::TYPE_ERROR
                    ) )
                );
            }
        }
        else {
            $success = false;
            $this->setPageTitle( '一括注文キャンセル　CSV登録エラー' );
            $this->view->assign( 'templateField', new \stdClass() );
            $this->view->assign( 'errorRows', array(
                new CoralCsvHandlerLine(
                    array( 0 => array( 'ファイル形式' => 'ファイル形式が適切ではありません。CSVファイルを登録してください。' ) ),
                    0,
                    CoralCsvHandlerLine::TYPE_ERROR
                ) )
            );
            $this->view->assign( 'isToDo', 0);
        }

        // エラーがなかったのでセッションオブジェクトに結果を保存
        if( $success ) {
            $rows = array();
            foreach( $results['valid'] as $row ) {
                $rows[] = (object)$row->getData();
            }
            $session = $this->getSessionStorage();
            $session->posts = $rows;
        }

        return $this->view;
    }

    /**
     * saveCsvアクション。一括注文キャンセルデータをDBへ登録する
     *
     */
    public function saveCsvAction() {
        // セッションから入力データを復元
        $session = $this->getSessionStorage();
        if( ! isset( $session->posts ) ) throw new \Exception( 'データが登録されていません。' );

        $posts = $session->posts;
        // セッションデータを廃棄
        unset( $session->posts );

        $canceller = new LogicCancel( Application::getInstance()->dbAdapter );

        // ユーザーIDの取得
        $obj = new TableUser( $this->dbAdapter );
        getUserInfoForMember( $this->app, $userClass, $seq );
        $userId = $obj->getUserId( $userClass, $seq );

        foreach( $posts as $key => $post ) {
            $orderSeq = $post->OrderSeq;
            $reason = $post->Note;
            $row = $this->app->dbAdapter->query(" SELECT KeyCode AS ReasonCode FROM M_Code WHERE CodeId = 90 AND KeyContent = :KeyContent "
            )->execute(array(':KeyContent' => $post->CancelReason))->current();
            $reasonCode = ($row) ? $row['ReasonCode'] : 1;

            try {
                // トランザクション開始
                //add $isToDo
                $isToDo = 0;
                if ($canceller->_usedTodo2Pay($orderSeq) == true) {
                    $isToDo = 1;
                }
                $result = $canceller->applies( (int)$orderSeq, $reason, $reasonCode, 0, true, $userId, $isToDo, Application::getInstance()->sbpsLogger);

                if (strlen( $result ) != 0) {
                    $res = $result;
                    $errorRows['errCode'] = $result;
                    if (strpos($result, "__sbps") !== false) {
                        $temp = explode('__sbps', $result);
                        $mes = $canceller->_SBPaymentMakeErrorInfoForAjax($temp[0]);
                        $res = "SBPS側でエラーが発生しました。". $mes ."(". $temp[0] .") ";
                        $errorRows['errCode'] = $temp[0];
                    }
                    $errorRows['OrderId'] = $post->OrderId;
                    $errorRows['Ent_OrderId'] = $post->Ent_OrderId;
                    $errorRows['NameKj'] = $post->NameKj;
                    $errorRows['errMessage'] = $res;
                    $this->setPageTitle( '一括注文キャンセル　CSV登録エラー' );
                    $this->view->assign( 'templateField', new \stdClass() );
                    $this->view->assign( 'errorRows', array(
                            new CoralCsvHandlerLine(
                                $errorRows,
                                $key,
                                CoralCsvHandlerLine::TYPE_ERROR
                            ) )
                    );
                    $this->view->assign( 'isToDo', 1);
                    $this->view->assign( 'totalCount', count($posts));
                    $this->setCommonScripts();
                    $this
                        ->addStyleSheet( './css/csv_table.css' )
                        ->addStyleSheet( './css/ordercancel_confirm_csv.css' );

                    $this->setTemplate( 'confirmCsv' );
                    return $this->view;
                } else {
                    if ( $isToDo == 1) {
                        // キャンセル確定メール送信
                        CoralMail::create( Application::getInstance()->dbAdapter, Application::getInstance()->smtpServer )->SendCancelMail($orderSeq, $userId);
                    }
                }
            } catch(OrderCancelException $cancelError) {
                $this->setPageTitle( '一括注文キャンセル　CSV登録エラー' );
                $this->view->assign( 'templateField', new \stdClass() );
                $this->view->assign( 'errorRows', array(
                    new CoralCsvHandlerLine(
                        array( 0 => array( 'DB更新' => 'この注文は、他の端末からキャンセル申請されているか、または、キャンセル不可の注文です。' ) ),
                        0,
                        CoralCsvHandlerLine::TYPE_ERROR
                    ) )
                );
                $postsCount = 0;
                if (!empty($posts)) {
                    $postsCount = count($posts);
                }
                $this->view->assign( 'isToDo', 0);
                $this->view->assign( 'totalCount', $postsCount);
                $this->setCommonScripts();
                $this
                ->addStyleSheet( '../../oemmember/css/csv_table.css' )
                ->addStyleSheet( '../../oemmember/css/ordercancel_confirm_csv.css' );

                $this->setTemplate( 'confirmCsv' );

                return $this->view;
            }
        }

        return $this->_redirect( 'ordercancel/completeCsv' );
    }

    /**
     * completeCsvアクション。一括注文キャンセル完了画面を表示する
     *
     */
    public function completeCsvAction() {
        $this->setCommonScripts();
        $this->setPageTitle( '一括注文キャンセル　登録完了' );

        return $this->view;
    }

    /**
     * downloadアクション。雛形のテンプレートCSVをクライアントへ送信する
     *
     */
    public function downloadAction() {
        $templateId = 'CKA01009_1';    // 注文キャンセル申請（CSV）
        $templateClass = 2;
        $seq = $this->userInfo->EnterpriseId;
        $templatePattern = 0;

        $logicTemplate = new LogicTemplate( $this->dbAdapter );
        $response = $logicTemplate->convertArraytoResponse( '', sprintf( 'Od_cancel_%s.csv', date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

    /**
     * 検証
     *
     * @param array $datas 検証データ
     *
     * @return array 検証結果
     */
    private function validate( $datas ) {
$sql = <<<EOQ
 SELECT o.OrderSeq
 ,      o.OrderId
 ,      c.NameKj
 ,      o.Ent_OrderId
 ,      l.CancelReasonCode
 ,      l.CancelReason
 ,      o.DataStatus
 ,      o.CloseReason
 ,      o.CombinedClaimTargetStatus
 ,      o.Cnl_Status
 ,      o.Cnl_CantCancelFlg
 ,      o.P_OrderSeq
 ,      o.UseAmount
 ,      e.AppFormIssueCond
 ,      ato.CreditTransferRequestFlg
FROM T_Order o
 INNER JOIN T_Customer c
         ON o.OrderSeq = c.OrderSeq
 LEFT OUTER JOIN T_Cancel l
         ON o.OrderSeq = l.OrderSeq
 LEFT OUTER JOIN T_Enterprise e
         ON e.EnterpriseId = o.EnterpriseId
 LEFT OUTER JOIN AT_Order ato
         ON ato.OrderSeq = o.OrderSeq
 WHERE o.EnterpriseId = :EnterpriseId
EOQ;
        $stm = $this->dbAdapter->query( $sql );
        $prm = array(
                ':EnterpriseId' => $this->userInfo->EnterpriseId,
        );
        $ordersearch = ResultInterfaceToArray( $stm->execute( $prm ) );

        // キャンセル理由
        $sql  = ' SELECT KeyCode ';
        $sql .= ' ,      KeyContent ';
        $sql .= '        FROM M_Code ';
        $sql .= '        WHERE CodeId = 90 ';
        $stm = $this->dbAdapter->query( $sql );
        $cancelReasons = ResultInterfaceToArray( $stm->execute( null ) );

        foreach( $cancelReasons as $cancelReason ) {
            $cancelReasonCodeList[$cancelReason['KeyCode']] = $cancelReason['KeyContent'];
        }

        // 検索対象作成
        $searchOrderId = array();
        $searchNameKj = array();
        $searchCancelReasonCode = array();
        $searchEnt_OrderId = array();
        $searchCancelReason = array();

        foreach( $ordersearch as $order ) {
            $searchOrderId[] = $order['OrderId'];
            $searchNameKj[] = $order['NameKj'];
            $searchCancelReasonCode[] = $order['CancelReasonCode'];
            if( array_key_exists( 'Ent_OrderId', $order ) ) {
                $searchEnt_OrderId[] = $order['Ent_OrderId'];
            }
            if( array_key_exists( 'CancelReason', $order ) ) {
                $searchCancelReason[] = $order['CancelReason'];
            }
        }

        $caches = array();  // OrderId重複チェック
        $pOrderCaches = array();    // 61:一部入金 or 91:ｸﾛｰｽﾞ[入金済み正常クローズ]の取りまとめ注文の重複チェック
        foreach( $datas as $i => $data ) {
            $errors = array();
            $detecting = false;
            $doubling = false;

            $key = 'OrderId';
            $value = $data[$key];

            // 20190117 Add 注文ID：未入力かつ、任意注文ID：入力ありの場合のみ処理追加
            if(empty($value) && !empty($data['Ent_OrderId'])){
                // 任意注文IDから注文IDを取得する。
                $sql = <<<EOQ
 SELECT OrderId
 FROM T_Order
 WHERE Ent_OrderId = :EntOrderId
EOQ;
                $stm = $this->dbAdapter->query( $sql );
                $prm = array(
                        ':EntOrderId' => $data['Ent_OrderId'],
                );
                $orderidlist = ResultInterfaceToArray( $stm->execute( $prm ) );

                // 任意注文IDが紐付かなかった場合、エラー
                if(empty($orderidlist)){
                    // 重複ではないが読み飛ばしのため重複フラグをtrueにする。
                    $doubling = true;
                    $errors[] = array(
                            '注文ID' => "注文ID '$value' に一致するデータが見つかりません。"
                    );
                } else {
                    // 複数件取得できた場合は、エラー
                    if(count($orderidlist) > 1){
                        // 重複ではないが読み飛ばしのため重複フラグをtrueにする。
                        $doubling = true;
                        $errors[] = array(
                                '任意注文番号' => "注文データが複数存在します。注文IDを指定してください。"
                        );
                    }
                    // 1件の場合、注文IDを上書きして後続処理を行う。
                    else {
                        $value = $orderidlist[0]['OrderId'];
                        $data[$key] = $orderidlist[0]['OrderId'];
                    }
                }
            }
            // OrderId重複チェック
            foreach( $caches as $cache ) {
                if( $cache == $value ) {
                    $doubling = true;
                    $errors[] = array(
                        '注文ID' => "注文ID '$value' が複数行存在します。"
                    );
                    break;
                }
            }
            if( !$doubling ){
                foreach( $searchOrderId as $j => $orderId ) {
                    // 該当のOrderIdが見つかった
                    if( $orderId == $value ) {
                        // キャッシュに保持
                        $caches[] = $value;
                        $detecting = true;
                        break;
                    }
                }
                // 最後まで該当のOrderIdが見つからなかった
                if( !$detecting ) {
                    $errors[] = array(
                        '注文ID' => "注文ID '$value' に一致するデータが見つかりません。"
                    );
                }
            }

            $key = 'NameKj';
            $value = $data[$key];
            // OrderIdとNameKjとの整合性チェック
            if( empty( $errors ) && $searchNameKj[$j] != $value ) {
                $errors[] = array(
                    '氏名' => "氏名 '$value' と 注文ID '" . $data['OrderId'] . "' が対応しません。"
                );
            }

            $key = 'CancelReason';
            $value = $data[$key];
            if (strlen($value) > 0) {
                $detecting = false;
                foreach( $cancelReasonCodeList as $code => $content ) {
                    // Codeで指定
                    if( $code == $value ) {
                        $detecting = true;
                        $data[$key] = $content;
                        break;
                    }
                    // Contentで指定
                    if( $content == $value ) {
                        $detecting = true;
                        break;
                    }
                }
                // 最後まで該当のCancelReasonCodeが見つからなかった
                if( !$detecting ) {
                    $errors[] = array(
                        'キャンセル理由' => "キャンセル理由 '$value' に一致するデータが見つかりません。"
                    );
                }
            }
            else {
                $errors[] = array(
                        'キャンセル理由' => "キャンセル理由は必須項目です。"
                );
            }

            // エラーが存在した
             if (!empty($errors)) {
                $errors['_raw_data'] = $data;
                $results['error'][] = new CoralCsvHandlerLine( $errors, $i, CoralCsvHandlerLine::TYPE_ERROR );
            }
            else {
                if( (   ( $ordersearch[$j]['DataStatus'] < 90 && $ordersearch[$j]['DataStatus'] != 61)
                     || ( $ordersearch[$j]['DataStatus'] == 91 && $ordersearch[$j]['CloseReason'] != 2 && $ordersearch[$j]['CloseReason'] != 3 )
                     || ( $ordersearch[$j]['DataStatus'] == 61)
                    )
                  && $ordersearch[$j]['CombinedClaimTargetStatus'] != 11 && $ordersearch[$j]['CombinedClaimTargetStatus'] != 12
                  && $ordersearch[$j]['Cnl_Status'] == 0 && $ordersearch[$j]['Cnl_CantCancelFlg'] != 1 ) {
					// 口座振替0円請求に対するキャンセル不可 
					// 事業者マスタ：請求金額0円時 & 注文情報：口座振替利用する & 注文ステータス：入金クローズ
					if($ordersearch[$j]['AppFormIssueCond'] == 2
						&& $ordersearch[$j]['CreditTransferRequestFlg'] != 0
						&& $ordersearch[$j]['DataStatus'] == 91
						&& $ordersearch[$j]['CloseReason'] == 1
						&& $ordersearch[$j]['UseAmount'] == 0) {
							$errors[] = array(
								'注文ID' => "この注文は、他の端末からキャンセル申請されているか、または、キャンセル不可の注文です。"
								);
							$errors['_raw_data'] = $data;
							$results['error'][] = new CoralCsvHandlerLine( $errors, $i, CoralCsvHandlerLine::TYPE_ERROR );
					} else {
                        // 取りまとめ注文
                        $pOrderChk = false;
                        if (($ordersearch[$j]['CombinedClaimTargetStatus'] = 91 || $ordersearch[$j]['CombinedClaimTargetStatus'] = 92)
                        ) {
                            // 親注文の重複は不可
                            if (in_array($ordersearch[$j]['P_OrderSeq'], $pOrderCaches)) {
                                $pOrderChk = true;
                            }else {
                                $pOrderCaches[] = $ordersearch[$j]['P_OrderSeq'];
                            }
                        }
                        // 通常注文、重複していない取りまとめ注文はOK
                        if ( !$pOrderChk ) {
                            $data['OrderSeq'] = $ordersearch[$j]['OrderSeq'];
                            $results['valid'][] = new CoralCsvHandlerLine( $data, $i, CoralCsvHandlerLine::TYPE_DATA );
                        }
                        else {
                            $errors[] = array(
                                    '注文ID' => "取りまとめ注文は、1つだけ指定してください。"
                            );
                            $errors['_raw_data'] = $data;
                            $results['error'][] = new CoralCsvHandlerLine( $errors, $i, CoralCsvHandlerLine::TYPE_ERROR );
                        }
                    }
                }
                else {
                    $errors[] = array(
                        '注文ID' => "この注文は、他の端末からキャンセル申請されているか、または、キャンセル不可の注文です。"
                    );
                    $errors['_raw_data'] = $data;
                    $results['error'][] = new CoralCsvHandlerLine( $errors, $i, CoralCsvHandlerLine::TYPE_ERROR );
                }
            }
        }
        return $results;
    }

    /**
     * ビューにJavaScriptのリンクを設定
     *
     * @return CoralControllerAction
     */
    private function setCommonScripts() {
        return $this
        ->addJavaScript( '../../js/prototype.js' )
        ->addJavaScript( '../../js/bytefx.js' )
        ->addJavaScript( '../../js/json+.js' )
        ->addJavaScript( '../../js/corelib.js' )
        ->addJavaScript( '../../js/json_format.js' )
        ->addJavaScript( '../../js/base.ui.js' )
        ->addJavaScript( '../../js/base.ui.tableex.js' )
        ->addJavaScript( '../../js/form_validator.js' );
    }

    /**
     * このコントローラクラス固有のセッション名前空間を取得する
     *
     * @return Container
     */
    private function getSessionStorage() {
        return new Container( Application::getInstance()->getApplicationId() . '_OrderCancelData' );
    }

}
