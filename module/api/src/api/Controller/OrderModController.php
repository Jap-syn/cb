<?php
namespace api\Controller;

ini_set('xdebug.var_display_max_data',-1);

use api\Application;
use api\classes\Service\ServiceOrderMod;
use api\classes\Service\OrderMod\ServiceOrderModConst;
use api\classes\Service\OrderMod\ServiceOrderModOrderConst;
use api\classes\Service\OrderMod\ServiceOrderModCustomerConst;
use api\classes\Service\OrderMod\ServiceOrderModDestinationConst;
use api\classes\Service\OrderMod\ServiceOrderModItemsConst;
use Coral\Coral\Controller\CoralControllerAction;
use Zend\Http\Client;
use Zend\Http\Request;
//use models\Logic\Normalizer\LogicNormalizerZenHyphens;
//use models\Logic\Normalizer\LogicNormalizerZenHyphenCompaction;

/**
 * 注文修正APIコントローラ
 */
class OrderModController extends CoralApiController {

    const USE_ANOTHER_DELIVERY = 1;

    /**
     * 新注文編集APIのサービスID
     * @var string
     */
    protected $_serviceId = "01";

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
        Application::getInstance()->logger->debug(sprintf('OrderModController initialized'));
    }

    /**
     * レスポンス送出初期化
     *
     * @access protected
     */
    protected function prepareXmlResponse() {
        $res = $this->getResponse();
        $res->getHeaders()->addHeaderLine( 'Content-Type', 'text/xml; charset=utf-8' );
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

        $serive = new ServiceOrderMod();

        $data = array();

        // OrderStatusを返却するかどうかの判定
        $serive->_rtOrderStatus = (int)($params['O_RtOrderStatus']) == 1 ? true : false;

//        //ハイフン正規化
//        $lnzh = new LogicNormalizerZenHyphens();
//        $lnzhc = new LogicNormalizerZenHyphenCompaction();
//        foreach (array('C_UnitingAddress', 'C_NameKj', 'D_UnitingAddress', 'D_DestNameKj') as $key){
//            $params[$key] = $lnzhc->normalize($lnzh->normalize($params[$key]));
//        }

        // 基本情報
        $order = array();
        $order[ServiceOrderModOrderConst::$EnterpriseId->name]             = $params['EnterpriseId'];
        $order[ServiceOrderModOrderConst::$ApiUserId->name]                = $params['ApiUserId'];
        $order[ServiceOrderModOrderConst::$OrderId->name]                  = strtoupper($params['OrderId']);
        $order[ServiceOrderModOrderConst::$ReceiptOrderDate->name]         = $params['O_ReceiptOrderDate'];
        $order[ServiceOrderModOrderConst::$SiteId->name]                   = $params['O_SiteId'];
        $order[ServiceOrderModOrderConst::$Ent_OrderId->name]              = $params['O_Ent_OrderId'];
        $order[ServiceOrderModOrderConst::$Ent_Note->name]                 = $params['O_Ent_Note'];
        $order[ServiceOrderModOrderConst::$UseAmount->name]                = $params['O_UseAmount'];
        $order[ServiceOrderModOrderConst::$ServiceExpectedDate->name]      = $params['O_ServicesProvidedDate'];
        $order[ServiceOrderModOrderConst::$Oem_OrderId->name]              = trim(nvl($params['O_Oem_OrderId']));
        $order[ServiceOrderModOrderConst::$T_OrderAutoCreditJudgeClass->name]        = $params['O_TestCreditResult'];
        $order[ServiceOrderModOrderConst::$AnotherDeliFlg->name]           = (int)($params['O_AnotherDeliFlg']);
        $order[ServiceOrderModOrderConst::$ItemCarriage->name]             = $params['I_ItemCarriage'];
        $order[ServiceOrderModOrderConst::$ItemCharge->name]               = $params['I_ItemCharge'];
        $order[ServiceOrderModOrderConst::$ItemOutsideTaxs->name]          = $params['I_OutsideTax'];
        $order[ServiceOrderModOrderConst::$SeparateShipment->name]         = $params['C_SeparateShipment'];    // 請求書別送
        $order[ServiceOrderModOrderConst::$CreditTransferRequestFlg->name] = $params['O_CreditTransferFlg'];   // 口座振替利用
        $data[ServiceOrderModConst::ORDER] = $order;

        // ご注文者（請求先）情報
        $customer = array();
        $customer[ServiceOrderModCustomerConst::$PostalCode->name]        = $params['C_PostalCode'];
        $customer[ServiceOrderModCustomerConst::$UnitingAddress->name]    = $params['C_UnitingAddress'];
        $customer[ServiceOrderModCustomerConst::$NameKj->name]            = $params['C_NameKj'];
        $customer[ServiceOrderModCustomerConst::$NameKn->name]            = $params['C_NameKn'];
        $customer[ServiceOrderModCustomerConst::$Phone->name]             = $params['C_Phone'];
        $customer[ServiceOrderModCustomerConst::$MailAddress->name]       = $params['C_MailAddress'];
        $customer[ServiceOrderModCustomerConst::$Occupation->name]        = $params['C_Occupation'];
        $customer[ServiceOrderModCustomerConst::$EntCustId->name]         = $params['C_EntCustId'];
        $customer[ServiceOrderModCustomerConst::$CorporateName->name]     = $params['C_CorporateName'];
        $customer[ServiceOrderModCustomerConst::$DivisionName->name]      = $params['C_DivisionName'];
        $customer[ServiceOrderModCustomerConst::$CpNameKj->name]          = $params['C_CpNameKj'];
        $data[ServiceOrderModConst::CUSTOMER] = $customer;

        // 別配送先情報
        $dest = array();
        if( $order[ServiceOrderModOrderConst::$AnotherDeliFlg->name] == self::USE_ANOTHER_DELIVERY ) {
            // 別配送先が明示的に指定されている場合のみ、別配送先パラメータを使用
            $dest[ServiceOrderModDestinationConst::$PostalCode->name ]    = $params['D_PostalCode'];
            $dest[ServiceOrderModDestinationConst::$UnitingAddress->name] = $params['D_UnitingAddress'];
            $dest[ServiceOrderModDestinationConst::$DestNameKj->name]     = $params['D_DestNameKj'];
            $dest[ServiceOrderModDestinationConst::$DestNameKn->name]     = $params['D_DestNameKn'];
            $dest[ServiceOrderModDestinationConst::$Phone->name]          = $params['D_Phone'];
        } else {
            // 別配送先未指定の場合は請求先からコピー
            $dest[ServiceOrderModDestinationConst::$PostalCode->name ]    = $params['C_PostalCode'];
            $dest[ServiceOrderModDestinationConst::$UnitingAddress->name] = $params['C_UnitingAddress'];
            $dest[ServiceOrderModDestinationConst::$DestNameKj->name]     = $params['C_NameKj'];
            $dest[ServiceOrderModDestinationConst::$DestNameKn->name]     = $params['C_NameKn'];
            $dest[ServiceOrderModDestinationConst::$Phone->name]          = $params['C_Phone'];
        }
        $data[ServiceOrderModConst::DESTINATION] = $dest;

        // 商品情報
        $items = array(); // key = idx, value = array( name, prise, num)
        foreach ( $params as $name => $value ) {
            //if ( !empty($value) && preg_match('/^I_(\w+)+_(\d+)$/i', $name, $regs) ) {
            if ( !($value === null || !strlen(trim((string)$value))) && preg_match('/^I_(\w+)+_(\d+)$/i', $name, $regs) ) {
                if ( is_null( $items[$regs[2]] ) ) {
                    $items[$regs[2]] = array();
                    $items[$regs[2]]['DataClass'] = 1; // 商品
                }
                $items[$regs[2]][$regs[1]] = $value;
             }
        }
        $data[ServiceOrderModConst::ITEMS] = $items;

        $this->prepareXmlResponse();

        echo $serive->invoke( $data );

        return $this->getResponse();
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
