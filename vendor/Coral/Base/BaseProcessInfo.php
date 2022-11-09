<?php
namespace Coral\Base;

/**
 * プロセスIDによるプロセス情報の取得とプロセスのkillを行うためのクラス
 *
 * exsample: PID 10073のプロセスが実行中ならkillする
 * require_once 'NetB/ProcessInfo.php';
 * $procInfo = new NetB_ProcessInfo(10073);
 * if($procInfo->isRunning()) {
 *     $procInfo->kill();
 * }
 *
 * example2: 現在のプロセスの情報を出力する
 * require_once 'NetB/ProcessInfo.php';
 * $procInfo = new NetB_ProcessInfo();
 * print_r($procInfo->export());
 *
 */
class BaseProcessInfo {
    /**
     * 稼働プラットフォームがWindowsであるかのフラグ
     * @static
     * @access protected
     * @var bool
     */
    protected static $__os_is_win = null;

    /**
     * 現在稼働しているプラットフォームがWindowsであるかを判断する。
     * Windows環境下の場合、同時にサポートしているOS（XP以降）であるかのチェックも行われ、
     * 未サポート環境の場合は例外が発生する。
     *
     * @static
     * @return bool 現在の稼働環境がWindowsでXP以降の場合はtrue、それ以外（Linux, Mac OS X等）はfalse
     */
    public static function isWin() {
        if(self::$__os_is_win === null) {
            if(substr(PHP_OS, 0, 3) != 'WIN') {
                self::$__os_is_win = false;
            } else {
                if(substr(PHP_OS, 0, 5) != 'WINNT') {
                    // Win2K以前はサポート外
                    throw new \Exception('BaseProcessInfo class only supports WINNT architecture in Windows environment.');
                }
                if(!version_compare(php_uname('r'), '5.1', 'ge')) {
                    // OSバージョンが5.1以降（＝XP以降）のみサポート
                    throw new \Exception('BaseProcessInfo class supports WinXP or later.');
                }
                self::$__os_is_win = true;
            }
        }
        return self::$__os_is_win;
    }

    /**
     * 情報を取得するプロセスIDと、標準エラーへの出力を無視するかのフラグを指定して
     * BaseProcessInfoの新しいインスタンスを開く
     *
     * @static
     * @param int | null $process_id プロセスID。省略時は現在のプロセスIDが使用される
     * @param bool | null $ignore_err 標準エラーへの出力を無視するかのフラグ。省略時はfalse
     * @return BaseProcessInfo
     */
    public function open($process_id = null, $ignore_err = false) {
        return new self($process_id, $ignore_err);
    }

    /**
     * プロセスID
     *
     * @access protected
     * @var int
     */
    protected $_pid;

    /**
     * 起動日時
     *
     * @access protected
     * @var string
     */
    protected $_start_time;

    /**
     * 実行コマンド名
     *
     * @access protected
     * @var string
     */
    protected $_command_name;

    /**
     * 引数付きのコマンドライン
     *
     * @access protected
     * @var string
     */
    protected $_command_line;

    /**
     * 稼働プラットフォームがWindowsであるかのフラグ
     *
     * @access protected
     * @var bool
     */
    protected $_is_win;

    /**
     * プロセス探索開始時点のタイムスタンプ
     *
     * @access protected
     */
    protected $_exec_ts;

    /**
     * 標準エラーへの出力を無視するかのフラグ
     *
     * @access protected
     * @var bool
     */
    protected $_ignore_err;

    /**
     * 情報を取得するプロセスIDと、標準エラーへの出力を無視するかのフラグを指定して
     * BaseProcessInfoの新しいインスタンスを初期化する
     *
     * @param int | null $process_id プロセスID。省略時は現在のプロセスIDが使用される
     * @param bool | null $ignore_err 標準エラーへの出力を無視するかのフラグ。省略時はfalse
     */
    public function __construct($process_id = null, $ignore_err = false) {
        if($process_id === null) {
            $process_id = getmypid();
        }

        $this->_is_win = self::isWin();
        $this->_pid = (int)$process_id;

        $this->setIgnoreCommandError($ignore_err);

        $this->_command_name = null;
        $this->_start_time = null;
        $this->_command_line = null;

        // プロセス情報取得を実行
        $this->parseProcessInfo();
    }

    /**
     * 標準エラーへの出力を無視するかの設定を取得する
     *
     * @return bool 標準エラーへの出力を無視する場合はtrue、それ以外はfalse。
     *              出力を無視しない設定の時に実行した内部コマンドが標準エラーへメッセージを出力すると例外が発生する
     */
    public function getIgnoreCommandError() {
        return $this->_ignore_err;
    }

    /**
     * 標準エラーへの出力を無視するかのフラグを設定する
     *
     * @param bool $ignore 標準出力への出力を無視する場合はtrue、それ以外はfalse。
     *                     出力を無視しない設定の時に実行した内部コマンドが標準エラーへメッセージを出力すると例外が発生する
     * @return BaseProcessInfo このインスタンス
     */
    public function setIgnoreCommandError($ignore) {
        $this->_ignore_err = $ignore ? true : false;
        return $this;
    }

    /**
     * 現在の稼働プラットフォームがWindowsであるかを判断する
     *
     * @return bool
     */
    public function isWindows() {
        return $this->_is_win;
    }

    /**
     * 取得したプロセス情報を連想配列でエクスポートする。
     * キーとその内容は以下の通り。
     *   process_id -> プロセスID
     *   command_name -> 実行コマンド名
     *   start_time -> 起動日時
     *   command_line -> 引数付きのコマンドライン
     *   is_running -> このプロセスが現在実行中かのフラグ
     *   hash -> プロセスIDと実行コマンド名、コマンドラインから生成されるハッシュ値
     * @return array
     */
    public function export() {
        $this->parseProcessInfo();
        return array(
            'process_id' => $this->getProcessId(),
            'command_name' => $this->getCommandName(),
            'start_time' => $this->getStartTime(),
            'command_line' => $this->getFullCommandLine(),
            'is_running' => $this->isRunning(),
            'hash' => $this->getProcessHash()
        );
    }

    /**
     * プロセスIDを取得する
     *
     * @return int
     */
    public function getProcessId() {
        return $this->_pid;
    }

    /**
     * 実行コマンド名を取得する
     *
     * @return string 実行コマンド名。isRunning()がfalseを返す場合、このメソッドはnullを返す
     */
    public function getCommandName() {
        return $this->_command_name;
    }

    /**
     * 起動日時を取得する
     *
     * @return string Y-m-d H:i:s 形式の起動日時。isRunning()がfalseを返す場合、このメソッドはnullを返す。
     *                非Windows環境下では値に1秒の誤差が含まれる場合がある点に注意
     */
    public function getStartTime() {
        return $this->_start_time;
    }

    /**
     * 起動時の引数付きコマンドラインを取得する
     *
     * @return string コマンドライン。isRunning()がfalseを返す場合、このメソッドはnullを返す。
     *                非Windows環境では一定の長さで切り詰められる点に注意
     */
    public function getFullCommandLine() {
        return $this->_command_line;
    }

    /**
     * このプロセスを示すハッシュ値を取得する
     *
     * @return string プロセスID、実行コマンド名、コマンドラインから生成されるハッシュ値。
     *                isRunning()がfalseを返す場合、このメソッドは長さ0の文字列を返す
     */
    public function getProcessHash() {
        return $this->isRunning() ?
            hash('sha1', sprintf('%s:%s', $this->getProcessId(), $this->getCommandName(), $this->getFullCommandLine())) :
            '';
    }

    /**
     * このプロセスが起動しているかを判断する
     *
     * @return bool 起動している場合はtrue、それ以外はfalse
     */
    public function isRunning() {
        return $this->_command_name !== null && $this->_start_time !== null && $this->_command_line !== null;
    }

    /**
     * このプロセスをkillする。
     * このプロセスがスクリプトの実行プロセスの場合は何も処理されない。
     *
     * @return BaseProcessInfo このインスタンス
     */
    public function kill() {
        $this->parseProcessInfo();
        if(!$this->isRunning()) return;

        $this->_kill();
        $this->parseProcessInfo();
        return $this;
    }

    /**
     * 現在のプロセスIDからプロセス情報を解析する
     *
     * @access protected
     */
    protected function parseProcessInfo() {
        // プロセスID以外のプロパティをクリア
        $this->_command_name = $this->_start_time = $this->_command_line = null;

        // プロセス情報取得コマンド実行時は標準エラーを無視する必要があるため、現在の設定を一時退避
        $ignoreErr = $this->getIgnoreCommandError();
        $this->setIgnoreCommandError(true);
        $cmdError = null;
        try {
            // プラットフォーム固有の情報取得コマンドを実行する
            if($this->isWindows()) {
                $lines = $this->_execPsWin();
            } else {
                $lines = $this->_execPs();
            }
        } catch(\Exception $err) {
            $cmdError = $err;
        }
        // 標準エラー無視設定を復元
        $this->setIgnoreCommandError($ignoreErr);
        if($cmdError != null) throw $cmdError;

        // コマンド実行結果が2行以下の場合はプロセスが存在しない
        if(count($lines) < 2) return;

        // フィールドを分割し、実行結果の末尾からプロパティを順次取得
        $items = mb_split('([ ]+)', $lines[1]);
        $result = array(
            'process_id' => array_pop($items),
            'command_name' => array_pop($items),
            'start_time' => array_pop($items),
            'command_line' => join(' ', $items)
        );
        $result['process_id'] = (int)$result['process_id'];
        $result['command_line'] = mb_ereg_replace('[\'\"]', '', $result['command_line']);

        // 起動日時の解析をプラットフォーム固有の処理で行う
        if($this->isWindows()) {
            $result['start_time'] = $this->_parseCreationDate($result['start_time']);
        } else {
            $result['start_time'] = $this->_parseElapsedTime($result['start_time']);
        }

        $this->_command_name = $result['command_name'];
        $this->_start_time = $result['start_time'];
        $this->_command_line = $result['command_line'];
    }

    /**
     * Windows固有のプロセス情報取得コマンドを実行する
     *
     * @access protected
     * @return array 実行結果の行データ
     */
    protected function _execPsWin() {
        $cmd = sprintf('wmic process where ProcessId=%d get CommandLine, CreationDate, Name, ProcessId',
                       $this->getProcessId());
        return $this->_execProcess($cmd);
    }

    /**
     * 非Windows環境のプロセス情報取得コマンドを実行する
     *
     * @access protected
     * @return array 実行結果の行データ
     */
    protected function _execPs() {
        // TODO: argsが27文字で切れるのをなんとかしたい

        // カラムの並び順はWin環境のwmic processに合わせる
        $cmd = sprintf('ps -p %d -o args -o etime -o comm -o pid',
                       $this->getProcessId());
        // psコマンド実行日時を退避（誤差有…）
        $this->_exec_ts = time();
        return $this->_execProcess($cmd);
    }

    /**
     * Windows環境向けにCreationDateの出力を日付文字列に展開する
     *
     * @access protected
     * @param string $val 起動日時データ
     * @return string 起動日時を示す日付文字列
     */
    protected function _parseCreationDate($val) {
        return date('Y-m-d H:i:s', strtotime(substr($val, 0, 14)));
    }

    /**
     * 非Windows環境向けにpsのetimeから起動日時の日付文字列を算出する。
     * Windows環境と異なり、相対値である経過時間がベースとなるため、タイミングによっては
     * 1秒の誤差が生じるため、この値はあくまで参考値とすること。
     *
     * @access protected
     * @param string $val 経過時間データ。psのetime形式
     * @return string 起動日時を示す日付文字列
     */
    protected function _parseElapsedTime($val) {
        $ptn = '^((\d+)\-)?((\d{2}):)?(\d{2}):(\d{2})$';
        if(!mb_ereg($ptn, $val, $matches)) {
            throw new \Exception("cannot parse elapsed time. '%s' is invalid format.", $val);
        }
        $times = array(
            'days' => (int)$matches[2],
            'hours' => (int)$matches[4],
            'minutes' => (int)$matches[5],
            'seconds' => (int)$matches[6]
        );
        // _execPsを呼び出した日時を基準に、etimeの各パートを使用して演算する
        $ts = $this->_exec_ts;
        $ts -= ($times['days'] * 24 * 60 * 60);
        $ts -= ($times['hours'] * 60 * 60);
        $ts -= ($times['minutes'] * 60);
        $ts -= $times['seconds'];

        // 日付文字列に変換
        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * 指定のコマンドラインコマンドを実行し、結果の出力を行データとして返す
     *
     * @access protected
     * @param string $command 引数付きの完全なコマンド
     * @return array $commandを実行した結果の行データ
     */
    protected function _execProcess($command) {
        $descs = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        // Win環境の場合にcmd.exeをバイパスするようオプションを構築する
        $opts = $this->isWindows() ? array('bypass_shell' => true) : array();

        // プロセス実行
        $proc = proc_open($command, $descs, $pipes, null, null, $opts);
        if(!is_resource($proc)) {
            throw new \Exception("cannot execute internal-command '%s'.", $command);
        }
        // 実行状態を安定させるため、0.1秒待ち合わせる
        usleep(100 * 1000);

        // プロセスが完了するまで待ち合わせる
        while(true) {
            $sts = proc_get_status($proc);
            if(!$sts['running']) break;
            usleep(100 * 1000);
        }

        // 標準出力および標準エラーを読み出す
        $output = trim(stream_get_contents($pipes[1]));
        $errout = trim(stream_get_contents($pipes[2]));

        // 標準エラーを無視しない設定の場合、エラー内容から例外を発生させる
        if(!$this->getIgnoreCommandError()) {
            if(strlen($errout)) {
                throw new \Exception(sprintf("internal-command error. command = '%s', message = '%s'",
                                            $command, $errout));
            }
        }

        // 行データの整形
        $result = array();
        foreach(mb_split('((\r\n)|[\r\n])', $output) as $line) {
            $line = trim($line);
            if(strlen($line)) $result[] = $line;
        }

        // すべてのパイプを閉じる
        foreach($pipes as $pipe) {
            @fclose($pipe);
        }
        // プロセスハンドルをクローズ
        @proc_close($proc);

        // 結果を返す
        return $result;
    }

    /**
     * killコマンドを実行する
     *
     * @access protected
     */
    protected function _kill() {
        // 現在のプロセスのkillは禁止
        if(getmypid() == $this->getProcessId()) {
            throw new \Exception('cannot terminate self process');
        }

        $max = 5;
        $success = false;
        while(--$max > 0) {
            // プラットフォーム固有処理を実行
            if($this->isWindows()) {
                $lines = $this->_execKillWin();
            } else {
                $lines = $this->_execKill();
            }
            $this->parseProcessInfo();
            if(!$this->isRunning()) {
                // プロセスが停止していたら成功
                $success = true;
                break;
            }
            // プロセスがまだ停止しないなら250ミリ秒待ってリトライ
            usleep(250 * 1000);
        }
        if(!$success) {
            // リトライ上限まで試行して停止しない場合は例外
            throw new \Exception('cannot terminate process');
        }
    }

    /**
     * Windows環境でプロセスの停止処理を実行する
     *
     * @access protected
     * @return array 実行結果の行データ
     */
    protected function _execKillWin() {
        $cmd = sprintf('wmic process where ProcessId=%d call terminate', $this->getProcessId());
        return $this->_execProcess($cmd);
    }

    /**
     * 非Windows環境でプロセスの停止処理を実行する
     *
     * @access protected
     * @return array 実行結果の行データ
     */
    protected function _execKill() {
        $cmd = sprintf('kill %d', $this->getProcessId());
        return $this->_execProcess($cmd);
    }

}
