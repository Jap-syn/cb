<?php
namespace Coral\Base\Db\Table;

require_once 'Zend/Db.php';
require_once 'Zend/Db/Table/Abstract.php';

/**
 * Zend_Db_Table_Abstractのシンプルな具象クラス
 *
 */
class BaseDbTableGeneric extends Zend_Db_Table_Abstract {
	/**
	 * コンストラクタ
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
	}
}
