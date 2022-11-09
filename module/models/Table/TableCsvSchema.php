<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Coral\Base\Reflection\BaseReflectionUtility;

class TableCsvSchema
{
	protected $_name = 'T_CsvSchema';

	protected $_primary = array(
		'EnterpriseId',
		'CsvClass',
		'Ordinal'
	);

	protected $_sequence = false;

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
	 * 事業者IDとCSV種別を指定してCSVカラム定義データを取得する
	 *
	 * @param int $entId 事業者ID。T_Enterprise.EnterpriseIdに一致する
	 * @param int $class CSV種別
	 * @param bool $strict スキーマを厳密に取得するかのフラグ。
	 *                      falseを指定した場合、指定事業者が指定スキーマを未定義の場合はデフォルトスキーマを返す。
	 *                      trueを指定している場合は例外がスローされる。省略時はfalse。
	 * @return ResultInterface
	 */
	public function getSchema($entId, $class, $strict = false)
	{
        $sql = " SELECT * FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass ORDER BY Ordinal ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':CsvClass' => $class,
        );

        $ri = $stm->execute($prm);

        if( $entId != 0 && $ri->count() == 0 ) {
            if( ! $strict ) {
                return $this->getSchema( 0, $class );
            }
            else {
                throw new \Exception("schema '$class' cannot found on eid:'$entId'.");
            }

        }
        return $ri;
	}

	/**
	 * 指定のCSVクラスが定義されているかを判断する
	 *
	 * @param int $class CSV種別
	 * @return bool
	 */
	public function isDefined($class)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_CsvSchema WHERE EnterpriseId = 0 AND CsvClass = :CsvClass ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':CsvClass' => $class,
        );

        return ((int)$stm->execute($prm)->current()['cnt'] > 0) ? true : false;
	}

	/**
	 * 指定の事業者が指定のCSVクラスを独自定義しているかを判断する
	 *
	 * @param int $entId 事業者ID
	 * @param int $class CSV種別
	 * @return bool $entIdが示す事業者が$classのCSV種別スキーマを独自定義している場合はtrue、それ以外はfalse
	 */
	public function hasDelivedSchema($entId, $class)
	{
        $sql = " SELECT COUNT(1) AS cnt FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':CsvClass' => $class,
        );

        return ((int)$stm->execute($prm)->current()['cnt'] > 0) ? true : false;
	}

	/**
	 * 指定のCSVクラスのクローンスキーマを指定事業者向けに作成する
	 *
	 * @param int $entId 事業者ID
	 * @param int $class CSV種別
	 * @return TableCsvSchema
	 */
	public function createSchema($entId, $class)
	{
        if( ! $this->isDefined($class) ) {
            throw new \Exception("unexpected CsvClass -> '$class'.");
        }

        if( ! $this->hasDelivedSchema( $entId, $class ) ) {

            $sql  = " INSERT INTO T_CsvSchema (EnterpriseId, CsvClass, Ordinal, TableName, ColumnName, PrimaryFlg, ValidationRegex, Caption, ApplicationData) ";
            $sql .= " (";
            $sql .= " SELECT :EnterpriseId AS EnterpriseId, CsvClass, Ordinal, TableName, ColumnName, PrimaryFlg, ValidationRegex, Caption, ApplicationData FROM T_CsvSchema T WHERE EnterpriseId = 0 AND CsvClass = :CsvClass ";
            $sql .= " )";

            $stm = $this->_adapter->query($sql);

            $prm = array(
                    ':EnterpriseId' => $entId,
                    ':CsvClass' => $class,
            );

            $stm->execute($prm);
        }

        return this;
	}

	public function getMaxOrdinal($entId, $class)
	{
        if( ! $this->isDefined($class) ) {
            throw new \Exception("unexpected CsvClass -> '$class'.");
        }

        $sql = " SELECT MAX(Ordinal) as max_value FROM T_CsvSchema WHERE EnterpriseId = :EnterpriseId AND CsvClass = :CsvClass ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':EnterpriseId' => $entId,
                ':CsvClass' => $class,
        );

        $ri = $stm->execute($prm);

        foreach ($ri as $row) {
            $val = $row['max_value'];
            // 正の整数以外は無視
            if( ! BaseReflectionUtility::isPositiveInteger($val) ) continue;
            return (int)$val;
        }

        // データが取得できなかった場合は例外
        throw new \Exception("cannot get max-value. EnterpriseId = '$entId', CsvClass = '$class'.");
	}
}
