<?php
namespace models\Logic\CreditJudge\Module;

use Zend\Db\Adapter\Adapter;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\Module\LogicCreditJudgeModuleCoreThreshold;
use models\Table\TableCreditJudgeThreshold;
use models\Table\TableOrder;
use models\Table\TableCjResult;
use models\Table\TableCjResultDetail;
use models\Table\TableEnterprise;
use models\Table\TableSite;
use Zend\Di\Definition\IntrospectionStrategy;

/**
 * ILU審査システムの審査結果を基にした拡張与信モジュール
 * judgeメソッドが返す可能性がある値：
 * 		JUDGE_RESULT_NG：与信NG確定
 * 		JUDGE_RESULT_OK：与信OK確定
 * 		JUDGE_RESULT_CONTINUE：審査継続
 */
class LogicCreditJudgeModuleILuSys extends LogicCreditJudgeAbstract {
	const LIMIT_OK_KEY = 'JUDGE_OK_LIMIT';

	const LIMIT_NG_KEY = 'JUDGE_NG_LIMIT';

	/**
	 * 基幹システム与信モジュール
	 *
	 * @var LogicCreditJudgeModuleCoreThreshold
	 */
	protected $_module_core;

	/**
     * データベースアダプタを指定して、LogicCreditJudgeModuleILuSysの新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     * @param int $creditCriterionId 与信判定基準ID
	 * @param array $options その他パラメータの連想配列
	 *
     */
    public function __construct(Adapter $adapter, $creditCriterionId, array $options = array()) {
		if(!is_array($options)) $options = array();
		$options = array_merge(array(
									 self::LIMIT_OK_KEY => 50,
									 self::LIMIT_NG_KEY => 0 ), $options);
		parent::__construct($adapter, $options);
		$this->initJudgeScoreLimits($creditCriterionId);
		$this->_module_core = new LogicCreditJudgeModuleCoreThreshold($adapter, $creditCriterionId);
    }

	/**
	 * 与信OK確定しきいスコア、与信NG確定しきいスコアをDB設定から初期化する
	 *
	 * @access protected
	 */
	protected function initJudgeScoreLimits($creditCriterionId) {
		$table = new TableCreditJudgeThreshold($this->getAdapter());
		$ri = $table->getByCriterionid($creditCriterionId);
		if ($ri->count() > 0) {
		    $row = $ri->current();
			$this
				->setOption(self::LIMIT_OK_KEY, $row['JudgeSystemHoldMAX'])
				->setOption(self::LIMIT_NG_KEY, $row['JudgeSystemHoldMIN']);
		}
	}

	/**
     * 新与信スコア判定
     *
     * @param $orsq　T_OrderのSeq
     * @return 1:OK -1:NG 3:審査継続
     */
    public function judge($orsq){

        //審査システムのスコア取得
        $sql = "SELECT * FROM T_CjResult WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC limit 1";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orsq));

        //取得できなかったら例外を投げる
        if (!($ri->count() > 0)) {
            throw new \Exception('T_CjResult not Data OrderSeq='.$orsq);
        }

        $score_result = $ri->current();

        // 注文情報取得
        $mdlo = new TableOrder($this->getAdapter());
        $order = $mdlo->find($orsq)->current();

        //取得できなかったら例外を投げる
        if (empty($order)) {
            throw new \Exception('T_Order not Data OrderSeq='.$orsq);
        }

        // 加盟店情報取得
        $mdle = new TableEnterprise($this->getAdapter());
        $enterprise = $mdle->findEnterprise($order['EnterpriseId'])->current();

        //取得できなかったら例外を投げる
        if (empty($enterprise)) {
            throw new \Exception('T_Enterprise not Data OrderSeq='.$orsq);
        }

        // 先に基幹システムで判定
        $coreResult =$this->_module_core->judge($orsq);
        if ($coreResult != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
            // 基幹システムで結果が確定した場合終了
            return $coreResult;
        }

        // 与信審査結果.リザルト=1 かつ 加盟店.与信審査システム連携=1 の場合、判定する
        if ($score_result['Result'] == 1 && $enterprise['JudgeSystemFlg'] == 1) {

            //---- スコア判定 ------

            // サイト情報を取得
            $mdls = new TableSite($this->getAdapter());
            $site = $mdls->findSite($order['SiteId'])->current();

            //取得できなかったら例外を投げる
            if (empty($site)) {
                throw new \Exception('T_Site not Data OrderSeq='.$orsq);
            }

            // サイト.与信判定方法で分岐
            if ($site['CreditJudgeMethod'] == 0) {
                // 与信判定方法=0(トータル)の場合

                //スコアがxx以下だったらNG
                if($this->getOption(self::LIMIT_NG_KEY) >= ($score_result['TotalScoreWeighting'] + $order['Incre_JudgeScoreTotal'])){
                    return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                }

                //スコアがxx以上だったらOK
                if($this->getOption(self::LIMIT_OK_KEY) <= ($score_result['TotalScoreWeighting'] + $order['Incre_JudgeScoreTotal'])){
                    return LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
                }

            }
            elseif ($site['CreditJudgeMethod'] == 1) {
                // 与信判定方法=1(個別)の場合

                // 与信審査結果詳細
                $mdlcj = new TableCjResultDetail($this->getAdapter());
                $cjResultDetail = ResultInterfaceToArray($mdlcj->findCjResult(array('CjrSeq' => $score_result['Seq'], 'ValidFlg' => 1), true));
                if (!empty($cjResultDetail)) {
                    foreach ($cjResultDetail as $row) {
                        $jr = $this->judgeScore($row['DetectionPatternScoreWeighting']);
                        if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                            return $jr;
                        }
                    }
                }

                // 社内与信－過去取引-スコア
                $jr = $this->judgeScore($order['Incre_PastOrderScore']);
                if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                    return $jr;
                }

                // 社内与信－備考欄-スコア
                $jr = $this->judgeScore($order['Incre_NoteScore']);
                if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                    return $jr;
                }

                // 社内与信－未払い-スコア
                $jr = $this->judgeScore($order['Incre_UnpaidScore']);
                if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                    return $jr;
                }

                // 社内与信－不払い-スコア
                $jr = $this->judgeScore($order['Incre_NonPaymentScore']);
                if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                    return $jr;
                }

                // 社内与信－身分証-スコア
                $jr = $this->judgeScore($order['Incre_IdentityDocumentScore']);
                if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                    return $jr;
                }

                // 社内与信－いたずらｷｬﾝｾﾙ-スコア
                 $jr = $this->judgeScore($order['Incre_MischiefCancelScore']);
                if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                    return $jr;
                }
            }
        }

        //OKでもNGでもなければ審査継続
        return LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE;
    }


    /**
     * 与信閾値の審査を行う
     *
     * @param int $score 審査するスコア
     * @return int 審査結果
     */
    protected function judgeScore($score) {
        //空の場合審査継続
        if (!isset($score)) {
            return LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE;
        }

        //スコアがxx以下だったらNG
        if($this->getOption(self::LIMIT_NG_KEY) >= $score){
            return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
        }

        //スコアがxx以上だったらOK
        if($this->getOption(self::LIMIT_OK_KEY) <= $score){
            return LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
        }

        //OKでもNGでもなければ審査継続
        return LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE;
    }
}

