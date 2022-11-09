<?php
namespace oemmember\classes;

use Coral\Coral\Form\CoralFormManager;
use Coral\Coral\Form\CoralFormItem;
use Coral\Coral\Validate\CoralValidateUtility;
use Zend\Db\Sql\Ddl\Column\Boolean;

/**
 * 注文情報の入力を既定するユーティリティクラス。
 *
 * createFormManagerスタティックメソッドやcreateDataBuilderスタティックメソッドで
 * データ生成クラスのインスタンスを取得する
 *
 */
class OrderInputInfo {
	/**
	 * T_Orderに関するフォームフィールドグループを指定するキー
	 *
	 * @var string
	 */
	const ARRKEY_ORDER = 'order';

	/**
	 * T_Customerに関するフォームフィールドグループを指定するキー
	 *
	 * @var string
	 */
	const ARRKEY_CUSTO = 'customer';

	/**
	 * T_DeliveryDestinationに関するフォームフィールドグループを指定するキー
	 */
	const ARRKEY_DELIV = 'delivery';

	/**
	 * T_OrderItemsに関するフォームフィールドグループを指定するキー
	 */
	const ARRKEY_ITEMS = 'order_items';

	const REGEXP_DATETIME = '/^\d{4}\/\d{2}\/\d{2}$/';

	public static function createFormManager(array $validationMap = array(), int $creditTransferFlg = 0, $isPeyeasyOem = false) {
		$result = new CoralFormManager();
    //ペイジー対象OEMの場合、氏名カナを必須にする
    $nameKnAttributes = array(
      'size'	=> 30
    );
    if($isPeyeasyOem) {
        $nameKnValidation = '/^.{1,30}$/';
        $nameKjDescription = '全角のみ可です。姓と名の間を全角スペースで区切ってください。<br/>例) 吉村　一郎';
        $nameKnAttributes['class']	= 'must';
				$nameKnDescription = '全角のみ可です。姓と名の間を全角スペースで区切ってください。<br/>例) ヨシムラ　イチロウ';
        $phoneValidation = '/^[-0-9ー０-９]{1,13}$/';
        $mailAddressValidation = '/^.{0,128}$/';
    }else{
        $nameKnValidation = nvl($validationMap['T_Customer.NameKn'], '/^.{0,30}$/');
        $nameKjDescription = '全角／半角のどちらでも可です。姓と名の間をスペースで区切ってください。<br/>例) 吉村　一郎';
				$nameKnDescription = '全角／半角のどちらでも可です。姓と名の間をスペースで区切ってください。<br/>例) ヨシムラ　イチロウ';
        $phoneValidation = CoralValidateUtility::PHONE_NUMBER;
        $mailAddressValidation = CoralValidateUtility::EMAIL_ADDRESS;
    }

		$result
			->createGroup( self::ARRKEY_ORDER, '基本情報', '' )
			->createGroup( self::ARRKEY_CUSTO, 'ご注文者（請求先）情報', '⇒与信・当社から注文者様への請求書・検索に使用されます。' )
			->createGroup( self::ARRKEY_DELIV, '別配送先情報', '⇒与信・当社から注文者様への請求書・検索に使用されます。' )
			->createGroup( self::ARRKEY_ITEMS, '商品情報（請求書に記載される項目）', '⇒当社から注文者様への請求書・検索に使用されます。' )
			// 基本情報
			->addItem( self::ARRKEY_ORDER, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'O_ReceiptOrderDate',
				CoralFormItem::ID			=> 'o_receipt_order_date',
				CoralFormItem::COLUMN_MAP	=> 'T_Order.ReceiptOrderDate',
				CoralFormItem::CAPTION		=> '注文日',
				CoralFormItem::DESCRIPTION	=> '[カレンダーで選択]で日付を選択するか、[今日]をクリックして入力してください<br />※キーボードからの入力はできません',
				CoralFormItem::VALIDATION	=> self::REGEXP_DATETIME,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 12,
					'class'	=> 'must',
					'readonly' => 'readonly'
				)
			) ) )
			->addItem( self::ARRKEY_ORDER, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
		        CoralFormItem::NAME			=> 'O_ServiceExpectedDate',
		        CoralFormItem::ID			=> 'o_service_expected_date',
		        CoralFormItem::COLUMN_MAP	=> 'T_Order.ServiceExpectedDate',
		        CoralFormItem::CAPTION		=> '役務提供予定日',
		        CoralFormItem::DESCRIPTION	=> '[カレンダーで選択]で日付を選択するか、[今日]をクリックして入力してください<br />※キーボードからの入力はできません。注意)過去日は指定できません',
		        CoralFormItem::VALIDATION	=> self::REGEXP_DATETIME,
		        CoralFormItem::ATTRIBUTES	=> array(
		                'size'	=> 12,
		                'class'	=> 'must',
		                'readonly' => 'readonly'
		        )
			) ) )
			->addItem( self::ARRKEY_ORDER, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_SELECT,
				CoralFormItem::NAME			=> 'O_SiteId',
				CoralFormItem::ID			=> 'o_siteid',
				CoralFormItem::COLUMN_MAP	=> 'T_Order.SiteId',
				CoralFormItem::CAPTION		=> '受付サイト',
				CoralFormItem::DESCRIPTION	=> 'この注文を受け付けたサイトを選択してください',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::NON_NEGATIVE_INTEGER,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 1,
					'class'	=> 'must',
				    'onChange' => 'onChangeSite()'
				)
			) ) )
			->addItem( self::ARRKEY_ORDER, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'O_Ent_OrderId',
				CoralFormItem::ID			=> 'o_ent_orderid',
				CoralFormItem::COLUMN_MAP	=> 'T_Order.Ent_OrderId',
				CoralFormItem::CAPTION		=> '任意注文番号',
				CoralFormItem::DESCRIPTION	=> 'この注文を識別する任意の文字や番号を入力してください。',
				CoralFormItem::VALIDATION	=> nvl($validationMap['T_Order.Ent_OrderId'], '/^.{0,255}$/'),
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 30
				)
			) ) )
			->addItem( self::ARRKEY_ORDER, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXTAREA,
				CoralFormItem::NAME			=> 'O_Ent_Note',
				CoralFormItem::ID			=> 'o_ent_note',
				CoralFormItem::COLUMN_MAP	=> 'T_Order.Ent_Note',
				CoralFormItem::CAPTION		=> '備考（メモ）',
				CoralFormItem::DESCRIPTION	=> 'この注文情報の備考をメモできます',
				CoralFormItem::VALIDATION	=> nvl($validationMap['T_Order.Ent_Note'], '/^.{0,4000}$/m'),
				CoralFormItem::ATTRIBUTES	=> array(
					'rows'	=> 3,
					'cols'	=> 30
				)
			) ) )
			->addItem( self::ARRKEY_ORDER, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_SELECT,
		        CoralFormItem::NAME			=> 'O_T_OrderClass',
		        CoralFormItem::ID			=> 'o_t_orderclass',
		        CoralFormItem::COLUMN_MAP	=> 'T_Order.T_OrderClass',
		        CoralFormItem::CAPTION		=> 'テスト注文',
		        CoralFormItem::DESCRIPTION	=> 'テスト注文の場合チェックを入れます（与信結果の選択ができます）。',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::INTEGER,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 1,
				)
			) ) )
			// 注文者情報
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_PostalCode',
				CoralFormItem::ID			=> 'c_postalcode',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.PostalCode',
				CoralFormItem::CAPTION		=> '郵便番号',
				CoralFormItem::DESCRIPTION	=> '半角数字とハイフン（「-」）で入力してください。<br/>例) 1400002、140-0002',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::POSTAL_CODE,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 10,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_SELECT,
				CoralFormItem::NAME			=> 'C_PrefectureName',
				CoralFormItem::ID			=> 'c_prefecturename',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.PrefectureCode',
				CoralFormItem::CAPTION		=> '都道府県名',
				CoralFormItem::DESCRIPTION	=> '都道府県名をリストから選択してください。<br/>例)東京都, 北海道, 奈良県',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::POSITIVE_INTEGER,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 1,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_City',
				CoralFormItem::ID			=> 'c_city',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.City',
				CoralFormItem::CAPTION		=> '市・区・郡',
				CoralFormItem::DESCRIPTION	=> '市や行政指定区、郡部の名前を漢字でご入力ください。<br/>例) 台東区, 札幌市中央区, 生駒郡三郷町',
				CoralFormItem::VALIDATION	=> '/^.{1,30}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 20,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_Town',
				CoralFormItem::ID			=> 'c_town',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.Town',
				CoralFormItem::CAPTION		=> '町名',
				CoralFormItem::DESCRIPTION	=> '都道府県・市区郡とビル名を除いた町名と番地をご入力ください。番地は半角全角のどちらでも可です。<br/>例) 駒形1-1-1, 北一条東1-2-3, 城山台1-1-1',
				CoralFormItem::VALIDATION	=> '/^.{1,30}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 30,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_Building',
				CoralFormItem::ID			=> 'c_building',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.Building',
				CoralFormItem::CAPTION		=> 'ビル名等',
				CoralFormItem::DESCRIPTION	=> '建物の名前や部屋番号などをご入力ください。<br/>例) ○○ビル, ××マンション1013号室',
				CoralFormItem::VALIDATION	=> '/^.{0,235}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 50
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_Address',
				CoralFormItem::ID			=> 'c_address',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.UnitingAddress',
				CoralFormItem::CAPTION		=> '住所',
				CoralFormItem::DESCRIPTION	=> 'キャンセル再登録 機能にて、注文を再登録した場合には、登録した住所は、「住所」欄にまとめて登録されます。',
				CoralFormItem::VALIDATION	=> '/^.{1,4000}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 65,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_NameKj',
				CoralFormItem::ID			=> 'c_namekj',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.NameKj',
				CoralFormItem::CAPTION		=> '氏名',
				CoralFormItem::DESCRIPTION	=> $nameKjDescription,
				CoralFormItem::VALIDATION	=> nvl($validationMap['T_Customer.NameKj'], '/^.{1,30}$/'),
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 30,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_NameKn',
				CoralFormItem::ID			=> 'c_namekn',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.NameKn',
				CoralFormItem::CAPTION		=> '氏名カナ',
				CoralFormItem::DESCRIPTION	=> $nameKnDescription,
				CoralFormItem::VALIDATION	=> $nameKnValidation,
				CoralFormItem::ATTRIBUTES	=> $nameKnAttributes
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_Phone',
				CoralFormItem::ID			=> 'c_phone',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.Phone',
				CoralFormItem::CAPTION		=> 'お電話番号<br/>（携帯可）',
				CoralFormItem::DESCRIPTION	=> '市外局番、局番などをハイフン（「-」）で区切り、半角数字で入力してください。<br/>例) 03-3333-3333',
				CoralFormItem::VALIDATION	=> $phoneValidation,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 20,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_MailAddress',
				CoralFormItem::ID			=> 'c_mailaddress',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.MailAddress',
				CoralFormItem::CAPTION		=> 'メールアドレス',
				CoralFormItem::DESCRIPTION	=> '半角英数字で入力してください。<br/>例) yoshimura@example.com',
				CoralFormItem::VALIDATION	=> $mailAddressValidation,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 40,
					'class'	=> 'must'
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
		        CoralFormItem::NAME			=> 'C_EntCustId',
		        CoralFormItem::ID			=> 'c_entcustid',
		        CoralFormItem::COLUMN_MAP	=> 'T_Customer.EntCustId',
		        CoralFormItem::CAPTION		=> '加盟店顧客番号',
		        CoralFormItem::DESCRIPTION	=> '任意でご入力ください。<br/>例) ECSTNO150001',
		        CoralFormItem::VALIDATION	=> nvl($validationMap['T_Customer.EntCustId'], '/^.{0,255}$/'),
		        CoralFormItem::ATTRIBUTES	=> array(
		                'size'	=> 30
		        )
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'C_Occupation',
				CoralFormItem::ID			=> 'c_occupation',
				CoralFormItem::COLUMN_MAP	=> 'T_Customer.Occupation',
				CoralFormItem::CAPTION		=> '職業',
				CoralFormItem::DESCRIPTION	=> '任意でご入力ください。<br/>例) 会社員, 学生, 自営業',
				CoralFormItem::VALIDATION	=> nvl($validationMap['T_Customer.Occupation'], '/^.{0,255}$/'),
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 40
				)
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
		        CoralFormItem::NAME			=> 'C_CorporateName',
		        CoralFormItem::ID			=> 'c_corporatename',
		        CoralFormItem::COLUMN_MAP	=> 'T_Customer.CorporateName',
		        CoralFormItem::CAPTION		=> '法人名',
		        CoralFormItem::DESCRIPTION	=> '任意でご入力ください。<br/>例) エービーシー商事',
		        CoralFormItem::VALIDATION	=> nvl($validationMap['T_Customer.CorporateName'], '/^.{0,255}$/'),
		        CoralFormItem::ATTRIBUTES	=> array(
		                'size'	=> 50
		        )
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
		        CoralFormItem::NAME			=> 'C_DivisionName',
		        CoralFormItem::ID			=> 'c_divisionname',
		        CoralFormItem::COLUMN_MAP	=> 'T_Customer.DivisionName',
		        CoralFormItem::CAPTION		=> '部署名',
		        CoralFormItem::DESCRIPTION	=> '任意でご入力ください。<br/>例) 総務部, 営業部, 購買部',
		        CoralFormItem::VALIDATION	=> nvl($validationMap['T_Customer.DivisionName'], '/^.{0,255}$/'),
		        CoralFormItem::ATTRIBUTES	=> array(
		                'size'	=> 30
		        )
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
		        CoralFormItem::NAME			=> 'C_CpNameKj',
		        CoralFormItem::ID			=> 'c_cpnamekj',
		        CoralFormItem::COLUMN_MAP	=> 'T_Customer.CpNameKj',
		        CoralFormItem::CAPTION		=> '担当者名',
		        CoralFormItem::DESCRIPTION	=> '任意でご入力ください。<br/>例) 山田　太郎',
		        CoralFormItem::VALIDATION	=> nvl($validationMap['T_Customer.CpNameKj'], '/^.{0,160}$/'),
		        CoralFormItem::ATTRIBUTES	=> array(
		                'size'	=> 30
		        )
			) ) )
			->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_SELECT,
		        CoralFormItem::NAME			=> 'O_ClaimSendingClass',
		        CoralFormItem::ID			=> 'o_claimsendingclass',
		        CoralFormItem::COLUMN_MAP	=> 'T_Order.ClaimSendingClass',
		        CoralFormItem::CAPTION		=> '請求書別送',
		        CoralFormItem::DESCRIPTION	=> '同梱請求書加盟店で請求先＝配送先の場合で、別送請求書となる場合にチェックをつけてください。',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::INTEGER,
				CoralFormItem::ATTRIBUTES	=> array(
					'size'	=> 1,
				)
			) ) );

			if (($creditTransferFlg == 1) || ($creditTransferFlg == 2) || ($creditTransferFlg == 3)) {
			    $result
			    ->addItem( self::ARRKEY_CUSTO, new CoralFormItem( array(
		        CoralFormItem::TYPE			=> CoralFormItem::TYPE_SELECT,
		        CoralFormItem::NAME			=> 'O_CreditTransferRequestFlg',
		        CoralFormItem::ID			=> 'o_CreditTransferRequestFlg',
		        CoralFormItem::COLUMN_MAP	=> 'AT_Order.CreditTransferRequestFlg',
		        CoralFormItem::CAPTION		=> '口座振替',
		        CoralFormItem::DESCRIPTION	=> '口座振替の利用状況を選択してください。',
		        CoralFormItem::VALIDATION	=> CoralValidateUtility::INTEGER,
		        CoralFormItem::ATTRIBUTES	=> array(
	                'size'	=> 1,
		        )
			) ) );
			}

			// 別配送先情報
			$result->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_PostalCode',
				CoralFormItem::ID			=> 'd_postalcode',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.PostalCode',
				CoralFormItem::CAPTION		=> '郵便番号',
				CoralFormItem::DESCRIPTION	=> '半角数字とハイフン（「-」）で入力してください。<br/>例) 1400002、140-0002',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::POSTAL_CODE,
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 10,
					'class'	=> 'must_if_enabled'
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_SELECT,
				CoralFormItem::NAME			=> 'D_PrefectureName',
				CoralFormItem::ID			=> 'd_prefecturename',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.PrefectureCode',
				CoralFormItem::CAPTION		=> '都道府県名',
				CoralFormItem::DESCRIPTION	=> '都道府県名をリストから選択してください。<br/>例)東京都, 北海道, 奈良県',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::POSITIVE_INTEGER,
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 1,
					'class'	=> 'must_if_enabled'
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_City',
				CoralFormItem::ID			=> 'd_city',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.City',
				CoralFormItem::CAPTION		=> '市・区・郡',
				CoralFormItem::DESCRIPTION	=> '市や行政指定区、郡部の名前を漢字でご入力ください。<br/>例) 台東区, 札幌市中央区, 生駒郡三郷町',
				CoralFormItem::VALIDATION	=> '/^.{1,30}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 20,
					'class'	=> 'must_if_enabled'
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_Town',
				CoralFormItem::ID			=> 'd_town',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.Town',
				CoralFormItem::CAPTION		=> '町名',
				CoralFormItem::DESCRIPTION	=> '都道府県・市区郡とビル名を除いた町名と番地をご入力ください。番地は半角全角のどちらでも可です。<br/>例) 駒形1-1-1, 北一条東1-2-3, 城山台1-1-1',
				CoralFormItem::VALIDATION	=> '/^.{1,30}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 30,
					'class'	=> 'must_if_enabled'
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_Building',
				CoralFormItem::ID			=> 'd_building',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.Building',
				CoralFormItem::CAPTION		=> 'ビル名等',
				CoralFormItem::DESCRIPTION	=> '建物の名前や部屋番号などをご入力ください。<br/>例) ○○ビル, ××マンション1013号室',
				CoralFormItem::VALIDATION	=> '/^.{0,235}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 50
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_Address',
				CoralFormItem::ID			=> 'd_address',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.UnitingAddress',
				CoralFormItem::CAPTION		=> '住所',
				CoralFormItem::DESCRIPTION	=> 'キャンセル再登録 機能にて、注文を再登録した場合には、登録した住所は、「住所」欄にまとめて登録されます。',
				CoralFormItem::VALIDATION	=> '/^.{1,4000}$/',
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 65,
					'class'	=> 'must_if_enabled'
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_NameKj',
				CoralFormItem::ID			=> 'd_namekj',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.DestNameKj',
				CoralFormItem::CAPTION		=> '氏名',
				CoralFormItem::DESCRIPTION	=> '全角／半角のどちらでも可です。姓と名の間をスペースで区切ってください。<br/>例) 吉村　一郎',
				CoralFormItem::VALIDATION	=> nvl($validationMap['T_DeliveryDestination.DestNameKj'], '/^.{1,30}$/'),
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 30,
					'class'	=> 'must_if_enabled'
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_NameKn',
				CoralFormItem::ID			=> 'd_namekn',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.DestNameKn',
				CoralFormItem::CAPTION		=> '氏名カナ',
				CoralFormItem::DESCRIPTION	=> '全角／半角のどちらでも可です。姓と名の間をスペースで区切ってください。<br/>例) ヨシムラ　イチロウ',
				CoralFormItem::VALIDATION	=> nvl($validationMap['T_DeliveryDestination.DestNameKn'], '/^.{0,30}$/'),
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 30
				)
			) ) )
			->addItem( self::ARRKEY_DELIV, new CoralFormItem( array(
				CoralFormItem::TYPE			=> CoralFormItem::TYPE_TEXT,
				CoralFormItem::NAME			=> 'D_Phone',
				CoralFormItem::ID			=> 'd_phone',
				CoralFormItem::COLUMN_MAP	=> 'T_DeliveryDestination.Phone',
				CoralFormItem::CAPTION		=> 'お電話番号<br/>（携帯可）',
				CoralFormItem::DESCRIPTION	=> '市外局番、局番などをハイフン（「-」）で区切り、半角数字で入力してください。<br/>例) 03-3333-3333',
				CoralFormItem::VALIDATION	=> CoralValidateUtility::PHONE_NUMBER,
				CoralFormItem::ATTRIBUTES	=> array(
					'disabled'	=> 'disabled',
					'size'	=> 20,
					'class'	=> 'must_if_enabled'
				)
			) ) )
			;

		return $result;
	}
}
