<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Coral\Base\BaseGeneralUtils;
use models\Table\TableCustomer;
use models\Table\TableMypageOrder;
use models\Table\TableSystemProperty;
use Coral\Base\Auth\BaseAuthUtility;
use Coral\Coral\CoralValidate;

class LogicMypageOrder {

    /**
     * マイページ作成の試行回数
     * @var int
     */
    const MYPAGE_RAND_CHARANGE = 5;

    /**
     * アダプタ
     *
     * @var Adapter
     */
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

    /**
     * 注文マイページの作成処理を行います
     * @param int $oseq 注文SEQ
     * @param date $limitDate 支払期限
     * @param int $oem_id OEMID
     * @param int $userId ユーザーID
     * @param BaseAuthUtility $authUtil
     * @throws \Exception
     */
    public function createMypageOrder($oseq, $limitDate, $oem_id, $userId, $authUtil) {
        $mdlc = new TableCustomer($this->_adapter);
        $mdlmo = new TableMypageOrder($this->_adapter);
        $mdlsysp = new TableSystemProperty($this->_adapter);

        try {

            // 注文マイページ有効期間日数の取得
            $validdays = $mdlsysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'OrderMypageValidDays');
            if (!CoralValidate::isInt($validdays)) $validdays = 150; // 取得に失敗した場合は150日

            // 有効期限の設定
            $validToDate = $limitDate;
            if ($validdays > 0 ) {  // 現実的にマイナスを設定することはありえないと思うが、念のため保護
                $validToDate = date('Y-m-d', strtotime(sprintf('%s +%d day', $limitDate , $validdays)));
            }

            // ｱｸｾｽ用URL有効期間日数の取得
            $accessKeyValiddays = $mdlsysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'OrderMypageAccessUrlValidDays');
            if (!CoralValidate::isInt($accessKeyValiddays)) $accessKeyValiddays = 14; // 取得に失敗した場合は14日

            // ｱｸｾｽ用URLの有効期限作成
            $accessUrlValidToDate = $limitDate;
            if ($accessKeyValiddays > 0 ) {  // 現実的にマイナスを設定することはありえないと思うが、念のため保護
                $accessUrlValidToDate = date('Y-m-d', strtotime(sprintf('%s +%d day', $limitDate , $accessKeyValiddays)));
            }

            // 注文マイページの存在チェック
            $sql = " SELECT * FROM T_MypageOrder WHERE OrderSeq = :OrderSeq ORDER BY Seq DESC Limit 1 ";
            $prm = array(
                ':OrderSeq' => $oseq,
            );
            $row = $this->_adapter->query($sql)->execute($prm)->current();

            if ($row) {
                // データが存在していれば注文マイページを再利用する
                $sql = "";
                $sql .= " UPDATE T_MypageOrder ";
                $sql .= " SET    ValidToDate = :ValidToDate ";
                $sql .= "       ,UpdateId = :UpdateId ";
                $sql .= "       ,UpdateDate = :UpdateDate ";
                $sql .= "       ,ValidFlg = :ValidFlg ";
                $sql .= " WHERE  Seq = :Seq ";

                $prm = array(
                    ':ValidToDate'           => $validToDate,            // 有効期限を更新する
                    ':UpdateId'              => $userId,                 // 更新者
                    ':UpdateDate'            => date('Y-m-d'),           // 更新日時
                    ':ValidFlg'              => 1,                       // 無効だったとしても有効にする
                    ':Seq'                   => $row['Seq'],             // 無効だったとしても有効にする
                );

                $this->_adapter->query($sql)->execute($prm);

                return; // 更新処理を終了
            }

            // ｱｸｾｽ用URLの生成
            $accessKey = null;
            for ( $i = 0; $i < self::MYPAGE_RAND_CHARANGE ; $i++ ) {
                $accessKey = BaseGeneralUtils::makeRandStr(50);  // 50文字

                // すでに存在するかチェック
                $cnt = $mdlmo->countByAccessKey($accessKey);
                if ($cnt == 0 ) break; // 重複していなければ確定
                if ($i >= self::MYPAGE_RAND_CHARANGE - 1) throw new \Exception('注文マイページの生成に失敗しました'); // 指定回数試行してもNGだった場合はエラー
            }

            // ランダム文字列を作成
            $random = null;
            $ri = $mdlc->findCustomer(array('OrderSeq' => $oseq));
            $phone = $ri->current()['SearchPhone'];  // SearchPhoneは、数字のみにした番号なのでこれを使う

            for ( $i = 0; $i < self::MYPAGE_RAND_CHARANGE ; $i++ ) {
                $random = BaseGeneralUtils::makeRandStr(8);  // ランダム文字列を生成
                $loginId = $random . $phone;

                // すでに存在するかチェック
                $cnt = $mdlmo->countByLoginId($loginId);
                if ($cnt == 0 ) break; // 重複していなければ確定
                if ($i >= self::MYPAGE_RAND_CHARANGE - 1) throw new \Exception('注文マイページの生成に失敗しました'); // 指定回数試行してもNGだった場合はエラー
            }

            // 指定注文SEQを論理削除（再発行の場合を考慮）
            $mdlmo->deleteByOrderSeq($oseq, $userId);

            // 注文マイページ登録
            $data = array(
                    'OrderSeq'      => $oseq,
                    'Token'         => $random,                                              // ランダム文字列
                    'Phone'         => $phone,
                    'LoginId'       => $loginId,                                              // ランダム文字列＋電話番号
                    'LoginPasswd'   => $authUtil->generatePasswordHash($loginId, $random),    // ランダム文字列（ハッシュ化済み）
                    'Hashed'        => 1,                                                   // ハッシュ化済み
                    'ValidToDate'   => $validToDate,                                        // 有効期限
                    'OemId'         => $oem_id,                                             // OEMID
                    'AccessKey'     => $accessKey,                                          // ｱｸｾｽURL
                    'AccessKeyValidToDate' => $accessUrlValidToDate,                        // ｱｸｾｽURL用有効期限
                    'RegistId'      => $userId,
                    'UpdateId'      => $userId,
                    'ValidFlg'      => 1,
            );
            $mdlmo->saveNew($data);

        } catch( \Exception $e) {
            // 今のところは上位へ委譲
            throw $e;
        }
    }

    /**
     * (注文マイページ作成処理に必要な)情報生成
     *
     * @param int $oseq 注文SEQ
     * @param string $searchPhone 検索用電話番号
     * @param array $makeInfo (戻り引数)注文マイページ作成処理に必要な情報
     * @return boolean true:成功／false:失敗
     */
    public function makeCreateInfo($oseq, $searchPhone, &$makeInfo)
    {
        //NOTE. 存在ﾁｪｯｸなし(makeRandStrは58ｷｬﾗｸﾀの組み合わせ生成であり、最低でも8ﾊﾞｲﾄ生成は[58^8≒約128兆]のﾊﾟﾀｰﾝであり、且つ、$searchPhoneとの組み合わせでもある為、重複が想定できない)

        // ｱｸｾｽ用URLの生成
        $accessKey = BaseGeneralUtils::makeRandStr(42) . sprintf("%08x", $oseq); // 50文字

        // ﾗﾝﾀﾞﾑ文字列を作成
        $random = BaseGeneralUtils::makeRandStr(8);
        $loginId = $random . $searchPhone;

        // 戻り引数へ値設定
        $makeInfo['accessKey'] = $accessKey;
        $makeInfo['random'] = $random;
        $makeInfo['phone'] = $searchPhone;
        $makeInfo['loginId'] = $loginId;

        return true;
    }

    /**
     * 注文マイページの作成処理を行います(EX)
     *
     * @param int $oseq 注文SEQ
     * @param date $limitDate 支払期限
     * @param int $oem_id OEMID
     * @param int $userId ユーザーID
     * @param BaseAuthUtility $authUtil
     * @param int $maxMypageOrderSeq 注文SEQに紐付く最大のT_MypageOrder.Seq(なし時は-1)
     * @param string $updTrgtMypageOrderSeq 注文SEQに紐付くT_MypageOrder.Seqのカンマ区切り連結文字(なし時は空欄)
     * @param array $makeInfo 注文マイページ作成処理に必要な情報
     * @throws \Exception
     */
    public function createMypageOrderEx($oseq, $limitDate, $oem_id, $userId, $authUtil, $maxMypageOrderSeq, $updTrgtMypageOrderSeq, $makeInfo) {
        $mdlc = new TableCustomer($this->_adapter);
        $mdlmo = new TableMypageOrder($this->_adapter);
        $mdlsysp = new TableSystemProperty($this->_adapter);

        try {

            // 注文マイページ有効期間日数の取得
            $validdays = $mdlsysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'OrderMypageValidDays');
            if (!CoralValidate::isInt($validdays)) $validdays = 150; // 取得に失敗した場合は150日

            // 有効期限の設定
            $validToDate = $limitDate;
            if ($validdays > 0 ) {  // 現実的にマイナスを設定することはありえないと思うが、念のため保護
                $validToDate = date('Y-m-d', strtotime(sprintf('%s +%d day', $limitDate , $validdays)));
            }

            // ｱｸｾｽ用URL有効期間日数の取得
            $accessKeyValiddays = $mdlsysp->getValue(TableSystemProperty::DEFAULT_MODULE, 'systeminfo', 'OrderMypageAccessUrlValidDays');
            if (!CoralValidate::isInt($accessKeyValiddays)) $accessKeyValiddays = 14; // 取得に失敗した場合は14日

            // ｱｸｾｽ用URLの有効期限作成
            $accessUrlValidToDate = $limitDate;
            if ($accessKeyValiddays > 0 ) {  // 現実的にマイナスを設定することはありえないと思うが、念のため保護
                $accessUrlValidToDate = date('Y-m-d', strtotime(sprintf('%s +%d day', $limitDate , $accessKeyValiddays)));
            }

            // 注文マイページの存在チェック
            if ($maxMypageOrderSeq != -1) {
                // データが存在していれば注文マイページを再利用する
                $sql = "";
                $sql .= " UPDATE T_MypageOrder ";
                $sql .= " SET    ValidToDate = :ValidToDate ";
                $sql .= "       ,UpdateId = :UpdateId ";
                $sql .= "       ,UpdateDate = :UpdateDate ";
                $sql .= "       ,ValidFlg = :ValidFlg ";
                $sql .= " WHERE  Seq = :Seq ";

                $prm = array(
                        ':ValidToDate'           => $validToDate,            // 有効期限を更新する
                        ':UpdateId'              => $userId,                 // 更新者
                        ':UpdateDate'            => date('Y-m-d'),           // 更新日時
                        ':ValidFlg'              => 1,                       // 無効だったとしても有効にする
                        ':Seq'                   => $maxMypageOrderSeq,
                );

                $this->_adapter->query($sql)->execute($prm);

                return; // 更新処理を終了
            }

            $accessKey  = $makeInfo['accessKey'];
            $random     = $makeInfo['random'];
            $phone      = $makeInfo['phone'];
            $loginId    = $makeInfo['loginId'];

            // [accessKey]と[random]が未決定時は例外をスロー
            if ($accessKey == "" || $random == "") {
                throw new \Exception('注文マイページの生成に失敗しました');
            }

            // 指定注文SEQを論理削除（再発行の場合を考慮）
            if ($updTrgtMypageOrderSeq != "") {
                $this->_adapter->query(" UPDATE T_MypageOrder SET UpdateDate = :UpdateDate, UpdateId = :UpdateId, ValidFlg = 0 WHERE Seq IN ( " . $updTrgtMypageOrderSeq . " ) "
                )->execute(array(':UpdateDate' => date('Y-m-d H:i:s'), ':UpdateId' => $userId));
            }

            // 注文マイページ登録
            $data = array(
                    'OrderSeq'      => $oseq,
                    'Token'         => $random,                                              // ランダム文字列
                    'Phone'         => $phone,
                    'LoginId'       => $loginId,                                              // ランダム文字列＋電話番号
                    'LoginPasswd'   => $authUtil->generatePasswordHash($loginId, $random),    // ランダム文字列（ハッシュ化済み）
                    'Hashed'        => 1,                                                   // ハッシュ化済み
                    'ValidToDate'   => $validToDate,                                        // 有効期限
                    'OemId'         => $oem_id,                                             // OEMID
                    'AccessKey'     => $accessKey,                                          // ｱｸｾｽURL
                    'AccessKeyValidToDate' => $accessUrlValidToDate,                        // ｱｸｾｽURL用有効期限
                    'RegistId'      => $userId,
                    'UpdateId'      => $userId,
                    'ValidFlg'      => 1,
            );
            $mdlmo->saveNew($data);

        } catch( \Exception $e) {
            // 今のところは上位へ委譲
            throw $e;
        }
    }
}
