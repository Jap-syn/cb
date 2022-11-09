<?php
namespace models\Logic;

use Zend\Config\Reader\Ini;
use Coral\Base\BaseLog;

/**
 * 電文リクエストクラス
 */
class Telegram {

    /**
     * コンストラクタ
     */
    public function __construct($config) {
        $this->_configPath = $config;
    }
    /**
     * JNB用電文
     * パラメータ:$telegramData リクエストの連想配列
     */
    public function telegramJNB($telegramData) {
        0;
        //ヘッダー部
        $headerKey = array('HD_VL', 'HD,MsgClass', 'HD_CompCode', 'HD_DetailCode', 'HD_ReqDateTime', 'HD_TranId', 'HD_HashValue' );

        //データ部
        $dataKey = array('DataKbn', 'ShokaiNo', 'KanjoDate', 'KisanDate', 'Amount', 'AnotherAmount', 'OutputCode', 'OutputName',
                         'RmtBankName', 'RmtBrName', 'CancelKind', 'EDIInfo', 'Dummy');

        //現在の時間(ミリ秒)取得
        list($micro, $unixtime) = explode(" ", microtime());

        //四捨五入で切り上げる
        $nowTime = sprintf('%s%03d', date('YmdHis', $unixtime), round($micro * 1000));

        //ログファイル場所取得
        $reader = new Ini();
        $data = $reader->fromFile($this->_configPath);
        $data = $data['log'];
        $this->logger = BaseLog::createFromArray($logConfig);

        $msg = "";

        //ログ文言作成
        foreach( $telegramData as $key => $value) {
            $msg .= $key." = ".$value;
        }
        //スタートログ出力
        $this->logger->info('TelegramJNB START:'.$msg);

        //ヘッダ行・データ行に分岐
        $headerData = array();
        $mainData = array();
        foreach($telegramData as $key => $value) {

            //ヘッダー行の場合
            if(in_array($key, $headerKey)) {
                $headerData[$key] = $value;
            } else if(in_array($key, $dataKey)) {
                $mainData[$key] = $value;
            }

        }

        // --- データ改変 ---

        //電文区分
        $headerData['HD_MsgClass'] = 'RP';

        //完了コード
        $headerData['HD_CompCode'] = '0000';

        //詳細コード
        $headerData['HD_DetailCode'] = '000000';

        //応答送信日時
        $headerData['HD_RspDateTime'] = $nowTime;

        //ヘッダーとデータ部をマージ
        $mergeData = array_merge($headerData,$mainData);

        //終了ログ書き込み
        $this->logger->info('TelegramJNB END');

        return  $mergeData;

    }
}
