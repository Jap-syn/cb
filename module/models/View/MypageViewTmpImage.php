<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewTmpImage
{
	protected $_name = 'MV_TmpImage';
	protected $_primary = 'Seq';
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
	 * OEM画像一時保存データを取得する
	 *
	 * @param int $seq
	 * @return ResultInterface
	 */
	public function find($seq)
	{
	    $sql  = " SELECT * FROM MV_TmpImage WHERE Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
	    );

	    return $stm->execute($prm);
	}

	/**
	 * 指定のOEM一時画像ファイル取得
	 *
	 * @param int $seq シーケンス番号
	 * @return ResultInterface
	 */
	public function findTmpImage($seq)
	{
	    $sql = " SELECT * FROM MV_TmpImage WHERE ValidFlg = 1 AND Seq = :Seq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':Seq' => $seq,
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
	    $sql  = " INSERT INTO MV_TmpImage (OemId, UseType, FileName, ImageData, ImageType, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
	    $sql .= "   :OemId ";
	    $sql .= " , :UseType ";
	    $sql .= " , :FileName ";
	    $sql .= " , :ImageData ";
	    $sql .= " , :ImageType ";
	    $sql .= " , :RegistDate ";
	    $sql .= " , :RegistId ";
	    $sql .= " , :UpdateDate ";
	    $sql .= " , :UpdateId ";
	    $sql .= " , :ValidFlg ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':OemId' => $data['OemId'],
	            ':UseType' => $data['UseType'],
	            ':FileName' => $data['FileName'],
	            ':ImageData' => $data['ImageData'],
	            ':ImageType' => $data['ImageType'],
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
