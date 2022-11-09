<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Validate\CoralValidatePostalCode;
use Coral\Coral\Validate\CoralValidatePhone;
use models\Table\TableManagementCustomer;
use models\Table\TableEnterprise;
use models\Table\TableUser;
use models\Logic\LogicNormalizer;
use models\Table\TableMailTemplate;
use models\Table\TablePostalCode;
use models\Table\TablePrefecture;
use Coral\Coral\Validate\CoralValidateMultiMail;
use models\Table\TableSystemProperty;

class CustomerController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * @var Application
     */
    protected $app;

    /**
     * 氏名・氏名かなの検索データを作成するための不要文字抽出用正規表現
     *
     * @static
     * @var string
    */
    const REGEXP_TRIM_NAME = '[ 　\r\n\t\v]';

    /**
     * 電話番号の検索データを作成するための不要文字抽出用正規表現
     *
     * @static
     * @var string
    */
    const REGEXP_TRIM_PHONE = '[^0-9０-９]';

    /**
     * 初期化
    */
    public function _init()
    {
        $this->app = Application::getInstance();

        $this->view->assign( 'userInfo', $this->app->authManagerAdmin->getUserInfo() );

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');
        $this->addJavaScript('../js/json.js');

        $this->setPageTitle("後払い.com - 管理顧客管理");

        // コードマスターから都道府県マスターを取得
        $codeMaster = new CoralCodeMaster( $this->app->dbAdapter );
        $prefecture = $codeMaster->getPrefectureMaster();

        $this->view->assign( 'prefecture', $prefecture );
    }

    /**
     * 管理顧客詳細画面を表示
    */
    public function managementdetailAction()
    {
        $manCustId = $this->getParams()['mcid'];

        // 管理顧客情報取得
        $mdlmc = new TableManagementCustomer( $this->app->dbAdapter );
        $data = $mdlmc->find( $manCustId )->current();

        // 加盟店顧客情報取得
        $sql = "SELECT * FROM T_EnterpriseCustomer WHERE ManCustId = :ManCustId";
        $entCustSeq = $this->app->dbAdapter->query($sql)->execute(array(':ManCustId' => $manCustId))->current()['EntCustSeq'];

        if( !$data['ManCustId'] ) {
            throw new \Exception( sprintf( "ManCustId '%s' は不正な指定です", $manCustId ) );
        }

        // 請求情報取得
        $sql = <<<EOQ
SELECT  COUNT( o.OrderSeq ) AS Cnt
,       IFNULL( SUM( o.UseAmount ), 0 ) AS SumUseAmount
FROM    T_Order o
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
        INNER JOIN T_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
WHERE   mc.ManCustId = :ManCustId
EOQ;

        $order = $this->app->dbAdapter->query( $sql )->execute( array( ':ManCustId' => $manCustId ) )->current();

        $sql = <<<EOQ
SELECT  IFNULL( SUM( cc.ClaimedBalance ), 0 ) AS SumClaimedBalance
FROM    T_ClaimControl cc
        INNER JOIN T_Order o ON o.OrderSeq = cc.OrderSeq
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
        INNER JOIN T_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
WHERE   mc.ManCustId = :ManCustId
AND     EXISTS (SELECT *
                  FROM T_Order o2
                       INNER JOIN T_Customer c2 ON ( c2.OrderSeq = o2.OrderSeq )
                       INNER JOIN T_EnterpriseCustomer ec2 ON ( ec2.EntCustSeq = c2.EntCustSeq )
                       INNER JOIN T_ManagementCustomer mc2 ON ( mc2.ManCustId = ec2.ManCustId )
                 WHERE mc.ManCustId = :ManCustId
                   AND o2.P_OrderSeq = cc.OrderSeq
                   AND o2.DataStatus <> 91
               )
EOQ;

        $claim = $this->app->dbAdapter->query( $sql )->execute( array( ':ManCustId' => $manCustId ) )->current();
        $order['SumClaimedBalance'] = $claim['SumClaimedBalance'];

        // 不払い情報取得
        $sql = <<<EOQ
SELECT  COUNT( DISTINCT o.OrderSeq ) AS Cnt
FROM    T_Order o
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
        INNER JOIN T_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
        LEFT OUTER JOIN T_ClaimControl cc ON ( cc.OrderSeq = o.P_OrderSeq )
WHERE   mc.ManCustId = :ManCustId
AND     cc.F_LimitDate < :F_LimitDate
AND     cc.ClaimedBalance > 0
AND     o.DataStatus <> 91
EOQ;

        $nonPayment = $this->app->dbAdapter->query( $sql )->execute( array( ':ManCustId' => $manCustId, ':F_LimitDate' => date('Y-m-d') ) )->current();

        $sql = <<<EOQ
SELECT  IFNULL( SUM( ClaimedBalance ), 0 ) AS SumClaimedBalance
FROM    T_ClaimControl cc
        INNER JOIN T_Order o ON ( o.OrderSeq = cc.OrderSeq )
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
        INNER JOIN T_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
WHERE   mc.ManCustId = :ManCustId
AND     cc.F_LimitDate < :F_LimitDate
AND     cc.ClaimedBalance > 0
AND     EXISTS (SELECT *
                  FROM T_Order o2
                       INNER JOIN T_Customer c2 ON ( c2.OrderSeq = o2.OrderSeq )
                       INNER JOIN T_EnterpriseCustomer ec2 ON ( ec2.EntCustSeq = c2.EntCustSeq )
                       INNER JOIN T_ManagementCustomer mc2 ON ( mc2.ManCustId = ec2.ManCustId )
                 WHERE mc2.ManCustId = :ManCustId
                   AND o2.P_OrderSeq = cc.OrderSeq
                   AND o2.DataStatus <> 91
               )
EOQ;

        $nonPaymentAmt = $this->app->dbAdapter->query( $sql )->execute( array( ':ManCustId' => $manCustId, ':F_LimitDate' => date('Y-m-d') ) )->current();
        $nonPayment['SumClaimedBalance'] = $nonPaymentAmt['SumClaimedBalance'];

        // 最新注文情報
        $sql = <<<EOQ
SELECT  o.OrderSeq
,       o.RegistDate
,       cc.LimitDate
FROM    T_Order o
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
        INNER JOIN T_ManagementCustomer mc ON ( mc.ManCustId = ec.ManCustId )
        LEFT OUTER JOIN T_ClaimControl cc ON ( cc.OrderSeq = o.OrderSeq )
WHERE   mc.ManCustId = :ManCustId
ORDER BY o.RegistDate DESC
LIMIT 1
EOQ;

        $lastorder = $this->app->dbAdapter->query( $sql )->execute( array( ':ManCustId' => $manCustId ) )->current();

        // 顧客マイページ情報
        $sql = <<<EOQ
SELECT OemId
,      MailAddress
,      LastLoginDate
,      Reserve
,      CONCAT((SELECT KeyContent FROM M_Code WHERE CodeId = 107 AND KeyCode = OemId), '/login/reissue') AS AccessUrl
,      NameSeiKj
,      NameMeiKj
,      IFNULL(Phone, MobilePhone) AS Phone
FROM   MPV_MypageCustomer
WHERE  ManCustId = :ManCustId
ORDER BY OemId;
EOQ;
        $ri = $this->app->dbAdapter->query( $sql )->execute( array( ':ManCustId' => $manCustId ) );
        if ($ri->count() > 0) {
            $mypageinfos = array();

            foreach ($ri as $row) {
                $mypageinfo = array();
                $mypageinfo['MailAddress'] = $row['MailAddress'];
                $mypageinfo['LastLoginDate'] = $row['LastLoginDate'];
                if (nvl($row['Reserve'],'') != '') {
                    $mypageinfo['ReissueHistory'] = \Zend\Json\Json::decode($row['Reserve'], \Zend\Json\Json::TYPE_ARRAY);
                }
                $mypageinfo['Action'] = $row['AccessUrl'];
                $mypageinfo['NameSeiKj'] = $row['NameSeiKj'];
                $mypageinfo['NameMeiKj'] = $row['NameMeiKj'];
                $aryPhone = explode('-', $row['Phone']);
                $mypageinfo['Phone_1'] = $aryPhone[0];
                $mypageinfo['Phone_2'] = $aryPhone[1];
                $mypageinfo['Phone_3'] = $aryPhone[2];

                // 配列へ追加
                $mypageinfos[] = $mypageinfo;
            }

            $this->view->assign( 'mypageinfos', $mypageinfos );// 抽出結果があるときのみアサイン実施
        }

        $this->view->assign( 'mcid', $manCustId );
        $this->view->assign( 'ecs', $entCustSeq );
        $this->view->assign( 'data', $data );
        $this->view->assign( 'order', $order );
        $this->view->assign( 'nonpayment', $nonPayment );
        $this->view->assign( 'lastorder', $lastorder );

        return $this->view;
    }

    /**
     * 管理顧客登録画面の表示
     */
    public function managementformAction() {
        $this->view->assign( 'data', array( 'isNew' => true ) );
        $this->view->assign( 'error', array() );

        return $this->view;
    }

    /**
     * 管理顧客編集画面を表示
    */
    public function managementeditAction() {
        $manCustId = $this->getParams()['mcid'];

        $data = array( 'isNew' => false );

        // 管理顧客情報取得
        $mdlmc = new TableManagementCustomer( $this->app->dbAdapter );
        $data = $mdlmc->find( $manCustId )->current();

        // 住所は結合住所（UnitingAddress）を使用するが、都道府県名まで登録されているので、都道府県名は削除して表示する。
        $data['UnitingAddress'] = str_replace($data['PrefectureName'], "", $data['UnitingAddress']);

        if( !$data['ManCustId'] ) {
            throw new \Exception( sprintf( "ManCustId '%s' は不正な指定です", $manCustId ) );
        }

        $this->view->assign( 'data', $data );

        $this->setTemplate( 'managementform' );
        return $this->view;
    }

    /**
     * 管理顧客登録内容の確認
    */
    public function managementconfirmAction() {
        $data = $this->getParams()['form'];

        $errors = array();
        // NameKj: 顧客名
        $key = 'NameKj';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "顧客名が未入力です";
        }

        // NameKn: 顧客名カナ
        $key = 'NameKn';
        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0 && !preg_match( '/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $data[$key] ) ) ) {
            $errors[$key] = "顧客名カナがカタカナでないです";
        }

        // PostalCode: 郵便番号
        $key = 'PostalCode';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "住所 〒が未入力です";
        }

        // PrefectureCode: 都道府県コード
        $key = 'PrefectureCode';
        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "住所 都道府県が未選択です";
        }
        if ( !isset( $errors[$key] ) && $data[$key] == 0 ) {
            $errors[$key] = "住所 都道府県が未選択です";
        }

        // UnitingAddress: 結合住所（市区郡町域）
        $key = 'UnitingAddress';
        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "住所 市区郡町域が未入力です";
        }

        // Phone: 電話番号
        $key = 'Phone';
        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "電話番号が未入力です";
        }

        if( !empty( $errors ) ) {
            $this->view->assign( 'data', $data );
            $this->view->assign( 'error', $errors );

            $this->setTemplate( 'managementform' );

            return $this->view;
        }

        // checkbox未チェックを0にする
        if( !isset( $data['BlackFlg'] ) ) $data['BlackFlg'] = 0;
        if( !isset( $data['GoodFlg'] ) ) $data['GoodFlg'] = 0;
        if( !isset( $data['ClaimerFlg'] ) ) $data['ClaimerFlg'] = 0;
        if( !isset( $data['RemindStopFlg'] ) ) $data['RemindStopFlg'] = 0;
        if( !isset( $data['IdentityDocumentFlg'] ) ) $data['IdentityDocumentFlg'] = 0;

        // 都道府県名を展開
        $codeMaster = new CoralCodeMaster( $this->app->dbAdapter );
        $data['PrefectureName'] = $codeMaster->getPrefectureName( $data['PrefectureCode'] );

        // 結合住所作成
        $data['UnitingAddress'] = $data['PrefectureName'] . $data['UnitingAddress'];

        $data['SearchNameKj'] = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen( $data['NameKj'] ) ? $data['NameKj'] : '' );
        $data['SearchNameKn'] = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen( $data['NameKn']) ? $data['NameKn'] : '' );
        $phone = BaseGeneralUtils::convertWideToNarrow( strlen( $data['Phone']) ? $data['Phone'] : '' );
        $data['SearchPhone'] = mb_ereg_replace( self::REGEXP_TRIM_PHONE, '', $phone );
        $data['SearchUnitingAddress'] = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen( $data['UnitingAddress'] ) ? $data['UnitingAddress'] : '' );

        $data['RegNameKj'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $data['NameKj'] );
        $data['RegUnitingAddress'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $data['UnitingAddress'] );
        $data['RegPhone'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $data['Phone'] );

        $data['IluCustomerListFlg'] = 1;    // [審査システム－顧客情報連携フラグ]を⇒[1：必要]化

        $mdlmc = new TableManagementCustomer( $this->app->dbAdapter );

        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 新規登録
            if( $data['isNew'] ) {
                $data['RegistId'] = $userId;
                $data['UpdateId'] = $userId;

                $data['ManCustId'] = $mdlmc->saveNew( $data );
            }
            // 編集
            else {
                $data['UpdateId'] = $userId;

                $mdlmc->saveUpdate( $data, $data['ManCustId'] );
            }
            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch( \Exception $err ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        $redirect = 'customer/managementdetail/mcid/' . $data['ManCustId'];

        return $this->_redirect( $redirect );
    }

    /**
     * 事業者別顧客詳細画面を表示
    */
    public function memberdetailAction()
    {
        $entCustSeq = $this->getParams()['ecseq'];

        // 事業者顧客情報取得
        $sql = ' SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq = :EntCustSeq ';
        $prm = array( ':EntCustSeq' => $entCustSeq );
        $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        if( !$data['EntCustSeq'] ) {
            throw new \Exception( sprintf( "EntCustSeq '%s' は不正な指定です", $entCustSeq ) );
        }

        // 事業者情報取得
        $mde = new TableEnterprise( $this->app->dbAdapter );
        $edata = $mde->findEnterprise( $data['EnterpriseId'] )->current();
        $data['EnterpriseNameKj'] = $edata['EnterpriseNameKj'];

        // 管理顧客情報取得
        $mdlmc = new TableManagementCustomer( $this->app->dbAdapter );
        $mcdata = $mdlmc->find( $data['ManCustId'] )->current();
        $data['GoodFlg'] = $mcdata['GoodFlg'];
        $data['BlackFlg'] = $mcdata['BlackFlg'];
        $data['ClaimerFlg'] = $mcdata['ClaimerFlg'];
        $data['RemindStopFlg'] = $mcdata['RemindStopFlg'];
        $data['IdentityDocumentFlg'] = $mcdata['IdentityDocumentFlg'];

        // 請求情報取得
        $sql = <<<EOQ
SELECT  COUNT( o.OrderSeq ) AS Cnt
,       IFNULL( SUM( o.UseAmount ), 0 ) AS SumUseAmount
FROM    T_Order o
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
WHERE   ec.EntCustSeq = :EntCustSeq
EOQ;

        $order = $this->app->dbAdapter->query( $sql )->execute( array( ':EntCustSeq' => $entCustSeq ) )->current();

        $sql = <<<EOQ
SELECT  IFNULL( SUM( cc.ClaimedBalance ), 0 ) AS SumClaimedBalance
FROM    T_ClaimControl cc
        INNER JOIN T_Order o ON o.OrderSeq = cc.OrderSeq
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
WHERE   ec.EntCustSeq = :EntCustSeq
AND     EXISTS (SELECT *
                  FROM T_Order o2
                       INNER JOIN T_Customer c2 ON ( c2.OrderSeq = o2.OrderSeq )
                       INNER JOIN T_EnterpriseCustomer ec2 ON ( ec2.EntCustSeq = c2.EntCustSeq )
                 WHERE ec2.EntCustSeq = :EntCustSeq
                   AND o2.P_OrderSeq = cc.OrderSeq
                   AND o2.DataStatus <> 91
               )
EOQ;

        $claim = $this->app->dbAdapter->query( $sql )->execute( array( ':EntCustSeq' => $entCustSeq ) )->current();
        $order['SumClaimedBalance'] = $claim['SumClaimedBalance'];

        // 不払い情報取得
        $sql = <<<EOQ
SELECT  COUNT( DISTINCT o.OrderSeq ) AS Cnt
FROM    T_Order o
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
        LEFT OUTER JOIN T_ClaimControl cc ON ( cc.OrderSeq = o.OrderSeq )
WHERE   cc.F_LimitDate < CURDATE()
AND     cc.ClaimedBalance > 0
AND     ec.EntCustSeq = :EntCustSeq
AND     o.DataStatus <> 91
EOQ;

        $nonPayment = $this->app->dbAdapter->query( $sql )->execute( array( ':EntCustSeq' => $entCustSeq ) )->current();

        $sql = <<<EOQ
SELECT  IFNULL( SUM( ClaimedBalance ), 0 ) AS SumClaimedBalance
FROM    T_ClaimControl cc
        INNER JOIN T_Order o ON ( o.OrderSeq = cc.OrderSeq )
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
WHERE   ec.EntCustSeq = :EntCustSeq
AND     cc.F_LimitDate < CURDATE()
AND     cc.ClaimedBalance > 0
AND     EXISTS (SELECT *
                  FROM T_Order o2
                       INNER JOIN T_Customer c2 ON ( c2.OrderSeq = o2.OrderSeq )
                       INNER JOIN T_EnterpriseCustomer ec2 ON ( ec2.EntCustSeq = c2.EntCustSeq )
                 WHERE ec2.EntCustSeq = :EntCustSeq
                   AND o2.P_OrderSeq = cc.OrderSeq
                   AND o2.DataStatus <> 91
               )
EOQ;

        $nonPaymentAmt = $this->app->dbAdapter->query( $sql )->execute( array( ':EntCustSeq' => $entCustSeq ) )->current();
        $nonPayment['SumClaimedBalance'] = $nonPaymentAmt['SumClaimedBalance'];

        // 最新注文情報
        $sql = <<<EOQ
SELECT  o.OrderSeq
,       o.RegistDate
,       cc.LimitDate
FROM    T_Order o
        INNER JOIN T_Customer c ON ( c.OrderSeq = o.OrderSeq )
        INNER JOIN T_EnterpriseCustomer ec ON ( ec.EntCustSeq = c.EntCustSeq )
        LEFT OUTER JOIN T_ClaimControl cc ON ( cc.OrderSeq = o.OrderSeq )
WHERE   ec.EntCustSeq = :EntCustSeq
ORDER BY o.RegistDate DESC
LIMIT 1
EOQ;

        $lastorder = $this->app->dbAdapter->query( $sql )->execute( array( ':EntCustSeq' => $entCustSeq ) )->current();

        // 口座振替情報(のｺｰﾄﾞﾏｽﾀ変換の一部)
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $data['FfAccountClassKeyContent'] = $codeMaster->getAccountClassCaption((int)$data['FfAccountClass']);
        $data['RequestStatusKeyContent'] = $codeMaster->getMasterCaption(196, (int)$data['RequestStatus']);
        $data['RequestSubStatusKeyContent'] = $codeMaster->getMasterCaption(210, (int)$data['RequestSubStatus']);

        $this->view->assign( 'ecseq', $entCustSeq );
        $this->view->assign( 'data', $data );
        $this->view->assign( 'order', $order );
        $this->view->assign( 'nonPayment', $nonPayment );
        $this->view->assign( 'lastorder', $lastorder );

        return $this->view;
    }

    /**
     * 事業者別顧客登録画面の表示（不使用）
    */
    public function memberformAction() {
        $this->view->assign( 'data', array() );
        $this->view->assign( 'error', array() );

        return $this->view;
    }

    /**
     * 事業者別顧客編集画面を表示
    */
    public function membereditAction() {
        $entCustSeq = $this->getParams()['ecseq'];

        // 事業者顧客情報取得
        // 事業者顧客情報取得
        $sql = ' SELECT * FROM T_EnterpriseCustomer WHERE EntCustSeq = :EntCustSeq ';
        $prm = array( ':EntCustSeq' => $entCustSeq );
        $data = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        if( !$data['EntCustSeq'] ) {
            throw new \Exception( sprintf( "EntCustSeq '%s' は不正な指定です", $entCustSeq ) );
        }

        // 事業者情報取得
        $mde = new TableEnterprise( $this->app->dbAdapter );
        $edata = $mde->findEnterprise( $data['EnterpriseId'] )->current();
        $data['EnterpriseNameKj'] = $edata['EnterpriseNameKj'];

        // 管理顧客情報取得
        $mdlmc = new TableManagementCustomer( $this->app->dbAdapter );
        $mcdata = $mdlmc->find( $data['ManCustId'] )->current();
        $data['BtoBCreditLimitAmountFlg'] = (! empty($data['BtoBCreditLimitAmountFlg'])) ? $data['BtoBCreditLimitAmountFlg'] : 0;
        $data['GoodFlg'] = $mcdata['GoodFlg'];
        $data['BlackFlg'] = $mcdata['BlackFlg'];
        $data['ClaimerFlg'] = $mcdata['ClaimerFlg'];
        $data['RemindStopFlg'] = $mcdata['RemindStopFlg'];
        $data['IdentityDocumentFlg'] = $mcdata['IdentityDocumentFlg'];

        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $masters = array(
                'FfAccountClass' => $codeMaster->getAccountClassMaster(),
                'RequestStatus' => $codeMaster->getMasterCodes(196, array(0 => '-----')),
                'RequestSubStatus' => $codeMaster->getMasterCodes(210, array(0 => '-----')),
        );
        if (!isset($data['RequestStatus'])) {
            // 初回時は"申請中"のみ選択可
            unset($masters['RequestStatus'][2]);
            unset($masters['RequestStatus'][3]);
            unset($masters['RequestStatus'][9]);
            $data['RequestStatus'] = "0"; // 0 : 未使用
            $data['RequestSubStatus'] = "0"; // 0 : 未使用
            $data['FfAccountClass'] = "1";// 1 : 普通
        }

        if (is_null($data['FfAccountClass'])) {
            $data['FfAccountClass'] = "1";// 1 : 普通
        }

        //日付セットボタンの値設定
        //現在日時
        $today = date('Y-m-d');
        //システムプロパティから申込完了予定日のデフォルト設定の値を取得する。
        $mdlsp = new TableSystemProperty($this->app->dbAdapter);
        $catsAppCompDate = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'CATSAppCompDate');
        $catsAppCompDate = preg_replace('/[^0-9]+$/', '', $catsAppCompDate);
        if (empty($catsAppCompDate)){
            $catsAppCompDate = 0;
        }
        //現在日時 + システムプロパティ.申込完了予定日のデフォルト設定(口座振替用)
        $setDate = date('Y-m-d', strtotime($today. '+'. $catsAppCompDate. ' days'));

        $this->view->assign('master_map', $masters);
        $this->view->assign( 'data', $data );
        $this->view->assign( 'setDate', $setDate );

        $this->setTemplate( 'memberform' );

        return $this->view;
    }

    /**
     * 事業者別顧客登録内容の確認
     */
    public function memberconfirmAction() {
        $data = $this->getParams()['form'];
        $entCustSeq = $data['EntCustSeq'];

        // 事業者顧客情報取得
        $sql  = ' SELECT ManCustId ';
        $sql .= ' ,      EnterpriseId ';
        $sql .= ' ,      RequestStatus AS RequestStatusRow ';
        $sql .= ' FROM   T_EnterpriseCustomer ';
        $sql .= ' WHERE  EntCustSeq = :EntCustSeq ';
        $prm = array( ':EntCustSeq' => $entCustSeq );
        $ecdata = $this->app->dbAdapter->query( $sql )->execute( $prm )->current();

        $data['ValidFlg'] = 1;

        $data = array_merge( $data, $ecdata );

        // 事業者情報取得
        $mde = new TableEnterprise( $this->app->dbAdapter );
        $edata = $mde->findEnterprise( $data['EnterpriseId'] )->current();
        $data['EnterpriseNameKj'] = $edata['EnterpriseNameKj'];

        // 管理顧客情報取得
        $mdlmc = new TableManagementCustomer( $this->app->dbAdapter );
        $mcdata = $mdlmc->find( $data['ManCustId'] )->current();
        $data['GoodFlg'] = $mcdata['GoodFlg'];
        $data['BlackFlg'] = $mcdata['BlackFlg'];
        $data['ClaimerFlg'] = $mcdata['ClaimerFlg'];
        $data['RemindStopFlg'] = $mcdata['RemindStopFlg'];
        $data['IdentityDocumentFlg'] = $mcdata['IdentityDocumentFlg'];

        $errors = array();

        // PostalCode: 郵便番号
        $key = 'PostalCode';
        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "郵便番号が未入力です。";
        }
        $cvpc = new CoralValidatePostalCode();
        if ( !isset($errors[$key] ) && !$cvpc->isValid( $data[$key] ) ) {
            $errors[$key] = "郵便番号の入力が不正です。";
        }

        // UnitingAddress: 住所
        $key = 'UnitingAddress';
        if( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "住所が未入力です。";
        }
        if ( !isset( $errors[$key] ) && (mb_strlen($data[$key]) > 4000) ) {
            $errors[$key] = "住所は4000文字以下で入力してください。";
        }

//        $mdl = new TablePostalCode($this->app->dbAdapter);
//        if (!$mdl->isValidPostAddressKanji($data['PostalCode'], $data['UnitingAddress'])) {
//            $errors[$key] = "郵便番号と住所が一致しません。";
//        }

        // NameKj: 顧客名
        $key = 'NameKj';
        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "顧客名が未入力です。";
        }
        if ( !isset( $errors[$key] ) && (mb_strlen($data[$key]) > 30) ) {
            $errors[$key] = "顧客名は30文字以下で入力してください。";
        }

        // NameKn: 顧客名カナ
        $key = 'NameKn';
//        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0 && !preg_match( '/^[ァ-ヾ]+$/u', preg_replace( '/(\s|　)/', '', $data[$key] ) ) ) {
//            $errors[$key] = "顧客名カナがカタカナでないです。";
//        }
        if (mb_strlen($data[$key]) > 160) {
            $errors[$key] = "顧客名カナは160文字以下で入力してください。";
        }

        // Phone: 電話番号
        $key = 'Phone';
        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "電話番号が未入力です。";
        }
        $cvpe = new CoralValidatePhone;
        if ( !isset($errors[$key] ) && !$cvpe->isValid( $data[$key] ) ) {
            $errors[$key] = "電話番号として正しくありません。";
        }
        if ( !isset($errors[$key] ) && (mb_strlen ($data[$key]) > 13)){
            $errors[$key] = "電話番号として正しくありません。";
        }

//        // MailAddress: メールアドレス
//        $key = 'MailAddress';
//        $cvmm = new CoralValidateMultiMail();
//        if ( !isset( $errors[$key] ) && strlen( $data[$key] ) > 0 ) {
//            if ( !filter_var($data[$key], FILTER_VALIDATE_EMAIL) ) {
//                $errors[$key] = "メールアドレスが不正な形式です。";
//            }
//        }

        // BtoBCreditLimitAmount: BtoB与信限度額
        $key = 'BtoBCreditLimitAmount';
        if( !isset( $errors[$key] ) && $data['BtoBCreditLimitAmountFlg'] == 1 && strlen( $data[$key] ) == 0 ) {
            $errors[$key] = "与信限度額の値が不正値です";
        }
        if( !isset( $errors[$key] ) && $data['BtoBCreditLimitAmountFlg'] == 1 && intval( $data[$key] ) <= 0 ) {
            $errors[$key] = "与信限度額の値が不正値です";
        }

        // 申込ステータス : (1:申請中、2:完了、3:不備、9:中止)で指定されている時は、口座振替情報は必須
        if ((int)$data['RequestStatus'] == 2) {
            // FfName: 銀行名
            $key = 'FfName';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'銀行名'は必須です");
            }
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
                $errors[$key] = array("'銀行名'は160文字以内で入力してください");
            }
            // FfCode: 銀行番号
            $key = 'FfCode';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'銀行番号'は必須です");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'銀行番号'の形式が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] <= 9999)) {
                $errors[$key] = array("'銀行番号'は9999以下の数値で入力してください");
            }
            // FfBranchName: 支店名
            $key = 'FfBranchName';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'支店名'は必須です");
            }
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
                $errors[$key] = array("'支店名'は160文字以内で入力してください");
            }
            // FfBranchCode: 支店番号
            $key = 'FfBranchCode';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'支店番号'は必須です");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'支店番号'の形式が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] <= 999)) {
                $errors[$key] = array("'支店番号'は999以下の数値で入力してください");
            }
            // FfAccountClass: 預金種目
            $key = 'FfAccountClass';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'預金種目'を選択してください");
            }
            if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                $errors[$key] = array("'預金種目'の指定が不正です");
            }
            if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                $errors[$key] = array("'預金種目'の指定が不正です");
            }
            // FfAccountNumber: 口座番号
            $key = 'FfAccountNumber';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'口座番号'は必須です");
            }
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) == 7 && is_numeric($data[$key]))) {
                $errors[$key] = array("'口座番号'は7桁の数値で入力してください");
            }
            // FfAccountName: 預金者名
            $key = 'FfAccountName';
            if (!isset($errors[$key]) && !(mb_strlen($data[$key]) > 0)) {
                $errors[$key] = array("'預金者名'は必須です");
            }
            if (!isset($errors[$key]) && !(mb_ereg("^[0-9A-Zｱ-ﾜﾝﾞﾟ .()-]+$", $data[$key]))) {
                $errors[$key] = array("'預金者名'は半角大文字で入力してください");
            }
        } else {
            // FfName: 銀行名
            $key = 'FfName';
            if (mb_strlen($data[$key]) > 0) {
                if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
                    $errors[$key] = array("'銀行名'は160文字以内で入力してください");
                }
            }
            // FfCode: 銀行番号
            $key = 'FfCode';
            if (mb_strlen($data[$key]) > 0) {
                if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                    $errors[$key] = array("'銀行番号'の形式が不正です");
                }
                if (!isset($errors[$key]) && !((int)$data[$key] <= 9999)) {
                    $errors[$key] = array("'銀行番号'は9999以下の数値で入力してください");
                }
            }
            // FfBranchName: 支店名
            $key = 'FfBranchName';
            if (mb_strlen($data[$key]) > 0) {
                if (!isset($errors[$key]) && !(mb_strlen($data[$key]) <= 160)) {
                    $errors[$key] = array("'支店名'は160文字以内で入力してください");
                }
            }
            // FfBranchCode: 支店番号
            $key = 'FfBranchCode';
            if (mb_strlen($data[$key]) > 0) {
                if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                    $errors[$key] = array("'支店番号'の形式が不正です");
                }
                if (!isset($errors[$key]) && !((int)$data[$key] <= 999)) {
                    $errors[$key] = array("'支店番号'は999以下の数値で入力してください");
                }
            }
            // FfAccountClass: 預金種目
            $key = 'FfAccountClass';
            if (mb_strlen($data[$key]) > 0) {
                if (!isset($errors[$key]) && !(is_numeric($data[$key]))) {
                    $errors[$key] = array("'預金種目'の指定が不正です");
                }
                if (!isset($errors[$key]) && !((int)$data[$key] > 0)) {
                    $errors[$key] = array("'預金種目'の指定が不正です");
                }
            }
            // FfAccountNumber: 口座番号
            $key = 'FfAccountNumber';
            if (mb_strlen($data[$key]) > 0) {
                if (!isset($errors[$key]) && !(mb_strlen($data[$key]) == 7 && is_numeric($data[$key]))) {
                    $errors[$key] = array("'口座番号'は7桁の数値で入力してください");
                }
            }
            // FfAccountName: 預金者名
            $key = 'FfAccountName';
            if (mb_strlen($data[$key]) > 0) {
                if (!isset($errors[$key]) && !(mb_ereg("^[0-9A-Zｱ-ﾜﾝﾞﾟ .()-]+$", $data[$key]))) {
                    $errors[$key] = array("'預金者名'は半角大文字で入力してください");
                }
            }
        }

        // PublishingConfirmDate: 申込完了予定日
        $key = 'RequestCompScheduleDate';
        if (!isset($errors[$key]) && (mb_strlen($data[$key]) > 0) && !IsValidFormatDate($data[$key])) {
            $errors[$key] = array("'申込完了予定日'の形式が不正です");
        }

        // RequestSubStatus: 申込サブステータス
        $key = 'RequestSubStatus';
        if (!isset($errors[$key]) && ((int)$data['RequestStatus'] == 1) && ($data[$key] == 0)) {
            $errors[$key] = array("'申込サブステータス'は必須です");
        }

        if( !empty( $errors ) ) {
            $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
            $masters = array(
                    'FfAccountClass' => $codeMaster->getAccountClassMaster(),
                    'RequestStatus' => $codeMaster->getMasterCodes(196, array(0 => '-----')),
                    'RequestSubStatus' => $codeMaster->getMasterCodes(210, array(0 => '-----')),
            );
            if (!isset($data['RequestStatusRow'])) {
                // 初回時は"申請中"のみ選択可
                unset($masters['RequestStatus'][2]);
                unset($masters['RequestStatus'][3]);
                unset($masters['RequestStatus'][9]);
            }
            //日付セットボタンの値設定
            //現在日時
            $today = date('Y-m-d');
            //システムプロパティから申込完了予定日のデフォルト設定の値を取得する。
            $mdlsp = new TableSystemProperty($this->app->dbAdapter);
            $catsAppCompDate = $mdlsp->getValue('[DEFAULT]', 'systeminfo', 'CATSAppCompDate');
            $catsAppCompDate = preg_replace('/[^0-9]+$/', '', $catsAppCompDate);
            if (empty($catsAppCompDate)){
                $catsAppCompDate = 0;
            }
            //現在日時 + システムプロパティ.申込完了予定日のデフォルト設定(口座振替用)
            $setDate = date('Y-m-d', strtotime($today. '+'. $catsAppCompDate. ' days'));

            $this->view->assign('master_map', $masters);
            $this->view->assign( 'data', $data );
            $this->view->assign( 'error', $errors );
            $this->view->assign( 'setDate', $setDate );

            $this->setTemplate( 'memberform' );

            return $this->view;
        }

        // 都道府県名を展開
        $codeMaster = new CoralCodeMaster( $this->app->dbAdapter );
        $data['PrefectureName'] = $codeMaster->getPrefectureName( $data['PrefectureCode'] );

        $data['SearchNameKj'] = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen( $data['NameKj'] ) ? $data['NameKj'] : '' );
        $data['SearchNameKn'] = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen( $data['NameKn']) ? $data['NameKn'] : '' );
        $phone = BaseGeneralUtils::convertWideToNarrow( strlen( $data['Phone']) ? $data['Phone'] : '' );
        $data['SearchPhone'] = mb_ereg_replace( self::REGEXP_TRIM_PHONE, '', $phone );
        $data['SearchUnitingAddress'] = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen( $data['UnitingAddress'] ) ? $data['UnitingAddress'] : '' );

        $data['RegNameKj'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_NAME )->normalize( $data['NameKj'] );
        $data['RegUnitingAddress'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_ADDRESS )->normalize( $data['UnitingAddress'] );
        $data['RegPhone'] = LogicNormalizer::create( LogicNormalizer::FILTER_FOR_TEL )->normalize( $data['Phone'] );

        // ユーザーIDの取得
        $obj = new TableUser( $this->app->dbAdapter );
        $userId = $obj->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

        $sql  = ' UPDATE T_EnterpriseCustomer ';
        $sql .= ' SET ';
        $sql .= '     NameKj = :NameKj ';
        $sql .= ' ,   NameKn = :NameKn ';
        $sql .= ' ,   PostalCode = :PostalCode ';
        $sql .= ' ,   PrefectureCode = :PrefectureCode ';
        $sql .= ' ,   PrefectureName = :PrefectureName ';
        $sql .= ' ,   City = :City ';
        $sql .= ' ,   Town = :Town ';
        $sql .= ' ,   Building = :Building ';
        $sql .= ' ,   UnitingAddress = :UnitingAddress ';
        $sql .= ' ,   Phone = :Phone ';
        $sql .= ' ,   MailAddress = :MailAddress ';
        $sql .= ' ,   SearchNameKj = :SearchNameKj ';
        $sql .= ' ,   SearchNameKn = :SearchNameKn ';
        $sql .= ' ,   SearchPhone = :SearchPhone ';
        $sql .= ' ,   SearchUnitingAddress = :SearchUnitingAddress ';
        $sql .= ' ,   RegNameKj = :RegNameKj ';
        $sql .= ' ,   RegUnitingAddress = :RegUnitingAddress ';
        $sql .= ' ,   RegPhone = :RegPhone ';
        $sql .= ' ,   Note = :Note ';
        $sql .= ' ,   BtoBCreditLimitAmountFlg = :BtoBCreditLimitAmountFlg ';
        $sql .= ' ,   BtoBCreditLimitAmount = :BtoBCreditLimitAmount ';
        $sql .= ' ,   FfName = :FfName ';
        $sql .= ' ,   FfCode = :FfCode ';
        $sql .= ' ,   FfBranchName = :FfBranchName ';
        $sql .= ' ,   FfBranchCode = :FfBranchCode ';
        $sql .= ' ,   FfAccountClass = :FfAccountClass ';
        $sql .= ' ,   FfAccountNumber = :FfAccountNumber ';
        $sql .= ' ,   FfAccountName = :FfAccountName ';
        $sql .= ' ,   RequestCompScheduleDate = :RequestCompScheduleDate ';
        if((int)$data['RequestStatus'] == 2){
            $sql .= ' ,   RequestCompDate = :RequestCompDate ';
        }
        $sql .= ' ,   RequestStatus = :RequestStatus ';
        $sql .= ' ,   RequestSubStatus = :RequestSubStatus ';
        $sql .= ' ,   FfNote = :FfNote ';

        if(!(int)$data['RequestStatus'] > 0){
            $sql .= ' ,   ClaimFeeFlg = :ClaimFeeFlg ';
        }
        $sql .= ' ,   UpdateDate = :UpdateDate ';
        $sql .= ' ,   UpdateId = :UpdateId ';
        $sql .= ' ,   ValidFlg = :ValidFlg ';
        $sql .= ' WHERE EntCustSeq = :EntCustSeq ';

        $stm = $this->app->dbAdapter->query( $sql );

        if (empty($data['FfCode'])) {
            $data['FfCode'] = null;
        }
        if (empty($data['FfBranchCode'])) {
            $data['FfBranchCode'] = null;
        }
        if (empty($data['RequestCompScheduleDate'])) {
            $data['RequestCompScheduleDate'] = null;
        }

        if((int)$data['RequestStatus'] > 0){
            $prm = array(
                    ':EntCustSeq' => $entCustSeq,
                    ':Note' => $data['Note'],
                    ':BtoBCreditLimitAmountFlg' => $data['BtoBCreditLimitAmountFlg'],
                    ':BtoBCreditLimitAmount' => $data['BtoBCreditLimitAmount'],
                    ':FfName' => $data['FfName'],
                    ':FfCode' => $data['FfCode'],
                    ':FfBranchName' => $data['FfBranchName'],
                    ':FfBranchCode' => $data['FfBranchCode'],
                    ':FfAccountClass' => $data['FfAccountClass'],
                    ':FfAccountNumber' => $data['FfAccountNumber'],
                    ':FfAccountName' => $data['FfAccountName'],
                    ':RequestCompScheduleDate' => $data['RequestCompScheduleDate'],
                    ':RequestStatus' => $data['RequestStatus'],
                    ':RequestSubStatus' => $data['RequestSubStatus'],
                    ':FfNote' => $data['FfNote'],
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $userId,
                    ':ValidFlg' => $data['ValidFlg'],
            );
        }else{
            $prm = array(
                    ':EntCustSeq' => $entCustSeq,
                    ':Note' => $data['Note'],
                    ':BtoBCreditLimitAmountFlg' => $data['BtoBCreditLimitAmountFlg'],
                    ':BtoBCreditLimitAmount' => $data['BtoBCreditLimitAmount'],
                    ':FfName' => null,
                    ':FfCode' => null,
                    ':FfBranchName' => null,
                    ':FfBranchCode' => null,
                    ':FfAccountClass' => null,
                    ':FfAccountNumber' => null,
                    ':FfAccountName' => null,
                    ':RequestCompScheduleDate' => null,
                    ':RequestStatus' => null,
                    ':RequestSubStatus' => null,
                    ':FfNote' => $data['FfNote'],
                    ':ClaimFeeFlg' => null,
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $userId,
                    ':ValidFlg' => $data['ValidFlg'],
            );
        }
        if((int)$data['RequestStatus'] == 2){
            $prm[':RequestCompDate'] = date('Y-m-d');
        }

        $prmAdd = array(
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':PrefectureCode' => null,
                ':PrefectureName' => null,
                ':City' => null,
                ':Town' => null,
                ':Building' => null,
                ':UnitingAddress' => $data['UnitingAddress'],
                ':Phone' => $data['Phone'],
                ':MailAddress' => $data['MailAddress'],
                ':SearchNameKj' => $data['SearchNameKj'],
                ':SearchNameKn' => $data['SearchNameKn'],
                ':SearchPhone' => $data['SearchPhone'],
                ':SearchUnitingAddress' => $data['SearchUnitingAddress'],
                ':RegNameKj' => $data['RegNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
        );
        $prm = array_merge($prm, $prmAdd);

        $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
        try {
            $stm->execute( $prm );

            $this->app->dbAdapter->getDriver()->getConnection()->commit();
        } catch( \Exception $err ) {
            $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }

        $redirect = 'customer/memberdetail/ecseq/' . $entCustSeq;

        return $this->_redirect( $redirect );
    }

    /**
     * 郵便番号を検索する。
     *
     * @see 管理顧客用に GeneralsvcController からコピーし、管理顧客用に修正。
     */
    public function customersearchzipAction()
    {
        $params = $this->getParams();
        $data = $params['form'];

        try {
            $query = " SELECT MPOS.*, MPRE.PrefectureCode FROM M_PostalCode MPOS, M_Prefecture MPRE WHERE MPOS.PrefectureKanji = MPRE.PrefectureName AND MPOS.PostalCode7 = :PostalCode7 ";
            $stm = $this->app->dbAdapter->query($query);

            $postalCode7 = mb_ereg_replace("[^0-9０-９]", "", $data['PostalCode']);
            $postalCode7 = mb_convert_kana($postalCode7, "n", "UTF-8");

            $prm = array(
               ':PostalCode7' => $postalCode7,
            );

            $msg = $stm->execute($prm)->current();
            if (!$msg) {
                $msg['PrefectureCode'] = $data['PrefectureCode'];
                $msg['UnitingAddress'] = $data['UnitingAddress'];
                $msg['PrefectureKanji'] = '';
            } else {
                $msg['UnitingAddress'] = $msg['CityKanji'] . $msg['TownKanji'];
            }
        }
        catch(\Exception $e)
        {
            $msg['PrefectureCode'] = $data['PrefectureCode'];
            $msg['UnitingAddress'] = $data['UnitingAddress'];
            $msg['PrefectureKanji'] = '';
        }

        echo \Zend\Json\Json::encode($msg);
        return $this->response;
    }
}

