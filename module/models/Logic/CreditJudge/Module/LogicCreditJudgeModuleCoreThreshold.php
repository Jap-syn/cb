<?php
namespace models\Logic\CreditJudge\Module;

use Zend\Db\Adapter\Adapter;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Table\TableCreditJudgeThreshold;
use models\Table\TableOrder;
use models\Table\TableCjResult;
use models\Table\TableCjResultDetail;
use models\Table\TableEnterprise;
use models\Table\TableSite;

/**
 * 基幹システムの審査結果を基にした拡張与信モジュール
 * judgeメソッドが返す可能性がある値：
 * 		JUDGE_RESULT_NG：与信NG確定
 * 		JUDGE_RESULT_OK：与信OK確定
 * 		JUDGE_RESULT_CONTINUE：審査継続
 */
class LogicCreditJudgeModuleCoreThreshold extends LogicCreditJudgeAbstract {
	const LIMIT_OK_KEY = 'JUDGE_OK_LIMIT';

	const LIMIT_NG_KEY = 'JUDGE_NG_LIMIT';

	/**
     * データベースアダプタを指定して、LogicCreditJudgeModuleCoreThresholdの新しいインスタンスを初期化する
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
				->setOption(self::LIMIT_OK_KEY, $row['CoreSystemHoldMAX'])
				->setOption(self::LIMIT_NG_KEY, $row['CoreSystemHoldMIN']);
		}
	}

	/**
     * 新与信スコア判定
     *
     * @param $orsq　T_OrderのSeq
     * @return 1:OK -1:NG 3:審査継続
     */
    public function judge($orsq){

        // 注文情報取得
        $mdlo = new TableOrder($this->getAdapter());
        $order = $mdlo->find($orsq)->current();

        //取得できなかったら例外を投げる
        if (empty($order)) {
            throw new \Exception('T_Order not Data OrderSeq='.$orsq);
        }

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
            if($this->getOption(self::LIMIT_NG_KEY) >= ($order['Incre_CoreScoreTotal'] + $order['Incre_JudgeScoreTotal'])){
                return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
            }

            //スコアがxx以上だったらOK
            if($this->getOption(self::LIMIT_OK_KEY) <= ($order['Incre_CoreScoreTotal'] + $order['Incre_JudgeScoreTotal'])){
                return LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
            }

        }
        elseif ($site['CreditJudgeMethod'] == 1) {
            // 与信判定方法=1(個別)の場合

            // 購入者.社内与信―スコア合計の取得
            // 配送先.社内与信－スコア合計の取得
            $sql  = "SELECT C.Incre_ScoreTotal AS Incre_ScoreTotal_C, DD.Incre_ScoreTotal AS Incre_ScoreTotal_D ";
            $sql .= "FROM T_OrderItems OI ";
            $sql .= "LEFT JOIN T_Customer C ON C.OrderSeq = OI.OrderSeq AND C.ValidFlg = 1 ";
            $sql .= "LEFT JOIN T_DeliveryDestination DD ON OI.DeliDestId = DD.DeliDestId AND DD.ValidFlg = 1 ";
            $sql .= "WHERE OI.ValidFlg = 1 AND OI.DataClass = 1 AND OI.OrderSeq = :OrderSeq ORDER BY OI.OrderItemId limit 1 ";
            $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orsq));
            if (!($ri->count() > 0)) {
                throw new \Exception('T_CjResult not Data OrderSeq='.$orsq);
            }
            $incre_score = $ri->current();

            //購入者.社内与信―スコア合計
            $jr = $this->judgeScore($incre_score['Incre_ScoreTotal_C']);
            if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                return $jr;
            }

            //配送先.社内与信－スコア合計
            $jr = $this->judgeScore($incre_score['Incre_ScoreTotal_D']);
            if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                return $jr;
            }

            // 社内与信－商品合計-スコア
            $jr = $this->judgeScore($order['Incre_ItemScoreTotal']);
            if ($jr != LogicCreditJudgeAbstract::JUDGE_RESULT_CONTINUE) {
                return $jr;
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

