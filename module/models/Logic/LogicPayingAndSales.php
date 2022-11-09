<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableUser;
use models\Table\TableSystemProperty;
use models\Table\ATablePayingAndSales;
use models\Table\TablePayingAndSales;
use models\Table\TableBatchLock;
use models\Table\TablePrePayingAndSales;


/**
 * 立替売上管理クラス
 */
class LogicPayingAndSales
{
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
	public function __construct($adapter)
	{
	    $this->_adapter = $adapter;
	}

	/**
	 * 立替・売上管理テーブル本登録バッチ
	 * 立替精算仮締め処理実施中は文字列を返す
	 * エラー時は文字列の配列を返す
	 */
    public function registpayingandsales(){
        $mdlu = new TableUser($this->_adapter);
        $mdllock = new TableBatchLock($this->_adapter);
        $mdlppas = new TablePrePayingAndSales($this->_adapter);
        $mdlpas = new TablePayingAndSales($this->_adapter);
        $mdl_atpas = new ATablePayingAndSales($this->_adapter);

        $message = null;
        try {
            $userId = $mdlu->getUserId(99, 1);

            //排他制御の確認
            $lock = $mdllock->findId(4, 1)->current()['BatchLock'];
            if($lock > 0) return "立替精算仮締め処理実施中のためスキップしました";

            //対象注文の抽出
            $ri = $mdlppas->getRegistOrders();
            $orders = ResultInterfaceToArray($ri);

            foreach($orders as $order)
            {
                $this->_adapter->getDriver()->getConnection()->beginTransaction();

                try{
                    $saveData = array_merge($order, array('RegistId' => $userId, 'UpdateId' => $userId));

                    //立替･売上管理テーブル
                    $seq = $mdlpas->saveNew($saveData);
                    $mdl_atpas->saveNew(array('Seq' => $seq));

                    //立替･売上管理_会計テーブル
                    $mdlppas->updateRegisted($order['Seq']);

                    $this->_adapter->getDriver()->getConnection()->commit();

                }catch(\Exception $e){
                    $this->_adapter->getDriver()->getConnection()->rollback();
                    $message[] = $e->getMessage()." registpayingandsales.php error OrderSeq:".$order['OrderSeq'];
                    continue;
                }
            }

        } catch (\Exception $e) {
            throw $e;
        }
        return $message;
	}

}
