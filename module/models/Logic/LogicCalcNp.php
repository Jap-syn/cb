<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableEnterprise;
use models\Table\TableEnterpriseTotal;
use models\Table\TableUser;

/**
 * 不払率算出/更新ロジック
 */
class LogicCalcNp
{
	/**
	 * 3ヶ月スパンの不払い率算出クエリを特定するキー定数
	 * @var string
	 */
	const QUERY_KEY_THREEMONTH_SPAN = 'three months';

	/**
	 * 全期間の不払い率算出クエリを特定するキー定数
	 * @var string
	 */
	const QUERY_KEY_ALL_SPAN = 'all';

	/**
	 * 平均単価算出クエリを特定するキー定数
	 * @var string
	 */
	const QUERY_KEY_AMOUNT_AVERAGE = 'amount average';

	/**
	 * 不払率算出クエリを格納する連想配列
	 * @var array
	 */
	protected static $_queries;

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
	 * 不払い率算出
	 * （2010.10.13 NG率算出処理、不払件数の保証/無保証内訳算出処理追加）
	 *
     * @throws Exception
	 */
	public function calc() {

	    $mdlet = new TableEnterpriseTotal($this->_adapter);         // 加盟店別集計
        $mdlu = new TableUser($this->_adapter);                     // ユーザー

        // 3ヶ月スパン用クエリ
        $query3 = self::getThreeMonthSpanQuery();

        // 全体スパン用クエリ
        $queryall = self::getAllSpanQuery();

        // 平均利用額算出用クエリ（2011.6.29 eda）
        $queryavg = self::getAmountAverageQuery();

        try {
            // トランザクション開始
            $this->_adapter->getDriver()->getConnection()->beginTransaction();

            // ユーザーＩＤ取得
            $opId = $mdlu->getUserId(99, 1);

            // 不払い率を0リセット
            $mdlet->resetNp($opId);

            // 3ヶ月スパンのデータ更新
            $ri = $this->_adapter->query($query3)->execute(null);
            foreach($ri as $data) {

                $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
                $ri_enterprise = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $data['EnterpriseId']));
                if (!($ri_enterprise->count() > 0)) { continue; }

                $sql  = " UPDATE T_EnterpriseTotal ";
                $sql .= " SET ";
                $sql .= "     NpCalcDate             = :NpCalcDate ";
                $sql .= " ,   NpMolecule3            = :NpMolecule3 ";
                $sql .= " ,   NpDenominator3         = :NpDenominator3 ";
                $sql .= " ,   NpRate3                = :NpRate3 ";
                $sql .= " ,   NpGuaranteeMolecule3   = :NpGuaranteeMolecule3 ";
                $sql .= " ,   NpNoGuaranteeMolecule3 = :NpNoGuaranteeMolecule3 ";
                $sql .= " ,   NpGuaranteeRate3       = :NpGuaranteeRate3 ";
                $sql .= " ,   NpNoGuaranteeRate3     = :NpNoGuaranteeRate3 ";
                $sql .= " ,   NpNgMolecule3          = :NpNgMolecule3 ";
                $sql .= " ,   NpNgDenominator3       = :NpNgDenominator3 ";
                $sql .= " ,   NpNgRate3              = :NpNgRate3 ";
                $sql .= " ,   UpdateDate             = :UpdateDate ";
                $sql .= " ,   UpdateId               = :UpdateId ";
                $sql .= " WHERE EnterpriseId         = :EnterpriseId ";

                $stm = $this->_adapter->query($sql);

                $prm = array(
                        ':NpCalcDate' => date('Y-m-d'),
                        ':NpMolecule3' => $data['ecnt'],
                        ':NpDenominator3' => $data['tcnt'],
                        ':NpRate3' => $data['rate'],
                        ':NpGuaranteeMolecule3' => $data['ecnt_h'],
                        ':NpNoGuaranteeMolecule3' => $data['ecnt_m'],
                        ':NpGuaranteeRate3' => $data['rate_h'],
                        ':NpNoGuaranteeRate3' => $data['rate_m'],
                        ':NpNgMolecule3' => $data['ncnt'],
                        ':NpNgDenominator3' => $data['tcnt_n'],
                        ':NpNgRate3' => $data['rate_n'],
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $opId,
                        ':EnterpriseId' => $data['EnterpriseId'],
                );

                $stm->execute($prm);
            }

            // 全体スパンのデータ更新
            $ri = $this->_adapter->query($queryall)->execute(null);
            foreach($ri as $data) {

                $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
                $ri_enterprise = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $data['EnterpriseId']));
                if (!($ri_enterprise->count() > 0)) { continue; }

                $sql  = " UPDATE T_EnterpriseTotal ";
                $sql .= " SET ";
                $sql .= "     NpCalcDate               = :NpCalcDate ";
                $sql .= " ,   NpMoleculeAll            = :NpMoleculeAll ";
                $sql .= " ,   NpDenominatorAll         = :NpDenominatorAll ";
                $sql .= " ,   NpRateAll                = :NpRateAll ";
                $sql .= " ,   NpGuaranteeMoleculeAll   = :NpGuaranteeMoleculeAll ";
                $sql .= " ,   NpNoGuaranteeMoleculeAll = :NpNoGuaranteeMoleculeAll ";
                $sql .= " ,   NpGuaranteeRateAll       = :NpGuaranteeRateAll ";
                $sql .= " ,   NpNoGuaranteeRateAll     = :NpNoGuaranteeRateAll ";
                $sql .= " ,   NpNgMoleculeAll          = :NpNgMoleculeAll ";
                $sql .= " ,   NpNgDenominatorAll       = :NpNgDenominatorAll ";
                $sql .= " ,   NpNgRateAll              = :NpNgRateAll ";
                $sql .= " ,   UpdateDate               = :UpdateDate ";
                $sql .= " ,   UpdateId                 = :UpdateId ";
                $sql .= " WHERE EnterpriseId           = :EnterpriseId ";

                $stm = $this->_adapter->query($sql);

                $prm = array(
                        ':NpCalcDate' => date('Y-m-d'),
                        ':NpMoleculeAll' => $data['ecnt'],
                        ':NpDenominatorAll' => $data['tcnt'],
                        ':NpRateAll' => $data['rate'],
                        ':NpGuaranteeMoleculeAll' => $data['ecnt_h'],
                        ':NpNoGuaranteeMoleculeAll' => $data['ecnt_m'],
                        ':NpGuaranteeRateAll' => $data['rate_h'],
                        ':NpNoGuaranteeRateAll' => $data['rate_m'],
                        ':NpNgMoleculeAll' => $data['ncnt'],
                        ':NpNgDenominatorAll' => $data['tcnt_n'],
                        ':NpNgRateAll' => $data['rate_n'],
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $opId,
                        ':EnterpriseId' => $data['EnterpriseId'],
                );

                $stm->execute($prm);
            }

            // 平均利用額データ更新（2011.6.29 eda）
            $ri = $this->_adapter->query($queryavg)->execute(null);
            foreach($ri as $data) {

                $sql = " SELECT * FROM T_Enterprise WHERE EnterpriseId = :EnterpriseId ";
                $ri_enterprise = $this->_adapter->query($sql)->execute(array(':EnterpriseId' => $data['EnterpriseId']));
                if (!($ri_enterprise->count() > 0)) { continue; }

                $sql  = " UPDATE T_EnterpriseTotal ";
                $sql .= " SET ";
                $sql .= "     NpCalcDate           = :NpCalcDate ";
                $sql .= " ,   NpOrderCountTotal    = :NpOrderCountTotal ";   // 総注文数
                $sql .= " ,   NpOrderCountOk       = :NpOrderCountOk ";      // OK注文数
                $sql .= " ,   NpAverageAmountTotal = :NpAverageAmountTotal ";// 利用額合計
                $sql .= " ,   NpAverageAmountOk    = :NpAverageAmountOk ";   // OK注文利用額合計
                $sql .= " ,   UpdateDate           = :UpdateDate ";          // 更新日時
                $sql .= " ,   UpdateId             = :UpdateId ";            // 更新者
                $sql .= " WHERE EnterpriseId       = :EnterpriseId ";

                $stm = $this->_adapter->query($sql);

                $prm = array(
                        ':NpCalcDate' => date('Y-m-d'),
                        ':NpOrderCountTotal' => $data['TotalCount'],
                        ':NpOrderCountOk' => $data['OkCount'],
                        ':NpAverageAmountTotal' => $data['TotalAmount'],
                        ':NpAverageAmountOk' => $data['OkAmount'],
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $opId,
                        ':EnterpriseId' => $data['EnterpriseId'],
                );

                $stm->execute($prm);
            }

            $this->_adapter->getDriver()->getConnection()->commit();

        }
        catch(\Exception $e)
        {
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $e;
        }

	}

	/**
	 * 3ヶ月スパン向けの不払い率/NG率算出クエリを取得する
	 *
	 * @static
	 * @return string
	 */
	public static function getThreeMonthSpanQuery() {
		$queries = self::getQueries();
		return $queries[self::QUERY_KEY_THREEMONTH_SPAN];
	}

	/**
	 * 全期間向けの不払い率/NG率算出クエリを取得する
	 *
	 * @static
	 * @return string
	 */
	public static function getAllSpanQuery() {
		$queries = self::getQueries();
		return $queries[self::QUERY_KEY_ALL_SPAN];
	}

	/**
	 * 平均利用額算出用クエリを取得する
	 *
	 * @static
	 * @return string
	 */
	public static function getAmountAverageQuery() {
		$queries = self::getQueries();
		return $queries[self::QUERY_KEY_AMOUNT_AVERAGE];
	}

	/**
	 * 不払率/NG率算出、および平均利用額算出クエリを格納した連想配列を取得する。
	 * 戻り値の配列はキー定数をキーとして、対応するSQLが格納されている
	 *
	 * @static
	 * @return array
	 */
	public static function getQueries() {
		if(! is_array(self::$_queries) || empty(self::$_queries)) {
			$base_query = <<<EOQ
SELECT
	e.EnterpriseId,
	CASE
		WHEN v.ecnt IS NULL THEN 0 ELSE v.ecnt
	END AS ecnt,
	CASE
		WHEN v.tcnt IS NULL THEN 0 ELSE v.tcnt
	END AS tcnt,
	CASE
		WHEN v.rate IS NULL THEN 0 ELSE v.rate
	END AS rate,
	CASE
		WHEN v.ecnt_h IS NULL THEN 0 ELSE v.ecnt_h
	END AS ecnt_h,
	CASE
		WHEN v.ecnt_m IS NULL THEN 0 ELSE v.ecnt_m
	END AS ecnt_m,
	CASE
		WHEN v.rate_h IS NULL THEN 0 ELSE v.rate_h
	END AS rate_h,
	CASE
		WHEN v.rate_m IS NULL THEN 0 ELSE v.rate_m
	END AS rate_m,
	CASE
		WHEN n.ncnt IS NULL THEN 0 ELSE n.ncnt
	END AS ncnt,
	CASE
		WHEN n.tcnt IS NULL THEN 0 ELSE n.tcnt
	END AS tcnt_n,
	CASE
		WHEN n.rate IS NULL THEN 0 ELSE n.rate
	END AS rate_n

FROM
	T_Enterprise e LEFT OUTER JOIN
	(
		SELECT
			v1.EnterpriseId,
			SUM(v1.entai_flg) AS ecnt,
			SUM(v1.total_flg) AS tcnt,
			SUM(v1.saiken_flg) AS scnt,
			TRUNCATE( (SUM(v1.entai_flg) + SUM(v1.saiken_flg)) / (SUM(v1.total_flg) + SUM(v1.saiken_flg)) * 100, 3 ) AS rate,
			(SUM(v1.entai_flg) - SUM(v1.entai_m_flg)) AS ecnt_h,
			TRUNCATE( (SUM(v1.entai_flg) - SUM(v1.entai_m_flg)) / SUM(v1.total_flg) * 100, 3 ) AS rate_h,
			SUM(v1.entai_m_flg) AS ecnt_m,
			TRUNCATE( SUM(v1.entai_m_flg) / SUM(v1.total_flg) * 100, 3 ) AS rate_m
		FROM
			(
				SELECT
					o.EnterpriseId,
					CASE
						WHEN o.Cnl_Status = 0 THEN 1 ELSE 0
					END AS total_flg,
					CASE
						WHEN (
							o.Cnl_Status = 0 AND o.DataStatus = 51
						) THEN 1
						ELSE 0
					END AS entai_flg,
					CASE
						WHEN (
							o.Cnl_Status = 0 AND o.DataStatus = 51 AND o.OutOfAmends = 1
						) THEN 1
						ELSE 0
					END AS entai_m_flg,
					CASE
						WHEN (
							o.Cnl_ReturnSaikenCancelFlg = 1 AND (o.Cnl_Status = 1 OR o.Cnl_Status = 2)
						) THEN 1
						ELSE 0
					END AS saiken_flg
				FROM
					T_Order o
				INNER JOIN T_ClaimControl cc
						ON cc.OrderSeq = o.OrderSeq
				WHERE
					cc.F_LimitDate BETWEEN ADDDATE(CURDATE(), INTERVAL {END} DAY) AND
					ADDDATE(CURDATE(), INTERVAL {BEGIN} DAY)
			) v1
		GROUP BY
			v1.EnterpriseId
	) v ON v.EnterpriseId = e.EnterpriseId
	LEFT OUTER JOIN
	(
		SELECT
			v2.EnterpriseId,
			SUM(v2.ng_flg) AS ncnt,
			SUM(v2.total_flg) AS tcnt,
			TRUNCATE( SUM(v2.ng_flg) / SUM(v2.total_flg) * 100, 3 ) AS rate
		FROM
			(
				SELECT
					EnterpriseId,
					1 AS total_flg,
					CASE
						WHEN CloseReason = 3 THEN 1 ELSE 0
					END AS ng_flg
				FROM
					T_Order
				WHERE
					DATE(RegistDate) BETWEEN ADDDATE(CURDATE(), INTERVAL {END} DAY) AND
					ADDDATE(CURDATE(), INTERVAL {BEGIN} DAY)
			) v2
		GROUP BY
			EnterpriseId
	) n ON n.EnterpriseId = v.EnterpriseId
ORDER BY
	e.EnterpriseId
EOQ;
			self::$_queries = array();
			foreach(array(
				// 3ヶ月スパン向け。期間は現在日時より60～150日前
				self::QUERY_KEY_THREEMONTH_SPAN => array('\{BEGIN\}' => -60, '\{END\}' => -150),
				// 全期間向け。期間は現在日時の前日～540日前
				self::QUERY_KEY_ALL_SPAN => array('\{BEGIN\}' => -1, '\{END\}' => -540)
			) as $key => $conf) {
				$q = $base_query;
				foreach($conf as $pattern => $replacement) {
					$q = mb_ereg_replace($pattern, $replacement, $q);
				}
				self::$_queries[$key] = $q;
			}

			// 平均利用額算出クエリを登録する（2011.6.29 eda）
			self::$_queries[self::QUERY_KEY_AMOUNT_AVERAGE] = self::_getAmountAvgQuery();
		}
		return self::$_queries;
	}

	// --------------------------------------------------------------------------------------------------------------
	//    ↓ ここから旧コード（2010.10.13 eda）
	// --------------------------------------------------------------------------------------------------------------

// Del By Takemasa(NDC) 20141226 Stt 未使用故コメントアウト化
// 	/**
// 	 * （旧）不払い率算出
// 	 *
// 	 * @return string '':成功 ''以外:失敗
// 	 */
// 	public function _old_calc()
// 	{
// 		$mdle = new Table_Enterprise($this->db);
//
// 		// 3ヶ月スパン
// 		$query3 = "
// 			SELECT
// 			    TOTAL.EnterpriseId,
// 			    CASE WHEN ENTAI.cnt IS NULL THEN 0 ELSE ENTAI.cnt END AS ecnt,
// 			    TOTAL.cnt AS tcnt,
// 			    TRUNCATE(CASE WHEN ENTAI.cnt IS NULL THEN 0 ELSE (ENTAI.cnt / TOTAL.cnt) * 10000 END, 0) AS rate,
// 			    CASE WHEN ENTAI.cnt IS NULL THEN 0 ELSE (ENTAI.cnt / TOTAL.cnt) * 100 END AS realrate
// 			FROM
// 			    (SELECT
// 			        EnterpriseId,
// 			        count(*) as cnt
// 			    FROM
// 			        T_Order
// 			    WHERE
// 			        Clm_F_LimitDate between adddate(curdate(), interval -150 day) AND
// 			        adddate(curdate(), interval -60 day) AND
// 			        Cnl_Status = 0
// 			    GROUP BY
// 			        EnterpriseId
// 			    ) TOTAL left outer join (SELECT
// 										    EnterpriseId,
// 										    count(*) as cnt
// 										FROM
// 										    T_Order
// 										WHERE
// 										    Clm_F_LimitDate between adddate(curdate(), interval -150 day) AND
// 										    adddate(curdate(), interval -60 day) AND
// 										    Cnl_Status = 0 AND
// 										    DataStatus = 51
// 										GROUP BY
// 										    EnterpriseId
// 										) ENTAI on TOTAL.EnterpriseId = ENTAI.EnterpriseId
// 			ORDER BY
// 			    TOTAL.EnterpriseId
// 		";
//
// 		// 全体スパン
// 		$queryall = "
// 			SELECT
// 			    TOTAL.EnterpriseId,
// 			    CASE WHEN ENTAI.cnt IS NULL THEN 0 ELSE ENTAI.cnt END AS ecnt,
// 			    TOTAL.cnt AS tcnt,
// 			    TRUNCATE(CASE WHEN ENTAI.cnt IS NULL THEN 0 ELSE (ENTAI.cnt / TOTAL.cnt) * 10000 END, 0) AS rate,
// 			    CASE WHEN ENTAI.cnt IS NULL THEN 0 ELSE (ENTAI.cnt / TOTAL.cnt) * 100 END AS realrate
// 			FROM
// 			    (SELECT
// 			        EnterpriseId,
// 			        count(*) as cnt
// 			    FROM
// 			        T_Order
// 			    WHERE
// 			        Clm_F_LimitDate between adddate(curdate(), interval -540 day) AND
// 			        adddate(curdate(), interval -1 day) AND
// 			        Cnl_Status = 0
// 			    GROUP BY
// 			        EnterpriseId
// 			    ) TOTAL left outer join (SELECT
// 										    EnterpriseId,
// 										    count(*) as cnt
// 										FROM
// 										    T_Order
// 										WHERE
// 										    Clm_F_LimitDate between adddate(curdate(), interval -540 day) AND
// 										    adddate(curdate(), interval -1 day) AND
// 										    Cnl_Status = 0 AND
// 										    DataStatus = 51
// 										GROUP BY
// 										    EnterpriseId
// 										) ENTAI on TOTAL.EnterpriseId = ENTAI.EnterpriseId
// 			ORDER BY
// 			    TOTAL.EnterpriseId
// 		";
//
// 		$result3 = $mdle->getAdapter()->query($query3)->fetchAll();
// 		$resultall = $mdle->getAdapter()->query($queryall)->fetchAll();
//
// 		try
// 		{
// 			// トランザクション開始
// 			$this->db->beginTransaction();
//
// 			// 不払い率を0リセット
// 			$mdle->resetNp();
//
// 			// 3ヶ月スパンのデータ更新
// 			for ($i = 0 ; count($result3) > $i ; $i++)
// 			{
// 				$udata3["NpCalcDate"] = date('Y-m-d');
// 				$udata3["NpMolecule3"] = $result3[$i]["ecnt"];
// 				$udata3["NpDenominator3"] = $result3[$i]["tcnt"];
// 				$udata3["NpRate3"] = $result3[$i]["rate"];
//
// 				$mdle->saveUpdate($udata3, $result3[$i]["EnterpriseId"]);
// 			}
//
// 			// 全体スパンのデータ更新
// 			for ($i = 0 ; count($resultall) > $i ; $i++)
// 			{
// 				$udataall["NpCalcDate"] = date('Y-m-d');
// 				$udataall["NpMoleculeAll"] = $resultall[$i]["ecnt"];
// 				$udataall["NpDenominatorAll"] = $resultall[$i]["tcnt"];
// 				$udataall["NpRateAll"] = $resultall[$i]["rate"];
//
// 				$mdle->saveUpdate($udataall, $resultall[$i]["EnterpriseId"]);
// 			}
//
// 			$this->db->commit();
//
// 			$result = "";
// 		}
// 		catch(Exception $e)
// 		{
// 			$this->db->rollBack();
// 			$result = $e->getMessage();
// 		}
//
// 		return $result;
// 	}
// Del By Takemasa(NDC) 20141226 End 未使用故コメントアウト化

	/**
	 * 平均利用額算出クエリを取得する
	 *
	 * @static
	 * @access protected
	 * @return string
	 */
	protected static function _getAmountAvgQuery() {
		// 日付スコープはリテラルで処理日前日～365日前としている
		return <<<EOQ
SELECT
	e.EnterpriseId,
	IFNULL(v.TotalCount, 0) AS TotalCount,
	IFNULL(v.TotalAmount, 0) AS TotalAmount,
	IFNULL(v.OkCount, 0) AS OkCount,
	IFNULL(v.OkAmount, 0) AS OkAmount
FROM
	T_Enterprise e LEFT OUTER JOIN
	(
		SELECT
			EnterpriseId,
			COUNT(*) AS TotalCount,
			SUM(UseAmount) AS TotalAmount,
			SUM(CASE
			 WHEN (Incre_Status IS NULL OR Incre_Status <> -1) AND Dmi_Status = 1 THEN 1
			 ELSE 0
			END) AS OkCount,
			SUM(CASE
			 WHEN (Incre_Status IS NULL OR Incre_Status <> -1) AND Dmi_Status = 1 THEN UseAmount
			 ELSE 0
			END) AS OkAmount
		FROM
			T_Order
		WHERE
			Cnl_Status = 0 AND
			DATE(RegistDate) BETWEEN ADDDATE(CURDATE(), INTERVAL -365 DAY) AND
			ADDDATE(CURDATE(), INTERVAL -1 DAY)
		GROUP BY
			EnterpriseId
	) v ON v.EnterpriseId = e.EnterpriseId
ORDER BY
	e.EnterpriseId
EOQ;
	}
}

// Del By Takemasa(NDC) 20141226 Stt 未使用故コメントアウト化
// class CalcNpException extends Exception
// {
//
// }
// Del By Takemasa(NDC) 20141226 End 未使用故コメントアウト化
