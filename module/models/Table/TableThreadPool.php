<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use models\Logic\ThreadPool\LogicThreadPoolException;

/**
 * スレッドプールテーブルのモデル
 */
class TableThreadPool
{
	// スレッド状態定数：実行開始待ち
    const STATUS_STAND_BY = 0;

	// スレッド状態定数：実行中
    const STATUS_RUNNING = 1;

	// スレッド状態定数：終了（異常終了）
    const STATUS_TERMINATED_ABNORMALLY = 8;

	// スレッド状態定数：終了（正常終了）
    const STATUS_TERMINATED_NORMALLY = 9;

	/**
	 * 定義済みスレッド状態定数を配列で取得する
	 *
	 * @static
	 * @return array
	 */
    public static function getAllStatuses() {
        return array(
            self::STATUS_STAND_BY,
            self::STATUS_RUNNING,
            self::STATUS_TERMINATED_ABNORMALLY,
            self::STATUS_TERMINATED_NORMALLY
        );
    }

	/**
	 * スレッドグループ名の正常性チェック
	 *
	 * @static
	 * @access protected
	 * @param string $groupName スレッドグループ名
	 * @return string 正常なスレッドグループ名。正常性チェックに失敗した場合は例外がスローされる
	 */
    public static function isValidGroupName($groupName) {
        $groupName = trim((string)$groupName);
        if(!strlen($groupName)) {
            throw new LogicThreadPoolException('ThreadGroup must be specified');
        }
        return $groupName;
    }

	/**
	 * テーブル名
	 *
	 * @access protected
	 * @var string
	 */
	protected $_name = 'T_ThreadPool';

	/**
	 * プライマリキー
	 *
	 * @access protected
	 * @var string
	 */
	protected $_primary = array('ThreadId');

	/**
	 * アダプタ
	 *
	 * @access protected
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
	 * 指定スレッドグループで実行開始待ちのスレッドデータをすべて取得する
	 *
	 * @param string $groupName スレッドグループ名
	 * @param null | string $order ソート順をascまたはdescで指定する。常にThreadIdに対して適用される。省略時はasc
	 * @param null | int $limit 取得する行数の制限指定。0またはそれより小さい値を指定した場合は無制限。省略時は0（＝無制限）
	 * @param null | int $offset 取得開始オフセット位置。0またはそれより小さい値を指定した場合は0と見なす。省略時は0
	 * @return ResultInterface
	 */
    public function fetchStandByItems($groupName, $order = 'asc', $limit = 0, $offset = 0) {
        return $this->fetchItemsByStatus($groupName, self::STATUS_STAND_BY, $order, $limit, $offset);
    }

    /**
     * 指定スレッドグループで実行開始待ちのスレッド件数を取得する
     *
     * @param string $groupName スレッドグループ名
     * @return int
     */
    public function countStandByItems($groupName) {
        return $this->countItemsByStatus($groupName, self::STATUS_STAND_BY);
    }

    /**
     * (obsolated) 指定スレッドグループで実行中のスレッドデータをすべて取得する。
     * ※：このメソッドは名前のtypoによりobsolated扱いになっています。fetchRunningItemsを使用してください
     *
     * @see {fetchRunningItems}
     */
    public function fetchRunngingItems($groupName, $order = 'asc', $limit = 0, $offset = 0) {
        return $this->fetchRunningItems($groupName, $order, $limit, $offset);
    }

	/**
	 * 指定スレッドグループで実行中のスレッドデータをすべて取得する
	 *
	 * @param string $groupName スレッドグループ名
	 * @param null | string $order ソート順をascまたはdescで指定する。常にThreadIdに対して適用される。省略時はasc
	 * @param null | int $limit 取得する行数の制限指定。0またはそれより小さい値を指定した場合は無制限。省略時は0（＝無制限）
	 * @param null | int $offset 取得開始オフセット位置。0またはそれより小さい値を指定した場合は0と見なす。省略時は0
	 * @return ResultInterface
	 */
    public function fetchRunningItems($groupName, $order = 'asc', $limit = 0, $offset = 0) {
        return $this->fetchItemsByStatus($groupName, self::STATUS_RUNNING, $order, $limit, $offset);
    }

    /**
     * 指定スレッドグループで実行中のスレッド件数を取得する
     *
     * @param string $groupName スレッドグループ名
     * @return int
     */
    public function countRunningItems($groupName) {
        return $this->countItemsByStatus($groupName, self::STATUS_RUNNING);
    }

	/**
	 * スレッドグループとスレッド状態を指定してスレッドデータを取得する
	 *
	 * @param string $groupName スレッドグループ名
	 * @param int $status スレッド状態
	 * @param null | string $order ソート順をascまたはdescで指定する。常にThreadIdに対して適用される。省略時はasc
	 * @param null | int $limit 取得する行数の制限指定。0またはそれより小さい値を指定した場合は無制限。省略時は0（＝無制限）
	 * @param null | int $offset 取得開始オフセット位置。0またはそれより小さい値を指定した場合は0と見なす。省略時は0
	 * @return ResultInterface
	 */
    public function fetchItemsByStatus($groupName, $status, $order = 'asc', $limit = 0, $offset = 0) {

        $groupName = self::isValidGroupName($groupName);
        if(!in_array($status, self::getAllStatuses())) {
            throw new \Exception('invalid status specified');
        }

        $sql  = " SELECT * FROM T_ThreadPool WHERE ThreadGroup = :ThreadGroup AND Status = :Status ";
        $sql .= " ORDER BY ";
        $sql .= sprintf('ThreadId %s', strtolower((string)$order) == 'desc' ? 'DESC' : 'ASC');

        $limit = (int)$limit;
        if($limit < 0) $limit = 0;
        $offset = (int)$offset;
        if($offset < 0) $offset = 0;

        if ($limit > 0) {
            $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
        }

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ThreadGroup' => $groupName,
                ':Status' => $status,
        );

        return $stm->execute($prm);
    }

    /**
     * 指定スレッドグループの指定状態のスレッド件数を取得する
     *
     * @param string $groupName スレッドグループ名
     * @param int $status スレッド状態
     * @return int
     */
    public function countItemsByStatus($groupName, $status) {
        $q = <<<EOQ
SELECT COUNT(*) AS cnt
FROM T_ThreadPool
WHERE
	ThreadGroup = :grp AND
	Status = :sts
EOQ;
        return (int)$this->_adapter->query($q)->execute(array('grp' => $groupName, 'sts' => $status))->current()['cnt'];
    }

	/**
	 * 指定スレッドグループで次に実行を開始するスレッド（＝もっとも古く登録された実行開始待ちスレッド）を取得する。
	 *
	 * @param string $groupName スレッドグループ名
	 * @return int | null
	 */
    public function findNextStartId($groupName) {
        self::isValidGroupName($groupName);
        $ri = $this->fetchStandByItems($groupName, 'asc', 1);
        if (!($ri->count() > 0)) { return null; }

        return (int)$ri->current()['ThreadId'];
    }

	/**
	 * 指定スレッドグループで指定のユーザデータを持つスレッドを取得する。オプションで完了済みスレッドを除外することもできる。
	 *
	 * @param string $groupName スレッドグループ名
	 * @param string $userData 問い合わせるユーザデータ
	 * @param null | boolean $excludeTerminated 完了済みスレッドを除外するかを指定する。省略時はtrue（＝除外する）
	 * @return ResultInterface
	 */
	public function fetchItemsByUserData($groupName, $userData, $excludeTerminated = true) {

        $groupName = self::isValidGroupName($groupName);

        $sql  = " SELECT * FROM T_ThreadPool WHERE ThreadGroup = :ThreadGroup  AND UserData = :UserData ";
        if($excludeTerminated) {
            $sql .= " AND Status IN (" . implode(",", array(self::STATUS_STAND_BY, self::STATUS_RUNNING)) . ") ";
        }
        $sql .= " ORDER BY ThreadId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ThreadGroup' => $groupName,
                ':UserData' => $userData,
        );

        return $stm->execute($prm);
	}

    /**
     * このテーブル向けの新しい行オブジェクトを作成する。
     *
     * @param string $groupName スレッドグループ名
     * @param null | string $userData スレッドアイテムに関連付ける任意データ
	 * @param $opId 担当者
     * @return プライマリキーのバリュー
     */
    public function createNewItem($groupName, $userData = '', $opId = null) {
        $groupName = self::isValidGroupName($groupName);

        // Mod By Takemasa(NDC) 20150107 Stt 関数saveNewを呼出すよう変更
        //return $this->createRow(array(
        //    'ThreadGroup' => $groupName,
        //    'UserData' => $userData
        //));
        $data = array(
            'ThreadGroup' => $groupName,
            'UserData' => $userData
        );
        if (!isset($opId)) {
            $mdlu = new TableUser($this->_adapter);
            $opId = $mdlu->getUserId(3, 99);
        }
        $data = array_merge($data, array(
            'Status' => self::STATUS_STAND_BY,
            'TerminateReason' => '',
            'CreateDate' => date('Y-m-d H:i:s'),
            'LastAccessDate' => date('Y-m-d H:i:s'),
            'RegistId' => $opId,
            'UpdateId' => $opId,
        ));
        return $this->saveNew( $data );
        // Mod By Takemasa(NDC) 20150107 End 関数saveNewを呼出すよう変更
    }

    /**
     * このテーブル向けの新しい行オブジェクトを作成する。
     *
     * @param string $groupName スレッドグループ名
     * @param null | string $userData スレッドアイテムに関連付ける任意データ
	 * @param $opId 担当者
     * @return プライマリキーのバリュー
     */
    public function createNewItemApi($groupName, $userData = '', $opId = null) {
        $groupName = self::isValidGroupName($groupName);

        // Mod By Takemasa(NDC) 20150107 Stt 関数saveNewを呼出すよう変更
        //return $this->createRow(array(
        //    'ThreadGroup' => $groupName,
        //    'UserData' => $userData
        //));
        $data = array(
            'ThreadGroup' => $groupName,
            'UserData' => $userData
        );
        if (!isset($opId)) {
            $mdlu = new TableUser($this->_adapter);
            $opId = $mdlu->getUserId(3, 99);
        }

        if(strcmp($groupName, 'api-order-rest') == 0){
            $status = self::STATUS_RUNNING;
        }else{
            $status = self::STATUS_STAND_BY;
        }
            $data = array_merge($data, array(
            'Status' => $status,
            'TerminateReason' => '',
            'CreateDate' => date('Y-m-d H:i:s'),
            'LastAccessDate' => date('Y-m-d H:i:s'),
            'RegistId' => $opId,
            'UpdateId' => $opId,
            ));

        return $this->saveNew( $data );
        // Mod By Takemasa(NDC) 20150107 End 関数saveNewを呼出すよう変更
    }

// Del By Takemasa(NDC) 20150107 Stt 廃止(使用禁止)
// 	/**
// 	 * オーバーライド。このテーブル向けの新しい行オブジェクトを生成する。
// 	 * このメソッドで生成した行データは、StatusとCreateDate、LastAccessDateが
// 	 * 初期化された状態となる
// 	 *
// 	 * @param null|array $data 初期データ。プライマリキーとStatus/CreateDate/LastAccessDateはクリアされる
// 	 * @return Zend_Db_Table_Row_Abstract 新しい行データ
// 	 */
// 	public function createRow(array $data = array()) {
// 		$data = array_merge($data, array(
//             'Status' => self::STATUS_STAND_BY,
//             'CreateDate' => date('Y-m-d H:i:s'),
//             'LastAccessDate' => date('Y-m-d H:i:s')
// 		));
// 		return parent::createRow($data);
// 	}
// Del By Takemasa(NDC) 20150107 End 廃止(使用禁止)

    /**
	 * スレッドプールデータを取得する
	 *
	 * @param int $threadId
	 * @return ResultInterface
	 */
	public function find($threadId)
	{
        $sql  = " SELECT * FROM T_ThreadPool WHERE ThreadId = :ThreadId ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ThreadId' => $threadId,
        );

        return $stm->execute($prm);
	}

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー
	 */
	public function saveNew($data)
	{
        $sql  = " INSERT INTO T_ThreadPool (ThreadGroup, CreateDate, LastAccessDate, Status, UserData, TerminateReason, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :ThreadGroup ";
        $sql .= " , :CreateDate ";
        $sql .= " , :LastAccessDate ";
        $sql .= " , :Status ";
        $sql .= " , :UserData ";
        $sql .= " , :TerminateReason ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':ThreadGroup' => $data['ThreadGroup'],
                ':CreateDate' => $data['CreateDate'],
                ':LastAccessDate' => $data['LastAccessDate'],
                ':Status' => isset($data['Status']) ? $data['Status'] : 0,
                ':UserData' => $data['UserData'],
                ':TerminateReason' => $data['TerminateReason'],
                ':RegistDate' => date('Y-m-d H:i:s'),
                ':RegistId' => $data['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $data['UpdateId'],
                ':ValidFlg' => isset($data['ValidFlg']) ? $data['ValidFlg'] : 1,
        );

        $ri = $stm->execute($prm);

        return $ri->getGeneratedValue();// 新規登録したPK値を戻す
	}

}
