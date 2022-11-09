<?php
namespace models\Logic;

use Zend\Db\Adapter\Adapter;
use Zend\Text\Table\Table;
use models\Table\TableNgAccessEnterprise;

/**
 * 不正アクセスクラス
 */
class LogicNgAccessIp
{
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
     * 不正アクセスIP登録
     *
     * @param string $ipAddress IPアドレス
     * @param string $savePath ファイル保存パス
     * @see システムで設定された[不正アクセスリミット]以上になる時、不正アクセスIPファイル保存フォルダへ、
     *      "IPアドレス名.txt"(空ファイル)を作成する
     */
    public function registNgAccess($ipAddress, $savePath)
    {
        // 不正アクセスIPホワイトリスト考慮
        $whiteList = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessIpWhiteList' "
            )->execute(null)->current()['PropValue'];
        if (false !== strpos($whiteList, $ipAddress)) {
            return;
        }

        $mdl = new \models\Table\TableNgAccessIp($this->_adapter);

        // クライアントIPアドレスの登録(新規or更新)
        $row = $mdl->findIpAddress($ipAddress);
        if ($row) {

            // 回数=0の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
            if ($row['Count'] == 0) {
                $mdl->saveUpdate(array('Count' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $row['Seq']);
                return;
            }

            // 連続不正アクセス判定基準間隔(秒)の取得＆現時刻と比較し間隔外の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
            $ngAccessReferenceTerm = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessReferenceTerm' "
                )->execute(null)->current()['PropValue'];
            if (strtotime(date('Y-m-d H:i:s')) - strtotime($row['NgAccessReferenceDate']) > $ngAccessReferenceTerm) {
                $mdl->saveUpdate(array('Count' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $row['Seq']);
                return;
            }

            $mdl->saveUpdate(array('Count' => $row['Count'] + 1), $row['Seq']); // カウントのインクリメント

            $ngAccessIpLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessIpLimit' "
                )->execute(null)->current()['PropValue'];
            if ($row['Count'] + 1 >= $ngAccessIpLimit) {
                // 指定回数に到達する時、不正アクセスIPファイル作成
                touch($savePath . $ipAddress . '.txt');
            }
        }
        else {
            $mdl->saveNew(array('IpAddress' => $ipAddress, 'Count' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')));
        }
    }

    /**
     * 不正アクセス回数ゼロクリア
     *
     * @param string $ipAddress IPアドレス
     */
    public function resetNgAccess($ipAddress)
    {
        $mdl = new \models\Table\TableNgAccessIp($this->_adapter);

        $row = $mdl->findIpAddress($ipAddress);
        if (!$row) { return; }  // 該当なし時は以降処理なし

        // 回数を0で更新(リセット)
        $mdl->saveUpdate(array('Count' => 0, 'NgAccessReferenceDate' => null), $row['Seq']);
    }

    //--------------------------------------------------------------------------
    // 以下、加盟店(T_Enterprise)関連
    //--------------------------------------------------------------------------
    /**
     * 連続不正アクセスなしの加盟店ログイン成功か？
     *
     * 加盟店ログイン認証が成功しても、[NgAccessCount]が規定値以上の場合はfalseを戻す。
     * そうでない場合はカウンタを0で初期化しtrueを戻す
     *
     * @param array $rowEnterprise 加盟店レコード
     * @return boolean true:OK／false:NG
     */
    public function isNotNgAccessEnterprise($rowEnterprise)
    {
        $mdlent = new \models\Table\TableEnterprise($this->_adapter);
        $mdlngent = new TableNgAccessEnterprise($this->_adapter);

        $row = $mdlngent->find($rowEnterprise['EnterpriseId'])->current();
        if (!$row) {
            // データが取得出来なかったら新規登録
            $row = array(
                'EnterpriseId' => $rowEnterprise['EnterpriseId'],
                'NgAccessCount' => 0,
                'NgAccessReferenceDate' => null,
            );
            $mdlngent->saveNew($row);
        }

        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合はこの時点でNG
        if ((int)$row['NgAccessCount'] >= $ngAccessLoginLimit) {
            return false;
        }

        // カウントを0で更新
        $mdlngent->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $rowEnterprise['EnterpriseId']);

        return true;
    }

    /**
     * ログイン認証失敗による加盟店テーブルの更新
     *
     * @param array $rowEnterprise 加盟店レコード
     */
    public function updateEnterpriseNgAccess($rowEnterprise)
    {
        $mdlngent = new TableNgAccessEnterprise($this->_adapter);

        $row = $mdlngent->find($rowEnterprise['EnterpriseId'])->current();
        if (!$row) {
            // データが取得出来なかったら新規登録
            $row = array(
                    'EnterpriseId' => $rowEnterprise['EnterpriseId'],
                    'NgAccessCount' => 0,
                    'NgAccessReferenceDate' => null,
            );
            $mdlngent->saveNew($row);
        }

        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合は以降処理なし
        if ((int)$row['NgAccessCount'] >= $ngAccessLoginLimit) {
            return;
        }

        $mdlent = new \models\Table\TableEnterprise($this->_adapter);

        // 回数=0の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        if ($row['NgAccessCount'] == 0) {
            $mdlngent->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowEnterprise['EnterpriseId']);
            return;
        }

        // 連続不正アクセス判定基準間隔(秒)の取得＆現時刻と比較し間隔外の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        $ngAccessReferenceTerm = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginReferenceTerm' "
            )->execute(null)->current()['PropValue'];
        if (strtotime(date('Y-m-d H:i:s')) - strtotime($row['NgAccessReferenceDate']) > $ngAccessReferenceTerm) {
            $mdlngent->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowEnterprise['EnterpriseId']);
            return;
        }

        $mdlngent->saveUpdate(array('NgAccessCount' => $row['NgAccessCount'] + 1), $rowEnterprise['EnterpriseId']);
    }

    //--------------------------------------------------------------------------
    // 以下、OEMオペレーター(T_OemOperator)関連
    //--------------------------------------------------------------------------
    /**
     * 連続不正アクセスなしのOEMオペレーターログイン成功か？
     *
     * OEMオペレーターログイン認証が成功しても、[NgAccessCount]が規定値以上の場合はfalseを戻す。
     * そうでない場合はカウンタを0で初期化しtrueを戻す
     *
     * @param array $rowOemOperator OEMオペレーターレコード
     * @return boolean true:OK／false:NG
     */
    public function isNotNgAccessOemOperator($rowOemOperator)
    {
        $mdl = new \models\Table\TableOemOperator($this->_adapter);

        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合はこの時点でNG
        if ((int)$rowOemOperator['NgAccessCount'] >= $ngAccessLoginLimit) {
            return false;
        }

        // カウントを0で更新
        $mdl->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $rowOemOperator['OemOpId']);

        return true;
    }

    /**
     * ログイン認証失敗によるOEMオペレーターテーブルの更新
     *
     * @param array $rowOemOperator OEMオペレーターレコード
     */
    public function updateOemOperatorNgAccess($rowOemOperator)
    {
        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合は以降処理なし
        if ((int)$rowOemOperator['NgAccessCount'] >= $ngAccessLoginLimit) {
            return;
        }

        $mdl = new \models\Table\TableOemOperator($this->_adapter);

        // 回数=0の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        if ($rowOemOperator['NgAccessCount'] == 0) {
            $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowOemOperator['OemOpId']);
            return;
        }

        // 連続不正アクセス判定基準間隔(秒)の取得＆現時刻と比較し間隔外の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        $ngAccessReferenceTerm = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginReferenceTerm' "
            )->execute(null)->current()['PropValue'];
        if (strtotime(date('Y-m-d H:i:s')) - strtotime($rowOemOperator['NgAccessReferenceDate']) > $ngAccessReferenceTerm) {
            $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowOemOperator['OemOpId']);
            return;
        }

        $mdl->saveUpdate(array('NgAccessCount' => $rowOemOperator['NgAccessCount'] + 1), $rowOemOperator['OemOpId']);
    }

    //--------------------------------------------------------------------------
    // 以下、オペレーター(T_Operator)関連
    //--------------------------------------------------------------------------
    /**
     * 連続不正アクセスなしのオペレーターログイン成功か？
     *
     * オペレーターログイン認証が成功しても、[NgAccessCount]が規定値以上の場合はfalseを戻す。
     * そうでない場合はカウンタを0で初期化しtrueを戻す
     *
     * @param array $rowOperator オペレーターレコード
     * @return boolean true:OK／false:NG
     */
    public function isNotNgAccessOperator($rowOperator)
    {
        $mdl = new \models\Table\TableOperator($this->_adapter);

        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合はこの時点でNG
        if ((int)$rowOperator['NgAccessCount'] >= $ngAccessLoginLimit) {
            return false;
        }

        // カウントを0で更新
        $mdl->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $rowOperator['OpId']);

        return true;
    }

    /**
     * ログイン認証失敗によるオペレーターテーブルの更新
     *
     * @param array $rowOperator オペレーターレコード
     */
    public function updateOperatorNgAccess($rowOperator)
    {
        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合は以降処理なし
        if ((int)$rowOperator['NgAccessCount'] >= $ngAccessLoginLimit) {
            return;
        }

        $mdl = new \models\Table\TableOperator($this->_adapter);

        // 回数=0の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        if ($rowOperator['NgAccessCount'] == 0) {
            $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowOperator['OpId']);
            return;
        }

        // 連続不正アクセス判定基準間隔(秒)の取得＆現時刻と比較し間隔外の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        $ngAccessReferenceTerm = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessLoginReferenceTerm' "
            )->execute(null)->current()['PropValue'];
        if (strtotime(date('Y-m-d H:i:s')) - strtotime($rowOperator['NgAccessReferenceDate']) > $ngAccessReferenceTerm) {
            $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowOperator['OpId']);
            return;
        }

        $mdl->saveUpdate(array('NgAccessCount' => $rowOperator['NgAccessCount'] + 1), $rowOperator['OpId']);
    }

    //--------------------------------------------------------------------------
    // (以下、顧客／注文マイページ関連)
    //--------------------------------------------------------------------------
    /**
     * (マイページ)不正アクセスIP登録
     *
     * NOTE : [registNgAccess]に対し、参照するシステムプロペティが異なるのみ
     *
     * @param string $ipAddress IPアドレス
     * @param string $savePath ファイル保存パス
     * @see システムで設定された[不正アクセスリミット]以上になる時、不正アクセスIPファイル保存フォルダへ、
     *      "IPアドレス名.txt"(空ファイル)を作成する。
     */
    public function registNgAccessMypage($ipAddress, $savePath)
    {
        // 不正アクセスIPホワイトリスト考慮
        $whiteList = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessIpWhiteList' "
            )->execute(null)->current()['PropValue'];
        if (false !== strpos($whiteList, $ipAddress)) {
            return;
        }

        $mdl = new \models\Table\TableNgAccessIp($this->_adapter);

        // クライアントIPアドレスの登録(新規or更新)
        $row = $mdl->findIpAddress($ipAddress);
        if ($row) {

            // 回数=0の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
            if ($row['Count'] == 0) {
                $mdl->saveUpdate(array('Count' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $row['Seq']);
                return;
            }

            // 連続不正アクセス判定基準間隔(秒)の取得＆現時刻と比較し間隔外の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
            $ngAccessReferenceTerm = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessReferenceTerm' "
                )->execute(null)->current()['PropValue'];
            if (strtotime(date('Y-m-d H:i:s')) - strtotime($row['NgAccessReferenceDate']) > $ngAccessReferenceTerm) {
                $mdl->saveUpdate(array('Count' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $row['Seq']);
                return;
            }

            $mdl->saveUpdate(array('Count' => $row['Count'] + 1), $row['Seq']); // カウントのインクリメント

            $ngAccessIpLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessIpLimit' "
                )->execute(null)->current()['PropValue'];
            if ($row['Count'] + 1 >= $ngAccessIpLimit) {
                // 指定回数に到達する時、不正アクセスIPファイル作成
                touch($savePath . $ipAddress . '.txt');
            }
        }
        else {
            $mdl->saveNew(array('IpAddress' => $ipAddress, 'Count' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')));
        }
    }

    /**
     * (マイページ)不正アクセス回数ゼロクリア
     *
     * @param string $ipAddress IPアドレス
     */
    public function resetNgAccessMypage($ipAddress)
    {
        return $this->resetNgAccess($ipAddress);
    }

    //--------------------------------------------------------------------------
    // 以下、マイページ顧客(T_MypageCustomer)関連
    //--------------------------------------------------------------------------
    /**
     * 連続不正アクセスなしのマイページ顧客ログイン成功か？
     *
     * マイページ顧客ログイン認証が成功しても、[NgAccessCount]が規定値以上の場合はfalseを戻す。
     * そうでない場合はカウンタを0で初期化しtrueを戻す
     *
     * @param array|false $rowMypageCustomer マイページ顧客レコード
     * @return boolean true:OK／false:NG
     */
    public function isNotNgAccessMypageCustomer($rowMypageCustomer)
    {
        if (!$rowMypageCustomer) { return true; }    // $rowMypageCustomerが無効な時は直ちにtrueで戻る

        $mdl = new \models\Table\TableMypageCustomer($this->_adapter);

        // 規定値取得(MypageNgAccessLoginLimit)
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合はこの時点でNG
        if ((int)$rowMypageCustomer['NgAccessCount'] >= $ngAccessLoginLimit) {
            return false;
        }

        // カウントを0で更新
        $mdl->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $rowMypageCustomer['CustomerId']);

        return true;
    }

    /**
     * ログイン認証失敗によるマイページ顧客テーブルの更新
     *
     * @param array|false $rowMypageCustomer マイページ顧客レコード
     */
    public function updateMypageCustomerNgAccess($rowMypageCustomer)
    {
        if (!$rowMypageCustomer) { return; }    // $rowMypageCustomerが無効な時は直ちに戻る

        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合は以降処理なし
        if ((int)$rowMypageCustomer['NgAccessCount'] >= $ngAccessLoginLimit) {
            return;
        }

        $mdl = new \models\Table\TableMypageCustomer($this->_adapter);

        // 回数=0の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        if ($rowMypageCustomer['NgAccessCount'] == 0) {
            $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowMypageCustomer['CustomerId']);
            return;
        }

        // 連続不正アクセス判定基準間隔(秒)の取得＆現時刻と比較し間隔外の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
        $ngAccessReferenceTerm = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessLoginReferenceTerm' "
            )->execute(null)->current()['PropValue'];
        if (strtotime(date('Y-m-d H:i:s')) - strtotime($rowMypageCustomer['NgAccessReferenceDate']) > $ngAccessReferenceTerm) {
            $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowMypageCustomer['CustomerId']);
            return;
        }

        $mdl->saveUpdate(array('NgAccessCount' => $rowMypageCustomer['NgAccessCount'] + 1), $rowMypageCustomer['CustomerId']);
    }

    //--------------------------------------------------------------------------
    // 以下、不正アクセス注文マイページ(T_NgAccessMypageOrder)関連
    //--------------------------------------------------------------------------
    /**
     * 注文マイページに存在するか？
     *
     * @param int $oemId OemId
     * @param string $phone 電話番号
     * @return boolean true:存在／false:不在
     */
    public function isExistMypageOrder($oemId, $phone)
    {
        $sql = " SELECT COUNT(mo.OrderSeq) AS CNT FROM MV_MypageOrder mo INNER JOIN MV_Order o ON (o.OrderSeq = mo.OrderSeq) WHERE IFNULL(o.OemId,0) = :OemId AND mo.Phone = :Phone ";
        $count = (int)$this->_adapter->query($sql)->execute(array(':OemId' => $oemId, ':Phone' => $phone))->current()['CNT'];
        return ($count > 0) ? true : false;
    }

    /**
     * 連続不正アクセスなしの注文マイページログイン成功か？
     *
     * 注文マイページログイン認証が成功しても、[NgAccessCount]が規定値以上の場合はfalseを戻す。
     * そうでない場合はカウンタを0で初期化しtrueを戻す
     *
     * @param array|false $rowNgAccessMypageOrder 不正アクセス注文マイページレコード
     * @return boolean true:OK／false:NG
     */
    public function isNotNgAccessMypageOrder($rowNgAccessMypageOrder)
    {
        if (!$rowNgAccessMypageOrder) { return true; }  // $rowNgAccessMypageOrderが無効な時は直ちにtrueで戻る

        $mdl = new \models\Table\TableNgAccessMypageOrder($this->_adapter);

        // 規定値取得
        $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessLoginLimit' "
            )->execute(null)->current()['PropValue'];

        // 規定値以上の場合はこの時点でNG
        if ((int)$rowNgAccessMypageOrder['NgAccessCount'] >= $ngAccessLoginLimit) {
            return false;
        }

        // カウントを0で更新
        $mdl->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $rowNgAccessMypageOrder['Seq']);

        return true;
    }

    /**
     * ログイン認証失敗による不正アクセス注文マイページテーブルの更新
     *
     * @param boolean $isExistMypageOrder 注文マイページに存在するか？
     * @param array|false $rowNgAccessMypageOrder 不正アクセス注文マイページレコード
     * @param int $oemId OemId
     * @param string $phone 電話番号
     */
    public function updateMypageOrderNgAccess($isExistMypageOrder, $rowNgAccessMypageOrder, $oemId, $phone)
    {
        if (!$isExistMypageOrder) { return; }   // 注文マイページに該当がない時は、以降処理不要

        $mdl = new \models\Table\TableNgAccessMypageOrder($this->_adapter);

        if ($rowNgAccessMypageOrder) {

            // 規定値取得
            $ngAccessLoginLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessLoginLimit' "
                )->execute(null)->current()['PropValue'];

            // 規定値以上の場合は以降処理なし
            if ((int)$rowNgAccessMypageOrder['NgAccessCount'] >= $ngAccessLoginLimit) {
                return;
            }

            // 回数=0の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
            if ($rowNgAccessMypageOrder['NgAccessCount'] == 0) {
                $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowNgAccessMypageOrder['Seq']);
                return;
            }

            // 連続不正アクセス判定基準間隔(秒)の取得＆現時刻と比較し間隔外の時は、カウントを1、連続不正アクセス判定基準時刻を現時刻で更新し処理を抜ける
            $ngAccessReferenceTerm = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessLoginReferenceTerm' "
                )->execute(null)->current()['PropValue'];
            if (strtotime(date('Y-m-d H:i:s')) - strtotime($rowNgAccessMypageOrder['NgAccessReferenceDate']) > $ngAccessReferenceTerm) {
                $mdl->saveUpdate(array('NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')), $rowNgAccessMypageOrder['Seq']);
                return;
            }

            $mdl->saveUpdate(array('NgAccessCount' => $rowNgAccessMypageOrder['NgAccessCount'] + 1), $rowNgAccessMypageOrder['Seq']);
        }
        else {
            $mdl->saveNew(array('OemId' => $oemId, 'Phone' => $phone, 'NgAccessCount' => 1, 'NgAccessReferenceDate' => date('Y-m-d H:i:s')));
        }
    }

    //--------------------------------------------------------------------------
    // (以下、解除関連)
    //--------------------------------------------------------------------------
    /**
     * 不正アクセス解除
     *
     * @param Adapter $adapter アダプタ(主スキーマ)
     * @param Adapter $adapterMypage アダプタ(マイページスキーマ)
     * @param int $serverNo サーバー番号 AP1:1／AP2:2
     */
    public function clearNgAccess($adapter, $adapterMypage, $serverNo)
    {
        $mdlnac = new \models\Table\TableNgAccessClear($adapter);

        // 主／マイページ両方にあるテーブル
        $mdlnai = new \models\Table\TableNgAccessIp($adapter);
        $mdlnaimypage = new \models\Table\TableNgAccessIp($adapterMypage);
        // 主側限定テーブル
        $mdle = new \models\Table\TableEnterprise($adapter);
        $mdlnge = new TableNgAccessEnterprise($adapter);
        $mdlo = new \models\Table\TableOperator($adapter);
        $mdloo = new \models\Table\TableOemOperator($adapter);
        // マイページ側限定テーブル
        $mdlmc = new \models\Table\TableMypageCustomer($adapterMypage);
        $mdlnamo = new \models\Table\TableNgAccessMypageOrder($adapterMypage);

        $adapter->getDriver()->getConnection()->beginTransaction();
        try {
            // 主スキーマ側より解除指示一覧の取得
            $lists = ResultInterfaceToArray($adapter->query(" SELECT * FROM T_NgAccessClear WHERE Status = 1 FOR UPDATE ")->execute(null));

            foreach ($lists as $list) {

                if ($list['Type'] == 0) {
                    // 不正アクセスIP
                    // (主側)
                    $row = $mdlnai->findIpAddress($list['LoginId']);
                    if ($row) {
                        $mdlnai->saveUpdate(array('Count' => 0, 'NgAccessReferenceDate' => null), $row['Seq']);
                    }
                    // (マイページ側)
                    $row = $mdlnaimypage->findIpAddress($list['LoginId']);
                    if ($row) {
                        $mdlnaimypage->saveUpdate(array('Count' => 0, 'NgAccessReferenceDate' => null), $row['Seq']);
                    }
                    // IPアドレスの場合は[ファイル削除]を考慮
                    $chkfile = "/var/www/html/NgAccessIp/";
                    $chkfile .= ($list['LoginId'] . '.txt');
                    if (file_exists($chkfile)) {
                        unlink($chkfile);
                    }
                }
                else if ($list['Type'] == 1) {
                    // 加盟店
                    $row = $mdle->findLoginId($list['LoginId'])->current();
                    if ($row) {
                        $nge = $mdlnge->find($row['EnterpriseId'])->current();
                        if ($nge) {
                            $mdlnge->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $row['EnterpriseId']);
                        }
                    }
                }
                else if ($list['Type'] == 2) {
                    // オペレーター
                    $row = $mdlo->findLoginId($list['LoginId'])->current();
                    if ($row) {
                        $mdlo->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $row['OpId']);
                    }
                }
                else if ($list['Type'] == 3) {
                    // OEMオペレーター
                    $row = $adapter->query(" SELECT OemOpId FROM T_OemOperator WHERE LoginId = :LoginId ")->execute(array(':LoginId' => $list['LoginId']))->current();
                    if ($row) {
                        $mdloo->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $row['OemOpId']);
                    }
                }
                else if ($list['Type'] == 4) {
                    // (Mypage)顧客マイページ
                    $row = $mdlmc->findLoginId($list['LoginId'])->current();
                    if ($row) {
                        $mdlmc->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $row['CustomerId']);
                    }
                }
                else if ($list['Type'] == 5) {
                    // (Mypage)注文マイページ
                    $parts = explode('@', $list['LoginId']); // [0]:OemId／[1]:Phone
                    $row = $mdlnamo->findOemPhone($parts[0], $parts[1])->current();
                    if ($row) {
                        $mdlnamo->saveUpdate(array('NgAccessCount' => 0, 'NgAccessReferenceDate' => null), $row['Seq']);
                    }
                }

                //------------------------------------------
                // 不正アクセス解除指示テーブル更新
                $savedata = array();
                // (自APサーバー処理が完了したことを保管)
                $aryServerStatus = explode(',', $list['ServerStatus']);
                $aryServerStatus[(int)$serverNo - 1] = 1;
                $serverStatus = implode(',', $aryServerStatus);
                $savedata['ServerStatus'] = $serverStatus;
                // (全APサーバー処理が完了した場合は、解除指示中フラグを更新)
                if (strpos($serverStatus, '0') === false) {
                    $savedata['Status'] = 0;
                }
                $mdlnac->saveUpdate($savedata, $list['Seq']);
            }

            $adapter->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e) {
            $adapter->getDriver()->getConnection()->rollback();
        }
    }

    /**
     * アクセスしたIPアドレスが連続不正アクセスIPアドレスか
     * 他APサーバーのNGに対応
     *
     * @param string $ipAddress IPアドレス
     * @param string $savePath ファイル保存パス
     * @see システムで設定された[不正アクセスリミット]以上になる時、不正アクセスIPファイル保存フォルダへ、
     *      "IPアドレス名.txt"(空ファイル)を作成する
     * @return boolean true:NG / false:OK
     */
    public function isNgAccess($ipAddress, $savePath){
        // 不正アクセスIPホワイトリスト考慮
        $whiteList = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessIpWhiteList' "
        )->execute(null)->current()['PropValue'];
        if (false !== strpos($whiteList, $ipAddress)) {
            return false;
        }
        $mdl = new \models\Table\TableNgAccessIp($this->_adapter);

        // クライアントIPアドレスの登録(新規or更新)
        $row = $mdl->findIpAddress($ipAddress);
        if ($row) {
            $ngAccessIpLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessIpLimit' "
            )->execute(null)->current()['PropValue'];
            if ($row['Count'] >= $ngAccessIpLimit) {
                // 指定回数に到達する時、不正アクセスIPファイル作成
                touch($savePath . $ipAddress . '.txt');
                return true;
            }
        }
        else {
            return false;
        }
    }
    /**
     * アクセスしたIPアドレスが連続不正アクセスIPアドレスか
     * 他APサーバーのNGに対応
     *
     * NOTE : [isNgAccess]に対し、参照するシステムプロペティが異なるのみ
     *
     * @param string $ipAddress IPアドレス
     * @param string $savePath ファイル保存パス
     * @see システムで設定された[不正アクセスリミット]以上になる時、不正アクセスIPファイル保存フォルダへ、
     *      "IPアドレス名.txt"(空ファイル)を作成する
     * @return boolean true:NG / false:OK
     */
    public function isNgAccessMypage($ipAddress, $savePath){
        // 不正アクセスIPホワイトリスト考慮
        $whiteList = $this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'NgAccessIpWhiteList' "
        )->execute(null)->current()['PropValue'];
        if (false !== strpos($whiteList, $ipAddress)) {
            return false;
        }
        $mdl = new \models\Table\TableNgAccessIp($this->_adapter);

        // クライアントIPアドレスの登録(新規or更新)
        $row = $mdl->findIpAddress($ipAddress);
        if ($row) {
            $ngAccessIpLimit = (int)$this->_adapter->query(" SELECT PropValue FROM T_SystemProperty WHERE Module = '[DEFAULT]' AND Category = 'systeminfo' AND Name = 'MypageNgAccessIpLimit' "
            )->execute(null)->current()['PropValue'];
            if ($row['Count'] >= $ngAccessIpLimit) {
                // 指定回数に到達する時、不正アクセスIPファイル作成
                touch($savePath . $ipAddress . '.txt');
                return true;
            }
        }
        else {
            return false;
        }
    }
}
