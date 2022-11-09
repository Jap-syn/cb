<?php
namespace Coral\Coral\CsvHandler;

use Zend\Db\Adapter\Adapter;
use models\Table\TableCsvSchema;
use models\Table\TableDeliMethod;
use models\Table\TablePayingAndSales;
use models\Table\TableOrderItems;
use Coral\Base\BaseGeneralUtils;

class CoralCsvHandlerPayingAndSales extends CoralCsvHandlerAbstract {
	const CSVSCHEMA_CLASS = 2;

	const OPTIONS_DELI_MASTER = 'deliMasters';

	const OPTIONS_ENT_ID = 'ent_id';

	const OPTIONS_DBADAPTER = 'dbAdapter';

	const OPTIONS_ORDER_LIST = 'orderList';

	const OPTIONS_CSV_SCHEMA = 'csvSchema';

	const OPTIONS_MODE = 'mode';

	const MODE_REGIST = 0;

	const MODE_CHANGE = 1;

	/**
	 * 配送先マスターを格納したarray
	 *
	 * @var array
	 */
	protected $_deliMaster;

	/**
	 * CSVスキーマを格納したarray
	 *
	 * @var array
	 */
	protected $_schema;

	/**
	 * ログイン中の事業者ID
	 *
	 * @var int
	 */
	protected $_ent_id;

	/**
	 * DBアダプタ
	 *
	 * @var Adapter
	 */
	protected $_dbAdapter;

	/**
	 * 伝票登録対象のデータを示す連想配列
	 *
	 * @var array
	 */
	protected $_orders;

	/**
	 * 注文IDから逆引きされた注文データのキャッシュ配列
	 *
	 * @var array
	 */
	protected $_orderCache;

	/**
	 * 伝票登録モード(MODE_REGIST: 登録、MODE_CHANGE: 修正)
	 *
	 * @var int
	 */
	protected $_mode;

	protected function init(array $options) {
		foreach( $options as $key => $value ) {
			switch($key) {
				case self::OPTIONS_ENT_ID:
					$this->setEnterpriseId( $value );
					break;
				case self::OPTIONS_DBADAPTER:
					$this->setDbAdapter( $value );
					break;
				case self::OPTIONS_DELI_MASTER:
					$this->setDeliveryMethodMaster( $value );
					break;
				case self::OPTIONS_ORDER_LIST:
					$this->setOrderList( $value );
					break;
				case self::OPTIONS_CSV_SCHEMA:
					$this->setCsvSchema( $value );
					break;
				case self::OPTIONS_MODE:
					$this->setMode( $value );
					break;
			}
		}

		if( $this->getDbAdapter() == null ) throw new \Exception( 'DBアダプタが設定されていません' );
		if( ! is_int( $this->getEnterpriseId() ) ) throw new \Exception( '事業者IDが設定されていません' );
		if( ! is_array( $this->getOrderList() ) ) throw new \Exception( '伝票登録対象の注文データリストが設定されていません' );
	}

	/**
	 * 配送先マスターを格納したarrayを取得する
	 *
	 * @return array
	 */
	public function getDeliveryMethodMaster() {
		return $this->_deliMaster;
	}

	/**
	 * 配送先マスターを格納したarrayを設定する
	 *
	 * @param array 配送先マスターデータ
	 * @return CoralCsvHandlerPayingAndSales
	 */
	public function setDeliveryMethodMaster(array $deliMaster) {
		$this->_deliMaster = $deliMaster;
		return $this;
	}

	/**
	 * CSVスキーマを格納したarrayを取得する
	 *
	 * @return array
	 */
	public function getCsvSchema() {
		return $this->_schema;
	}

	/**
	 * CSVスキーマを格納したarrayを設定する
	 *
	 * @return array
	 */
	public function setCsvSchema( array $schema ) {
		$this->_schema = $schema;
	}

	/**
	 * 現在のアカウントの事業者IDを取得する
	 *
	 * @return int
	 */
	public function getEnterpriseId() {
		return $this->_ent_id;
	}

	/**
	 * 現在のアカウントの事業者IDを設定する
	 *
	 * @param int $entId
	 * @return CoralCsvHandlerPayingAndSales
	 */
	public function setEnterpriseId($entId) {
		$this->_ent_id = (int)$entId;
		return $this;
	}

	/**
	 * DBアダプタを取得する
	 *
	 * @return Adapter
	 */
	public function getDbAdapter() {
		return $this->_dbAdapter;
	}

	/**
	 * DBアダプタを設定する
	 *
	 * @param Adapter $adapter
	 * @return CoralCsvHandlerPayingAndSales
	 */
	public function setDbAdapter(Adapter $adapter) {
		$this->_dbAdapter = $adapter;
		return $this;
	}

	/**
	 * 登録対象の注文データリストを取得する
	 *
	 * @return array
	 */
	public function getOrderList() {
		return $this->_orders;
	}

	/**
	 * 登録対象の注文データリストを設定する
	 *
	 * @param array $orderList 注文データリスト
	 */
	public function setOrderList(array $orderList) {
		$this->_orders = $orderList;

		return $this;
	}

	/**
	 * 伝票登録モードを取得する(MODE_REGIST: 登録、MODE_CHANGE: 修正)
	 *
	 * @return int
	 */
	public function getMode() {
		return $this->_mode;
	}

	/**
	 * 伝票登録モードを設定する(MODE_REGIST: 登録、MODE_CHANGE: 修正)
	 *
	 * @param int 伝票登録モード
	 */
	public function setMode($mode) {
		$this->_mode = $mode;
	}

	/**
	 * CSVの行を処理する
	 *
	 * @access protected
	 * @param array $row 読み取られた行データ
	 * @param int $line 読み取り行のインデックス。0から始まる
	 * @return CoralCsvHandlerLine
	 */
	protected function _validate(array $row, $line) {
		$schema = $this->getCsvSchema();

		$result = array();
		$errors = array();

		// 列数が定義と異なる場合は速攻エラー扱い
		if( count($row) != count($schema) ) {
			return new CoralCsvHandlerLine( array( array( '行' => '列の数が不正です。列の数は ' . count( $schema ) . ' である必要があります。' ) ), $line, CoralCsvHandlerLine::TYPE_ERROR );
		}

		// 1行目はヘッダ行チェックを行い、ヘッダ行ならスキップ
		if( $line == 0 && $this->checkHeaderLine($row) ) {
			return new CoralCsvHandlerLine( $row, $line, CoralCsvHandlerLine::TYPE_HEADER );
		}
		// 通常の検証処理
		$index = 0;
		$entOrderIdIndex = $this->getEntOrderIdIndex();
		foreach( $schema as $col_schema ) {
			$value = trim($row[ $index ]);
			$is_valid = false;
			switch( $col_schema['ColumnName'] ) {
				case 'OrderId':
					// 注文IDから注文データを逆引き
					$order = $this->findOrder( $value, $row[ $entOrderIdIndex ] );

					if( ! is_array( $order ) ) {
						// 逆引き失敗
						$errors[] = $this->createOrderError( $value, $row[$entOrderIdIndex], false );

					} else if( $this->_orderCache[ $order['OrderSeq'] ] != null ) {
						// キャッシュにすでに存在（＝ファイル内で注文IDの重複）
						$errors[] = $this->createOrderError( $value, $row[$entOrderIdIndex], true );

					}
					// 社内与信ステータスが OK でないとエラー (2015/04/02)
					else if( $order['Incre_Status'] != 1 ) {
						$errors[] = array(
								$col_schema['ColumnName'] =>
								'与信OKの注文ではありません。'
						);
					}
					// 注文サイトが役務サイトであるとエラー (2015/04/02)
					else if( $order['ServiceTargetClass'] == 1 ) {
						$errors[] = array(
								$col_schema['ColumnName'] =>
								'役務サイトでの注文に伝票番号登録はできません。'
						);
					}
					else if( $this->getMode() == self::MODE_CHANGE ) {
					    // 注文商品テーブルから配送方法と配送伝票番号を取得
					    $mdloi = new TableOrderItems( $this->_dbAdapter );
					    $orderItems = $mdloi->findByOrderSeq( $order['OrderSeq'] )->current();
					    $methodName = $this->findDeliMethod( $orderItems['Deli_DeliveryMethod'] )['DeliMethodName'];
					    $journalNumber = $orderItems['Deli_JournalNumber'];
                        $deliConfirmArrivalFlg = $orderItems['Deli_ConfirmArrivalFlg'];

					    // 立替・売上管理テーブルから本締め／仮締め区分取得
					    $mdlpas = new TablePayingAndSales( $this->_dbAdapter );
					    $pcs = $mdlpas->findPayingAndSales( array( 'OrderSeq' => $order['OrderSeq'] ) )->current()['PayingControlStatus'];

						// 修正の場合、着荷確認済みだとエラー
					    if( $deliConfirmArrivalFlg == 1 ) {
					        $errors[] = array(
					                $col_schema['ColumnName'] =>
					                'この注文は着荷確認済みです。'
					        );
					    }
					    // 修正の場合、データステータスが41,51 でないとエラー (2015/04/02)
					    if( !($order['DataStatus'] == 41 || $order['DataStatus'] == 51) ) {
					        $errors[] = array(
					                $col_schema['ColumnName'] =>
					                '伝票入力可能なステータスの注文ではありません。'
					        );
					    }
						// 修正の場合、伝票番号が同じならエラー (2015/04/02)
						else if( $journalNumber == $row[ $this->findSchemaOrder('Deli_JournalNumber') ]
							&& $methodName == $this->findDeliMethod( $row[ $this->findSchemaOrder('Deli_DeliveryMethod') ] )['DeliMethodName'] ) {
							$errors[] = array(
									$col_schema['ColumnName'] =>
									'この注文は同じ伝票番号で登録済です。'
							);
						}
    					// 修正の場合、本締め／仮締め区分が本締めならエラー (2015/04/02)
						else if( $pcs == 1 ) {
    						$errors[] = array(
    						        $col_schema['ColumnName'] =>
    						        '既に立替処理が終わっている注文です。'
    						);
    					}
    					// 修正の場合、キャンセル申請中ならエラー (2015/08/27)
                        else if($order['Cnl_Status'] == 1) {
    						$errors[] = array(
    						        $col_schema['ColumnName'] =>
    						        'この注文はキャンセル済みです。'
    						);
                        }
					} else if( $this->getMode() == self::MODE_REGIST ) {
					    // 注文商品テーブルから配送方法と配送伝票番号を取得
					    $mdloi = new TableOrderItems( $this->_dbAdapter );
					    $orderItems = $mdloi->findByOrderSeq( $order['OrderSeq'] )->current();
					    $journalNumber = $orderItems['Deli_JournalNumber'];

					    // 登録の場合、データステータスが31でないとエラー (2015/04/02)
					    if( $order['DataStatus'] != 31 ) {
					        $errors[] = array(
					                $col_schema['ColumnName'] =>
					                '伝票番号登録待ち注文ではありません。'
					        );
					    }

						// 登録の場合、伝票番号が登録済みならエラー (2015/04/02)
						else if( !empty( $journalNumber ) ) {
							$errors[] = array(
									$col_schema['ColumnName'] =>
									'この注文は既に伝票番号登録済です。'
							);
						}

						// 登録の場合、キャンセル申請中ならエラー
						else if($order['Cnl_Status'] == 1) {
						    $errors[] = array(
						            $col_schema['ColumnName'] =>
						            'この注文はキャンセル済みです。'
						    );
						}
					}
					if( empty( $errors ) ) {
						// 注文データが見つかったのでキャッシュしておく
						$result[ 'OrderSeq' ] = $order['OrderSeq'];
						$this->_orderCache[ $order['OrderSeq'] ] = $order;
					}
					break;

				case 'Deli_DeliveryMethod':
					// 配送方法の場合は配送会社名からIDを逆引き
					$deliMethod = $this->findDeliMethod( $value );
					if( $deliMethod != null ) {
						// 配送方法がマスタに存在
						if( $deliMethod['ProductServiceClass'] == 1 ) {
							// 役務区分の配送方法はエラー (2015/04/02)
							$errors[] = array(
									$col_schema['ColumnName'] =>
									'物販用の配送会社ではありません。 => ' . $value
							);
						}
						$result[ 'Deli_DeliveryMethodName' ] = $value;
						$value = $deliMethod['DeliMethodId'];

					} else {
						// 配送方法が見つからない
						$errors[] = array(
							$col_schema['ColumnName'] =>
							'該当する配送会社がありません。 => ' . $value
						);
					}
					break;

				case 'Ent_OrderId':
					// 任意注文番号は注文ID処理時にマッチングに使用されたので検証は行わない
					continue;
			}
			$validate_exp = $col_schema['ValidationRegex'];
			// 08.9.30 正規表現の扱いを変更 → preg_matchをmb_eregに変更 by eda
			if( $col_schema['ColumnName'] == 'Deli_JournalNumber' ) {
				$deliMethod = $this->findDeliMethod( $row[ $this->findSchemaOrder('Deli_DeliveryMethod') ] );
				if( $deliMethod != null && $deliMethod['JournalRegistClass'] == 0 ) {
					$is_valid = true;
					// 伝票番号登録区分(0：不要)が指定されている場合はチェックなし(20151119)
				} else if( $deliMethod == null || strlen( "{$deliMethod['ValidateRegex']}" ) == 0 ) {
					// 伝票番号の場合のみ、配送マスタの定義から検証
					if( strlen( "{$deliMethod['ValidateRegex']}" ) == 0 ) {
						$is_valid = mb_ereg( preg_replace('/((^\/)|(\/[img]*$))/', '', $col_schema['ValidationRegex']), $value );
					}
				} else {
					$validate_exp = $deliMethod['ValidateRegex'];
					$is_valid = mb_ereg( $deliMethod['ValidateRegex'], $value );
				}
			} else if($col_schema['ColumnName'] == 'ReceiptOrderDate') {
				// 注文日は無条件に検証OK（2013.7.12 eda）
				$is_valid = true;

			} else if($col_schema['ColumnName'] == 'Deli_DeliveryMethod' ) {
			    // 配送会社は検証済みのため検証しない (2015/06/01)
			    $is_valid = true;
			} else {
				// 伝票番号以外はスキーマ定義から検証
				$is_valid = mb_ereg( preg_replace('/((^\/)|(\/[img]*$))/', '', $col_schema['ValidationRegex']), $value );

			}
			if( ! $is_valid ) {
				// 検証エラー
				$errors[] = array(
					$col_schema['ColumnName'] =>
					( empty($value) ? 'データを省略できません。' : ( 'データが間違っています。桁数が間違っているか、ハイフンや空白などで区切られているかもしれません。 => ' . $value ) )
				);
			}
			$result[ $col_schema['ColumnName'] ] = $value;
			$index++;
		}

		if( count( $errors ) > 0 ) {
			$errors['_raw_data'] = $result;
			return new CoralCsvHandlerLine( $errors, $line, CoralCsvHandlerLine::TYPE_ERROR );
		}

		return new CoralCsvHandlerLine( $result, $line, CoralCsvHandlerLine::TYPE_DATA );
	}

	/**
	 * CSV処理開始前の準備を行う
	 *
	 * @access protected
	 */
	protected function begin() {
        if( $this->getDeliveryMethodMaster() == null ) {
            $enterprise = new \models\Table\TableEnterprise($this->_dbAdapter);
            $entData = $enterprise->findEnterprise($this->getEnterpriseId)->current();

            $delilogic = new \models\Logic\LogicDeliveryMethod($this->_dbAdapter);
            $this->setDeliveryMethodMaster( $delilogic->getDeliMethodList($entData['OemId']) );
        }
        $this->_orderCache = array();
	}

	protected function end($result) {

	}

	/**
	 * 注文IDから注文データを検索する
	 *
	 * @access private
	 * @param int $orderId 注文ID
	 * @return array|null マッチする注文データが1件存在する場合はそのデータ、それ以外（重複含む）はnull
	 */
	private function findOrder($orderId, $entOrderId = null) {
		if( empty($entOrderId) ) $entOrderId = null;

		$orders = array();
		foreach( $this->getOrderList() as $orderItem ) {
			if( ! empty( $orderId ) ) {
				// 注文IDが空でない場合は注文IDでの一致を試みる
				if( $orderItem['OrderId'] == $orderId) { $orders[] = $orderItem; break; }
			} else if( ! empty( $entOrderId ) ) {
				// 注文IDが空の場合のみ、任意注文番号での一致を試みる
				if( $orderItem['Ent_OrderId'] == $entOrderId ) { $orders[] = $orderItem; break; }
			}
		}
		return ( count( $orders ) == 1 ) ? $orders[0] : null;
	}

	/**
	 * 指定の配送方法に一致するデータを配送方法マスタから取得する
	 *
	 * @access private
	 * @param string|int $deliMethodName 配送方法。配送方法名称（正式およびB）またはIDと比較される
	 * @return array|null 一致するデータが配送方法マスタに存在する場合はそのデータ、それ以外はnull
	 */
	private function findDeliMethod($deliMethodName) {
		foreach( $this->getDeliveryMethodMaster() as $deliRow ) {
			if(
				$deliRow['DeliMethodId'] == $deliMethodName ||
				$deliRow['DeliMethodName'] == $deliMethodName ||
				$deliRow['DeliMethodNameB'] == $deliMethodName
			) {
				return $deliRow;
			}
		}
		return null;
	}

	/**
	 * CSVスキーマを探索し、任意注文番号のカラム位置を検出する
	 *
	 * @access private
	 * @return int 任意注文番号のカラム位置または-1
	 */
	private function getEntOrderIdIndex() {
		$index = 0;
		foreach( $this->getCsvSchema() as $col_schema ) {
			if( $col_schema['ColumnName'] == 'Ent_OrderId' ) return $index;
			$index++;
		}
		return -1;
	}

	/**
	 * 注文ID絡みの検証エラー行を生成する
	 *
	 * @access private
	 * @param string $orderId
	 * @param string $entOrderId
	 * @param bool $doubling 重複エラーかを指定するフラグでfalseの場合はデータ検出エラーを意味する
	 * @return array エラーに関連付けるキーとエラーメッセージを格納した連想配列
	 */
	private function createOrderError($orderId, $entOrderId, $doubling) {
		if( empty($orderId) && empty($entOrderId) ) {
			return array( 'OrderId' => '注文IDまたは任意注文番号が指定されていません。' );
		}

		if( empty($entOrderId) ) {
			return $doubling ?
				( array( 'OrderId' => "注文ID '$orderId' が複数行存在します。" ) ) :
				( array( 'OrderId' => "注文ID '$orderId' に一致するデータが見つかりません。" ) );
		}
		if( empty($orderId) ) {
			return $doubling ?
				( array( 'OrderId' => "任意注文番号 '$entOrderId' が複数行存在します。" ) ) :
				( array( 'OrderId' => "任意注文番号 '$entOrderId' に一致するデータが見つかりません。" ) );
		}

		return $doubling ?
			( array( 'OrderId' => "注文ID '$orderId' または 任意注文番号 '$entOrderId' が複数行存在します。" ) ) :
			( array( 'OrderId' => "注文ID '$orderId' または 任意注文番号 '$entOrderId' に一致するデータが見つかりません。" ) );
	}

	/**
	 * 指定行がヘッダ行かを検出する
	 *
	 * @access private
	 * @param array $row 検査する行データ
	 * @return bool $rowがヘッダ行と判断できた場合はtrue、それ以外はfalse
	 */
	private function checkHeaderLine($row) {
		$schema = $this->getCsvSchema();
		$check_cols = array(
			'ReceiptOrderDate' => null
		);
		$index = 0;
		foreach( $schema as $col_schema ) {
			if( array_key_exists( $col_schema['ColumnName'], $check_cols ) ) {
				$check_cols[ $col_schema['ColumnName'] ] = array( 'index' => $index, 'schema' => $col_schema );
			}
			$index++;
		}
		// チェック対象のカラムがスキーマに含まれない場合は無条件に非ヘッダ行
		if( BaseGeneralUtils::ArrayIsAllEmpty( $check_cols ) ) {
			return false;
		}

		$result = true;
		foreach( $check_cols as $key => $schema ) {
			$value = $row[$schema['index']];

			// ReceiptOrderDateの検出は、日付と見なせる文字列または空文字に読み替えて実行（2013.7.12 eda）
			$validator = $key == 'ReceiptOrderDate' ?
				'/^(\d{2,4}([\/\-.]\d{1,2}){2})?$/' :
				$schema['schema']['ValidationRegex'];

			// 前後の空白を除去して検証実行
			$match = preg_match( $validator, trim($value) );

			// 検証がNG且つ値が空でない場合はヘッダカラムの可能性ありとみなす
			$result = $result && ( ! $match && ! empty( $value ) );
		}
		return $result;
	}

	/**
	 * 指定のカラム名の、検証スキーマ上の列位置を探索する
	 *
	 * @access private
	 * @param string $columnName 探索するカラム名
	 * @return int 指定カラムの、検索スキーマ上の列位置
	 */
	private function findSchemaOrder($columnName) {
		foreach( $this->getCsvSchema() as $col_schema ) {
			if( $col_schema['ColumnName'] == $columnName ) return ( $col_schema['Ordinal'] - 1 );
		}
		return -1;
	}

	/**
	 * 指定の配列の中にnull以外の値が含まれているかを判断する。
	 *
	 * @access private
	 * @param array 検索する配列データ
	 * @return bool $arrayのすべての要素がnullの場合はfalse、それ以外はtrue
	 */
	private function array_has_value($array) {
		$result = true;
		foreach($array as $item) $result = $result && ( $item != null );
		return $result;
	}
}