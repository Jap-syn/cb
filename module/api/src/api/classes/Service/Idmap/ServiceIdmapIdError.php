<?php
namespace api\classes\Service\Idmap;

use api\classes\Service\Idmap\ServiceIdmapConst;
use api\classes\Service\Response\ServiceResponseIdmap;

/**
 * 注文ID変換APIの個別エラーを管理するクラス
 */
class ServiceIdmapIdError {
    /**
     * 個別エラーコード
     *
     * @var string
     */
    public $idErrorCode;

    /**
     * 個別エラーコードを指定してServiceIdmapIdErrorの
     * 新しいインスタンスを初期化する
     *
     * @param string $idErrorCode 個別エラーコード
     */
    public function __construct($idErrorCode) {
        $this->idErrorCode = $idErrorCode;
    }
}
