<?php
namespace models\Logic\CreditJudge\Module;

use Zend\Db\Adapter\Adapter;
use models\Logic\CreditJudge\LogicCreditJudgeAbstract;
use models\Logic\CreditJudge\Connector\LogicCreditJudgeConnectorJintec;
use models\Table\TableOrder;
use models\Table\TableManagementCustomer;
use models\Table\TableJtcResult;
use models\Table\TableJtcResultDetail;

/**
 * ジンテック連携を利用した拡張与信モジュール
 * judgeメソッドが返す可能性がある値：
 * 		JUDGE_RESULT_NG：与信NG確定
 * 		JUDGE_RESULT_OK：与信OK確定
 * 		JUDGE_RESULT_PENDING：与信保留確定（＝手動与信対象）
 */
class LogicCreditJudgeModuleJintec extends LogicCreditJudgeAbstract {
	/**
	 * 既定のカテゴリマスターを取得する
	 *
	 * @static
	 * @return array
	 */
	public static function getDefaultCategoryMaster() {
		return array(
		             0 => "不明",
					 1 => "該当データなし",
					 2 => "無効",
					 3 => "都合停止あり",
					 4 => "直近加入（3ヶ月以下）",
					 5 => "通常利用（4ヶ月以上23ヶ月以下）",
					 6 => "変更過多",
					 7 => "反復利用",
					 8 => "長期利用",
					 9 => "ＭＳＧなし");
	}

	/**
	 * カテゴリマスター
	 *
	 * @access protected
	 * @var array
	 */
	protected $_categories;

	/**
	 * ユーザーID
	 *
	 * @access protected
	 * @var int
	 */
	protected $_userId;

	/**
	 * 自動化期間の場合true、それ以外はfalse
	 *
	 * @access protected
	 * @var bool
	 */
	protected $_autoFlg;

	/**
	 * ジンテック結果SEQ
	 * @var int
	 */
	protected $_jtcSeq;

	/**
     * データベースアダプタを指定して、LogicCreditJudgeModuleJintecの新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
	 * @param null | array $options 追加オプション
	 * @param null | array $category カテゴリマスター
     */
    public function __construct(Adapter $adapter, array $options = array(), array $category = array()) {
		parent::__construct($adapter, $options);

		$this
			->resetCategories()
			->margeCategories($category);
    }

	/**
	 * 現在のカテゴリマスターを取得する
	 *
	 * @return array
	 */
	public function getCategories() {
		return $this->_categories;
	}
	/**
	 * カテゴリマスターを設定する
	 *
	 * @param array $categories カテゴリマスター
	 * @return LogicCreditJudgeModuleJintec
	 */
	public function setCategories(array $categories = array()) {
		if(!is_array($categories)) $categories = array();
		$this->_categories = $categories;
		return $this;
	}
	/**
	 * 現在のカテゴリマスターに指定のカテゴリマスターをマージする
	 *
	 * @param null | array $categories マージする新しい値を含んだカテゴリマスター
	 * @return LogicCreditJudgeModuleJintec
	 */
	public function margeCategories(array $categories = array()) {
		if(!is_array($categories)) $categories = array();
		$this->_categories = array_merge($this->_categories, $categories);
		return $this;
	}
	/**
	 * カテゴリマスターをクラス規定の初期値にリセットする
	 *
	 * @return LogicCreditJudge_ModuleJintec
	 */
	public function resetCategories() {
		$this->_categories = self::getDefaultCategoryMaster();
		return $this;
	}
	/**
	 * カテゴリマスターキーを表示名に展開する
	 *
	 * @param mixed $category_num カテゴリマスターキー
	 * @return null | string
	 */
	public function parseCategory($category_num) {
		return isset($this->_categories[$category_num]) ? $this->_categories[$category_num] : null;
	}

    /**
     * ユーザーIDを取得する
     *
     * @return int
     */
    public function getUserId() {
        return $this->_userId;
    }

    /**
     * ユーザーIDを設定する
     *
     * @param int $userId ユーザーID
     */
    public function setUserId($userId) {
        $this->_userId = $userId;
    }

    /**
     * 自動化期間判定を取得する
     *
     * @return boolean
     */
    public function getAutoFlg() {
        return $this->_autoFlg;
    }

    /**
     * 自動化期間判定を設定する
     *
     * @param bool $autoFlg 自動化期間判定
     */
    public function setAutoFlg($autoFlg) {
        $this->_autoFlg = $autoFlg;
    }

    /**
     * ジンテック結果SEQを取得する
     * @return number
     */
    public function getJtcSeq() {
        return $this->_jtcSeq;
    }

    /**
     * 指定の注文に対しジンテック連携による審査を実行し、判定結果を返す。
     * 判定結果は以下の定数値のいずれかを返す。
     * JUDGE_RESULT_NG：与信NG確定
     * JUDGE_RESULT_OK：与信OK確定
     * JUDGE_RESULT_PENDING：与信保留確定（＝手動与信対象）
     * JUDGE_RESULT_CONTINUE：審査継続
     *
     * @param int $oseq 注文SEQ
     * @return int 判定結果
     */
	public function judge($orsq) {

        //電話番号取得
	    $mdlmc = new TableManagementCustomer($this->_adapter);
	    $ri = $mdlmc->findByOrderSeq($orsq);

        //取得に失敗した場合例外を投げる
        if (!($ri->count() > 0)) {
            throw new \Exception('T_Customer not Data OrderSeq='.$orsq);
        }

        $customer_result = $ri->current();

        //取得した電話番号の-を外す
        $phone_param = array("phone"=>str_replace("-","",$customer_result['Phone']));

        $connector = new LogicCreditJudgeConnectorJintec($this->getOptions());

        // ジンテック連携結果データ保存
        $mdljtc = new TableJtcResult($this->_adapter);
        $data = array(
            'OrderSeq' => $orsq,
            'SendDate' => date('Y-m-d H:i:s'),
            'ReceiveDate' => null,
            'Status' => 1,
            'Result' => null,
            'JintecManualJudgeFlg' => null,
        );
        $this->_jtcSeq = $mdljtc->saveNew($data);
        $GLOBALS['CreditLog']['JtcSeq'] = $this->_jtcSeq;

        //ジンテックからのフラグ取得
        $jintec_result = $connector->connect($phone_param);

        // フラグ1の6存在チェック
        $wifi = false;
        $key = '01';
        if (isset($jintec_result['tel1']['flags']['f'.$key]) && ($jintec_result['tel1']['flags']['f'.$key] == 6)) {
           $wifi = true;
        }

        //attentionがなければ例外
        if(!isset($jintec_result['tel1']['attention'])){
            throw new \Exception("Jintec Request Error");
        }
        $attention = $jintec_result['tel1']['attention'];

        // ジンテック連携結果データ更新
        $data = array(
            'ReceiveDate' => date('Y-m-d H:i:s'),
            'Status' => 9,
        );
        $mdljtc->saveUpdate($data, $this->_jtcSeq);

        // ジンテック結果詳細データ登録
        $mdljtcdtl = new TableJtcResultDetail($this->_adapter);
        foreach ($jintec_result as $key1 => $value1) {
            $data = array(
                    'JtcSeq' => $this->_jtcSeq,
                    'OrderSeq' => $orsq,
                    'ClassId' => $key1,
            );

            // 項目ID、値の調整
            if (is_array($value1)) {
                foreach ($value1 as $key2 => $value2) {
                    $data['ItemId'] = $key2;
                    $data['Value'] = is_array($value2) ? json_encode($value2) : $value2;
                    $mdljtcdtl->saveNew($data); // 登録
                }
            } else {
                    $data['ItemId'] = null;
                    $data['Value'] = $value1;
                    $mdljtcdtl->saveNew($data); // 登録
            }
        }

        //T_Order取得
        $sql = "SELECT Incre_Note FROM T_Order WHERE OrderSeq = :OrderSeq LIMIT 1";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orsq));

        //取得できなかったら例外を投げる
        if (!($ri->count() > 0)) {
            throw new \Exception('T_Order not Data OrderSeq='.$orsq);
        }

        $order_result = $ri->current();

        $orders = new TableOrder($this->_adapter);

        //書き込みデータ作成
        $wifi_msg = '';
        if ($wifi) {
            $wifi_msg = "ジンテックAPI:Wi-Fi"."\n";
        }
        $udata['Incre_Note'] = $order_result['Incre_Note'].$wifi_msg."ジンテックAPI:".$this->_categories[$attention]."\n----\n";
        $udata['Jintec_Flags'] = $attention;
        $udata['UpdateId'] = $this->getUserId();

// Mod By Takemasa(NDC) 20150108 Stt debugモード廃止
//         //デバッグフラグ
//         if($configs->debug_mode){
//
//             //-------- デバッグモードであれば作成したXMLをファイルに保存 --------
//             //保存先取得
//             $save_dir = $configs->save_dir;
//
//             //ファイルオープン
//             $fno = @fopen($save_dir."update.sql", 'w');
//
//             //ファイルオープンに失敗した場合エラー
//             if(!$fno){
//                 throw new Exception('cannot open file');
//             }
//
//             // 文字列を書き出します。
//             if(!fwrite($fno, "備考:".$udata['Incre_Note']." ジンテックフラグ:".$udata['Jintec_Flags'] )){
//                 throw new Exception('cannot write to file');
//             }
//
//             // ファイルをクローズします。
//             fclose($fno);
//
//         }else{
//             //T_Orderに備考・ステータス書き込み
//             $orders->saveUpdate($udata, $orsq);
//         }

        //T_Orderに備考・ステータス書き込み
        $orders->saveUpdate($udata, $orsq);
// Mod By Takemasa(NDC) 20150108 End debugモード廃止

        // ----- 2018.11.08 Add -----
        // サイト毎に設定されたジンテック判定区分で戻り値を変更する。
        // T_Site取得
        $sql = "SELECT * FROM T_Site s INNER JOIN T_Order o ON s.SiteId = o.SiteId WHERE o.OrderSeq = :OrderSeq LIMIT 1";
        $ri = $this->_adapter->query($sql)->execute(array(':OrderSeq' => $orsq));

        //取得できなかったら例外を投げる
        if (!($ri->count() > 0)) {
            throw new \Exception('T_Site not Data OrderSeq='.$orsq);
        }

        $site = $ri->current();

        // wifi利用はNG判定へ
        if (($wifi) && ($site['JintecJudge'] == 1)) {
            // 判定OKの場合
            if($site['JintecJudge10'] == 0) {
                return LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
            }
            // 判定NGの場合
            else if($site['JintecJudge10'] == 1) {
                return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
            }
            // 判定保留の場合
            else {
                if ($this->judgeNoPendingEnt($orsq)) {
                    // 事業者が保留無し事業者の場合、与信NGにする
                    return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                }
                else {
                    if ($this->getAutoFlg()) {
                        // 自動化期間の場合、与信NGにする
                        return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                    }
                    else {
                        // 通常与信期間の場合、与信保留にする
                        return LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING;
                    }
                }
            }
        }else if ($wifi) {
            // wifi利用はOK判定へ
            return LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
        }


        // ジンテック判定設定ありの場合、設定された内容で判定
        if ($site['JintecJudge'] == 1) {
            // 判定OKの場合
            if($site['JintecJudge' . $attention ] == 0) {
                return LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
            }
            // 判定NGの場合
            else if($site['JintecJudge' . $attention ] == 1) {
                return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
            }
            // 判定保留の場合
            else {
                if ($this->judgeNoPendingEnt($orsq)) {
                    // 事業者が保留無し事業者の場合、与信NGにする
                    return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                }
                else {
                    if ($this->getAutoFlg()) {
                        // 自動化期間の場合、与信NGにする
                        return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                    }
                    else {
                        // 通常与信期間の場合、与信保留にする
                        return LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING;
                    }
                }
            }
        }
        // ジンテック判定未設定の場合、デフォルトの値で判定
        else {
            //返却されたattentionによって返却値変更
            //戻り値5，8は与信OK、2，3，4，6,7は与信NG、1，9は手動与信への判定
            switch($attention){
                //保留
                case 0:
                case 1:
                case 9:
                    if ($this->judgeNoPendingEnt($orsq)) {
                        // 事業者が保留無し事業者の場合、与信NGにする
                        return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                    }
                    else {
                        if ($this->getAutoFlg()) {
                            // 自動化期間の場合、与信NGにする
                            return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                            }
                        else {
                            // 通常与信期間の場合、与信保留にする
                            return LogicCreditJudgeAbstract::JUDGE_RESULT_PENDING;
                        }
                    }
                    break;
                    //与信NG
                case 2:
                case 3:
                case 4:
                case 6:
                case 7:
                    return LogicCreditJudgeAbstract::JUDGE_RESULT_NG;
                    break;

                    //与信OK
                case 5:
                case 8:
                    return LogicCreditJudgeAbstract::JUDGE_RESULT_OK;
                    break;

                    //知らないものだったら例外
                default:
                    throw new \Exception(sprintf("Error unknown attention [%s]", $attention));
            }
        }
	}
}
