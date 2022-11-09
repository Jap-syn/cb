<?php
namespace api\Controller;

ini_set('xdebug.var_display_max_data',-1);

use api\Application;
use api\classes\Service\ServiceOrder;
use api\classes\Service\Order\ServiceOrderConst;
use api\classes\Service\Order\ServiceOrderOrderConst;
use api\classes\Service\Order\ServiceOrderCustomerConst;
use api\classes\Service\Order\ServiceOrderDestinationConst;
use api\classes\Service\Order\ServiceOrderItemsConst;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Http\Client;
use Zend\Http\Request;
//use models\Logic\Normalizer\LogicNormalizerZenHyphens;
//use models\Logic\Normalizer\LogicNormalizerZenHyphenCompaction;

/**
 * 注文登録APIコントローラ
 */
class OrderController extends CoralApiController {
    const USE_ANOTHER_DELIVERY = 1;

    protected $_componentRoot = './application/views/components';

    /**
     * @access protected
     * @var array
     */
    protected $_threadPoolConfig;

    /**
     * (non-PHPdoc)
     * @see Coral/Coral/Controller/CoralControllerAction#_init()
     */
    protected function _init() {
    }

    /**
     * Rest受け付け用IF
     */
    public function restAction() {
        $params = $this->getParams();

        if (isset($params['token'])) {
            if (parent::decodeToken($params['token'], $ret, $ret2)) {
                unset($params['token']);
                $params = array_merge($params, $ret);
            }
        }
        else {
            $redirectUrl = parent::getRedirectUrl('/' . $this->getControllerName() . '/' . $this->getActionName(), $this->getPureParams());
            if ($redirectUrl != null) {
                return $this->redirect()->toUrl($redirectUrl);
            }
        }

//        $this->_helper->viewRenderer->setNoRender();
        $serive = new ServiceOrder();
        $data = array();

        // OrderStatusを返却するかどうかの判定
        $serive->_rtOrderStatus = (int)($params['O_RtOrderStatus']) == 1 ? true : false;

        // 新旧ＡＰＩ切替情報
        $serive->_rtApiNewClass = $params['O_NewSystemFlg'];

        //処理開始時間を設定する。(UNIX TIMESTAMP)
        $serive->_actionStateTimestamp = time();

//        //ハイフン正規化
//        $lnzh = new LogicNormalizerZenHyphens();
//        $lnzhc = new LogicNormalizerZenHyphenCompaction();
//        foreach (array('C_UnitingAddress', 'C_NameKj', 'D_UnitingAddress', 'D_DestNameKj') as $key){
//            $params[$key] = $lnzhc->normalize($lnzh->normalize($params[$key]));
//        }

        // 基本情報
        $order = array();
        $order[ServiceOrderOrderConst::$ReceiptOrderDate->name]            = $params['O_ReceiptOrderDate'];
        $order[ServiceOrderOrderConst::$EnterpriseId->name]                = $params['O_EnterpriseId'];
        $order[ServiceOrderOrderConst::$SiteId->name]                      = $params['O_SiteId'];
        $order[ServiceOrderOrderConst::$ApiUserId->name]                   = $params['O_ApiUserId'];
        $order[ServiceOrderOrderConst::$Ent_OrderId->name]                 = $params['O_Ent_OrderId'];
        $order[ServiceOrderOrderConst::$Ent_Note->name]                    = $params['O_Ent_Note'];
        $order[ServiceOrderOrderConst::$UseAmount->name]                   = $params['O_UseAmount'];
        $order[ServiceOrderOrderConst::$AnotherDeliFlg->name]              = (int)($params['O_AnotherDeliFlg']);       // intに補正（09.09.14 by eda）
        $order[ServiceOrderOrderConst::$ItemCarriage->name]                = $params['I_ItemCarriage'];
        $order[ServiceOrderOrderConst::$ItemCharge->name]                  = $params['I_ItemCharge'];
        $order[ServiceOrderOrderConst::$ItemOutsideTaxs->name]             = $params['I_OutsideTax'];                  // 外税額
        $order[ServiceOrderOrderConst::$Oem_OrderId->name]                 = trim(nvl($params['O_Oem_OrderId']));
        $order[ServiceOrderOrderConst::$ServiceExpectedDate->name]         = $params['O_ServicesProvidedDate'];        // 役務提供予定日
        $order[ServiceOrderOrderConst::$T_OrderClass->name]                = $params['O_TestOrderFlg'];                // テスト注文区分
        $order[ServiceOrderOrderConst::$T_OrderAutoCreditJudgeClass->name] = $params['O_TestCreditResult'];            // テスト注文自動与信審査区分
        $order[ServiceOrderOrderConst::$SeparateShipment->name]            = $params['C_SeparateShipment'];            // 請求書別送
        $order[ServiceOrderOrderConst::$CreditTransferRequestFlg->name]    = $params['O_CreditTransferFlg'];           // 口座振替利用
        $data[ServiceOrderConst::ORDER] = $order;

        // ご注文者（請求先）情報
        $customer = array();
        $customer[ServiceOrderCustomerConst::$PostalCode->name]     = $params['C_PostalCode'];
        $customer[ServiceOrderCustomerConst::$UnitingAddress->name] = $params['C_UnitingAddress'];
        $customer[ServiceOrderCustomerConst::$NameKj->name]         = $params['C_NameKj'];
        $customer[ServiceOrderCustomerConst::$NameKn->name]         = $params['C_NameKn'];
        $customer[ServiceOrderCustomerConst::$Phone->name]          = $params['C_Phone'];
        $customer[ServiceOrderCustomerConst::$MailAddress->name]    = $params['C_MailAddress'];
        $customer[ServiceOrderCustomerConst::$Occupation->name]     = $params['C_Occupation'];
        $customer[ServiceOrderCustomerConst::$EntCustId->name]      = $params['C_EntCustId'];                   // 加盟店顧客番号
        $customer[ServiceOrderCustomerConst::$CorporateName->name]  = $params['C_CorporateName'];               // 法人名
        $customer[ServiceOrderCustomerConst::$DivisionName->name]   = $params['C_DivisionName'];                // 部署名
        $customer[ServiceOrderCustomerConst::$CpNameKj->name]       = $params['C_CpNameKj'];                    // 担当者名
        $customer[ServiceOrderCustomerConst::$AddressKn->name]      = '';                                       // 住所カナ
        $data[ServiceOrderConst::CUSTOMER] = $customer;

        // 別配送先情報
        $dest = array();
        if( $order[ServiceOrderOrderConst::$AnotherDeliFlg->name] == self::USE_ANOTHER_DELIVERY ) {
            // 別配送先が明示的に指定されている場合のみ、別配送先パラメータを使用（09.09.14 追加 by eda）
            $dest[ServiceOrderDestinationConst::$PostalCode->name ]    = $params['D_PostalCode'];
            $dest[ServiceOrderDestinationConst::$UnitingAddress->name] = $params['D_UnitingAddress'];
            $dest[ServiceOrderDestinationConst::$DestNameKj->name]     = $params['D_DestNameKj'];
            $dest[ServiceOrderDestinationConst::$DestNameKn->name]     = $params['D_DestNameKn'];
            $dest[ServiceOrderDestinationConst::$Phone->name]          = $params['D_Phone'];
        } else {
            // 別配送先未指定の場合は請求先からコピー（09.09.14 追加 by eda）
            $dest[ServiceOrderDestinationConst::$PostalCode->name ]    = $params['C_PostalCode'];
            $dest[ServiceOrderDestinationConst::$UnitingAddress->name] = $params['C_UnitingAddress'];
            $dest[ServiceOrderDestinationConst::$DestNameKj->name]     = $params['C_NameKj'];
            $dest[ServiceOrderDestinationConst::$DestNameKn->name]     = $params['C_NameKn'];
            $dest[ServiceOrderDestinationConst::$Phone->name]          = $params['C_Phone'];
        }
        $data[ServiceOrderConst::DESTINATION] = $dest;

        // 商品情報
        $items = array(); // key = idx, value = array( name, prise, num)
        foreach ( $params as $name => $value ) {
            // !empty($value) で 数字の '0' が入っていた場合にクリアされてしまうので判断方法を変更（2010.12.03 eda）
            //if ( !empty($value) && preg_match('/^I_(\w+)+_(\d+)$/i', $name, $regs) ) {
            if ( !($value === null || !strlen(trim((string)$value))) && preg_match('/^I_(\w+)+_(\d+)$/i', $name, $regs) ) {
                if ( is_null( $items[$regs[2]] ) ) {
                    $items[$regs[2]] = array();
                    $items[$regs[2]]['DataClass'] = 1; // 商品
                }
                $items[$regs[2]][$regs[1]] = $value;
             }
        }
        $data[ServiceOrderConst::ITEMS] = $items;

        // レスポンスのContent-Typeをtext/xmlに設定
//        $this->getResponse()->setHeader( 'Content-Type', 'text/xml; charset=utf-8', true );
        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/xml; charset=utf-8' );
        echo $serive->invoke( $data );

        return $this->getResponse();
    }

    /**
     * XML-RPC受付用IF.<br>
     * 実装途中のため使用不可
     */
    public function rpcAction() {
        $this->_helper->viewRenderer->setNoRender();

        $service = new Service_Order();

        require_once 'Zend/XmlRpc/Server.php';
        $server = new Zend_XmlRpc_Server();
        $server->setClass($service, "order");

        // XXX furukawa 返却値がXMLソースに...
        echo $server->handle();
    }

    /**
     * テストフォームの表示
     */
    public function testAction() {}

    /**
     * テストファイル送信
     */
    public function testfileAction() {
//        $this->_helper->viewRenderer->setNoRender();

        // インスタンス生成
        $http = new Client();
        $post = array();

        // POSTデータ設定
        $textArray = file('./module/api/config/testdata.txt',
            FILE_TEXT | FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ( $textArray as $line ) {
            // 分解
            list($name, $value) = preg_split('/\s*[=\r\n]\s*/', $line);

            // 名前、値がブランクは無視
            if ( $name == '' || $value == '' ) continue;

            // コメント行無視
            if ( preg_match('/^\s*;.*$/', $name) ) continue;

            if ( $name == 'URI' ) {
                $http->setUri($value);
            }
            else {
                $nameCount = 0;
                if (!empty($name)) {
                    $nameCount = count($name);
                }
                for($i = 0; $i < $nameCount; $i++) {
                    $post = array_merge($post, array($name => $value));
                }
            }
        }
        $http->setParameterPost($post);

        // POSTリクエスト設定
        $http->setMethod(Request::METHOD_POST);

        // POST送信
        $httpResponse = $http->send($http->getRequest());

        // 表示
        header("Content-type: " . $http->getHeader("Content-type"));
        echo $httpResponse->getBody();

        return $this->response;
    }

// Del By Yanase 20150225 マジックメソッド廃止
//     /**
//      * 存在しないアクションメソッドが指定された場合
//      * @param $method
//      * @param $args
//      */
//     public function __call($method, $args) {
//         throw new Exception(sprintf("method '%s' not implemented.", $method));
//     }
// Del By Yanase 20150225 マジックメソッド廃止
}
