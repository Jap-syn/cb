<?php
namespace models\Logic\SmbcRelation\Receipt;

/**
 * SMBC決済ステーション連携入金処理ロジック用の入金履歴データクラス
 */
class LogicSmbcRelationReceiptItem {
    /** 契約コード @var string */
    public $shopCd;

    /** 収納企業コード @var string */
    public $syunoCoCd;

    /** 決済手段区分 @var string */
    public $billMethod;

    /** 請求番号 @var string */
    public $shopOrderNumber;

    /** 決済受付番号 @var string */
    public $kessaiNumber;

    /** 入金金額 @var int */
    public $receiptAmount;

    /** 入金金額（生データ） @var string */
    public $rawReceiptAmount;

    /** 収納日時 @var string */
    public $receivedDate;

    /** 精算期日 @var string */
    public $seisanDate;

    /** 振込予定日 @var string */
    public $depositDate;

    /** 顧客名 @var string */
    public $customerName;

    /** 顧客名カナ @var string */
    public $customerKana;

    /** 決済ステーション処理日時 @var string */
    public $processDate;

    /** データ種別 @var string */
    public $acceptCode;

    /** データ種別名 @var string */
    public $acceptName;

    /** 注文SEQ @var int */
    public $orderSeq;

    /** 注文ID @var string */
    public $orderId;

    /** 入金方法 @var int */
    public $payWayType;

    /** 印紙代フラグ @var int */
    public $stampFlag;

    /** 収納機関コード @var int */
    public $syunoKikanCd;

    /**
     * 履歴行データを使用してLogicSmbcRelationReceiptItemの
     * 新しいインスタンスを初期化する
     *
     * @param array $data 入金履歴行データ
     */
    public function __construct(array $data) {
        $this->shopCd           = $data[0];         // 項番1：契約コード
        $this->syunoCoCd        = $data[1];         // 項番2：収納企業コード
        $this->billMethod       = $data[3];         // 項番4：決済手段区分
        $this->shopOrderNumber  = $data[9];         // 項番10：請求番号
        $this->kessaiNumber     = $data[10];        // 項番11：決済受付番号
        $this->receiptAmount    = (int)$data[13];   // 項番14：入金金額
        $this->rawReceiptAmount = $data[13];        // （整数変換前の入金金額）
        $this->receivedDate     = $this->parseYYYYMMDDHHMMSS($data[14]);        // 項番15：収納日時
        $this->seisanDate       = $this->parseYYYYMMDD($data[15]);              // 項番16：精算期日
        $this->depositDate      = $this->parseYYYYMMDD($data[16]);              // 項番17：振込予定日
        $this->customerName     = $data[18];        // 項番19：顧客名
        $this->customerKana     = $data[19];        // 項番20：顧客名カナ
        $this->processDate      = $this->parseYYYYMMDDHHMMSS($data[20]);        // 項番21：決済ステーション処理日時
        $this->acceptCode       = $data[21];        // 項番22：種別・データ種別
        $this->acceptName       = $data[22];        // 項番23：種別名・データ種別名
        $this->syunoKikanCd     = $data[23];        // 項番24：収納機関コード

        $payway_map = array(
            '02' => 1,  // コンビニ
            '04' => 2,  // ゆうちょ
            '06' => 3   // 銀行
        );
        $this->payWayType = nvl($payway_map[$this->billMethod], -1);

        $this->stampFlag = 0;
    }

    /**
     * YYYYMMDDHHMMSS形式の日付時刻文字列をyyyy-MM-dd HH:mm:ss形式に展開する
     *
     * @access protected
     * @param string $s 日付時刻文字列
     * @return string
     */
    protected function parseYYYYMMDDHHMMSS($s) {
        return preg_replace('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', '\1-\2-\3 \4:\5:\6', $s);
    }

    /**
     * YYYYMMDD形式の日付文字列をyyyy-MM-dd形式に展開する
     *
     * @access protected
     * @param string $s 日付文字列
     * @return string
     */
    protected function parseYYYYMMDD($s) {
        return preg_replace('/^(\d{4})(\d{2})(\d{2})$/', '\1-\2-\3', $s);
    }
}
