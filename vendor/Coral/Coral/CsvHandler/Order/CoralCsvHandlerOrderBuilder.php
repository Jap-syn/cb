<?php
namespace Coral\Coral\CsvHandler\Order;

use Zend\Json\Json;
use Zend\Db\Adapter\Adapter;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Coral\Validate\CoralValidateUtility;
use models\Logic\LogicPayeasy;
use models\Logic\LogicConstant;

/**
 * CSV行データの内容を解析し、注文データの連想配列を構築するクラス
 * 入力値の検証および不足データの補間はこのクラスに集約される
 *
 */
class CoralCsvHandlerOrderBuilder {
	/**
	 * 注文データグループ用のグループキー定数
	 *
	 * @var string
	 */
	const GROUP_ORDER = 'order';

	/**
	 * 顧客データグループ用のグループキー定数
	 *
	 * @var string
	 */
	const GROUP_CUSTOMER = 'customer';

	/**
	 * 配送先データグループ用のグループキー定数
	 *
	 * @var string
	 */
	const GROUP_DELIVERY = 'delivery';

	/**
	 * 商品データグループ用のグループキー定数
	 *
	 * @var string
	 */
	const GROUP_ITEMS = 'order_items';

	/**
	 * サマリデータグループ用のグループキー定数
	 *
	 * @var string
	 */
	const GROUP_SUMMARY = 'summary';

	/**
	 * TablePrefectureのRowsetを指定するオプション定数
	 *
	 * @var string
	 */
	const OPTIONS_PREFECTURE_ROWSET = 'prefectures';

	/**
	 * サイト情報配列を指定するオプション定数
	 *
	 * @var string
	 */
	const OPTIONS_SITE_INFO = 'siteInfo';

	/**
	 * CSVスキーマ
	 *
	 * @var array
	 */
	protected $_schema;

	/**
	 * TablePrefectureの有効なRowset
	 *
	 * @var array
	 */
	protected $_prefectures;

	/**
	 * データを蓄積するグループ配列。要素がグループ分けされたデータになる。
	 *
	 * @var array
	 */
	protected $_groups;

	/**
	 * 論理グループ名の配列。グループを縦断処理する場合に使用
	 *
	 * @var array
	 */
	protected $_group_keys;

	/**
	 * 処理済み行数
	 *
	 * @var int
	 */
	protected $_count;

	/**
	 * CSVスキーマをオーダー情報でバインドした連想配列。キャッシュとして使用
	 *
	 * @var array
	 */
	protected $_coluSchemaCache;

	/**
	 * クローズフラグ
	 *
	 * @var bool
	 */
	protected $_closed;

	/**
	 * ビルド済みの最終データ。最初のbuildメソッドで構築される
	 *
	 * @var array
	 */
	protected $_buildedData;

	/**
	 * 別配送先指定フラグ
	 *
	 * @var int
	 */
	protected $_another_deli_flg;

	/**
	 * サイト情報配列
	 *
	 * @var array
	 */
	protected $_siteInfo;

	/**
	 * 商品金額の合計
	 *
	 * @var int
	 */
	protected $_item_summary;

	/**
	 * 加盟店ID
	 * @var int
	 */
	protected $_enterpriseId;

	/**
	 * ペイジー決済の対象OEMか
	 * @var bool
	 */
	protected $_payeasy_flg;

    /**
     * OEM ID
     * @var bool
     */
    protected $_oem_id;

    /**
	 * DBアダプタ
	 * @var Adapter
	 */
	protected $_adapter;

	/**
	 * 加盟店IDとDBアダプタの設定
	 *
	 * @param int $enterpriseId 加盟店ID
	 * @param Adapter $adapter DBアダプタ
	 */
	public function setEnterpriseAndDBAdapter($enterpriseId, $adapter) {
	    $this->_enterpriseId = $enterpriseId;
	    $this->_adapter = $adapter;
	    $sql = "SELECT OemId FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId";
      $oemId = $this->_adapter->query($sql)->execute( array(':EnterpriseId' => $this->_enterpriseId) )->current()['OemId'];
      $logicpayeasy = new LogicPayeasy($this->_adapter);
      $this->_payeasy_flg = $logicpayeasy->isPayeasyOem($oemId);
        $this->_oem_id = $oemId;
	    return $this;
	}

	/**
	 * CSVスキーマを指定して、CoralCsvHandlerOrderBuilderの新しいインスタンスを初期化する
	 *
	 * @param array $schema CSVスキーマ
	 * @param array $options
	 */
	public function __construct(array $schema, $options = array()) {

        // グループキー配列を構築
        $this->_group_keys = array(
                self::GROUP_ORDER,
                self::GROUP_CUSTOMER,
                self::GROUP_DELIVERY,
                self::GROUP_ITEMS,
                self::GROUP_SUMMARY
        );

        // グループ配列を初期化
        $this->_groups = array();
        foreach( $this->_group_keys as $key ) {
            $this->_groups[ $key ] = array();
        }


        // CSVスキーマ割り当て
        $this->_schema = $schema;


        // オプションパラメータの設定
        if( ! is_array( $options ) ) $options = array();
        foreach( $options as $key => $value ) {
            switch( $key ) {
                case self::OPTIONS_PREFECTURE_ROWSET:
                    $this->setPrefectures( $value );
                    break;
                case self::OPTIONS_SITE_INFO:
                    $this->setSiteInfo( $value );
                    break;
            }
        }

        // その他の初期化
        $this->_count = 0;
        $this->_closed = false;
        $this->_buildedData = null;
        $this->_another_deli_flg = 0;
        $this->_item_summary = 0;
	}

	/**
	 * このビルダが閉じているかを判断する。
	 * ビルダは注文単位で最終行が入力されると自動的に閉じられ、以降のaddRowは例外をスローするようになる。
	 *
	 * @return bool このビルダインスタンスがすでに閉じている場合はtrue、それ以外はfalse
	 */
	public function isClosed() {
	    return $this->_closed ? true : false;
	}

	/**
	 * CSVスキーマデータを取得する
	 *
	 * @return array
	 */
	public function getCsvSchema() {
	    return $this->_schema;
	}

	/**
	 * 処理行数を取得する
	 *
	 * @return int
	 */
	public function itemCount() {
	    return $this->_count;
	}

	/**
	 * 都道府県情報を取得する
	 *
	 * @return array
	 */
	public function getPrefectures() {
	    return $this->_prefectures;
	}

	/**
	 * 都道府県情報を設定する
	 *
	 * @param array $prefectures 都道府県情報。TablePrefectureの有効なRowsetを指定する
	 * @return CoralCsvHandlerOrderBuilder
	 */
	public function setPrefectures($prefectures) {
	    if( ! is_array( $prefectures ) ) $prefectures = null;
        $this->_prefectures = $prefectures;
        return $this;
	}

	/**
	 * サイトIDを検証するためのサイト情報を取得する
	 *
	 * @return array サイト情報。キーがサイトID、値がサイト名とデフォルトサイトであるかを示す連想配列（name, default）になる
	 */
	public function getSiteInfo() {
	    return $this->_siteInfo;
	}

	/**
	 * サイトIDを検証するためのサイト情報を設定する
	 *
	 * @param array $siteInfo サイト情報。キーがサイトID、値がサイト名とデフォルトサイトであるかを示す連想配列（name, default）になる
	 * @return CoralCsvHandlerOrderBuilder
	 */
	public function setSiteInfo($siteInfo) {
	    if( ! is_array( $siteInfo ) ) $siteInfo = array();
		$this->_siteInfo = $siteInfo;

		return $this;
	}

	/**
	 * デフォルトのサイトIDを取得する
	 *
	 * @return int|null デフォルトサイトID。サイト情報が未設定の場合はnullを返す
	 */
	public function getDefaultSite() {
	    $sites = $this->getSiteInfo();
		if( empty($sites) ) {
			return null;
		}

		$siteIdList = array();
		foreach( $this->getSiteInfo() as $siteId => $siteInfo ) {
			$siteIdList[] = $siteId;
			if( $siteInfo['default'] ) return $siteId;
		}
		if( count( $siteIdList ) > 0 ) {
			sort( $siteIdList );
			return $siteIdList[0];
		}
		return null;
	}

	/**
	 * 指定のサイトIDのサイトがメールアドレス必須かを判断する
	 *
	 * @param int $siteId
	 * @return bool
	 */
	public function isMailRequiredSite($siteId) {
	    $sites = $this->getSiteInfo();
		$siteInfo = $sites[$siteId];
		if( $siteInfo == null ) return true;
		return $siteInfo['mail_require'] ? true : false;
	}

	/**
	 * 指定のサイトIDが適切かを判断する
	 *
	 * @param int $siteId 検査するサイトID
	 * @return bool $siteIdが設定されているサイト情報に適合する場合はtrue、それ以外はfalse
	 */
	public function isValidSiteId($siteId) {
	    $sites = $this->getSiteInfo();
		return ( ! empty( $sites[ $siteId ] ) ) ? true : false;
	}

	/**
	 * 蓄積したデータを元に確定した注文関連データの連想配列を作成する
	 *
	 * @return array 確定した連想配列データ
	 */
	public function build() {
	    if( ! $this->isClosed() ) throw new \Exception( 'ＣＳＶレイアウトが異なっています。レイアウトや必須項目の確認をお願いします。' );

		if( $this->_buildedData == null ) {
			// 戻り値のプレースホルダ作成
			$result = array();
			foreach( $this->_group_keys as $key ) {
				if( $key == self::GROUP_SUMMARY ) continue;
				$result[ $key ] = array();
			}

			// 注文・顧客・配送先データの構築
			foreach( array( self::GROUP_ORDER, self::GROUP_CUSTOMER, self::GROUP_DELIVERY) as $groupKey ) {
				$row = $result[ $groupKey ];
				foreach( $this->_groups[ $groupKey ] as $ordinal => $value ) {
					$schema = $this->findColumnSchema( $ordinal );
					$row[ $schema['ColumnName'] ] = $value;
				}
				$result[ $groupKey ] = $row;
			}
			// 注文データに別配送先フラグを設定
			$result[ self::GROUP_ORDER ]['AnotherDeliFlg'] = $this->_another_deli_flg;
			// 顧客データと配送先データに都道府県情報追加
			$result[ self::GROUP_CUSTOMER ] = $this->parsePrefecture( $result[ self::GROUP_CUSTOMER ] );
			$result[ self::GROUP_DELIVERY ] = $this->parsePrefecture( $result[ self::GROUP_DELIVERY ], /* $isDeliveryDestination =  */true );
			// 郵便番号を整形
			$result[ self::GROUP_CUSTOMER ] = $this->fixPostalCode( $result[ self::GROUP_CUSTOMER ] );
			$result[ self::GROUP_DELIVERY ] = $this->fixPostalCode( $result[ self::GROUP_DELIVERY ], /* $isDeliveryDestination =  */true );
			// 電話番号を整形（2011.6.28 eda）
			// → 入力がすべて半角数字で構成されていて且つ先頭がゼロでない場合にゼロプレフィックスを行う
			$result[ self::GROUP_CUSTOMER ] = $this->fixPhoneNumber( $result[ self::GROUP_CUSTOMER ] );
			$result[ self::GROUP_DELIVERY ] = $this->fixPhoneNumber( $result[ self::GROUP_DELIVERY ], /* $isDeliveryDestination =  */true );


			// 商品データの構築
			foreach( $this->_groups[ self::GROUP_ITEMS ] as $item_row ) {
				$buf = array();
				foreach( $item_row as $ordinal => $value ) {
					$schema = $this->findColumnSchema( $ordinal );
					$buf[ $schema['ColumnName'] ] = $value;
				}
				$buf[ 'DataClass' ] = 1;
				$result[ self::GROUP_ITEMS ][] = $buf;
			}

			// サマリデータ向けにテーブル名とグループのマップを作成
			$groupMap = array(
				'T_OrderItems' => self::GROUP_ITEMS,
				'T_Order' => self::GROUP_ORDER
			);

			// サマリデータをループ処理し、他のグループに付け替える
			foreach( $this->_groups[ self::GROUP_SUMMARY ] as $ordinal => $value ) {
				$schema = $this->findColumnSchema( $ordinal );
				$targetGroup = $groupMap[ $schema['TableName'] ];

				switch( $targetGroup ) {
					case self::GROUP_ORDER:
						$result[ $targetGroup ][$schema['ColumnName']] = $value;
						break;
					case self::GROUP_ITEMS:

					    $DataClassStr = -1;
					    if      (preg_match( '/送料/',       $schema['Caption'] )) {$DataClassStr = 2; }
					    else if (preg_match( '/店舗手数料/', $schema['Caption'] )) {$DataClassStr = 3; }
					    else if (preg_match( '/外税額/',     $schema['Caption'] )) {$DataClassStr = 4; }

						$result[ $targetGroup ][] = array(
							'ItemNameKj' => $schema['Caption'],
							$schema['ColumnName'] => $value,
							'ItemNum' => 1,
							'DataClass' => $DataClassStr
						);
						break;
				}
			}

			$this->_buildedData = $result;
		}
		return $this->_buildedData;
	}

	/**
	 * CSV行データを読込み、データの構築を進める
	 *
	 * @param array $row CSVデータ行
	 * @return null|array $rowが適切なデータの場合はnull、検証エラーが発生した場合はエラー情報の配列
	 */
	public function addRow(array $row) {
	    mb_regex_encoding('UTF-8');
	    try {
			if( $this->isClosed() ) {
				throw new \Exception( 'データの構成が不正です。注文開始行が見つかりません' );
			}

			// グループに分割
			$items = $this->parseLine($row);

			// 最低条件のチェック
			if( ! $this->isValid( $items ) ) {
				throw new \Exception( '入力行が不正です。商品データが含まれません' );
			}

			// 先頭行かの判断
			$is_first = $this->isFirstRow( $items );
			// 最初のデータが先頭行でない場合は例外
			if( ! $is_first && $this->_count == 0 ) {
				throw new \Exception( 'データの構成が不正です。注文内容を含まないデータから始まっています。注文日または受付サイトを見直してください。' );
			}
			// 先頭行が最初のデータ以外で入ってきたら例外
			if( $is_first && $this->_count > 0 ) {
				throw new \Exception( 'データの構成が不正です。1つの注文が完了する前に新しい注文が開始されました。送料や請求金額を設定しているか確認してください' );
			}

			// 最終行かの判断
			$is_last = $this->isLastRow( $items );

			// 中間データ行かの判断
			$is_body = $this->isContentRow( $items );
			// 先頭行でも中間行でも最終行でもないデータ（＝商品データ以外の情報を含む中間行）

			$validation_errors = array();

			// 先頭行の場合の処理
			if( $is_first ) {
				// サイトIDがない場合はデフォルトサイトIDで置換する
				$siteIdColumn = $this->getSchemaByColumn( $items[ self::GROUP_ORDER ], 'SiteId' );
				if( $siteIdColumn != null ) {
				    if( empty( $items[ self::GROUP_ORDER][ $siteIdColumn['ListNumber'] ] ) ) {
				        $items[ self::GROUP_ORDER ][ $siteIdColumn['ListNumber'] ] = $this->getDefaultSite();
				    }
				}
				// 配送先がない場合は顧客データからコピーする
				if( BaseGeneralUtils::ArrayIsAllEmpty( $items[ self::GROUP_DELIVERY ] ) ) {
					// TODO: 顧客グループと配送先グループのマッチングが酷すぎる。なんとか考える。
					foreach( $items[ self::GROUP_DELIVERY ] as $ordinal => $value ) {
						// この配送先カラムのスキーマ定義を取得
						$deliSchema = $this->findColumnSchema( $ordinal );
						$colName = $deliSchema['ColumnName'];
						// 氏名・氏名かなのみカラム名が一致しないので加工
						if( $colName == 'DestNameKj' ) {
							$colName = 'NameKj';
						} else if( $colName == 'DestNameKn' ) {
							$colName = 'NameKn';
						}  else if( $colName == 'DestPostalCode' ) {
						    $colName = 'PostalCode';
						}  else if( $colName == 'DestUnitingAddress' ) {
						    $colName = 'UnitingAddress';
						}  else if( $colName == 'DestPhone' ) {
						    $colName = 'Phone';
						}
						// この配列カラムの定義と同じテーブルカラム名を持つデータを顧客グループから検索する
						$custoSchema = $this->getSchemaByColumn( $items[ self::GROUP_CUSTOMER ], $colName );

						// 商品グループの対応スキーマが見つからないので無視
						if( ! $custoSchema ) continue;

						// データをコピー
						$items[ self::GROUP_DELIVERY ][ $ordinal ] = $items[ self::GROUP_CUSTOMER ][ $custoSchema['ListNumber'] ];
					}
					// 別配送先フラグ確定
					$this->_another_deli_flg = 0;
				} else {
					// 別配送先フラグ確定
					$this->_another_deli_flg = 1;
				}
				// 住所の番地区切りの置換処理（08.7.14 追加）
				foreach( array( self::GROUP_CUSTOMER, self::GROUP_DELIVERY ) as $g ) {
					// 全角ハイフン（SJIS:0x815D）を半角マイナスに置換する → 印刷時消失対応
					$col = $this->getSchemaByColumn( $items[$g], 'UnitingAddress' );
					$items[$g][$col['ListNumber']] = preg_replace( '/‐/', '-', $items[$g][$col['ListNumber']] );
				}
				// 注文日の検証前補正処理（08.9.30 追加）
				$col = $this->getSchemaByColumn( $items[self::GROUP_ORDER], 'ReceiptOrderDate' );
				$items[self::GROUP_ORDER][$col['ListNumber']] = $this->fixDateString( $items[self::GROUP_ORDER][$col['ListNumber']] );

				if($this->_payeasy_flg){
				    //氏名と氏名カナの半角スペースを全角スペースに変換
    				foreach(array ('NameKj','NameKn') as $key){
        				$col = $this->getSchemaByColumn( $items[self::GROUP_CUSTOMER], $key );
        				$items[self::GROUP_CUSTOMER][$col['ListNumber']] = mb_convert_kana($items[self::GROUP_CUSTOMER][$col['ListNumber']],'S');
    				}
				}

				// 整形済みグループデータに検証を適用
				foreach( array( self::GROUP_ORDER, self::GROUP_CUSTOMER, self::GROUP_DELIVERY ) as $g ) {
					$validation_errors = array_merge( $validation_errors, $this->validateGroup( $items[ $g ] ) );
				}
							// サイトIDの値域検査
				$siteId = $items[ self::GROUP_ORDER ][$siteIdColumn['ListNumber']];
				if( ! empty( $siteId ) && ! $this->isValidSiteId( $siteId ) ) {
					$validation_errors[] = array( $siteIdColumn['ListNumber'] => "'$siteId' は不正なサイトIDです" );
				} else {
					// 有効なサイトIDなので、メールアドレス必須をチェックし、
					// 必須サイトの場合は検証を行う（※ validateGroup()ではスキップされている）
					if( $this->isMailRequiredSite( $siteId ) ) {
						$mailColumn = $this->getSchemaByColumn( $items[ self::GROUP_CUSTOMER ], 'MailAddress' );
						$value = $items[ self::GROUP_CUSTOMER ][ $mailColumn['ListNumber'] ];
						if($this->_payeasy_flg){
						  $err = CoralValidateUtility::checkPeMailAddress($value, true, true);
							if ($err != null) {
						    $validation_errors[] = array( $mailColumn['Caption'] => $err['message'] );
						  }
						} else {
    				  if( ! mb_ereg( preg_replace('/((^\/)|(\/[img]*$))/', '', $mailColumn['ValidationRegex']), $value )){
    					  if( empty($items[ self::GROUP_CUSTOMER ][ $mailColumn['ListNumber'] ]) ) {
    						  $validation_errors[] = array( $mailColumn['Caption'] => 'データを空にすることはできません' );
    						} else {
    						  $validation_errors[] = array( $mailColumn['Caption'] => '入力データが不正です' );
    						}
    				  }
						}
					} else if($this->_payeasy_flg){
					  $mailColumn = $this->getSchemaByColumn( $items[ self::GROUP_CUSTOMER ], 'MailAddress' );
						$err = CoralValidateUtility::checkPeMailAddress($items[ self::GROUP_CUSTOMER ][ $mailColumn['ListNumber'] ]);
					  if (isset($err)) {
						  $validation_errors[] = array( $mailColumn['Caption'] => '入力データが不正です' );
			      }
					}
				}
				//加盟店.口座振替利用が1:利用する、かつ口座振替申込区分が1：利用する（WEB申込み）の場合、メールアドレスの入力チェックを行う。
				$sql = " SELECT CreditTransferFlg FROM T_Enterprise WHERE  EnterpriseId = :EnterpriseId ";
				$row_creditTransferFlg = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $this->_enterpriseId))->current();
   				$creditTransferRequestFlgColumn = $this->getSchemaByColumn( $items[ self::GROUP_ORDER ], 'CreditTransferRequestFlg' );
                    $mailColumn = $this->getSchemaByColumn( $items[ self::GROUP_CUSTOMER ], 'MailAddress' );
                if ($row_creditTransferFlg['CreditTransferFlg'] == 1 && $items[ self::GROUP_ORDER][ $creditTransferRequestFlgColumn['ListNumber']] == 1 && empty($items[ self::GROUP_CUSTOMER ][$mailColumn['ListNumber']])) {
                    $validation_errors[] = array($mailColumn['Caption'] => 'メールアドレスを空にすることはできません');
                } elseif ($row_creditTransferFlg['CreditTransferFlg'] == 2 && $items[ self::GROUP_ORDER][ $creditTransferRequestFlgColumn['ListNumber']] == 1 && empty($items[ self::GROUP_CUSTOMER ][$mailColumn['ListNumber']])) {
                    $validation_errors[] = array( $mailColumn['Caption'] => 'メールアドレスを空にすることはできません' );
                } elseif ($row_creditTransferFlg['CreditTransferFlg'] == 3 && $items[ self::GROUP_ORDER][ $creditTransferRequestFlgColumn['ListNumber']] == 1 && empty($items[ self::GROUP_CUSTOMER ][$mailColumn['ListNumber']])) {
                    $validation_errors[] = array( $mailColumn['Caption'] => 'メールアドレスを空にすることはできません' );
                }
                // 役務対象サイト時の対応
                // (役務対象区分=1:役務、の時は[役務提供予定日:ServiceExpectedDate]が有効日付書式で必須)
                $sql = " SELECT ServiceTargetClass FROM T_Site WHERE SiteId = :SiteId AND EnterpriseId = :EnterpriseId ";
                $row_site = $this->_adapter->query($sql)->execute(array(':SiteId' => $items[ self::GROUP_ORDER ][ $siteIdColumn['ListNumber'] ],':EnterpriseId' => $this->_enterpriseId))->current();
                if ($row_site['ServiceTargetClass'] == 1) {
                    // 役務サイト
                    $serviceExpectedDateColumn = $this->getSchemaByColumn( $items[ self::GROUP_ORDER ], 'ServiceExpectedDate' );
                    $items[self::GROUP_ORDER][$serviceExpectedDateColumn['ListNumber']] = $this->fixDateString( $items[self::GROUP_ORDER][$serviceExpectedDateColumn['ListNumber']] );
                    if (!$serviceExpectedDateColumn) {
                        throw new \Exception( '受付サイトが役務対象時には、役務提供予定日の列設定と値設定が必須です。' );
                    }
                    elseif (empty($items[ self::GROUP_ORDER][ $serviceExpectedDateColumn['ListNumber'] ])) {
                        throw new \Exception( '受付サイトが役務対象時には、役務提供予定日の列設定と値設定が必須です。' );
                    }
                    elseif (!IsDate( $items[ self::GROUP_ORDER][ $serviceExpectedDateColumn['ListNumber'] ]) ) {
                        $validation_errors[] = array( '役務提供予定日' => '日付データが不正です' );
                    }
                    else {
                        $diffDate = BaseGeneralUtils::CalcSpanDays($items[ self::GROUP_ORDER][ $serviceExpectedDateColumn['ListNumber'] ], date('Y-m-d'));
                        if ($diffDate >= 30) {
                            $validation_errors[] = array( '役務提供予定日' => '過去日を指定することはできません' );
                        }
                    }
                }
                else {
                    // 物販サイト
                    $serviceExpectedDateColumn = $this->getSchemaByColumn( $items[ self::GROUP_ORDER ], 'ServiceExpectedDate' );
                    $items[self::GROUP_ORDER][$serviceExpectedDateColumn['ListNumber']] = $this->fixDateString( $items[self::GROUP_ORDER][$serviceExpectedDateColumn['ListNumber']] );
                    if (!empty($items[ self::GROUP_ORDER][ $serviceExpectedDateColumn['ListNumber'] ])) {
                        $validation_errors[] = array( '役務提供予定日' => '受付サイトが役務対象でないため、役務提供予定日は設定できません' );
                    }
                }

                //加盟店.無保証案件の請求代行プランが1:利用するの場合、自由入力項目の入力チェックを行う。
                $sql = " SELECT BillingAgentFlg FROM T_Enterprise WHERE  EnterpriseId = :EnterpriseId ";
                $row_BillingAgentFlg = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $this->_enterpriseId))->current();
                if ($row_BillingAgentFlg['BillingAgentFlg'] == 1) {
                    for ($idx = 1; $idx < 21; $idx++) {
                        $orderAdd_Free = $this->getSchemaByColumn( $items[ self::GROUP_ORDER ], ('Free'. $idx) );
                        $chkTxt = mb_convert_encoding( $items[ self::GROUP_ORDER][ $orderAdd_Free['ListNumber'] ] , 'sjis-win', 'UTF-8');
                        if (strlen($chkTxt) > 50) {
                            $validation_errors[] = array( $orderAdd_Free['Caption'] => '半角50文字以内または全角25文字以内でご入力ください' );
                        }
                    }
                }

				// 注文データ確定
				$this->_groups[ self::GROUP_ORDER ] = $items[ self::GROUP_ORDER ];
				// 顧客データ確定
				$this->_groups[ self::GROUP_CUSTOMER ] = $items[ self::GROUP_CUSTOMER ];
				// 配送先データ確定
				$this->_groups[ self::GROUP_DELIVERY ] = $items[ self::GROUP_DELIVERY ];
			}

			// 最終行の場合の処理
			if( $is_last ) {
				// 検証を適用
				$validation_errors = array_merge( $validation_errors, $this->validateGroup( $items[ self::GROUP_SUMMARY ] ) );
				// サマリデータ確定
				$this->_groups[ self::GROUP_SUMMARY ] = $items[ self::GROUP_SUMMARY ];
				// クローズする
				$this->_closed = true;
			}

			// 商品データに検証を適用
			$validation_errors = array_merge( $validation_errors, $this->validateGroup( $items[ self::GROUP_ITEMS ] ) );

			// 商品データを追加
			$item_rows = $this->_groups[ self::GROUP_ITEMS ];
			if( ! is_array( $item_rows ) ) $item_rows = array();
			$item_rows[] = $items[ self::GROUP_ITEMS ];
			// 商品金額を算出
			$itemMoney = $this->calcItemMoney( $items[ self::GROUP_ITEMS ] );
			// 商品金額合計に加算
			$this->_item_summary += $itemMoney;
			$this->_groups[ self::GROUP_ITEMS ] = $item_rows;

	        // 最終行なら利用額合計を検算し、一致しなければエラー扱い
            if( $is_last && ! $this->verifySummary() ) {
                $validation_errors[] = array( '請求金額合計' => '請求金額が誤っています' );
            }


			// 処理カウンタをインクリメント
			$this->_count++;

			return count( $validation_errors ) == 0 ? null : $validation_errors;
		} catch(\Exception $err) {
			// 例外はすべてエラー情報として返す
			if( ! is_array( $validation_errors ) ) $validation_errors = array();
			$validation_errors[] = array( '(不正データ行)' => $err->getMessage() );
			return $validation_errors;
		}
	}

	/**
	 * CSV行データを論理グループに分割する。
	 * グループデータはこのクラスのGROUP_*で定義される定数をキーとして配列を持ち、
	 * 個々の配列は対応するCSVスキーマのListNumberをキー、CSVデータを値とする連想配列になる。
	 *
	 * @param array $row CSV行データ
	 * @return array グループに分割された配列データ
	 */
	protected function parseLine(array $row) {
        $result = array();

        $colIndex = 0;
        foreach( $this->getCsvSchema() as $schema_row ) {
            $data = Json::decode( $schema_row['ApplicationData'], Json::TYPE_ARRAY );
            if( $data ) {
                $groupName = $data['group'];
                $group = $result[ $groupName ];
                if( $group == null ) $group = array();
                $group[ $schema_row['ListNumber'] ] = trim( $row[ $colIndex ] );

                $result[ $groupName ] = $group;
            }
            $colIndex++;
        }
        return $result;
	}

	/**
	 * 指定の配列グループが入力行の最低条件を満たしているかを判断する。
	 * 入力行の最低条件は、商品情報グループが空でないことを条件とする。
	 *
	 * @param array $groups parseLineメソッドでグループ単位に分割されたCSV行データ
	 * @return bool $groupsが入力行の最低条件を満たしている場合はtrue、それ以外はfalse
	 */
	protected function isValid(array $gourps) {
        // 商品情報が空でなければ正常データ
        return ! BaseGeneralUtils::ArrayIsAllEmpty( $gourps[ self::GROUP_ITEMS ] );
	}

	/**
	 * 指定の配列グループが先頭行の条件を満たしているかを判断する。
	 * 先頭行の条件は、注文データ・顧客データおよび商品データを含んでいることを条件とする。
	 *
	 * @param array $groups parseLineメソッドでグループ単位に分割されたCSV行データ
	 * @return bool $groupsが先頭行の条件を満たしている場合はtrue、それ以外はfalse
	 */
	protected function isFirstRow(array $groups) {
        // 注文データと顧客データがあり、正常な入力行なら先頭行
        return
            ! BaseGeneralUtils::ArrayIsAllEmpty( $groups[ self::GROUP_ORDER ] ) &&
            ! BaseGeneralUtils::ArrayIsAllEmpty( $groups[ self::GROUP_CUSTOMER ] ) &&
            $this->isValid( $groups  );
	}

	/**
	 * 指定の配列グループが最終行の条件を満たしているかを判断する。
	 * 最終行の条件は、商品データとサマリデータを含んでいることを条件とする。
	 *
	 * @param array $groups parseLineメソッドでグループ単位に分割されたCSV行データ
	 * @return bool $groupsが最終行の条件を満たしている場合はtrue、それ以外はfalse
	 */
	protected function isLastRow(array $groups) {
        // サマリデータがあり、正常な入力行なら最終行
        return
            ! BaseGeneralUtils::ArrayIsAllEmpty( $groups[ self::GROUP_SUMMARY ] ) &&
            $this->isValid( $groups  );
	}

	/**
	 * 指定の配列グループが、先頭でも最終でもない中間データ行であるかを判断する。
	 * 中間データ行の条件は、商品情報以外を含んでいないことを条件とする
	 *
	 * @param array $groups parseLineメソッドでグループ単位に分割されたCSV行データ
	 * @return bool $groupsが中間データ行の条件を満たしている場合はtrue、それ以外はfalse
	 */
	protected function isContentRow(array $groups) {
        return
            BaseGeneralUtils::ArrayIsAllEmpty( $groups[ self::GROUP_ORDER ] ) &&
            BaseGeneralUtils::ArrayIsAllEmpty( $groups[ self::GROUP_CUSTOMER ] ) &&
            BaseGeneralUtils::ArrayIsAllEmpty( $groups[ self::GROUP_DELIVERY ] ) &&
            BaseGeneralUtils::ArrayIsAllEmpty( $groups[ self::GROUP_SUMMARY ] ) &&
            $this->isValid( $groups  );
	}

	/**
	 * CSVカラムの位置情報から対応するCSVスキーマ情報を取得する
	 *
	 * @param int|string $ordinal CSVカラムの位置情報。CSVスキーマのOrdinalプロパティに一致する
	 * @return array
	 */
	protected function findColumnSchema($ordinal) {
	    if( empty( $this->_coluSchemaCache ) ) {
			$this->_coluSchemaCache = array();
			foreach( $this->getCsvSchema() as $schema_row ) {
				$this->_coluSchemaCache[ $schema_row['ListNumber'] ] = $schema_row;
			}
		}

		return $this->_coluSchemaCache[ $ordinal ];
	}

	/**
	 * 指定の配列グループをテーブルカラム名で検索し、対応するスキーマ情報を取得する
	 *
	 * @param array $group 検索対象のグループデータ
	 * @param string $columnName テーブルカラム名
	 * @return array
	 */
	protected function getSchemaByColumn($group, $columnName) {
	    foreach( $group as $ordinal => $value ) {
			$schema = $this->findColumnSchema( $ordinal );
			if( $schema ) {
				if( $schema['ColumnName'] == $columnName ) return $schema;
			}
		}
		return null;
	}

	/**
	 * 指定の配列グループの住所情報から都道府県コードと都道府県名を展開し、連想配列に追加する。
	 * このメソッドは渡された連想配列のキー'UnitingAddress'を住所文字列とみなし、先頭に一致する
	 * 都道府県の都道府県コードと都道府県名をそれぞれキー'PrefectureCode'・'PrefectureName'として
	 * 元の連想配列に追加する。
	 *
	 * @param array $group 処理対象のグループデータ
	 * @return $groupに都道府県情報を追加した連想配列
	 */
	protected function parsePrefecture(array $group, $isDeliveryDestination = false) {

        $key = ($isDeliveryDestination) ? 'DestUnitingAddress' : 'UnitingAddress';

        if( $this->_prefectures == null )  return $group;
        $address = $group[$key];
        if( empty( $address ) ) return $group;
        foreach( $this->_prefectures as $prefecture ) {
            if( strpos( $address, $prefecture['PrefectureName'] ) === 0 ) {
                $group['PrefectureCode'] = $prefecture['PrefectureCode'];
                $group['PrefectureName'] = $prefecture['PrefectureName'];
                break;
            }
        }
        return $group;
	}

	/**
	 * 指定の配列グループをスキーマの定義で検証する
	 *
	 * @param array $group 処理対象のグループデータ
	 * @return array 検証エラー情報
	 */
	protected function validateGroup(array $group) {
	    $errors = array();
		foreach( $group as $ordinal => $value ) {
			$schema = $this->findColumnSchema( $ordinal );
			if( $schema == null ) continue;

			// メールアドレスは別検証するのでスキップ
			if( $schema['ColumnName'] == 'MailAddress' ) continue;

			// 定義された正規表現で検証実行
			if( ! mb_ereg( preg_replace('/((^\/)|(\/[img]*$))/', '', $schema['ValidationRegex']), $value ) ) {
				if( empty($value) ) {
				    if( $schema['ColumnName'] == 'ClaimSendingClass' || $schema['ColumnName'] == 'CreditTransferRequestFlg' || $schema['ColumnName'] == 'NameKn') {
				        $errors[] = array( $schema['Caption'] => 'データを空にすることはできません' );
				    }
				    else if($schema['ColumnName'] != 'TaxRate' ){
					$errors[] = array( $schema['Caption'] => 'データを0または空にすることはできません' );
				    }
				} else {
					$errors[] = array( $schema['Caption'] => '入力データが不正です' );
				}
			} else if( $schema['ColumnName'] == 'ReceiptOrderDate' ) {
				// 注文日のみ、日付としての妥当性を検証
			    if ( !IsValidDate($value) ) {
			        $errors[] = array( $schema['Caption'] => "'$value' は有効な日付データではありません" );
			    }
			} else if( $schema['ColumnName'] == 'ItemNum' ) {
				// 数量は正規表現が通っても0は不正とする
			    if ( $value == 0 ) {
			        $errors[] = array( $schema['Caption'] => "データを0または空にすることはできません" );
			    }
                if (( $this->_oem_id == LogicConstant::OEM_ID_SMBC ) && ( $value > 999 )) {
                    $errors[] = array( $schema['Caption'] => "データを999を超える数字を入力することはできません" );
                }
			} else if( $schema['ColumnName'] == 'NameKj'){
			    if($this->_payeasy_flg){
			        $err = CoralValidateUtility::checkPeNameKj($value);
			        if (isset($err)) {
			            $errors[] = array( $schema['Caption'] => $err['message'] );
			        }
			     }
		  } else if( $schema['ColumnName'] == 'NameKn'){
		      if($this->_payeasy_flg){
		          $err = CoralValidateUtility::checkPeNameKn($value);
			        if (isset($err)) {
			            $errors[] = array( $schema['Caption'] => $err['message'] );
			        }
		      }
			} else if(  $schema['ColumnName'] == 'Phone'){
			    if($this->_payeasy_flg){
			        $err = CoralValidateUtility::checkPePhoneNumber($value);
			        if (isset($err)) {
			            $errors[] = array( $schema['Caption'] => $err['message'] );
			        }
          }
			}

		}
		return $errors;
	}

	/**
	 * 指定の配列グループに含まれる郵便番号データを3桁-4桁の形式に整形する
	 *
	 * @param array $group 処理対象のグループデータ
	 * @return 配列中の郵便番号データを整形した配列
	 */
	protected function fixPostalCode(array $group, $isDeliveryDestination = false) {

        $key = ($isDeliveryDestination) ? 'DestPostalCode' : 'PostalCode';

        $postalCode = $group[$key];
        if( ! empty($postalCode) ) {
            $group[$key] = CoralValidateUtility::fixPostalCode( $postalCode, true );
        }
        return $group;
	}

	/**
	 * 指定の配列グループに含まれる、指定キーが示す電話番号に対し、先頭に0を補完する
	 *
	 * @access protected
	 * @param array $group 処理対象のグループデータ
	 * @return array 電話番号に補完処理を適用した後のグループデータ
	 */
	protected function fixPhoneNumber(array $group, $isDeliveryDestination = false) {

        $key = ($isDeliveryDestination) ? 'DestPhone' : 'Phone';

        // 無条件に全角→半角変換を適用する（2011.6.30 eda）
        $phoneNumber = BaseGeneralUtils::convertWideToNarrow($group[$key]);

        // 先頭が0でない場合は0を付加する
        // → 数字以外が混じっている場合も無条件にゼロプレフィックスする仕様に変更（2011.6.30 eda）
        if(! preg_match('/^0/', $phoneNumber)) {
            $group[$key] = '0' . $phoneNumber;
        }
        return $group;
	}

	/**
	 * 指定の日付文字列を整形する。
	 * 主に区切り文字の統一を行うが、年パートのみ、4桁以下の場合はを2000年ベースの4桁に補正する処理が加わる
	 *
	 * @param string $date 日付文字列
	 * @return 補正された日付文字列
	 */
	protected function fixDateString($date) {
	    $date = parseDateByLeft($date);
	    $date = str_replace('.', '/', $date);
        $date = str_replace('-', '/', $date);
        $parts = explode('/', $date);
        if( count($parts) < 2 ) return $date;
        if( BaseReflectionUtility::isNumeric( $parts[0] ) && ((int)$parts[0]) < 1000 ) {
            // 1000以下（＝1～3桁）の場合は2000年ベースに読み替える
            $parts[0] = 2000 + ((int)$parts[0]);
        }
        return join('/', $parts);
	}

	/**
	 * 指定の商品グループデータの金額（単価＊数量）を算出する
	 *
	 * @param array $item 1行のCSVデータ中の商品グループデータ
	 * @return int
	 */
	protected function calcItemMoney($item) {
        $sql = " SELECT UseAmountFractionClass FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
        $row = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $this->_enterpriseId))->current();

        $retval = ((int)$item[ $this->getSchemaByColumn( $item, 'UnitPrice' )['ListNumber'] ]) *
            ((int)$item[ $this->getSchemaByColumn( $item, 'ItemNum' )['ListNumber'] ]);

        if ((int)$row['UseAmountFractionClass'] == 0) { $retval = floor( $retval ); }
        if ((int)$row['UseAmountFractionClass'] == 1) { $retval = round( $retval ); }
        if ((int)$row['UseAmountFractionClass'] == 2) { $retval = ceil(  $retval ); }

        return $retval;
	}

	/**
	 * 利用額の検算を行う
	 *
	 * @return bool 商品金額の合計（＋送料・手数料）と利用額が一致する場合はtrue、それ以外はfalse
	 */
protected function verifySummary() {

    $sql = " SELECT TaxClass FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
    $tax = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $this->_enterpriseId))->current();

        $summary = $this->_groups [self::GROUP_SUMMARY];

        $carrie_and_charge = 0;
        $sum = 0;
        if (strcmp ( $tax['TaxClass'] , "1" ) == 0) {
            foreach ( $summary as $ordinal => $value ) {
                $schema = $this->findColumnSchema ( $ordinal );
                switch ($schema ['ColumnName']) {
                    case 'UnitPriceCarriage' : // 送料
                    case 'UnitPriceCharge' : // 店舗手数料
                    case 'UnitPriceTax' : // 外税額
                        $carrie_and_charge += ( int ) $value;
                        break;
                    case 'UseAmount' :
                        $sum = ( int ) $value;
                        break;
                }
            }
        } else {
            foreach ( $summary as $ordinal => $value ) {
                $schema = $this->findColumnSchema ( $ordinal );
                switch ($schema ['ColumnName']) {
                    case 'UnitPriceCarriage' : // 送料
                    case 'UnitPriceCharge' : // 店舗手数料
                        $carrie_and_charge += ( int ) $value;
                        break;
                    case 'UseAmount' :
                        $sum = ( int ) $value;
                        break;
                }
            }
        }
        return ($this->_item_summary + $carrie_and_charge == $sum) ? true : false;
    }
}
