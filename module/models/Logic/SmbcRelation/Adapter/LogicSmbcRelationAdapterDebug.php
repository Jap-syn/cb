<?php
namespace models\Logic\SmbcRelation\Adapter;

use models\Table\TableSmbcRelationLog;

/**
 * SMBC決済ステーションのサービスへの接続をエミュレートする、デバッグ向け接続アダプタ
 */
class LogicSmbcRelationAdapterDebug extends LogicSmbcRelationAdapterAbstract {
    /**
     * 決済ステーションへ指定データを送信し、受信結果を返す
     *
     * @param array $data 送信データ
     * @return array 受信データ
     */
    public function send(array $data) {
        /* ************************************************************************************************************
         * 2015/09/04
         *  開発環境では外部通信が出来ないので、このクラスを使用して外部通信後の処理が正常に流れるか確認する。
         *  実際にはHttpのsendメソッドをコール。（HttpかDebugかはT_SystemPropertyに保持）
         * ************************************************************************************************************ */
        // このクラスは送信データに必須のダミー戻りデータをマージするだけで返す
        $body = $this->formatParams($data);

        return $this->parseResponse($body);
    }

    /**
     * 送信データの連想配列をHTTP送信向けにフォーマットする
     *
     * @access protected
     * @param array $data 送信データ
     * @return mixed フォーマット済みデータ
     */
    protected function formatParams(array $data) {
        // このクラスでは入力をそのまま返す
        return $data;
    }

    /**
     * 受信したコンテンツを受信結果データに展開する
     *
     * @access protected
     * @param mixed $response 受信したコンテンツ
     * @return array 展開済み受信データ
     */
    protected function parseResponse($response) {
        // このクラスでは必須項目のみマージして返す
        if(!is_array($response)) $response = array();
        $dummy = array(
            'kessai_date' => date('Ymd'),
            'kessai_time' => date('His'),
            'kessai_no' => sprintf('%014d', mktime()),
            'rescd' => '000000',
            'res' => ''
        );
        if($this->getTargetFunctionCode() == TableSmbcRelationLog::TARGET_FUNC_REGISTER) {
            // 請求情報登録時はダミーのバーコードデータとダミーの銀行情報も追加
            $dummy = array_merge($dummy, array(
                // バーコード情報
                'haraidashi_no1' => sprintf('919%05d%04d%017d0%06d0%06d%d',
                                            // 収納代行会社コード5桁、MICコードを指定
                                            '08191',

                                            // 発行区分・企業識別コード、すべて0
                                            0,

                                            // 自由使用欄17桁には請求番号を割り当てる
                                            $response['shoporder_no'],

                                            //支払期限
                                            substr($response['shiharai_date'], 2),

                                            // 支払金額
                                            $response['seikyuu_kingaku'],

                                            // チェックディジットは計算せず0固定
                                            0
                                       ),

                'bank_cd' => '0000',            // 金融機関コード
                'bank_name' => 'ダミー銀行',    // 銀行名
                'branch_cd' => '000',           // 支店コード
                'branch_name' => 'ダミー支店',  // 支店名
                'kouza_shubetsu' => 1,          // 口座種別
                'kouza_no' => '9990999',        // 口座番号
                'kouza_name' => 'ﾀﾞﾐｰﾃﾞｰﾀ ｶ)'   // 口座名義
            ));
        }

        return array_merge($response, $dummy);
    }

}
