<?php
namespace models\Logic\SmbcRelation\Adapter;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Headers;

/**
 * SMBC決済ステーションのサービスへhttp接続するための接続アダプタ
 */
class LogicSmbcRelationAdapterHttp extends LogicSmbcRelationAdapterAbstract {
    /**
     * レスポンスデータのダンプ先ディレクトリパス
     *
     * @static
     * @access protected
     * @var string
     */
    protected static $__resp_dump_dir = null;

    /**
     * レスポンスデータのダンプ先ディレクトリを取得する
     *
     * @static
     * @return string | boolean
     */
    public static function getResponseDumpDir() {
        $current = self::$__resp_dump_dir;
        if($current && strlen($current) && is_dir(realpath($current))) {
            return realpath(self::$__resp_dump_dir);
        } else {
            return false;
        }
    }
    /**
     * レスポンスデータのダンプ先ディレクトリを設定する
     *
     * @static
     * @param string $dir ディレクトリパス
     */
    public static function setResponseDumpDir($dir) {
        if($dir && strlen($dir) && is_dir(realpath($dir))) {
            self::$__resp_dump_dir = realpath($dir);
        }
    }

    /**
     * レスポンスデータのダンプファイルパスを取得する。
     * このメソッドは、setResponseDumpDir()メソッドで有効なディレクトリパスが設定されている
     * 場合のみ有効なパスを返し、それ以外は明示的にfalseを返す。
     *
     * ファイル名はクラス名を小文字変換したものに、呼出し時点のマイクロ秒まで付与された
     * タイムスタンプを付加して生成される。
     *
     * @static
     * @return string | boolean
     */
    public static function getResponseDumpPath() {
        return self::__createDumpPath('response');
    }

    /**
     * リクエストデータのダンプファイルパスを取得する。
     * このメソッドは、setResponseDumpDir()メソッドで有効なディレクトリパスが設定されている
     * 場合のみ有効なパスを返し、それ以外は明示的にfalseを返す。
     *
     * ファイル名はクラス名を小文字変換したものに、呼出し時点のマイクロ秒まで付与された
     * タイムスタンプを付加して生成される。
     *
     * @static
     * @return string | boolean
     */
    public static function getRequestDumpPath() {
        return self::__createDumpPath('request');
    }

    /**
     * リクエストまたはレスポンスデータのダンプを出力するファイルパスを構築する
     *
     * @static
     * @access private
     * @param string $mode
     * @return string
     */
    private static function __createDumpPath($mode) {
        $dir = self::getResponseDumpDir();

        if(!$dir) return false;

        $ms = explode(' ', microtime());
        $name = sprintf(
                    '%s-%s-%s_%s.txt',
                    strtolower(__CLASS__),
                    nvl($mode, 'unknown'),
                    date('YmdHis'),
                    preg_replace('/^[^\.]*\./', '', $ms[0]) );
        $path = f_path($dir, $name, DIRECTORY_SEPARATOR);
        return $path;
    }

    /**
     * 決済ステーションへ指定データを送信し、受信結果を返す
     *
     * @param array $data 送信データ
     * @return array 受信データ
     *
     * @see 開発環境では外部通信が出来ないため、この処理は必ずエラーになる。 → 外部通信できる環境で getResponse()->getBody() が取得できることを要確認！！！
     */
    public function send(array $data) {
        $fixed = $this->formatParams($data);

        $max = $this->getRetryCount();
        $try = 1;

        while($try <= $max) {
            $clientConfig = array(
                    'adapter' => 'Zend\Http\Client\Adapter\Curl',
                    'curloptions' => array(
                            CURLOPT_FOLLOWLOCATION => TRUE,
                            CURLOPT_SSL_VERIFYPEER => FALSE
                    ),
            );

            $client = new Client($this->getUrl(), $clientConfig);
            $client->setParameterPost($fixed);
//             $client = new Client($this->getUrl());
//             $client->setOptions(array(
//                 'timeout' => $this->getRequestTimeout(),
//                 'ssltransport' => 'tls'
//             ))->setParameterPost($fixed);
            $client->getResponse()->getHeaders()->addHeaderLine('Content-Type', 'application/x-www-form-urlencoded');
//             $client->setHeaders('Content-Type', 'application/x-www-form-urlencoded');

            $path = self::getRequestDumpPath();
            if($path) {
                @file_put_contents($path, var_export($fixed, true));
            }

            try {
                $response = $client->setMethod('POST')->send();
                $path = self::getResponseDumpPath();
                if($path) {
                    // ダンプ先パスが有効な場合のみ受信生データのダンプを試行する
                    @file_put_contents($path, $response->getBody());
                }
            } catch(\Zend\Http\Client\Exception $clientErr) {
                $this->warn(sprintf('[send] http error. error = %s (%s), try next(%s)', $clientErr->getMessage(), get_class($clientErr), $try));
                // \Zend\Http\Client\Exceptionのみハンドルする
                // → その他の例外はリトライ対象外ですぐに上位へ
                $try++;
                usleep(500 * 1000); // 500ms待ち合わせる
                continue;
            }
            return $this->parseResponse($response->getBody());
        }
        throw new \Exception(sprintf('HTTP接続のリトライ上限 %d 回を超過しました', ($try - 1)));
    }

    /**
     * 送信データの連想配列をHTTP送信向けにフォーマットする
     *
     * @access protected
     * @param array $data 送信データ
     * @return mixed フォーマット済みデータ
     */
    protected function formatParams(array $data) {
        $result = array();
        $to_enc = $this->getTextEncoding();
        $from_enc = mb_internal_encoding();
        foreach($data as $key => $value) {
            // キー・値とも送信向けのテキストエンコードに変換
            $result[$key] = mb_convert_encoding(nvl($value), $to_enc, $from_enc);
        }
        return $result;
    }

    /**
     * 受信したコンテンツを受信結果データに展開する
     *
     * @access protected
     * @param mixed $response 受信したコンテンツ
     * @return array 展開済み受信データ
     */
    protected function parseResponse($response) {
        $result = array();
        $to_enc = mb_internal_encoding();
        $from_enc = $this->getTextEncoding();

        foreach(explode("\r\n", $response) as $line) {
            if(preg_match('/.*=/', $line)) {
                $parts = explode('=', $line, 2);
                $result[mb_convert_encoding(trim($parts[0]), $to_enc, $from_enc)] =
                    mb_convert_encoding(trim($parts[1]), $to_enc, $from_enc);
            }
        }

        $this->debug(sprintf('[parseResponse] parsed values = %s', var_export($result, true)));

        return $result;
    }

}
