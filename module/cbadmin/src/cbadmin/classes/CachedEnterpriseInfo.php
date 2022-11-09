<?php
namespace cbadmin\classes;

use Zend\Db\Adapter\Adapter;
use models\Table\TableEnterprise;

/**
 * 事業者情報を検索するためのシンプルな検索クラス。
 * インスタンスレベルで事業者IDをキーとするキャッシュを保持するため、
 * 繰り返し処理で事業者情報を参照する場合に使用する
 */
class CachedEnterpriseInfo {
	/**
	 * データベースアダプタ
	 * @var Adapter
	 */
	protected $_db;

	/**
	 * キャッシュ
	 * @var array
	 */
	protected $_cache;

	/**
	 * データベースアダプタを指定して、{@link CachedEnterpriseId}の
	 * 新しいインスタンスを初期化する
	 * @param Adapter $db データベースアダプタ
	 */
	public function __construct(Adapter $db) {
		$this->_cache = array();

		$this->setDbAdapter($db);
	}

	/**
	 * データベースアダプタを取得する
	 * @return Adapter
	 */
	public function getDbAdapter() {
		return $this->_db;
	}

	/**
	 * データベースアダプタを設定する
	 * @param Adapter $db データベースアダプタ
	 * @return CachedEnterpriseInfo このインスタンス自身
	 */
	public function setDbAdapter(Adapter $db) {
		$this->_db = $db;
		return $this;
	}

	/**
	 * 指定の事業者IDのデータを連想配列で取得する。
	 * 検索結果はインスタンス内にキャッシュされ、
	 * 次回以降の問合せではキャッシュ値が使用される
	 * @param int|string $enterpriseId 事業者ID
	 * @return array|null 指定IDに一致する事業者データの連想配列
	 */
	public function find($enterpriseId) {
        $key = (string)$enterpriseId;
        if( empty($key) ) return null;

        if( ! isset($this->_cache[$key]) ) {
            $table = new TableEnterprise($this->_db);
            $sql = " SELECT e.*, et.* FROM T_Enterprise e INNER JOIN T_EnterpriseTotal et ON (et.EnterpriseId = e.EnterpriseId) WHERE e.EnterpriseId = :EnterpriseId ";
            $row = $this->_db->query($sql)->execute(array(':EnterpriseId' => $enterpriseId))->current();
            if (!$row) { return null; }

            $this->_cache[$key] = $row;
        }
        return $this->_cache[$key];
	}
}

