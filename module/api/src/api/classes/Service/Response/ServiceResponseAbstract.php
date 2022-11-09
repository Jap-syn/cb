<?php
namespace api\classes\Service\Response;

/**
 * メッセージを管理するクラス。
 */
class Message {
    /**
     * @var string メッセージコード
     */
    public $messageCd;
    /**
     * @var string メッセージ内容
     */
    public $messageText;

    /**
     *
     * @param $cd
     * @param $text
     */
    public function __construct($cd, $text) {
        $this->messageCd = $cd;
        $this->messageText = $text;
    }
}

/**
 * サービスレスポンスの抽象クラス。<br>
 * 処理結果とメッセージのみを管理。
 */
class ServiceResponseAbstract {

    /**
     * @var string 処理結果が正常を示す文字列
     */
    const SUCCESS = 'success';
    /**
     * @var string 処理結果がエラーを示す文字列
     */
    const ERROR = 'error';

    /**
     * @var string ステータス文字列
     */
    public $status;

    /**
     * メッセージ
     * @var array メッセージ配列
     */
    public $messages = array();

    /**
     *
     * @param $cd メッセージコード
     * @param $text メッセージ内容
     */
    public function addMessage($cd, $text) {
        $this->messages[] = new Message($cd, $text);
    }
}