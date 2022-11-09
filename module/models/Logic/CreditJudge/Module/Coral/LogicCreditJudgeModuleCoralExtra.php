<?php
namespace models\Logic\CreditJudge\Module\Coral;

use Zend\Db\Adapter\Adapter;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Table\TableOrder;
use models\Table\TableEnterprise;
use models\Table\TableCreditJudgeThreshold;
use models\Table\TableCustomer;
use models\View\ViewOrderCustomer;
use models\Table\TableManagementCustomer;
use models\Table\TableCreditPoint;
use models\Table\TableCancel;
use models\Table\TableCreditCondition;
use models\Table\TableSite;
use models\Table\TableEnterpriseTotal;
use models\Table\TableSystemProperty;
use models\Logic\LogicNormalizer;
use models\Table\TableCreditSystemInfo;
use models\Table\TableOrderSummary;
use models\Table\TableCreditOkTicket;
use models\Table\TableOrderNotClose;

/**
 * 後払い.com独自の基準による追加与信モジュール
 * 過去の取引実績によるスコアリングを行う
 * judgeメソッドが返す可能性がある値：
 * 		JUDGE_RESULT_NG：与信NG確定
 * 		JUDGE_RESULT_OK：与信OK確定
 * 		JUDGE_RESULT_PENDING：与信保留確定（＝手動与信対象）
 * 		JUDGE_RESULT_CONTINUE：審査継続
 */
class LogicCreditJudgeModuleCoralExtra extends LogicCreditJudgeAbstract {
    /**
     *  与信時注文利用額フラグ
     */
     protected $debugUserAmountOver = 0;

     /**
      * 与信時注文利用額フラグ デフォルト値
      */
     protected $defaultUserAmountOver = 0;

    /**
	 * T_Orderモデル
     * @access protected
     * @var TableOrder
     */
	protected $mdlo;

    /**
	 * T_OrderNotCloseモデル
     * @access protected
     * @var TableOrderNotClose
     */
	protected $mdlonc;

	/**
	 * T_CreditPointモデル
	 * @access protected
	 * @var TableCreditPoint
	 */
	protected $mdlcp;

	/**
	 * 備考欄書式による加算処理対象の事業者IDのリスト
	 * @access protected
	 * @var array
	 */
	protected $_entIds = array();

	/**
	 * リアルタイム与信を行うかのフラグ値（廃止予定）
	 * @access protected
	 * @var boolean
	 */
	protected $_realTimeCjFlg = false;

	/**
	 * 処理中注文の過去取引に対する注文SEQのリスト
	 * @access protected
	 * @var array
	 */
	protected $_pastOrderSeqs = array();

	/**
	 * 与信判定基準ID
	 *
	 * @access protected
	 * @var int
	 */
	protected $_creditCriterionId;

	/**
	 * 自動化期間判定結果
	 *
	 * @var bool
	 */
	protected $_autoFlg;

	/**
	 * スコアリング結果
	 */
	protected $_resultArray;

	/**
	 * 与信判定結果
	 *
	 * @var int
	 */
	protected $_judgeResult;

	/**
	 * 与信対象項目の正規化設定
	 * @access protected
	 * @var array
	 */
	protected $map = array(
	        '1' => LogicNormalizer::FILTER_FOR_ADDRESS,
	        '2' => LogicNormalizer::FILTER_FOR_NAME,
	        '3' => LogicNormalizer::FILTER_FOR_ITEM_NAME,
	        '4' => LogicNormalizer::FILTER_FOR_MAIL,
	        '5' => LogicNormalizer::FILTER_FOR_ID,
	        //'6' => LogicNormalizer::FILTER_FOR_ADDRESS,
	        //'7' => LogicNormalizer::FILTER_FOR_ID,
	        '8' => LogicNormalizer::FILTER_FOR_TEL,
	        '9' => LogicNormalizer::FILTER_FOR_MONEY,
	);

	/**
	 * ログ出力用
	 *
	 * @var array
	 */
	protected $judgeMap = array(
	   self::JUDGE_RESULT_OK => 'OK',
	   self::JUDGE_RESULT_NG => 'NG',
	   self::JUDGE_RESULT_PENDING => 'PENDING',
	   self::JUDGE_RESULT_CONTINUE => 'CONTINUE',
	);

	/**
	 * LogicCreditJudgeModuleCoralExtraの新しいインスタンスを初期化する
	 *
	 * @param Adapter $adapter アダプタ
	 * @param null | array $options 追加オプション
	 */
	public function __construct(Adapter $adapter, array $options = array()) {
		$this->clearEnterpriseIds();
        parent::__construct($adapter, $options);

		$this->mdlo = new TableOrder($this->_adapter);
		$this->mdlcp = new TableCreditPoint($this->_adapter);
		$this->mdlonc = new TableOrderNotClose($this->_adapter);

        //デバッグフラグ取得
        $this->debugUserAmountOver = isset($options['user_amount_over'])?$options['user_amount_over']:$this->defaultUserAmountOver;

        $this->_resultArray = array();
	}

	/**
	 * 備考欄の得点加算書式による与信スコア加算対象の事業者IDをすべて取得する
	 *
	 * @return array
	 */
	public function getEnterpriseIds() {
		return $this->_entIds;
	}
	/**
	 * 備考欄の得点加算書式による与信スコア加算対象の事業者IDを新規に設定する
	 *
	 * @param array $entIds 事業者IDのリスト
	 * @return LogicCreditJudgeModuleCoralExtra このインスタンス
	 */
	public function setEnterpriseIds(array $entIds = array()) {
		if(!is_array($entIds)) $entIds = array();
		$this->_entIds = $entIds;
		return $this;
	}
	/**
	 * 備考欄の得点加算書式による与信スコア加算対象の事業者IDをクリアする
	 *
	 * @return LogicCreditJudgeModuleCoralExtra このインスタンス
	 */
	public function clearEnterpriseIds() {
		return $this->setEnterpriseIds();
	}
	/**
	 * 備考欄の得点加算書書式による与信スコア加算対象の事業者IDに指定のリストをマージする
	 *
	 * @param array $entIds 事業者IDのリスト
	 * @return LogicCreditJudgeModuleCoralExtra このインスタンス
	 */
	public function mergeEnterpriseIds(array $entIds = array()) {
		if(!is_array($entIds)) $entIds = array();
		foreach($entIds as $entId) {
			$this->addEnterpriseId($entId);
		}
		return $this;
	}
	/**
	 * 備考欄の得点加算書式による与信スコア加算対象の事業者IDを追加する
	 *
	 * @param mixed $entId 事業者ID
	 * @return LogicCreditJudgeModuleCoralExtra このインスタンス
	 */
	public function addEnterpriseId($entId) {
		if(!in_array($entId, $this->_entIds)) {
			$this->_entIds[] = $entId;
		}
		return $this;
	}
	/**
	 * 備考欄の得点加算書式による与信スコア加算対象の事業者IDリストから
	 * 指定の事業者IDを削除する
	 *
	 * @param mixed $entId 事業者ID
	 * @return LogicCreditJudgeModuleCoralExtra このインスタンス
	 */
	public function removeEnterpriseId($entId) {
		$new_list = array();
		foreach($this->_entIds as $id) {
			if($id != $entId) {
				$new_list[] = $id;
			}
		}
		$this->_entIds = $new_list;
		return $this;
	}

	/**
	 * リアルタイム与信を実施するかのフラグを取得する
	 *
	 * @return boolean
	 */
	public function getRealTimeCjFlg() {
		return $this->_realTimeCjFlg;
	}
	/**
	 * リアルタイム与信を実施するかのフラグを設定する
	 *
	 * @param boolean $realTimeCjFlg リアルタイム与信実施フラグ
	 * @return LogicCreditJudgeModuleCoralExtra このインスタンス
	 */
	public function setRealTimeCjFlg($realTimeCjFlg) {
		$this->_realTimeCjFlg = $realTimeCjFlg;
		return $this;
	}

	/**
	 * 与信判定基準IDを取得する
	 *
	 * @return number
	 */
	public function getCreditCriterionId() {
	    return $this->_creditCriterionId;
	}
	/**
	 * 与信判定基準IDを設定する
	 *
	 * @param int $creditCriterionId 与信判定基準ID
	 */
	public function setCreditCriterionId($creditCriterionId) {
	    $this->_creditCriterionId = $creditCriterionId;
	}

	/**
	 * 自動化期間判定結果を取得する
	 *
	 * @return bool
	 */
	public function getAutoFlg() {
	    return $this->_autoFlg;
	}
	/**
	 * 自動化期間判定結果を設定する
	 *
	 * @param bool $autoFlg 自動化期間判定結果
	 */
	public function setAutoFlg($autoFlg) {
	    $this->_autoFlg = $autoFlg;
	}

	/**
	 * スコアリング結果取得
	 *
	 * @return array スコアリング結果
	 */
	public function getResultArray() {
	    return $this->_resultArray;
	}

    /**
     * 指定の注文の審査を実行し、判定結果を返す。
     * 判定結果は以下の定数値のいずれかを返す。
     * JUDGE_RESULT_NG：与信NG確定
     * JUDGE_RESULT_OK：与信OK確定
     * JUDGE_RESULT_PENDING：与信保留確定（＝手動与信対象）
     * JUDGE_RESULT_CONTINUE：審査継続
     *
     * @param int $oseq 注文SEQ
     * @return int 判定結果
     */
    public function judge($oseq) {

        $tblsp = new TableSystemProperty($this->getAdapter());
        $orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();

        // 与信判定結果
        $judgeResultTmp = null;

        // スコアの初期化
        $scoreTotal = 0;
        $this->_resultArray['Incre_JudgeScoreTotal'] = 0;
        $this->_resultArray['JudgeScore_Detail'] = array();

        // リピート判定
        $this->judgeRepeat($oseq);

        // テスト注文与信判定
        $tocResult = $this->judgeTOrderClass($oseq);
        if (!is_null($tocResult)) {
            $this->debug(sprintf('[%s] judge determined to %s. (test order)', $oseq, $this->judgeMap[$tocResult]));
            // 与信保留以外に確定した場合、終了
            if ($tocResult != self::JUDGE_RESULT_PENDING) {
                return $tocResult;
            }
        }
        $judgeResultTmp = $tocResult;

        // 与信が確定していない場合、判定処理を行う
        if (is_null($judgeResultTmp)) {
            // 与信OKチケット判定
            if ( $this->judgeOkTicket($oseq, $orderCustomer['EnterpriseId']) ) {
                $this->debug(sprintf('[%s] judge determined to OK. (use ok ticket)', $oseq));
                return self::JUDGE_RESULT_OK;
            }

            // 事業者情報から自動与信モードを取得
            $ent = $this->getDbCache()->fetchEnterprise($orderCustomer['EnterpriseId'])->current();
            $judgeMode = $ent['AutoCreditJudgeMode'];
            $judgeFlg = $ent['AutoJudgeFlg'];
            if(!in_array($judgeMode, array(0, 1, 2, 3, 4, 5))) $judgeMode = 0;	// 値域不正の場合は0に読み替え

            // 全件OK事業者は与信OK確定
            if($judgeMode == 1) {
                $this->debug(sprintf('[%s] judge determined to OK. (enterprise all ok)', $oseq));
                return self::JUDGE_RESULT_OK;
            }

            // グローバル変数（スキップ対象）の初期化
            $GLOBALS['SkipTarget'] = 0;

            $skipFlg = 1; // スキップ処理実施設定（オンコーディング やらない場合は変更の必要有り）

            if ($skipFlg == 1){

                $skipAll = intval($tblsp->getValue('[DEFAULT]', 'systeminfo', 'skipallflg'));        // 社内与信スキップ
                $skipEnt = intval($tblsp->getValue('[DEFAULT]', 'systeminfo', 'SkipAllEnterprise')); // 社内与信スキップ対象加盟店

                $skiplist = explode(',', $skipEnt);

                // 社内与信スキップ判定
                if($skipAll == 1 && in_array($orderCustomer['EnterpriseId'], $skiplist)) {
                    $this->debug(sprintf('[%s] judge determined to CONTINUE. (CB CreditJudge skip)', $oseq));
                    return self::JUDGE_RESULT_CONTINUE;
                }

                $execSkip = intval($tblsp->getValue('[DEFAULT]', 'systeminfo', 'ExecSkipFlg'));        // ホワイトリストスキップ処理を実施するorしない設定

                // ホワイトリストスキップ処理
                if ($execSkip == 1) {
                    $useYear  = intval($tblsp->getValue('[DEFAULT]', 'systeminfo', 'useyear'));            // スキップ対象期間
                    $stEnt  = intval($tblsp->getValue('[DEFAULT]', 'systeminfo', 'SkipTargetEnterprise')); // スキップ対象加盟店

                    $list = explode(',', $stEnt);

                    // スキップ対象者リストに対象の管理顧客が登録されているか確認
                    if( in_array($orderCustomer['EnterpriseId'], $list)){ // 期間指定なし
                        // 管理顧客ID特定
                        $sql = ' SELECT ec.ManCustId FROM T_Customer c INNER JOIN T_EnterpriseCustomer ec ON (ec.EntCustSeq = c.EntCustSeq) WHERE c.CustomerId = :CustomerId ';
                        $mancust = $this->_adapter->query($sql)->execute(array(':CustomerId' => $orderCustomer['CustomerId']))->current()['ManCustId'];

                        if ($mancust != NULL) {
                            // スキップ対象者リスト検索
                            $skipsql = ' SELECT COUNT(*) AS cnt FROM T_SkipTarget WHERE ManCustId = :ManCustId AND LastClaimDate >= :ClaimDate ';
                            $prm = array(
                                    ':ManCustId'  => $mancust,
                                    ':ClaimDate'  => date("Y-m-d",strtotime("-" . $useYear . " year")),
                            );
                            $skipcnt = $this->_adapter->query($skipsql)->execute($prm)->current()['cnt'];
                            if ($skipcnt > 0) {
                                $this->debug(sprintf('[%s] judge determined to CONTINUE. (skip target)', $oseq));
                                $GLOBALS['SkipTarget'] = 1;
                                return self::JUDGE_RESULT_CONTINUE;
                            }
                        }
                    }
                }
            }

            // 与信判定期間が自動化期間で、手動与信(全自動時NG)の場合はNG確定
            if($this->getAutoFlg() && $judgeMode == 5) {
                $this->debug(sprintf('[%s] judge determined to NG. (enterpirse auto)', $oseq));
                return self::JUDGE_RESULT_NG;
            }

            // 与信判定期間が通常与信期間で
            //   手動与信(全自動時自動)、手動与信(全自動時NG)の場合
            //   社内自動与信を行わない場合
            // 与信保留確定
            if(!$this->getAutoFlg() && (($judgeMode == 2 || $judgeMode == 5) || $judgeFlg == 0)) {
                // 社内自動与信を行わない場合で、保留なしの場合、NGにする
                if($this->judgeNoPendingEnt($oseq)) {
                    $this->debug(sprintf('[%s] judge determined to NG. (enterpirse normal AND no pending enterprise) ', $oseq));
                    $judgeResultTmp = self::JUDGE_RESULT_NG;

                } else {
                    $this->debug(sprintf('[%s] judge determined to PENDING. (enterpirse normal)', $oseq));
                    $judgeResultTmp = self::JUDGE_RESULT_PENDING;
                }
            }
        }

        // 与信が確定していない場合、判定処理を行う
        if (is_null($judgeResultTmp)) {
            // 与信判定期間が自動化期間の場合
            if ($this->getAutoFlg()) {
                // 与信審査可能金額か確認する
                // 利用額が自動判定可能金額以上であればNG確定
                if($this->judgeUseAmountOver($oseq)) {
                    $this->debug(sprintf('[%s] judge determined to NG. (over use amount)', $oseq));
                    $GLOBALS['CreditLog']['Jud_AutoUseAmountOverYN'] = 1;
                    return self::JUDGE_RESULT_NG;
                }

                // 利用限度額以内か判定する
                if($this->judgeCreditLimitAmountOver($oseq)) {
                    $this->debug(sprintf('[%s] judge determined to NG. (over limit amount)', $oseq));
                    $GLOBALS['CreditLog']['Jud_AutoUseAmountOverYN'] = 1;
                    return self::JUDGE_RESULT_NG;
                }
            }
        }

        // 過去取引SEQを初期化
        $this->initPastOrderSeqs($orderCustomer['OrderSeq']);

        // 与信が確定していない場合、判定処理を行う
        if (is_null($judgeResultTmp)) {
            // 過去二年間の取引の注文SEQを取得
            $seqs = $this->getPastOrderSeqs();

            // 取引実績がある場合、処理実行
            if(!empty($seqs)) {
                // 状況取得用にカンマ区切り文字列として構築
                $pastOrders = join(',', $seqs);

                // 過去二年間の債権キャンセルで与信判定
                if ($this->hasSaikenCancel($pastOrders)) {
                    // 一定回数以上ある場合、与信NG確定
                    $this->debug(sprintf('[%s] judge determined to NG. (saiken cancel)', $oseq));

                    //20151209 Sodeyama　与信ＮＧ確定時のスコアリング(債権キャンセルあり）暫定対応のためインラインにスコア
                    //$scoreTotal += -6000;
                    $this->_resultArray['TotalScore'] = $scoreTotal;

                    //$GLOBALS['CreditLog']['Jud_SaikenCancelScore'] = -6000;
                    $GLOBALS['CreditLog']['Jud_SaikenCancelYN'] = 1;
                    return self::JUDGE_RESULT_NG;
                }

                // 過去二年間の不払い日数で与信判定
                if ($this->hasNonPaymentDays($pastOrders)) {
                    // 判定基準日数以上ある場合、与信NG確定
                    $this->debug(sprintf('[%s] judge determined to NG. (nonpayment days)', $oseq));

                    //20181112 ichihara　与信ＮＧ確定時のスコアリング(不払い日数超過）暫定対応のためインラインにスコア
                    $scoreTotal += -7000;
                    $this->_resultArray['TotalScore'] = $scoreTotal;

                    $GLOBALS['CreditLog']['Jud_NonPaymentDaysScore'] = -7000;
                    $GLOBALS['CreditLog']['Jud_NonPaymentDaysYN'] = 1;
                    return self::JUDGE_RESULT_NG;
                }

                // 不払いスコアリング
                $scoreTotal += $this->scoreByNonPayment($pastOrders, $orderCustomer['SiteId']);
                if (isset($this->_judgeResult)) {
                    // 与信結果が確定した場合、終了
                    $this->debug(sprintf('[%s] judge determined to %s. (nonpayment)', $oseq, $this->judgeMap[$this->_judgeResult]));
                    //20151209 Sodeyama　与信ＮＧ確定時のスコアリング(不払いあり）暫定対応のためインラインにスコア
                    $scoreTotal += -5000;
                    $this->_resultArray['TotalScore'] = $scoreTotal;

                    $GLOBALS['CreditLog']['Jud_NonPaymentCntScore'] = -5000;
                    $GLOBALS['CreditLog']['Jud_NonPaymentCntYN'] = 1;
                    return $this->_judgeResult;
                }

                // 未払いスコアリング
                $scoreTotal += $this->scoreByUnpaid($pastOrders);
                if (isset($this->_judgeResult)) {
                    // 与信結果が確定した場合、終了
                    $this->debug(sprintf('[%s] judge determined to %s. (unpaid)', $oseq, $this->judgeMap[$this->_judgeResult]));
                    //20151209 Sodeyama　与信ＮＧ確定時のスコアリング(未払いあり）暫定対応のためインラインにスコア
                    $scoreTotal += -4000;
                    $this->_resultArray['TotalScore'] = $scoreTotal;

                    $GLOBALS['CreditLog']['Jud_UnpaidCntScore'] = -4000;
                    $GLOBALS['CreditLog']['Jud_UnpaidCntYN'] = 1;
                    return $this->_judgeResult;
                }
            }
        }

        // 過去二年間の取引実績に応じたスコアを計上
        $scoreTotal += $this->scoreByPastOrders($orderCustomer['OrderSeq']);

        // 備考欄の得点加算書式による加算スコアを計上
        $scoreTotal += $this->scoreByEntNote($orderCustomer['OrderSeq']);

        // 身分証アップロードによるスコアを計上
        $scoreTotal += $this->scoreByIdentityDocumentFlg($oseq);

        // いたずらキャンセルによるスコアを計上
        $scoreTotal += $this->scoreByMischiefCancel();

        // 連続注文判定結果
        if ( isset($GLOBALS['MultiOrderScore']) ) {
            $multiOrderScore = (int)$GLOBALS['MultiOrderScore'];
            $scoreTotal += $multiOrderScore;
            $GLOBALS['MultiOrderScore'] = null; // 念のため解放
            $this->debug(sprintf('[%s] MultiOrderScore = %s', $oseq, $multiOrderScore));
        }

        // スコアリング結果
        $this->_resultArray['Incre_JudgeScoreTotal'] = $scoreTotal;

        $this->debug(sprintf('[%s] Total Score = %s', $oseq, $scoreTotal));

        // 与信が確定している場合、それを返す
        if (!is_null($judgeResultTmp)) {
            return $judgeResultTmp;
        }

        // 審査継続確定
        // → 自動与信モード且つスコアが与信NG閾値～与信OK閾値の間
        return self::JUDGE_RESULT_CONTINUE;
    }

	/**
	 * 注文備考欄の得点加算書式によるスコアリングを実施
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ
	 * @return int 計上するスコア
	 */
	protected function scoreByEntNote($oseq) {
	    // スコア初期化
	    $this->_resultArray['JudgeScore_Detail']['Incre_NoteScore'] = 0;
	    $GLOBALS['CreditLog']['Jud_EntNoteScore'] = 0;

        $orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();

        // 加算点を加える事業所一覧を取得する
        $entIdList = $this->getEnterpriseIds();

        // 対象事業者の場合には加算点をスコア合計に
        if(in_array($orderCustomer['EnterpriseId'], $entIdList)) {
            // 注文情報から加算点の取得
            preg_match("/^\[加算点:[-]?[0-9]+(¥.[0-9]+)?\]/", $orderCustomer['Ent_Note'], $m);
            preg_match("/[-]?[0-9]+(¥.[0-9]+)?/", $m[0], $match);

            // 加算点があった場合にはスコアに加算する
            $this->debug(sprintf('[%s] additional score determined by Ent_Note. score = %s (EnterpriseId = %s)', $oseq, (int)$match[0], $orderCustomer['EnterpriseId']));
            $this->_resultArray['JudgeScore_Detail']['Incre_NoteScore'] = (int)$match[0];
            $GLOBALS['CreditLog']['Jud_EntNoteScore'] = (int)$match[0];
            return (int)$match[0];
        }
        return 0;
	}

	/**
	 * 過去二年間の取引状況に応じたスコアリングを実施する
	 *
	 * @param int $oseq 注文Seq
	 * @return int スコア
	 */
	protected function scoreByPastOrders($oseq) {
	    // スコア初期化
	    $this->_resultArray['JudgeScore_Detail']['Incre_PastOrderScore'] = 0;
	    $GLOBALS['CreditLog']['Jud_PastOrdersScore'] = 0;

        $orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();
        $ent = $this->getDbCache()->fetchEnterprise($orderCustomer['EnterpriseId'])->current();
        $judgeMode = $ent['AutoCreditJudgeMode'];
        if(!in_array($judgeMode, array(0, 1, 2, 3, 4, 5))) $judgeMode = 0;	// 値域不正は0に読み替える

        // 与信属性がリピートでない場合はスコア0確定
        $custIncre = $this->_resultArray['Customer_ClassDetail'];
        if($custIncre['Incre_ArTel'] != 1 && $custIncre['Incre_ArAddr'] != 1) {
            return 0;
        }

        // 再与信の場合はスコア0確定
        if(preg_match("/^事業者側修正により再与信戻し/", $orderCustomer['Incre_Note'])) {
            return 0;
        }

        // 注文合計金額が5万以上の場合はスコア0確定
        if($orderCustomer['UseAmount'] > 50000) {
            return 0;
        }

        // 過去二年間の取引の注文SEQを取得
        $seqs = $this->getPastOrderSeqs();

        // 取引実績がない場合はスコア0確定
        if(empty($seqs)) {
            return 0;
        }

        // 状況取得用にカンマ区切り文字列として構築
        $pastOrders = join(',', $seqs);

        // 対象注文の履歴チェック
        // 過去二年間の取引を精査し、以下の基準で安全であるかをチェックし、安全でない場合はスコア0確定
        // - 与信NGが含まれていない
        // - 取引未完の注文が残っていない
        // - 今回取引額が過去最高取引額より低いか、一定以上の増額になっていない
        // - 直前の注文がキャンセルされていない
        // - 5日以上延滞している取引がない
        if($this->judgeOrderHistory($pastOrders, $orderCustomer['UseAmount'])) {
            return 0;
        }

        // 加算するスコアを取得
        $maxCnt = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 107)->current()['Point'];

        if (is_null($maxCnt)) {
            // 加算するスコアを取得出来ない場合は0
            return 0;
        }

        // スコアリング結果
        $this->_resultArray['JudgeScore_Detail']['Incre_PastOrderScore'] = intval($maxCnt);
        $GLOBALS['CreditLog']['Jud_PastOrdersScore'] = intval($maxCnt);

        // 加算スコアを返す
        return intval($maxCnt);
	}

	/**
	 * 過去二年間のうちに取引があったかを判別する
	 *
	 * @access protected
	 * @param $pastOrders 対象注文の過去取引注文SEQ
	 * @return boolean $pastOrdersの注文SEQのいずれかが過去二年間の取引ならtrue、それ以外はfalse
	 */
	protected function hasTransactInLastTwoYears($pastOrders) {
	    $count = $this->mdlo->findOrderCustomerByTwoYearsOrderCnt($pastOrders);
		return $count > 0;
	}

	/**
	 * 過去二年間のうちに債権返却または不払があったかを判別する
	 * @access protected
	 * @param $pastOrders 対象注文の過去取引注文SEQ
	 * @return boolean $pastOrdersの注文SEQのいずれかが過去二年間のうちに債権返却されたか不払であった場合はtrue、それ以外はfalse
	 */
	protected function hasSaikenCnlOrDamageTransaction($pastOrders) {
	    return $this->mdlo->findOrderCustomerBySaikenCancelCnt($pastOrders) > 0 ||
				$this->mdlo->findOrderCustomerByNoRecDamagedCnt($pastOrders) > 0
					? true : false;
	}

	/**
	 * 過去二年間の対象履歴の個別判定。
	 * 与信NGがある、現在クローズしていない注文が残っている、延滞5日以上の取引がある、これまでの最高取引額より一定以上高い取引額である、
	 * 直前注文がキャンセルされている、のいずれかに該当する場合は安全でないと判断する
	 *
	 * @access protected
	 * @param $pastOrders 対象者の過去注文OrderSeq
	 * @param $userAmount 合計額
	 * @return boolean 安全な注文と判断できる場合はtrue、それ以外はfalse
	 */
	protected function judgeOrderHistory($pastOrders, $useAmonut) {

// 	    // 与信NGがある
// 		if($this->mdlo->findOrderCustomerByNgCnt2($pastOrders) > 0)  {
// 			return true;
// 		}

		// クローズしていない注文がある
		if($this->mdlo->findOrderCustomerByNoCloseCnt2($pastOrders) > 0)  {
			return true;
		}

		// 過去支払最大額以上である
		// 過去支払最大額取得
		$pastMaxAmount = $this->mdlo->findOrderCustomerByMaxUseAmountCnt2($pastOrders);

		// 支払い実績が取得出来なかった場合(＝入金クローズ済の注文の利用額合計が0円以下)
		if ($pastMaxAmount <= 0){
		    return true;
		}

		// 過去支払額の設定
		// +5000円か3.0倍の大きい方を取得する
		$maxAmount = $pastMaxAmount + 5000 >= $pastMaxAmount * 3 ?
			$pastMaxAmount + 5000 : $pastMaxAmount * 3;

		if($maxAmount < $useAmonut) {
			return true;
		}

// 		// 直前注文がキャンセル
// 		if($this->mdlo->findOrderCustomerByOneBeforeCnlCnt($pastOrders) > 0)  {
// 			return true;
// 		}

		// 支払遅れが5日以上ある
		if($this->mdlo->findOrderCustomerByLateRecCnt2($pastOrders) > 0 )  {
		    return true;
		}

		return false;
	}

	/**
	 * 指定注文SEQの過去取引を示す注文SEQリストを初期化する。
	 * このメソッドで初期化された注文SEQのリストは内部でキャッシュされ、
	 * 再びこのメソッドが呼び出されるまで存続する
	 *
	 * @access protected
	 * @param int $oseq 注文SEQ。初期化された注文SEQリストからは除外される
	 * @return array 初期化された注文SEQのリスト
	 */
	protected function initPastOrderSeqs($oseq) {

        $row_c = $this->_adapter->query(" SELECT RegUnitingAddress, RegPhone FROM T_Customer WHERE OrderSeq = :OrderSeq "
            )->execute(array(':OrderSeq' => $oseq))->current();

        $row_o = $this->_adapter->query(" SELECT SiteId FROM T_Order WHERE OrderSeq = :OrderSeq ")->execute(array(':OrderSeq' => $oseq))->current();

        $seqs = array();
        foreach($this->mdlonc->getPastNotCloseOrderSeqs(nvl($row_c['RegUnitingAddress'],''), nvl($row_c['RegPhone'],''), $row_o['SiteId']) as $row) {
            if($row['OrderSeq'] != $oseq) {
                $seqs[] = (int)$row['OrderSeq'];
            } else {
            }
        }
        $this->_pastOrderSeqs = $seqs;
	}

	/**
	 * 現在処理中の過去取引注文SEQリストを取得する
	 *
	 * @access protected
	 * @return array 現在処理中の過去取引注文SEQ
	 */
	protected function getPastOrderSeqs() {
	    return $this->_pastOrderSeqs;
	}

	/**
	 * 指定注文の利用額が自動判定可能基準額を超えているかを判断する。
	 * 自動判定可能基準額とは、以下の金額の内どちらか低いものを指す。
	 * - 当該事業者の平均利用単価に事業者個別またはシステム規定の倍率を掛けた金額
	 * - システム規定の自動判別可能基準上限額
	 *
	 * @param int $oseq 注文SEQ
	 * @return boolean 指定注文の利用額が基準額を超えている場合はtrue、それ以外はfalse
	 */
	public function judgeUseAmountOver($oseq) {

        $tableSi = new TableSite($this->getAdapter());
        $tableEt = new TableEnterpriseTotal($this->getAdapter());
        $tableSysP = new TableSystemProperty($this->getAdapter());

        // 注文情報取得
        $order = $this->getDbCache()->fetchOrderCustomer($oseq)->current();

        // 利用金額上限の取得
        $limitThreshold = null;;
        $site = $tableSi->findSite($order['SiteId'])->current();
        if (!is_null($site['CreditOrderUseAmount']) && $this->debugUserAmountOver != 0) {   // デバッグフラグが無効の場合、ｼｽﾃﾑﾌﾟﾛﾊﾟﾃｨを使用
            // サイトの与信時注文利用額
            $limitThreshold = intval($site['CreditOrderUseAmount']);
        }
        if (is_null($limitThreshold)) {
            // システムプロパティの与信時注文利用額
            $limitThreshold = intval($tableSysP->getValue('[DEFAULT]', 'systeminfo', 'CreditOrderUseAmount'));
        }

        // 与信平均単価倍率の取得
        $averageUnitPriceRate = null;
        if (!is_null($site['AverageUnitPriceRate'])) {
            // サイトの与信平均単価倍率
            $averageUnitPriceRate = floatval($site['AverageUnitPriceRate']);
        }
        if (is_null($averageUnitPriceRate)) {
            // システムプロパティの与信平均単価倍率
            //$averageUnitPriceRate = floatval(($tableSysP->getValue('cbadmin', 'cj_api', 'default_average_unit_price_rate')));
            $averageUnitPriceRate = (float)($this->getOption('default_average_unit_price_rate'));
        }
        if (is_null($averageUnitPriceRate) || $averageUnitPriceRate <= 0) {
            // 0以下は1に補正
            $averageUnitPriceRate = 1;
        }

        // 平均単価倍率上限の計算
        //   与信平均単価 = 加盟店集計.過去365日の与信通過注文の平均利用額 / 加盟店集計.過去365日の与信通過注文数
        //   平均単価倍率上限 = 与信平均単価 * 与信平均単価倍率
        $et = $tableEt->find($order['EnterpriseId'])->current();
        $amount = intval($et['NpAverageAmountOk']);
        $count = intval($et['NpOrderCountOk']);
        $calc = ($count == 0) ? 0 : $amount / $count;

        $avgThreshold = $calc * $averageUnitPriceRate;

        if ($avgThreshold == 0) {
            // 計算した平均単価倍率上限が0の場合は利用金額上限を使用
            $threshold = $limitThreshold;
        }else{
            // どちらか低い金額を基準とする
            $threshold = $limitThreshold < $avgThreshold ? $limitThreshold : $avgThreshold;
        }

        return $order['UseAmount'] > $threshold ? true : false;
    }

    /**
     * リピート判定を行う
     * 各自動与信結果クラスを判定する
     *
     * @param int $oseq 注文Seq
     */
    protected function judgeRepeat($oseq)
    {
        $orderCustomer = $this->getDbCache()->fetchOrderCustomer($oseq)->current();

        // 請求先に対するリピート判定
        $res = array();
        // 管理顧客の付帯情報による優良・ブラック判定
        $mdlMc = new TableManagementCustomer($this->getAdapter());
        $mc = $mdlMc->findByOrderSeq($oseq)->current();

        if ($mc['BlackFlg'] == 1) {
            // ブラック顧客
            $res['Incre_ArName'] = 5;
            $res['Incre_ArAddr'] = 5;
            $res['Incre_ArTel'] = 5;
        }
        elseif ($mc['BlackFlg'] != 1 && $mc['GoodFlg'] == 1) {
            // 優良顧客
            $res['Incre_ArName'] = 2;
            $res['Incre_ArAddr'] = 2;
            $res['Incre_ArTel'] = 2;
        }
        else {
            // 住所判定
            $classAdrs = $this->judgeClass($oseq, $mc['UnitingAddress'], 1);

            // 氏名判定
            $className = $this->judgeClass($oseq, $mc['NameKj'], 2);

            // 電話番号判定
            $classTel = $this->judgeClass($oseq, $mc['Phone'], 8);

            $res['Incre_ArAddr'] = $classAdrs;
            $res['Incre_ArName'] = $className;
            $res['Incre_ArTel'] = $classTel;
        }
        $this->_resultArray['Customer_ClassDetail'] = $res;
        $this->_resultArray['Customer_CustomerId'] = $orderCustomer['CustomerId'];

        $GLOBALS['CreditLog']['Jud_Cust_IncreArName'] = $res['Incre_ArName'];
        $GLOBALS['CreditLog']['Jud_Cust_IncreArAddr'] = $res['Incre_ArAddr'];
        $GLOBALS['CreditLog']['Jud_Cust_IncreArTel'] = $res['Incre_ArTel'];

        // 配送先に対するリピート判定
        $resAll = array();

        $itemDelis = $this->getDbCache()->fetchItemDelivery($oseq);

        foreach($itemDelis as $itemDeli) {
            $res = array();
            if($orderCustomer['AnotherDeliFlg'] == 1) { // 別配送先指定時のみ
                // 住所判定
                $classAdrs = $this->judgeClass($oseq, $itemDeli['UnitingAddress'], 1, "deli");

                // 氏名判定
                $className = $this->judgeClass($oseq, $itemDeli['DestNameKj'], 2);

                // 電話番号判定
                $classTel = $this->judgeClass($oseq, $itemDeli['Phone'], 8);

                $res['Incre_ArAddr'] = $classAdrs;
                $res['Incre_ArName'] = $className;
                $res['Incre_ArTel'] = $classTel;

            }
            else {
                $res['Incre_ArAddr'] = null;
                $res['Incre_ArName'] = null;
                $res['Incre_ArTel'] = null;
            }

            $resAll[$itemDeli['DeliDestId']] = $res;

            $GLOBALS['CreditLog']['Jud_Deli_IncreArName'] = $res['Incre_ArName'];
            $GLOBALS['CreditLog']['Jud_Deli_IncreArAddr'] = $res['Incre_ArAddr'];
            $GLOBALS['CreditLog']['Jud_Deli_IncreArTel'] = $res['Incre_ArTel'];

        }
        $this->_resultArray['DeliveryDestination_ClassDetail'] = $resAll;
    }

    /**
     * 対象カテゴリの各クラスの件数から設定する属性を返す
     * 住所、氏名、電話番号から呼ばれる
     * @param int $oseq orderSeq
     * @param string $target 対象条件文字列（非正規化）
     * @param int $category カテゴリ
     * @param string $deli 配送先住所の場合のみdeliという文字列を入れている
     * @return LogicCreditJudgeLocalScore この与信によるスコアリング結果
     */
    public function judgeClass($oseq, $target, $category, $deli = null) {

        // ブラック、優良、リピートで設定された完全一致の件数を取得
        // ブラック
        $blackCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 5);
        // 優良
        $goodCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 2);
        // リピート
        $repeatCount = $this->doneJudgeByAutoCreditConditonCnt($category, $target, 1);

        // 新規フラグ
        $isNew = false;

        // 上記の件数が0であった場合=新規の場合は与信条件にリピートとして登録
        if($blackCount + $goodCount + $repeatCount == 0) {
            // array
            $data = array(
                    'Seq' => -1,                    // dummy
                    'OrderSeq' => $oseq,		    // OrderSeq
                    'Category' => $category, 		// カテゴリ
                    'RegistDate' => date('Y-m-d'),  // 登録日
                    'Class' => 1,      				// リピート
                    'ValidFlg' => 1,                // 有効
                    'Cstring' => $target,			// 条件文字列
                    // deli文字列は比較した後で設定
                    'CreditCriterionId' => 0,       // 与信判定基準ID
            );

            // 重複は登録しない
            if (!isset($this->_resultArray['CreditCondition_Insert'])) {
                $data['CstringHash'] = $deli;   // deli文字列
                $this->_resultArray['CreditCondition_Insert'][] = $data;
            } else {
                $dchk = false;
                foreach ($this->_resultArray['CreditCondition_Insert'] as $insRow) {
                    // 重複チェックのため一旦削除
                    $deliFlg = $insRow['CstringHash'];
                    unset($insRow['CstringHash']);

                    if ($insRow === $data) {
                        // 重複除外
                        $dchk = true;

                        // 戻す
                        $insRow['CstringHash'] = $deliFlg;

                        // 重複した場合はリピート判定にする
                        $repeatCount = 1;
                        break;
                    }
                }
                if (!$dchk) {
                    $data['CstringHash'] = $deli;   // deli文字列
                    $this->_resultArray['CreditCondition_Insert'][] = $data;
                }
            }
            $isNew = true;
        }

        // クラスの設定 　
        // 手動設定:-1or1
        $class = $isNew ? -1 : 1;

        if($blackCount > 0) {
            // ブラック:5
            $class = 5;
        }
        if($goodCount > 0) {
            // 優良:2
            $class = 2;
        }
        if($repeatCount > 0) {
            // リピート:1
            $class = 1;
        }

        return $class;
    }

    /**
     * 自動登録された与信条件から与信対象項目に完全一致する条件の件数を取得する
     *
     * @param int $category カテゴリ
     * @param string $target 与信対象
     * @param string $class 属性（ブラック、優良、リピート等）
     * @return int 完全一致する与信条件の件数
     */
    public function doneJudgeByAutoCreditConditonCnt($category, $target, $class) {
        // 対象の正規化
        $regtarget = LogicNormalizer::create($this->map[$category])->normalize($target);

        // マッチする与信条件を取得する。
        $matchCount = $this->findCreditConditionCnt($category, $regtarget, $class);

        return $matchCount;
    }

    /**
     * 自動登録された与信条件から指定の条件に一致するデータの件数を取得する。
     * このメソッドは doneJudgeByAutoCreditConditionCnt メソッドからのみ呼び出され、
     * 内容は TableCreditCondition::findCreditConditionCnt の代替となっている。
     *
     * @access protected
     * @param int $category カテゴリ
     * @param string $regtarget 正規化済みの与信対象データ
     * @param string $class 属性クラス（ブラック、優良、リピート等）
     * @return int 一致する与信条件の件数
     */
    protected function findCreditConditionCnt($category, $regtarget, $class) {

        $sql  = " SELECT COUNT(*) AS Cnt ";
        $sql .= " FROM   T_CreditCondition ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    ValidFlg   = 1 ";
        $sql .= " AND    Category   = :Category ";
        $sql .= " AND    Class      = :Class ";
        $sql .= " AND    RegCstring = :RegCstring ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':Category' => $category,
                ':Class' => $class,
                ':RegCstring' => $regtarget,
        );

        return (int)$stm->execute($prm)->current()['Cnt'];
    }

    /**
     * テスト注文の与信判定を行う
     *
     * @param int $oseq 注文Seq
     */
    protected function judgeTOrderClass($oseq) {

        $order = $this->mdlo->find($oseq)->current();

        $judgeResult = null;

        if ($order['T_OrderClass'] == 1) {
            // テスト注文の場合、判定する

            // テスト注文自動与信審査区分
            switch ($order['T_OrderAutoCreditJudgeClass']) {
                case 0:
                    // OK
                    $judgeResult = self::JUDGE_RESULT_OK;
                    break;
                case 1:
                    // NG
                    $judgeResult = self::JUDGE_RESULT_NG;
                    break;
                case 2:
                    // 保留（手動与信へ）
                    $judgeResult = self::JUDGE_RESULT_PENDING;
                    break;
                default:
                    break;
            }
        }

        return $judgeResult;
    }

    /**
     * 利用限度額以内か判定する
     *
     * @param int $oseq 注文Seq
     */
    public function judgeCreditLimitAmountOver($oseq) {

        $tableSi = new TableSite($this->getAdapter());
        $tableSysP = new TableSystemProperty($this->getAdapter());
        $tableMc = new TableManagementCustomer($this->getAdapter());
        $tableCreSysInfo = new TableCreditSystemInfo($this->getAdapter());
        $tableOrderSummary = new TableOrderSummary($this->getAdapter());

        // 注文情報取得
        $order = $this->getDbCache()->fetchOrderCustomer($oseq)->current();

        // 加盟店顧客の情報取得
        $sql  = " SELECT EC.BtoBCreditLimitAmountFlg, EC.BtoBCreditLimitAmount ";
        $sql .= " FROM   T_EnterpriseCustomer EC ";
        $sql .= " INNER JOIN T_Customer C ON EC.EntCustSeq = C.EntCustSeq ";
        $sql .= " INNER JOIN T_Order O ON O.OrderSeq = C.OrderSeq ";
        $sql .= " WHERE  1 = 1 ";
        $sql .= " AND    O.OrderSeq = :OrderSeq ";

        $stm = $this->_adapter->query($sql);

        $prm = array(
                ':OrderSeq' => $oseq,
        );

        $ec = $stm->execute($prm)->current();

        // サイト
        $site = $tableSi->findSite($order['SiteId'])->current();

        // 自動与信限度額の取得
        $creditLimitAmount = null;
        if ($site['SitClass'] == 1 && $ec['BtoBCreditLimitAmountFlg'] == 1) {
            // サイト.サイト区分=1(法人） かつ 加盟店顧客.BtoB与信限度額フラグ=1(あり)のときのみ
            if (!is_null($ec['BtoBCreditLimitAmount'])) {
                // 加盟店顧客.BtoB与信限度額
                $creditLimitAmount = intval($ec['BtoBCreditLimitAmount']);
            }
        }
        if (is_null($creditLimitAmount)) {
            if (!is_null($site['AutoCreditLimitAmount'])) {
                // サイト.自動与信限度額
                $creditLimitAmount = intval($site['AutoCreditLimitAmount']);
            }
        }
        if (is_null($creditLimitAmount)) {
            // システムプロパティの与信時注文利用額
            $creditLimitAmount = intval($tableSysP->getValue('[DEFAULT]', 'systeminfo', 'AutoCreditLimitAmount'));
        }

        // 顧客の利用総額を取得
        $rowCreSysInfo = $tableCreSysInfo->find()->current(); // 与信システム情報
        $intClaimPastDays = intval($rowCreSysInfo['ClaimPastDays']);
        $intDeliveryPastDays = intval($rowCreSysInfo['DeliveryPastDays']);
        $dteClaimPastDays = date("Y-m-d",strtotime("-" . $intClaimPastDays . " day"));
        $dteDeliveryPastDays = date("Y-m-d",strtotime("-" . $intDeliveryPastDays . " day"));

        // 請求先、配送先の情報を取得
        $rowOrderSummary = $tableOrderSummary->findByOrderSeq($oseq)->current(); // 注文サマリー情報
        $regUnitingAddress = $order['RegUnitingAddress'];
        $regPhone = $order['RegPhone'];
        $regDestUnitingAddress = $rowOrderSummary['RegDestUnitingAddress'];
        $regDestPhone = $rowOrderSummary['RegDestPhone'];

        // 利用総額の算出
        $custCreditAmount = (int)$order['UseAmount'];

        $strOrderSeqs = '0';

        // --------------------
        // [請求先]×[請求先]
        // --------------------
        if ($rowCreSysInfo['AutoCreditLimitAmount1'] == 1) {
            $sql = <<<EOQ
SELECT IFNULL(GROUP_CONCAT(O.OrderSeq), '0') AS OrderSeqs
FROM   T_Order O
INNER JOIN T_Customer C ON C.OrderSeq = O.OrderSeq
WHERE  1 = 1
AND    O.DataStatus >= 31
AND    O.DataStatus < 90
AND    (C.RegUnitingAddress = :RegUnitingAddress OR C.RegPhone = :RegPhone )
AND    O.RegistDate >= :RegistDate
EOQ;
            $prm = array(
                    ':RegUnitingAddress' => $regUnitingAddress,
                    ':RegPhone' => $regPhone,
                    ':RegistDate' => $dteClaimPastDays,
            );

            $strOrderSeqs .= ',';
            $strOrderSeqs .= $this->_adapter->query($sql)->execute($prm)->current()['OrderSeqs'];
        }

        // --------------------
        // [請求先]×[配送先]
        // --------------------
        if ($rowCreSysInfo['AutoCreditLimitAmount2'] == 1) {
            $sql = <<<EOQ
SELECT IFNULL(GROUP_CONCAT(O.OrderSeq), '0') AS OrderSeqs
FROM   T_Order O
INNER JOIN T_OrderSummary OS ON OS.OrderSeq = O.OrderSeq
WHERE  1 = 1
AND    O.DataStatus >= 31
AND    O.DataStatus < 90
AND    (OS.RegDestUnitingAddress = :RegDestUnitingAddress OR OS.RegDestPhone = :RegDestPhone )
AND    O.RegistDate >= :RegistDate
EOQ;
            $prm = array(
                    ':RegDestUnitingAddress' => $regUnitingAddress,
                    ':RegDestPhone' => $regPhone,
                    ':RegistDate' => $dteDeliveryPastDays,
            );

            $strOrderSeqs .= ',';
            $strOrderSeqs .= $this->_adapter->query($sql)->execute($prm)->current()['OrderSeqs'];
        }

        // --------------------
        // [配送先]×[請求先]
        // --------------------
        if ($rowCreSysInfo['AutoCreditLimitAmount3'] == 1) {
            $sql = <<<EOQ
SELECT IFNULL(GROUP_CONCAT(O.OrderSeq), '0') AS OrderSeqs
FROM   T_Order O
INNER JOIN T_Customer C ON C.OrderSeq = O.OrderSeq
WHERE  1 = 1
AND    O.DataStatus >= 31
AND    O.DataStatus < 90
AND    (C.RegUnitingAddress = :RegUnitingAddress OR C.RegPhone = :RegPhone )
AND    O.RegistDate >= :RegistDate
EOQ;
            $prm = array(
                    ':RegUnitingAddress' => $regDestUnitingAddress,
                    ':RegPhone' => $regDestPhone,
                    ':RegistDate' => $dteClaimPastDays,
            );

            $strOrderSeqs .= ',';
            $strOrderSeqs .= $this->_adapter->query($sql)->execute($prm)->current()['OrderSeqs'];
        }

        // --------------------
        // [配送先]×[配送先]
        // --------------------
        if ($rowCreSysInfo['AutoCreditLimitAmount4'] == 1) {
            $sql = <<<EOQ
SELECT IFNULL(GROUP_CONCAT(O.OrderSeq), '0') AS OrderSeqs
FROM   T_Order O
INNER JOIN T_OrderSummary OS ON OS.OrderSeq = O.OrderSeq
WHERE  1 = 1
AND    O.DataStatus >= 31
AND    O.DataStatus < 90
AND    (OS.RegDestUnitingAddress = :RegDestUnitingAddress OR OS.RegDestPhone = :RegDestPhone )
AND    O.RegistDate >= :RegistDate
EOQ;
            $prm = array(
                    ':RegDestUnitingAddress' => $regDestUnitingAddress,
                    ':RegDestPhone' => $regDestPhone,
                    ':RegistDate' => $dteDeliveryPastDays,
            );

            $strOrderSeqs .= ',';
            $strOrderSeqs .= $this->_adapter->query($sql)->execute($prm)->current()['OrderSeqs'];
        }

        // 利用額合計を算出
        $sql = <<<EOQ
SELECT IFNULL(SUM(O.UseAmount), 0) AS total
FROM   T_Order O
WHERE  1 = 1
AND    O.OrderSeq IN ($strOrderSeqs)
EOQ;

        $custCreditAmount += (int)$this->_adapter->query($sql)->execute()->current()['total'];

        return $custCreditAmount > $creditLimitAmount ? true : false;
    }

    /**
     * 過去二年間のうちに債権返却が一定回数以上あったかを判別する
     * @access protected
     * @param $pastOrders 対象注文の過去取引注文SEQ
     * @return boolean $pastOrdersの注文SEQのうちに債権返却された注文が一定回数以上あった場合はtrue、それ以外はfalse
     */
    protected function hasSaikenCancel($pastOrders) {
        // 過去二年間の債権キャンセル件数
        $cnt = $this->mdlo->findOrderCustomerBySaikenCancelCnt2($pastOrders);

        // 許容回数を取得
        $maxCnt = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 203)->current()['Point'];

        if (is_null($maxCnt)) {
            // 許容回数を取得出来ない場合は判定無効
            return false;
        }

        return $cnt >= intval($maxCnt) ? true : false;
    }

    /**
     * 過去二年間のうちに不払い日数が判定基準日以上あったかを判別する
     * @access protected
     * @param $pastOrders 対象注文の過去取引注文SEQ
     * @return boolean $pastOrdersの注文SEQのうちに不払い日数が判定基準日以上あった場合はtrue、それ以外はfalse
     */
    protected function hasNonPaymentDays($pastOrders) {
        // 過去二年間の不払い日数
        $cnt = $this->mdlo->findOrderCustomerByNonPaymentDays($pastOrders);

        // 許容回数を取得
        $maxCnt = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 206)->current()['Point'];

        if (empty($maxCnt)) {
            // 許容回数を取得出来ない場合、0の場合は判定無効
            return false;
        }

        return $cnt >= intval($maxCnt) ? true : false;
    }

    /**
     * 過去二年間のうちに不払いが一定回数以上あったかを判別する
     * 一定回数未満の場合、不払い総額が一定金額以上かどうかでスコアリングする
     *
     * @access protected
     * @param $pastOrders 対象注文の過去取引注文SEQ
     * @param $siteId ｻｲﾄID
     * @return int スコアリング結果
     */
    protected function scoreByNonPayment($pastOrders, $siteId) {

        // スコア初期化
        $score = 0;
        $this->_resultArray['JudgeScore_Detail']['Incre_NonPaymentScore'] = 0;
        $GLOBALS['CreditLog']['Jud_NonPaymentAmtScore'] = 0;

        // 与信結果初期化
        unset($this->_judgeResult);

        // 過去二年間の不払い件数、総額
        $cnt = $this->mdlo->findOrderCustomerByNonPaymentCnt($pastOrders);
        $amt = $this->mdlo->findOrderCustomerByNonPaymentAmount($pastOrders);

        // 過去二年間に同じ店舗での購入履歴があるか
        $cntForSite = $this->mdlo->findOrderCustomerByNonPaymentSiteCnt($pastOrders, $siteId);
        $cntForSite = intval($cntForSite); // 取れなかった場合を想定し保護

        // 許容回数を取得
        $maxCnt = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 202)->current()['Point'];

        // 購入店舗のみ不払い件数設定
        $pnt204 = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 204)->current()['Point'];
        $pnt204 = intval($pnt204); // 取れなかった場合を想定し保護

        // 他店舗での不払い件数設定
        $pnt205 = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 205)->current()['Point'];
        $pnt205 = intval($pnt205); // 取れなかった場合を想定し保護

        if (!is_null($maxCnt)) {
            // 許容回数を取得出来たら判定

            // 同じ店舗での購入履歴 = 0　または　購入店舗のみ不払い件数設定 = 0
            if ($cntForSite == 0 || $pnt204 == 0) {
                // 判定結果がTRUEの場合、与信NG確定、そうでない場合は審査継続
                if (intval($cnt) >= intval($maxCnt)) {
                    $this->_judgeResult = self::JUDGE_RESULT_NG;
                    return 0;
                }
            }

            // 同じ店舗での購入履歴 > 0　かつ　購入店舗のみ不払い件数設定 > 0
            if ($cntForSite > 0 && $pnt204 > 0) {
                $cnt = $this->mdlo->findOrderCustomerByNonPaymentCntForSite($pastOrders, $siteId);
                if (intval($cnt) >= $pnt204) {
                    $this->_judgeResult = self::JUDGE_RESULT_NG;
                    return 0;
                }

                if ($pnt205 > 0) {
                    $cnt = $this->mdlo->findOrderCustomerByNonPaymentCntForOtherSite($pastOrders, $siteId);
                    if (intval($cnt) >= $pnt205) {
                        $this->_judgeResult = self::JUDGE_RESULT_NG;
                        return 0;
                    }
                }
            }
        }

        // 不払い総額最大を取得
        $row = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 304)->current();

        if (isset($row) && !is_null($row['GeneralProp']) && !is_null($row['Point'])) {
            // 不払い総額最大を取得できた場合、スコアリング
            $prop = intval($row['GeneralProp']);
            $point = intval($row['Point']);

            if ($amt >= $prop) {
                $score = $point;
            }
        }

        // スコアリング結果
        $this->_resultArray['JudgeScore_Detail']['Incre_NonPaymentScore'] = $score;

        $GLOBALS['CreditLog']['Jud_NonPaymentAmtScore'] = $score;

        return $score;
    }

    /**
     * 過去二年間のうちに未払いが一定回数以上あったかを判別する
     * 一定回数未満の場合、未払い総額が一定金額以上かどうかでスコアリングする
     *
     * @access protected
     * @param $pastOrders 対象注文の過去取引注文SEQ
     * @return int スコアリング結果
     */
    protected function scoreByUnpaid($pastOrders) {
        // スコア初期化
        $score = 0;
        $this->_resultArray['JudgeScore_Detail']['Incre_UnpaidScore'] = 0;
        $GLOBALS['CreditLog']['Jud_UnpaidAmtScore'] = 0;

        // 与信結果初期化
        unset($this->_judgeResult);

        // 過去二年間の未払い件数、総額
        $cnt = $this->mdlo->findOrderCustomerByUnpaidCnt($pastOrders);
        $amt = $this->mdlo->findOrderCustomerByUnpaidAmount($pastOrders);

        // 許容回数を取得
        $maxCnt = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 201)->current()['Point'];

        if (!is_null($maxCnt)) {
            // 許容回数を取得出来たら判定
            // 判定結果がTRUEの場合、与信NG確定、そうでない場合は審査継続
            if (intval($cnt) >= intval($maxCnt)) {
                $this->_judgeResult = self::JUDGE_RESULT_NG;
                return 0;
            }
        }

        // 未払い総額最大を取得
        $row = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 303)->current();

        if (isset($row) && !is_null($row['GeneralProp']) && !is_null($row['Point'])) {
            // 未払い総額最大を取得できた場合、スコアリング
            $prop = intval($row['GeneralProp']);
            $point = intval($row['Point']);

            if ($amt >= $prop) {
                $score = $point;
            }
        }

        // スコアリング結果
        $this->_resultArray['JudgeScore_Detail']['Incre_UnpaidScore'] = $score;
        $GLOBALS['CreditLog']['Jud_UnpaidAmtScore'] = $score;

        // スコアリング結果を返す
        return $score;
    }

    /**
     * 身分証アップロードによるスコアを計上
     *
     * @param int $oseq 注文Seq
     * @return int スコアリング結果
     */
    protected function scoreByIdentityDocumentFlg($oseq) {
        // スコア初期化
        $score = 0;
        $this->_resultArray['JudgeScore_Detail']['Incre_IdentityDocumentScore'] = 0;
        $GLOBALS['CreditLog']['Jud_IdentityDocumentFlgScore'] = 0;

        // 管理顧客取得
        $mdlMc = new TableManagementCustomer($this->getAdapter());
        $mc = $mdlMc->findByOrderSeq($oseq)->current();

        if ($mc['IdentityDocumentFlg'] == 1) {
            // 身分証アップロードフラグ=1の場合、スコア取得
            $point = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 108)->current()['Point'];

            if (!is_null($point)) {
                // 取得出来た場合に返す
                $score = intval($point);
            }
        }

        // スコアリング結果
        $this->_resultArray['JudgeScore_Detail']['Incre_IdentityDocumentScore'] = $score;
        $GLOBALS['CreditLog']['Jud_IdentityDocumentFlgScore'] = $score;

        return $score;
    }

    /**
     * いたずらキャンセルによるスコアを計上
     *
     * @param array $seqs 過去注文Seq
     * @return int スコアリング結果
     */
    protected function scoreByMischiefCancel() {
        // スコア初期化
        $score = 0;
        $this->_resultArray['JudgeScore_Detail']['Incre_MischiefCancelScore'] = 0;
        $GLOBALS['CreditLog']['Jud_MischiefCancelScore'] = 0;

        // 過去二年間の取引の注文SEQを取得
        $seqs = $this->getPastOrderSeqs();

        // 過去注文がある場合のみ
        if(!empty($seqs)) {

            // 状況取得用にカンマ区切り文字列として構築
            $pastOrders = join(',', $seqs);

            //いたずらキャンセル件数取得
            $mdlcan = new TableCancel($this->getAdapter());
            $cnt = $mdlcan->findMischiefCancel($pastOrders);

            if ($cnt > 0) {
                // いたずらキャンセルがある場合、スコア取得
                $point = $this->mdlcp->findCreditPoint($this->getCreditCriterionId(), 109)->current()['Point'];

                if (!is_null($point)) {
                    // 取得出来た場合に返す
                    $score = intval($point);
                }
            }
        }

        // スコアリング結果
        $this->_resultArray['JudgeScore_Detail']['Incre_MischiefCancelScore'] = $score;
        $GLOBALS['CreditLog']['Jud_MischiefCancelScore'] = $score;

        return $score;
    }

    /**
     * 与信OKチケット判定を行う
     *
     * @param int $oseq 注文Seq
     * @return bool true:OKチケット使用、false:OKチケット未使用
     */
    protected function judgeOkTicket($oseq, $enterpriseId) {

        $booFnc = false;

        // 与信対象注文と同一加盟店で、有効な与信OKチケットが存在するかチェック
        $sql = ' SELECT Seq, OrderSeq FROM T_CreditOkTicket WHERE EnterpriseId = :EnterpriseId AND Status = 0 AND ValidToDate >= :ValidToDate ';
        $prm = array(
            ':EnterpriseId' => $enterpriseId,
            ':ValidToDate' => date('Y-m-d H:i:s'),
        );

        $ri = $this->_adapter->query($sql)->execute($prm);

        if ($ri->count() > 0) {
            // OKチケットが存在する場合

            // 比較元データの取得とSQLの構築
            // 注文、請求先、配送先情報の取得
            $ordsql  = ' SELECT o.SiteId ';
            $ordsql .= '        ,os.PostalCode ';
            $ordsql .= '        ,os.RegUnitingAddress ';
            $ordsql .= '        ,os.RegNameKj ';
            $ordsql .= '        ,os.RegPhone ';
            $ordsql .= '        ,os.DestPostalCode ';
            $ordsql .= '        ,os.RegDestUnitingAddress ';
            $ordsql .= '        ,os.RegDestNameKj ';
            $ordsql .= '        ,os.RegDestPhone ';
            $ordsql .= '        ,o.UseAmount ';
            $ordsql .= ' FROM   T_Order o ';
            $ordsql .= '        INNER JOIN T_OrderSummary os ';
            $ordsql .= '                ON o.OrderSeq = os.OrderSeq ';
            $ordsql .= ' WHERE  o.OrderSeq = :OrderSeq ';
            $baseOrdRow = $this->_adapter->query($ordsql)->execute(array(':OrderSeq' => $oseq))->current();

            // 商品情報の取得
            $itmsql  = ' SELECT GROUP_CONCAT(ItemNameKj, UnitPrice, ItemNum ORDER BY ItemNameKj, UnitPrice, ItemNum) AS ItemInfo ';
            $itmsql .= ' FROM   T_OrderItems ';
            $itmsql .= ' WHERE  OrderSeq = :OrderSeq ';
            $itmsql .= ' AND    DataClass = 1 ';
            $baseItmRow = $this->_adapter->query($itmsql)->execute(array(':OrderSeq' => $oseq))->current();

            foreach ( $ri as $row ) {

                // 比較先データの取得
                $diffOrdRow = $this->_adapter->query($ordsql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current();
                $diffItmRow = $this->_adapter->query($itmsql)->execute(array(':OrderSeq' => $row['OrderSeq']))->current();

                // 全項目の比較
                $booDiff = true;
                $booDiff = $booDiff && $baseOrdRow['SiteId']                == $diffOrdRow['SiteId'];
                $booDiff = $booDiff && $baseOrdRow['PostalCode']            == $diffOrdRow['PostalCode'];
                $booDiff = $booDiff && $baseOrdRow['RegUnitingAddress']     == $diffOrdRow['RegUnitingAddress'];
                $booDiff = $booDiff && $baseOrdRow['RegNameKj']             == $diffOrdRow['RegNameKj'];
                $booDiff = $booDiff && $baseOrdRow['RegPhone']              == $diffOrdRow['RegPhone'];
                $booDiff = $booDiff && $baseOrdRow['DestPostalCode']        == $diffOrdRow['DestPostalCode'];
                $booDiff = $booDiff && $baseOrdRow['RegDestUnitingAddress'] == $diffOrdRow['RegDestUnitingAddress'];
                $booDiff = $booDiff && $baseOrdRow['RegDestNameKj']         == $diffOrdRow['RegDestNameKj'];
                $booDiff = $booDiff && $baseOrdRow['RegDestPhone']          == $diffOrdRow['RegDestPhone'];
                $booDiff = $booDiff && $baseOrdRow['UseAmount']             == $diffOrdRow['UseAmount'];
                $booDiff = $booDiff && $baseItmRow['ItemInfo']              == $diffItmRow['ItemInfo'];

                if ( $booDiff ) {
                    // 全属性が同じだったら、戻り値にtrueを設定
                    $booFnc = true;

                    // 与信OKチケットを更新する
                    $mdlCot = new TableCreditOkTicket($this->_adapter);
                    $data = array(
                        'Status' => '1',
                        'UseOrderSeq' => $oseq,
                        'UseDate' => date('Y-m-d H:i:s'),
                    );
                    $mdlCot->saveUpdate($data, $row['Seq']);
                    $GLOBALS['CreditLog']['CotSeq'] = $row['Seq'];
                    break;
                }
            }
        }

        return $booFnc;
    }
}
