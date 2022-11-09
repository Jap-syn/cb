<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableUser;
use models\Table\TableOrder;
use models\Table\TableCustomer;
use models\Table\TableRemindHistory;
use models\Logic\LogicTemplate;
use Coral\Coral\CoralCodeMaster;

class ExternalDemandController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * 一括登録時のファイルフィールドのname属性
     *
     * @var string
    */
    const UPLOAD_FIELD_NAME = 'Csv_File';

    /**
     * アプリケーションオブジェクト
     * @var Application
    */
    private $app;

    /**
     * クラス固有の初期化処理
     */
    protected function _init()
    {
        $this->app = Application::getInstance();

        $this
            ->addStyleSheet( '../css/default02.css' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/json.js' );

        $this->setPageTitle( "後払い.com - 外部督促出力ファイルの取り込み(SMS)" );
    }

    /**
     * registアクション
     *
    */
    public function registAction()
    {
        return $this->view;
    }

    /**
     * confirmアクション
     *
    */
    public function confirmAction()
    {
        $errors = array();

        // CSVファイル取り込み
        $csv = $_FILES[ self::UPLOAD_FIELD_NAME ]['tmp_name'];

        // 拡張子チェック
        if( strrchr( $_FILES[ self::UPLOAD_FIELD_NAME ]['name'], '.' ) === '.csv' && $csv != "" ) {
            $templateId = 'CKI09078_1'; // 外部督促用データ取込
            $templateClass = 0;
            $seq = 0;
            $templatePattern = 0;

            // CSV解析実行
            $logicTemplate = new LogicTemplate( $this->app->dbAdapter );
            $rows = $logicTemplate->convertFiletoArray( $csv, $templateId, $templateClass, $seq, $templatePattern );

            // ロジック解析失敗
            if( $rows == false ) {
                $this->view->assign( 'error', $logicTemplate->getErrorMessage() );
                $this->setTemplate( 'error' );
                return $this->view;
            }

            // NOTE : 特別なバリデーションは行わない(20150930_1140)

            // データ更新

            // ユーザーIDの取得
            $obj = new \models\Table\TableUser( $this->app->dbAdapter );
            $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            $mdlac = new \models\Table\TablePushSms($this->app->dbAdapter);
            $mdlo = new TableOrder( $this->app->dbAdapter );

            $nonsetlist = array();
            $regCount = 0;
            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
            try {
                foreach( $rows as $row ) {

                    // [Status=0]のレコードの取得
                    $row_ac = $this->app->dbAdapter->query(" SELECT * FROM T_PushSms WHERE Status = 0 AND PhoneNumber = :PhoneNumber LIMIT 1 "
                        )->execute(array(':PhoneNumber' => str_replace('-', '', $row['CallPhoneNumber'])))->current();

                    if ($row_ac) {

                        // 開始日時が取得出来ない場合は、督促日にシステム日付をセットする(20151127)
                        $sendDateTime = (IsValidDate($row['SendDateTime'])) ? date('Y-m-d', strtotime($row['SendDateTime'])) : date('Y-m-d');

                        // (UPDATE:T_PushSms)
                        $mdlac->saveUpdate(
                            array(
                                'SendDateTime' => $sendDateTime,
                                'UseDateTime' => $row['UseDateTime'],
                                'CallPhoneNumber' => $row['CallPhoneNumber'],
                                'Caririer' => $row['Caririer'],
                                'IncMessageNumber' => $row['IncMessageNumber'],
                                'IncMessage' => $row['IncMessage'],
                                'State' => $row['State'],
                                'ErrorCode' => $row['ErrorCode'],
                                'DeliveryState' => $row['DeliveryState'],
                                'DeliveryErrorCode' => $row['DeliveryErrorCode'],
                                'Status' => 1,
                                'UpdateId' => $userId,
                                'UpdateDate' => date('Y-m-d H:i:s'),
                            ) , $row_ac['Seq']);

                        // (UPDATE:T_Order)
                        $mdlo->saveUpdate(
                            array(
                                'FinalityRemindDate' => $sendDateTime,
                                'FinalityRemindOpId' => $this->app->authManagerAdmin->getUserInfo()->OpId,
                                'UpdateId' => $userId,
                                'UpdateDate' => date('Y-m-d H:i:s'),
                            ) , $row_ac['OrderSeq']);

                        $regCount++;


                    }
                    else {
                        // T_PushSmsに更新対象の注文IDがない場合は、リストへ積み上げる
                        $nonsetlist[] = $row['CallPhoneNumber'];
                    }
                }

                $this->app->dbAdapter->getDriver()->getConnection()->commit();

                $this->view->assign( 'importInfo', $regCount . '件の外部督促結果を取り込みました。' );
                $this->view->assign( 'nonsetlist', $nonsetlist );
                $this->setTemplate( 'completion' );
                return $this->view;
           }
            catch( \Exception $e ) {
                $this->app->dbAdapter->getDriver()->getConnection()->rollback();

                $this->view->assign( 'error',$e->getMessage() );
                $this->setTemplate( 'error' );
                return $this->view;
            }
        }
        else
        {
            $this->view->assign( 'error', 'ファイル形式が適切ではありません。<br />CSVファイルを登録してください' );
            $this->setTemplate( 'error' );
            return $this->view;
        }
    }

    /**
     * errorアクション
     *
     */
    public function errorAction() {

        return $this->view;
    }

    /**
     * completionアクション
     *
    */
    public function completionAction() {

        return $this->view;
    }
}
