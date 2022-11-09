<?php
namespace Coral\Coral\External;

class CoralExternalAtPayment
{
	protected $fileName;   // 入金ファイル
	protected $corpCode;   // 会社コード（コンビニ用）
	protected $custNum;    // 顧客番号（郵便振替用）
	protected $apDatas;    // 解析結果：入金処理対象明細データ
	protected $errDatas;   // 解析結果：処理対象外の明細データ（取消対象データも含む）
	protected $cnclDatas;  // 解析結果：取消対象のデータ

	/**
	 * コンストラクタ
	 * @param unknown $fileName
	 * @param number $corpCode
	 * @param number $custNum
	 */
	function __construct($fileName, $corpCode = 0, $custNum = 0)
	{
		$this->fileName = $fileName;
		$this->corpCode = $corpCode;
		$this->custNum = $custNum;
		$this->apDatas = array();
		$this->errDatas = array();
		$this->cnclDatas = array();

		$this->analyze();
	}

	/**
	 * ファイル解析
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

		while ($record = fgets($handle)) {

			$totalRecordsNum++;
			$recordType = (int)substr($record, 0, 1);

			if ($recordType == 2) {

				$subTotalRecordsNum++;

				// 明細レコード
				$apData = new CoralExternalAtPaymentData();
				$apData->PayWayType = (int)substr($record, 1, 1);
				$apData->DataKind = (int)substr($record, 2, 1);
				$apData->CollectionType = (int)substr($record, 3, 1);
				$apData->SettlementType = (int)substr($record, 4, 2);
				$apData->KeyInfo = substr($record, 6, 53);
				$apData->PaymentAmount = (int)substr($record, 60, 9);
				$apData->CustPaymentDate = substr($record, 69, 8);
				$apData->AccountPaymentDate = substr($record, 77, 8);

				// バーコード仕様変更に伴いコンビニ支払、且つ、20桁目が0の場合は対象外とする（CB_B2C_DEV-231）
                $target = true;
				if (($apData->PayWayType == 1) && ((int)substr($record, 19, 1) == 0)) {
                    $target = false;
                }

				if (!$target) {
                    // 処理対象外のレコード
                    $this->errDatas[] = $apData;
                } elseif (
					//($apData->PayWayType == 1 || $apData->PayWayType == 2) 			// 1:コンビニ、2:郵便振替
					//&& ($apData->DataKind == 1 || $apData->DataKind == 2)			// 1:速報、2:確報
					//&& $apData->DataKind == 1										// 1:速報、2:確報
					(
						($apData->PayWayType == 1 && $apData->DataKind == 1) 		// 1:コンビニ かつ 1:速報
						||
						($apData->PayWayType == 2 && $apData->DataKind == 2)		// 2:郵便振替 かつ 2:確報
					)
					&& $apData->CollectionType == 1)								// 1:通常回収
				{
					// コンビニか郵便振替、確定情報、通常回収、であれば入金処理対象として処理

					try {
						if ($apData->PayWayType == 1) {

							// コンビニ
							//$apData->AtobaraiOrderId = (int)substr($apData->KeyInfo, 21, 8);
							$apData->AtobaraiOrderId = (int)substr($apData->KeyInfo, 13, 8);		// 2013.10.11 どうやら資料が間違っているらしい
							$apData->AtobaraiOrderId = $this->extractUniqueKeyCvs($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）
							$apData->CorpCode = (int)substr($apData->KeyInfo, 8, 4);
							$apData->CustNum = $this->custNum;
							$apData->StampFlag = ((int)substr($apData->KeyInfo, 36, 1) == 1) ? true : false;

							if ($apData->CorpCode != $this->corpCode) {
								//fclose($handle);
								throw new \Exception("Does not match corporation code included.", 4);
							}
						} else {
                            // 盛田屋ﾚｺｰﾄﾞ対応(OrderSeq通知ﾚｺｰﾄﾞの除外)
                            if (substr($record, 8, 34) == '0000000000000000000000000000000000') {
                                throw new \Exception("Processing object outside (MORITAYA order[type OrderSeq]).");
                            }

							// 郵便振替
							//$apData->AtobaraiOrderId = (int)substr($apData->KeyInfo, 29, 9);
							$apData->AtobaraiOrderId = $this->extractUniqueKeyYucho($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）

							$apData->CorpCode = $this->corpCode;
							$apData->CustNum = (int)substr($apData->KeyInfo, 44, 9);
							$apData->StampFlag = false;

							if ($apData->CustNum != $this->custNum) {
								//fclose($handle);
								throw new \Exception("Does not match customer number included.", 5);
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
                            $apData->AtobaraiOrderId = $this->extractUniqueKeyCvs($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）
                        } else {
                            // 郵便振替
                            $apData->AtobaraiOrderId = $this->extractUniqueKeyYucho($apData->KeyInfo);	// ユニークキーの抽出を専用メソッドに変更（2014.10.27 eda）
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

		fclose($handle);
	}

	/**
	 * 入金処理データを取得する
	 */
	function getApDatas()
	{
		return $this->apDatas;
	}

	/**
	 * 入金処理対象外データを取得する
	 * @return Ambigous <multitype:, CoralExternalAtPaymentData>
	 */
	function getApErrorDatas()
	{
		return $this->errDatas;
	}

	/**
	 * 取消対象のデータを取得する
	 */
	function getApCancelDatas()
	{
	    return $this->cnclDatas;
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
	    return $this->checkUniqueKeyValue(substr($keyInfo, 13, 8));
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
	    return $this->checkUniqueKeyValue(substr($keyInfo, 29, 9));
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
	    // キー領域先頭8文字がすべて0の場合はサポートされないフォーマット
	    if(preg_match('/^0{8}/', $key))
	    {
	        throw new \Exception('サポートされないレコードフォーマット');
	    }
	    return (int)$key;
	}
}
