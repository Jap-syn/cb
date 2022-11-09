<?php
namespace oemmypage\Controller;

use oemmypage\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Json\Json;
use models\Table\TableMailSubject;
use models\Table\TableMypageCustomer;
use models\Table\TableMypageToBackIF;

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
     * コントローラ初期化
     */
    protected function _init() {
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
    }

    /**
     * リクエスト情報からレスポンスモードを設定。合わせてContent-Typeヘッダを出力。
     */
    protected function _initResponse() {
        // リクエスト情報からレスポンスモードを決定
        $params = $this->getParams();
        $this->_responseMode = isset($params['mode']) ? $params['mode'] : self::RESPONSE_MODE_JSON;
        // レスポンスモードに合わせてContent-Typeヘッダを出力
        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', $this->_responseContentType[ $this->_responseMode ] );
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

    /**
     * このアプリケーションが使用するデータベースへの接続情報を取得するAPIメソッド
     */
    /*
    public function dbInfoAction() {
        // レスポンス初期化
        $this->_initResponse();

        $configPath = Application::getInstance()->configRoot;

        $inidata = array();
        $file = $configPath . '/config.ini';
        if (file_exists($file))
        {
            $reader = new Ini();
            $inidata = $reader->fromFile($file);
        }
        $ini = $inidata['database'];

        $ini_array = $ini;

        $this->writeResponse($ini_array);

        return $this->getResponse();
    }
    */

    /**
     * メールサブジェクト登録API
     */
    public function mailSubjectRegistAction() {
        // レスポンス初期化
        $this->_initResponse();

        // パラメータ取得
//        $params = $this->getParams();
        $params = $this->params()->fromPost();  // POSTパラメータのみ使用

        $db = Application::getInstance()->dbAdapter;
        $mdlms = new TableMailSubject($db);

        $result = 0;
        $message = '';
        try {
            // トランザクション開始
            $db->getDriver()->getConnection()->beginTransaction();

            $i = 0;
            while (isset($params['MailSubject' . $i])) {
                // メールサブジェクト登録
                $mdlms->saveNew(array(
                    'MailSubject' => $params['MailSubject' . $i],
                    'FailAttfileFlg' => $params['FailAttfileFlg' . $i],
                    'FailExtfileFlg' => $params['FailExtfileFlg' . $i],
                    'FailNameFlg' => 0,
                    'FailAddressFlg' => 0,
                    'FailBirthFlg' => 0,
                    'ChkFlg' => 0,
                    'ListPrintFlg' => 0,
                    'AttfileName' => $params['AttfileName' . $i],
                ));

                $i++;
            }

            // コミット
            $db->getDriver()->getConnection()->commit();
        }
        catch (\Exception $e) {
            // ロールバック
            $db->getDriver()->getConnection()->rollBack();

            // エラー情報
            $result = 9;
            $message = $e->getMessage();
        }

        // レスポンス出力
        $res = array(
            'Result' => $result,
            'Message' => $message
        );
        $this->writeResponse($res);

        // 結果を返却
        return $this->getResponse();
    }

    /**
     * 免許証チェック対象データ取得API
     */
    public function getLicenseCheckDataAction() {
        // レスポンス初期化
        $this->_initResponse();

        // パラメータ取得
//        $params = $this->getParams();
        $params = $this->params()->fromPost();  // POSTパラメータのみ使用

        $db = Application::getInstance()->dbAdapter;
        $mdlms = new TableMailSubject($db);

        $data = ResultInterfaceToArray($mdlms->getLicenseCheckData());
        $res = array(
            'LicenseCheckData' => $data
        );

        // レスポンス出力
        $this->writeResponse($res);

        // 結果を返却
        return $this->getResponse();
    }

    /**
     * 免許証チェック結果反映API
     */
    public function setLicenseCheckResultAction() {
        // レスポンス初期化
        $this->_initResponse();

        // パラメータ取得
//        $params = $this->getParams();
        $params = $this->params()->fromPost();  // POSTパラメータのみ使用

        $db = Application::getInstance()->dbAdapter;
        $mdlms = new TableMailSubject($db);
        $mdlmc = new TableMypageCustomer($db);
        $mdlmbi = new TableMypageToBackIF($db);

        $result = 0;
        $message = '';
        try {
            // トランザクション開始
            $db->getDriver()->getConnection()->beginTransaction();

            $i = 0;
            while (isset($params['MailSubject' . $i])) {
                // メールサブジェクト更新
                $mdlms->saveUpdate(array(
                        'FailNameFlg' => $params['FailNameFlg' . $i],
                        'FailAddressFlg' => $params['FailAddressFlg' . $i],
                        'FailBirthFlg' => $params['FailBirthFlg' . $i],
                        'ChkFlg' => 1,
                    )
                    , $params['MailSubject' . $i]
                );

                // 顧客番号が設定されている場合のみ実行
                if ($params['CustomerId' . $i] > 0) {
                    $customerId = $params['CustomerId' . $i];

                    // 基幹反映指示インタフェース登録
                    //   3：身分証アップロードフラグ更新指示
                    $mdlmbi->saveNew(array(
                            'Status' => 0,
                            'Reason' => 0,
                            'IFClass' => 3,
                            'IFData' => Json::encode(array('CustomerId' => $customerId)),
                            'OrderSeq' => null,
                            'ManCustId' => null,
                            'CustomerId' => $customerId,
                            'ValidFlg' => 1,
                        )
                    );
                }

                $i++;
            }

            // コミット
            $db->getDriver()->getConnection()->commit();
        }
        catch (\Exception $e) {
            // ロールバック
            $db->getDriver()->getConnection()->rollBack();

            // エラー情報
            $result = 9;
            $message = $e->getMessage();
        }

        // レスポンス出力
        $res = array(
                'Result' => $result,
                'Message' => $message
        );
        $this->writeResponse($res);

        // 結果を返却
        return $this->getResponse();
    }

    /**
     * 免許証チェックエラー取得API
     */
    public function getLicenseCheckErrorAction() {
        // レスポンス初期化
        $this->_initResponse();

        // パラメータ取得
//        $params = $this->getParams();
        $params = $this->params()->fromPost();  // POSTパラメータのみ使用

        $db = Application::getInstance()->dbAdapter;
        $mdlms = new TableMailSubject($db);

        $data = ResultInterfaceToArray($mdlms->getLicenseCheckError($params));
        $res = array(
                'LicenseCheckError' => $data
        );

        // レスポンス出力
        $this->writeResponse($res);

        // 結果を返却
        return $this->getResponse();
    }

    /**
     * エラーリスト印刷済フラグ更新API
     */
    public function listPrintFlgUpdateAction() {
        // レスポンス初期化
        $this->_initResponse();

        // パラメータ取得
//        $params = $this->getParams();
        $params = $this->params()->fromPost();  // POSTパラメータのみ使用

        $db = Application::getInstance()->dbAdapter;
        $mdlms = new TableMailSubject($db);

        $result = 0;
        $message = '';
        try {
            if (isset($params['MailSubjectList']) && strlen($params['MailSubjectList']) > 0) {
                // トランザクション開始
                $db->getDriver()->getConnection()->beginTransaction();

                // カンマ区切りで格納されているため、分解して更新処理
                $mailSubjectList = explode(',', $params['MailSubjectList']);
                foreach ($mailSubjectList as $mailSubject) {
                    // メールサブジェクト更新
                    $mdlms->saveUpdate(array(
                            'ListPrintFlg' => 1,
                    )
                    , $mailSubject
                    );
                }

                // コミット
                $db->getDriver()->getConnection()->commit();
            }
        }
        catch (\Exception $e) {
            // ロールバック
            $db->getDriver()->getConnection()->rollBack();

            // エラー情報
            $result = 9;
            $message = $e->getMessage();
        }

        // レスポンス出力
        $res = array(
                'Result' => $result,
                'Message' => $message
        );
        $this->writeResponse($res);

        // 結果を返却
        return $this->getResponse();
    }
}
