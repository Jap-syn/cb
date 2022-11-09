<?php
namespace models\Logic;

use Coral\Base\BaseLog;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Base\IO\BaseIOUtility;
use models\Table\TableCustomer;
use Zend\Http\Client;
use models\Table\TableClaimError;
use models\Table\TableClaimHistory;
use models\Table\TableSystemProperty;
use models\Table\TableCode;

/**
 * ペイジー関連ロジック
 *
 */
class LogicPayeasy
{
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $_adapter = null;

    /**
	   * ロガーインスタンス
	   *
	   * @access protected
	   * @var BaseLog
	   */
    protected $_logger;

    //M_Code.CodeId定数
    const PAYEASY_CODEID = 205;

    //M_Code.KeyCode定数
    const OEM_LIST_KEYCODE = 1;
    const URL_KEYCODE = 2;
    const TIMEOUT_KEYCODE = 3;
    const ENT_CODE_KEYCODE = 4;
    const ENT_SUB_CODE_KEYCODE = 5;
    const HASH_PASS_KEYCODE = 6;
    const BK_NUMBER_KEYCODE = 7;
    const PAYEASY_FEE = 8;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     * @param BaseLog $logger ロガー
     */
    function __construct( Adapter $adapter, $logger = null ){
        $this->_adapter = $adapter;
        $this->_logger = $logger;
    }
    /**
     * コメント設定のペイジー決済の項目から指定したkeycodeの備考を取得する
     * @param int $keyCode
     * @return string
     */
    public function findCommentPayeasy($keyCode){
        $mdlCode = new TableCode($this->_adapter);
        $rslt = $mdlCode->find(self::PAYEASY_CODEID, $keyCode)->current()['Note'];
        return $rslt;
    }
    /**
     * ペイジー対象のOEMか
     * @return bool
     */
    public function isPayeasyOem($oemId){
        $rslt = $this->findCommentPayeasy(self::OEM_LIST_KEYCODE);
        $rslt = str_replace(array(' ','　'), '', $rslt); //空白除去

        //入力なしの場合は対象なし
        if($rslt == "" || $rslt == null){
            return false;
        }

        $oemList = explode(',', $rslt);

        //値が入っていない要素を削除
        foreach($oemList as $key => $val){
            if($val == ""){
                unset($oemList[$key]);
            }
        }

        //0を含む場合はnullも対象にする
        if(in_array(0, $oemList)){
            $oemList[] = null;
        }

        return in_array($oemId, $oemList);
    }

    /**
     * 収納番号発番
     *
     */
    public function getBkNumber($chSeq, &$responseBody, &$errorMessage){

        //連携パラメータ取得
        $params = $this->getSendParams($chSeq);

        $responseBody = '';

        //リクエスト送信
        $isSuccess = $this->_PayeasySendRequest($params, $responseBody, $errorMessage);

        return $isSuccess;
    }
    /**
     * リクエスト送信時に必要なパラメータを取得する
     * @param int $chSeq
     */
    protected function getSendParams($chSeq){

        $mdlch = new TableClaimHistory($this->_adapter);
        $mdlc = new TableCustomer($this->_adapter);

        $chInfo = $mdlch->find($chSeq)->current();
        $custInfo = $mdlc->findCustomer(array('OrderSeq'=>$chInfo['OrderSeq']))->current();

        //支払期限日 = 請求日 + 30日
        $vdate = date('Ymd',strtotime($chInfo['ClaimDate'] . " + 30 day"));

        $shopid  = $this->findCommentPayeasy(self::ENT_CODE_KEYCODE);
        $cshopid = $this->findCommentPayeasy(self::ENT_SUB_CODE_KEYCODE);
        // CB_B2C_DEV-62
        $payeasyfee = $this->findCommentPayeasy(self::PAYEASY_FEE);
        if(empty($payeasyfee)){
        	$payeasyfee = 0;
        }
        // T_ClaimHistory更新(T_ClaimHistory行単位)
        $mdlch->saveUpdate(array('PayeasyFee' => $payeasyfee, 'UpdateDate' => date('Y-m-d H:i:s')), $chSeq);

        $params['p_ver'] = '0200';
        $params['stdate'] = date('Ymd');
        $params['vdate'] = $vdate;
        $params['stran'] = sprintf('%06d',substr($chInfo['OrderSeq'], -6));
        $params['bkcode'] = 'pe01';
        $params['shopid'] = $shopid;
        $params['cshopid'] = $cshopid;
        // CB_B2C_DEV-62
        $params['amount'] = $chInfo['ClaimAmount'] + $payeasyfee;
        $params['custm'] = $custInfo['NameKn'];
        $params['custmKanji'] = $custInfo['NameKj'];
//        //メールアドレスは省略可
//        if($custInfo['MailAddress'] != null && $custInfo['MailAddress'] != ''){
//            $params['mailaddr'] = $custInfo['MailAddress'];
//        }
        $params['tel'] = $custInfo['Phone'];

        //ハッシュ化のパスワード取得
        $hashpas = $this->findCommentPayeasy(self::HASH_PASS_KEYCODE);

        //改竄チェック用の項目を追加
        $schksum = '';
        foreach ($params as $val){
            $schksum .= $val;
        }
        $schksum .= $hashpas;
        $schksum = mb_convert_encoding($schksum, 'SJIS', 'UTF-8');
        $params['schksum'] = htmlspecialchars(md5($schksum));

        return $params;
    }
    /**
     * (Payeasy)リクエスト送信
     *
     * @param string $params オンライン決済ASPに渡すパラメータ
     * @param string $responseBody レスポンスデータ
     * @param array $errorMessage エラーメッセージ文字列の配列
     * @return boolean true:成功／false:失敗
     */
    protected function _PayeasySendRequest($params, &$responseBody, &$errorMessage) {

        //接続時の設定を取得
        $url     = $this->findCommentPayeasy(self::URL_KEYCODE);
        $timeout = $this->findCommentPayeasy(self::TIMEOUT_KEYCODE);

        //送信パラメータの文字列を作成
        $prm = array();
        foreach ($params as $key => $val){
            if(isset($val)){
                $prm[] = $key."=".urlencode(mb_convert_encoding($val,'SJIS'));
            }
        }
        $prm = implode('&', $prm);

        $option = array(
                'adapter'=> 'Zend\Http\Client\Adapter\Curl', // SSL通信用に差し替え
                'ssltransport' => 'tls',
                'maxredirects' => 1,                         // 試行回数(maxredirects) を 1 に設定
        );

        $client = new Client($url, $option);
        $client->setOptions(array('timeout' => (int)$timeout, 'keepalive' => true, 'maxredirects' => 1));

        if(isset($this->_logger)) $this->_logger->info('ペイジー連携送信パラメータ:'.var_export($params, true));

        try {
            //ローカルでテストする際はtrueにする
            $dummyFlg = false;

            if($dummyFlg){
                //ダミー処理実行
                $this->getDummyResponse($params, $responseBody);
            }else{
                //送信開始時間出力
                if(isset($this->_logger)) $this->_logger->info('payeasy send start: '.date('H:i:s').'.'.substr(explode(".",microtime(true))[1],0,3));

                // データ送信を実行する
                $response = $client
                ->setRawBody($prm)
                ->setEncType('application/x-www-form-urlencoded;', ';')
                ->setMethod('Post')
                ->send();

                //送信終了時間出力
                if(isset($this->_logger)) $this->_logger->info('payeasy send end: '.date('H:i:s').'.'.substr(explode(".",microtime(true))[1],0,3));

                // 結果を取得する
                $status = $response->getStatusCode();
                if ($status != 200) {
                    $errorMessage = 'HTTPステータスコード：'.$status;
                    return false;
                }
                $responseBody =  $response->getBody();
            }

            $originResp = $responseBody;
            parse_str($responseBody, $responseBody);

            //データの不一致
            if(is_null($responseBody['rsltcd']) || $responseBody['rsltcd'] == ''){
                //エラーの場合は変換前と変換後のデータをエラーメッセージとする(最大文字数：それぞれ450文字ずつ)
                $errorMessage = 'レスポンス変換エラー 変換前：'.mb_substr($originResp, 0, 450).' 変換後：'.mb_substr(var_export($responseBody,true),0,450);
                return false;
            }

            //結果コードのチェック
            if($responseBody['rsltcd'] != "0000000000000" ){
                $errorMessage = '結果コード:'.$responseBody['rsltcd'];
                return false;
            }

            if(isset($this->_logger)) $this->_logger->info('ペイジー連携受信パラメータ:'.var_export($responseBody, true));
            return true;

        }
        catch (\Exception $err) {
            $errorMessage = $err->getMessage();
            return false;
        }
    }

    /**
     * リクエストパラメータをログに出力してダミーのレスポンス配列をresponseBodyに格納する
     * @param array $params
     * @param array $responseBody
     */
    protected function getDummyResponse($params, &$responseBody){

        $mbtran = $params['stdate'].$params['stran'].$params['shopid'].$params['cshopid'];
        //収納機関受付番号と消込識別情報には、ランダムな文字列を設定
        $bktrans = substr(base_convert(md5(uniqid()), 15, 36) , 0, 24);
        $tranid = substr(base_convert(md5(uniqid()), 15, 36) , 0, 24);

        $resp = array(
            'p_ver'     => '0200',
            'stdate'    => $params['stdate'],
            'stran'     => $params['stran'],
            'bkcode'    => 'pe01',
            'shopid'    => $params['shopid'],
            'cshopid'   => $params['cshopid'],
            'amount'    => $params['amount'],
            'mbtran'    => $mbtran,
            'bktrans'   => $bktrans,
            'tranid'    => $tranid,
            'ddate'     => date('Ymd'),
            'rsltcd'    => '0000000000000',
            'skno'      => '58054',
            'vdate'     => $params['vdate']
        );
        $tmpArray = array();
        foreach ($resp as $key => $val){
            $tmpArray[] = $key.'='.$val;
        }
        $responseBody = implode($tmpArray,'&');
    }

}
