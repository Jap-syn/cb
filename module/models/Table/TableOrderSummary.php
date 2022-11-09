<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\LogicNormalizer;
use Coral\Base\BaseGeneralUtils;

/**
 * T_OrderSummaryテーブルへのアダプタ
 */
class TableOrderSummary
{
	/**
	 * 氏名・氏名かなの検索データを作成するための不要文字抽出用正規表現
	 *
	 * @var string
	 */
	const REGEXP_TRIM_NAME = '[ 　\r\n\t\v]';

	/**
	 * 電話番号の検索データを作成するための不要文字抽出用正規表現
	 *
	 * @var string
	 */
	const REGEXP_TRIM_PHONE = '[^0-9]';

	/**
	 * @static
	 * @private
	 *
	 * サマリデータを問い合わせるベースクエリ
	 *
	 * @var string
	 */
	private static $__base_query;

	/**
	 * 与信データを正規化するためのフィルター
	 */
    private $map = array(
           '1' => LogicNormalizer::FILTER_FOR_ADDRESS,
           '2' => LogicNormalizer::FILTER_FOR_NAME,
           '3' => LogicNormalizer::FILTER_FOR_ITEM_NAME,
           '4' => LogicNormalizer::FILTER_FOR_MAIL,
           '5' => LogicNormalizer::FILTER_FOR_ID,
         //'6' => LogicNormalizer::FILTER_FOR_ADDRESS,
         //'7' => LogicNormalizer::FILTER_FOR_ID,
           '8' => LogicNormalizer::FILTER_FOR_TEL,
           '9' => LogicNormalizer::FILTER_FOR_MONEY,
    );

	/**
	 * @static
	 *
	 * サマリデータを問い合わせるベースクエリを取得する。
	 * クエリは元になるOrderSeqを指定する位置パラメータを含んでいる。
	 *
	 * @return string サマリデータを問い合わせるクエリ
	 */
	public static function getBaseQuery() {
		if( empty( self::$__base_query ) ) {
			// 2010.9.29 類似住所検索のパフォーマンス改善のためにT_Customerの結合を追加
			self::$__base_query = <<<BASE_QUERY_TEMPLATE
SELECT
	O.OrderSeq,
	IFNULL(
		(
			SELECT SumMoney
			FROM T_OrderItems
			WHERE OrderSeq = O.OrderSeq AND DataClass = 2
			LIMIT 1
		), 0
	) AS CarriageFee,
	IFNULL(
		(
			SELECT SumMoney
			FROM T_OrderItems
			WHERE OrderSeq = O.OrderSeq AND DataClass = 3
			LIMIT 1
		), 0
	) AS ChargeFee,
	D.DestNameKj AS DestNameKj,
	D.DestNameKn AS DestNameKn,
	D.PostalCode AS DestPostalCode,
	D.UnitingAddress AS DestUnitingAddress,
	D.Phone AS DestPhone,
	D.RegDestNameKj AS RegDestNameKj,
	D.RegUnitingAddress AS RegDestUnitingAddress,
	D.RegPhone AS RegDestPhone,
	I.OrderItemId,
	(
		SELECT GROUP_CONCAT( DISTINCT ItemNameKj SEPARATOR '\n' )
		FROM T_OrderItems
		WHERE OrderSeq = O.OrderSeq AND DataClass = 1 AND ValidFlg = 1
		ORDER BY OrderItemId
		LIMIT 1
	) AS OrderItemNames,
	(
		SELECT COUNT(*)
		FROM T_OrderItems
		WHERE OrderSeq = O.OrderSeq AND DataClass = 1 AND ValidFlg = 1
		LIMIT 1
	) AS ItemCount,
	I.ItemNameKj,
	I.Deli_JournalIncDate,
	I.Deli_DeliveryMethod,
	(
		SELECT DISTINCT DeliMethodName
		FROM M_DeliveryMethod
		WHERE DeliMethodId = I.Deli_DeliveryMethod
		LIMIT 1
	) AS Deli_DeliveryMethodName,
	I.Deli_JournalNumber,
	I.Deli_JournalNumber,
	C.NameKj,
	C.NameKn,
	C.PostalCode,
	C.UnitingAddress,
	C.Phone,
	C.MailAddress,
	C.RegNameKj,
	C.RegUnitingAddress,
	C.RegPhone,
	O.OemId
FROM
	T_Order O,
	T_OrderItems I INNER JOIN
	T_DeliveryDestination D ON D.DeliDestId = I.DeliDestId
	INNER JOIN
	T_Customer C ON C.OrderSeq = I.OrderSeq
WHERE
	I.OrderSeq = O.OrderSeq AND
	I.OrderItemId = (
		SELECT MIN( OrderItemId )
		FROM T_OrderItems
		WHERE OrderSeq = O.OrderSeq
		AND ValidFlg = 1
		AND DataClass = 1
	) AND
	O.OrderSeq = ?
;
BASE_QUERY_TEMPLATE;
		}
		return self::$__base_query;
	}

	/**
	 * @protected
	 *
	 * テーブル名
	 *
	 * @var string
	 */
	protected $_name = 'T_OrderSummary';

	/**
	 * @protected
	 *
	 * プライマリキー名
	 *
	 * @var string
	 */
	protected $_primary = array('SummaryId');

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
	 * 指定の注文シーケンスのサマリデータをこのテーブルから取得する。
	 * 指定のシーケンスに対応するサマリが作成されていない場合、この
	 * メソッドはnullを返す
	 *
	 * @param int $seq 注文シーケンス
	 * @return ResultInterface
	 */
	public function findByOrderSeq($seq)
	{
        $sql = " SELECT * FROM T_OrderSummary WHERE OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $seq,
        );

        return $stm->execute($prm);
	}

	/**
	 * 指定の注文シーケンスのサマリデータを取得する。
	 * このメソッドの戻り値は常に最新の問い合わせ結果を返す。
	 *
	 * @param int $seq 注文シーケンス
	 * @return ResultInterface $seqの注文のサマリデータ
	 */
	public function getSummary($seq)
	{
	    $stmt = $this->_adapter->query(self::getBaseQuery());
        return $stmt->execute( array($seq) );
	}

	/**
	 * 指定の注文シーケンス（またはシーケンス群）のサマリデータを更新する。
	 * このメソッドの実行により、対象の注文のサマリデータは常に最新状態に更新される。
	 *
	 * @param int $seq 注文シーケンス番号
	 * @param int $userId ユーザID
	 * @return int 追加・更新されたサマリデータのSummaryId
	 */
	public function updateSummary($seq, $userId)
	{
        // 購入者の検索用氏名・電話番号を更新
        $this->updateSearchCustomerColumns($seq, $userId);
        // 配送先の検索用氏名・電話番号を更新
        $this->updateSearchDeliColumns($seq, $userId);

        $summaryId = -1;
        $row = array();
        $ri = $this->findByOrderSeq( $seq );
        if ($ri->count() > 0) {
            $row = $ri->current();
            $summaryId = $row['SummaryId'];
        }
        else {
            $summaryId = $this->saveNew(array('RegistId' => $userId, 'UpdateId' => $userId,));
            $row = $this->find($summaryId)->current();
        }

        $data = $this->getSummary( $seq )->current();
        if($data) {
            foreach( $data as $key => $val ) {
                $row[$key] = $val;
            }
        }

        // UPDATE
        $row['UpdateId'] = $userId;
        return $this->saveUpdate($row, $summaryId);
	}

    /**
     *
     * @param int $seq 注文シーケンス番号
	 * @param int $userId ユーザID
     */
	public function updateSearchCustomerColumns($seq, $userId)
	{
	    $result = array();

        $sql = " SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ";
        $ri =$this->_adapter->query($sql)->execute( array( ':OrderSeq' => $seq ) );
        foreach( $ri as $order_row ) {

            $sql = " SELECT * FROM T_Customer WHERE OrderSeq = :OrderSeq ";
            $ri_customer = $this->_adapter->query($sql)->execute( array( ':OrderSeq' => $order_row['OrderSeq'] ) );
            if( $ri_customer->count() > 0 ) {
                $customer = $ri_customer->current();

                $searchNameKj = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen($customer['NameKj']) ? $customer['NameKj'] : '' );
                $searchNameKn = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen($customer['NameKn']) ? $customer['NameKn'] : '' );
                $phone = BaseGeneralUtils::convertWideToNarrow( strlen($customer['Phone']) ? $customer['Phone'] : '' );
                $searchPhone = mb_ereg_replace( self::REGEXP_TRIM_PHONE, '', $phone );
                $searchUnitingAddress = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen($customer['UnitingAddress']) ? $customer['UnitingAddress'] : '' );
                $regNameKj = LogicNormalizer::create($this->map['2'])->normalize($customer['NameKj']);
                $regUnitingAddress = LogicNormalizer::create($this->map['1'])->normalize($customer['UnitingAddress']);
                $regPhone = LogicNormalizer::create($this->map['8'])->normalize($customer['Phone']);

                $sql  = " UPDATE T_Customer ";
                $sql .= " SET ";
                $sql .= "     SearchNameKj         = :SearchNameKj ";
                $sql .= " ,   SearchNameKn         = :SearchNameKn ";
                $sql .= " ,   SearchPhone          = :SearchPhone ";
                $sql .= " ,   SearchUnitingAddress = :SearchUnitingAddress ";
                $sql .= " ,   RegNameKj            = :RegNameKj ";
                $sql .= " ,   RegUnitingAddress    = :RegUnitingAddress ";
                $sql .= " ,   RegPhone             = :RegPhone ";
                $sql .= " ,   UpdateDate           = :UpdateDate ";
                $sql .= " ,   UpdateId             = :UpdateId ";
                $sql .= " WHERE CustomerId         = :CustomerId ";

                $stm = $this->_adapter->query($sql);

                $prm = array(
                        ':SearchNameKj' => $searchNameKj,
                        ':SearchNameKn' => $searchNameKn,
                        ':SearchPhone' => $searchPhone,
                        ':SearchUnitingAddress' => $searchUnitingAddress,
                        ':RegNameKj' => $regNameKj,
                        ':RegUnitingAddress' => $regUnitingAddress,
                        ':RegPhone' => $regPhone,
                        ':CustomerId' => $customer['CustomerId'],
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $userId,
                );

                $stm->execute($prm);
            }
        }
	}

    /**
     *
     * @param int $seq 注文シーケンス番号
	 * @param int $userId ユーザID
     */
	public function updateSearchDeliColumns($seq, $userId)
	{
	    $result = array();

        $sql = " SELECT * FROM T_Order WHERE OrderSeq = :OrderSeq ";
        $ri =$this->_adapter->query($sql)->execute( array( ':OrderSeq' => $seq ) );
        foreach( $ri as $order_row ) {

            $sql = " SELECT * FROM T_OrderItems WHERE OrderSeq = :OrderSeq AND ValidFlg = 1 ";
            $ri_orderitems = $this->_adapter->query($sql)->execute( array( ':OrderSeq' => $order_row['OrderSeq'] ) );
            foreach( $ri_orderitems as $orderItem ) {

                $sql = " SELECT * FROM T_DeliveryDestination WHERE DeliDestId = :DeliDestId AND ValidFlg = 1 ";
                $ri_deli = $this->_adapter->query($sql)->execute( array( ':DeliDestId' => $orderItem['DeliDestId'] ) );
                if( $ri_deli->count() > 0 ) {
                    $deli = $ri_deli->current();

                    $searchDestNameKj = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen($deli['DestNameKj']) ? $deli['DestNameKj'] : '' );
                    $searchDestNameKn = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen($deli['DestNameKn']) ? $deli['DestNameKn'] : '' );
                    $phone = BaseGeneralUtils::convertWideToNarrow( strlen($deli['Phone']) ? $deli['Phone'] : '' );
                    $searchPhone = mb_ereg_replace( self::REGEXP_TRIM_PHONE, '', $phone );
                    $searchUnitingAddress = mb_ereg_replace( self::REGEXP_TRIM_NAME, '', strlen($deli['UnitingAddress']) ? $deli['UnitingAddress'] : '' );
                    $regDestNameKj = LogicNormalizer::create($this->map['2'])->normalize($deli['DestNameKj']);
                    $regUnitingAddress = LogicNormalizer::create($this->map['1'])->normalize($deli['UnitingAddress']);
                    $regPhone = LogicNormalizer::create($this->map['8'])->normalize($deli['Phone']);

                    $sql  = " UPDATE T_DeliveryDestination ";
                    $sql .= " SET ";
                    $sql .= "     SearchDestNameKj     = :SearchDestNameKj ";
                    $sql .= " ,   SearchDestNameKn     = :SearchDestNameKn ";
                    $sql .= " ,   SearchPhone          = :SearchPhone ";
                    $sql .= " ,   SearchUnitingAddress = :SearchUnitingAddress ";
                    $sql .= " ,   RegDestNameKj        = :RegDestNameKj ";
                    $sql .= " ,   RegUnitingAddress    = :RegUnitingAddress ";
                    $sql .= " ,   RegPhone             = :RegPhone ";
                    $sql .= " ,   UpdateDate           = :UpdateDate ";
                    $sql .= " ,   UpdateId             = :UpdateId ";
                    $sql .= " WHERE DeliDestId         = :DeliDestId ";

                    $stm = $this->_adapter->query($sql);

                    $prm = array(
                            ':SearchDestNameKj' => $searchDestNameKj,
                            ':SearchDestNameKn' => $searchDestNameKn,
                            ':SearchPhone' => $searchPhone,
                            ':SearchUnitingAddress' => $searchUnitingAddress,
                            ':RegDestNameKj' => $regDestNameKj,
                            ':RegUnitingAddress' => $regUnitingAddress,
                            ':RegPhone' => $regPhone,
                            ':DeliDestId' => $deli['DeliDestId'],
                            ':UpdateDate' => date('Y-m-d H:i:s'),
                            ':UpdateId' => $userId,
                    );

                    $stm->execute($prm);
                }
            }
        }
	}

	/**
	 * コンバート時のデータ取得に利用する。RegNameKjがNULLの一覧を返す
	 */
	public function getRegDestNameKjIsNull($limit = 1)
	{
		throw new \Exception('this method call is invalid');
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_OrderSummary (OrderSeq, CarriageFee, ChargeFee, DestNameKj, DestNameKn, DestPostalCode, DestUnitingAddress, DestPhone, OrderItemId, OrderItemNames, ItemCount, ItemNameKj, Deli_JournalIncDate, Deli_DeliveryMethod, Deli_DeliveryMethodName, Deli_JournalNumber, NameKj, NameKn, PostalCode, UnitingAddress, Phone, MailAddress, RegDestNameKj, RegDestUnitingAddress, RegDestPhone, RegNameKj, RegUnitingAddress, RegPhone, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :OrderSeq ";
        $sql .= " , :CarriageFee ";
        $sql .= " , :ChargeFee ";
        $sql .= " , :DestNameKj ";
        $sql .= " , :DestNameKn ";
        $sql .= " , :DestPostalCode ";
        $sql .= " , :DestUnitingAddress ";
        $sql .= " , :DestPhone ";
        $sql .= " , :OrderItemId ";
        $sql .= " , :OrderItemNames ";
        $sql .= " , :ItemCount ";
        $sql .= " , :ItemNameKj ";
        $sql .= " , :Deli_JournalIncDate ";
        $sql .= " , :Deli_DeliveryMethod ";
        $sql .= " , :Deli_DeliveryMethodName ";
        $sql .= " , :Deli_JournalNumber ";
        $sql .= " , :NameKj ";
        $sql .= " , :NameKn ";
        $sql .= " , :PostalCode ";
        $sql .= " , :UnitingAddress ";
        $sql .= " , :Phone ";
        $sql .= " , :MailAddress ";
        $sql .= " , :RegDestNameKj ";
        $sql .= " , :RegDestUnitingAddress ";
        $sql .= " , :RegDestPhone ";
        $sql .= " , :RegNameKj ";
        $sql .= " , :RegUnitingAddress ";
        $sql .= " , :RegPhone ";
        $sql .= " , :OemId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $data['OrderSeq'],
                ':CarriageFee' => $data['CarriageFee'],
                ':ChargeFee' => $data['ChargeFee'],
                ':DestNameKj' => $data['DestNameKj'],
                ':DestNameKn' => $data['DestNameKn'],
                ':DestPostalCode' => $data['DestPostalCode'],
                ':DestUnitingAddress' => $data['DestUnitingAddress'],
                ':DestPhone' => $data['DestPhone'],
                ':OrderItemId' => $data['OrderItemId'],
                ':OrderItemNames' => $data['OrderItemNames'],
                ':ItemCount' => $data['ItemCount'],
                ':ItemNameKj' => $data['ItemNameKj'],
                ':Deli_JournalIncDate' => $data['Deli_JournalIncDate'],
                ':Deli_DeliveryMethod' => $data['Deli_DeliveryMethod'],
                ':Deli_DeliveryMethodName' => $data['Deli_DeliveryMethodName'],
                ':Deli_JournalNumber' => $data['Deli_JournalNumber'],
                ':NameKj' => $data['NameKj'],
                ':NameKn' => $data['NameKn'],
                ':PostalCode' => $data['PostalCode'],
                ':UnitingAddress' => $data['UnitingAddress'],
                ':Phone' => $data['Phone'],
                ':MailAddress' => $data['MailAddress'],
                ':RegDestNameKj' => $data['RegDestNameKj'],
                ':RegDestUnitingAddress' => $data['RegDestUnitingAddress'],
                ':RegDestPhone' => $data['RegDestPhone'],
                ':RegNameKj' => $data['RegNameKj'],
                ':RegUnitingAddress' => $data['RegUnitingAddress'],
                ':RegPhone' => $data['RegPhone'],
                ':OemId' => $data['OemId'],
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
     * 指定されたレコードを更新する。
     *
     * @param array $data 更新内容
     * @param int $seq 更新するSeq
	 * @return プライマリキーのバリュー
     */
    public function saveUpdate($data, $seq)
    {
        $sql = " SELECT * FROM T_OrderSummary WHERE SummaryId = :SummaryId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SummaryId' => $seq,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_OrderSummary ";
        $sql .= " SET ";
        $sql .= "     OrderSeq = :OrderSeq ";
        $sql .= " ,   CarriageFee = :CarriageFee ";
        $sql .= " ,   ChargeFee = :ChargeFee ";
        $sql .= " ,   DestNameKj = :DestNameKj ";
        $sql .= " ,   DestNameKn = :DestNameKn ";
        $sql .= " ,   DestPostalCode = :DestPostalCode ";
        $sql .= " ,   DestUnitingAddress = :DestUnitingAddress ";
        $sql .= " ,   DestPhone = :DestPhone ";
        $sql .= " ,   OrderItemId = :OrderItemId ";
        $sql .= " ,   OrderItemNames = :OrderItemNames ";
        $sql .= " ,   ItemCount = :ItemCount ";
        $sql .= " ,   ItemNameKj = :ItemNameKj ";
        $sql .= " ,   Deli_JournalIncDate = :Deli_JournalIncDate ";
        $sql .= " ,   Deli_DeliveryMethod = :Deli_DeliveryMethod ";
        $sql .= " ,   Deli_DeliveryMethodName = :Deli_DeliveryMethodName ";
        $sql .= " ,   Deli_JournalNumber = :Deli_JournalNumber ";
        $sql .= " ,   NameKj = :NameKj ";
        $sql .= " ,   NameKn = :NameKn ";
        $sql .= " ,   PostalCode = :PostalCode ";
        $sql .= " ,   UnitingAddress = :UnitingAddress ";
        $sql .= " ,   Phone = :Phone ";
        $sql .= " ,   MailAddress = :MailAddress ";
        $sql .= " ,   RegDestNameKj = :RegDestNameKj ";
        $sql .= " ,   RegDestUnitingAddress = :RegDestUnitingAddress ";
        $sql .= " ,   RegDestPhone = :RegDestPhone ";
        $sql .= " ,   RegNameKj = :RegNameKj ";
        $sql .= " ,   RegUnitingAddress = :RegUnitingAddress ";
        $sql .= " ,   RegPhone = :RegPhone ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE SummaryId = :SummaryId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SummaryId' => $seq,
                ':OrderSeq' => $row['OrderSeq'],
                ':CarriageFee' => $row['CarriageFee'],
                ':ChargeFee' => $row['ChargeFee'],
                ':DestNameKj' => $row['DestNameKj'],
                ':DestNameKn' => $row['DestNameKn'],
                ':DestPostalCode' => $row['DestPostalCode'],
                ':DestUnitingAddress' => $row['DestUnitingAddress'],
                ':DestPhone' => $row['DestPhone'],
                ':OrderItemId' => $row['OrderItemId'],
                ':OrderItemNames' => $row['OrderItemNames'],
                ':ItemCount' => $row['ItemCount'],
                ':ItemNameKj' => $row['ItemNameKj'],
                ':Deli_JournalIncDate' => $row['Deli_JournalIncDate'],
                ':Deli_DeliveryMethod' => $row['Deli_DeliveryMethod'],
                ':Deli_DeliveryMethodName' => $row['Deli_DeliveryMethodName'],
                ':Deli_JournalNumber' => $row['Deli_JournalNumber'],
                ':NameKj' => $row['NameKj'],
                ':NameKn' => $row['NameKn'],
                ':PostalCode' => $row['PostalCode'],
                ':UnitingAddress' => $row['UnitingAddress'],
                ':Phone' => $row['Phone'],
                ':MailAddress' => $row['MailAddress'],
                ':RegDestNameKj' => $row['RegDestNameKj'],
                ':RegDestUnitingAddress' => $row['RegDestUnitingAddress'],
                ':RegDestPhone' => $row['RegDestPhone'],
                ':RegNameKj' => $row['RegNameKj'],
                ':RegUnitingAddress' => $row['RegUnitingAddress'],
                ':RegPhone' => $row['RegPhone'],
                ':OemId' => $row['OemId'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * データを取得する
     *
     * @param int $seq
     * @return ResultInterface
     */
    public function find($seq)
    {
        $sql = " SELECT * FROM T_OrderSummary WHERE SummaryId = :SummaryId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':SummaryId' => $seq,
        );

        return $stm->execute($prm);
    }

    /**
     * 配送方法・伝票番号の更新
     *
     * @param int $orderSeq 注文Seq
     * @param int $deliveryMethod 配送方法
     * @param string $journalNumber 伝票番号
     * @return ResultInterface
     */
    public function updateJournal($orderSeq, $deliveryMethod, $journalNumber)
    {
        $sql = " UPDATE T_OrderSummary SET Deli_DeliveryMethod = :Deli_DeliveryMethod, Deli_JournalNumber = :Deli_JournalNumber WHERE OrderSeq = :OrderSeq ";
        return $this->_adapter->query($sql)->execute(array('Deli_DeliveryMethod' => $deliveryMethod, 'Deli_JournalNumber' => $journalNumber, 'OrderSeq' => $orderSeq));
    }
}

