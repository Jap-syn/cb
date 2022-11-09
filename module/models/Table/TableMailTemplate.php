<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * T_MailTemplateテーブルへのアダプタ
 */
class TableMailTemplate
{
	protected $_name = 'T_MailTemplate';
	protected $_primary = array('Id');
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
	 * すべてのテンプレートデータを取得する
	 *
	 * @return ResultInterface
	 */
	public function getAll()
	{
	    $sql = " SELECT * FROM T_MailTemplate ORDER BY Class ";
        return $this->_adapter->query($sql)->execute(null);
	}

	/**
	 * 指定テンプレートクラスのテンプレートデータを取得する。
	 *
	 * @param int $class クラス
	 * @return ResultInterface
	 */
	public function findMailTemplate($class, $oemId = 0)
	{
        if ($oemId == 0) {
            $sql = " SELECT * FROM T_MailTemplate WHERE (OemId = 0 OR OemId IS NULL) AND Class = :Class ";
            $prm = array(
                    ':Class' => $class,
            );
        }
        else {
            $sql = " SELECT * FROM T_MailTemplate WHERE Class = :Class AND OemId = :OemId ";
            $prm = array(
                    ':Class' => $class,
                    ':OemId' => $oemId,
            );
        }

        $stm = $this->_adapter->query($sql);

        return $stm->execute($prm);
    }

	/**
	 * 新しいレコードをインサートする。
	 *
	 * @param array $data インサートする連想配列
	 * @return プライマリキーのバリュー？
	 */
	public function saveNew($data)
	{
        // テンプレート取得と、クラス名の設定
        $data['ClassName'] = $this->findMailTemplate($data['Class'])->current()['ClassName'];

        $sql  = " INSERT INTO T_MailTemplate (Class, ClassName, FromTitle, FromTitleMime, FromAddress, ToTitle, ToTitleMime, ToAddress, Subject, SubjectMime, Body, OemId, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :Class ";
        $sql .= " , :ClassName ";
        $sql .= " , :FromTitle ";
        $sql .= " , :FromTitleMime ";
        $sql .= " , :FromAddress ";
        $sql .= " , :ToTitle ";
        $sql .= " , :ToTitleMime ";
        $sql .= " , :ToAddress ";
        $sql .= " , :Subject ";
        $sql .= " , :SubjectMime ";
        $sql .= " , :Body ";
        $sql .= " , :OemId ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Class' => $data['Class'],
                ':ClassName' => $data['ClassName'],
                ':FromTitle' => $data['FromTitle'],
                ':FromTitleMime' => $data['FromTitleMime'],
                ':FromAddress' => $data['FromAddress'],
                ':ToTitle' => $data['ToTitle'],
                ':ToTitleMime' => $data['ToTitleMime'],
                ':ToAddress' => $data['ToAddress'],
                ':Subject' => $data['Subject'],
                ':SubjectMime' => $data['SubjectMime'],
                ':Body' => $data['Body'],
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
	 * @param unknown_type $id 更新するテンプレートID
	 */
	public function saveUpdate($data, $id)
	{
        $sql = " SELECT * FROM T_MailTemplate WHERE Id = :Id ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Id' => $id,
        );

        $row = $stm->execute($prm)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE T_MailTemplate ";
        $sql .= " SET ";
        $sql .= "     Class = :Class ";
        $sql .= " ,   ClassName = :ClassName ";
        $sql .= " ,   FromTitle = :FromTitle ";
        $sql .= " ,   FromTitleMime = :FromTitleMime ";
        $sql .= " ,   FromAddress = :FromAddress ";
        $sql .= " ,   ToTitle = :ToTitle ";
        $sql .= " ,   ToTitleMime = :ToTitleMime ";
        $sql .= " ,   ToAddress = :ToAddress ";
        $sql .= " ,   Subject = :Subject ";
        $sql .= " ,   SubjectMime = :SubjectMime ";
        $sql .= " ,   Body = :Body ";
        $sql .= " ,   OemId = :OemId ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE Id = :Id ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Id' => $id,
                ':Class' => $row['Class'],
                ':ClassName' => $row['ClassName'],
                ':FromTitle' => $row['FromTitle'],
                ':FromTitleMime' => $row['FromTitleMime'],
                ':FromAddress' => $row['FromAddress'],
                ':ToTitle' => $row['ToTitle'],
                ':ToTitleMime' => $row['ToTitleMime'],
                ':ToAddress' => $row['ToAddress'],
                ':Subject' => $row['Subject'],
                ':SubjectMime' => $row['SubjectMime'],
                ':Body' => $row['Body'],
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
	 * クラスIDと名称のアレイを取得する。
	 *
	 * @return array
	 */
	public function getTemplatesArray()
	{
	    $datas = $this->getAll();

		foreach ($datas as $data)
		{
			if($data['ClassName'] != null) {
				$d[$data['Class']] = $data['ClassName'];
			}
		}

		return $d;
    }
}
