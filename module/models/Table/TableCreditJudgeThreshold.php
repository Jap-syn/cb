<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_CreditJudgeThresholdテーブルへのアダプタ
 */
class TableCreditJudgeThreshold
{
	protected $_name = 'T_CreditJudgeThreshold';
	protected $_primary = array('Seq');
	protected $_adapter = null;

	/**
	 * コンストラクタ
	 *
	 * @param Adapter $adapter アダプタ
	 */
	public function __construct(Adapter $adapter)
	{
	    $this->_adapter = $adapter;
	}

	/**
	 * データ取得
	 *
     * @param int $seq シーケンス
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql = " SELECT * FROM T_CreditJudgeThreshold WHERE Seq = :Seq ORDER BY Seq ";
        return $this->_adapter->query($sql)->execute(array(':Seq' => $seq));
	}


    /**
     * 指定された与信判定基準でデータ取得
     *
     * @param unknown $creditcriterionid 与信判定基準ID
     * @return ResultInterface
     */
    public function getByCriterionid($creditcriterionid)
    {
        $sql = " SELECT * FROM T_CreditJudgeThreshold WHERE CreditCriterionId = :CreditCriterionId AND ValidFlg = 1 ORDER BY Seq ";
        return $this->_adapter->query($sql)->execute(array(':CreditCriterionId' => $creditcriterionid));
    }

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_CreditJudgeThreshold (CreditCriterionId, JudgeSystemHoldMAX, JudgeSystemHoldMIN, CoreSystemHoldMAX, CoreSystemHoldMIN, JintecManualJudgeUnpaidFlg, JintecManualJudgeNonPaymentFlg, JintecManualJudgeSns, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :CreditCriterionId ";
        $sql .= " , :JudgeSystemHoldMAX ";
        $sql .= " , :JudgeSystemHoldMIN ";
        $sql .= " , :CoreSystemHoldMAX ";
        $sql .= " , :CoreSystemHoldMIN ";
        $sql .= " , :JintecManualJudgeUnpaidFlg ";
        $sql .= " , :JintecManualJudgeNonPaymentFlg ";
        $sql .= " , :JintecManualJudgeSns ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CreditCriterionId' => $data['CreditCriterionId'],
                ':JudgeSystemHoldMAX' => $data['JudgeSystemHoldMAX'],
                ':JudgeSystemHoldMIN' => $data['JudgeSystemHoldMIN'],
                ':CoreSystemHoldMAX' => $data['CoreSystemHoldMAX'],
                ':CoreSystemHoldMIN' => $data['CoreSystemHoldMIN'],
                ':JintecManualJudgeUnpaidFlg' => isset($data['JintecManualJudgeUnpaidFlg']) ? $data['JintecManualJudgeUnpaidFlg'] : 0,
                ':JintecManualJudgeNonPaymentFlg' => isset($data['JintecManualJudgeNonPaymentFlg']) ? $data['JintecManualJudgeNonPaymentFlg'] : 0,
                ':JintecManualJudgeSns' => $data['JintecManualJudgeSns'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

	/**
	 * レコードを更新する。
	 *
	 * @param array $data 更新内容
     * @param int $seq シーケンス
     * @return ResultInterface
	 */
	public function saveUpdate($data, $seq)
	{
        $row = $this->find($seq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_CreditJudgeThreshold ";
        $sql .= " SET ";
        $sql .= "     CreditCriterionId = :CreditCriterionId ";
        $sql .= " ,   JudgeSystemHoldMAX = :JudgeSystemHoldMAX ";
        $sql .= " ,   JudgeSystemHoldMIN = :JudgeSystemHoldMIN ";
        $sql .= " ,   CoreSystemHoldMAX = :CoreSystemHoldMAX ";
        $sql .= " ,   CoreSystemHoldMIN = :CoreSystemHoldMIN ";
        $sql .= " ,   JintecManualJudgeUnpaidFlg = :JintecManualJudgeUnpaidFlg ";
        $sql .= " ,   JintecManualJudgeNonPaymentFlg = :JintecManualJudgeNonPaymentFlg ";
        $sql .= " ,   JintecManualJudgeSns = :JintecManualJudgeSns ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Seq              = :Seq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Seq' => $seq,
                ':CreditCriterionId' => $row['CreditCriterionId'],
                ':JudgeSystemHoldMAX' => $row['JudgeSystemHoldMAX'],
                ':JudgeSystemHoldMIN' => $row['JudgeSystemHoldMIN'],
                ':CoreSystemHoldMAX' => $row['CoreSystemHoldMAX'],
                ':CoreSystemHoldMIN' => $row['CoreSystemHoldMIN'],
                ':JintecManualJudgeUnpaidFlg' => $row['JintecManualJudgeUnpaidFlg'],
                ':JintecManualJudgeNonPaymentFlg' => $row['JintecManualJudgeNonPaymentFlg'],
                ':JintecManualJudgeSns' => $row['JintecManualJudgeSns'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
	}
	/**
	 * 入力チェック
	 * @param array $datas チェックするパラメータの配列
	 * @param array $check_model チェックする型 string:文字 int:数値
	 * @param array $string_name エラー表示用日本語名
	 */
	public function validation_check($datas,$check_model,$string_name)
	{
	    $i = 0;
		foreach($datas as $key=>$value){

			//stringの場合空チェック
			if($check_model[$i] == "string"){
				//空の場合false
				if(empty($value) && strlen($value)==0){
					return array(false,$string_name[$i]);
				}
			} elseif($check_model[$i] == "int"){

				//intの場合数値チェック
				if(!is_numeric($value)){
					return array(false,$string_name[$i]);
				}

				//空の場合false
				if(empty($value) && strlen($value)==0){
					return array(false,$string_name[$i]);
				}
			}
			$i++;
		}
		return array(true,"");
	}
}
