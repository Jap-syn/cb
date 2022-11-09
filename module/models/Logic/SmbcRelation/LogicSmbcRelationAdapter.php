<?php
namespace models\Logic\SmbcRelation;

use models\Logic\SmbcRelation\Adapter\LogicSmbcRelationAdapterAbstract;
use models\Logic\SmbcRelation\Adapter\LogicSmbcRelationAdapterDebug;
use models\Logic\SmbcRelation\Adapter\LogicSmbcRelationAdapterHttp;

/**
 * SMBC決済ステーション接続アダプタのファクトリクラス
 */
class LogicSmbcRelationAdapter {
    /** オプションキー定数：HTTPタイムアウトを指定する @var string */
    const OPT_TIMEOUT = 'option-timeout';

    /** オプションキー定数：HTTP接続リトライ回数を指定する @var string */
    const OPT_RETRY = 'option-retry';

    /** オプションキー定数：送受信テキストエンコードを指定する @var string */
    const OPT_TEXT_ENC = 'option-text-encoding';

    /** オプションキー定数：対象機能識別コードを指定する @var string */
    const OPT_TARGET_FUNC = 'options-target-function';

    /**
     * 生成するアダプタ名と接続先URL、その他のオプション配列を指定して
     * SMBC決済ステーション接続アダプタの新しいインスタンスを生成する
     *
     * @static
     * @param strinng $adapterName 生成するアダプタの名前
     * @param string $url 接続先URL
     * @param array $options その他の初期化オプション配列
     * @return LogicSmbcRelationAdapterAbstract 初期化済みの接続アダプタ
     */
    public static function create($adapterName, $url, array $options = array()) {
        $adapterName = ucfirst(strtolower($adapterName));
        // 2015/09/04 開発環境では外部通信できないので、外部通信後の処理が正常に流れるようにLogicSmbcRelationAdapterDebugをコールする。
        // （HttpかDebugかはT_SystemPropertyに保持）
        if ($adapterName == 'Http') {
            return new LogicSmbcRelationAdapterHttp($url, $options);
        } else if ($adapterName == 'Debug') {
            return new LogicSmbcRelationAdapterDebug($url, $options);
        } else {
            return new LogicSmbcRelationAdapterAbstract($url, $options);
        }
//         $className = sprintf('LogicSmbcRelationAdapter%s', $adapterName);
//         return new $className($url, $options);
    }
}