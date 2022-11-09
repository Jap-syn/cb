<?php
namespace models\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * M_TemplateHeader(テンプレートヘッダーマスター)テーブルへのアダプタ
 */
class TableTemplateHeader
{
    protected $_name = 'M_TemplateHeader';
    protected $_primary = array('TemplateSeq');
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
     * テンプレートヘッダーマスターデータを取得する
     *
     * @param int $templateSeq テンプレートSEQ
     * @return ResultInterface
     */
    public function find($templateSeq)
    {
        $sql = " SELECT * FROM M_TemplateHeader WHERE TemplateSeq = :TemplateSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $templateSeq,
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
        $sql  = " INSERT INTO M_TemplateHeader (TemplateId, TemplateClass, Seq, TemplatePattern, TemplateName, TitleClass, DelimiterValue, EncloseValue, CharacterCode, NoDataFieldSettingFlg, FormId, Reserve, RegistDate, RegistId, UpdateDate, UpdateId, ValidFlg) VALUES (";
        $sql .= "   :TemplateId ";
        $sql .= " , :TemplateClass ";
        $sql .= " , :Seq ";
        $sql .= " , :TemplatePattern ";
        $sql .= " , :TemplateName ";
        $sql .= " , :TitleClass ";
        $sql .= " , :DelimiterValue ";
        $sql .= " , :EncloseValue ";
        $sql .= " , :CharacterCode ";
        $sql .= " , :NoDataFieldSettingFlg ";
        $sql .= " , :FormId ";
        $sql .= " , :Reserve ";
        $sql .= " , :RegistDate ";
        $sql .= " , :RegistId ";
        $sql .= " , :UpdateDate ";
        $sql .= " , :UpdateId ";
        $sql .= " , :ValidFlg ";
        $sql .= " )";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateId' => $data['TemplateId'],
                ':TemplateClass' => $data['TemplateClass'],
                ':Seq' => $data['Seq'],
                ':TemplatePattern' => isset($data['TemplatePattern']) ? $data['TemplatePattern'] : 0,
                ':TemplateName' => $data['TemplateName'],
                ':TitleClass' => isset($data['TitleClass']) ? $data['TitleClass'] : 0,
                ':DelimiterValue' => $data['DelimiterValue'],
                ':EncloseValue' => $data['EncloseValue'],
                ':CharacterCode' => isset($data['CharacterCode']) ? $data['CharacterCode'] : 'UTF-8',
                ':NoDataFieldSettingFlg' => isset($data['NoDataFieldSettingFlg']) ? $data['NoDataFieldSettingFlg'] : 0,
                ':FormId' => $data['FormId'],
                ':Reserve' => $data['Reserve'],
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
     * @param int $templateSeq テンプレートSEQ
     * @return ResultInterface
     */
    public function saveUpdate($data, $templateSeq)
    {
        $row = $this->find($templateSeq)->current();

        foreach ($data as $key => $value)
        {
            if (array_key_exists($key, $row))
            {
                $row[$key] = $value;
            }
        }

        $sql  = " UPDATE M_TemplateHeader ";
        $sql .= " SET ";
        $sql .= "     TemplateId = :TemplateId ";
        $sql .= " ,   TemplateClass = :TemplateClass ";
        $sql .= " ,   Seq = :Seq ";
        $sql .= " ,   TemplatePattern = :TemplatePattern ";
        $sql .= " ,   TemplateName = :TemplateName ";
        $sql .= " ,   TitleClass = :TitleClass ";
        $sql .= " ,   DelimiterValue = :DelimiterValue ";
        $sql .= " ,   EncloseValue = :EncloseValue ";
        $sql .= " ,   CharacterCode = :CharacterCode ";
        $sql .= " ,   NoDataFieldSettingFlg = :NoDataFieldSettingFlg ";
        $sql .= " ,   FormId = :FormId ";
        $sql .= " ,   Reserve = :Reserve ";
        $sql .= " ,   RegistDate = :RegistDate ";
        $sql .= " ,   RegistId = :RegistId ";
        $sql .= " ,   UpdateDate = :UpdateDate ";
        $sql .= " ,   UpdateId = :UpdateId ";
        $sql .= " ,   ValidFlg = :ValidFlg ";
        $sql .= " WHERE TemplateSeq = :TemplateSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $templateSeq,
                ':TemplateId' => $row['TemplateId'],
                ':TemplateClass' => $row['TemplateClass'],
                ':Seq' => $row['Seq'],
                ':TemplatePattern' => $row['TemplatePattern'],
                ':TemplateName' => $row['TemplateName'],
                ':TitleClass' => $row['TitleClass'],
                ':DelimiterValue' => $row['DelimiterValue'],
                ':EncloseValue' => $row['EncloseValue'],
                ':CharacterCode' => $row['CharacterCode'],
                ':NoDataFieldSettingFlg' => $row['NoDataFieldSettingFlg'],
                ':FormId' => $row['FormId'],
                ':Reserve' => $row['Reserve'],
                ':RegistDate' => $row['RegistDate'],
                ':RegistId' => $row['RegistId'],
                ':UpdateDate' => date('Y-m-d H:i:s'),
                ':UpdateId' => $row['UpdateId'],
                ':ValidFlg' => $row['ValidFlg'],
        );

        return $stm->execute($prm);
    }

    /**
     * 指定されたレコードを削除する。
     *
     * @param int $templateSeq テンプレートSEQ
     */
    public function delete($templateSeq)
    {
        $sql = " DELETE FROM M_TemplateHeader WHERE TemplateSeq = :TemplateSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':TemplateSeq' => $templateSeq,
        );

        return $stm->execute($prm);
    }

    /**
     * テンプレートヘッダーマスターデータを全て取得する
     *
     * @return ResultInterface
     */
    public function getAll()
    {
        $sql = " SELECT * FROM M_TemplateHeader WHERE ValidFlg = 1 ";

        $stm = $this->_adapter->query($sql);

        return $stm->execute(null);
    }

    /**
     * テンプレートSEQを取得
     *
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @return array|bool テンプレート or 処理失敗の場合、false
     */
    public function getTemplateSeq($templateId, $templateClass, $seq, $templatePattern = 0, $default = true )
    {
        // 指定条件からテンプレートSEQを検索
        $sql = " SELECT * FROM M_TemplateHeader WHERE TemplateId = :TemplateId AND TemplateClass = :TemplateClass AND Seq = :Seq AND TemplatePattern = :TemplatePattern ";
        $prm = array(
                ':TemplateId' => $templateId,
                ':TemplateClass' => $templateClass,
                ':Seq' => $seq,
                ':TemplatePattern' => $templatePattern,
        );
        $templateSeq = $this->_adapter->query($sql)->execute($prm)->current()['TemplateSeq'];

        // 見つからなかったらデフォルト（CB設定）を返す
        if( empty( $templateSeq ) && $default ) {
            $prm = array(
                ':TemplateId' => $templateId,
                ':TemplateClass' => 0,
                ':Seq' => 0,
                ':TemplatePattern' => 0,
            );
            $templateSeq = $this->_adapter->query($sql)->execute($prm)->current()['TemplateSeq'];
        }

        return $templateSeq;
    }
}
