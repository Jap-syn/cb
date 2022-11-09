<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableOem;

/**
 * 立替金振込手数料を管理するクラス
 */
class LogicFfTcFee
{
	// 立替金振込手数料：同行30,000円以上
	const SAME_FF_TC_FEESAME_FF_TC_FEE_THIRTYK_AND_OVER = 324;

	// 立替金振込手数料：同行30,000円未満
	const SAME_FF_TC_FEE_UNDER_THIRTYK = 151;

	// 立替金振込手数料：他行30,000円以上
	const OTHER_FF_TC_FEE_THIRTYK_AND_OVER = 648;

	// 立替金振込手数料：他行30,000円以上
	const OTHER_FF_TC_FEE_UNDER_THIRTYK = 432;

	/**
	 * アダプタ
	 *
	 * @var Adapter
	 */
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
	 * 振込手数料を取得する
	 * @param $pattern 同行 or 他行
	 * @param $money 振込金額
	 * @param $oemId OEMID
	 * @return int 立替金振込手数料
	 */
	public function getTransferCommission($tcClass, $money, $oemId = null) {

        $result = 0;

        if ($money > 0)
        {
            if ($tcClass == 1)
            {
                if ($money >= 30000)
                {
                    return $this->getFfTcFee(2, $oemId, $tcClass, $money);
                }
                else
                {
                    return $this->getFfTcFee(1, $oemId, $tcClass, $money);
                }
            }
            else
            {
                if ($money >= 30000)
                {
                    return $this->getFfTcFee(4, $oemId, $tcClass, $money);
                }
                else
                {
                    return $this->getFfTcFee(3, $oemId, $tcClass, $money);
                }
            }
        }

        return $result;

	}

	/**
	 * 振込手数料を取得する
	 * @param int $number 1 or 2 or 3 or 4
	 * @param int $oemId
	 * @param int $tcClass
	 * @param int $money
	 * @return int 立替金振込手数料
	 */
	public function getFfTcFee($number, $oemId, $tcClass, $money) {

        if($oemId == null || $oemId == 0) {

            // 次期システムからはマスタから取得する(20150820_2115_suzuki_h)
            return $this->calculateTransferFee($tcClass, $money);

//             switch($number) {
//                 case 1 : return self::SAME_FF_TC_FEE_UNDER_THIRTYK;
//                 case 2 : return self::SAME_FF_TC_FEESAME_FF_TC_FEE_THIRTYK_AND_OVER;
//                 case 3 : return self::OTHER_FF_TC_FEE_UNDER_THIRTYK;
//                 case 4 : return self::OTHER_FF_TC_FEE_THIRTYK_AND_OVER;
//             }
        }
        else {
            return $this->getFfTcFeeByOemId($number, $oemId);
        }
	}

	/**
	 * 対象OEMの振込手数料を取得する
	 * @param $number 1 or 2 or 3 or 4
	 * @param $oemId
	 * @return int 立替金振込手数料
	 */
	public function getFfTcFeeByOemId($number, $oemId) {

        // 対象OEM取得
        $mdlo = new TableOem($this->_adapter);
        $oem = $mdlo->findOem($oemId)->current();

        switch($number) {
            case 1 : return $oem['SameFfTcFeeUnderThirtyK'];
            case 2 : return $oem['SameFfTcFeeThirtyKAndOver'];
            case 3 : return $oem['OtherFfTcFeeUnderThirtyK'];
            case 4 : return $oem['OtherFfTcFeeThirtyKAndOver'];
        }
	}


	/**
	 * 振込手数料の計算
	 *
	 * @param int $tcClass 同行他行区分
	 * @param int $transferAmount 振込額
	 *
	 */
	private function calculateTransferFee($tcClass, $transferAmount)
	{
        $rtnTransferFee = 0;

        $sql  = " SELECT ";
        $sql .= "       Note ";
        $sql .= " FROM  M_Code ";
        $sql .= " WHERE CodeId = 93 ";
        $sql .= " AND   Class1 = :TcClass ";
        $sql .= " AND   :TransferAmount BETWEEN Class2 AND Class3 ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TcClass' => $tcClass,
                ':TransferAmount' => $transferAmount,
        );

        $row = $stm->execute($prm)->current();

        $rtnTransferFee = isset($row) ? $row['Note'] : 0;

        return $rtnTransferFee;
	}
}


