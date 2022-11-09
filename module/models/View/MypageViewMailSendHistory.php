<?php
namespace models\View;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

class MypageViewMailSendHistory
{
	protected $_name = 'MV_MailSendHistory';
	protected $_primary = 'MailSendSeq';
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
	 * メール送信履歴データを取得する(有効フラグ＝有効データに限る)
	 *
	 * @param int $mailSendSeq メール送信履歴SEQ
	 * @return ResultInterface
	 */
	public function find($mailSendSeq)
	{
	    $sql = " SELECT * FROM MV_MailSendHistory WHERE ValidFlg = 1 AND MailSendSeq = :MailSendSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':MailSendSeq' => $mailSendSeq,
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
	    $sql  = " INSERT INTO MV_MailSendHistory (MailTemplateId, OrderSeq, EnterpriseId, ManCustId, ToAddress, CcAddress, BccAddress, Subject, Body, MailSendDate, ErrFlg, ErrReason, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
	    $sql .= "   :MailTemplateId ";
	    $sql .= " , :OrderSeq ";
	    $sql .= " , :EnterpriseId ";
	    $sql .= " , :ManCustId ";
	    $sql .= " , :ToAddress ";
	    $sql .= " , :CcAddress ";
	    $sql .= " , :BccAddress ";
	    $sql .= " , :Subject ";
	    $sql .= " , :Body ";
	    $sql .= " , :MailSendDate ";
	    $sql .= " , :ErrFlg ";
	    $sql .= " , :ErrReason ";
	    $sql .= " , :RegistDate ";
	    $sql .= " , :RegistId ";
	    $sql .= " , :UpdateDate ";
	    $sql .= " , :UpdateId ";
	    $sql .= " , :ValidFlg ";
	    $sql .= " )";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':MailTemplateId' => $data['MailTemplateId'],
	            ':OrderSeq' => $data['OrderSeq'],
	            ':EnterpriseId' => $data['EnterpriseId'],
	            ':ManCustId' => $data['ManCustId'],
	            ':ToAddress' => $data['ToAddress'],
	            ':CcAddress' => $data['CcAddress'],
	            ':BccAddress' => $data['BccAddress'],
	            ':Subject' => $data['Subject'],
	            ':Body' => $data['Body'],
	            ':MailSendDate' => $data['MailSendDate'],
	            ':ErrFlg' => isset($data['ErrFlg']) ? $data['ErrFlg'] : 0,
	            ':ErrReason' => $data['ErrReason'],
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
	 * @param int $mailSendSeq メール送信履歴SEQ
	 * @return ResultInterface
	 */
	public function saveUpdate($data, $mailSendSeq)
	{
	    $row = $this->find($mailSendSeq)->current();

	    foreach ($data as $key => $value)
	    {
	        if (array_key_exists($key, $row))
	        {
	            $row[$key] = $value;
	        }
	    }

	    $sql  = " UPDATE MV_MailSendHistory ";
	    $sql .= " SET ";
	    $sql .= "     MailTemplateId = :MailTemplateId ";
	    $sql .= " ,   OrderSeq = :OrderSeq ";
	    $sql .= " ,   EnterpriseId = :EnterpriseId ";
	    $sql .= " ,   ManCustId = :ManCustId ";
	    $sql .= " ,   ToAddress = :ToAddress ";
	    $sql .= " ,   CcAddress = :CcAddress ";
	    $sql .= " ,   BccAddress = :BccAddress ";
	    $sql .= " ,   Subject = :Subject ";
	    $sql .= " ,   Body = :Body ";
	    $sql .= " ,   MailSendDate = :MailSendDate ";
	    $sql .= " ,   ErrFlg = :ErrFlg ";
	    $sql .= " ,   ErrReason = :ErrReason ";
	    $sql .= " ,   RegistDate = :RegistDate ";
	    $sql .= " ,   RegistId = :RegistId ";
	    $sql .= " ,   UpdateDate = :UpdateDate ";
	    $sql .= " ,   UpdateId = :UpdateId ";
	    $sql .= " ,   ValidFlg = :ValidFlg ";
	    $sql .= " WHERE MailSendSeq = :MailSendSeq ";

	    $stm = $this->_adapter->query($sql);

	    $prm = array(
	            ':MailSendSeq' => $mailSendSeq,
	            ':MailTemplateId' => $row['MailTemplateId'],
	            ':OrderSeq' => $row['OrderSeq'],
	            ':EnterpriseId' => $row['EnterpriseId'],
	            ':ManCustId' => $row['ManCustId'],
	            ':ToAddress' => $row['ToAddress'],
	            ':CcAddress' => $row['CcAddress'],
	            ':BccAddress' => $row['BccAddress'],
	            ':Subject' => $row['Subject'],
	            ':Body' => $row['Body'],
	            ':MailSendDate' => $row['MailSendDate'],
	            ':ErrFlg' => $row['ErrFlg'],
	            ':ErrReason' => $row['ErrReason'],
	            ':RegistDate' => $row['RegistDate'],
	            ':RegistId' => $row['RegistId'],
	            ':UpdateDate' => date('Y-m-d H:i:s'),
	            ':UpdateId' => $row['UpdateId'],
	            ':ValidFlg' => $row['ValidFlg'],
	    );

	    return $stm->execute($prm);
	}

}
