<?php
namespace Coral\Coral;


/**
 * @class
 *
 * 合計項目数と1ページあたりの項目数からページングに関する情報を生成するための
 * ヘルパークラス
 */
class CoralPager {
    /**
     * @static
     *
     * 指定の値を正の整数に整形する
     *
     * @param mixed $value 整形する値
     * @return int $valueを整形した製の整数値
     */
    public static function fixToInteger($value) {
        $value = preg_replace( '/[^\\d\\.]/', '', "$value" );
        return preg_match( '/^[\\d\\.]+$/', $value ) ? ((int)$value) : 0;
    }

    /**
     * @access protected
     *
     * 合計項目数
     *
     * @var int
     */
    protected $_total = 0;

    /**
     * @access protected
     *
     * 1ページあたりの項目数
     *
     * @var int
     */
    protected $_ipp = 1;

    /**
     * 合計項目数と1ページあたりの項目数を指定して、CoralPagerの
     * 新しいインスタンスを初期化する
     *
     * @param int|null $total_items 合計項目数。省略時は0
     * @param int|null $items_per_page 1ページあたりの項目数。省略時は1
     */
    public function __construct($total_items = 0, $items_per_page = 1) {
        $this->setTotalItems( $total_items )->setItemsPerPage( $items_per_page );
    }

    /**
     * 合計項目数を取得する
     *
     * @return int 合計項目数
     */
    public function getTotalItems() {
        return $this->_total;
    }
    /**
     * 合計項目数を設定する。負の数を指定した場合、0とみなされる。
     *
     * @param int $total_items 合計項目数
     * @return CoralPager
     */
    public function setTotalItems($total_items) {
        $this->_total = self::fixToInteger( $total_items );
        return $this;
    }

    /**
     * 1ページあたりの項目数を取得する
     *
     * @return int 1ページあたりの項目数
     */
    public function getItemsPerPage() {
        return $this->_ipp;
    }
    /**
     * 1ページあたりの項目数を設定する
     *
     * @param int $items_per_page 1ページあたりの項目数。1以下の値を指定した場合1とみなされる
     * @return CoralPager
     */
    public function setItemsPerPage($items_per_page) {
        $items_per_page = self::fixToInteger( $items_per_page );
        // 1以下にはしない
        $this->_ipp = $items_per_page < 1 ? 1 : $items_per_page;
        return $this;
    }

    /**
     * 現在の総ページ数を取得する
     *
     * @return int 現在の設定での総ページ数
     */
    public function getTotalPage() {
        return floor( $this->_total / $this->_ipp ) +
            ( $this->_total % $this->_ipp != 0 ? 1 : 0 );
    }

    /**
     * 指定のページの開始インデックスを取得する。
     * このメソッドは配列などの0ベースのリストに対するインデックスを想定しており、
     * 1ページ目の開始インデックスは0になる。
     *
     * 第二引数$one_baseにTRUEを指定した場合は1ベースのインデックスを返す。
     *
     * @param int $page インデックスを取得するページ番号。1以下または総ページ数の範囲内に補正される
     * @param null|bool $one_base 1ベースインデックスを採用するかのフラグ。省略時はFALSE
     * @return int $pageページ目の先頭項目にアクセスするインデックス番号
     */
    public function getStartIndex($page, $one_base = false) {
        $page = self::fixToInteger( $page );
        if( $page < 1 ) $page = 1;

        // 合計項目数を上限として0ベースの開始インデックスを算出
        $result = ( $page - 1 ) * $this->_ipp > $this->_total - 1 ?
            $this->_total - 1 : ( $page - 1 ) * $this->_ipp;

        // $one_baseの補正を適用して返す
        return $result + ( $one_base ? 1 : 0 );
    }
    /**
     * 指定ページの終了インデックスを取得する。
     * このメソッドは配列などの0ベースのリストに対するインデックスを想定しており、
     * 1ページ目の終了インデックスは1ページあたりの項目数 - 1になる。
     *
     * 第二引数$one_baseにTRUEを指定した場合は1ベースのインデックスを返す。
     *
     * @param int $page インデックスを取得するページ番号。1以下または総ページ数の範囲内に補正される
     * @param null|bool $one_base 1ベースのインデックスを採用するかのフラグ。省略時はFALSE
     * @return int $pageページ目の終了項目にアクセスするインデックス番号
     */
    public function getEndIndex($page, $one_base = false) {
        // 開始インデックス + 項目数 - 1で基準値計算
        $result = $this->getStartIndex( $page ) + $this->_ipp - 1;
        // 合計項目数を越えないよう補正
        $result = $result > $this->_total - 1 ? $this->_total - 1 : $result;

        // $one_baseの補正を適用
        return $result + ( $one_base ? 1 : 0 );
    }
    /**
     * 指定ページの項目にアクセスするための開始～終了インデックスを配列で取得する。
     * 戻り値の配列はキー'0'および'start'に開始インデックス、'1'および'end'に終了インデックスが格納される。
     *
     * 第二引数の$one_baseにTRUEを指定した場合、開始・終了インデックスとも1ベースのインデックスに補正される
     *
     * @param int $page インデックスを取得するページ番号。1以下または総ページ数の範囲内に補正される
     * @param null|bool $one_base 1ベースのインデックスを採用するかのフラグ。省略時はFALSE
     * @return array $pageページ目の開始・終了項目へアクセスするインデックスを格納した配列
     */
    public function getIndexRange($page, $one_base = false) {
        // 数値キーの配列を作成
        $result = array(
            $this->getStartIndex( $page, $one_base ),
            $this->getEndIndex( $page, $one_base )
        );

        // キー'start'と'end'を設定して返す
        return array_merge( $result, array( 'start' => $result[0], 'end' => $result[1] ) );
    }
}