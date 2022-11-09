<?php
namespace oemmember\classes;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use models\Table\TableDeliMethod;
use models\Table\TablePrefecture;
use models\Logic\LogicConstant;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Validate\CoralValidateUtility;
use Coral\Coral\Validate\CoralValidatePostalCode;
use Coral\Coral\Validate\CoralValidatePhone;
use Coral\Coral\Validate\CoralValidateMultiMail;
use Zend\Db\Sql\Ddl\Column\Boolean;

/**
 * 事業者による注文編集ロジック
 */
class OrderEditor {
	/**
	 * グルーピングID定数：注文基本情報グループ
	 * @var string
	 */
	const GROUP_ORDER = 'order_info_group';

	/**
	 * グルーピングID定数：購入者情報グループ
	 * @var string
	 */
	const GROUP_CUSTOMER = 'customer_info_group';

	/**
	 * グルーピングID定数：配送先情報グループ
	 * @var string
	 */
	const GROUP_DELIVERY = 'delivery_info_group';

	/**
	 * グルーピングID定数：配送伝票情報グループ
	 * @var string
	 */
	const GROUP_JOURNAL = 'journal_info_group';

	/**
	 * グルーピングID定数：商品明細情報グループ
	 * @var string
	 */
	const GROUP_ITEMS = 'items_info_group';

    /**
	 * グルーピングID定数：注文追加情報グループ
	 * @var string
	 */
	const GROUP_ADDINFO = 'order_add_info_group';

	/**
	 * グルーピングID定数：全グループ一括を指定する抽象グループ
	 * @var string
	 */
	const GROUP_ALL = 'all_groups';

	/**
	 * ステータス定数：与信中
	 * @var string
	 */
	const STATUS_CREDIT_PROGRESS = '与信中';

	/**
	 * ステータス定数：伝票登録待ち
	 * @var string
	 */
	const STATUS_WAIT_FOR_SHIPPING = '伝票登録待ち';

	/**
	 * ステータス定数：請求書印刷待ち
	 * @var string
	 */
	const STATUS_WAIT_FOR_INVOICING = '請求書印刷待ち';

	/**
	 * ステータス定数：請求中
	 * @var string
	 */
	const STATUS_INVOICING = '請求中';

	/**
	 * ステータス定数：取引完了
	 * @var string
	 */
	const STATUS_CLOSED = '取引完了';

	/**
	 * ステータス定数：取引完了（キャンセル）
	 * @var string
	 */
	const STATUS_CANCELED = '取引完了（キャンセル）';

	/**
	 * ステータス定数：請求中（返却前）
	 * @var string
	 */
	const STATUS_CLOSED_SAIKEN = '取引完了（返却申請中）';

	/**
	 * ステータス定数：取引完了（債権返却）
	 * @var string
	 */
	const STATUS_CANCELED_SAIKEN = '取引完了（返却済み）';

	/**
	 * ステータス定数：取引完了（与信NG）
	 * @var string
	 */
	const STATUS_NG = '取引完了（与信NG）';

    /**
	 * ステータス定数：クレジット決済完了
	 * @var string
	 */
	const STATUS_SEND_CREDIT_BUYING_COMPLETE = '決済完了';

	/**
	 * 注文ステータスを取得するために必要なデータを取得する基本クエリを返す
	 * @access protected
	 * @return string
	 */
	protected static function _getJudgeQuery() {
		return <<<EOQ
SELECT
	o.*,
	i.*
FROM
	T_Order o INNER JOIN
	T_OrderSummary s ON s.OrderSeq = o.OrderSeq INNER JOIN
	T_OrderItems i ON i.OrderItemId = s.OrderItemId
WHERE
	o.OrderSeq = ?
EOQ;
	}

	/**
	 * DBアダプタ
	 * @var Adapter
	 * @access protected
	 */
	protected $_adapter;

	/**
	 * OrderEditorの新しいインスタンスを初期化する
	 * @param Adapter $adapter DBアダプタ
	 */
	public function __construct(Adapter $adapter) {
		$this
			->setAdapter($adapter);
	}

	/**
	 * DBアダプタを取得する
	 * @return Adapter
	 */
	public function getAdapter() {
		return $this->_adapter;
	}
	/**
	 * DBアダプタを設定する
	 * @param Adapter $adapter DBアダプタ
	 * @return OrderEditor このインスタンス自身
	 */
	public function setAdapter(Adapter $adapter) {
		if(! ($adapter instanceof Adapter)) {
			throw new OrderEditorException(OrderEditorException::ERR_MSG_INVALID_DB_ADAPTER);
		}
		$this->_adapter = $adapter;
		return $this;
	}

	/**
	 * 指定シーケンスの注文の修正可能性を判断する
	 * @param int $order_seq 注文シーケンス
	 * @param null|string $target_group 判別対象の項目グループ。省略時は全グループ一括取得となる
	 * @return array
	 */
	public function judgeOrderModifiability($order_seq, $target_group = self::GROUP_ALL) {
	    $data = $this->_getOrderForJudge($order_seq);
		if(! $data) throw new OrderEditorException(OrderEditorException::ERR_MSG_DATA_NOT_AVAILABLE);

		$results = array();

		// 基本情報・購入者情報・配送先情報は<strike>請求書印刷前</strike>伝票登録前なら修正可能
		$results[self::GROUP_ORDER] =
			$results[self::GROUP_CUSTOMER] =
			$results[self::GROUP_DELIVERY] = ($data['DataStatus'] < 41 && ! $data['Cnl_Status']);

		// 配送伝票情報は請求書印刷待ち/入金確認待ち/一部入金の何れかで且つ未着荷の場合のみ修正可能
		$results[self::GROUP_JOURNAL] = (
			in_array($data['DataStatus'], array(41, 51, 61)) &&
			$data['Deli_ConfirmArrivalFlg'] != 1 &&
			! $data['Cnl_Status']);

		// 商品情報は伝票登録前なら修正可能
		$results[self::GROUP_ITEMS] = ($data['DataStatus'] < 41 && ! $data['Cnl_Status']);

        // 注文追加情報は請求書発行前なら修正可能
		$sql = <<<EOQ
        SELECT *
FROM T_Order o
INNER JOIN T_Enterprise e ON e.EnterpriseId = o.EnterpriseId
WHERE  o.OrderSeq = :OrderSeq
EOQ;
        
                $entdata = $this->getAdapter()->query($sql)->execute(array(':OrderSeq' => $order_seq))->current();
                $billingAgentFlg   = $entdata['BillingAgentFlg'];
                $results[self::GROUP_ADDINFO] = ($data['DataStatus'] < 51 && $billingAgentFlg == 1);

		// ※ キャンセル依頼中の場合、すべての情報の修正は不可

		if(in_array($target_group, array_keys($results))) {
			return $results[$target_group];
		} else {
			return $results;
		}
	}

	/**
	 * 指定シーケンスの注文の、現在のステータス表示文字列を取得する
	 * @param int $order_seq 注文シーケンス
	 * @return string 指定注文のステータスを、事業者向けに表示する文字列
	 */
	public function getStatusLabel($order_seq) {
	    $data = $this->_getOrderForJudge($order_seq);
		if(! $data) throw new OrderEditorException(OrderEditorException::ERR_MSG_DATA_NOT_AVAILABLE);

        // トラッキングID取得
		$sql = " SELECT ExtraPayKey FROM AT_Order WHERE OrderSeq = :OrderSeq ";
		$aoData = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $order_seq))->current();
		$status = $data['DataStatus'];
		$close_reason = $data['CloseReason'];
		$cnl_status = $data['Cnl_Status'];
		$type = $data['Cnl_ReturnSaikenCancelFlg'];

		if($status == 91) { // クローズ時
			switch($close_reason) {
				case 2:	// キャンセルクローズ
					//債権返却済み
					if($type == '1') {
						return self::STATUS_CANCELED_SAIKEN;
					} else {
					return self::STATUS_CANCELED;
					}
				case 3:	// 与信NGクローズ
					return self::STATUS_NG;
				case 4:	// 損害確定クローズ
				    return '取引完了（損害確定）';
			    case 5:	// テスト注文クローズ
			        return '取引完了（テスト注文クローズ）';
		        case 6:	// 立替精算戻しクローズ
		            return '取引完了（立替精算戻しクローズ）';
			}
            //クローズ かつ トラッキングIDが設定されていた場合
			if(isset($aoData['ExtraPayKey'])){
			    $receiptMethod = $this->getReceiptMethod($order_seq);
			    return $receiptMethod['KeyContent'] . self::STATUS_SEND_CREDIT_BUYING_COMPLETE;
			}
			// 上記以外は単に取引完了
			return self::STATUS_CLOSED;

		} else {			// それ以外
		    // 一部入金
		    if($status == 61) {
		        if ($cnl_status == '0') { return '請求中（一部入金済）'; }
		        else if ($cnl_status == '1' && $type == '0') { return self::STATUS_CANCELED; }
		        else if ($cnl_status == '1' && $type == '1') { return self::STATUS_CLOSED_SAIKEN; }
		    }

			//債権返却前
			if($type == '1' && $cnl_status == '1') return self::STATUS_CLOSED_SAIKEN;

			// キャンセル依頼中
			if($cnl_status) return self::STATUS_CANCELED;

			// 与信中
			if($status < 31) return self::STATUS_CREDIT_PROGRESS;

			// 伝票登録待ち
			if($status < 41) return self::STATUS_WAIT_FOR_SHIPPING;

            //請求中 かつ トラッキングIDが設定されていた場合
			if($status != 41 && isset($aoData['ExtraPayKey'])){
			    $receiptMethod = $this->getReceiptMethod($order_seq);
			    return $receiptMethod['KeyContent'] . self::STATUS_SEND_CREDIT_BUYING_COMPLETE;
			}

			// 請求書印刷待ち or 請求中
			return $status == 41 ? self::STATUS_WAIT_FOR_INVOICING : self::STATUS_INVOICING;
		}
	}

    /**
     * Get Receipt Method
     */
	protected function getReceiptMethod($order_seq)
    {
		$sql = " 
SELECT KeyContent FROM M_Code WHERE CodeId = 198 
AND KeyCode = (SELECT ReceiptClass AS ReceiptClass FROM T_ReceiptControl WHERE OrderSeq = :OrderSeq LIMIT 1)
";
		$mc = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $order_seq))->current();

		return $mc;
    }

	/**
	 * ステータス判別用に、指定シーケンスの注文データを取得する
	 * @access protected
	 * @param int $order_seq 注文シーケンス
	 * @return array 注文データの連想配列
	 */
	protected function _getOrderForJudge($order_seq) {
	    if (!($this->_adapter instanceof Adapter))
	        throw new OrderEditorException(OrderEditorException::ERR_MSG_NO_DB_ADAPTER);

	    $sql = self::_getJudgeQuery() . " LIMIT 1 ";
	    $ri = $this->_adapter->query($sql)->execute(array($order_seq));
        if (!($ri->count() > 0)) {
            return null;
        }

        $rs = new ResultSet();
        $rs->initialize($ri);
        return $rs->toArray()[0];
	}

	/**
	 * 入力値の整形
	 * @param array $input postされたデータ
	 * @return array 整形済みの入力データ
	 */
	public function fixInputParams(array $input) {

        $pref_table = new TablePrefecture($this->getAdapter());

        $sql = " SELECT * FROM M_Prefecture WHERE PrefectureCode > 0 AND ValidFlg = 1 ORDER BY PrefectureCode ";
        $ri = $this->_adapter->query($sql)->execute(null);
        $rs = new ResultSet();
        $rs->initialize($ri);
        $prefs = $rs->toArray();

        // 日付データの整形処理
        if( isset($input['Order']) && isset($input['Order']['ReceiptOrderDate']) ) {
            $input['Order']['ReceiptOrderDate'] = $this->_fixDateString($input['Order']['ReceiptOrderDate']);
        }
        if( isset($input['Journal']) ) {
            $input['Journal']['Deli_JournalIncDate'] = $this->_fixDateString($input['Journal']['Deli_JournalIncDate']);
        }

        // 購入者住所関連の整形処理
        if( isset($input['Customer']) ) {
            // 郵便番号の正規化
            $input['Customer']['PostalCode'] = CoralValidateUtility::fixPostalCode($input['Customer']['PostalCode']);
            // 住所情報から都道府県情報を逆引き
            if(isset($input['Customer']['UnitingAddress'])) {
                foreach($prefs as $pref_row) {
                    if(strpos($input['Customer']['UnitingAddress'], $pref_row['PrefectureName']) === 0) {
                        $input['Customer']['PrefectureCode'] = $pref_row['PrefectureCode'];
                        $input['Customer']['PrefectureName'] = $pref_row['PrefectureName'];
                        break;
                    }
                }
                // 全角ハイフン（SJIS:0x815D）を半角マイナスに補正（印刷対応） → 再与信の（見かけ上の）誤判定になりそうなので保留
                //$input['Customer']['UnitingAddress'] = preg_replace( '/‐/', '-', $input['Customer']['UnitingAddress'] );
            }
        }

        // 配送先データの確定
        if(!$input['Order']['AnotherDeliFlg'] && isset($input['Customer'])) {
            $deli = array(
                    'DestNameKj' => $input['Customer']['NameKj'],
                    'DestNameKn' => $input['Customer']['NameKn'],
                    'PostalCode' => $input['Customer']['PostalCode'],
                    'UnitingAddress' => $input['Customer']['UnitingAddress'],
                    'Phone' => $input['Customer']['Phone']
            );
            $input['Destination'] = $deli;
            $input['Order']['AnotherDeliFlg'] = '0';
        }

        // 配送先住所関連の整形処理
        if( isset($input['Destination']) ) {
            // 郵便番号の正規化
            $input['Destination']['PostalCode'] = CoralValidateUtility::fixPostalCode($input['Destination']['PostalCode']);
            // 住所情報から都道府県情報を逆引き
            if(isset($input['Destination']['UnitingAddress'])) {
                foreach($prefs as $pref_row) {
                    if(strpos($input['Destination']['UnitingAddress'], $pref_row['PrefectureName']) === 0) {
                        $input['Destination']['PrefectureCode'] = $pref_row['PrefectureCode'];
                        $input['Destination']['PrefectureName'] = $pref_row['PrefectureName'];
                        break;
                    }
                }
                // 全角ハイフン（SJIS:0x815D）を半角マイナスに補正（印刷対応） → 再与信の（見かけ上の）誤判定になりそうなので保留
                //$input['Destination']['UnitingAddress'] = preg_replace( '/‐/', '-', $input['Destination']['UnitingAddress'] );
            }
        }

        return $input;
	}

	/**
	 * yyyy/MM/dd形式の日付文字列をyyyy-MM-dd形式に変換する
	 * @access protected
	 * @param string $date 日付文字列
	 * @return string yyyy-MM-dd形式に変換された日付文字列
	 */
	protected function _fixDateString($date) {
	    $date = trim((string)$date);
		if(! strlen($date)) return $date;

		$date = BaseGeneralUtils::convertWideToNarrow($date);
		return mb_ereg_replace('/', '-', $date);
	}

	/**
	 * 入力フォームを検証する
	 * @param array $input 入力データ
	 * @param null|boolean $mail_required メールアドレス必須フラグ
	 * @return array 検証結果
	 */
	public function validateForEdit(array $input, $mail_required = false, $oemId = 0) {

	    // 修正可能性を取得
		$modifiability = $this->judgeOrderModifiability($input['Order']['OrderSeq']);

		$errors = array();

		// 注文基本情報の検証
		if( $modifiability[self::GROUP_ORDER] ) {
			$errors['Order'] = $this->validateOrderGroup($input['Order']);
		}

		// 購入者情報の検証
		if( $modifiability[self::GROUP_CUSTOMER] ) {
			$errors['Customer'] = $this->validateCustomerGroup($input['Customer'], $mail_required, $input['Order']['CreditTransferRequestFlg'], $input['Order']['PayeasyFlg']);
		}

		// 配送先情報の検証
		if( $modifiability[self::GROUP_DELIVERY] && $input['Order']['AnotherDeliFlg'] ) {
			$errors['Destination'] = $this->validateDestinationGroup($input['Destination']);
		}

		// 配送伝票情報の検証
		if( $modifiability[self::GROUP_JOURNAL] ) {
			$errors['Journal'] = $this->validateJournalGroup($input['Journal']);
		}

		// 商品情報の検証
		if( $modifiability[self::GROUP_ITEMS] ) {
			$errors['Items'] = $this->validateItemsGroup($input['Items'], $oemId);
		}
        //注文追加情報の検証
		if($modifiability[self::GROUP_ADDINFO]){
            $errors['OrderAddInfo'] = $this->validateOrderAddInfoGroup($input['OrderAddInfo']);
          }

		return $errors;
	}

    /**
     * 注文基本情報を検証する
     * @param array $input
     * @return array
     */
    public function validateOrderGroup(array $input) {

        $errors = array();

        // OrderSeq: 注文シーケンス
        $key = 'OrderSeq';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'注文Seq'が指定されていません");
        }
        if (!isset($errors[$key]) && !is_numeric($input[$key])) {
            $errors[$key] = array("'注文Seq'の指定が不正です");
        }

        // ReceiptOrderDate: 注文日
        $key = 'ReceiptOrderDate';
        $input[$key] = $this->_fixDateString($input[$key]);
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'注文日'が未入力です");
        }
        if (!isset($errors[$key]) && !IsValidFormatDate($input[$key])) {
            $errors[$key] = array("'注文日'の形式が不正です");
        }

        // SiteId: 受付サイト
        $key = 'SiteId';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'サイトID'を指定してください");
        }
        if (!isset($errors[$key]) && !is_numeric($input[$key])) {
            $errors[$key] = array("'サイトID'の指定が不正です");
        }

        // 役務提供予定日
        // 選択したサイトが役務かどうか取得
        $sql = " SELECT ServiceTargetClass FROM T_Site WHERE SiteId = :SiteId ";
        $sTargetClass =$this->_adapter->query($sql)->execute(array(':SiteId' => $input[$key]))->current();

        // 選択したサイトが役務（ServiceTargetClass = 1）の場合、役務提供予定日は必須
        $key = 'ServiceExpectedDate';
        if ($sTargetClass['ServiceTargetClass'] == 1) {
            $isValueChange = ($input['ServiceExpectedDate'] == $input['OrgServiceExpectedDate']) ? false : true;// 値変更ありか？
            $input[$key] = $this->_fixDateString($input[$key]);
            if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
                $errors[$key] = array("'役務提供予定日'が未入力です");
            }
            if (!isset($errors[$key]) && !IsValidFormatDate($input[$key])) {
                $errors[$key] = array("'役務提供予定日'の形式が不正です");
            }
            // 30日以上過去日指定不可 ※ただし入力値がオリジナルの値と異なるときのみ検証
            $diffDate = BaseGeneralUtils::CalcSpanDays($input[$key], date('Y-m-d'));
            if (!isset($errors[$key]) && $isValueChange && ($diffDate >= 30)) {
                $errors[$key] = array("'役務提供予定日'に過去日は指定できません");
            }
        }

        // Ent_OrderId: 任意注文番号
        $key = 'Ent_OrderId';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 255)) {
            $errors[$key] = array("'任意注文番号'は255文字以内で入力してください");
        }

        // Ent_Note: 備考
        $key = 'Ent_Note';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 4000)) {
            $errors[$key] = array("'備考'は4,000文字以内で入力してください");
        }

        return $errors;
	}

    /**
     * 購入者情報を検証する
     * @param array $input
     * @param null|boolean $mail_required メールアドレス必須フラグ
     * @return array
     */
    public function validateCustomerGroup(array $input, $mail_required = false, $CreditTransferRequestFlg, $PayeasyFlg = false) {

        $errors = array();

        // NameKj: 氏名
        $key = 'NameKj';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'氏名'を入力してください");
        }
        if($PayeasyFlg){
            $err = CoralValidateUtility::checkPeNameKj($input[$key], false);
            if (isset($err)) {
                $errors[$key] = array($err['subject'].$err['message']);
            }
        }else{
            if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 160)) {
                $errors[$key] = array("'氏名'は160文字以内で入力してください");
            }
        }

        // NameKn: よみがな
        $key = 'NameKn';
        if($PayeasyFlg){
            $err = CoralValidateUtility::checkPeNameKn($input[$key], false);
            if (isset($err)) {
                $errors[$key] = array($err['subject'].$err['message']);
            }
        }else{
            if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 160)) {
                $errors[$key] = array("'よみがな'は160文字以内で入力してください");
            }
        }

        // PostalCode: 郵便番号
        $key = 'PostalCode';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'郵便番号'を入力してください");
        }
        $cvpc = new CoralValidatePostalCode();
        if (!isset($errors[$key]) && !$cvpc->isValid($input[$key])) {
            $errors[$key] = array("'郵便番号'の入力が不正です");
        }

        // UnitingAddress: 住所
        $key = 'UnitingAddress';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'住所'を入力してください");
        }
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 4000)) {
            $errors[$key] = array("'住所'が長すぎます");
        }

        // Phone: 電話番号
        $key = 'Phone';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'電話番号'を入力してください");
        }
        if($PayeasyFlg){
            $err = CoralValidateUtility::checkPePhoneNumber($input[$key], false);
            if (isset($err)) {
                $errors[$key] = array($err['subject'].$err['message']);
            }
        }
        $cvp = new CoralValidatePhone();
        if (!isset($errors[$key]) && !$cvp->isValid($input[$key])) {
            $errors[$key] = array("'電話番号'の入力が不正です");
        }
        // MailAddress: メールアドレス
        $key = 'MailAddress';
        if ($mail_required) {
            if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
                $errors[$key] = array("'メールアドレス'を入力してください");
            }
        }else if ($input['CreditTransferFlg'] == 1 && !isset($errors[$key]) && ($CreditTransferRequestFlg == 1) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'メールアドレス'を入力してください");
        }else if ($input['CreditTransferFlg'] == 2 && !isset($errors[$key]) && ($CreditTransferRequestFlg == 1) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'メールアドレス'を入力してください");
        }else if ($input['CreditTransferFlg'] == 3 && !isset($errors[$key]) && ($CreditTransferRequestFlg == 1) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'メールアドレス'を入力してください");
        }
        $cvmm = new CoralValidateMultiMail();
        if (!isset($errors[$key]) && (strlen($input[$key]) > 0) && !$cvmm->isValid($input[$key])) {
            $errors[$key] = array("'メールアドレス'の入力が不正です");
        }
        if($PayeasyFlg){
            $err = CoralValidateUtility::checkPeMailAddress($input[$key], false);
            if (isset($err)) {
                $errors[$key] = array($err['subject'].$err['message']);
            }
        }else{
            if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 255)) {
                $errors[$key] = array("'メールアドレス'は255文字以内で入力してください");
            }
        }

        // EntCustId: 加盟店顧客番号
        $key = 'EntCustId';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 255)) {
            $errors[$key] = array("'加盟店顧客番号'は255文字以内で入力してください");
        }

        // Occupation: 職業
        $key = 'Occupation';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 255)) {
            $errors[$key] = array("'職業'は255文字以内で入力してください");
        }

        // CorporateName: 法人名
        $key = 'CorporateName';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 255)) {
            $errors[$key] = array("'法人名'は255文字以内で入力してください");
        }

        // DivisionName: 部署名
        $key = 'DivisionName';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 255)) {
            $errors[$key] = array("'部署名'は255文字以内で入力してください");
        }

        // CpNameKj: 担当者名
        $key = 'CpNameKj';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 160)) {
            $errors[$key] = array("'担当者名'は160文字以内で入力してください");
        }

        return $errors;
    }

	/**
	 * 配送先情報を検証する
	 * @param array $input
	 * @return array
	 */
	public function validateDestinationGroup(array $input) {

        $errors = array();

        // DestNameKj: 氏名
        $key = 'DestNameKj';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'配送先氏名'を入力してください");
        }
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 160)) {
            $errors[$key] = array("'配送先氏名'は160文字以内で入力してください");
        }

        // DestNameKn: よみがな
        $key = 'DestNameKn';
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 160)) {
            $errors[$key] = array("'配送先よみがな'は160文字以内で入力してください");
        }

        // PostalCode: 郵便番号
        $key = 'PostalCode';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'配送先郵便番号'を入力してください");
        }
        $cvpc = new CoralValidatePostalCode();
        if (!isset($errors[$key]) && !$cvpc->isValid($input[$key])) {
            $errors[$key] = array("'配送先郵便番号'の入力が不正です");
        }

        // UnitingAddress: 住所
        $key = 'UnitingAddress';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'配送先住所'を入力してください");
        }
        if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 4000)) {
            $errors[$key] = array("'配送先住所'が長すぎます");
        }

        // Phone: 電話番号
        $key = 'Phone';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'配送先電話番号'を入力してください");
        }
        $cvp = new CoralValidatePhone();
        if (!isset($errors[$key]) && !$cvp->isValid($input[$key])) {
            $errors[$key] = array("'配送先電話番号'の入力が不正です");
        }

        return $errors;
	}

	/**
	 * 配送伝票情報を検証する
	 * @param array $input
	 * @return array
	 */
	public function validateJournalGroup(array $input) {

        $errors = array();

        // Deli_DevliveryMethod: 配送会社
        $key = 'Deli_DeliveryMethod';
        if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
            $errors[$key] = array("'配送会社'を指定してください");
        }
        if (!isset($errors[$key]) && !is_numeric($input[$key])) {
            $errors[$key] = array("'配送会社'の指定が不正です");
        }

        // Deli_JournalNumber: 伝票番号
        $key = 'Deli_JournalNumber';
        $journalRegistClass = $this->_adapter->query(" SELECT JournalRegistClass FROM M_DeliveryMethod WHERE DeliMethodId = :DeliMethodId "
                )->execute(array(':DeliMethodId' => $input['Deli_DeliveryMethod']))->current()['JournalRegistClass'];
        if ($journalRegistClass != 0) {
            // 伝票番号不要配送方法が選択されていない時は検証を要する
            if (!isset($errors[$key]) && !(strlen($input[$key]) > 0)) {
                $errors[$key] = array("'伝票番号'を指定してください");
            }
            if (!isset($errors[$key]) && !(mb_strlen($input[$key]) <= 255)) {
                $errors[$key] = array("'伝票番号'は255文字以内で入力してください");
            }
        }

        // 配送会社の検証エラーがない場合は値域の確認を行う
        if(! is_array($errors['Deli_DeliveryMethod'])) {
            if(! $this->_isValidDeliveryMethod($input['Deli_DeliveryMethod'])) {
                $errors['Deli_DeliveryMethod'] = array("'配送会社'の指定が不正です");
            }
        }
        // 伝票番号の検証エラーがない場合は書式の確認を行う
        if(! is_array($errors['Deli_DeliveryMethod']) && ! is_array($errors['Deli_JournalNumber'])) {
            if(! $this->_isValidJournalNumber($input['Deli_DeliveryMethod'], $input['Deli_JournalNumber'])) {
                $errors['Deli_JournalNumber'] = array("'伝票番号'の書式が間違っています。配送会社ごとに決められた桁数で入力する必要があります");
            }
        }

        return $errors;
	}

	/**
	 * 配送方法のIDが正しいかを判別する
	 * @access protected
	 * @param int $deli_method 配送方法ID
	 * @return boolean
	 */
	protected function _isValidDeliveryMethod($deli_method) {
	    $master = $this->_getDeliMethod($deli_method);
		return $master ? true : false;
	}

	/**
	 * 指定の伝票番号が、指定の配送方法のルールに合致するかを判断する
	 * @access protected
	 * @param int $deli_method 配送方法ID
	 * @param string $journal 伝票番号
	 * @return boolean
	 */
	protected function _isValidJournalNumber($deli_method, $journal) {
	    $master = $this->_getDeliMethod($deli_method);
		if(! $master) return false;
		$reg = $master['ValidateRegex'];
		return mb_ereg_match($reg, $journal) ? true : false;
	}

	/**
	 * 指定のIDに一致する配送方法マスターデータを取得する
	 * @access protected
	 * @param int $deli_method 配送方法ID
	 * @return array
	 */
	protected function _getDeliMethod($deli_method) {
        $masters = new TableDeliMethod($this->_adapter);
        $row = $masters->find($deli_method)->current();
        return ($row) ? $row : null;
	}

	/**
	 * 商品明細情報を検証する
	 * @param array $input
	 * @return array
	 */
	public function validateItemsGroup(array $input, $oemId = 0) {

        $errors = array();

        foreach($input as $index => $data) {
            $data_class = (int)$data['DataClass'];

            // [$data_class]が、1以上4以下に該しない場合は、1に丸める
            if ($data_class > 4 || $data_class < 1) $data_class = 1;

            $unit_price_label = '';
            if      ($data_class == 1) { $unit_price_label = '単価'  ; }
            else if ($data_class == 2) { $unit_price_label = '送料'  ; }
            else if ($data_class == 3) { $unit_price_label = '手数料'; }
            else if ($data_class == 4) { $unit_price_label = '外税額'; }

            $item_index = $index + 1;

            // 2015/06/18 Del Y.Suzuki 新規行の場合、OrderItemIdは存在しないので、validationから外す Stt
//             // OrderItemId: 注文商品ID
//             $key = 'OrderItemId';
//             if (!isset($errors[$index][$key]) && !(strlen($data[$key]) > 0)) {
//                 $errors[$index][$key] = array("不正な商品データが指定されました");
//             }
//             if (!isset($errors[$index][$key]) && !is_numeric($data[$key])) {
//                 $errors[$index][$key] = array("不正な商品データが指定されました");
//             }
            // 2015/06/18 Del Y.Suzuki 新規行の場合、OrderItemIdは存在しないので、validationから外す End

            // UnitPrice: 単価
            $key = 'UnitPrice';
            if (!isset($errors[$index][$key]) && !(strlen($data[$key]) > 0)) {
                $errors[$index][$key] = array(sprintf("'%s'を入力してください", $unit_price_label));
            }
            if (!isset($errors[$index][$key]) && !is_numeric($data[$key])) {
                $errors[$index][$key] = array(sprintf("'%s'の入力が不正です", $unit_price_label));
            }

            if($data_class == 1) {

                // ItemNameKj: 商品名
                $key = 'ItemNameKj';
                if (!isset($errors[$index][$key]) && !(strlen($data[$key]) > 0)) {
                    $errors[$index][$key] = array(sprintf("'商品名%s'を指定してください", ($data['OrderItemId'] != 'a') ? nvl($item_index, '') : '(追加行)'));
                }
                if (!isset($errors[$index][$key]) && !(mb_strlen($data[$key]) <= 255)) {
                    $errors[$index][$key] = array(sprintf("'商品名%s'は255文字以内で指定してください", ($data['OrderItemId'] != 'a') ? nvl($item_index, '') : '(追加行)'));
                }

                // ItemNum: 数量
                $key = 'ItemNum';
                if (!isset($errors[$index][$key]) && !(strlen($data[$key]) > 0)) {
                    $errors[$index][$key] = array(sprintf("'数量%s'を指定してください", ($data['OrderItemId'] != 'a') ? nvl($item_index, '') : '(追加行)'));
                }
                if (!isset($errors[$index][$key]) && !is_numeric($data[$key])) {
                    $errors[$index][$key] = array(sprintf("'数量%s'の指定が不正です", ($data['OrderItemId'] != 'a') ? nvl($item_index, '') : '(追加行)'));
                }
                if (!isset($errors[$index][$key]) && ($oemId == LogicConstant::OEM_ID_SMBC) && ($data[$key] > 999)) {
                    $errors[$index][$key] = array(sprintf("'数量%s'は3桁以内を指定してください", ($data['OrderItemId'] != 'a') ? nvl($item_index, '') : '(追加行)'));
                }
            }
        }

        return $errors;
	}
    /**
	 * 注文追加情報を検証する
	 * @param array $input
	 * @return array
	 */
	public function validateOrderAddInfoGroup(array $input){
        $errors = array();
      foreach($input as $key => $data){
          //自由入力情報 Free1,Free2...Free20
          if(strpos($key, 'Free') === 0){
              if(!isset($errors[$key]) && isset($data)){
                  //文字エンコード UTF-8->SJIS
                  $str = mb_convert_encoding($data, "SJIS");
                  //61バイト以上はエラー
                  if(strlen($str) > 50){
                      $line_num = substr($key, 4, strlen($key) - 1);
                      $errors[$key] = array(sprintf("自由入力情報は1行に最大半角50文字以内で入力してください(%s行目)",$line_num));
                  }
              }
          }
      }
      return $errors;
  }
}

/**
 * OrderEditor用ランタイム例外
 */
class OrderEditorException extends \Exception {
	/**
	 * エラーメッセージ定数：DBアダプタ未指定
	 * @var string
	 */
	const ERR_MSG_NO_DB_ADAPTER = 'database adapter is not specified.';

	/**
	 * エラーメッセージ定数：DBアダプタの型不正
	 * @var string
	 */
	const ERR_MSG_INVALID_DB_ADAPTER = 'invalid database adapter specified.';

	/**
	 * エラーメッセージ定数：指定データが存在しない
	 * @var string
	 */
	const ERR_MSG_DATA_NOT_AVAILABLE = 'request data not available.';

	/**
	 * 原因となった例外
	 *
	 * @var Exception
	 */
	protected $_innerException;

	/**
	 * OrderEditorExceptionの新しいインスタンスを初期化する
	 *
	 * @param string|null $message エラーメッセージ
	 * @param int|null $code エラーコード
	 * @param \Exception|null 原因の例外
	 */
	public function __construct($message = null, $code = 0, $innerException = null) {
		parent::__construct($message, $code);

		$this->_innerException = ( $innerException instanceof \Exception ) ? $innerException : null;
	}

	/**
	 * この例外の原因となった基の例外を取得する
	 *
	 * @return \Exception|null
	 */
	public function getInnerException() {
		return $this->_innerException;
	}
}

