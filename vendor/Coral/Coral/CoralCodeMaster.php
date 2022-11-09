<?php
namespace Coral\Coral;

use models\Table\TableCode;
use models\Table\TablePrefecture;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use models\View\MypageViewPrefecture;

class CoralCodeMaster
{
	private static $_cache;
	protected $_adapter = null;

    /**
     * コンストラクタ
     *
     * @param Adapter $adapter アダプタ
     */
    public function __construct(Adapter $adapter)
    {
        $this->_adapter = $adapter;
    }

// Del By Takemasa(NDC) 20141211 Stt 方式変更(廃止)
// 	/**
// 	 * 指定クラスのマスターデータリストを取得する。
// 	 * クラスに一致するデータは内部でキャッシュされるため、DBアクセスを最小限に抑える。
// 	 *
// 	 * @static
// 	 * @access private
// 	 * @param int $class マスタークラス
// 	 * @return array 指定クラスに一致するマスターデータ（連想配列）の配列
// 	 */
// 	private static function _getMasterCodes($class) {
// 		if( ! self::$_cache ) self::$_cache = array();
//
// 		if( ! isset(self::$_cache[$class]) ) {
// 			$mdl = new Table_GeneralPurpose(Application::getInstance()->dbAdapter);
// 			self::$_cache[$class] = $mdl->getMasterByClass($class)->toArray();		// キャッシュにあたりtoArray()で連想配列にしておく
// 		}
//
// 		return self::$_cache[$class];
// 	}
// Del By Takemasa(NDC) 20141211 Stt 方式変更(廃止)

	/**
	 * 指定クラスのマスター取得
	 *
	 * @param int マスタークラス
	 * @param array 最初に追加するアイテム
	 * @return array マスターデータ
	 */
	public function getMasterCodes($class, $baseItem = null)
	{
	    if (isset($baseItem)) {
            foreach ($baseItem as $key => $value) {
                $result[$key] = $value;
            }
        }

        $mdl = new TableCode($this->_adapter);
        $rs = $mdl->getMasterByClass($class);
        foreach ($rs as $row) {
            $result[$row['KeyCode']] = $row['KeyContent'];
        }

        return $result;
	}

	/**
	 * 指定クラス・コードのキャプションを取得する
	 *
	 * @return string キャプション
	 */
	public function getMasterCaption($class, $code, $isShortCaption = false)
	{
        $mdl = new TableCode($this->_adapter);
        $caption = ($isShortCaption) ? $mdl->getMasterShortCaption($class, $code) : $mdl->getMasterCaption($class, $code);
        return $caption;
	}

	/**
	 * 入金形態マスターを取得する。
	 *
	 * @return array 入金形態マスター
	 */
	public function getReceiptMethodMaster()
	{
		return array(0 => '未入金', 2 => '郵便局', 3 => '銀行', 4 => 'LINE Pay');
	}

	/**
	 * 入金形態キャプションを取得する。
	 *
	 * @return string 入金形態キャプション
	 */
	public function getReceiptMethodCaption($code)
	{
		switch($code)
		{
			case 1:
				$result = 'コンビニ';
				break;
			case 2:
				$result = '郵便局';
				break;
			case 3:
				$result = '銀行';
				break;
			case 4:
				$result = 'LINE Pay';
				break;
			default:
				$result = '未入金';
				break;
		}

		return $result;
	}

	/**
	 * 入金形態キャプションを取得する。(コードマスターから取得)
	 *
	 * @return string 入金形態キャプション
	 */
	public function getReceiptMethodCaptionM_Code($code)
	{
		    // 入金方法
	    if (! empty ( $code )) {
	        // コードマスターから入金方法のコメントを取得
	        $mdlc = new TableCode($this->_adapter);
	        $ReceiptMethod = $mdlc->find ( 198, $code )->current ();
	        $result = $ReceiptMethod['KeyContent'];
	    }

		return $result;
	}

	/**
	 * 与信情報の有効無効マスタを取得する。
	 *
	 * @return array 有効マスタ
	 */
	public function getCreditConditionValidFlgMaster()
	{
		return array(-1 => '---', 1 => '有効', 0 => '無効');
	}

	/**
	 * プランマスターを取得する
	 *
	 * @param boolean $isDummyOn ダミーを必要とするか？(初期値:true)
	 * @return array プランマスター
	 */
	public function getPlanMaster($isDummyOn = true)
	{
        $sql = " SELECT PricePlanId, PricePlanName FROM M_PricePlan WHERE ValidFlg = 1 ORDER BY PricePlanId ";

        $ri = $this->_adapter->query($sql)->execute(null);

        $result = ($isDummyOn) ? array(0 => '-----') : array();
        foreach ($ri as $row) {
            $result[$row['PricePlanId']] = $row['PricePlanName'];
        }

        return $result;
	}

	/**
	 * プランキャプションを取得する。
	 */
	public function getPlanCaption($code)
	{
        $sql = " SELECT PricePlanName FROM M_PricePlan WHERE PricePlanId = :PricePlanId ";

        $row = $this->_adapter->query($sql)->execute(array(':PricePlanId' => $code))->current();

        if (!$row) {
            return '';
        }

        return $row['PricePlanName'];
	}

	/**
	 * 締め日パターンマスターを取得する
	 *
	 * @param boolean $isDummyOn ダミーを必要とするか？(初期値:true)
	 * @return array 締め日パターンマスター
	 */
	public function getFixPatternMaster($isDummyOn = true)
	{
        return ($isDummyOn) ? $this->getMasterCodes(2, array(0 => '-----')) : $this->getMasterCodes(2);
	}

	/**
	 * 締め日パターンキャプションを取得する。
	 */
	public function getFixPatternCaption($code)
	{
		return $this->getMasterCaption(2, $code);
	}

	/**
	 * 社内与信条件カテゴリーマスターを取得する
	 *
	 * @return array 社内与信カテゴリーマスター
	 */
	public function getCreditCategoryMaster()
	{
		return $this->getMasterCodes(3, array(0 => '-----'));
	}

	/**
	 * 社内与信条件カテゴリーキャプションを取得する。
	 */
	public function getCreditCategoryCaption($code)
	{
		return $this->getMasterCaption(3, $code);
	}

	/**
	 * 社内与信条件クラスショートキャプションを取得する。
	 */
	public function getCreditCategoryMasterList()
	{
        $sql = " SELECT Class2 FROM M_Code WHERE CodeId = 3 AND ValidFlg = 1 ORDER BY KeyCode ";

        $ri = $this->_adapter->query($sql)->execute(null);

        return ResultInterfaceToArray($ri);
	}

	/**
	 * 社内与信条件検索方法マスターを取得する
	 *
	 * @return array 社内与信検索方法マスター
	 */
	public function getCreditSearchPatternMaster()
	{
	    return $this->getMasterCodes(192, array(0 => '-----'));
	}

	/**
	 * 社内与信条件検索方法キャプションを取得する。
	 */
	public function getCreditSearchPatternCaption($code)
	{
	    return $this->getMasterCaption(192, $code);
	}

	/**
	 * 社内与信条件検索方法ショートキャプションを取得する。
	 */
	public function getCreditSearchPatternMasterList()
	{
	    $sql = " SELECT Class2 FROM M_Code WHERE CodeId = 192 AND ValidFlg = 1 ORDER BY KeyCode ";

	    $ri = $this->_adapter->query($sql)->execute(null);

	    return ResultInterfaceToArray($ri);
	}

	/**
	 * 社内与信条件クラスマスターを取得する
	 *
	 * @return array 社内与信カテゴリーマスター
	 */
	public function getCreditClassMaster()
	{
		return $this->getMasterCodes(4, array(0 => '-----'));
	}

	/**
	 * 社内与信条件クラスキャプションを取得する。
	 */
	public function getCreditClassCaption($code)
	{
		return $this->getMasterCaption(4, $code);
	}

	/**
	 * 社内与信条件クラスショートキャプションを取得する。
	 */
	public function getCreditClassShortCaption($code)
	{
		return $this->getMasterCaption(4, $code, true);
	}

	/**
	 * 立替え条件クラスマスターを取得する
	 *
	 * @return array 立替条件マスター
	 */
	public function getPayChgConditionClassMaster()
	{
		return $this->getMasterCodes(6);
	}

	/**
	 * 立替え条件クラスキャプションを取得する。
	 */
	public function getPayChgConditionClassCaption($code)
	{
		return $this->getMasterCaption(6, $code);
	}

	/**
	 * 電話結果マスターを取得する
	 *
	 * @return array 電話結果マスター
	 */
	public function getCallResultMaster()
	{
		return $this->getMasterCodes(7, array(0 => '-----'));
	}

	/**
	 * 電話結果キャプションを取得する。
	 */
	public function getCallResultCaption($code)
	{
		return $this->getMasterCaption(7, $code);
	}

	/**
	 * メールチェック結果マスターを取得する
	 *
	 * @return array メールチェック結果マスター
	 */
	public function getSendMailResultMaster()
	{
		return $this->getMasterCodes(8, array(0 => '-----'));
	}

	/**
	 * メールチェック結果キャプションを取得する。
	 */
	public function getSendMailResultCaption($code)
	{
		return $this->getMasterCaption(8, $code);
	}

	/**
	 * データステータスマスターを取得する
	 *
	 * @return array メールチェック結果マスター
	 */
	public function getDataStatusMaster()
	{
		return $this->getMasterCodes(9);
	}

	/**
	 * データステータスキャプションを取得する。
	 */
	public function getDataStatusCaption($code)
	{
		return $this->getMasterCaption(9, $code);
	}

	/**
	 * サイト形態マスターを取得する
	 *
	 */
	public function getSiteFormMaster()
	{
		return $this->getMasterCodes(10, array(0 => '-----'));
	}

	/**
	 * サイト形態キャプションを取得する。
	 */
	public function getSiteFormCaption($code)
	{
		return $this->getMasterCaption(10, $code);
	}

	/**
	 * 着荷未確認理由マスターを取得する
	 *
	 */
	public function getNoArrReasonMaster()
	{
		return $this->getMasterCodes(11, array(0 => '-----'));
	}

	/**
	 * 着荷未確認理由キャプションを取得する。
	 */
	public function getNoArrReasonCaption($code)
	{
		return $this->getMasterCaption(11, $code);
	}

	/**
	 * 請求パターンマスターを取得する
	 *
	 */
	public function getClaimPatternMaster()
	{
		return $this->getMasterCodes(12);
	}

	/**
	 * 請求パターンキャプションを取得する。
	 */
	public function getClaimPatternCaption($code, $isShort = false)
	{
		return $this->getMasterCaption(12, $code, $isShort);
	}

	/**
	 * e電話帳マスターを取得する
	 *
	 */
	public function getEDenMaster()
	{
		return $this->getMasterCodes(13, array(0 => '---'));
	}

	/**
	 * e電話帳キャプションを取得する。
	 */
	public function getEDenCaption($code)
	{
		return $this->getMasterCaption(13, $code);
	}

	/**
	 * 電話履歴マスターを取得する
	 *
	 */
	public function getPhoneHistoryMaster()
	{
		return $this->getMasterCodes(14, array(0 => '---'));
	}

	/**
	 * 電話履歴キャプションを取得する。
	 */
	public function getPhoneHistoryCaption($code)
	{
		return $this->getMasterCaption(14, $code);
	}

// Del By Takemasa(NDC) 20150630 Stt 廃止
// 	/**
// 	 * 立替予定日マスターを取得する
// 	 *
// 	 */
// 	public function getPaymentSchedule()
// 	{
// 		return $this->getMasterCodes(15);
// 	}
//
// 	/**
// 	 * 立替予定日キャプションを取得する。
// 	 */
// 	public function getPaymentScheduleCaption($code)
// 	{
// 		return $this->getMasterCaption(15, $code);
// 	}
// Del By Takemasa(NDC) 20150630 End 廃止

	/**
	 * 初回請求支払期限算出方法マスターを取得する
	 *
	 */
	public function getLimitDatePatternMaster()
	{
		return $this->getMasterCodes(16);
	}

	/**
	 * 初回請求支払期限算出方法キャプションを取得する。
	 */
	public function getLimitDatePatternCaption($code)
	{
		return $this->getMasterCaption(16, $code);
	}

	/**
	 * 翌月指定日マスターを取得する
	 *
	 */
	public function getLimitDayMaster()
	{
		return $this->getMasterCodes(17);
	}

	/**
	 * 翌月指定日キャプションを取得する。
	 */
	public function getLimitDayCaption($code)
	{
		return $this->getMasterCaption(17, $code);
	}

	/**
	 * 督促分類マスターを取得する
	 *
	 */
	public function getRemindClassMaster()
	{
		return $this->getMasterCodes(18, array(0 => '---'));
	}

	/**
	 * 督促分類キャプションを取得する。
	 */
	public function getRemindClassCaption($code)
	{
		return $this->getMasterCaption(18, $code);
	}

	/**
	 * キャリアマスターを取得する
	 *
	 */
	public function getCarrierMaster()
	{
		return $this->getMasterCodes(19, array(0 => '---'));
	}

	/**
	 * キャリアキャプションを取得する。
	 */
	public function getCarrierCaption($code)
	{
		return $this->getMasterCaption(19, $code);
	}

	/**
	 * 最終回収手段マスターを取得する
	 *
	 */
	public function getFinalityCollectionMeanMaster()
	{
		return $this->getMasterCodes(20, array(0 => '---'));
	}

	/**
	 * 最終回収手段キャプションを取得する。
	 */
	public function getFinalityCollectionMeanCaption($code)
	{
		return $this->getMasterCaption(20, $code);
	}

	/**
	 * TEL有効マスターを取得する
	 *
	 */
	public function getValidTelMaster()
	{
		return $this->getMasterCodes(21, array(0 => '---'));
	}

	/**
	 * TEL有効キャプションを取得する。
	 */
	public function getValidTelCaption($code)
	{
		return $this->getMasterCaption(21, $code);
	}

	/**
	 * メール有効マスターを取得する
	 *
	 */
	public function getValidMailMaster()
	{
		return $this->getMasterCodes(22, array(0 => '---'));
	}

	/**
	 * メール有効キャプションを取得する。
	 */
	public function getValidMailCaption($code)
	{
		return $this->getMasterCaption(22, $code);
	}

	/**
	 * 住所有効マスターを取得する
	 *
	 */
	public function getValidAddressMaster()
	{
		return $this->getMasterCodes(23, array(0 => '---'));
	}

	/**
	 * 住所有効キャプションを取得する。
	 */
	public function getValidAddressCaption($code)
	{
		return $this->getMasterCaption(23, $code);
	}

	/**
	 * 住民票マスターを取得する
	 *
	 */
	public function getResidentCardMaster()
	{
		return $this->getMasterCodes(24, array(0 => '---'));
	}

	/**
	 * 住民票キャプションを取得する。
	 */
	public function getResidentCardCaption($code)
	{
		return $this->getMasterCaption(24, $code);
	}

	/**
	 * 手書き手紙マスターを取得する
	 *
	 */
	public function getLonghandLetterMaster()
	{
		return $this->getMasterCodes(25, array(0 => '---'));
	}

	/**
	 * 手書き手紙キャプションを取得する。
	 */
	public function getLonghandLetterCaption($code)
	{
		return $this->getMasterCaption(25, $code);
	}

	/**
	 * 追加連絡先状態マスターを取得する
	 *
	 */
	public function getCinfoStatusMaster()
	{
		return $this->getMasterCodes(26, array(0 => '---'));
	}

	/**
	 * 追加連絡先状態キャプションを取得する。
	 */
	public function getCinfoStatusCaption($code)
	{
		return $this->getMasterCaption(26, $code);
	}

	/**
	 * 口座種別マスターを取得する。
	 */
	public function getAccountClassMaster()
	{
		return $this->getMasterCodes(51);
	}

	/**
	 * 指定コードの口座種別キャプションを取得する。
	 */
	public function getAccountClassCaption($code)
	{
		return $this->getMasterCaption(51, $code);
	}

	/**
	 * ロールコードマスターを取得する
	 *
	 * @return array プランマスター
	 */
	public function getRoleCodeMaster($class1)
	{
		// return $this->getMasterCodes(52, array(0 => '-----'));
	    $baseItem = array(0 => '-----');

        foreach ($baseItem as $key => $value) {
            $result[$key] = $value;
        }

// 		$mdl = new TableCode($this->_adapter);
// 		$rs = $mdl->getMasterByClass($class);

		$sql = " SELECT * FROM M_Code WHERE ValidFlg = 1 AND CodeId = :CodeId AND Class1 = :Class1 ORDER BY KeyCode ";

		$stm = $this->_adapter->query($sql);

		$prm = array(
		        ':CodeId' => 52,
		        ':Class1' => $class1,
		);

		$ri = $stm->execute($prm);

		foreach ($ri as $row) {
		    $result[$row['KeyCode']] = $row['KeyContent'];
		}

		return $result;

	}

	/**
	 * ロールコードキャプションを取得する。
	 */
	public function getRoleCodeCaption($code)
	{
		return $this->getMasterCaption(52, $code);
	}

	/**
	 * 曜日マスターを取得する
	 *
	 * @return array プランマスター
	 */
	public function getWeekdayMaster()
	{
		return $this->getMasterCodes(53);
	}

	/**
	 * 曜日キャプションを取得する。
	 */
	public function getWeekdayCaption($code)
	{
		return $this->getMasterCaption(53, $code);
	}

	/**
	 * 曜日ショートキャプションを取得する。
	 */
	public function getWeekdayShortCaption($code)
	{
		return $this->getMasterCaption(53, $code, true);
	}

	/**
	 * 業種マスターを取得する
	 *
	 * @return array プランマスター
	 */
	public function getIndustryMaster()
	{
		return $this->getMasterCodes(54, array(0 => '-----'));
	}

	/**
	 * 業種キャプションを取得する。
	 */
	public function getIndustryCaption($code)
	{
		return $this->getMasterCaption(54, $code);
	}

	/**
	 * 推定月商マスターを取得する
	 *
	 * @return array プランマスター
	 */
	public function getPreSalesMaster()
	{
		return $this->getMasterCodes(55, array(0 => '-----'));
	}

	/**
	 * 推定月商キャプションを取得する。
	 */
	public function getPreSalesCaption($code)
	{
		return $this->getMasterCaption(55, $code);
	}

	/**
	 * 振込手数料マスターを取得する
	 *
	 * @return array プランマスター
	 */
	public function getTcClassMaster()
	{
		return $this->getMasterCodes(56, array(0 => '-----'));
	}

 	/**
	 * 振込手数料キャプションを取得する。
	 */
	public function getTcClassCaption($code)
	{
		return $this->getMasterCaption(56, $code);
	}

	/**
	 * 書類回収マスターを取得する
	 * @return array 書類回収マスター
	 */
	public function getDocCollectMaster()
	{
	    return $this->getMasterCodes(84, array(-1 => '-----'));
	}

	/**
	 * 書類回収キャプションを取得する
	 * @param int $code 書類回収コード
	 * @return string 書類回収キャプション
	 */
	public function getDocCollectCaption($code) {
		return $this->_getLiteralMasterCaption( $this->getDocCollectMaster(), $code );
 	}

	/**
	 * 審査結果マスターを取得する
	 * @return array 審査結果マスター
	 */
	public function getExaminationResultMaster($baseItem = null)
	{
	    if (is_null($baseItem)) {
	        return $this->getMasterCodes(75);
	    }
	    return $this->getMasterCodes(75 , array(-1 => '-----'));
	}

	/**
	 * 与信結果メール送信機能マスターを取得する
	 * @return array 与信結果メール送信機能マスター
	 */
	public function getCjMailModeMaster() {
		return $this->getMasterCodes(57);
	}

	/**
	 * 審査結果キャプションを取得する
	 * @param int $code 審査結果コード
	 * @return string 審査結果キャプション
	 */
	public function getExaminationResultCaption($code) {
		return $this->_getLiteralMasterCaption( $this->getExaminationResultMaster(), $code );
	}

	/**
	 * 非DBマスター項目のキャプションを取得する
	 * @param array $masterList 要素がCode/Captionを持つ連想配列のマスター配列
	 * @param int $code コード値
	 * @return string キャプション
	 */
	protected function _getLiteralMasterCaption($masterList, $code) {
		foreach($masterList as $master) {
			if( $master['KeyCode'] == $code ) return $master['KeyContent'];
		}
		return null;
	}

	/**
	 * 都道府県マスターを取得する
	 *
	 * @return array
	 */
	public function getPrefectureMaster()
	{
        $mdl = new TablePrefecture($this->_adapter);
        $rs = $mdl->getAll();

        if (!($rs->count() > 0)) { return null; }

        foreach ($rs as $row) {
            $result[$row['PrefectureCode']] = $row['PrefectureName'];
        }
        return $result;
	}

	/**
	 * 都道府県マスターを取得する
	 * MyPageに使用
	 *
	 * @return array
	 */
	public function getMyPagePrefectureMaster()
	{
	    $mdl = new MypageViewPrefecture($this->_adapter);
	    $rs = $mdl->getAll();

	    if (!($rs->count() > 0)) { return null; }

	    foreach ($rs as $row) {
	        $result[$row['PrefectureCode']] = $row['PrefectureName'];
	    }
	    return $result;
	}

	/**
	 * 指定都道府県コードの都道府県名を取得する。
	 *
	 * @param int $prefectureCode 都道府県コード
	 * @return string 都道府県名
	 */
	public function getPrefectureName($prefectureCode)
	{
        $mdl = new TablePrefecture($this->_adapter);
        return $mdl->getPrefectureName($prefectureCode);
	}

	/**
	 * 指定都道府県コードの都道府県名を取得する。
	 * MyPageに使用
	 *
	 * @param int $prefectureCode 都道府県コード
	 * @return string 都道府県名
	 */
	public function getMyPagePrefectureName($prefectureCode)
	{
	    $mdl = new MypageViewPrefecture($this->_adapter);
	    return $mdl->getPrefectureName($prefectureCode);
	}

	/**
	 * 指定都道府県名の都道府県コードを取得する。
	 *
	 * @param string $prefectureName 都道府県名
	 * @param boolean $isShortName 略称か否か
	 * @return int 都道府県コード
	 */
	public function getPrefectureCode($prefectureName, $isShortName = false)
	{
        $mdl = new TablePrefecture($this->_adapter);
        return $mdl->getPrefectureCode($prefectureName, $isShortName);
	}

	/**
	 * 与信自動事業者判定を取得する
	 */
	public function getAutoCreditJudgeModeMaster()
	{
	    return $this->getMasterCodes(80, array(-1 => '-----'));
	}

	/**
	 * 与信自動事業者判定キャプションを取得する
	 * @param int $code 与信自動事業者判定コード
	 * @return string 与信自動事業者判定キャプション
	 */
	public function getAutoCreditJudgeModeCaption($code) {
		return $this->getAutoCreditJudgeModeMaster()[$code];
	}

	/**
	 * 請求取りまとめ機能マスターを取得する
	 * @return array 請求取りまとめ機能マスター
	 */
	public function getCombinedClaimMode() {
		return $this->getMasterCodes(58);
	}

	/**
	 * 請求取りまとめ機能を取得する
	 * @param int $code 請求取りまとめコード
	 * @return string 請求取りまとめキャプション
	 */
	public function getCombinedClaimModeCaption($code) {
		return $this->getMasterCaption(58, $code);
	}

	/**
	 * get～Master()で取得したマスター配列を、コード値 => キャプション 形式の連想配列に変換する
	 * @param array $masterList マスターリスト
	 * @param null|boolean $useShortCaption キャプションに短い形式を使用するかのフラグ。デフォルトはfalse
	 * @return array コード値 => キャプション 形式に展開された連想配列
	 */
	public function masterToArray($masterList, $useShortCaption = false) {
	    $result = array();

		foreach($masterList as $master) {
			$result[$master['KeyCode']] = $master[$useShortCaption ? 'Class2' : 'KeyContent'];
		}
		return $result;
	}

	/**
	 * 請求自動ストップフラグマスターを取得する
	 *
	 * @return array 請求自動ストップフラグマスター
	 */
	public function getAutoClaimStopFlgMaster() {
		return array(
			array('KeyCode' => 0, 'KeyContent' => '自動ストップしない'),
			array('KeyCode' => 1, 'KeyContent' => '初回請求発行時に自動ストップ')
		);
	}

	/**
	 * 請求書任意注文番号印刷フラグマスターを取得する
	 *
	 * @return array 請求書任意注文番号印刷フラグマスター
	 */
	public function getPrintEntOrderIdOnClaimFlgMaster() {
		return array(
			array('KeyCode' => 0, 'KeyContent' => '印刷しない'),
			array('KeyCode' => 1, 'KeyContent' => '印刷する')
		);
	}

	/**
	 * キャンセル理由を取得する
	 */
	public function getCancelReasonMaster()
	{
	    return $this->getMasterCodes(90, array(-1 => '-----'));
	}

	/**
	 * 立替サイクルマスターを取得する
	 *
	 * @return array 立替サイクルマスター
	 */
	public function getPayingCycleMaster()
	{
	    $sql = " SELECT PayingCycleId, PayingCycleName FROM M_PayingCycle WHERE ValidFlg = 1 ORDER BY ListNumber ";

	    $ri = $this->_adapter->query($sql)->execute(null);

	    $result = array(0 => '-----');
	    foreach ($ri as $row) {
	        $result[$row['PayingCycleId']] = $row['PayingCycleName'];
	    }
	    return $result;
	}

	/**
	 * 保留理由マスターを取得する
	 * @return array 保留理由マスター
	 */
	public function getReserveReasonMaster()
	{
	    return $this->getMasterCodes(92, array(-1 => '-----'));
	}

	/**
	 * NG理由マスターを取得する
	 * @return array NG理由マスター
	 */
	public function getNGReasonMaster()
	{
	    return $this->getMasterCodes(190);
	}

	/**
	 * 入金科目マスターを取得する
	 * @return array 入金科目マスター
	 */
	public function getReceiptClassMaster()
	{
	    return $this->getMasterCodes(95, array(-1 => '-----'));
	}

	/**
	 * 雑収入雑損失科目マスターを取得する
	 * @return array 雑収入雑損失科目マスター
	 */
	public function getSundryClassMaster()
	{
	    return $this->getMasterCodes(96, array(-1 => '-----'));
	}

    /**
     * 収納代行会社IDマスタを取得する
     * @return array 収納代行会社IDマスタ
     */
    public function getCvsReceiptAgentMaster()
    {
        return $this->getMasterCodes(101, array(-1 => '-----'));
    }

    /**
     * 事業者管理統計ファイル種類マスターを取得する
     *
     * @return array 事業者管理統計ファイル種類マスター
     */
    public function getEntManagementFileTypeMaster() {
        return array(
                0 => '事業者別売上',
                1 => '売上統計',
        );
    }

    /**
     * スタイルシート種別マスターを取得する
     *
     * @return array スタイルシート種別
     */
    public function getOemStyleSheetsMaster()
    {
        return $this->getMasterCodes(102);
    }

    /**
     * スタイルシート種別キャプションを取得する。
     */
    public function getOemStyleSheetsCaption($code)
    {
        return $this->getMasterCaption(102, $code);
    }

    /**
     * ﾈｯﾄDE受取ｽﾃｰﾀｽを取得する
     */
    public function getNetStatusMaster()
    {
        return $this->getMasterCodes(188, array(-1 => '全て'));
    }

    /**
     * 申込ステータスを取得する
     */
    public function getRequestStatusMaster()
    {
        return $this->getMasterCodes(196, array(-1 => '全て', 0 => '-----'));
    }

    /**
     * 申込サブステータスを取得する
     */
    public function getRequestSubStatusMaster()
    {
        return $this->getMasterCodes(210, array(-1 => '全て', 0 => '-----'));
    }

    /**
     * 指定クラスのマスター取得(KeyCode⇒KeyContent逆転版)
     *
     * @param int $class マスタークラス
     * @param string $addwhere 追加抽出条件(SQL)
     * @return array マスターデータ(KeyCode⇒KeyContent逆転版)
     */
    public function getMasterCodesReverseKeyValue($class, $addwhere = null)
    {
        // SQL整形
        $sql = " SELECT KeyCode, KeyContent FROM M_Code WHERE ValidFlg = 1 AND CodeId = :CodeId ";
        if (!is_null($addwhere)) {
            $sql .= $addwhere;
        }

        $retval = array();
        $ri = $this->_adapter->query($sql)->execute(array(':CodeId' => $class));
        foreach ($ri as $row) {
            $retval[$row['KeyContent']] = $row['KeyCode'];
        }

        return $retval;
    }
}
