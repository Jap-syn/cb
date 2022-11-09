<?php
namespace cbadmin\Controller;

use Zend\Json\Json;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\Controller\CoralControllerAction;
use cbadmin\Application;
use Coral\Base\BaseHtmlUtils;
use models\Table\TableOrder;
use Coral\Coral\CoralCodeMaster;
use oemmember\Controller\AccountController;
use models\Logic\LogicTemplate;

class RwordercsvController extends CoralControllerAction
{
	protected $_componentRoot = './application/views/components';

	/**
	 * アプリケーションオブジェクト
	 * @var Application
	 */
	private $app;

	/**
	 * 更新対象外の明示定数
	 * @var unknown
	 */
	const NO_UPDATE_VALUE = '99';

	const SESSION_JOB_PARAMS = 'RWORDERCSV_PARAMS';

	/**
	 * Controllerを初期化する
	 */
	public function _init()
	{
        $this->app = Application::getInstance();
        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

        $this->addStyleSheet('../css/default02.css');
        $this->addJavaScript('../js/prototype.js');

        $this->setPageTitle("後払い.com - 備考コメント一括登録");

        $this->view->assign('NO_UPDATE_VALUE', self::NO_UPDATE_VALUE);

	}



    /**
     * 備考コメント一括登録フォーム
     * @return \Coral\Coral\View\CoralViewModel
     */
    public function formAction()
	{
	    $fileClass = BaseHtmlUtils::SelectTag('fileClass',array(1 => '一括更新', 2 => '備考のみ更新'));
	    $this->view->assign('fileClass', $fileClass);
	    return $this->view;
	}

    /**
     * 一括更新ファイルインポート処理
     * @throws \Exception
     * @return \Coral\Coral\View\CoralViewModel
     */
	public function confirm1Action()
	{
	    $mdlo = new TableOrder($this->app->dbAdapter);
	    $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

	    // 区分による定義
	    $param = $this->getParams();
	    $fileClass = $param['fileClass'];

	    $tmpName = $_FILES["cres"]["tmp_name"];

	    $listData = array();
	    $cntSumLine = 0;    // 総件数(ヘッダ込みなので-1)
	    $cntErrLine = 0;    // エラー件数
	    $cntNrmLine = 0;    // 総件数 - エラー件数 = 正常件数

	    $handle = null;
	    try {
	        // ユーザーIDの取得
	        $obj = new \models\Table\TableUser($this->app->dbAdapter);
	        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

	        if ($tmpName == '') {
	            $this->view->assign('message', '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />ファイルを選択してください。</span>');
	            $this->view->assign('listData', $listData);
	            return $this->view;
	        }
	        $handle = fopen($tmpName, "r");

	        if (!$handle) {
	            // ファイルオープン失敗
	            $message = '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />再試行してください。</span>';
	        }
	        else {
	            $this->app->logger->info(' rwordercsv/confirm1Action start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
	            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

	            // ループ内変数初期化
	            $cntLine = 0;

	            $lstLetterClaimStopFlg = array(0 => '通常', 1 => 'ストップ', self::NO_UPDATE_VALUE => '更新しない');
	            $lstMailClaimStopFlg = array(0 => '通常', 1 => 'ストップ', self::NO_UPDATE_VALUE => '更新しない');
	            $lstVisitFlg = array(0 => '通常', 1 => '訪問済', self::NO_UPDATE_VALUE => '更新しない');

	            $lstFinalityCollectionMean = $codeMaster->getFinalityCollectionMeanMaster();
	            $lstFinalityCollectionMean[self::NO_UPDATE_VALUE] = '更新しない';

	            $lstRemindClass = $codeMaster->getRemindClassMaster();
	            $lstRemindClass[self::NO_UPDATE_VALUE] = '更新しない';

	            $lstValidTel = $codeMaster->getValidTelMaster();
	            $lstValidTel[self::NO_UPDATE_VALUE] = '更新しない';

	            $lstValidAddress = $codeMaster->getValidAddressMaster();
	            $lstValidAddress[self::NO_UPDATE_VALUE] = '更新しない';

	            $lstValidMail = $codeMaster->getValidMailMaster();
	            $lstValidMail[self::NO_UPDATE_VALUE] = '更新しない';

	            // ファイル解析ループ
	            setlocale(LC_ALL,'ja_JP.UTF-8');
	            while (($data = $this->fgetcsv_reg($handle, 5000, ",")) !== false) {

	                // 現在行をカウント(1からスタート！)
	                $cntLine += 1;

	                // 1行目のヘッダ行はスキップ
	                if ($cntLine <= 1) {
	                    continue;
					}

					// count関数対策
					$dataLen = 0;
					if(!empty($data)) {
						$dataLen = count($data);
					}

	                // ファイルの列数が異なる場合はエラー
	                if ($dataLen != 14) {
	                    throw new \Exception('ファイルレイアウトが異なります');
	                }

	                // --------------------------------
	                // 行データ取得
	                // --------------------------------
	                $row = array();
                    $row['FileKbn']                 = nvl($data[0], ''); // ファイル区分
                    $row['OrderId']                 = nvl($data[1], ''); // 注文ID
                    $row['Incre_Note']              = mb_convert_encoding(nvl($data[2], ''), "UTF-8", "SJIS-win"); // 備考
                    $row['LetterClaimStopFlg']      = nvl($data[3], '0'); // 紙ストップ区分
                    $row['MailClaimStopFlg']        = nvl($data[4], '0'); // メールストップ区分
                    $row['ClaimStopReleaseDate']    = nvl($data[5], ''); // 請求ストップ解除日
                    $row['PromPayDate']             = nvl($data[6], ''); // 支払約束日
                    $row['VisitFlg']                = nvl($data[7], '0'); // 訪問済処理区分
                    $row['FinalityCollectionMean']  = nvl($data[8], '0'); // 最終回収手段
                    $row['FinalityRemindDate']      = nvl($data[9], ''); // 最終督促日
                    $row['RemindClass']             = nvl($data[10], '0'); // 督促分類
                    $row['ValidTel']                = nvl($data[11], '0'); // TEL有効
                    $row['ValidAddress']            = nvl($data[12], '0'); // 住所有効
                    $row['ValidMail']               = nvl($data[13], '0'); // メール有効

                    $row['RowNo'] = $cntLine; // 行番号
                    $row['Error'] = ''; // エラー情報

	                // --------------------------------
	                // チェック及び項目追加
	                // --------------------------------
	                // ファイル区分
	                if ($row['FileKbn'] != '1') {
	                    $row['Error'] = 'ファイル区分に無効な値が入力されています';
	                    goto LABEL_ERROR;
	                }

	                // 注文ID
	                $rowOrder = $mdlo->findOrder(array('OrderId' => $row['OrderId']))->current();
	                if (!$rowOrder) {
	                    $row['Error'] = '注文IDが特定出来ません';
	                    goto LABEL_ERROR;
	                }

	                // 取れたデータを事前項目として設定
	                $row['OrderSeq'] = $rowOrder['OrderSeq'];

	                // 備考
	                $row['CaptionIncre_Note'] = $row['Incre_Note'];
	                if (strlen($row['Incre_Note']) == 0) {
	                    $row['CaptionIncre_Note'] = '更新しない';
	                }

	                // 紙ストップ区分
                    if (!isset($lstLetterClaimStopFlg[$row['LetterClaimStopFlg']])) {
                        $row['Error'] = '紙ストップ区分に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionLetterClaimStopFlg'] = $lstLetterClaimStopFlg[$row['LetterClaimStopFlg']];

	                // メールストップ区分
                    if (!isset($lstMailClaimStopFlg[$row['MailClaimStopFlg']])) {
                        $row['Error'] = 'メールストップ区分に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionMailClaimStopFlg'] = $lstMailClaimStopFlg[$row['MailClaimStopFlg']];

	                // 請求ストップ解除日
                    if (strlen($row['ClaimStopReleaseDate']) > 0 && $row['ClaimStopReleaseDate'] <> self::NO_UPDATE_VALUE) {
                        if (!IsValidDate($row['ClaimStopReleaseDate'])) {
                            $row['Error'] = '請求ストップ解除日に無効な値が入力されています';
                            goto LABEL_ERROR;
                        }
                        $row['ClaimStopReleaseDate'] = date('Y-m-d', strtotime($row['ClaimStopReleaseDate']));
                    }
                    $row['CaptionClaimStopReleaseDate'] = ($row['ClaimStopReleaseDate'] == self::NO_UPDATE_VALUE) ? '更新しない' : $row['ClaimStopReleaseDate'];

	                // 支払約束日
                    if (strlen($row['PromPayDate']) > 0 && $row['PromPayDate'] <> self::NO_UPDATE_VALUE) {
                        if (!IsValidDate($row['PromPayDate'])) {
                            $row['Error'] = '支払約束日に無効な値が入力されています';
                            goto LABEL_ERROR;
                        }
                        $row['PromPayDate'] = date('Y-m-d', strtotime($row['PromPayDate']));
                    }
                    $row['CaptionPromPayDate'] = ($row['PromPayDate'] == self::NO_UPDATE_VALUE) ? '更新しない' : $row['PromPayDate'];

	                // 訪問済処理区分
                    if (!isset($lstVisitFlg[$row['VisitFlg']])) {
                        $row['Error'] = '訪問済処理区分に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionVisitFlg'] = $lstVisitFlg[$row['VisitFlg']];

	                // 最終回収手段
                    if (!isset($lstFinalityCollectionMean[$row['FinalityCollectionMean']])) {
                        $row['Error'] = '最終回収手段に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionFinalityCollectionMean'] = $lstFinalityCollectionMean[$row['FinalityCollectionMean']];

	                // 最終督促日
                    if (strlen($row['FinalityRemindDate']) > 0 && $row['FinalityRemindDate'] <> self::NO_UPDATE_VALUE) {
                        if (!IsValidDate($row['FinalityRemindDate'])) {
                            $row['Error'] = '最終督促日に無効な値が入力されています';
                            goto LABEL_ERROR;
                        }
                        $row['FinalityRemindDate'] = date('Y-m-d', strtotime($row['FinalityRemindDate']));
                    }
                    $row['CaptionFinalityRemindDate'] = ($row['FinalityRemindDate'] == self::NO_UPDATE_VALUE) ? '更新しない' : $row['FinalityRemindDate'];

	                // 督促分類
                    if (!isset($lstRemindClass[$row['RemindClass']])) {
                        $row['Error'] = '督促分類に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionRemindClass'] = $lstRemindClass[$row['RemindClass']];

	                // TEL有効
                    if (!isset($lstValidTel[$row['ValidTel']])) {
                        $row['Error'] = 'TEL有効に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionValidTel'] = $lstValidTel[$row['ValidTel']];

	                // 住所有効
                    if (!isset($lstValidAddress[$row['ValidAddress']])) {
                        $row['Error'] = '住所有効に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionValidAddress'] = $lstValidAddress[$row['ValidAddress']];

	                // メール有効
                    if (!isset($lstValidMail[$row['ValidMail']])) {
                        $row['Error'] = 'メール有効に無効な値が入力されています';
                        goto LABEL_ERROR;
                    }
                    $row['CaptionValidMail'] = $lstValidMail[$row['ValidMail']];

                    goto LABEL_NEXT;

LABEL_ERROR:
                    // エラー件数をカウントアップ
                    $cntErrLine++;

LABEL_NEXT:
                    // 表示用配列に上乗せ
                    $listData[] = $row;

	            }

	            // ファイルクローズ
	            fclose($handle);

	            $cntSumLine = $cntLine - 1;  // 総件数(ヘッダ込みなので-1)
	            $cntNrmLine = $cntSumLine - $cntErrLine; // 総件数 - エラー件数 = 正常件数

 	            $log = sprintf("ファイル　「%s」　の解析が完了しました。<BR>（対象件数：%d件　取込件数：%d件　エラー件数：%d件）", $_FILES["cres"]["name"], $cntSumLine, $cntNrmLine, $cntErrLine);
	            $this->app->logger->info(' rwordercsv/confirm1Action completed(' . $log . ') ');
	        }
	    } catch(\Exception $e) {
	        $message = '<span style="font-size: 18px; color: red;">' . $e->getMessage() . '</span>';
	        $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
	        $this->app->logger->info(' rwordercsv/confirm1Action error(' . $message . ') ');
	        // (初期化処理)
	        if ($handle) { fclose($handle); }
	    }

	    $this->view->assign('message', $message);
	    $this->view->assign('fileName',  $_FILES["cres"]["name"]);
	    $this->view->assign('sumCount', $cntSumLine);
	    $this->view->assign('nrmCount', $cntNrmLine);
	    $this->view->assign('errCount', $cntErrLine);
	    $this->view->assign('listData', $listData);


	    return $this->view;
	}

	/**
	 * 備考ファイルインポート処理
	 * @throws \Exception
	 * @return \Coral\Coral\View\CoralViewModel
	 */
	public function confirm2Action()
	{
	    $mdlo = new TableOrder($this->app->dbAdapter);
	    $codeMaster = new CoralCodeMaster($this->app->dbAdapter);

	    // 区分による定義
	    $param = $this->getParams();
	    $fileClass = $param['fileClass'];

	    $tmpName = $_FILES["cres"]["tmp_name"];

	    $listData = array();
	    $cntSumLine = 0;    // 総件数(ヘッダ込みなので-1)
	    $cntErrLine = 0;    // エラー件数
	    $cntNrmLine = 0;    // 総件数 - エラー件数 = 正常件数

	    $handle = null;
	    try {
	        // ユーザーIDの取得
	        $obj = new \models\Table\TableUser($this->app->dbAdapter);
	        $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

	        if ($tmpName == '') {
	            $this->view->assign('message', '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />ファイルを選択してください。</span>');
	            $this->view->assign('listData', $listData);
	            return $this->view;
	        }
	        $handle = fopen($tmpName, "r");

	        if (!$handle) {
	            // ファイルオープン失敗
	            $message = '<span style="font-size: 18px; color: red;">CSVファイルのオープンに失敗しました。<br />再試行してください。</span>';
	        }
	        else {
	            $this->app->logger->info(' rwordercsv/confirm2Action start(' . $_FILES["cres"]["name"] . ' / filesize : ' . filesize($tmpName) . ') ');
	            $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();

	            // ループ内変数初期化
	            $cntLine = 0;

	            // ファイル解析ループ
	            setlocale(LC_ALL,'ja_JP.UTF-8');
	            while (($data = $this->fgetcsv_reg($handle, 5000, ",")) !== false) {

	                // 現在行をカウント(1からスタート！)
	                $cntLine += 1;

	                // 1行目のヘッダ行はスキップ
	                if ($cntLine <= 1) {
	                    continue;
					}

					// count関数対策
					$dataLen = 0;
					if(!empty($data)) {
						$dataLen = count($data);
					}

	                // ファイルの列数が異なる場合はエラー
	                if ($dataLen != 3) {
	                    throw new \Exception('ファイルレイアウトが異なります');
	                }

	                // --------------------------------
	                // 行データ取得
	                // --------------------------------
	                $row = array();
	                $row['FileKbn']                 = nvl($data[0], ''); // ファイル区分
	                $row['OrderId']                 = nvl($data[1], ''); // 注文ID
	                $row['Incre_Note']              = mb_convert_encoding(nvl($data[2], ''), "UTF-8", "SJIS-win"); // 備考

	                $row['RowNo'] = $cntLine; // 行番号
	                $row['Error'] = ''; // エラー情報

	                // --------------------------------
	                // チェック及び項目追加
	                // --------------------------------
	                // ファイル区分
	                if ($row['FileKbn'] != '2') {
	                    $row['Error'] = 'ファイル区分に無効な値が入力されています';
	                    goto LABEL_ERROR;
	                }

	                // 注文ID
	                $rowOrder = $mdlo->findOrder(array('OrderId' => $row['OrderId']))->current();
	                if (!$rowOrder) {
	                    $row['Error'] = '注文IDが特定出来ません';
	                    goto LABEL_ERROR;
	                }

	                // 取れたデータを事前項目として設定
	                $row['OrderSeq'] = $rowOrder['OrderSeq'];

	                // 備考
	                $row['CaptionIncre_Note'] = $row['Incre_Note'];
	                if (strlen($row['Incre_Note']) == 0) {
	                    $row['CaptionIncre_Note'] = '更新しない';
	                }

	                goto LABEL_NEXT;

LABEL_ERROR:
	                // エラー件数をカウントアップ
	                $cntErrLine++;

LABEL_NEXT:
	                // 表示用配列に上乗せ
	                $listData[] = $row;

	            }

	            // ファイルクローズ
	            fclose($handle);

	            $cntSumLine = $cntLine - 1;  // 総件数(ヘッダ込みなので-1)
	            $cntNrmLine = $cntSumLine - $cntErrLine; // 総件数 - エラー件数 = 正常件数

	            $log = sprintf("ファイル　「%s」　の解析が完了しました。<BR>（対象件数：%d件　取込件数：%d件　エラー件数：%d件）", $_FILES["cres"]["name"], $cntSumLine, $cntNrmLine, $cntErrLine);
	            $this->app->logger->info(' rwordercsv/confirm1Action completed(' . $log . ') ');
	        }
	    } catch(\Exception $e) {
	        $message = '<span style="font-size: 18px; color: red;">' . $e->getMessage() . '</span>';
	        $this->app->dbAdapter->getDriver()->getConnection()->rollBack();
	        $this->app->logger->info(' rwordercsv/confirm2Action error(' . $message . ') ');
	        // (初期化処理)
	        if ($handle) { fclose($handle); }
	    }

	    $this->view->assign('message', $message);
	    $this->view->assign('fileName',  $_FILES["cres"]["name"]);
	    $this->view->assign('sumCount', $cntSumLine);
	    $this->view->assign('nrmCount', $cntNrmLine);
	    $this->view->assign('errCount', $cntErrLine);
	    $this->view->assign('listData', $listData);


	    return $this->view;
	}

	/**
	 * 画面情報をセッションに保存
	 */
	public function jobparamsetAction()
	{
	    // セッションに情報をセットする
	    unset($_SESSION[self::SESSION_JOB_PARAMS]);
	    $_SESSION[self::SESSION_JOB_PARAMS] = $this->getParams();

	    return;
	}

	/**
	 * CSVﾀﾞｳﾝﾛｰﾄﾞ処理1
	 */
	public function csvoutput1Action()
	{
	    $response = $this->csvdownload('CKI01039_2');
        return $response;
	}

	/**
	 * CSVﾀﾞｳﾝﾛｰﾄﾞ処理2
	 */
	public function csvoutput2Action()
	{
	    $response = $this->csvdownload('CKI01039_1');
	    return $response;
	}

	/**
	 * ﾀﾞｳﾝﾛｰﾄﾞﾌｧｲﾙ作成
	 * @param string $templateId テンプレートID
	 * @throws \Exception
	 * @return string
	 */
	protected function csvdownload($templateId)
	{
	    $param = $_SESSION[self::SESSION_JOB_PARAMS];
	    unset($_SESSION[self::SESSION_JOB_PARAMS]);

	    // 注文SEQのリストを取得
	    $i = 0;
	    $arrOrderSeq = array();
	    while (isset($param['OrderSeq' . $i])) {
	        $arrOrderSeq[] = $param['OrderSeq' . $i];
	        $i++;
	    }

	    // カンマ区切りの文字列に変換
	    $oseqs = implode(',', $arrOrderSeq);

	    // ﾀﾞｳﾝﾛｰﾄﾞ対象リストを取得
	    $sql  = " SELECT o.OrderId ";
	    $sql .= "      , o.Incre_Note ";
	    $sql .= "      , o.LetterClaimStopFlg ";
	    $sql .= "      , o.MailClaimStopFlg ";
	    $sql .= "      , o.ClaimStopReleaseDate ";
	    $sql .= "      , o.PromPayDate ";
	    $sql .= "      , o.VisitFlg ";
	    $sql .= "      , o.FinalityCollectionMean ";
	    $sql .= "      , o.FinalityRemindDate ";
	    $sql .= "      , o.RemindClass ";
	    $sql .= "      , c.ValidTel ";
	    $sql .= "      , c.ValidAddress ";
	    $sql .= "      , c.ValidMail ";
	    $sql .= "  FROM T_Order o ";
	    $sql .= "       INNER JOIN T_Customer c ON o.OrderSeq = c.OrderSeq  ";
	    $sql .= " WHERE o.OrderSeq IN ($oseqs) ";
	    $datas = ResultInterfaceToArray($this->app->dbAdapter->query($sql)->execute(null));

	    // ﾀﾞｳﾝﾛｰﾄﾞを実施
	    $fileName = 'backup_order_' . date('YmdHis') . '.csv';
	    $templateClass = 0;
	    $seq = 0;
	    $templatePattern = 0;
	    $tmpFileName = $tmpFilePath . $fileName;

	    $logicTemplate = new LogicTemplate($this->app->dbAdapter);
// 	    $result = $logicTemplate->convertArraytoFile( $datas, $tmpFileName, $templateId, $templateClass, $seq, $templatePattern );
	    $result = $logicTemplate->convertArraytoResponse($datas, $fileName, $templateId, $templateClass, $seq, $templatePattern, $this->getResponse());
	    if( $result == false ) {
	        throw new \Exception( $logicTemplate->getErrorMessage() );
	    }

	    return $result;

	}

	/**
	 * CSV更新処理1
	 * @throws Exception
	 * @return \Coral\Coral\View\CoralViewModel
	 */
	public function save1Action()
	{
	    $param = $this->getParams();

	    // ユーザーIDの取得
	    $obj = new \models\Table\TableUser($this->app->dbAdapter);
	    $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

	    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
	    try {

	        $i = 0;
	        while (isset($param['OrderSeq' . $i])) {
                $oseq = $param['OrderSeq' . $i];

                // 更新前の値を取得
        	    $sql  = " SELECT o.OrderId ";
        	    $sql .= "      , o.Incre_Note ";
        	    $sql .= "      , o.LetterClaimStopFlg ";
        	    $sql .= "      , o.MailClaimStopFlg ";
        	    $sql .= "      , o.ClaimStopReleaseDate ";
        	    $sql .= "      , o.PromPayDate ";
        	    $sql .= "      , o.VisitFlg ";
        	    $sql .= "      , o.FinalityCollectionMean ";
        	    $sql .= "      , o.FinalityRemindDate ";
        	    $sql .= "      , o.RemindClass ";
        	    $sql .= "      , c.ValidTel ";
        	    $sql .= "      , c.ValidAddress ";
        	    $sql .= "      , c.ValidMail ";
        	    $sql .= "      , c.CustomerId ";
        	    $sql .= "  FROM T_Order o ";
        	    $sql .= "       INNER JOIN T_Customer c ON o.OrderSeq = c.OrderSeq  ";
        	    $sql .= " WHERE o.OrderSeq = :OrderSeq ";
        	    $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

        	    // 更新項目で上書き(99のデータは上書きしない)
        	    if (strlen($param['Incre_Note' . $i]) > 0) {
        	        $row['Incre_Note'] = $param['Incre_Note' . $i] . "\n" . $row['Incre_Note'];
        	    }
        	    if ($param['LetterClaimStopFlg' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['LetterClaimStopFlg'] = $param['LetterClaimStopFlg' . $i];
        	    }
        	    if ($param['MailClaimStopFlg' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['MailClaimStopFlg'] = $param['MailClaimStopFlg' . $i];
        	    }
        	    if ($param['ClaimStopReleaseDate' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['ClaimStopReleaseDate'] = null;
        	        if (strlen($param['ClaimStopReleaseDate' . $i]) > 0) {
        	            $row['ClaimStopReleaseDate'] = $param['ClaimStopReleaseDate' . $i];
        	        }
        	    }
        	    if ($param['PromPayDate' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['PromPayDate'] = null;
        	        if (strlen($param['PromPayDate' . $i]) > 0) {
        	            $row['PromPayDate'] = $param['PromPayDate' . $i];
        	        }
        	    }
        	    if ($param['VisitFlg' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['VisitFlg'] = $param['VisitFlg' . $i];
        	    }
        	    if ($param['FinalityCollectionMean' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['FinalityCollectionMean'] = $param['FinalityCollectionMean' . $i];
        	    }
        	    if ($param['FinalityRemindDate' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['FinalityRemindDate'] = null;
        	        if (strlen($param['FinalityRemindDate' . $i]) > 0) {
        	            $row['FinalityRemindDate'] = $param['FinalityRemindDate' . $i];
        	        }
        	    }
        	    if ($param['RemindClass' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['RemindClass'] = $param['RemindClass' . $i];
        	    }
        	    if ($param['ValidTel' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['ValidTel'] = $param['ValidTel' . $i];
        	    }
        	    if ($param['ValidAddress' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['ValidAddress'] = $param['ValidAddress' . $i];
        	    }
	            if ($param['ValidMail' . $i] != self::NO_UPDATE_VALUE) {
        	        $row['ValidMail'] = $param['ValidMail' . $i];
        	    }

        	    // 更新処理(注文)
                $sql  = " UPDATE T_Order ";
                $sql .= "    SET Incre_Note = :Incre_Note ";
                $sql .= "      , LetterClaimStopFlg = :LetterClaimStopFlg ";
                $sql .= "      , MailClaimStopFlg = :MailClaimStopFlg ";
                $sql .= "      , ClaimStopReleaseDate = :ClaimStopReleaseDate ";
                $sql .= "      , PromPayDate = :PromPayDate ";
                $sql .= "      , VisitFlg = :VisitFlg ";
                $sql .= "      , FinalityCollectionMean = :FinalityCollectionMean ";
                $sql .= "      , FinalityRemindDate = :FinalityRemindDate ";
                $sql .= "      , RemindClass = :RemindClass ";
                $sql .= "      , UpdateDate = :UpdateDate ";
                $sql .= "      , UpdateId = :UpdateId ";
                $sql .= "  WHERE  OrderSeq = :OrderSeq ";

                $prm = array(
                    ':Incre_Note' => $row['Incre_Note'],
                    ':LetterClaimStopFlg' => $row['LetterClaimStopFlg'],
                    ':MailClaimStopFlg' => $row['MailClaimStopFlg'],
                    ':ClaimStopReleaseDate' => $row['ClaimStopReleaseDate'],
                    ':PromPayDate' => $row['PromPayDate'],
                    ':VisitFlg' => $row['VisitFlg'],
                    ':FinalityCollectionMean' => $row['FinalityCollectionMean'],
                    ':FinalityRemindDate' => $row['FinalityRemindDate'],
                    ':RemindClass' => $row['RemindClass'],
                    ':UpdateDate' => date('Y-m-d H:i:s'),
                    ':UpdateId' => $userId,
                    ':OrderSeq' => $oseq,
                );
                $this->app->dbAdapter->query($sql)->execute($prm);

                // 更新処理(顧客)
                $sql  = " UPDATE T_Customer ";
                $sql .= "    SET ValidTel = :ValidTel ";
                $sql .= "      , ValidAddress = :ValidAddress ";
                $sql .= "      , ValidMail = :ValidMail ";
                $sql .= "      , UpdateDate = :UpdateDate ";
                $sql .= "      , UpdateId = :UpdateId ";
                $sql .= "  WHERE  CustomerId = :CustomerId ";
                $prm = array(
                        ':ValidTel' => $row['ValidTel'],
                        ':ValidAddress' => $row['ValidAddress'],
                        ':ValidMail' => $row['ValidMail'],
                        ':UpdateDate' => date('Y-m-d H:i:s'),
                        ':UpdateId' => $userId,
                        ':CustomerId' => $row['CustomerId'],
                );
                $this->app->dbAdapter->query($sql)->execute($prm);

	            $i++;
	        }

	        $this->app->dbAdapter->getDriver()->getConnection()->commit();

	    } catch (\Exception $e ) {
	        $this->app->dbAdapter->getDriver()->getConnection()->rollback();
	        throw $e;
	    }

	    $this->view->assign('fileName', $param['fileName']);
	    $this->view->assign('nrmCount', $param['nrmCount']);
	    return $this->view;

	}


	/**
	 * CSV更新処理2
	 * @throws Exception
	 * @return \Coral\Coral\View\CoralViewModel
	 */
	public function save2Action()
	{
	    $param = $this->getParams();

	    // ユーザーIDの取得
	    $obj = new \models\Table\TableUser($this->app->dbAdapter);
	    $userId = $obj->getUserId(0, $this->app->authManagerAdmin->getUserInfo()->OpId);

	    $this->app->dbAdapter->getDriver()->getConnection()->beginTransaction();
	    try {

	        $i = 0;
	        while (isset($param['OrderSeq' . $i])) {
	            $oseq = $param['OrderSeq' . $i];

	            // 更新前の値を取得
	            $sql  = " SELECT o.Incre_Note ";
	            $sql .= "  FROM T_Order o ";
	            $sql .= " WHERE o.OrderSeq = :OrderSeq ";
	            $row = $this->app->dbAdapter->query($sql)->execute(array(':OrderSeq' => $oseq))->current();

	            // 更新項目で上書き
	            if (strlen($param['Incre_Note' . $i]) > 0) {
	                $row['Incre_Note'] = $param['Incre_Note' . $i] . "\n" . $row['Incre_Note'];
	            }

	            // 更新処理(注文)
	            $sql  = " UPDATE T_Order ";
	            $sql .= "    SET Incre_Note = :Incre_Note ";
	            $sql .= "      , UpdateDate = :UpdateDate ";
	            $sql .= "      , UpdateId = :UpdateId ";
	            $sql .= "  WHERE  OrderSeq = :OrderSeq ";

	            $prm = array(
	                    ':Incre_Note' => $row['Incre_Note'],
	                    ':UpdateDate' => date('Y-m-d H:i:s'),
	                    ':UpdateId' => $userId,
	                    ':OrderSeq' => $oseq,
	            );
	            $this->app->dbAdapter->query($sql)->execute($prm);

	            $i++;
	        }

	        $this->app->dbAdapter->getDriver()->getConnection()->commit();

	    } catch (\Exception $e ) {
	        $this->app->dbAdapter->getDriver()->getConnection()->rollback();
	        throw $e;
	    }

	    $this->view->assign('fileName', $param['fileName']);
	    $this->view->assign('nrmCount', $param['nrmCount']);
	    return $this->view;


	}

	/**
	 * 20181029 ADD fgetcsvにバグがあるため、代替関数追加
	 * ファイルポインタから行を取得し、CSVフィールドを処理する
	 * @param resource handle
	 * @param int length
	 * @param string delimiter
	 * @param string enclosure
	 * @return ファイルの終端に達した場合を含み、エラー時にFALSEを返します。
	 */
	function fgetcsv_reg(&$handle,$length=NULL,$d=',',$e='"'){
	    $d=preg_quote($d);
	    $e=preg_quote($e);
	    $_line="";
	    $eof=false;
	    while(($eof!=true) && (!feof($handle))){
	        $_line.=(empty($length) ? fgets($handle) : fgets($handle,$length));
	        $itemcnt=preg_match_all('/'.$e.'/',$_line,$dummy);
	        if($itemcnt%2==0){
	            $eof=true;
	        }
	    }

	    $_csv_line=preg_replace('/(?:\\r\\n|[\\r\\n])?$/',$d,trim($_line));
	    $_csv_pattern='/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
	    preg_match_all($_csv_pattern,$_csv_line,$_csv_matches);
	    $_csv_data=$_csv_matches[1];
	    for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
	        $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
	        $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
	    }
	    return empty($_line) ? false : $_csv_data;
	}

}

