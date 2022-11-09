<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use models\Table\TableTmpImage;

/**
 * 画像アップロード関連クラス
 *
 */
class LogicImageUpLoader
{
    /**
     * $_FILESで判断されたMIMEタイプを適切なタイプに変換する
     *
     * @static
     * @param string $mime_type 元のMIMEタイプ
     * @return string 変換されたより適切なMIMEタイプ
     */
    public static function convertImageType($mime_type)
    {
        $map = array(
                'image/x-icon' => 'image/vnd.microsoft.icon'
        );
        return isset($map[$mime_type]) ? $map[$mime_type] : $mime_type;
    }

    /**
     * DBアダプタ
     *
     * @var Adapter
     */
    private $db;

    /**
     * コンストラクタ
     *
     * @param Adapter $dbAdapter DBアダプタ
     */
    public function __construct($dbAdapter)
    {
        $this->db = $dbAdapter;
    }

    /**
     *  ロゴ1一時画像ファイル保存
     *
     * @param int $oem_id OemId
     * @param string $mine_type mineタイプ
     * @param string $file_name ファイル名
     * @param string $tmp テンポラリファイル
     * @param int $userId UserId
     * @return int 登録したSeq番号
     */
    public function saveLogo1TmpImage($oem_id = null, $mine_type, $file_name, $tmp, $userId)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);

        //作成日時取得
        $createdDate =  date( "Y-m-d H:i:s", time() );

        // ロゴ1データ新規作成
        $logo1Data = array(
            //OEMID
            'OemId'     => $oem_id,
            //使用タイプロゴ1
            'UseType'   => 0,
            //ファイル名
            'FileName'  => $file_name,
            //イメージデータbase64エンコードすること
            'ImageData' => base64_encode(file_get_contents($tmp)),
            //MineType
            'ImageType' => self::convertImageType($mime_type),
            //作成日時
            'CreatedDate' =>$createdDate,
            // RegistId
            'RegistId' => $userId,
            // UpdateId
            'UpdateId' => $userId,
        );

        $newId = $mdl_tmpImage->saveNew($logo1Data);

        return $newId;

    }
    /**
     *  ロゴ1一時画像ファイル取得
     *
     * @param int OemId
     */
    public function getLogo1TmpImage($seq)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);
        $tmpImage = $mdl_tmpImage->findTmpImage($seq)->current();

        if($tmpImage['UseType'] != 0){
            return null;
        }

        return $tmpImage;

    }

    /**
     *  ロゴ2一時画像ファイル保存
     *
     * @param int $oem_id OemId
     * @param string $mine_type mineタイプ
     * @param string $file_name ファイル名
     * @param string $tmp テンポラリファイル
     * @param int $userId UserId
     * @return int 登録したSeq番号
     */
    public function saveLogo2TmpImage($oem_id = null, $mine_type, $file_name, $tmp, $userId)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);

        //作成日時取得
        $createdDate =  date( "Y-m-d H:i:s", time() );

        // ロゴ2データ新規作成
        $logo2Data = array(
            //OEMID
            'OemId'     => $oem_id,
            //使用タイプロゴ2
            'UseType'   => 1,
            //ファイル名
            'FileName'  => $file_name,
            //イメージデータbase64エンコードすること
            'ImageData' => base64_encode(file_get_contents($tmp)),
            //MineType
            'ImageType' => self::convertImageType($mime_type),
            //作成日時
            'CreatedDate' =>$createdDate,
            // RegistId
            'RegistId' => $userId,
            // UpdateId
            'UpdateId' => $userId,
        );
        $newId = $mdl_tmpImage->saveNew($logo2Data);

        return $newId;

    }

    /**
     *  ロゴ2一時画像ファイル取得
     *
     * @param int OemId
     */
    public function getLogo2TmpImage($seq)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);
        $tmpImage = $mdl_tmpImage->findTmpImage($seq)->current();

        if($tmpImage['UseType'] != 1){
            return null;
        }

        return $tmpImage;
    }

    /**
     *  印影一時画像ファイル保存
     *
     * @param int $oem_id OemId
     * @param string $mine_type mineタイプ
     * @param string $file_name ファイル名
     * @param string $tmp テンポラリファイル
     * @param int $userId UserId
     * @return int 登録したSeq番号
     */
    public function saveImprintTmpImage($oem_id = null, $mine_type, $file_name, $tmp, $userId)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);

        //作成日時取得
        $createdDate =  date( "Y-m-d H:i:s", time() );

        // 印影データ新規作成
        $logo2Data = array(
            //OEMID
            'OemId'     => $oem_id,
            //使用タイプ印影
            'UseType'   => 2,
            //ファイル名
            'FileName'  => $file_name,
            //イメージデータbase64エンコードすること
            'ImageData' => base64_encode(file_get_contents($tmp)),
            //MineType
            'ImageType' => self::convertImageType($mime_type),
            //作成日時
            'CreatedDate' =>$createdDate,
            // RegistId
            'RegistId' => $userId,
            // UpdateId
            'UpdateId' => $userId,
        );
        $newId = $mdl_tmpImage->saveNew($logo2Data);

        return $newId;

    }

    /**
     *  印影一時画像ファイル取得
     *
     * @param int $seq
     */
    public function getImprintTmpImage($seq)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);
        $tmpImage = $mdl_tmpImage->findTmpImage($seq)->current();
        if($tmpImage['UseType'] != 2){
            return null;
        }
        return $tmpImage;

    }

    /**
     * favicon一時保存
     *
     * @param int $oem_id OEM ID
     * @param string $mime_type MIMEタイプ
     * @param string $file_name 元ファイル名
     * @param string $tmp アップロード一時ファイル
     * @param int $userId UserId
     * @return int 保存した一時画像シーケンス
     */
    public function saveFavIconTmpImage($oem_id, $mime_type, $file_name, $tmp, $userId)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);

        $data = array(
            // OEM ID
            'OemId' => (int)$oem_id,
            // 使用タイプ：favicon
            'UseType' => 3,
            // ファイル名
            'FileName' => $file_name,
            // イメージデータ → BASE64エンコード
            'ImageData' => base64_encode(file_get_contents($tmp)),
            // MIMEタイプ
            'ImageType' => self::convertImageType($mime_type),
            // 作成日時
            'CreatedDate' => date('Y-m-d H:i:s'),
            // RegistId
            'RegistId' => $userId,
            // UpdateId
            'UpdateId' => $userId,
        );
        return $mdl_tmpImage->saveNew($data);
    }

    /**
     *  favicon一時ファイル取得
     *
     * @param int $seq 一時画像シーケンス
     */
    public function getFavIconTmpImage($seq)
    {
        $mdl_tmpImage = new TableTmpImage($this->db);
        $tmpImage = $mdl_tmpImage->findTmpImage($seq)->current();
        return $tmpImage['UseType'] == 3 ? $tmpImage : null;
    }

    /**
     *  ロゴ1画像ファイル取得
     *
     * @param int OemId
     */
    public function getLogo1Image($oem_id)
    {
        $sql = " SELECT LargeLogo FROM T_Oem WHERE OemId = :OemId ";
        $row = $this->db->query($sql)->execute(array(':OemId' => $oem_id))->current();
        return ($row) ? $row['LargeLogo'] : "";
    }

    /**
     *  ロゴ2画像ファイル取得
     *
     * @param int OemId
     */
    public function getLogo2Image($oem_id)
    {
        $sql = " SELECT SmallLogo FROM T_Oem WHERE OemId = :OemId ";
        $row = $this->db->query($sql)->execute(array(':OemId' => $oem_id))->current();
        return ($row) ? $row['SmallLogo'] : "";
    }

    /**
     * 印影画像ファイル取得
     *
     * @param int OemId
     */
    public function getImPrintImage($oem_id)
    {
        $sql = " SELECT Imprint FROM T_Oem WHERE OemId = :OemId ";
        $row = $this->db->query($sql)->execute(array(':OemId' => $oem_id))->current();
        return ($row) ? $row['Imprint'] : "";
    }

    /**
     * faviconデータを取得する
     *
     * @param int $oem_id OEM ID
     * @return string faviconのBASE64データまたはnull
     */
    public function getFavIcon($oem_id)
    {
        $row = $this->getFavIconInfo($oem_id);
        return $row ? $row['FavIcon'] : null;
    }

    /**
     * faviconのMIMEタイプを取得する
     *
     * @param int $oem_id OEM ID
     * @return string faviconのMIMEタイプまたはnull
     */
    public function getFavIconType($oem_id)
    {
        $row = $this->getFavIconInfo($oem_id);
        return $row ? $row['FavIconType'] : null;
    }

    /**
     * 登録されている指定OEM向けのfavicon情報を取得する
     * 戻り値は連想配列で、キー'FavIcon'にBASE64エンコードされたfaviconデータ、
     * キー'FavIconType'にMIMEタイプが格納されている。
     * 指定OEM向けに登録されていない場合、このメソッドはnullを返す
     *
     * @param int $oem_id OEM ID
     * @return array | null
     */
    public function getFavIconInfo($oem_id)
    {
        $sql = " SELECT FavIcon, FavIconType FROM T_Oem WHERE OemId = :OemId ";
        $row = $this->db->query($sql)->execute(array(':OemId' => $oem_id))->current();
        return ($row) ? $row : null;
    }
}