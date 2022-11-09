<?php
namespace oemmember\classes;

use Zend\Json\Json;
use oemmember\Application;
//use models\Table\TableCsvSchema;
use models\Table\TableTemplateHeader;
use models\Table\TableTemplateField;
use models\Table\TableEnterprise;
use models\Logic\LogicTemplate;
use models\Table\TableSite;

/**
 * 履歴検索のユーティリティ。フォームに対するバインド情報などを提供する
 *
 */
class SearchUtility {
    const TEMPLATE_ID = 'CKA01005_1'; // 取引履歴検索結果一覧＆CSV

    const TEMPLATE_CLASS_DEFAULT = '0';     // CB（デフォルト）

    const TEMPLATE_CLASS = '2';     // 加盟店

    const DEFAULT_TEMPLATE_PATTERN = '0';

    /**
     * 検索条件式が「BETWEEN」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_BETWEEN = 'between';

    /**
     * 条件式が「＝」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_EQUAL = 'equal';

    /**
     * 条件式が「！＝」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_NOT_EQUAL = 'not equal';

    /**
     * 条件式が「LIKE '%?%'」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_LIKE = 'like';

    /**
     * SEARCH_TYPE_LIKEと同等の条件タイプ定数だが、
     * 対象フィールドの空白文字を除去指定を含む点が異なる
     *
     */
    const SEARCH_TYPE_LIKE_CONCAT = 'like concat';

    /**
     * SEARCH_TYPE_LIKEと同等の条件タイプ定数だが、
     * 対象フィールドのハイフンを除去指定を含む点が異なる
     *
     */
    const SEARCH_TYPE_LIKE_PHONE = 'like phone';

    /**
     * 条件式が「LIKE '?%'」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_STARTS_WITH = 'starts with';

    /**
     * 条件式が「LIKE '%?'」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_ENDS_WITH = 'ends with';

    /**
     * 条件式が「＞」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_GREATER_THAN = 'greater than';

    /**
     * 条件式が「＜」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_LESS_THAN = 'less than';

    /**
     * 条件式が「＞＝」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_GREATER_THAN_OR_EQUAL_TO = 'greater than or equal to';

    /**
     * 条件式が「＜＝」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_LESS_THAN_OR_EQUAL_TO = 'less than or equal to';

    /**
     * 条件式が「in (value, value...)」であることを示す条件タイプ定数
     *
     */
    const SEARCH_TYPE_IN_VALUES = 'in values';

    /**
     * 顧客情報に対する検索条件であることを示す条件グループ定数
     *
     */
    const SEARCH_GROUP_CUSTOMER = 'customer';

    /**
     * 注文情報に対する検索条件であることを示す条件グループ定数
     *
     */
    const SEARCH_GROUP_ORDER = 'order';

    /**
     * 配送情報に対する検索条件であることを示す条件グループ定数
     *
     */
    const SEARCH_GROUP_DELIVERY = 'delivery';

    /**
     * その他の情報に対する検索条件であることを示す条件グループ定数
     *
     */
    const SEARCH_GROUP_OTHER = 'other';

    /**
     * フォームフィールドが単一行のテキストフィールドであることを示すコントロール種別定数
     *
     */
    const CONTROL_TYPE_SIMPLE_TEXT = 'simple_text';

    /**
     * フォームフィールドが複数行テキストであることを示すコントロール種別定数
     *
     */
    const CONTROL_TYPE_MULTILINE_TEXT = 'multi_text';

    /**
     * フォームフィールドがリスト選択であることを示すコントロール種別定数
     *
     */
    const CONTROL_TYPE_LIST = 'list';

    /**
     * フォームフィールドが2つのテキストフィールドの範囲を指定することを示すコントロール種別定数
     *
     */
    const CONTROL_TYPE_TEXT_SPAN = 'text_span';

    /**
     * フォームフィールドが2つの日付フィールドの範囲を指定することを示すコントロール種別定数
     *
     */
    const CONTROL_TYPE_DATE_SPAN = 'date_span';

    /**
     * フォームフィールドが複数チェックボックスであることを示すコントロール種別定数
     *
     */
    const CONTROL_TYPE_CHECKBOXES = 'checkboxes';


    /**
     * フォームフィールドが配送伝票番号であることを示すコントロール種別定数
     *
     */
    const CONTROL_TYPE_JOURNALNUMBER = 'journalnumber';

    /**
     * getSearchConditions()メソッドの実行結果キャッシュ
     *
     * @var array
     */
    protected static $_searchConditions;

//    protected static $_columnMaps;

    /**
     * getGroupMaps()メソッドの実行結果キャッシュ
     *
     * @var array
     */
    protected static $_groupMap;

    /**
     * findConditionByColumn()メソッド用のキャッシュで、カラム名をキー、対応するgetSearchConditions()の要素を値とする
     *
     * @var array
     */
    protected static $_columnCache;

    /**
     * 検索方法などのメタデータのベースマスタ連想配列
     *
     * @var array
     */
    protected static $_defs;

    /**
     * 事業者ID
     */
    protected static $enterpriseId;

    /**
     * 事業者IDを設定する。
     *
     * @param int $entId 事業者ID
     */
    public function setEnterpriseId( $entId ) {
        self::$enterpriseId = $entId;
    }

    /**
     * 検索キーカラムのデータ型キャッシュ
     *
     * @access protected
     * @var array
     */
    protected static $_dataTypesCache;

    /**
     * 利用可能な検索条件項目を配列で取得する。
     * 戻り値の配列の各要素はどの情報に対する条件かを示す'group'、検索時のDBカラム名である'column'、
     * 表示時のキャプションである'caption'、検索タイプを示す'type'、HTMLに表示するヘルプ情報である'help'、
     * フォーム上のコントロールスクリプトを示す'control'の各キーを持つ連想配列になる。
     *
     * @param int $entId 事業者ID
     * @param int $templateClass 区分(2: 加盟店テンプレート、3: サイトテンプレート)
     * @param int $templatePattern テンプレートパターン(区分2: 0、区分3: サイトID)
     * @return array
     */
    public static function getSearchConditions($entId,$templatePattern=null) {
        if( $templatePattern == -99 || $templatePattern == null ) {
            $templatePattern = 0;
        }
        if( ! is_array( self::$_searchConditions ) ) {
            self::$_searchConditions = array();
        };

        if( !is_array( self::$_searchConditions[$templatePattern] ) ) {
            $templateHeader = new TableTemplateHeader( Application::getInstance()->dbAdapter );
            $templateSeq = $templateHeader->getTemplateSeq( self::TEMPLATE_ID, self::TEMPLATE_CLASS, $entId, $templatePattern );
            $templateField = new TableTemplateField( Application::getInstance()->dbAdapter );
            $mdlEnt = new TableEnterprise( Application::getInstance()->dbAdapter );
            $ent = $mdlEnt->find($entId)->current();
            // サイトのデータを取得
            $siteTable = new TableSite(Application::getInstance()->dbAdapter );
            $siteList = $siteTable->getAll($entId);
            $PaymentAfterArrivalFlg = false;
            foreach( $siteList as $site ) {
                if ( $site['PaymentAfterArrivalFlg'] == 1){
                    $PaymentAfterArrivalFlg = true;
                }
            }
            $arr = array();
            $rows = ResultInterfacetoArray( $templateField->get( $templateSeq ) );
            $i = 0;
            foreach( $rows as $row ) {
                if( $row['PhysicalName'] == 'RegistName' || $row['PhysicalName'] == 'UpdateName' || $row['PhysicalName'] == 'UpdateDate' ) {
                    continue;
                }
                if ( $ent['ReceiptStatusSearchClass'] == '0' &&
                    ($row['PhysicalName'] == 'IsWaitForReceipt' || $row['PhysicalName'] == 'ReceiptDate' || $row['PhysicalName'] == 'ReceiptProcessDate' || $row['PhysicalName'] == 'ReceiptClass') ) {
                    // 入金ステータス検索条件区分 0:不可 の場合、入金状態もしくは入金日の検索は不可
                    continue;
                }
                if ( !$PaymentAfterArrivalFlg && $row['PhysicalName'] == 'ExtraPayKey') {
                    // サイト.届いてから決済利用フラグ 0：利用しない かつ テンプレートフィールドマスター.フィールド名 ExtraPayKeyの場合、設定不可
                    continue;
                }
                if ( $ent['CreditTransferFlg'] == '0' &&
                    ($row['PhysicalName'] == 'CreditTransferRequestFlg' || $row['PhysicalName'] == 'RequestStatus' || $row['PhysicalName'] == 'RequestSubStatus' || $row['PhysicalName'] == 'RequestCompDate' || $row['PhysicalName'] == 'CreditTransferMethod1' || $row['PhysicalName'] == 'CreditTransferMethod2') ) {
                    continue;
                }
                $arr[$i]['column'] = $row['PhysicalName'];
                $arr[$i]['caption'] = $row['LogicalName'];
                $arr[$i]['hidden'] = !(bool)$row['ValidFlg'];
                $arr[$i]['required'] = (bool)$row['RequiredFlg'];
                if( $row['PhysicalName'] == 'NgNoGuaranteeChange' ) {
                    $arr[$i]['hidden'] = true;
                }
                $i++;
            }

            self::$_searchConditions[$templatePattern] = self::fixMetadata($arr);
        }

        return self::$_searchConditions[$templatePattern];
    }

    /**
     * 検索方法などのメタデータのベースマスタを取得する
     *
     * @return array
     */
    public static function getMetadataDefinitions() {
        if(empty(self::$_defs) || ! is_array(self::$_defs)) {
            // config/search_settings.json を連想配列に復元
            $filename = Application::getInstance()->configRoot . '/search_settings.json';
            self::$_defs =Json::decode(file_get_contents($filename), Json::TYPE_ARRAY);
        }
        return self::$_defs;
    }

    /**
    /**
     * 指定の検索条件項目のメタデータ部分を内部定義のベースマスタで更新する。
     * この処理を経ることで、検索方法やヘルプキャプションなどはすべて search_settings.json での
     * 定義が適用されることになる
     *
     * @access protected
     * @param array $conditions 事業者IDで絞り込み済みの、T_TemplateFieldから構築した検索条件項目
     * @return array $conditionsの内容をsearch_settings.jsonの内容で上書きfixした検索条件項目
     */
    protected static function fixMetadata(array $conditions) {
        $defs = self::getMetadataDefinitions();

        // 引数を順次ループ
        foreach($conditions as $i => &$condition) {
            $key = $condition['column'];
            // 内部定義にキーが存在していたら上書きする
            if(isset($defs[$key])) {
                foreach($defs[$key] as $k => $v) {
                    $condition[$k] = $v;
                }
            }
        }
        return $conditions;
    }

    /**
     * 条件グループと対応する見出し情報を格納した連想配列を取得する。
     * キーはこのクラスのSEARCH_GROUP_*で定義される定数値で、その定数値に対応する見出し文言が値になる。
     *
     * @return array
     */
    public static function getGroupMap() {
        if( ! is_array( self::$_groupMap ) ) {
            self::$_groupMap = array(
                self::SEARCH_GROUP_CUSTOMER    => '顧客登録情報',
                self::SEARCH_GROUP_ORDER    => '注文登録情報',
                self::SEARCH_GROUP_DELIVERY    => '配送伝票番号登録情報',
                self::SEARCH_GROUP_OTHER    => 'その他の情報'
            );
        }

        return self::$_groupMap;
    }

    /**
     * 指定のDBカラム名に対応する検索条件項目を取得する。
     * 戻り値はgetSearchConditions()メソッドの戻り値に含まれる項目で、'column'の値が$columnに一致する
     *
     * @param string $column キーとなるDBカラム名
     * @return array 検索条件項目を示す連想配列
     */
    public static function findConditionByColumn($column) {
        if( ! is_array( self::$_columnCache ) ) {
            // キャッシュ用配列の作成
            self::$_columnCache = array();
        }
        if( isset( self::$_columnCache[ $column ] ) ) {
            // キャッシュにヒットしたのでそれを返す
            return self::$_columnCache[ $column ];
        }

        // 条件一式を取得し、一致するものを探す
        foreach( self::getSearchConditions( self::$enterpriseId ) as $condition ) {
            if( $condition['column'] == $column ) {
                // ヒットした条件をキャッシュして返す
                self::$_columnCache[ $column ] = $condition;
                return $condition;
            }
        }

        // 見つからない
        return null;
    }

    /**
     * 指定の検索条件項目と入力値の連想配列から検索条件オブジェクトを作成する
     *
     * @param array $condition 検索条件項目の連想配列
     * @param array $values 検索フォームからポストされたすべての値を格納した連想配列
     * @param array $refList $conditionに設定される値の表示を解決する参照リスト
     * @param null | boolean $suppressForceStringSearch 文字列型カラムへの検索条件を強制的に文字列へキャストする
     *                                                  変換処理を実行しないかのフラグ。デフォルトはfalseで、文字列型
     *                                                  カラムへの検索条件はキャスト関数を経た条件を生成する
     * @return SearchExpressionInfo
     */
    public static function createExpression($condition, $values, $refList = array(),$suppressForceStringSearch = false) {
        if( ! is_array( $refList ) ) $refList = array();
        $id = $condition['column'];
        $value = $values[$id];
        $value2 = $values["{$id}_2"];

        if(!is_array($value)) {
            // 複数選択項目でない場合
            if( $refList[$value] !== null ) {
                $value = $refList[$value];
            }
        } else {
            // 複数選択項目の場合
            $buf = array();
            foreach($value as $v) {
                if(isset($refList[$v])) $buf[] = $refList[$v];
            }
            $value = join('、', $buf);
        }
        if(!is_array($value2)) {
            // 複数選択項目でない場合
            if( $refList[$value2] !== null ) {
                $value2 = $refList[$value2];
            }
        } else {
            // 複数選択項目の場合
            $buf = array();
            foreach($value2 as $v) {
                if(isset($refList[$v])) $buf[] = $refList[$v];
            }
            $value2 = join('、', $buf);
        }
        // カラム名からオリジナルのコントロール定義を取得
        // → BETWEENからgte/lteへ切り替えるためにコントローラ側でコントロール定義を変更する可能性があるため
        $org_condition = self::findConditionByColumn( $id );

        // カラム名が文字列型かを判断 (2015/06/19)
        $char_col = self::isCharColumn( $id );

        // $suppressForceStringSearchにtrueが設定されていたら文字列型カラムと見なさない (2015/06/19)
        if( $suppressForceStringSearch ) $char_col = false;

        switch( $condition['control'] ) {
            case self::CONTROL_TYPE_DATE_SPAN:
                if( $values[ "{$id}_Mode" ] ) {
                    // Deli_JournalIncDate と RegistDate と ApprovalDate と ReceiptProcessDate はDatetime型
                    if( $id == 'Deli_JournalIncDate' || $id == 'RegistDate' || $id == 'ApprovalDate' || $id == 'ReceiptProcessDate' ) {
                        return new SearchExpressionInfo(
                            "$id >= :$id AND $id <= :$id" . '2',
                            "{$condition['caption']} が '$value' から '$value2' の間",
                            array( ":$id" => $value . ' 00:00:00', ":$id" . '2' => $value2 . ' 23:59:59' )
                        );
                    }
                    return new SearchExpressionInfo(
                        "$id BETWEEN :$id AND :$id" . '2',
                        "{$condition['caption']} が '$value' から '$value2' の間",
                        array( ":$id" => $value, ":$id" . '2' => $value2 )
                    );
                }
                else {
                    // Deli_JournalIncDate と RegistDate と ApprovalDate と ReceiptProcessDate はDatetime型
                    if( $id == 'Deli_JournalIncDate' || $id == 'RegistDate' || $id == 'ApprovalDate' || $id == 'ReceiptProcessDate' ) {
                        return new SearchExpressionInfo(
                            "$id >= :$id AND $id <= :$id" . '2',
                            "{$condition['caption']} が '$value' である",
                            array( ":$id" => $value . ' 00:00:00', ":$id" . '2' => $value . ' 23:59:59' )
                        );
                    }
                    return new SearchExpressionInfo(
                        "$id = :$id",
                        "{$condition['caption']} が '$value' である",
                        array( ":$id" => $value )
                    );
                }
                break;
            case self::CONTROL_TYPE_TEXT_SPAN:
                if( $values[ "{$id}_Mode" ] ) {
                    if( $id == 'OrderId' ) {
                        if( ( $numbericFlg = preg_match( '/^[0-9]+$/', $value ) ) == 1 ) {
                            $value2 = preg_replace( '/[^0-9]/', '', $value2 );
                        }
                    }
                    return new SearchExpressionInfo(
                        $char_col ? "$id BETWEEN CAST(:$id AS CHAR) AND CAST(:$id" . '2 AS CHAR)' : "$id BETWEEN :$id AND :$id" . '2',
                        "{$condition['caption']} が '$value' から '$value2' の間",
                        array( ":$id" => $value, ":$id" . '2' => $value2 ),
                        self::isCharColumn($id) && $suppressForceStringSearch
                    );
                }
                else {
                    if( $id == 'OrderId' ) {    // OrderIdは後方一致
                        return new SearchExpressionInfo(
                            $char_col ? "$id LIKE CAST(:$id AS CHAR)" : "$id LIKE :$id",
                            "{$condition['caption']} が '$value' で終わる",
                            array( ":$id" => '%' . $values[$id] ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                    }
                    else if( $id == 'Ent_OrderId' ) {    // Ent_OrderIdは部分一致
                        return new SearchExpressionInfo(
                            $char_col ? "$id LIKE CAST(:$id AS CHAR)" : "$id LIKE :$id",
                            "{$condition['caption']} が '$value' を含む",
                            array( ":$id" => '%' . $values[$id] . '%' ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                    }
                    return new SearchExpressionInfo(
                        $char_col ? "$id = CAST(:$id AS CHAR)" : "$id = :$id",
                        "{$condition['caption']} が '$value' である",
                        array( ":$id" => $value ),
                        self::isCharColumn($id) && $suppressForceStringSearch
                    );
                }
                break;
            case self::CONTROL_TYPE_LIST:
                return new SearchExpressionInfo(
                    "$id = :$id",
                    "{$condition['caption']} が '$value' である",
                    array( ":$id" => $values[$id] )
                );
                break;
            case self::CONTROL_TYPE_CHECKBOXES:
                $parameter = array();
                foreach( $values[$id] as $k => $v ) {
                    if( $id == 'ClaimSendingClass' && $v == 21 ) {   // 同梱/別送の場合は 12 も別送とする
                        $parameter = array_merge( $parameter, array( ":$id" . 99 => '12' ) );
                    }
                    $parameter = array_merge( $parameter, array( ":$id" . $k => $v ) );
                }
                $exception = "$id IN (" . implode( array_keys( $parameter ), ', ' ) . ')';
                $countValues = 0;
                if(!empty($values[$id])){
                    $countValues = count($values[$id]);
                }
                return new SearchExpressionInfo(
                    $exception,
                    $countValues ?
                        "{$condition['caption']} が [$value] のいずれかである" :
                        "{$condition['caption']} がいずれかである",
                    $parameter
                );
                break;
            case self::CONTROL_TYPE_JOURNALNUMBER:   // 配送伝票番号は部分一致
                return new SearchExpressionInfo(
                    "$id LIKE :$id",
                    "{$condition['caption']} が '$value' を含む",
                    array( ":$id" => '%' . $values[$id] . '%' )
                );
                break;
            default:
                switch( $condition['type'] ) {
                    case self::SEARCH_TYPE_EQUAL:
                        return new SearchExpressionInfo(
                            $char_col ? "$id = :$id" : "$id = CAST(:$id AS CHAR)",
                            "{$condition['caption']} が '$value' である",
                            array( ":$id" => $value ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                        break;
                    case self::SEARCH_TYPE_NOT_EQUAL:
                        return new SearchExpressionInfo(
                            $char_col ? "$id <> CAST(:$id AS CHAR)" : "$id <> :$id",
                            "{$condition['caption']} が '$value' でない",
                            array( ":$id" => $value ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                        break;
                    case self::SEARCH_TYPE_GREATER_THAN:
                        return new SearchExpressionInfo(
                            $char_col ? "$id > CAST(:$id AS CHAR)" : "$id > :$id",
                            ( $org_condition['control'] == self::CONTROL_TYPE_DATE_SPAN ?
                                "{$condition['caption']} が '$value' より未来" :
                                "{$condition['caption']} が '$value' より大きい"
                            ),
                            array( ":$id" => $value ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                        break;
                    case self::SEARCH_TYPE_GREATER_THAN_OR_EQUAL_TO:
                        return new SearchExpressionInfo(
                            $char_col ? "$id >= CAST(:$id AS CHAR)" : "$id >= :$id",
                            ( $org_condition['control'] == self::CONTROL_TYPE_DATE_SPAN ?
                                "{$condition['caption']} が '$value' 以降" :
                                "{$condition['caption']} が '$value' 以上"
                            ),
                            array( ":$id" => $value ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                        break;
                    case self::SEARCH_TYPE_LESS_THAN:
                        return new SearchExpressionInfo(
                            $char_col ? "$id < CAST(:$id AS CHAR)" : "$id < :$id",
                            ( $org_condition['control'] == self::CONTROL_TYPE_DATE_SPAN ?
                                "{$condition['caption']} が '$value' より過去" :
                                "{$condition['caption']} が '$value' より小さい"
                            ),
                            array( ":$id" => $values[$id] ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                        break;
                    case self::SEARCH_TYPE_LESS_THAN_OR_EQUAL_TO:
                        // Deli_JournalIncDate と RegistDate と ApprovalDate と ReceiptProcessDate はDatetime型
                        if( $id == 'Deli_JournalIncDate' || $id == 'RegistDate' || $id == 'ApprovalDate' || $id == 'ReceiptProcessDate' ) {
                            $values[$id] = $values[$id] . ' 23:59:59';
                        }
                        return new SearchExpressionInfo(
                            $char_col ? "$id <= CAST(:$id AS CHAR)" : "$id <= :$id",
                            ( $org_condition['control'] == self::CONTROL_TYPE_DATE_SPAN ?
                                "{$condition['caption']} が '$value' 以前" :
                                "{$condition['caption']} が '$value' 以下"
                            ),
                            array( ":$id" => $values[$id] ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                        break;
                    case self::SEARCH_TYPE_LIKE:
                        return new SearchExpressionInfo(
                            $char_col ? "$id LIKE CAST(:$id AS CHAR)" :"$id LIKE :$id",
                            "{$condition['caption']} が '$value' を含む",
                            array( ":$id" => '%' . $values[$id] . '%' ),
                            self::isCharColumn($id) && $suppressForceStringSearch
                        );
                        break;
                    case self::SEARCH_TYPE_LIKE_CONCAT:    // 2010.10.6 eda
                        // 半角スペース/全角スペース/タブ文字を対象フィールドから除去したものに対してLIKEする

                        // 検索値もスペース/タブを除去しておく
                        $v = mb_ereg_replace( '[ 　\t]', '', $values[$id] );
                        return new SearchExpressionInfo(
                            "REPLACE(REPLACE(REPLACE($id, ' ', ''), '　', ''), '\\t', '') LIKE :$id",
                            "{$condition['caption']} が '$value' を含む",
                            array( ":$id" => '%' . $v . '%' )
                        );
                        break;
                    case self::SEARCH_TYPE_LIKE_PHONE:
                        // ハイフンを対象フィールドから除去したものに対してLIKEする

                        // 検索値もハイフンを除去しておく
                        $v = mb_ereg_replace( '[-]', '', $values[$id] );
                        return new SearchExpressionInfo(
                        "REPLACE($id, '-', '') LIKE :$id",
                        "{$condition['caption']} が '$value' を含む",
                        array( ":$id" => '%' . $v . '%' )
                        );
                        break;
                    case self::SEARCH_TYPE_STARTS_WITH:
                        if( $id != 'ItemNameKj' ) {
                            return new SearchExpressionInfo(
                                "$id  LIKE :$id",
                                "{$condition['caption']} が '$value' で始まる",
                                array( ":$id" => $values[$id] .'%' )
                            );
                        }
                        return new SearchExpressionInfo(
                            "V.OrderSeq IN ( SELECT OrderSeq FROM T_OrderItems WHERE $id LIKE :$id",
                            "{$condition['caption']} が '$value' で始まる",
                            array( ":$id" => $values[$id] . '%' )
                        );
                        break;
                    case self::SEARCH_TYPE_ENDS_WITH:
                        return new SearchExpressionInfo(
                            "$id LIKE :$id",
                            sprintf("$id LIKE '%%%s'", self::_escapeWildcard($values[$id])),
                            "{$condition['caption']} が '$value' で終わる",
                            array( ":$id" => '%' . $values[$id] )
                        );
                        break;
                }
                break;
        }
        return null;
    }

    /**
     * MySQLでLIKEを発行できるよう入力文字列をエスケープする
     * ・ワイルドカード文字（%および_）もバックスラッシュエスケープする
     * ・バックスラッシュ自体は通常の2重バックスラッシュではなく4重バックスラッシュにエスケープする
     * ・（quoteではないので）前後に引用符は付加しない
     * @param string $wc
     * @return string
     */
    private static function _escapeWildcard($s) {
        // 事前にバックスラッシュを2重化してからaddcslashesを行う
        // → addcslashesのパラメータはZend_Db_Adapter_Abstract::quote()と同一
        return addcslashes(str_replace("\\", "\\\\", $s), "\000\r\n\\'\"\032%_");
    }

    /**
     * 指定の検索カラムが文字列型かを判断する
     *
     * @static
     * @access protected
     * @param string $colName カラム名
     * @return boolean
     */
    protected static function isCharColumn($colName) {
        if(self::$_dataTypesCache == null || !is_array(self::$_dataTypesCache)) {
            self::$_dataTypesCache = array();

            // 定義情報はひとまず固定(本来は"DESC V_OrderSearch")
            $columns = array();
            $columns[] = array('Field' => 'OrderSeq', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'ReceiptOrderDate', 'Type' => 'date' );
            $columns[] = array('Field' => 'DataStatus', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'EnterpriseId', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'SiteId', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'OrderId', 'Type' => 'varchar(50)' );
            $columns[] = array('Field' => 'Ent_OrderId', 'Type' => 'varchar(255)' );
            $columns[] = array('Field' => 'Ent_Note', 'Type' => 'varchar(4000)' );
            $columns[] = array('Field' => 'UseAmount', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'RegistDate', 'Type' => 'datetime' );
            $columns[] = array('Field' => 'OutOfAmends', 'Type' => 'bigint(11)' );
            $columns[] = array('Field' => 'IncreStatus', 'Type' => 'int(2)' );
            $columns[] = array('Field' => 'CarriageFee', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'ChargeFee', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'Chg_ExecDate', 'Type' => 'date' );
            $columns[] = array('Field' => 'Cnl_CantCancelFlg', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'Cnl_Status', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'AnotherDeliFlg', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'CombinedClaimTargetStatus', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'P_OrderSeq', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'CombinedClaimParentFlg', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'ClaimSendingClass', 'Type' => 'tinyint(4)' );
            $columns[] = array('Field' => 'ServiceExpectedDate', 'Type' => 'date' );
            $columns[] = array('Field' => 'CustomerId', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'NameKj', 'Type' => 'varchar(160)' );
            $columns[] = array('Field' => 'NameKn', 'Type' => 'varchar(160)' );
            $columns[] = array('Field' => 'PostalCode', 'Type' => 'varchar(12)' );
            $columns[] = array('Field' => 'UnitingAddress', 'Type' => 'varchar(4000)' );
            $columns[] = array('Field' => 'Phone', 'Type' => 'varchar(50)' );
            $columns[] = array('Field' => 'MailAddress', 'Type' => 'varchar(255)' );
            $columns[] = array('Field' => 'EntCustId', 'Type' => 'varchar(255)' );
            $columns[] = array('Field' => 'DestNameKj', 'Type' => 'varchar(160)' );
            $columns[] = array('Field' => 'DestNameKn', 'Type' => 'varchar(160)' );
            $columns[] = array('Field' => 'DestPostalCode', 'Type' => 'varchar(12)' );
            $columns[] = array('Field' => 'DestUnitingAddress', 'Type' => 'varchar(4000)' );
            $columns[] = array('Field' => 'DestPhone', 'Type' => 'varchar(50)' );
            $columns[] = array('Field' => 'OrderItemId', 'Type' => 'bigint(20)' );
            $columns[] = array('Field' => 'OrderItemNames', 'Type' => 'varchar(4000)' );
            $columns[] = array('Field' => 'ItemNameKj', 'Type' => 'varchar(255)' );
            $columns[] = array('Field' => 'ItemCount', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'Deli_JournalIncDate', 'Type' => 'datetime' );
            $columns[] = array('Field' => 'Deli_DeliveryMethod', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'Deli_DeliveryMethodName', 'Type' => 'varchar(255)' );
            $columns[] = array('Field' => 'Deli_JournalNumber', 'Type' => 'varchar(255)' );
            $columns[] = array('Field' => 'CancelDate', 'Type' => 'datetime' );
            $columns[] = array('Field' => 'CancelReason', 'Type' => 'varchar(255)' );
            $columns[] = array('Field' => 'ApprovalDate', 'Type' => 'datetime' );
            $columns[] = array('Field' => 'CancelReasonCode', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'ExecScheduleDate', 'Type' => 'date' );
            $columns[] = array('Field' => 'ClaimDate', 'Type' => 'date' );
            $columns[] = array('Field' => 'Cnl_ReturnSaikenCancelFlg', 'Type' => 'bigint(11)' );
            $columns[] = array('Field' => 'RealCancelStatus', 'Type' => 'int(2)' );
            $columns[] = array('Field' => 'Deli_JournalNumberAlert', 'Type' => 'int(0)' );
            $columns[] = array('Field' => 'ArrivalConfirmAlert', 'Type' => 'int(0)' );
            $columns[] = array('Field' => 'IsWaitForReceipt', 'Type' => 'int(0)' );
            $columns[] = array('Field' => 'NgNoGuaranteeChange', 'Type' => 'int(0)' );
            $columns[] = array('Field' => 'ReceiptClass', 'Type' => 'int(11)' );
            $columns[] = array('Field' => 'ReceiptProcessDate', 'Type' => 'datetime' );


//             foreach($db->describeTable('V_OrderSearch') as $defColName => $def) {
//                 self::$_dataTypesCache[strtoupper($defColName)] = strtoupper($def['DATA_TYPE']);
//             }
//             $type = self::$_dataTypesCache[strtoupper($colName)];
            foreach( $columns as $column ) {
                if( stristr( $column['Type'], 'INT' ) ) {
                    $type = 'INT';
                }
                else if( stristr( $column['Type'], 'DATE' ) ) {
                    $type = 'DATE';
                }
                else if( stristr( $column['Type'], 'VARCHAR' ) ) {
                   $type = 'VARCHAR';
                }
                else if( stristr( $column['Type'], 'CHAR' ) ) {
                    $type = 'CHAR';
                }
                else if( stristr( $column['Type'], 'TEXT' ) ) {
                    $type = 'TEXT';
                }
                else {
                    $type= '';
                }
                self::$_dataTypesCache[$column['Field']] = $type;
            }
        }
        $type = self::$_dataTypesCache[$colName];
        $result = in_array($type, array('CHAR', 'VARCHAR', 'TEXT')) ? true : false;
        return $result;
    }
}
?>