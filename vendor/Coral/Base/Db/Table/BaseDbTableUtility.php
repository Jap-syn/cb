<?php
namespace Coral\Base\Db\Table;

require_once 'Zend/Db/Table.php';

/**
 * Zend_Db_Table関連のユーティリティ
 *
 * TODO: prepareDataArrayのバグ対応
 */
class BaseDbTableUtility {
	/**
	 * テーブルへ設定するデータ配列を、設定先のテーブルのメタデータで有効な列に対応するもののみに
	 * 整形する
	 *
	 * @param Zend_Db_Table_Abstract $table 設定先のテーブルオブジェクト
	 * @param array $array データ配列
	 * @return array
	 */
	public static function prepareDataArray(Zend_Db_Table_Abstract $table, array $array) {
		$metadata = $table->info();
		
		$cols = $metadata['cols'];
		
		$result = array();
		foreach($array as $key => $value) {
			if( in_array( $key, $cosl ) ) {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}
}
