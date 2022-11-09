<?php
namespace models\Logic\CreditJudge\Connector;

use DOMDocument;
use Zend\Http\Client;
use Zend\Json\Json;
use models\Logic\CreditJudge\SystemConnect\LogicCreditJudgeSystemConnectException;
use Coral\Base\IO\BaseIOUtility;

/**
 * ILU審査システム連携
 */
class LogicCreditJudgeConnectorIlu
                        extends LogicCreditJudgeConnectorAbstract {

    /**
     * オプションを指定してLogicCreditJudgeConnectorIluの
     * 新しいインスタンスを初期化する
     *
     * @param LogicCreditJudgeOptions | array | Zend\Config\Reader\Ini $options オプション
     */
    public function __construct($options) {
        parent::__construct($options);
    }

    /**
     * ILU審査システムの審査サービスに接続し、レスポンスデータを返す。
     *
     * @access protected
     * @param string $xml_params ILU審査システムに渡すパラメータ
     * @return string レスポンスデータ
     */
    protected function _connect($params) {
        $client = new Client($this->_options->getIluUrl());

        //タイムアウト時刻取得
        $time_out = $this->_options->getTimeoutTime();

        //タイムアウト設定
        $client->setOptions(array('timeout' => $time_out, 'keepalive' => true, 'maxredirects' => 1));  // 20150717 試行回数(maxredirects) を 1 に設定

        try {
            // データ送信を実行する
            $response = $client
                            ->setRawBody($params)
                            ->setEncType('application/xml; charset=UTF-8', ';')
                            ->setMethod('Post')
                            ->send();

            // 結果を取得する
            $status = $response->getStatusCode();
            $res_msg = $response->getReasonPhrase();
            $res_msg = mb_convert_encoding($res_msg, mb_internal_encoding(), BaseIOUtility::detectEncoding($res_msg));

            if($status == 200) {
                // HTTPステータスが200だったら受信内容をそのまま返す
                return $response->getBody();
            }
            // ステータスが200以外の場合はコード1で例外をスロー
            throw new LogicCreditJudgeSystemConnectException(sprintf('Judge System Request failed (%s : %s)', $status, $res_msg), 1);
        } catch(\Exception $err) {
            if(!$err->getCode()) {
                // コード指定がない場合はタイムアウトと見なす
                throw new LogicCreditJudgeSystemConnectException(sprintf('Judge System Request Timed out (%s)', $err->getMessage()));
            } else {
                // それ以外はそのままキャッチした例外をスロー
				throw $err;
            }
        }
    }

    /**
     * ILU審査システムのダミーレスポンスデータを返す。
     *
     * @access protected
     * @param string $params パターンマスター取得サービス向け送信データ
     * @return string ダミーレスポンスデータ
     */
    protected function _connectDummy($params) {
        return $this->loadLocalIluData();
    }

    /**
     * 送信データを接続先固有のフォーマットにエンコードする。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     *
     * @abstract
     * @access protected
     * @param array $params 連想配列形式の送信データ
     * @return string クラス固有の接続先が要求するフォーマットにエンコードされた送信用データ
     */
    protected function _encodeParams(array $params) {

        // システムID
        $system_id = $params['system_id'];
        // 注文情報
		$order_data = $params['order_data'];
		// 注文商品情報
		$order_items = $params['order_items'];
		// 顧客情報
		$customer_data = $params['customer_data'];
		// 配送先情報
		$destination_data = $params['destination_data'];
		// 明細情報取得
		$order_detail_data = $params['order_detail_data'];

		//----- XML作成 -------
		$doc = new DOMDocument("1.0", "utf-8");

		//order_regist ヘッダ部分作成
		$element = $doc->createElementNS('http://www.w3.org/2001/XMLSchema', 'xsd:order_regist', "");

		// 新しい要素をルート (ドキュメントの子要素) として挿入する
		$root_element = $doc->appendChild($element);

		//属性の追加
		$root_element->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

		/******************************************/
		/*               system_id                */
		/******************************************/

		$system_id_element = $root_element->appendChild( $doc->createElement("system_id") );
		$system_id_element->appendChild( $doc->createTextNode($system_id['system_id']) );


		/******************************************/
		/*               order_info               */
		/******************************************/

		$order_info_element = $root_element->appendChild( $doc->createElement("order_info") );

		//business_person_id
		$statusElement = $order_info_element->appendChild( $doc->createElement("business_person_id") );
		$statusElement->appendChild( $doc->createTextNode($order_data['EnterpriseId']) );

		//system_order_id
		$statusElement = $order_info_element->appendChild( $doc->createElement("system_order_id") );
		$statusElement->appendChild( $doc->createCDATASection($order_data['OrderId']) );

        //-を/に変換
        $order_data['ReceiptOrderDate'] = str_replace("-", "/", $order_data['ReceiptOrderDate']);
        $order_data['RegistDate'] = str_replace("-", "/", $order_data['RegistDate']);

		//order_date
		$statusElement = $order_info_element->appendChild( $doc->createElement("order_date") );
		$statusElement->appendChild( $doc->createCDATASection($order_data['RegistDate']) );

		//receipt_site
		$statusElement = $order_info_element->appendChild( $doc->createElement("receipt_site") );
		$statusElement->appendChild( $doc->createCDATASection($order_data['SiteId']) );

		//optional_order_id
		$statusElement = $order_info_element->appendChild( $doc->createElement("optional_order_id") );
		$statusElement->appendChild( $doc->createCDATASection($order_data['Ent_OrderId']) );

		//total_amount
		$statusElement = $order_info_element->appendChild( $doc->createElement("total_amount") );
		//数値型チェック
		$order_data['UseAmount'] = $this->resetNum($order_data['UseAmount']);
		$statusElement->appendChild( $doc->createTextNode($order_data['UseAmount']) );

		//delivery_charge
		$statusElement = $order_info_element->appendChild( $doc->createElement("delivery_charge") );
		//数値型チェック
		$order_items['postage'] = $this->resetNum($order_items['postage']);
		$statusElement->appendChild( $doc->createTextNode($order_items['postage']) );

		//shop_commission
		$statusElement = $order_info_element->appendChild( $doc->createElement("shop_commission") );
		//数値型チェック
		$order_items['commission'] = $this->resetNum($order_items['commission']);
		$statusElement->appendChild( $doc->createTextNode($order_items['commission']) );

		//note
		$statusElement = $order_info_element->appendChild( $doc->createElement("note") );
		$statusElement->appendChild( $doc->createCDATASection($order_data['Ent_Note']) );

		/* -------------------------------------- */
		/*              payment_info              */
		/* -------------------------------------- */
		$payment_info_element = $order_info_element->appendChild( $doc->createElement("payment_info") );

		//payment_seq 20150605現状1固定
		$statusElement = $payment_info_element->appendChild( $doc->createElement("payment_seq") );
		$statusElement->appendChild( $doc->createTextNode(1) );

		//date_for_payment 20150605現状空文字固定
		$statusElement = $payment_info_element->appendChild( $doc->createElement("date_for_payment") );
		$statusElement->appendChild( $doc->createTextNode('') );

		//amount
		$statusElement = $payment_info_element->appendChild( $doc->createElement("amount") );
		//数値型チェック
		$order_data['UseAmount'] = $this->resetNum($order_data['UseAmount']);
		$statusElement->appendChild( $doc->createTextNode($order_data['UseAmount']) );


		/******************************************/
		/*          order_customer_info           */
		/******************************************/

		$customer_info_element = $root_element->appendChild( $doc->createElement("order_customer_info") );

		//system_customer_id
		$statusElement = $customer_info_element->appendChild( $doc->createElement("system_customer_id") );
		$statusElement->appendChild( $doc->createCDATASection($customer_data['ManCustId']) );

		//postal_code
		$statusElement = $customer_info_element->appendChild( $doc->createElement("postal_code") );
		$statusElement->appendChild( $doc->createCDATASection($customer_data['PostalCode']) );

		//address
		$statusElement = $customer_info_element->appendChild( $doc->createElement("address") );
		$statusElement->appendChild( $doc->createCDATASection($customer_data['UnitingAddress']) );

		//name
		$statusElement = $customer_info_element->appendChild( $doc->createElement("name") );
		$statusElement->appendChild( $doc->createCDATASection($customer_data['NameKj']) );

		//name_kana
		$statusElement = $customer_info_element->appendChild( $doc->createElement("name_kana") );
		$statusElement->appendChild( $doc->createCDATASection($customer_data['NameKn']) );

		//telno
		$statusElement = $customer_info_element->appendChild( $doc->createElement("telno") );
		$statusElement->appendChild( $doc->createCDATASection($customer_data['Phone']) );

		//mailaddress
		$statusElement = $customer_info_element->appendChild( $doc->createElement("mailaddress") );
		$statusElement->appendChild( $doc->createCDATASection($customer_data['MailAddress']) );

		//job 20150605現状空文字固定
		$statusElement = $customer_info_element->appendChild( $doc->createElement("job") );
		$statusElement->appendChild( $doc->createCDATASection('') );

		//birth 20150605現状空文字固定
		$statusElement = $customer_info_element->appendChild( $doc->createElement("birth") );
		$statusElement->appendChild( $doc->createCDATASection('') );

		//sex 20150605現状空文字固定
		$statusElement = $customer_info_element->appendChild( $doc->createElement("sex") );
		$statusElement->appendChild( $doc->createCDATASection('') );


		/******************************************/
		/*               destination_info         */
		/******************************************/

		// 別届先がある場合のみ設定
		if (!empty($destination_data)) {

    		$destination_info_element = $root_element->appendChild( $doc->createElement("destination_info") );

    		//destination_seq 20140326現状1固定
    		$statusElement = $destination_info_element->appendChild( $doc->createElement("destination_seq") );
    		$statusElement->appendChild( $doc->createTextNode(1) );

    		//destination_postal_code
    		$statusElement = $destination_info_element->appendChild( $doc->createElement("destination_postal_code") );
    		$statusElement->appendChild( $doc->createCDATASection($destination_data['PostalCode']) );

    		//destination_address
    		$statusElement = $destination_info_element->appendChild( $doc->createElement("destination_address") );
    		$statusElement->appendChild( $doc->createCDATASection($destination_data['UnitingAddress']) );

    		//destination_name
    		$statusElement = $destination_info_element->appendChild( $doc->createElement("destination_name") );
    		$statusElement->appendChild( $doc->createCDATASection($destination_data['DestNameKj']) );

    		//destination_name_kana
    		$statusElement = $destination_info_element->appendChild( $doc->createElement("destination_name_kana") );
    		$statusElement->appendChild( $doc->createCDATASection($destination_data['DestNameKn']) );

    		//destination_telno
    		$statusElement = $destination_info_element->appendChild( $doc->createElement("destination_telno") );
    		$statusElement->appendChild( $doc->createCDATASection($destination_data['Phone']) );
		}


		/******************************************/
		/*            order_detail                */
		/******************************************/
		foreach($order_detail_data as $key => $value){
			$item_seq = $key+1;
			$order_info_element = $root_element->appendChild( $doc->createElement("order_detail") );

			//item_destination_seq
			$statusElement = $order_info_element->appendChild( $doc->createElement("item_destination_seq") );
			$statusElement->appendChild( $doc->createTextNode($item_seq));

			//item_description
			$statusElement = $order_info_element->appendChild( $doc->createElement("item_description") );
			$statusElement->appendChild( $doc->createCDATASection($value['ItemNameKj']) );

			//unit_price
			$statusElement = $order_info_element->appendChild( $doc->createElement("unit_price") );
			//数値型チェック
			$value['UnitPrice'] = $this->resetNum($value['UnitPrice']);
			$statusElement->appendChild( $doc->createTextNode($value['UnitPrice']) );

			//item_volume
			$statusElement = $order_info_element->appendChild( $doc->createElement("item_volume") );
			//数値型チェック
			$value['ItemNum'] = $this->resetNum($value['ItemNum']);
			$statusElement->appendChild( $doc->createTextNode($value['ItemNum']) );
		}

		//XML保存
		$xml_data = $doc->saveXML();

		//order_registに整形
		$xml_replace_data = str_replace("xsd:order_regist", "order_regist", $xml_data);


		//制御コードを空にする
		if(!mb_ereg_replace('[\x00-\x08\x0b\x0e\x0f\x7f]', '', $xml_replace_data)){
			// 失敗した場合はなにもしない
			// → 原則発生しないハズ
			//throw new Exception("mb_ereg_replace ERROR");

		}

		return $xml_replace_data;
    }

    /**
     * 接続先から受信した生のレスポンスデータを連想配列にデコードする。
     * このメソッドは抽象メソッドなので、派生クラスで固有実装をする必要がある。
     *
     * @abstract
     * @access protected
     * @param string $response クラス固有の接続先から受信したレスポンスデータ
     * @return int 1:成功  2:NG
     */
    protected function _decodeResponse($response) {
        // SimpleXMLを経由して連想配列に展開
		$xml_param = simplexml_load_string($response);
		return get_object_vars($xml_param);
    }

	/**
	 * 数値型初期値設定
	 * @param $string　確認したい値
	 * @return $resp
	 */
	protected function resetNum($xml_data) {
	    if($xml_data == ""){
	 		return 0;
	 	}else{
// 20151101_2200_suzuki_h_数値はINT型で連携すること。審査システムにて数量の小数点対応が漏れているため、
// 審査が対応するまでの暫定対応
// return $xml_data;
	 	    return (int)$xml_data;
	 	}
	}
}
