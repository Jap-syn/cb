<?php
namespace Coral\Coral\CsvHandler\Order;

use Zend\Json\Json;
use Zend\Db\Adapter\Adapter;
use Coral\Base\BaseGeneralUtils;
use Coral\Base\Reflection\BaseReflectionUtility;
use Coral\Coral\Validate\CoralValidateUtility;

class CoralEditCsvHandlerOrderBuilder extends CoralCsvHandlerOrderBuilder {

    /**
     * CSVスキーマ上で定義される[注文ID]のListNumber
     *
     * @var int
     */
    protected $_listNumberOfOrderId = 0;

	/**
	 * CSVスキーマを指定して、CoralEditCsvHandlerOrderBuilderの新しいインスタンスを初期化する
	 *
	 * @param array $schema CSVスキーマ
	 * @param array $options
	 */
	public function __construct(array $schema, $options = array()) {
        parent::__construct($schema, $options);

        // CSVスキーマ上で定義される[注文ID]のListNumber捕捉＆変数設定
        foreach ($schema as $ar) {
            if ($ar['ColumnName'] == 'OrderId') {
                $this->_listNumberOfOrderId = (int)$ar['ListNumber'];
                break;
            }
        }
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
						  if( ! mb_ereg( preg_replace('/((^\/)|(\/[img]*$))/', '', $mailColumn['ValidationRegex']), $value )) {
						    if( empty($value) ) {
						      $validation_errors[] = array( $mailColumn['Caption'] => 'データを0または空にすることはできません' );
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
                $serviceExpectedDatePastChk = false;
                $sql = " SELECT ServiceTargetClass FROM T_Site WHERE SiteId = :SiteId AND EnterpriseId = :EnterpriseId ";
                $row_site = $this->_adapter->query($sql)->execute(array(':SiteId' => $items[ self::GROUP_ORDER ][ $siteIdColumn['ListNumber'] ],':EnterpriseId' => $this->_enterpriseId))->current();
                if ($row_site['ServiceTargetClass'] == 1) {
                    // 役務サイト
                    $serviceExpectedDateColumn = $this->getSchemaByColumn( $items[ self::GROUP_ORDER ], 'ServiceExpectedDate' );
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
                        // 過去日チェックを行う対象　該当注文がある場合のみチェック
                        $serviceExpectedDatePastChk = true;
                    }
                }
                else {
                    // 物販サイト
                    $serviceExpectedDateColumn = $this->getSchemaByColumn( $items[ self::GROUP_ORDER ], 'ServiceExpectedDate' );
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

// CoralCsvHandlerOrderBuilder継承に対する変更点(ここから)
                // 注文ID
                $orderId = $this->_groups[self::GROUP_ORDER][$this->_listNumberOfOrderId];
                if ($orderId != '') {
                    // T_Order検索
                    $row_odr = $this->_adapter->query(" SELECT * FROM T_Order WHERE OrderId = :OrderId AND EnterpriseId = :EnterpriseId "
                        )->execute(array(':OrderId' => $orderId, 'EnterpriseId' => $this->_enterpriseId))->current();

                    // 注文有効のチェック
                    if (!$row_odr) {
                        $validation_errors[] = array( '注文ID' => '指定の注文が無効です' );
                    }
                    // 修正可能な状態かのチェック
                    else if (!($row_odr['DataStatus'] < 41 && $row_odr['Cnl_Status'] == 0)) {
                        $validation_errors[] = array( '注文ID' => '指定の注文は登録されていないか修正可能な状態ではありません' );
                    }

                    // 該当注文があり、過去日チェックを行う場合
                    if ( $row_odr != false && $serviceExpectedDatePastChk ) {
                        // 役務提供予定日が変更された場合のみチェックする
                        $isValueChange = (date('Y-m-d', strtotime($items[ self::GROUP_ORDER][ $serviceExpectedDateColumn['ListNumber'] ])) == $row_odr['ServiceExpectedDate']) ? false : true;
                        $diffDate = BaseGeneralUtils::CalcSpanDays($items[ self::GROUP_ORDER][ $serviceExpectedDateColumn['ListNumber'] ], date('Y-m-d'));
                        if ($isValueChange && $diffDate >= 30) {
                            $validation_errors[] = array( '役務提供予定日' => '過去日を指定することはできません' );
                        }
                    }
                }
// CoralCsvHandlerOrderBuilder継承に対する変更点(ここまで)
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
}
