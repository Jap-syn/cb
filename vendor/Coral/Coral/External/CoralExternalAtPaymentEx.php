<?php
namespace Coral\Coral\External;

/**
 * 拡張＠ペイメント入金ファイルパーサ
 * オリジナルと異なり、企業コード、顧客番号の突合を行わない
 */
class CoralExternalAtPaymentEx extends CoralExternalAtPayment {
	/**
	 * 受け入れ対象の＠ペイメントコンビニ加入者固有コードリスト
	 *
	 * @access protected
	 * @var array
	 */
	protected $_validCvSubscriberCode = array();

	/**
	 * 受け入れ対象の＠ペイメント郵振加入者固有データリスト
	 *
	 * @access protected
	 * @var array
	 */
	protected $_validYuSubscriberData = array();

	/**
	 * オーバーライド。コンストラクタ
	 * @param unknown $fileName
	 */
	public function __construct($fileName)
	{
        parent::__construct($fileName, 0, 0);
    }

	/**
	 * 処理対象のコンビニ加入者固有コードのリストを取得する。
	 * 加入者固有コードは4桁の数字のみで構成されている必要がある。
	 *
	 * @return array
	 */
	public function getValidCvsSubscriberCode()
	{
		return $this->_validCvSubscriberCode;
	}
	/**
	 * 処理対象のコンビニ加入者固有コードのリストを設定する
	 * 加入者固有コードは4桁の数字のみで構成されている必要がある。
	 *
	 * @param array $list 加入者固有コードリスト
	 * @return CoralExternalAtPaymentEx このインスタンス
	 */
	public function setValidCvsSubscriberCode(array $list)
	{
		if(!is_array($list)) $list = array();
		$this->_validCvSubscriberCode = $list;
		return $this;
	}

	/**
	 * 処理対象のゆうちょ加入者固有データのリストを取得する。
	 * 加入者固有データは9桁の数字のみで構成されている必要がある。
	 *
	 * @return array
	 */
	public function getValidYuchoSubscriberData()
	{
		return $this->_validYuSubscriberData;
	}
	/**
	 * 処理対象のゆうちょ加入者固有データのリストを取得する。
	 * 加入者固有データは9桁の数字のみで構成されている必要がある。
	 *
	 * @param array $list 加入者固有データリスト
	 * @return CoralExternalAtPaymentEx このインスタンス
	 */
	public function setValidYuchoSubscriberData(array $list)
	{
		if(!is_array($list)) $list = array();
		$this->_validYuSubscriberData = $list;
		return $this;
	}

	/**
	 * オーバーライド。ファイル解析を実行する
	 * @throws Exception
	 */
	protected function analyze()
	{
		$totalRecordsNum = 0;
		$subTotalRecordsNum = 0;

		$handle = @fopen($this->fileName, "r");

		if (!$handle) {
			throw new \Exception("Can't open file. filename=" . $this->fileName, 1);
		}

		// 処理対象の支払方法区分に対して処理するデータ種別と回収区分を関連付けて定義
		$recTypeConfig = array(
			'1' => array('DataKind' => 1, 'CollectionType' => 1),	// 1：コンビニ	→ 1：速報、1：通常回収
// Mod By Takemasa(NDC) 20160418 Stt [＠ペイメントインポート]での、OrderSeqﾚｺｰﾄﾞ取込み処理の復活
// 			//'2' => array('DataKind' => 2, 'CollectionType' => 1)	// 2：郵振	→ 2：確報、1：通常回収
// 			'2' => array('DataKind' => 2, 'CollectionType' => 9)	// ※：2014年9月現在、MT未対応のため郵振は処理しないよう架空の回収区分を設定
	        '2' => array('DataKind' => 2, 'CollectionType' => 1)	// 2：郵振	→ 2：確報、1：通常回収
// Mod By Takemasa(NDC) 20160418 End [＠ペイメントインポート]での、OrderSeqﾚｺｰﾄﾞ取込み処理の復活
		);
		$validCvSubscribers = $this->getValidCvsSubscriberCode();	// 有効なコンビニ加入者固有コードのリスト
		$validYuSubscribers = $this->getValidYuchoSubscriberData();	// 有効なゆうちょ加入者固有データのリスト

		while ($record = fgets($handle)) {
			$totalRecordsNum++;
			$recordType = (int)substr($record, 0, 1);

			if ($recordType == 2) {
				$subTotalRecordsNum++;

				// 明細レコード
				$apData = new CoralExternalAtPaymentDataEx();
				$apData->PayWayType = (int)substr($record, 1, 1);
				$apData->DataKind = (int)substr($record, 2, 1);
				$apData->CollectionType = (int)substr($record, 3, 1);
				$apData->SettlementType = (int)substr($record, 4, 2);
				$apData->KeyInfo = substr($record, 6, 53);
				$apData->PaymentAmount = (int)substr($record, 60, 9);
				$apData->CustPaymentDate = substr($record, 69, 8);
				$apData->AccountPaymentDate = substr($record, 77, 8);
				$apData->CorporateCode = (int)substr($record, 85, 5);
				$apData->etcAddCode = (int)substr($record, 90, 7);

                // バーコード仕様変更に伴いコンビニ支払、且つ、20桁目が0の場合は対象外とする（CB_B2C_DEV-234）
                $target = true;
                if (($apData->PayWayType == 1) && ((int)substr($record, 19, 1) != 0)) {
                    $target = false;
                }

				$tConf = isset($recTypeConfig[$apData->PayWayType]) ? $recTypeConfig[$apData->PayWayType] : null;

                if (!$target) {
                    // 処理対象外のレコード
                    $this->errDatas[] = $apData;
                } elseif (
					is_array($tConf) &&										// 定義済み支払方法区分か
					$apData->DataKind == $tConf['DataKind'] &&				// データ種別の判断
					$apData->CollectionType == $tConf['CollectionType']		// 回収区分の判断
				)
				{
					// コンビニか郵便振替、確定情報、通常回収、であれば入金処理対象として処理

					try {
						if ($apData->PayWayType == 1) {
							// コンビニ
                            //$apData->AtobaraiOrderSeq = (int)substr($apData->KeyInfo, 12, 17);
                            $apData->AtobaraiOrderSeq = $this->extractUniqueKeyCvs($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）
							$apData->CorpCode = (int)substr($apData->KeyInfo, 8, 4);
							$apData->CustNum = $this->custNum;
							$apData->StampFlag = ((int)substr($apData->KeyInfo, 36, 1) == 1) ? true : false;

							// コンビニ加入者固有コード
							// → T_OemCvsAccount.SubscriberCode, T_OemClaimAccountInfo.Cv_SubscriberCode
							$apData->CvSubscriberCode = substr($apData->KeyInfo, 8, 4);
							// 加入者固有コードの有効リストが設定されている場合はコードの精査を行う
							if (count($validCvSubscribers) && !in_array($apData->CvSubscriberCode, $validCvSubscribers)) {
								throw new \Exception('undefined subscriber-code specified');
							}
						} else {
							// 郵便振替
                            //$apData->AtobaraiOrderSeq = (int)substr($apData->KeyInfo, 2, 42);
                            $apData->AtobaraiOrderSeq = $this->extractUniqueKeyYucho($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）

                            // 関数extractUniqueKeyYuchoで判断しきれない、[サポートされないレコード]の除外
                            if ($apData->AtobaraiOrderSeq > 99999999) {
                                throw new \Exception("Processing object outside (Invalid OrderSeq).");
                            }

							$apData->CorpCode = $this->corpCode;
							$apData->CustNum = substr($apData->KeyInfo, 44, 9);
							$apData->StampFlag = false;

							// ゆうちょ加入者固有コード
							// → T_OemYuchoAccount.SubscriberData, T_OemClaimAccountInfo.Yu_SubscriberData
							$apData->YuSubscriberCode = substr($apData->KeyInfo, 44, 9);
							// 加入者固有コードの有効リストが設定されている場合はコードの精査を行う
							if (count($validYuSubscribers) && !in_array($apData->YuSubscriberCode, $validYuSubscribers)) {
								throw new \Exception('undefined subscriber-code specified');
							}
						}

						$this->apDatas[] = $apData;
					} catch (\Exception $e) {
						$this->errDatas[] = $apData;
					}
                } elseif ($apData->DataKind == 3) {
                    try {
                        // 取消対象のレコード
                        if ($apData->PayWayType == 1) {
                            // コンビニ
                            $apData->AtobaraiOrderSeq = $this->extractUniqueKeyCvs($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）
                        } else {
                            // 郵便振替
                            $apData->AtobaraiOrderSeq = $this->extractUniqueKeyYucho($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）
                        }

                        $this->cnclDatas[] = $apData;
                    } catch (\Exception $e) {
                        $this->errDatas[] = $apData;
                    }
				} else {
					// 処理対象外のレコード
					$this->errDatas[] = $apData;
				}
			} elseif ($recordType == 8) {
				// 支払方法毎の小計が相違する場合は例外
				$recCnt = (int)substr($record, 1, 8);

				if ($recCnt != $subTotalRecordsNum) {
					fclose($handle);
					throw new \Exception("Sub Total Number of records are inconsistent.", 2);
				}

				$subTotalRecordsNum = 0;
			} elseif ($recordType == 9) {
				// 総レコード数が相違する場合は例外
				$recCnt = (int)substr($record, 1, 8);

				if ($recCnt != $totalRecordsNum) {
					fclose($handle);
					throw new \Exception("Total Number of records are inconsistent.", 3);
				}
			}
		}

		@fclose($handle);
	}

	/**
	 * 指定のCVS入金キイ情報から、後払い注文を特定するためのキー値を抽出する
	 *
	 * @access protected
	 * @var string $keyInfo キイ情報
	 * @return int 注文を特定するためのキー値。注文IDの数字部
	 */
	protected function extractUniqueKeyCvs($keyInfo)
	{
	    return substr($keyInfo, 19, 10);
	}

	/**
	 * 指定のゆうちょ入金キイ情報から、後払い注文を特定するためのキー値を抽出する
	 *
	 * @access protected
	 * @var string $keyInfo キイ情報
	 * @return int 注文を特定するためのキー値。注文IDの数字部
	 */
	protected function extractUniqueKeyYucho($keyInfo)
	{
	    return $this->checkUniqueKeyValue(substr($keyInfo, 2, 42));
	}

	/**
	 * 指定の注文キー文字列を精査し、数値型のキーに変換する。
	 * このクラスでサポートされないフォーマットのレコードから抽出された値を渡した場合、
	 * このメソッドは例外をスローする
	 *
	 * @access protected
	 * @var string $key 注文を特定するためのキー値
	 * @return int キー値を変換した数値
	 */
	protected function checkUniqueKeyValue($key)
	{
	    // キー領域先頭8文字がすべて0ではない場合はサポートされないフォーマット
	    if(!preg_match('/^0{8}/', $key))
	    {
	        throw new \Exception('サポートされないレコードフォーマット');
	    }
	    return (int)$key;
	}
}
