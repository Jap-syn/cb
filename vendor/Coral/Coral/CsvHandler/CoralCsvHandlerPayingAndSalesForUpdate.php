<?php
namespace Coral\Coral\CsvHandler;

use Zend\Db\Adapter\Adapter;
use models\Table\TableDeliMethod;
use Coral\Base\BaseDelegate;

class CoralCsvHandlerPayingAndSalesForUpdate extends CoralCsvHandlerAbstract {
	const OPTIONS_ENT_ID = 'ent_id';

	const OPTIONS_DBADAPTER = 'dbAdapter';

	const OPTIONS_VALIDATE_CALLBACK = 'varidate_callback';

	const OPTIONS_COLUMN_INFO = 'column_info';

	/**
	 * ログイン中の事業者ID
	 *
	 * @access protected
	 * @var int
	 */
	protected $_ent_id;

	/**
	 * DBアダプタ
	 *
	 * @access protected
	 * @var Adapter
	 */
	protected $_dbAdapter;

	/**
	 * 入力検証に使用するコールバックデリゲート
	 *
	 * @access protected
	 * @var BaseDelegate
	 */
	protected $_validation_callback;

	/**
	 * 入力CSVの列位置に一致するカラム名
	 *
	 * @access protected
	 * @var array
	 */
	protected $_column_info = array();

	/**
	 * 配送方法マスタ
	 *
	 * @access protected
	 * @var array
	 */
	protected $_deli_method_master = null;

	/**
	 * 配送方法名からの逆引きキャッシュ
	 *
	 * @access protected
	 * @var array
	 */
	protected $_deli_method_cache = null;

	protected function init(array $options) {
	    foreach( $options as $key => $value ) {
			switch($key) {
				case self::OPTIONS_ENT_ID:
					$this->setEnterpriseId( $value );
					break;
				case self::OPTIONS_DBADAPTER:
					$this->setDbAdapter( $value );
					break;
				case self::OPTIONS_VALIDATE_CALLBACK:
					$this->setValidationCallback( $value );
					break;
				case self::OPTIONS_COLUMN_INFO:
					$this->setColumnInfo( $value );
					break;
			}
		}

		// DBアダプタのチェック
		if( $this->getDbAdapter() == null ) throw new \Exception( 'DBアダプタが設定されていません' );

		// 事業者IDのチェック
		if( ! is_int( $this->getEnterpriseId() ) ) throw new \Exception( '事業者IDが設定されていません' );

		// コールバックのチェックはbegin()まで遅延させる
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
	 * @return CoralCsvHandlerPayingAndSalesForUpdate
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
	 * @return CoralCsvHandlerPayingAndSalesForUpdate
	 */
	public function setDbAdapter(Adapter $adapter) {
	    $this->_dbAdapter = $adapter;
		return $this;
	}

	/**
	 * 入力検証時に使用するコールバックデリゲートを取得する
	 *
	 * @return BaseDelegate
	 */
	public function getValidationCallback() {
	    return $this->_validation_callback;
	}

	/**
	 * 入力検証時に使用するコールバックデリゲートを設定する
	 *
	 * @param BaseDelegate $callback コールバックデリゲート
	 * @return CoralCsvHandlerPayingAndSalesForUpdate
	 */
	public function setValidationCallback(BaseDelegate $callback) {
	    $this->_validation_callback = $callback;
		return $this;
	}

	/**
	 * 入力CSVの列位置に対応するカラム情報を取得する
	 *
	 * @return array
	 */
	public function getColumnInfo() {
	    return $this->_column_info;
	}

	/**
	 * 入力CSVの列位置に対応するカラム情報を設定する
	 *
	 * @param array $col_info カラム情報
	 * @return CoralCsvHandlerPayingAndSalesForUpdate
	 */
	public function setColumnInfo(array $col_info) {
	    if(! is_array($col_info)) $col_info = array();
		$this->_column_info = $col_info;
		return $this;
	}

	/**
	 * 配送方法マスターデータを取得する。
	 * 戻り値はキーが配送方法ID、値が検証用正規表現文字列の連想配列となる
	 *
	 * @return array
	 */
	public function getDeliMethodMaster() {
	    if($this->_deli_method_master == null) {
			$result = array();
			$logic = new \models\Logic\LogicDeliveryMethod($this->getDbAdapter());
			$ri = $logic->getDeliMethodListByEnterpriseId($this->getEnterpriseId());
			foreach($ri as $m_row) {
				$result[$m_row['DeliMethodId']] = nvl($m_row['ValidateRegex'], '^.+$');
			}
			$this->_deli_method_master = $result;
		}
		return $this->_deli_method_master;
	}

	/**
	 * CSVの行を処理する
	 *
	 * @param array $row 読み取られた行データ
	 * @param int $line 読み取り行のインデックス。0から始まる
	 * @return CoralCsvHandlerLine
	 */
	protected function _validate(array $row, $line) {
	    $col_info = $this->getColumnInfo();
		$callback = $this->getValidationCallback();

		$result = array();
		$errors = array();

		// 列数が定義と異なる場合は速攻エラー扱い
		if( count($row) != count(array_keys($col_info)) && count($row) != count(array_keys($col_info)) - 1 ) {
		    return new CoralCsvHandlerLine( array( array( '行' => '行データの形式が不正です。' ) ), $line, CoralCsvHandlerLine::TYPE_ERROR );
		}

		// 1行目はヘッダ行チェックを行い、ヘッダ行ならスキップ
		if( $line == 0 && $this->checkHeaderLine($row) ) {
		    return new CoralCsvHandlerLine( $row, $line, CoralCsvHandlerLine::TYPE_HEADER );
		}

		// 注文IDのカラムインデックス → 0 固定
		$order_id_col = 0;

		// 任意注文番号のカラムインデックス → 1 固定
		$ent_oid_col = 1;

		// 配送会社のカラムインデックス → 2 固定
		$deli_name_col = 2;

		// 伝票番号のカラムインデックス → 3 固定
		$deli_number_col = 3;

		// 自社印刷分フラグのカラムインデックス → 列定義の末尾
		$sb_flg_col = count(array_keys($col_info)) - 1;

		// 検証用に現在の行データを取得する
		$current_row = $callback->invoke($row[$order_id_col]);
		if(!$current_row) {
		    // エラー情報を仮登録
		    $errors[] = array('OrderId' => sprintf("注文ID '%s' はデータが見つからないか、伝票番号更新ができない状態です。", $row[$order_id_col]));
		    // 注文IDで見つからないので任意注文番号での取得も試みる
		    $current_row = $callback->invoke($row[$ent_oid_col], true);
		    if(!$current_row) {
		        // 対象行が見つからなかった
		        $errors[] = array('Ent_OrderId' => sprintf("任意注文番号 '%s' はデータが見つからないか、伝票番号更新ができない状態です。", $row[$ent_oid_col]));
		    } else if(count($current_row) > 1) {
		        // 対象行が複数見つかった
		        $errors[] = array('Ent_OrderId' => sprintf("任意注文番号 '%s' の注文を特定できません。重複があります。", $row[$ent_oid_col]));
		        $current_row = null;	// 現在行をクリア
		    } else {
		        // 対応する行が見つかったので現在行として定義しなおし、エラー情報の仮登録を削除
		        $current_row = $current_row[0];
		        array_pop($errors);
		        // 注文IDを割り振っておく
		        $row[$order_id_col] = $current_row['OrderId'];
		    }
		}

		// 配送方法検証マスタ
		$deli_validator = $this->getDeliMethodMaster();

		// 同値性検証カラム名リスト
		$check_col_names = array(
		        'Ent_OrderId',
		        'ReceiptOrderDate'
		);

        // 処理行の配送方法と対応する伝票番号検証ルールを取得
        if(!strlen(trim($row[$deli_name_col]))) {
            // 配送会社がブランクの場合は配送会社変更なしとして扱う
            $row[$deli_name_col] = $current_row['Deli_DeliveryMethodName'];
        }
        $deli_row = $this->findDeliMethodByDeliMethodName($row[$deli_name_col]);
        $current_deli_row = $this->findDeliMethodByDeliMethodName($current_row['Deli_DeliveryMethodName']);
        $regex = $deli_row ? $deli_validator[$deli_row['DeliMethodId']] : null;
        if(!$deli_row || !$regex) {
            // 配送方法不正・検証ルール不正
            $errors[] = array('Deli_DeliveryMethodName' => sprintf("無効な'%s'が指定されました", $col_info['Deli_DeliveryMethodName']));
        }

		// 検証実行
		$has_invalid_col = false;
		$i = 0;
		foreach($col_info as $col_name => $label) {
			// 値抽出
			$value = trim($row[$i]);

			// 注文日の場合は日付フォーマットでの補正を試みる
			if($col_name == 'ReceiptOrderDate') {
				$value = f_df($value, 'Y-m-d');
			}

			if($current_row && $col_name == 'Deli_JournalNumber') {
				// 伝票番号カラム
			    // 正規表現で検証実行
				if(!$regex) {
					// 検証ルール無効
					$errors[] = array($col_name => sprintf("'%s'の検証ができません", $label));
				} else {
					if(!mb_ereg($regex, $value)) {
						// 伝票番号の検証エラー
						$errors[] = array($col_name => sprintf("'%s'の形式が間違っています。", $label));
					} else if($value == $current_row[$col_name]) {
						// 更新されていない
						$errors[] = array($col_name => sprintf("'%s'の内容が変更されていません。", $label));
					}
				}
            } else if($current_row && $col_name == 'Deli_DeliveryMethodName') {
                // 配送会社
                // 正しい配送方法が指定されていたら配送会社名と配送方法IDをセット
                if($regex) {
                    $value = $deli_row['DeliMethodName'];
                    $result['Deli_DeliveryMethod'] = $deli_row['DeliMethodId'];
                    if($deli_row['DeliMethodId'] != $current_deli_row['DeliMethodId']) {
                        $result['DeliMethodChanged'] = true;
                    }
                }
			} else if($current_row && in_array($col_name, $check_col_names)) {
				// 同値性検証の対象カラムの場合
				// 入力が検証データと一致しているかをチェック
				if($value != $current_row[$col_name]) {
					$errors[] = array($col_name => sprintf("'%s'は書き換え禁止データです。", $label));
				}
			} else {
				// それ以外のカラムはチェックしない
				if($current_row) {
					// 検証用データがある場合はその値で更新
					$value = $current_row[$col_name];
				}
			}
			$result[$col_name] = $col_name == 'IsSelfBilling' ? ($value ? '○' : '') : $value;
			$i++;
		}

		if( count( $errors ) > 0 ) {
			$errors['_raw_data'] = $result;
			return new CoralCsvHandlerLine($errors, $line, CoralCsvHandlerLine::TYPE_ERROR);
		}

		return new CoralCsvHandlerLine($result, $line, CoralCsvHandlerLine::TYPE_DATA);
	}

	/**
	 * CSV処理開始前の準備を行う
	 */
	protected function begin() {
	    // 設定されているコールバックの正当性を確認
		if( $this->getValidationCallback() == null || (!$this->getValidationCallback() instanceof BaseDelegate)) {
			throw new \Exception( '検証コールバックの設定に不備があります' );
		}

		// 配送方法逆引きキャッシュの初期化
		$this->_deli_method_cache = array();
	}

	protected function end($result) {

	}

	/**
	 * 指定行がヘッダ行かを検出する
	 *
	 * @param array $row 検査する行データ
	 * @return bool $rowがヘッダ行と判断できた場合はtrue、それ以外はfalse
	 */
	private function checkHeaderLine($row) {
		// 5番目の列（＝注文日）がyyyy-MM-dd形式でない場合はヘッダ行と見なす
		return !preg_match('/^\d{4}-\d{2}-\d{2}$/u', $row[4]);
	}

    /**
     * 指定の配送方法名（または配送方法ID）で配送方法を検索する。
     * このメソッドは、M_DeliveryMethod.ValidFlgの設定や、現在処理対象のOEMでの配送方法設定の内容に
     * 関わらず、指定の配送方法名に対応するM_DeliveryMethodの行データを返す
     *
     * @access protected
     * @param mixed $deliMethodName 配送方法名、別名、または配送方法ID
     * @return array 該当する配送方法マスターデータ（連想配列）
     */
    protected function findDeliMethodByDeliMethodName($deliMethodName) {
        if(!isset($this->_deli_method_cache[$deliMethodName])) {
            $tbl = new TableDeliMethod($this->_dbAdapter);

            $row = null;
            if(is_int($deliMethodName)) {
                // 配送方法ID検索
                $row = $tbl->find($deliMethodName)->current();
            } else {
                // 配送方法名検索
                $w1 = " SELECT * FROM M_DeliveryMethod WHERE DeliMethodName  = :DeliMethodName ORDER BY DeliMethodId ";
                $w2 = " SELECT * FROM M_DeliveryMethod WHERE DeliMethodNameB = :DeliMethodName ORDER BY DeliMethodId ";

                $row = $this->_dbAdapter->query($w1)->execute(array(':DeliMethodName' => $deliMethodName))->current();
                if(!$row) {
                    $row = $this->_dbAdapter->query($w2)->execute(array(':DeliMethodName' => $deliMethodName))->current();
                }
            }

            if($row) {
                // DeliMethodId、DeliMethodName、DeliMethodNameBのそれぞれを
                // 今回のデータのキーにする
                $this->_deli_method_cache[$row['DeliMethodId']] =
                $this->_deli_method_cache[$row['DeliMethodName']] =
                $this->_deli_method_cache[$row['DeliMethodNameB']] = $row;
            }
        }

        return $this->_deli_method_cache[$deliMethodName];
    }
}