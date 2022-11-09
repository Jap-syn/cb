<?php
namespace models\Logic\CreditJudge;

use Zend\Db\Adapter\Adapter;

/**
 * 与信判定インターフェイス
 */
interface LogicCreditJudgeInterface {
    /**
     * データベースアダプタを設定する
     * @return Adapter
     */
    function getAdapter();

    /**
     * データベースアダプタを設定する
     *
     * @param Adapter $adapter データベースアダプタ
     * @return LogicCreaditJudgeInterface
     */
    function setAdapter(Adapter $adapter);

    /**
     * 指定の注文の審査を実行し、判定結果を返す。
     * 判定結果は以下の値のいずれかを返す。
     * -1：与信NG確定
     * 1：与信OK確定
     * 2：与信保留確定（＝手動与信対象）
     * 3：審査継続
     *
     * @param int $oseq 注文SEQ
     * @return int 判定結果
     */
    function judge($oseq);

}
