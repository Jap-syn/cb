<?php
namespace models\Logic;

use models\Table\TableTemplateHeader;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Base\IO\BaseIOUtility;

/**
* テンプレート変換クラス
*
* @package models\Logic
* @author T.Yanase
*/

class LogicTemplate
{
    /**
     * アダプタ
     *
     * @var Adapter
     */
    protected $adapter = null;

    /**
     * エラーメッセージ
     *
     * @var string
     */
    protected $errorMsg;

    /**
     * テンプレートヘッダー
     *
     * @var TemplateHeader
     */
    protected $templateHeader;

    /**
     * 強制指定タイトル行区分
     *
     * @var int
     * @see 初期値null
     */
    protected $_forceTitleClass = null;

    /**
     * 強制指定タイトル行区分の設定
     *
     * @param int $val タイトル行区分
     */
    public function setForceTitleClass($val)
    {
        $this->_forceTitleClass = $val;
    }

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    function __construct( Adapter $adapter )
    {
        $this->adapter = $adapter;
        $this->errorMsg = "";
    }

    /**
     * 配列を指定テンプレートで変換後レスポンス書き込み
     *
     * @param array $target 変換前の配列
     * @param string $fileName ファイル名
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @param Response $response 書き込んだレスポンス
     * @return Response|bool 書き込んだレスポンス or 処理失敗の場合、false
    */
    public function convertArraytoResponse( $targets, $fileName, $templateId, $templateClass, $seq, $templatePattern = 0, $response )
    {
        // テンプレートSEQ取得
        $templateSeq = $this->getTemplateSeq( $templateId, $templateClass, $seq, $templatePattern );
        if( $templateSeq == false ) {
            return false;
        }
        if($templateId == 'CKA04016_1'){
            return $this->convertArraytoResponseForSeq2( $targets, $fileName, $templateSeq, $response );
        }else{
            return $this->convertArraytoResponseForSeq( $targets, $fileName, $templateSeq, $response );
        }
    }

    /**
     * 配列を指定テンプレートで変換後レスポンス書き込み(注文検索結果CSV用)
     *
     * @param array $target 変換前の配列
     * @param string $fileName ファイル名
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @param Response $response 書き込んだレスポンス
     * @param array $params パラメータ
     * @return Response|bool 書き込んだレスポンス or 処理失敗の場合、false
     */
    public function convertArraytoResponse2( $targets, $fileName, $templateId, $templateClass, $seq, $templatePattern = 0, $response, $params )
    {
        // テンプレートSEQ取得
        $templateSeq = $this->getTemplateSeq( $templateId, $templateClass, $seq, $templatePattern );
        if( $templateSeq == false ) {
            return false;
        }

            return $this->convertArraytoResponseForSeq3( $targets, $fileName, $templateSeq, $response, $params );

    }
    /**
     * 配列を指定テンプレートで変換後レスポンス書き込み(CSVファイル名 日本語出力対応)
     *
     * @param array $target 変換前の配列
     * @param string $fileName ファイル名
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @param Response $response 書き込んだレスポンス
     * @return Response|bool 書き込んだレスポンス or 処理失敗の場合、false
     */
    public function JPNconvertArraytoResponse( $targets, $fileName, $templateId, $templateClass, $seq, $templatePattern = 0, $response )
    {
        // テンプレートSEQ取得
        $templateSeq = $this->getTemplateSeq( $templateId, $templateClass, $seq, $templatePattern );

        if( $templateSeq == false ) {
            return false;
        }

        return $this->JPNconvertArraytoResponseForSeq( $targets, $fileName, $templateSeq, $response );
    }

    /**
     * 配列を指定テンプレートで変換後レスポンス書き込み
     *
     * @param array $target 変換前の配列
     * @param string $fileName ファイル名
     * @param int $templateSeq テンプレートSEQ
     * @param Response $response 書き込んだレスポンス
     * @return array|bool 書き込んだレスポンス or 処理失敗の場合、false
    */
    public function convertArraytoResponseForSeq( $targets, $fileName, $templateSeq, $response )
    {
        // テンプレート取得
        $template = $this->getTemplate( $templateSeq );
        if( $template == false ) {
            return false;
        }

        // テンプレートヘッダー取得
        $obj = new TableTemplateHeader( $this->adapter );
        $this->templateHeader = $obj->find( $templateSeq )->current();
        if( $this->templateHeader['ValidFlg'] != 1 ) {
            $this->errorMsg = '有効なテンプレートではありません。';
            return false;
        }
        $datas = array();
        // 変換前の配列を$template['ListNumber']順にする
        if( is_array( $targets ) ) {
            for( $i = 0; $i < count($targets); $i++ ) {
                foreach( $template as $key => $value ) {
                    $datas[$i][$value['ListNumber']] = $targets[$i][$value['PhysicalName']];
                }
            }
        }
        // タイトル行区分取得
        $titleClass = $this->templateHeader['TitleClass'];
        // タイトル行は日本語名で出力
        if( $titleClass == 1 ) {
            foreach( $template as $key => $value ) {
                $headers[$value['ListNumber']] = $value['LogicalName'];
            }
            ksort($headers);
        }

        // タイトル行はフィールド名で出力
        elseif( $titleClass == 2 ) {
            foreach( $template as $key => $value ) {
                $headers[$value['ListNumber']] = $value['PhysicalName'];
            }
            ksort($headers);
        }
            // CSV出力
            $this->writeCsv( $headers, $datas, $fileName, $response );
        return $response;
    }

    /**
     * 配列を指定テンプレートで変換後レスポンス書き込み
     *
     * @param array $target 変換前の配列
     * @param string $fileName ファイル名
     * @param int $templateSeq テンプレートSEQ
     * @param Response $response 書き込んだレスポンス
     * @return array|bool 書き込んだレスポンス or 処理失敗の場合、false
     */
    public function convertArraytoResponseForSeq2( $targets, $fileName, $templateSeq, $response )
    {
        // テンプレート取得
        $template = $this->getTemplate( $templateSeq );
        if( $template == false ) {
            return false;
        }

        // テンプレートヘッダー取得
        $obj = new TableTemplateHeader( $this->adapter );
        $this->templateHeader = $obj->find( $templateSeq )->current();
        if( $this->templateHeader['ValidFlg'] != 1 ) {
            $this->errorMsg = '有効なテンプレートではありません。';
            return false;
        }

        $taxRateFlg = false;
        $datas = array();
        // 変換前の配列を$template['ListNumber']順にする
        if( is_array( $targets ) ) {
            for( $i = 0; $i < count($targets); $i++ ) {
                foreach( $template as $key => $value ) {
                    if('TaxRate' == $value['PhysicalName']){
                        $taxRateFlg = true;
                        continue;
                    }

                    $datas[$i][$value['ListNumber']] = $targets[$i][$value['PhysicalName']];
                }
            }
        }
        // タイトル行区分取得
        $titleClass = $this->templateHeader['TitleClass'];
        // タイトル行は日本語名で出力
        if( $titleClass == 1 ) {
            foreach( $template as $key => $value ) {
                if('消費税率' == $value['LogicalName']){
                    continue;
                }
                $headers[$value['ListNumber']] = $value['LogicalName'];
            }
            ksort($headers);
        }

        // タイトル行はフィールド名で出力
        elseif( $titleClass == 2 ) {
            foreach( $template as $key => $value ) {
                if('TaxRate' == $value['PhysicalName']){
                    continue;
                }
                $headers[$value['ListNumber']] = $value['PhysicalName'];
            }
            ksort($headers);
        }
        if($taxRateFlg){
            // CSV出力
            $this->writeCsv2( $headers, $datas, $fileName, $response );
        }else{
            // CSV出力
            $this->writeCsv( $headers, $datas, $fileName, $response );
        }
        return $response;
    }

    /**
     * 配列を指定テンプレートで変換後レスポンス書き込み(注文検索結果CSV用)
     *
     * @param array $target 変換前の配列
     * @param string $fileName ファイル名
     * @param int $templateSeq テンプレートSEQ
     * @param Response $response 書き込んだレスポンス
     * @param array $params パラメータ
     * @return array|bool 書き込んだレスポンス or 処理失敗の場合、false
     */
    public function convertArraytoResponseForSeq3( $targets, $fileName, $templateSeq, $response, $params )
    {

        // テンプレート取得
        $template = $this->getTemplate( $templateSeq );
        if( $template == false ) {

            return false;
        }

        // テンプレートヘッダー取得
        $obj = new TableTemplateHeader( $this->adapter );
        $this->templateHeader = $obj->find( $templateSeq )->current();
        if( $this->templateHeader['ValidFlg'] != 1 ) {
            $this->errorMsg = '有効なテンプレートではありません。';
            return false;
        }

        $datas = array();
        // 変換前の配列を$template['ListNumber']順にする
        if( is_array( $targets ) ) {
            for( $i = 0; $i < count($targets); $i++ ) {
                foreach( $template as $key => $value ) {
                    // 検索パターン1の場合
                    if( $params['SearchPattern'] == '1' ) {
                        //抽出項目に全てにチェックがない場合除外しない
                        if(!(is_null($params['ReceiptAmount']) && is_null($params['UseAmount']))){
                            //抽出項目にチェックがないものを読み飛ばす
                            if(is_null($params['ReceiptAmount']) && 'ReceiptAmount' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['UseAmount']) && 'UseAmount' == $value['PhysicalName']){
                                continue;
                            }
                        }
                        $datas[$i][$value['ListNumber']] = $targets[$i][$value['PhysicalName']];
                    // 検索パターン2の場合
                    }else if( $params['SearchPattern'] == '2' ) {
                        //抽出項目に全てにチェックがない場合除外しない
                        if(!(is_null($params['RegistDate']) && is_null($params['ReceiptOrderDate']) && is_null($params['SiteId']) && is_null($params['EnterpriseNameKj']) && is_null($params['NameKj']) && is_null($params['Incre_Note']) && is_null($params['Phone']) && is_null($params['MailAddress']) && is_null($params['UnitingAddress']) && is_null($params['DestUnitingAddress']) && is_null($params['F_LimitDate']) && is_null($params['Incre_DecisionOpId']) && is_null($params['Incre_ScoreTotal']) && is_null($params['TotalScore']) && is_null($params['ReceiptDate']) && is_null($params['ReceiptAmountTotal']) && is_null($params['Cnl_Status']) && is_null($params['ItemNameKj']) && is_null($params['UnitPrice']) && is_null($params['UseAmount']) && is_null($params['Incre_Status']))){
                            //抽出項目にチェックがないものを読み飛ばす
                            if(is_null($params['RegistDate']) && 'RegistDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['ReceiptOrderDate']) && 'ReceiptOrderDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['SiteId']) && 'SiteId' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['EnterpriseNameKj']) && 'EnterpriseNameKj' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['NameKj']) && 'NameKj' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Incre_Note']) && 'Incre_Note' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Phone']) && 'Phone' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['MailAddress']) && 'MailAddress' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['UnitingAddress']) && 'UnitingAddress' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['DestUnitingAddress']) && 'DestUnitingAddress' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['F_LimitDate']) && 'F_LimitDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Incre_DecisionOpId']) && 'Incre_DecisionOpId' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Incre_ScoreTotal']) && 'Incre_ScoreTotal' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['TotalScore']) && 'TotalScore' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['ReceiptDate']) && 'ReceiptDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['ReceiptAmountTotal']) && 'ReceiptAmountTotal' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Cnl_Status']) && 'Cnl_Status' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['ItemNameKj']) && 'ItemNameKj' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['UnitPrice']) && 'UnitPrice' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['UseAmount']) && 'UseAmount' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Incre_Status']) && 'Incre_Status' == $value['PhysicalName']){
                                continue;
                            }
                        }
                        $datas[$i][$value['ListNumber']] = $targets[$i][$value['PhysicalName']];
                    // 検索パターン3の場合
                    }else if( $params['SearchPattern'] == '3' ) {
                        //抽出項目に全てにチェックがない場合除外しない
                        if(!(is_null($params['ReceiptOrderDate']) && is_null($params['EnterpriseNameKj']) && is_null($params['NameKj']) && is_null($params['Phone']) && is_null($params['MailAddress']) && is_null($params['PostalCode']) && is_null($params['UnitingAddress']) && is_null($params['Deli_ConfirmArrivalDate']) && is_null($params['ExecScheduleDate'])  && is_null($params['UseAmount']) && is_null($params['ItemNameKj']) && is_null($params['Incre_Status']) && is_null($params['PromPayDate']) && is_null($params['RemindClass']) && is_null($params['CombinedClaimTargetStatus']))){
                            //抽出項目にチェックがないものを読み飛ばす
                            if(is_null($params['ReceiptOrderDate']) && 'ReceiptOrderDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['EnterpriseNameKj']) && 'EnterpriseNameKj' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['NameKj']) && 'NameKj' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Phone']) && 'Phone' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['MailAddress']) && 'MailAddress' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['PostalCode']) && 'PostalCode' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['UnitingAddress']) && 'UnitingAddress' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Deli_ConfirmArrivalDate']) && 'Deli_ConfirmArrivalDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['ExecScheduleDate']) && 'ExecScheduleDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['UseAmount']) && 'UseAmount' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['ItemNameKj']) && 'ItemNameKj' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['Incre_Status']) && 'Incre_Status' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['PromPayDate']) && 'PromPayDate' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['RemindClass']) && 'RemindClass' == $value['PhysicalName']){
                                continue;
                            }
                            if(is_null($params['CombinedClaimTargetStatus']) && 'CombinedClaimTargetStatus' == $value['PhysicalName']){
                                continue;
                            }
                        }
                        $datas[$i][$value['ListNumber']] = $targets[$i][$value['PhysicalName']];
                    }
                }
            }
        }

        // 検索パターン1の場合
        if( $params['SearchPattern'] == '1' ) {
            foreach( $template as $key => $value ) {
                //抽出項目に全てにチェックがない場合除外しない
                if(!(is_null($params['ReceiptAmount']) && is_null($params['UseAmount']))){
                    //抽出項目にチェックがないものを読み飛ばす
                    if(is_null($params['ReceiptAmount']) && '入金額' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['UseAmount']) && '注文金額' == $value['LogicalName']){
                        continue;
                    }
                }
                $headers[$value['ListNumber']] = $value['LogicalName'];
            }
            ksort($headers);
        // 検索パターン2の場合
        } else if( $params['SearchPattern'] == '2' ) {
            foreach( $template as $key => $value ) {
                //抽出項目に全てにチェックがない場合除外しない
                if(!(is_null($params['RegistDate']) && is_null($params['ReceiptOrderDate']) && is_null($params['SiteId']) && is_null($params['EnterpriseNameKj']) && is_null($params['NameKj']) && is_null($params['Incre_Note']) && is_null($params['Phone']) && is_null($params['MailAddress']) && is_null($params['UnitingAddress']) && is_null($params['DestUnitingAddress']) && is_null($params['F_LimitDate']) && is_null($params['Incre_DecisionOpId']) && is_null($params['Incre_ScoreTotal']) && is_null($params['TotalScore']) && is_null($params['ReceiptDate']) && is_null($params['ReceiptAmountTotal']) && is_null($params['Cnl_Status']) && is_null($params['ItemNameKj']) && is_null($params['UnitPrice']) && is_null($params['UseAmount']) && is_null($params['Incre_Status']))){
                    //抽出項目にチェックがないものを読み飛ばす
                    if(is_null($params['RegistDate']) && '注文登録日' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['ReceiptOrderDate']) && '注文日' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['SiteId']) && 'サイトID' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['EnterpriseNameKj']) && '会社名' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['NameKj']) && '注文者名' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Incre_Note']) && '備考' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Phone']) && '注文者TEL' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['MailAddress']) && '注文者メアド' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['UnitingAddress']) && '注文者住所' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['DestUnitingAddress']) && '配送先住所' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['F_LimitDate']) && '初回支払期限' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Incre_DecisionOpId']) && '与信担当者' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Incre_ScoreTotal']) && '社内与信スコア' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['TotalScore']) && '審査システムスコア' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['ReceiptDate']) && '入金日' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['ReceiptAmountTotal']) && '入金額' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Cnl_Status']) && 'キャンセル状態' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['ItemNameKj']) && '商品名' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['UnitPrice']) && '商品単価' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['UseAmount']) && '利用額' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Incre_Status']) && '審査結果' == $value['LogicalName']){
                        continue;
                    }
                }
                $headers[$value['ListNumber']] = $value['LogicalName'];
            }
            ksort($headers);
        // 検索パターン3の場合
        } else if( $params['SearchPattern'] == '3' ) {
            foreach( $template as $key => $value ) {
                //抽出項目に全てにチェックがない場合除外しない
                if(!(is_null($params['ReceiptOrderDate']) && is_null($params['EnterpriseNameKj']) && is_null($params['NameKj']) && is_null($params['Phone']) && is_null($params['MailAddress']) && is_null($params['PostalCode']) && is_null($params['UnitingAddress']) && is_null($params['Deli_ConfirmArrivalDate']) && is_null($params['ExecScheduleDate'])  && is_null($params['UseAmount']) && is_null($params['ItemNameKj']) && is_null($params['Incre_Status']) && is_null($params['PromPayDate']) && is_null($params['RemindClass']) && is_null($params['CombinedClaimTargetStatus']))){
                    //抽出項目にチェックがないものを読み飛ばす
                    if(is_null($params['ReceiptOrderDate']) && '注文日' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['EnterpriseNameKj']) && '会社名' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['NameKj']) && '注文者名' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Phone']) && '注文者TEL' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['MailAddress']) && '注文者メアド' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['PostalCode']) && '注文者郵便番号' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['UnitingAddress']) && '注文者住所' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Deli_ConfirmArrivalDate']) && '着荷確認日' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['ExecScheduleDate']) && '立替予定日' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['UseAmount']) && '利用額' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['ItemNameKj']) && '商品１名前' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['Incre_Status']) && '審査結果' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['PromPayDate']) && '支払約束日' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['RemindClass']) && '督促分類' == $value['LogicalName']){
                        continue;
                    }
                    if(is_null($params['CombinedClaimTargetStatus']) && '取りまとめ' == $value['LogicalName']){
                        continue;
                    }
                }
                $headers[$value['ListNumber']] = $value['LogicalName'];
            }
            ksort($headers);
        }

        // CSV出力
        $this->writeCsv( $headers, $datas, $fileName, $response );

        return $response;
    }
    /**
     * 配列を指定テンプレートで変換後レスポンス書き込み(CSVファイル名 日本語出力対応)
     *
     * @param array $target 変換前の配列
     * @param string $fileName ファイル名
     * @param int $templateSeq テンプレートSEQ
     * @param Response $response 書き込んだレスポンス
     * @return array|bool 書き込んだレスポンス or 処理失敗の場合、false
     */
    public function JPNconvertArraytoResponseForSeq( $targets, $fileName, $templateSeq, $response )
    {
        // テンプレート取得
        $template = $this->getTemplate( $templateSeq );
        if( $template == false ) {
            return false;
        }

        // テンプレートヘッダー取得
        $obj = new TableTemplateHeader( $this->adapter );
        $this->templateHeader = $obj->find( $templateSeq )->current();
        if( $this->templateHeader['ValidFlg'] != 1 ) {
            $this->errorMsg = '有効なテンプレートではありません。';
            return false;
        }

        $datas = array();
        // 変換前の配列を$template['ListNumber']順にする
        if( is_array( $targets ) ) {
            for( $i = 0; $i < count($targets); $i++ ) {
                foreach( $template as $key => $value ) {
                    $datas[$i][$value['ListNumber']] = $targets[$i][$value['PhysicalName']];
                }
            }
        }

        // タイトル行区分取得
        $titleClass = $this->templateHeader['TitleClass'];
        // タイトル行は日本語名で出力
        if( $titleClass == 1 ) {
            foreach( $template as $key => $value ) {
                $headers[$value['ListNumber']] = $value['LogicalName'];
            }
            ksort($headers);
        }
        // タイトル行はフィールド名で出力
        elseif( $titleClass == 2 ) {
            foreach( $template as $key => $value ) {
                $headers[$value['ListNumber']] = $value['PhysicalName'];
            }
            ksort($headers);
        }

        // CSV出力
        $this->JPNwriteCsv( $headers, $datas, $fileName, $response );

        return $response;
    }

    /**
     * ファイル内容を指定テンプレートで変換
     *
     * @param string $fileName ファイル名
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @return array|bool 変換後配列 or 処理失敗の場合、false
     */
    public function convertFiletoArray( $fileName, $templateId, $templateClass, $seq, $templatePattern )
    {
        // テンプレートSEQ取得
        $templateSeq = $this->getTemplateSeq( $templateId, $templateClass, $seq, $templatePattern );

        if( $templateSeq == false ) {
            return false;
        }

        return $this->convertFiletoArrayForSeq( $fileName, $templateSeq );
    }

    /**
     * ファイル内容を指定テンプレートで変換
     *
     * @param string $fileName ファイル名
     * @param int $templateSeq テンプレートSEQ
     * @return array|bool 変換後の配列 or 処理失敗の場合、false
     */
    public function convertFiletoArrayForSeq( $fileName, $templateSeq )
    {
        // テンプレート取得
        $template = $this->getTemplate( $templateSeq );

        if( $template == false ) {
            return false;
        }

        // テンプレートヘッダー取得
        $obj = new TableTemplateHeader( $this->adapter );
        $this->templateHeader = $obj->find( $templateSeq )->current();
        if( $this->templateHeader['ValidFlg'] != 1 ) {
            $this->errorMsg = '有効なテンプレートではありません。';
            return false;
        }

        // UTF-8へエンコード実施
        if( $this->templateHeader['CharacterCode'] == '*' ) {
            BaseIOUtility::convertFileEncoding( $fileName, null, null, true );
        }
        else {
            // 変換実行
            $src = file_get_contents( $fileName );
            file_put_contents( $fileName, mb_convert_encoding( $src, mb_internal_encoding(), $this->templateHeader['CharacterCode'] ) );
        }

        $handle = fopen( $fileName, "r" );
        if( ! $handle ) {
            $this->errorMsg = 'ファイルが読み込めませんでした。';
            fclose( $handle );
            return false;
        }

        // 囲み文字取得
        $enclose = !empty( $this->templateHeader['EncloseValue'] ) ? $this->templateHeader['EncloseValue'] : '"';

        // 区切り文字取得
        $delimiter = !empty( $this->templateHeader['DelimiterValue'] ) ? $this->templateHeader['DelimiterValue'] : ',';

        // 配列キー作成
        foreach( $template as $key => $value ) {
            $headers[$value['ListNumber']] = $value['PhysicalName'];
        }
        ksort($headers);

        $result = array();
        try {
            while( ! feof( $handle ) ) {
                $row = fgetcsv( $handle, 0, $delimiter, $enclose );

                if( empty( $row ) ) {
                    $row = null;
                } else if( count( $row ) == 1 && empty( $row[0] ) ) {
                    $row = null;
                }
                if( $row != null ) {
                    $headersCount = 0;
                    if(!empty($headers)) {
                        $headersCount = count($headers);
                    }
                    if( $headersCount != count( $row ) ) {
                        $this->errorMsg = 'テンプレート形式が異なります。';
                        fclose( $handle );
                        return false;
                    }
                    $result[] = array_combine( $headers, $row );
                }
            }
            fclose( $handle );
        } catch( \Exception $err ) {
            if( $handle ) {
                fclose( $handle );
            }
            $this->errorMsg = $err->getMessage();
            return false;
        }

        // タイトル行区分取得
        $titleClass = $this->templateHeader['TitleClass'];
        if (!is_null($this->_forceTitleClass)) {
            // 強制指定タイトル行区分指定があれば、置き換え
            $titleClass = $this->_forceTitleClass;
        }
        // タイトル行は削除
        if( $titleClass == 1 || $titleClass == 2 ) {
            array_shift( $result );
        }

        return $result;
    }

    /**
     * 配列を指定テンプレートで変換
     *
     * @param array $targets 変換前の配列
     * @param string $fileName ファイル名
     * @param char $templateId テンプレートID
     * @param int $templateClass 区分(0：CB、1：OEM、2：加盟店、3：サイト)
     * @param int $seq シーケンス(区分0：CB、区分1：OEMID、区分2：加盟店ID、区分3：サイトID)
     * @param int $templatePattern テンプレートパターン(デフォルトは0)
     * @return bool
     */
    public function convertArraytoFile( $targets, $fileName, $templateId, $templateClass, $seq, $templatePattern )
    {
        // テンプレートSEQ取得
        $templateSeq = $this->getTemplateSeq( $templateId, $templateClass, $seq, $templatePattern );

        if( $templateSeq == false ) {
            return false;
        }

        return $this->convertArraytoFileForSeq( $targets, $fileName, $templateSeq );
    }

    /**
     * 配列を指定テンプレートで変換
     *
     * @param array $targets 変換前の配列
     * @param string $fileName ファイル名
     * @param int $templateSeq テンプレートSEQ
     * @return bool
     */
    public function convertArraytoFileForSeq( $targets, $fileName, $templateSeq )
    {
        // テンプレート取得
        $template = $this->getTemplate( $templateSeq );
        if( $template == false ) {
            return false;
        }

        // テンプレートヘッダー取得
        $obj = new TableTemplateHeader( $this->adapter );
        $this->templateHeader = $obj->find( $templateSeq )->current();
        if( $this->templateHeader['ValidFlg'] != 1 ) {
            $this->errorMsg = '有効なテンプレートではありません。';
            return false;
        }

        // 変換前の配列を$template['ListNumber']順にする
        $datas = array();
        if( is_array( $targets ) ) {
            for( $i = 0; $i < count($targets); $i++ ) {
                foreach( $template as $key => $value ) {
                    $datas[$i][$value['ListNumber']] = $targets[$i][$value['PhysicalName']];
                }
            }
        }

        // タイトル行区分取得
        $titleClass = $this->templateHeader['TitleClass'];
        // タイトル行は日本語名で出力
        if( $titleClass == 1 ) {
            foreach( $template as $key => $value ) {
                $headers[$value['ListNumber']] = $value['LogicalName'];
            }
            ksort($headers);
        }
        // タイトル行はフィールド名で出力
        elseif( $titleClass == 2 ) {
            foreach( $template as $key => $value ) {
                $headers[$value['ListNumber']] = $value['PhysicalName'];
            }
            ksort($headers);
        }

        // $resultsをファイル書き込み
        $fh = fopen( $fileName, 'w' );
        fwrite( $fh, $this->encodeRow($headers) . "\r\n");
        foreach($datas as $data) {
            fwrite( $fh, $this->encodeRow($data) . "\r\n");
        }
        fclose( $fh );

        return true;
    }

    /**
     * エラーメッセージを取得
     *
     * @return string エラーメッセージ
     */
    public function getErrorMessage()
    {
        return $this->errorMsg;
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
    protected function getTemplateSeq($templateId, $templateClass, $seq, $templatePattern = 0)
    {
        // 指定条件からテンプレートSEQを検索
        $sql = " SELECT * FROM M_TemplateHeader WHERE TemplateId = :TemplateId AND TemplateClass = :TemplateClass AND Seq = :Seq AND TemplatePattern = :TemplatePattern ";
        $prm = array(
                ':TemplateId' => $templateId,
                ':TemplateClass' => $templateClass,
                ':Seq' => $seq,
                ':TemplatePattern' => $templatePattern,
        );
        $ri = $this->adapter->query($sql)->execute($prm);
        $row = ResultInterfaceToArray($ri);

        // 条件に一致するテンプレートSEQが1以外の場合
        $rowCount = 0;
        if(!empty($row)) {
            $rowCount = count($row);
        }
        if($rowCount != 1) {
            // TemplateClass = CB 以外だったら TemplateClass = CB で再取得
            if($templateClass != 0) {
                $prm = array(
                    ':TemplateId' => $templateId,
                    ':TemplateClass' => 0,
                    ':Seq' => 0,
                    ':TemplatePattern' => 0,
                );
                $ri = $this->adapter->query($sql)->execute($prm);
                $row = ResultInterfaceToArray($ri);

                $rowCount = 0;
                if(!empty($row)) {
                    $rowCount = count($row);
                }
                if($rowCount != 1) {
                    $this->errorMsg = 'テンプレートが特定できませんでした。';
                    return false;
                }
            }
            else {
                $this->errorMsg = 'テンプレートが特定できませんでした。';
                return false;
            }
        }
        $templateSeq = $row[0]['TemplateSeq'];

        return $templateSeq;
    }

    /**
     * テンプレートを取得
     *
     * @param int $templateSeq テンプレートSEQ
     * @return array|bool テンプレートSEQ or 処理失敗の場合、false
     */
    protected function getTemplate($templateSeq)
    {
        // 指定されたテンプレートSEQからテンプレートを検索
        $sql = " SELECT * FROM M_TemplateField WHERE TemplateSeq = :TemplateSeq AND ValidFlg = 1 ORDER BY ListNumber ";
        $stm = $this->adapter->query($sql);
        $prm = array(
                ':TemplateSeq' => $templateSeq,
        );
        $ri = $this->adapter->query($sql)->execute($prm);
        $template = ResultInterfaceToArray($ri);

        if(empty($template)) {
            $this->errorMsg = '指定されたテンプレートが見つかりませんでした。';
            return false;
        }

        return $template;
    }

    /**
     * 現在の設定でCSV出力を開始する
     *
     * @param array $header 出力ヘッダ
     * @param array $datas 出力データ
     * @param string $fileName 出力データに設定するファイル名(ローカルパスを指定した場合はそのファイルパスに出力)
     * @param ResponseInterface $response 出力オブジェクト
     */
    protected function writeCsv($header, $datas, $fileName, $response ) {
        $is_match = preg_match( '/(.*[\\\\\\/])?([^\\\\\\/]+)$/', $fileName, $matches );

        if( $is_match ) $fileName = $matches[2];

        if( ! ( $response instanceof ResponseInterface ) ) $response = null;

        $fileName = urlencode( $fileName );

        $dispValue = 'attachment';

        // レスポンスヘッダの出力
        $contentType = 'application/octet-stream';
        if( $response ) {
            $response->getHeaders()->addHeaderLine( 'Content-Type', $contentType )
            ->addHeaderLine( 'Content-Disposition' , "$dispValue; filename=$fileName" );
        } else {
            header( "Content-Type: $contentType" );
            header( "Content-Disposition: $dispValue; filename=$fileName" );
        }
        // データ出力
        if ($this->templateHeader['TitleClass'] > 0) {
            echo $this->encodeRow( $header ) . "\r\n";
        }
        foreach( $datas as $data ) {
            echo $this->encodeRow( $data ) . "\r\n";
        }
    }

    /**
     * 現在の設定でCSV出力を開始する(CSVファイル名 日本語出力対応)
     *
     * @param array $header 出力ヘッダ
     * @param array $datas 出力データ
     * @param string $fileName 出力データに設定するファイル名(ローカルパスを指定した場合はそのファイルパスに出力)
     * @param ResponseInterface $response 出力オブジェクト
     */
    protected function JPNwriteCsv($header, $datas, $fileName, $response ) {
        $is_match = preg_match( '/(.*[\\\\\\/])?([^\\\\\\/]+)$/', $fileName, $matches );

        if( $is_match ) $fileName = $matches[2];

        if( ! ( $response instanceof ResponseInterface ) ) $response = null;

        $fileName = rawurlencode( $fileName );

        $dispValue = 'attachment';

        // レスポンスヘッダの出力
        $contentType = 'application/octet-stream';
        if( $response ) {
            $response->getHeaders()->addHeaderLine( 'Content-Type', $contentType )
            ->addHeaderLine( 'Content-Disposition' , "$dispValue; filename=$fileName; filename*=UTF-8\''$fileName" );
        } else {
            header( "Content-Type: $contentType" );
            header( "Content-Disposition: $dispValue; filename=$fileName; filename*=UTF-8\''$fileName" );
        }

        // データ出力
        if ($this->templateHeader['TitleClass'] > 0) {
            echo $this->encodeRow( $header ) . "\r\n";
        }
        foreach( $datas as $data ) {
            echo $this->encodeRow( $data ) . "\r\n";
        }
    }

    /**
     * 設定された文字エンコードで囲み文字と区切り文字で変換した文字列を返す
     *
     * @param array $row
     * @return string
     */
    protected function encodeRow($row) {
        $result = array();
        if( is_array( $row ) ) {
            foreach($row as $col) {
                $d = (string)$col;
                // データがないフィールドを半角スペースに置き換える
                if( $this->templateHeader['NoDataFieldSettingFlg'] == 1 && strlen( $d ) == 0 ) {
                    $d = ' ';
                }
                $enclose = !empty( $this->templateHeader['EncloseValue'] ) ? $this->templateHeader['EncloseValue'] : '';
                if (is_numeric($d) && $d[0] != '0') {
                    $enclose = "";
                }
                $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $d ) ) . $enclose;
            }
            $delimiter = !empty( $this->templateHeader['DelimiterValue'] ) ? $this->templateHeader['DelimiterValue'] : ',';

            return mb_convert_encoding( join( $delimiter, $result ), $this->templateHeader['CharacterCode'] == '*' ? BaseIOUtility::ENCODING_WIN_SJIS : $this->templateHeader['CharacterCode'] );
        }
        return null;
    }
}
