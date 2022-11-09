<?php
namespace api\Controller;

use api\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableNotificationManage;
use Zend\Json\Json;
use Zend\Log\Logger;

/**
 * API共通コントローラ
 */
class CoralApiController extends CoralControllerAction {

    /**
     * トークン作成の試行回数
     * @var int
     */
    const TOKEN_RAND_CHARANGE = 5;

    /**
     * トークンの長さ
     * @var int
     */
    const TOKEN_LENGTH = 20;

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    protected $app;

    /**
     * 初期化処理
     * @access protected
     */
    protected function _init() {
    }

    /**
     * リダイレクトURL取得（billing/xml以外）
     *
     * @param string $urlSuffix URLサフィックス 例)"/status/rest"
     * @param array $params getPureParams()で取得した内容
     * @return string|null リダイレクトURL(null時はリダイレクト不要)
     */
    protected function getRedirectUrl($urlSuffix, $params) {
        $this->app = Application::getInstance();

        // OEM時は以降の処理不要
        if ($this->app->isOemActive()) {
            return null;
        }

        // 事業者ID／APIユーザーID取得
        $keyPrefix = ($urlSuffix == '/order/rest') ? 'O_' : '';
        $enterpriseId = $params[$keyPrefix . 'EnterpriseId'];
        $apiUserId = $params[$keyPrefix . 'ApiUserId'];

        // APIユーザが加盟店(サイト)に紐づき、且つリダイレクト設定有効時は行取得可能
        $sql = <<<EOQ
SELECT DISTINCT aue.ApiUserId, cod.Note
FROM   T_ApiUser au
       INNER JOIN T_ApiUserEnterprise aue ON (aue.ApiUserId = au.ApiUserId)
       INNER JOIN T_Site sit ON (sit.SiteId = aue.SiteId)
       INNER JOIN T_Enterprise ent ON (ent.EnterpriseId = sit.EnterpriseId)
       INNER JOIN M_Code cod ON (cod.KeyCode = ent.OemId AND cod.CodeId = 194)
WHERE  cod.Class1 = '1'
AND    ent.EnterpriseId = :EnterpriseId
AND    aue.ApiUserId = :ApiUserId
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId, ':ApiUserId' => $apiUserId))->current();
        if (!$row) {
            return null;
        }

        $mdlnm = new TableNotificationManage($this->app->dbAdapter);

        // トークン生成(トークン生成失敗時は直ちにリターンし従来通りの処理を行うようにする)
        $token = null;
        for ( $i = 0; $i < self::TOKEN_RAND_CHARANGE ; $i++ ) {
            $token = BaseGeneralUtils::makeRandStr(self::TOKEN_LENGTH);

            // すでに存在するかチェック
            $cnt = $mdlnm->countByToken($accessKey);
            if ($cnt == 0 ) break; // 重複していなければ確定
            if ($i >= self::TOKEN_RAND_CHARANGE - 1) return null;
        }

        // 通知内容管理(T_NotificationManage)へ登録
        $data = array(
                'Token' => $token
            ,   'ReceivedData' => Json::encode($params)
            ,   'ReceivedData2' => null
        );
        $mdlnm->saveNew($data);

        // リダイレクトURLを生成し戻す
        return ($row['Note'] . $urlSuffix . '/token/' . $token);
    }

    /**
     * リダイレクトURL取得（billing/xml専用）
     *
     * @param string $urlSuffix URLサフィックス
     * @param array $params xmlをarray化した[$decode_data]
     * @return string|null リダイレクトURL(null時はリダイレクト不要)
     */
    protected function getRedirectUrlForBilling($urlSuffix, $params) {
        $this->app = Application::getInstance();

        // OEM時は以降の処理不要
        if ($this->app->isOemActive()) {
            return null;
        }

        $enterpriseId = $params['Auth']['EnterpriseId'];
        $apiUserId = $params['Auth']['ApiUserId'];

        // APIユーザが加盟店(サイト)に紐づき、且つリダイレクト設定有効時は行取得可能
        $sql = <<<EOQ
SELECT DISTINCT aue.ApiUserId, cod.Note
FROM   T_ApiUser au
       INNER JOIN T_ApiUserEnterprise aue ON (aue.ApiUserId = au.ApiUserId)
       INNER JOIN T_Site sit ON (sit.SiteId = aue.SiteId)
       INNER JOIN T_Enterprise ent ON (ent.EnterpriseId = sit.EnterpriseId)
       INNER JOIN M_Code cod ON (cod.KeyCode = ent.OemId AND cod.CodeId = 194)
WHERE  cod.Class1 = '1'
AND    ent.EnterpriseId = :EnterpriseId
AND    aue.ApiUserId = :ApiUserId
EOQ;
        $row = $this->app->dbAdapter->query($sql)->execute(array(':EnterpriseId' => $enterpriseId, ':ApiUserId' => $apiUserId))->current();
        if (!$row) {
            return null;
        }

        $mdlnm = new TableNotificationManage($this->app->dbAdapter);

            // トークン生成(トークン生成失敗時は直ちにリターンし従来通りの処理を行うようにする)
        $token = null;
        for ( $i = 0; $i < self::TOKEN_RAND_CHARANGE ; $i++ ) {
            $token = BaseGeneralUtils::makeRandStr(self::TOKEN_LENGTH);

            // すでに存在するかチェック
            $cnt = $mdlnm->countByToken($accessKey);
            if ($cnt == 0 ) break; // 重複していなければ確定
            if ($i >= self::TOKEN_RAND_CHARANGE - 1) return null;
        }

        // 通知内容管理(T_NotificationManage)へ登録
        $data = array(
                'Token' => $token
            ,   'ReceivedData' => null
            ,   'ReceivedData2' => Json::encode($params)
        );
        $mdlnm->saveNew($data);

        // リダイレクトURLを生成し戻す
        return ($row['Note'] . $urlSuffix . '/token/' . $token);
    }

    /**
     * トークンのデコード
     *
     * @param string $token トークン
     * @param array $data getPureParams()関数呼出し結果相当
     * @param array $data2 XMLデータarray化相当
     * @return boolean true:成功／false:失敗(指定トークンの登録なし)
     */
    protected function decodeToken($token, &$data, &$data2) {
        $this->app = Application::getInstance();

        // トークンに関連付けられた通知内容管理データ取得
        $mdlnm = new TableNotificationManage($this->app->dbAdapter);
        $row = $mdlnm->findByToken($token)->current();
        if (!$row) {
            return false;
        }

        // 戻り引数へデコード結果をアサイン
        $data = Json::decode($row['ReceivedData'], Json::TYPE_ARRAY);
        $data2 = Json::decode($row['ReceivedData2'], Json::TYPE_ARRAY);

        // 指定されたレコードを削除する
        $mdlnm->deleteBySeq($row['Seq']);

        return true;
    }
}