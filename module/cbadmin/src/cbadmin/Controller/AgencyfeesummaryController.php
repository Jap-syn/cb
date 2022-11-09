<?php
namespace cbadmin\Controller;

use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use Coral\Base\IO\BaseIOCsvWriter;
use Coral\Base\IO\BaseIOUtility;
use models\Table\TableAgency;

class AgencyfeesummaryController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript( '../js/json+.js' );
        $this->addJavaScript( '../js/prototype.js' );
        $this->addJavaScript( '../js/corelib.js' );
        $this->addJavaScript( '../js/base.ui.js');

        $this->setPageTitle("後払い.com - 代理店手数料確認");
	}

	/**
	 * 代理店手数料確認一覧
	 */
	public function listAction()
	{
        $params = $this->getParams();

        $month = (isset($params['monthList'])) ? $params['monthList'] : null;
        $oem = (isset($params['oemList'])) ? $params['oemList'] : 0;
        $agency = (isset($params['agencyList'])) ? $params['agencyList'] : 0;

        // 対象年月
        $ri = $this->app->dbAdapter->query(" SELECT TargetMonth, DATE_FORMAT(TargetMonth, '%Y年%m月度') AS TargetMonthViewVal FROM T_AgencyFeeSummary GROUP BY TargetMonth ORDER BY TargetMonth DESC; ")->execute(null);
        $monthList = array();
        foreach ($ri as $row) {
            $monthList[$row['TargetMonth']] = $row['TargetMonthViewVal'];
            if (is_null($month)) {
                // [対象年月]未通知時、先頭レコードの内容を変数へ設定する
                $month = $monthList[$row['TargetMonth']];
            }
        }

        // OEMIDと名前のリスト取得
        $mdlOem = new \models\Table\TableOem($this->app->dbAdapter);
        $oemList = $mdlOem->getOemIdList();

        // 代理店リスト
        $sql  = " SELECT 0 AS AgencyId, '-----' AS AgencyNameKj ";
        $sql .= " UNION ALL ";
        $sql .= " SELECT a.AgencyId, a.AgencyNameKj FROM M_Agency a ";
        $sql .= " WHERE  1 = 1 ";
        if ($oem > 0) {
            $sql .= " AND a.OemId = " . $oem;
        }

        $ri = $this->app->dbAdapter->query($sql)->execute(null);
        $agencyList = array();
        foreach($ri as $row) {
            $agencyList[$row['AgencyId']] = $row['AgencyNameKj'];
        }

        $this->view->assign('monthListTag',BaseHtmlUtils::SelectTag("monthList", $monthList, $month, 'onChange="this.form.submit(); "'));
        $this->view->assign('oemListTag',BaseHtmlUtils::SelectTag("oemList", $oemList, $oem, 'onChange="this.form.submit(); "'));
        $this->view->assign('agencyListTag',BaseHtmlUtils::SelectTag("agencyList", $agencyList, $agency, 'onChange="this.form.submit(); "'));
        // (location.href関連)
        $dcsvlink = "";
        if (isset($params['monthList'] )) { $dcsvlink .= ("/monthList/" . $params['monthList']); }
        if (isset($params['oemList']   )) { $dcsvlink .= ("/oemList/" . $params['oemList']); }
        if (isset($params['agencyList'])) { $dcsvlink .= ("/agencyList/" . $params['agencyList']); }
        $this->view->assign('dcsvlink1', "agencyfeesummary/dcsv1" . $dcsvlink);
        $this->view->assign('dcsvlink2', "agencyfeesummary/dcsv2" . $dcsvlink);
        $this->view->assign('dcsvlink3', "agencyfeesummary/dcsv3" . $dcsvlink);

        // [対象年月]未通知時は、本段階で戻る
        if (!isset($params['monthList'])) {
            return $this->view;
        }

        // 検索条件による抽出
        $sql  = " SELECT afs.* ";
        $sql .= " ,      a.AgencyNameKj ";
        $sql .= " FROM   T_AgencyFeeSummary afs ";
        $sql .= "        INNER JOIN M_Agency a ON (a.AgencyId = afs.AgencyId) ";
        $sql .= " WHERE  1 = 1 ";

        $prms = array();

        // (検索条件：対象年月)
        if (isset($params['monthList'])) {
            $sql .= " AND afs.TargetMonth = :Month ";
            $prms = array(':Month' => $month);
        }
        // (検索条件：OEM)
        if ($oem > 0) {
            $sql .= " AND a.OemId = " . $oem;
        }
        // (検索条件：代理店)
        if ($agency > 0) {
            $sql .= " AND a.AgencyId = " . $agency;
        }

        $data = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute($prms));

        $this->view->assign('list',$data);

        return $this->view;
	}

	/**
	 * 振込ダウンロード
	 */
	public function dcsv1Action()
	{
	    // パラメータ取得
	    $params  = $this->getParams();
	    $month = (isset($params['monthList'])) ? $params['monthList'] : null;
	    $oem = (isset($params['oemList'])) ? $params['oemList'] : 0;
	    $agency = (isset($params['agencyList'])) ? $params['agencyList'] : 0;

	    // 振込データ取得
	    $sql  = " SELECT a.FfCode ";
	    $sql .= " ,      a.FfName ";
	    $sql .= " ,      a.FfBranchCode ";
	    $sql .= " ,      a.BranchName ";
	    $sql .= " ,      a.FfAccountClass ";
	    $sql .= " ,      a.AccountNumber ";
	    $sql .= " ,      a.AccountHolder ";
	    $sql .= " ,      afs.PaymentAmount ";
	    $sql .= " FROM   T_AgencyFeeSummary afs ";
	    $sql .= "        INNER JOIN M_Agency a ON (a.AgencyId = afs.AgencyId) ";
	    $sql .= " WHERE  1 = 1 ";
	    $sql .= " AND    afs.PaymentTargetClass = 1 ";     // 支払対象区分が対象のもの

	    $prm = array();
	    // (検索条件：対象年月)
	    if (isset($params['monthList'])) {
	        $sql .= " AND afs.TargetMonth = :Month ";
	        $prm[':Month'] = $month;
	    }
	    // (検索条件：OEM)
	    if ($oem > 0) {
	        $sql .= " AND a.OemId = :Oem ";
	        $prm[':Oem'] = $oem;
	    }
	    // (検索条件：代理店)
	    if ($agency > 0) {
	        $sql .= " AND afs.AgencyId = :Agency ";
	        $prm[':Agency'] = $agency;
	    }

	    $sql .= " ORDER BY afs.AgencyId ";

	    $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
	    $datas = ResultInterfaceToArray( $ri );

	    // ヘッダレコード
	    $headerRecords = array();
	    unset($rec);
	    $rec['DataClassification'] = 1;
	    $rec['IdentifyingCode'] = 21;
	    $rec['CodeDivision'] = 0;
	    $rec['ConsignorCode'] = 1848513200;
	    $rec['ConsignorName'] = 'ｶ)ｷｬｯﾁﾎﾞｰﾙ';
	    $rec['TransferDate'] = date('md');
	    $rec['SourceBankNumber'] = '0033';
	    $rec['SourceBankName'] = '';
	    $rec['SourceBranchNumber'] = '002';
	    $rec['SourceBranchName'] = '';
	    $rec['SourceDepositType'] = 1;
	    $rec['SourceAccountNumber'] = '3804573';
	    $rec['Dummy1'] = '';

	    $headerRecords[] = $rec;

	    // データレコード
	    $dataRecords = array();
	    $totalNumber = 0;          // 合計件数（トレーラーレコード用）
        $totalAmountOfMoney = 0;   // 合計金額（トレーラーレコード用）
	    foreach($datas as $data) {
	        // データレコード作成
	        unset($rec);
	        $rec['DataClassification'] = 2;                                // データ区分
	        $rec['PayeeBankNumber'] = $data['FfCode'];                     // 振込先金融機関番号
	        $rec['PayeeBankName'] = $data['FfName'];                       // 振込先金融機関名
	        $rec['PayeeBranchNumber'] = $data['FfBranchCode'];             // 振込先支店番号
	        $rec['PayeeBranchName'] = $data['BranchName'];                 // 振込先支店名
	        $rec['ClearingHouseNumber'] = '';                              // 手形交換所番号
	        $rec['PayeeDepositType'] = $data['FfAccountClass'];            // 振込先預金種目
	        $rec['PayeeAccountNumber'] = $data['AccountNumber'];           // 振込先口座番号
	        $rec['PayeeAccountName'] = $data['AccountHolder'];             // 振込先名義
	        $rec['TransferAmountOfMoney'] = $data['PaymentAmount'];        // 振込金額
	        $rec['NewCode'] = '';                                          // 新規コード
	        $rec['Dummy2'] = '';                                           // ダミー２
	        $rec['Dummy3'] = '';                                           // ダミー３
	        $rec['Dummy4'] = '';                                           // ダミー４
	        $rec['Dummy5'] = '';                                           // ダミー５
	        $rec['Dummy6'] = '';                                           // ダミー６

	        $dataRecords[] = $rec;

	        // 合計件数
	        $totalNumber++;

	        //合計金額
	        $totalAmountOfMoney += isset($data['PaymentAmount']) ? $data['PaymentAmount'] : 0;
	    }

	    // トレーラーレコード
	    $trallerRecords = array();
	    unset($rec);
	    $rec['DataClassification'] = 8;
	    $rec['TotalNumber'] = $totalNumber;
	    $rec['TotalAmountOfMoney'] = $totalAmountOfMoney;
	    $rec['Dummy7'] = '';

	    $trallerRecords[] = $rec;

	    // エンドレコード
	    $endRecords = array();
	    unset($rec);
	    $rec['DataClassification'] = 9;
  	    $rec['Dummy8'] = '';

  	    $endRecords[] = $rec;

  	    //全データ
  	    $fileDatas = array_merge($headerRecords, $dataRecords, $trallerRecords, $endRecords);

  	    // ファイル名
	    $filename = sprintf( 'TransferData_OEM%s_%s.csv', $oem, date('YmdHis') );

	    // CSV出力
  	    $response = $this->convertArraytoResponse( $fileDatas, $filename, $this->getResponse() );

  	    return $response;
	}

	/**
	 * 代理店別CSVダウンロード
	 */
	public function dcsv2Action()
	{
	    // パラメータ取得
	    $params  = $this->getParams();
	    $month = (isset($params['monthList'])) ? $params['monthList'] : null;
	    $oem = (isset($params['oemList'])) ? $params['oemList'] : 0;
	    $agency = (isset($params['agencyList'])) ? $params['agencyList'] : 0;

	    // 代理店別CSVデータ取得
	    $sql  = " SELECT DATE_FORMAT(afs.TargetMonth, '%Y%m') AS TargetMonth ";
	    $sql .= " ,      a.OemId ";
	    $sql .= " ,      o.OemNameKj AS OemName ";
	    $sql .= " ,      (@no:=@no+1) AS No ";
	    $sql .= " ,      a.AgencyId ";
	    $sql .= " ,      a.AgencyNameKj ";
	    $sql .= " ,      afs.EnterpriseCount ";
	    $sql .= " ,      afs.EnterpriseSalesAmount ";
	    $sql .= " ,      afs.AgencyFee ";
	    $sql .= " ,      afs.CarryOverTC AS CarryOver ";
	    $sql .= " ,      afs.SubTotal AS TotalAgencyFee ";
	    $sql .= " ,      (afs.SubTotal - afs.PaymentAmount) AS TransferCommission ";
	    $sql .= " ,      CASE WHEN PaymentTargetClass = 0 THEN 0 ELSE afs.PaymentAmount END AS DecisionPayment ";
	    $sql .= " ,      CASE WHEN PaymentTargetClass = 0 THEN afs.SubTotal ELSE 0 END AS NextCarryOver ";
	    $sql .= " ,      afs.MonthlyFee ";
	    $sql .= " FROM   (SELECT @no:=0) AS number ";
	    $sql .= " ,      T_AgencyFeeSummary afs ";
	    $sql .= "        INNER JOIN M_Agency a ON (a.AgencyId = afs.AgencyId) ";
	    $sql .= "        LEFT JOIN T_Oem o ON (o.OemId = a.oemId) ";
	    $sql .= " WHERE  1 = 1 ";

	    $prm = array();
	    // (検索条件：対象年月)
	    if (isset($params['monthList'])) {
	        $sql .= " AND afs.TargetMonth = :Month ";
	        $prm[':Month'] = $month;
	    }
	    // (検索条件：OEM)
	    if ($oem > 0) {
	        $sql .= " AND a.OemId = :Oem ";
	        $prm[':Oem'] = $oem;
	    }
	    // (検索条件：代理店)
	    if ($agency > 0) {
	        $sql .= " AND afs.AgencyId = :Agency ";
	        $prm[':Agency'] = $agency;
	    }

	    $sql .= " ORDER BY afs.AgencyId ";

	    $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
	    $datas = ResultInterfaceToArray( $ri );

	    // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
	    $templateId = 'CKI14105_2';    // テンプレートID       代理店手数料CSV
	    $templateClass = 0;            // 区分                 CB
	    $seq = 0;                      // シーケンス           CB
	    $templatePattern = 0;          // テンプレートパターン

	    $logicTemplate = new \models\Logic\LogicTemplate( $this->app->dbAdapter );
	    $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'AgencyFee_OEM%s_%s.csv', $oem, date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );

	    if( $response == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $response;
	}

    /**
     * 加盟店別注文別CSVダウンロード
     */
    public function dcsv3Action()
    {
        // パラメータ取得
        $params  = $this->getParams();
        $month = (isset($params['monthList'])) ? $params['monthList'] : null;
        $oem = (isset($params['oemList'])) ? $params['oemList'] : 0;
        $agency = (isset($params['agencyList'])) ? $params['agencyList'] : 0;

        // 加盟店別注文別CSVデータ取得
        // 加盟店側
        $sql  = " SELECT DATE_FORMAT(af.AddUpFixedMonth, '%Y%m') AS TargetMonth ";
        $sql .= " ,      a.OemId ";
        $sql .= " ,      o.OemNameKj AS OemName ";
        $sql .= " ,      a.AgencyId ";
        $sql .= " ,      a.AgencyNameKj ";
        $sql .= " ,      e.EnterpriseId ";
        $sql .= " ,      e.EnterpriseNameKj AS EnterpriseName ";
        $sql .= " ,      od.OrderId ";
        $sql .= " ,      od.ReceiptOrderDate ";
        $sql .= " ,      cr.ReceiptDate AS ReceiptProcessDate ";
        $sql .= " ,      af.UseAmount ";
        $sql .= " ,      af.AgencyFeeRate ";
        $sql .= " ,      af.AgencyDivideFeeRate ";
        $sql .= " ,      af.AgencyFee ";
        $sql .= " ,      sit.SiteNameKj ";
        $sql .= " FROM   T_AgencyFee af ";
        $sql .= "        INNER JOIN M_Agency a ON (a.AgencyId = af.AgencyId) ";
        $sql .= "        LEFT JOIN T_Oem o ON (o.OemId = a.oemId) ";
        $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = af.EnterpriseId) ";
        $sql .= "        INNER JOIN T_Order od ON (od.OrderSeq = af.OrderSeq) ";
        $sql .= "        INNER JOIN T_Site sit ON (sit.SiteId = od.SiteId) ";
        $sql .= "        LEFT JOIN V_CloseReceiptControl cr ON (cr.OrderSeq = od.P_OrderSeq) ";
        $sql .= "        LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = od.P_OrderSeq) ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= "   AND  af.AddUpFlg = 1 ";

        $prm = array();
        // (検索条件：対象年月)
        if (isset($params['monthList'])) {
            $sql .= " AND af.AddUpFixedMonth = :Month ";
            $prm[':Month'] = $month;
        }
        // (検索条件：OEM)
        if ($oem > 0) {
            $sql .= " AND a.OemId = :Oem ";
            $prm[':Oem'] = $oem;
        }
        // (検索条件：代理店)
        if ($agency > 0) {
            $sql .= " AND af.AgencyId = :Agency ";
            $prm[':Agency'] = $agency;
        }
        // OEM側
        $sql .= " UNION ALL ";
        $sql .= " SELECT DATE_FORMAT(oaf.AddUpFixedMonth, '%Y%m') AS TargetMonth ";
        $sql .= " ,      a.OemId ";
        $sql .= " ,      o.OemNameKj AS OemName ";
        $sql .= " ,      a.AgencyId ";
        $sql .= " ,      a.AgencyNameKj ";
        $sql .= " ,      e.EnterpriseId ";
        $sql .= " ,      e.EnterpriseNameKj AS EnterpriseName ";
        $sql .= " ,      od.OrderId ";
        $sql .= " ,      od.ReceiptOrderDate ";
        $sql .= " ,      cr.ReceiptDate AS ReceiptProcessDate ";
        $sql .= " ,      oaf.UseAmount ";
        $sql .= " ,      oaf.AgencyFeeRate ";
        $sql .= " ,      oaf.AgencyDivideFeeRate ";
        $sql .= " ,      oaf.AgencyFee ";
        $sql .= " ,      sit.SiteNameKj ";
        $sql .= " FROM   T_OemAgencyFee oaf ";
        $sql .= "        INNER JOIN M_Agency a ON (a.AgencyId = oaf.AgencyId) ";
        $sql .= "        LEFT JOIN T_Oem o ON (o.OemId = a.oemId) ";
        $sql .= "        INNER JOIN T_Enterprise e ON (e.EnterpriseId = oaf.EnterpriseId) ";
        $sql .= "        INNER JOIN T_Order od ON (od.OrderSeq = oaf.OrderSeq) ";
        $sql .= "        INNER JOIN T_Site sit ON (sit.SiteId = od.SiteId) ";
        $sql .= "        LEFT JOIN V_CloseReceiptControl cr ON (cr.OrderSeq = od.P_OrderSeq) ";
        $sql .= "        LEFT OUTER JOIN T_ClaimControl cc ON (cc.OrderSeq = od.P_OrderSeq) ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= "   AND  oaf.AddUpFlg = 1 ";

        $prm = array();
        // (検索条件：対象年月)
        if (isset($params['monthList'])) {
            $sql .= " AND oaf.AddUpFixedMonth = :Month ";
            $prm[':Month'] = $month;
        }
        // (検索条件：OEM)
        if ($oem > 0) {
            $sql .= " AND a.OemId = :Oem ";
            $prm[':Oem'] = $oem;
        }
        // (検索条件：代理店)
        if ($agency > 0) {
            $sql .= " AND oaf.AgencyId = :Agency ";
            $prm[':Agency'] = $agency;
        }

        $sql .= " ORDER BY AgencyId ";
        $sql .= " ,        EnterpriseId ";
        $sql .= " ,        EnterpriseName ";
        $sql .= " ,        OrderId ";

        $ri = $this->app->dbAdapter->query( $sql )->execute( $prm );
        $datas = ResultInterfaceToArray( $ri );

        // テンプレートヘッダー/フィールドマスタを使用して、CSVをResposeに入れる
        $templateId = 'CKI14105_3';    // テンプレートID       代理店注文別手数料CSV
        $templateClass = 0;            // 区分                 CB
        $seq = 0;                      // シーケンス           CB
        $templatePattern = 0;          // テンプレートパターン

        $logicTemplate = new \models\Logic\LogicTemplate( $this->app->dbAdapter );

        // (検索条件：代理店)
        if($agency > 0) {
            $mdlAgency = new TableAgency($this->app->dbAdapter);
            $agencyList = $mdlAgency->find($agency)->current();
            $agencyName = $agencyList['AgencyNameKj'];

            $response = $logicTemplate->JPNconvertArraytoResponse( $datas, sprintf( '%s年%s月分%s様_代理店手数料明細.csv', date('Y', strtotime($month)), date('m', strtotime($month)), $agencyName ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );
        }else {
            $response = $logicTemplate->convertArraytoResponse( $datas, sprintf( 'EnterpriseOrderAgencyFee_OEM%s_%s.csv', $oem, date('YmdHis') ), $templateId, $templateClass, $seq, $templatePattern, $this->getResponse() );
        }

        if( $response == false ) {
            throw new \Exception( $logicTemplate->getErrorMessage() );
        }

        return $response;
    }

	/**
	 * 配列をレスポンス書き込み
	 *
	 * @param array $datas 配列
	 * @param string $fileName ファイル名
	 * @param Response $response 書き込んだレスポンス
	 * @return Response|bool 書き込んだレスポンス or 処理失敗の場合、false
	 */
	private function convertArraytoResponse($datas, $fileName, $response)
	{
	    // CSV出力
	    $this->writeCsv( $datas, $fileName, $response );

	    return $response;
	}

	/**
	 * 現在の設定でCSV出力を開始する
	 *
	 * @param array $datas 出力データ
	 * @param string $fileName 出力データに設定するファイル名(ローカルパスを指定した場合はそのファイルパスに出力)
	 * @param ResponseInterface $response 出力オブジェクト
	 */
	private function writeCsv($datas, $fileName, $response)
	{
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
	private function encodeRow($row) {
	    $noDataFieldSettingFlg = 0;
	    $encloseValue = '"';
	    $delimiterValue = ',';
	    $characterCode = '*';

	    $result = array();
	    foreach($row as $col) {
	        $d = (string)$col;
	        // データがないフィールドを半角スペースに置き換える
	        if( $noDataFieldSettingFlg == 1 && strlen( $d ) == 0 ) {
	            $d = ' ';
	        }
	        $enclose = !empty( $encloseValue ) ? $encloseValue : '"';
	        $result[] = $enclose . str_replace("\\'", "'", preg_replace( '/' . $enclose . '/', $enclose . $enclose, $d ) ) . $enclose;
	    }
	    $delimiter = !empty( $delimiterValue ) ? $delimiterValue : ',';

	    return mb_convert_encoding( join( $delimiter, $result ), $characterCode == '*' ? BaseIOUtility::ENCODING_SJIS : $characterCode );
	}
}
