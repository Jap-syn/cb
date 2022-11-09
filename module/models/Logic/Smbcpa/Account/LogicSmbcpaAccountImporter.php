<?php
namespace models\Logic\Smbcpa\Account;

use Zend\Db\Adapter\Adapter;
use Coral\Base\IO\BaseIOCsvReader;
use Coral\Base\IO\BaseIOUtility;
use models\Logic\Smbcpa\LogicSmbcpaCommon;

/**
 * SMBCバーチャル口座サービス向けの口座データインポートロジック。
 * CSVファイルからの一時テーブル構築とその一時データからの実インポート、
 * 処理待ち一時テーブルのクリーンナップ（＝インポートキャンセル）、インポートエラーのクリーンナップなどの
 * 機能を提供する
 */
class LogicSmbcpaAccountImporter extends LogicSmbcpaCommon {
    /**
     * 処理対象のOEM ID
     *
     * @access protected
     * @var int
     */
    protected $_oemId = -1;

    /**
     * 処理を実行するオペレータID
     *
     * @access protected
     * @var int
     */
    protected $_opId = -1;

    /**
     * CSV読み込みトランザクションでコンテキストデータ
     *
     * @access protected
     * @var array
     */
    protected $_csvContext;

    /**
     * インポート一時テーブルのテンプレート連想配列
     *
     * @access protected
     * @var array
     */
    protected $_importWorkRowTemplates;

    /**
     * 口座種別
     *
     * @access protected
     * @var array
     */
    protected $_depositClass = 0;

    /**
     * DBアダプタと処理対象のOEM ID、処理を実行するオペレータIDを指定して
     * LogicSmbcpaAccountImporterの新しいインスタンスを初期化する
     *
     * @param Adapter $adapter アダプタ
     * @param int $oemId OEM ID
     * @param null | int $opId オペレータID
     */
    public function __construct(Adapter $adapter, $oemId, $opId = -1) {
        parent::__construct($adapter);

        $this->setTargetOemId($oemId);
        $this->setOperatorId($opId);
    }

    /**
     * 処理対象のOEM IDを取得する
     *
     * @return int
     */
    public function getTargetOemId() {
        return $this->_oemId;
    }
    /**
     * 処理対象のOEM IDを設定する
     *
     * @param int $oemId OEM ID
     * @return LogicSmbcpaAccountImporter このインスタンス
     */
    public function setTargetOemId($oemId) {
        if(!is_numeric($oemId)) throw new \Exception('invalid oem-id specified');
        $this->_oemId = (int)$oemId;
        return $this;
    }

    /**
     * 処理を実行するオペレータIDを取得する
     *
     * @return int
     */
    public function getOperatorId() {
        return $this->_opId;
    }
    /**
     * 処理を実行するオペレータIDを設定する
     *
     * @param int $opId オペレータID
     * @return LogicSmbcpaAccountImporter このインスタンス
     */
    public function setOperatorId($opId) {
        $this->_opId = (int)$opId;
        return $this;
    }

    /**
     * インポート時に設定する口座種別を取得する
     *
     * @return int
     */
    public function getDepositClass() {
        return $this->_depositClass;
    }
    /**
     * インポート時に設定する口座種別を設定する。
     * 0（普通）または1（当座）のみが指定可能で、これら以外が指定された場合は自動的に
     * 0（普通）に読み替えられる。
     *
     * @param int $depo 口座種別
     * @return LogicSmbcpaAccountImporter このインスタンス
     */
    public function setDepositClass($depo) {
        $depo = (int)$depo;
        if(!in_array($depo, array(0, 1))) $depo = 0;
        $this->_depositClass = $depo;
        return $this;
    }

    /**
     * 現在処理対象となっているSMBCバーチャル口座契約情報のサマリーを取得する
     *
     * @return array
     */
    public function getCurrentSmbcpaSummary() {
        $oid = $this->getTargetOemId();
        return $this->getSmbcpaTable()->findSummaryByOemId($oid);
    }

    /**
     * 指定CSVファイルを作業用一時テーブルに読み込む
     *
     * @param string $file CSVファイルのパス
     * @return array 結果を格納した連想配列。
     *               キー'key'には今回の読み込みで使用したプロセスキーが格納されており、このキーを
     *               インポート処理時に指定する必要がある。
     *               その他は'success'、'error'、'skip'のそれぞれに、読み込み成功件数、
     *               読み込みエラー件数、スキップ件数が格納される。
     */
    public function loadCsvFile($file) {
$this->debug('[loadCsv] called.');
        $start = microtime(true);

        // ファイルの存在チェック
        if(!is_file($file)) {
            throw new \Exception('CSVファイルの指定が不正です');
        }

        // 現在のSMBCバーチャル口座契約のサマリを取得
        $data = $this->getCurrentSmbcpaSummary();
        if(!$data) {
            throw new \Exception('SMBCバーチャル口座契約情報が取得できません。OEM指定が不正です');
        }

        // トランザクションパラメータを今回の処理向けに初期化
        $this->_csvContext = array(
            'key' => null,
            'smbcpaId' => (int)$data['SmbcpaId'],
            'oemId' => (int)$data['OemId']
        );

        // CSV読み込み準備
        BaseIOUtility::convertFileEncoding($file, null, null, true);
        $reader = new BaseIOCsvReader($file, array($this, 'csvReadCallback'), true);

        // 今回のトランザクションキー確定
        $this->_csvContext['key'] = md5(join(' ', array(microtime(), sprintf('%010d', rand()))));

        // 処理結果カウンタを初期化
        $counts = array(
            'skip' => 0,
            'ok' => 0,
            'ng' => 0
        );

        // CsvReaderに与えたコールバック内でDBへのインサートを行っているのでここでトランザクションを形成
        // ※：実際の読み込み処理はcsvReadCallback()メソッドを参照
        $this->_adapter->getDriver()->getConnection()->beginTransaction();
$this->debug('[loadCsv] begin load.');
        try {
            // 読み込み開始
            foreach($reader->read() as $result) {
                $counts[$result[0]]++;
            }

            // データをコミット
            $this->_adapter->getDriver()->getConnection()->commit();
$this->debug(sprintf('[loadCsv] end load normaly. elapsed time = %s', (microtime(true) - $start)));
        } catch(\Exception $err) {
            $this->_adapter->getDriver()->getConnection()->rollBack();
$this->info(sprintf('[loadCsv] end load ABNORMALY !! elapsed time = %s, error = ', (microtime(true) - $start), $err->getMessage()));
            throw $err;
        }

        return array(
            'key' => $this->_csvContext['key'],
            'success' => $counts['ok'],
            'error' => $counts['ng'],
            'skip' => $counts['skip']
        );
    }

    /**
     * 指定プロセスキーの処理対象データを元にSMBCバーチャル口座のインポート処理を実行する
     *
     * @param string $processKey 一時データの処理対象を識別するためのプロスキー
     * @param int | null $limit 今回の呼出しでインポートを実行するデータ数の最大値。省略時は500が採用される
     * @param null | int インポート先のSMBCバーチャル口座グループID
     * @return array 処理結果を格納した連想配列。以下のキーと値が格納される
     *               'processed'    -> 処理対象件数
     *               'success'      -> インポート成功件数
     *               'error'        -> インポートエラー件数
     *               'remain'       -> 同一プロセスキーにおける残件数。この値が0以上の場合は継続してこのメソッドを呼び出せる
     *               'totalError'   -> 同一プロセスキーにおける累積エラー件数
     *               'groupId'      -> インポート先のSMBCバーチャル口座グループID。同一プロセスキーに対する処理で最初の要求以外は
     *                                 この値を次のexecImportで指定する必要がある
     */
    public function execImport($processKey, $limit = 500, $groupId = null) {
$this->debug(sprintf('[execImport:%s] called. limit = %s, group = %s', $processKey, $limit, $groupId));
        $start = microtime(true);

        // トランザクションIDのチェック
        if(!strlen($processKey)) {
            throw new \Exception('トランザクションIDが指定されていません');
        }

        $grpTable = $this->getSmbcpaGroupTable();
        $workTable = $this->getImportWorkTable();
        $accTable = $this->getSmbcpaAccountTable();

        // SMBCバーチャル口座契約サマリーを取得
        $oid = $this->getTargetOemId();
        $data = $this->getCurrentSmbcpaSummary();
        if(!$data) {
            throw new \Exception(sprintf("OEM ID '%d' は不正な指定です", $oid));
        }

        // 関連するSMBCバーチャル口座アカウントグループを取得
        $gid = $groupId ? $groupId : $workTable->getLastAccountGroupId($processKey);
        if(!$gid) {
            // グループID未設定時は新規にIDを割り振る
            $gid = $grpTable->saveNew(array(
                                           'SmbcpaId' => $data['SmbcpaId'],
                                           'DepositClass' => $this->getDepositClass(),
                                           'ReturnedFlg' => 0,
                                           'RegistDate' => date('Y-m-d H:i:s')
                                           ));
        }
$this->debug(sprintf('[execImport:%s] fixed group-id = %s', $processKey, $gid));
        $grpRow = $grpTable->find($gid)->current();
        if(!$grpRow) {
            throw new \Exception('AccountGroup not found');
        }

        // 一時テーブルからインポート対象データを指定件数づつ処理する
        $processed = $success = $error = 0;
        $targets = $workTable->findImportTargets($processKey, $limit);
        if($targets->count()) {
$this->debug(sprintf('[execImport:%s] begin process. target count = %s', $processKey, $targets->count()));
            // 処理対象残あり
            $key = null;
            $label = null;
            $colInfo = $this->getImportCsvSchema();
            foreach($targets as $target) {
                $processed++;
                $key = $target['ManageKey'];
                $label = $target['ManageKeyLabel'];
if($processed % 500 == 0) $this->debug(sprintf('[execImport:%s] processed count = %s, key = %s, label = %s, account = %s-%s', $processKey, $processed, $key, $label, $target['BranchCode'], $target['AccountNumber']));

                // リレーションやステータスなどの初期情報を設定
                $accData = array(
                    'SmbcpaId' => $data['SmbcpaId'],
                    'AccountGroupId' => $grpRow['AccountGroupId'],
                    'RegistDate' => date('Y-m-d H:i:s'),
                    'Status' => 0,
                    'LastStatusChanged' => date('Y-m-d H:i:s')
                );
                // 一時データから口座情報を転記
                foreach($colInfo as $col => $conf) {
                    if(isset($conf['to_imp'])) {
                        $value = $target[$col];
                        if(preg_match($conf['match'], $value)) {
                            if($col == 'AccountNumber' && strlen($value) < 7) {
                                // 口座番号が7桁未満の場合は7桁になるようゼロプレフィックス
                                $value = sprintf('%07s', $value);
                            }
                            $accData[$col] = $value;
                        }
                    }
                }

                // 口座情報登録実行
                try {
                    $accTable->saveNew($accData);
                    $target['AccountGroupId'] = $grpRow['AccountGroupId'];    // 一時データにグループIDを書き戻す
                    $success++;
                } catch(\Exception $err) {
$this->info(sprintf('[execImport:%s] an error has occured. processed count = %s, error = %s', $processKey, $processed, $err->getMessage()));
                    // インサートの失敗はエラーとして記録
                    $target['ImportError'] = $err->getMessage();
                    $error++;
                }
                // 一時データを処理済みに更新
                $target['DeleteFlg'] = 1;
                $target['EndTime'] = date('Y-m-d H:i:s');
                $workTable->saveUpdate($target, $target['Seq']);

            }
            // 対象グループのインポート件数を更新
            $grpRow['TotalAccounts'] = ((int)$grpRow['TotalAccounts'] + $success);
            $grpRow['ManageKey'] = $key;
            $grpRow['ManageKeyLabel'] = $label;
            $grpTable->saveUpdate($grpRow, $grpRow['AccountGroupId']);

        }
        // 残件数を取得
        $remain = $workTable->countTargets($processKey);
        // エラー件数を取得
        $totalErrors = $workTable->countImportError($processKey);

$this->debug(sprintf('[execImport:%s] end process. elapsed time = %s, remain = %s', $processKey, (microtime(true) - $start), $remain));
        // 結果を返す
        return array(
            'processed' => $processed,
            'success' => $success,
            'error' => $error,
            'remain' => $remain,
            'totalError' => $totalErrors,
            'groupId' => $grpRow['AccountGroupId']
        );
    }

    /**
     * 指定プロセスキーの処理待ちデータを削除してインポート処理をキャンセルする
     *
     * @param string $processKey キャンセル対象のプロセスキー
     */
    public function cancelImport($processKey) {
$this->debug(sprintf('[cancelImport:%s] method called. target processKey = %s', $processKey, $processKey));
        $this->_adapter->getDriver()->getConnection()->beginTransaction();
        try {
            $sql = " DELETE FROM T_SmbcpaAccountImportWork WHERE ProcessKey = :ProcessKey ";
            $ri = $this->_adapter->query($sql)->execute(array(':ProcessKey' => $processKey));
            $count = $ri->getAffectedRows();
$this->debug(sprintf('[cancelImport:%s] %s rows deleted.', $processKey, f_nf($count, '#,##0')));
            $this->_adapter->getDriver()->getConnection()->commit();
        } catch(\Exception $err) {
$this->info(sprintf('[cancelImport:%s] an error has occured. error = %s', $processKey, $err->getMessage()));
            $this->_adapter->getDriver()->getConnection()->rollBack();
            throw $err;
        }
    }

    /**
     * 指定プロセスキーのインポート実行待ちサマリーを取得する。
     * 戻り値はフラットな連想配列で以下の情報を格納する。
     * ProcessKey   -> 指定プロセスキー
     * SuccessCount -> インポート可能な正常データの件数
     * ErrorCount   -> CSVエラーデータの件数
     * 対象のデータは削除フラグが立っていないもの（＝CSV読み込み直後）のデータに限定される
     *
     * @param string $processKey プロセスキー
     * @return array
     */
    public function reportPreImportSummary($processKey) {
        $q = <<<EOQ
SELECT
    ProcessKey,
    SUM(CASE WHEN CsvError IS NULL THEN 1 ELSE 0 END) AS SuccessCount,
    SUM(CASE WHEN CsvError IS NOT NULL THEN 1 ELSE 0 END) AS ErrorCount
FROM
    T_SmbcpaAccountImportWork
WHERE
    ProcessKey = :ProcessKey AND
    IFNULL(DeleteFlg, 0) = 0
GROUP BY
    ProcessKey
EOQ;
        $row = $this->_adapter->query($q)->execute(array(':ProcessKey' => $processKey))->current();
        return ($row) ? $row : array('ProcessKey' => $processKey, 'SuccessCount' => 0, 'ErrorCount' => 0);
    }

    /**
     * 指定プロセスキーのCSVエラーデータの件数をカウントする
     *
     * @param string $processKey プロセスキー
     * @return int 指定プロセスキーに関連付けられたCSVエラー一時データの件数
     */
    public function countCsvErrors($processKey) {
        $q = <<<EOQ
SELECT
    COUNT(*) AS cnt
FROM
    T_SmbcpaAccountImportWork
WHERE
    ProcessKey = :ProcessKey AND
    IFNULL(DeleteFlg, 0) = 0 AND
    CsvError IS NOT NULL
EOQ;
        return (int)$this->_adapter->query($q)->execute(array(':ProcessKey' => $processKey))->current()['cnt'];
    }

    /**
     * 指定プロセスキーのCSVエラーデータを取得する
     *
     * @param string $processKey プロセスキー
     * @param int | null $page 取得するページ位置。省略時またはマイナス値を指定した場合は1に読み替えられる
     * @param int | null $limit 1ページ分で取得する件数の上限。0またはマイナス値を指定した場合は無制限
     * @return ResultInterface SMBCバーチャル口座インポート作業テーブルの行イメージ
     */
    public function reportCsvErrors($processKey, $page = 1, $limit = 500) {
        $page = (int)$page;
        if($page < 1) $page = 1;

        $limt = (int)$limit;
        if($limit > 0) {
            $offset = $limit * ($page - 1);

            $limitCondition = $offset ?
                sprintf('LIMIT %d OFFSET %d', $limit, $offset) :
                sprintf('LIMIT %d', $limit);
        } else {
            $limitCondition = '';
        }

        $q = <<<EOQ
SELECT
    *
FROM
    T_SmbcpaAccountImportWork
WHERE
    ProcessKey = :ProcessKey AND
    IFNULL(DeleteFlg, 0) = 0 AND
    CsvError IS NOT NULL
ORDER BY
    Seq
%s
EOQ;
        return $this->_adapter->query(sprintf($q, $limitCondition))->execute(array(':ProcessKey' => $processKey));
    }

    /**
     * 指定プロセスキーのCSVエラーをクリアする
     *
     * @param string $processKey CSVエラーをクリアするプロセスキー
     */
    public function clearCsvErrors($processKey) {
$this->debug(sprintf('[clearCsvErrors:%s] method called. target processKey = %s', $processKey, $processKey));
        $sql = " DELETE FROM T_SmbcpaAccountImportWork WHERE ProcessKey = :ProcessKey AND CsvError IS NOT NULL ";
        $ri = $this->_adapter->query($sql)->execute(array(':ProcessKey' => $processKey));
        $count = $ri->getAffectedRows();
$this->debug(sprintf('[clearCsvErrors:%s] %s rows deleted.', $processKey, f_nf($count, '#,##0')));
    }

    /**
     * 指定プロセスキーのインポートエラーレポートを取得する。
     * 戻り値の要素は以下の情報が格納されている。
     * ProcessKey   -> プロセスキー
     * StartFrom    -> このグループに含まれるエラー一時行のうち最も古いStartTime
     * StartTo      -> このグループに含まれるエラー一時行のうち最も新しいStartTime
     * ImportError  -> エラー内容の先頭45文字。グループキーになる
     * Count        -> このグループのエラー一時行の行数
     *
     * @param string $processKey プロセスキー
     * @return ResultInterface
     */
    public function reportErrors($processKey) {
        $q = <<<EOQ
SELECT
    ProcessKey,
    MIN(StartTime) AS StartFrom,
    MAX(StartTime) AS StartTo,
    SUBSTRING(ImportError, 1, 45) AS ImportError,
    COUNT(*) AS Count
FROM
    T_SmbcpaAccountImportWork
WHERE
    ProcessKey = :ProcessKey AND
    ImportError IS NOT NULL
GROUP BY
    ProcessKey,
    SUBSTRING(ImportError, 1, 45)
ORDER BY
    MIN(StartTime)
EOQ;
        return $this->_adapter->query($q)->execute(array(':ProcessKey' => $processKey));
    }

    /**
     * 指定プロセスキーのインポートエラーをクリアして一時データを削除する
     *
     * @param string $processKey エラーをクリアするプロセスキー
     */
    public function clearImportErrors($processKey) {
$this->debug(sprintf('[clearImportErrors:%s] method called. target processKey = %s', $processKey, $processKey));
        $sql = " DELETE FROM T_SmbcpaAccountImportWork WHERE ProcessKey = :ProcessKey AND ImportError IS NOT NULL ";
        $ri = $this->_adapter->query($sql)->execute(array(':ProcessKey' => $processKey));
        $count = $ri->getAffectedRows();
$this->debug(sprintf('[clearImportErrors:%s] %s rows deleted.', $processKey, f_nf($count, '#,##0')));
    }

    /**
     * loadCsvFile内で動作するCSVリーダーから呼び出されるコールバックメソッド。
     * 他の用途から呼び出してはならない。
     *
     * @param array $line CSV行データ
     * @param int $lineNum 行番号
     * @param BaseIOCsvReader $reader 処理を実行しているCSVリーダー
     */
    public function csvReadCallback(array $line, $lineNum, BaseIOCsvReader $reder) {
if(($lineNum + 1) % 1000 == 0) $this->debug(sprintf('[csvReadCallback:%08d] called.', $lineNum));
        // 挿入データのバッファを初期化
        $buf = array(
            'SmbcpaId' => $this->_csvContext['smbcpaId'],
            'ProcessKey' => $this->_csvContext['key'],
            'StartTime' => date('Y-m-d H:i:s'),
            'OpId' => $this->getOperatorId()
        );

        $col_count = 0;
        if(!empty($line)) {
            $col_count = count($line);
        }
        $result = 'ok';
        if($col_count < 5) {
            // カラム数が少ないのでエラー
            $result = 'ng';
            $buf = array(
                'CsvError' => sprintf('%d,カラム数が不正（%d カラム）', $lineNum + 1, $col_count)
            );
$this->info(sprintf('[csvReadCallback:%08d] invalid column count. count = %s', $lineNum, $col_count));
        } else {
            // カラム定義を取得
            $colInfo = $this->getImportCsvSchema();
            $errors = array();
            // 行データをバッファに転記
            foreach(array_keys($colInfo) as $pos => $colName) {
                if(isset($line[$pos])) {
                    // 値確定
                    $value = trim($line[$pos]);
                    if(strlen($value)) {
                        $buf[$colName] = $value;
                    }

                    // 検証処理
                    $label = $colInfo[$colName]['label'];
                    $rule = $colInfo[$colName]['match'];
                    // 検証エラーが発生したカラムのラベルを保存
                    if(!preg_match($rule, $value)) {
$this->info(sprintf('[csvReadCallback:%08d] col = %s, val = %s, rule = %s, is_date = %s', $lineNum, $colName, $value, $rule, $colInfo[$colName]['is_date'] ? 'YES' : 'NO'));
                        $errors[] = $label;
                    }
                }
            }

            // 検証エラーがあった場合の処理
            if(!empty($errors)) {
                if($lineNum == 0 &&
                   in_array($colInfo['BranchCode']['label'], $errors) &&
                   in_array($colInfo['AccountNumber']['label'], $errors)) {

                    // 先頭行で且つ店番号・口座番号がエラーならヘッダ行と見なす
                    $result = 'skip';
                    $buf = array_merge($buf, array(
                        'EndTime' => date('Y-m-d H:i:s'),    // インポート処理完了扱い
                        'DeleteFlg' => 1,                    // 削除フラグも立てておく
                        'CsvError' => '見出し行'
                    ));
                } else {
                    // 通常のエラー行
                    $result = 'ng';
                    $buf = array_merge($buf, array(
                        'CsvError' => join(',', array_merge(array($lineNum + 1), $errors))
                    ));
                }
            } else {
                // 正常行
                $result = 'ok';
            }
        }

        // 今回の行データをインサート
        $workTable = $this->getImportWorkTable();
        $workTable->saveNew($buf);
        return array($result);
    }

    /**
     * 一時テーブルに登録するための行データテンプレートを連想配列で取得する。
     * このメソッドが返す連想配列は、テーブルスキーマに一致するキーをすべて備えるが、
     * すべてのカラムで値にはnullが設定されているので、必要に応じて呼び出し側で
     * 値を与える必要がある。
     *
     * @access protected
     * @return array
     */
    protected function _createTempRowTemplate() {
        if($this->_importWorkRowTemplates == null) {
            $seq = $this->getImportWorkTable()->saveNew(array());
            $this->_importWorkRowTemplates = $this->getImportWorkTable()->find($seq)->current();
        }
        return $this->_importWorkRowTemplates;
    }

    /**
     * 口座データインポート作業に必要なカラム定義情報を取得する。
     * CSVのスキーマ通りの並び順でSMBCバーチャル口座／SMBCバーチャル口座インポート作業のテーブルで定義されている対応する
     * カラム名をキーとして、値には表示名、検証ルールの正規表現およびSMBCバーチャル口座へのインポート対象で
     * あるかを示すフラグを備える
     *
     * @return array
     */
    public function getImportCsvSchema() {
        $DATE_MATCH = '/^(\d{4}([\-\/]\d{2}){2})?$/';
        $DATETIME_MATCH = '/^(\d{4}([\-\/]\d{2}){2} \d{2}:\d{2})?$/';
        $DATETIME_MATCH_S = '/^(\d{4}([\-\/]\d{2}){2} \d{2}:\d{2}:\d{2})?$/';
        return array(
            'BranchCode'        => array('label' => '店番号',             'match' => '/^\d{3}$/', 'to_imp' => 1),
            'AccountNumber'     => array('label' => '口座番号',           'match' => '/^\d{1,7}$/', 'to_imp' => 1),
            'AccountHolder'     => array('label' => 'ワンタイム口座名',   'match' => '/^.{1,255}$/u', 'to_imp' => 1),
            'ManageKey'         => array('label' => 'ワンタイム管理番号', 'match' => '/^[\d_]{15}$/'),
            'ManageKeyLabel'    => array('label' => '管理番号名',         'match' => '/^.{1,255}$/u'),
            'NumberingDate'     => array('label' => '採番日',             'match' => $DATE_MATCH, 'to_imp' => 1, 'is_date' => 1),
            'EffectiveDate'     => array('label' => '適用日',             'match' => $DATE_MATCH, 'to_imp' => 1, 'is_date' => 1),
            'ModifiedDate'      => array('label' => '更新日',             'match' => $DATE_MATCH, 'to_imp' => 1, 'is_date' => 1),
            'SmbcpaStatus'      => array('label' => 'ステータス',         'match' => '/^.{1,255}$/u', 'to_imp' => 1),
            'ExpirationDate'    => array('label' => '有効期限',           'match' => $DATETIME_MATCH, 'to_imp' => 1, 'is_date' => 1),
            'LastReceiptDate'   => array('label' => '最新入金日',         'match' => $DATETIME_MATCH_S, 'to_imp' => 1, 'is_date' => 1),
            'ReleasedDate'      => array('label' => '入金開放日',         'match' => $DATE_MATCH, 'to_imp' => 1, 'is_date' => 1)
        );
    }

}
