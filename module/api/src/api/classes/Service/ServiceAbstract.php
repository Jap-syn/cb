<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\Response\ServiceResponseAbstract;
use api\classes\Service\Exception\ServiceExceptionOemAccess;
use models\Table\TableApiUser;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use models\Table\TableApiUserEnterprise;
use models\Table\TableOem;
use Zend\Json\Json;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

/**
 * サービスの基底クラス.
 */
abstract class ServiceAbstract {

    /**
     * DBアダプタ
     *
     * @var Adapter
     */
    protected $_db;

    /**
     * サービスID
     * @var string (2byte)
     */
    protected $_serviceId;

    /**
     *
     * @var int
     */
    protected $_apiUserId;
    /**
     *
     * @var int
     */
    protected $_enterpriseId;
    /**
     *
     * @var int
     */
    protected $_siteId;

    /**
     * {@link checkApiUserRelation}でサイトIDのチェックを行うか
     *
     * @access protected
     * @var bool
     */
    protected $_checkSiteId = true;

    /**
     * リクエスト内容をまとめた連想配列
     * @var array
     */
    public $_data;

    /**
     * サービスの処理結果を管理するクラス
     * @var ServiceResponseAbstract
     */
    protected $_response;

    /**
     * 処理実行
     *
     * @param struct $data リクエスト内容をまとめた連想配列
     * @return string 処理結果
     */
    final public function invoke(array $data) {
        $app = Application::getInstance();
        // 内部に保持
        $this->_data = $data;
        $this->_db = $app->dbAdapter;

        // ログ出力用
        $logger = $app->logger;
        $className = get_class($this);

        $logger->debug("$className#invoke(data) - start");
        $json = Json::encode($data);
        $serial = md5(sprintf('%s:%s', $json, microtime(true)));
        $logger->debug( "[$serial] invokeに渡されたデータ：" . Json::encode($data) );

        try {
            $result = true;

            $logger->debug("[$serial] $className#init() - start");
            $this->init();
            $logger->debug("[$serial] $className#init() - end");

            // 認証チェック
            $logger->debug("[$serial] $className#auth() - start");
            $result = $this->auth();
            $logger->debug("[$serial] $className#auth() - end");

            // 形式チェック
            if ( $result ) {
                $logger->debug("$className#check() - start");
                $result = $this->check();

                // デバッグ用条件（2010.12.03 eda）
                if($app->appGlobalConfig->debug_mode && $app->appGlobalConfig->force_reject_order) {
                    // 強制的に検証エラーを発生させる
                    $result = false;
                    $this->_response->addMessage(
                        sprintf('E%s999', $this->_serviceId), '現在メンテナンス中のため、注文登録を受け付けておりません' );
                }
                $logger->debug("[$serial] $className#check() - end");
            }
            // 処理実行
            if ( $result ) {
                $logger->debug("[$serial] $className#exec() - start");
                $result = $this->exec();
                $logger->debug("[$serial] $className#exec() - end");
            }
            if ( $result ) {
                // 正常
                $this->_response->status = ServiceResponseAbstract::SUCCESS;
                $logger->debug("[$serial] $className#invoke(data) - success");
            }
            else {
                // 異常
                $this->_response->status = ServiceResponseAbstract::ERROR;
                $logger->debug("[$serial] $className#invoke(data) - error");
            }
        }
        catch (ServiceExceptionOemAccess $oemAccessError) {
            // OEMアクセス関連エラーはすべて404として返す
            Application::getInstance()->logger->info(sprintf('[%s] %s OEM ERROR: %s',
                                                             $serial,
                                                             $className,
                                                             $oemAccessError->getMessage()));
            Application::getInstance()->return404Error();
        }
        catch (\Exception $e) {
            $logger->err( sprintf('[%s] %s exception: %s %s %s', $serial, $className, $e->getMessage(), "\r\n", $e->getTraceAsString()) );
            $this->_response->status = ServiceResponseAbstract::ERROR;
            $this->errorHandle( $e );
        }
        // 処理結果を返却
        return $this->returnResponse();
    }

    /**
     * 初期化処理
     */
    abstract protected function init();

    /**
     * 認証処理
     * @return bool
     */
    protected function auth() {
        $result = true;
        // OEMアクセスIDの正当性を検証
        // このチェックに引っかかるほとんどのパターンはServiceExceptionOemAccess例外となる
        if ( !$this->checkOemAccessId() ) {
            // checkOemAccessId()がfalseを返すケースは
            // OEMモード無効且つEnterpriseId不正のみのため、102エラーとする
            $this->_response->addMessage("E" . $this->_serviceId . "102",
                "不正なリクエストと判定されました。".
                "WEB-APIの利用申請が行なわれていない場合は、サポートセンターまでお問合せください。");
            $result = false;
        }
        // リクエスト元のIPアドレスとサイトIDの関連性チェック
        if ( $result && ! $this->checkIpAddress() ) {
            $this->_response->addMessage("E" . $this->_serviceId . "101",
                "不正なリクエストと判定されました。".
                "サポートセンターまでお問合せください。");
            $result = false;
        }
        // 事業者ID、サイトID、APIユーザIDの関連性チェック
        if ( $result && !$this->checkApiUserRelation() ) {
            $this->_response->addMessage("E" . $this->_serviceId . "102",
                "不正なリクエストと判定されました。".
                "WEB-APIの利用申請が行なわれていない場合は、サポートセンターまでお問合せください。");
            $result = false;
        }
        return $result;
    }

    /**
     * 入力内容に対する検証を行なう.
     *
     * @return bool 検証結果
     */
    abstract protected function check();

    /**
     * 実処理を行なう.
     *
     * @return bool 実行結果
     */
    abstract protected function exec();

    /**
     * 処理結果を文字列として返却する.
     *
     * @return string 処理結果
     */
    abstract protected function returnResponse();

    /**
     * システムエラー発生時の処理内容を定義する.
     *
     * @param $exception 例外
     */
    protected function errorHandle($exception) {
        $this->_response->addMessage("E" . $this->_serviceId . "901",
            "システムで障害が発生しました。".
            "サポートセンターまでお問合せください。");
    }

    /**
     * リクエスト元のIPアドレスとAPIユーザーIDの関連性チェック.
     *
     * @return bool チェック結果
     */
    protected function checkIpAddress() {

        $result = false;

        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更 Stt
        $ip = f_get_client_address();
//         $ip = $_SERVER['REMOTE_ADDR'];
        // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更 End

        $mdlapi = new TableApiUser($this->_db);
        // APIユーザー取得
        $userData = $mdlapi->findApiUser($this->_apiUserId)->current();

        if ( !empty($userData) ) {
            // リクエスト元のIPアドレスとAPIユーザーIDの関連性チェック
            $ipListString = $userData['ConnectIpAddressList'];
            $ipList = preg_split("/;/", $ipListString, 0, PREG_SPLIT_NO_EMPTY );
            foreach ( $ipList as $allowIpPattern ) {
                if ( self::wildcard_match($allowIpPattern, $ip) ) {
                    $result = true;
                    break;
                }
            }
        }

        // エラー時にはログ出力
        if ( !$result ) {
            Application::getInstance()->logger->info( "auth error. IP: $ip, ApiUserId:" . $this->_apiUserId);
        }

        return $result;
    }

    /**
     * 事業者ID、サイトID、APIユーザIDの関連性チェック.
     *
     * @return bool チェック結果
     */
    protected function checkApiUserRelation() {
        $mdlent = new TableEnterprise($this->_db);
        $mdlsite = new TableSite($this->_db);
        $mdlapi = new TableApiUser($this->_db);
        $mdlapiEnt = new TableApiUserEnterprise($this->_db);
        // 事業者IDのチェック
        $entData = $mdlent->findEnterprise($this->_enterpriseId)->current();
        if ( empty($entData) || $entData['ValidFlg'] != '1' ) {
            // 有効な事業者IDではない
            return false;
        }
        // 事業者IDとサイトIDのチェック
        if($this->_checkSiteId) {            // サイトIDチェックフラグがONの場合のみチェック（2013.8.16 eda）
            $rs = new ResultSet();
            $validSites = $rs->initialize($mdlsite->getValidAll($this->_enterpriseId))->toArray();
            if ( empty($validSites) ) {
                // 事業者に対し、有効なサイトが存在しない
                return false;
            }
            $siteExists = false;
            foreach ( $validSites as $siteData ) {

                if ( $siteData['SiteId'] == $this->_siteId ) {
                    $siteExists = true;
                    break;
                }
            }
            if ( !$siteExists ) {
                // 事業者IDとサイトIDの関連性が不正
                return false;
            }
        }
        // APIユーザーIDのチェック
        $apiData = $mdlapi->getValidApiUser($this->_apiUserId)->current();
        if ( empty($apiData) ) {
            // 有効なAPIユーザーIDではない
            return false;
        }

        $rs = new ResultSet();
        $validSites = $rs->initialize($mdlsite->getValidAll($this->_enterpriseId))->toArray();
        if ( empty($validSites) ) {
            // 事業者に対し、有効なサイトが存在しない
            return false;
        }
        // APIユーザーIDに紐づくサイトが存在すれば、OKとする
        $isRelation = false;
        foreach ( $validSites as $siteData ) {
            // APIユーザーIDと事業者IDの関連性チェック
            if ( $mdlapiEnt->isExistsRelation($this->_apiUserId, $siteData['SiteId']) ) {
                $isRelation = true;
                break;
            }
        }
        if ( $isRelation == false ) {
            return false;
        }

        return true;
    }

    /**
     * 当該事業者のOEM IDとアクセスしているディレクトリとの関連性をチェック
     * OEM ID絡みの不一致などが検出された場合、このメソッドはServiceExceptionOemAccessをスローする
     *
     * @return bool チェック結果
     */
    protected function checkOemAccessId() {

        $app = Application::getInstance();
        $oemAccessId = $app->getOemAccessId();

        $oemTable = new TableOem($this->_db);
        $entTable = new TableEnterprise($this->_db);

        // 事業者IDのチェック
        $entData = $entTable->findEnterprise($this->_enterpriseId)->current();

        if ( !$entData || $entData['ValidFlg'] != 1 ) {
            // 有効な事業者IDではない場合
            if(!$app->isOemActive()) {
                // OEMアクセスが無効なら通常の認証エラー
                return false;
            } else {
                // OEMアクセスが有効ならOEM IDエラー
                $msg = sprintf("不正な事業者IDのアクセス (EnterpriseId = %s)", $entData['EnterpriseId']);
                throw new ServiceExceptionOemAccess($msg, $this->_serviceId, '100');
            }
        }
        if(!$app->isOemActive()) {
            // OEMアクセスモードが無効な場合
            if(!((int)$entData['OemId'])) {
                // 事業者がOEM　IDに関連付けられていなければOK
                return true;
            }
            // OEM IDエラー
            $msg = sprintf("OEM ID '%s' に関連付けられた事業者のアクセス (EnterpriseId = %s)", $entData['OemId'], $entData['EnterpriseId']);
            throw new ServiceExceptionOemAccess($msg, $this->_serviceId, '100');
        } else {
            // OEMアクセスが有効な場合
            if(!$entData['OemId']) {
                // 事業者がOEM IDに関連付けられていない場合はNG
                // OEM IDエラー
                $msg = sprintf("OEM IDに関連付けられていない事業者のアクセス (EnterpriseId = %s)", $entData['EnterpriseId']);
                throw new ServiceExceptionOemAccess($msg, $this->_serviceId, '100');
            }
        }
        // 事業者に割り当てられたOEM IDからOEMテーブルを検索
//         $oemWheres = array(
//             $this->_db->quoteInto('OemId = ?', (int)$entData['OemId']),
//             'ValidFlg = 1'
//         );
//         foreach($oemTable->fetchAll(join(' AND ', $oemWheres)) as $row) {
//             // OEMテーブルのアクセスIDと現在のアクセスIDが一致したらOK
//             if($row->AccessId == $oemAccessId) {
//                 return true;
//             }
//             // 最初の行でマッチしなかったらマッチングは終了
//             break;
//         }
        $sql = " SELECT * FROM T_Oem WHERE OemId = :OemId AND ValidFlg = 1 ";
//         $stm = $this->_db->query($sql);
        $stm = $app->dbAdapter->query($sql);
        $prm = array(
                ':OemId' => (int)$entData['OemId'],
        );

        $row = $stm->execute($prm)->current();
        if($row['AccessId'] == $oemAccessId) {
            return true;
        }
        // 不正アクセス確定
        // OEM IDエラー
        $msg = sprintf("不正なアクセスIDディレクトリへのアクセス (EnterpriseId = %s, AccessId = %s)", $entData['EnterpriseId'], $oemAccessId);
        throw new ServiceExceptionOemAccess($msg, $this->_serviceId, '100');
    }


    /**
     * ワイルドカードを用いたマッチング処理を行う
     * @param $pattern パターン
     * @param $subject 対象文字列
     * @return bool 一致するか否か
     */
    public static function wildcard_match($pattern, $subject) {
        // 正規表現の特殊文字をエスケープ
        $escapedPattern = preg_quote($pattern);
        // エスケープされた特殊文字のうち、ワイルドカードとして利用可能な
        // '*?[]'を正規表現に変換する
        $convertedPattern = strtr($escapedPattern,
            array(
                // エスケープ済みの特殊文字(=ワイルドカード) => 正規表現
                '\*' => '.*',
                '\?' => '.',
                '\[' => '[',
                '\]' => ']',
           )
        );
        // 正規表現として実行して結果を返却
        return preg_match('/^' . $convertedPattern . "$/", $subject);
    }
}
