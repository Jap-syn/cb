<?php
namespace models\Logic\CreditJudge;
/**
 * 社内与信によるスコアリング結果を保持するエンティティクラス
 */
class LogicCreditJudgeLocalScore {
	/**
	 * 分類
	 * @var int
	 */
	public $Class = -1;

	/**
	 * スコア
	 * @var int
	 */
	public $Score = 0;

	/**
	 * 備考（メッセージ）
	 * @var string
	 */
	public $Note = '';
}
