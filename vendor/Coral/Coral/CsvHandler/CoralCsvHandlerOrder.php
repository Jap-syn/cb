<?php
namespace Coral\Coral\CsvHandler;

use Zend\Json\Json;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\IsNotNull;
use models\Table\TableCsvSchema;
use models\Table\TableSite;
use Coral\Base\BaseGeneralUtils;
use Coral\Coral\CsvHandler\CoralCsvHandlerAbstract;
use Coral\Coral\CsvHandler\Order\CoralCsvHandlerOrderBuilder;
use Zend\Validator\InArray;
use models\Logic\Normalizer\LogicNormalizerZenHyphens;
use models\Logic\Normalizer\LogicNormalizerZenHyphenCompaction;

class CoralCsvHandlerOrder extends CoralCsvHandlerAbstract {
	const CSVSCHEMA_CLASS = 1;

	const OPTIONS_DBADAPTER = 'dbAdapter';

	const OPTIONS_ENT_ID = 'ent_id';

	/**
	 * 事業者ID
	 *
	 * @var int
	 */
	protected $_ent_id;

	/**
	 * DBアダプタ
	 *
	 * @var Adapter
	 */
	protected $_dbAdapter;

	/**
	 * CsvSchema
	 *
	 * @var array
	 */
	protected $_schema;

	/**
	 * CSV行を解析・スタックし、注文データを構築するビルダ
	 *
	 * @var CoralCsvHandlerOrderBuilder
	 */
	protected $_builders;

	/**
	 * 現在の事業者に関連したサイト情報を格納した連想配列。
	 * キーがサイトID、値がname = サイト名、default = デフォルトサイトフラグで構成される連想配列になる
	 *
	 * @var array
	 */
	protected $_sites;

	/**
	 * コントローラからの通知パラメタ
	 *
	 * @var mixed|array
	 */
	protected $_controller_params = null;

	/**
	 * コントローラからの通知パラメタ設定
	 *
	 * @param mixed|array $controller_params コントローラからの通知パラメタ
	 * @return CoralCsvHandlerOrder
	 */
	public function setControllerParams($controller_params) {
        $this->_controller_params = $controller_params;
        return $this;
	}

	/**
	 * T_Prefectureの有効なRowset
	 *
	 * @var array
	 */
	protected $_prefectures = null;

	protected function init(array $options) {
		foreach( $options as $key => $value ) {
			switch( $key ) {
				case self::OPTIONS_DBADAPTER:
					$this->setDbAdapter( $value );
					break;
				case self::OPTIONS_ENT_ID:
					$this->setEnterpriseId( $value );
					break;
			}
		}

		$entId = $this->getEnterpriseId();
		if( is_null($this->_dbAdapter) ) throw new \Exception( 'DBアダプタが設定されていません' );
		if( ! is_int( $entId ) ) throw new \Exception( '事業者IDが設定されていません' );

        // 加盟店の注文CSV取込み指定の有無を確認
        $sql = " SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01001_1' AND TemplateClass = 2 AND Seq = :Seq AND ValidFlg = 1 ";
        $row_hdr = $this->_dbAdapter->query($sql)->execute(array(':Seq' => $this->_ent_id))->current();
        if (!$row_hdr) {
            // 加盟店定義なし
            $sql = " SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01001_1' AND TemplateClass = 0 AND Seq = 0 AND ValidFlg = 1 ";
            $row_hdr = $this->_dbAdapter->query($sql)->execute(null)->current();    // 本箇所での取得はシステムで保障される
        }

        $sql=<<<EOQ
SELECT TemplateSeq
,      ListNumber
,      PhysicalName AS ColumnName
,      LogicalName AS Caption
,      FieldClass
,      RequiredFlg
,      DefaultValue
,      DispWidth
,      TableName
,      ValidationRegex
,      ApplicationData
,      RegistDate
,      RegistId
,      UpdateDate
,      UpdateId
,      ValidFlg
FROM   M_TemplateField
WHERE  TemplateSeq = :TemplateSeq
AND    ValidFlg = 1
ORDER BY ListNumber
EOQ;
        $ri = $this->_dbAdapter->query($sql)->execute(array(':TemplateSeq' => $row_hdr['TemplateSeq']));

        $this->_schema = ResultInterfaceToArray($ri);

        $this->_builders = array();
	}

	/**
	 * DBアダプタを取得する
	 *
	 * @return Adapter
	 */
	public function getDbAdapter() {
		return $this->_dbAdapter;
	}

	/**
	 * DBアダプタを設定する
	 *
	 * @param Adapter $dbAdapter
	 * @return CoralCsvHandlerOrder
	 */
	public function setDbAdapter(Adapter $dbAdapter) {
		$this->_dbAdapter = $dbAdapter;

		return $this;
	}

	/**
	 * 事業者IDを取得する
	 *
	 * @return int
	 */
	public function getEnterpriseId() {
		return $this->_ent_id;
	}

	/**
	 * 事業者IDを設定する
	 *
	 * @param int $entId
	 * @return CoralCsvHandlerOrder
	 */
	public function setEnterpriseId($entId) {
		$this->_ent_id = (int)$entId;

		return $this;
	}

	/**
	 * CSVスキーマを取得する
	 *
	 * @return array
	 */
	public function getCsvSchema() {
		return $this->_schema;
	}

	/**
	 * CSVデータから作成された注文データを保持するすべてのCoralCsvHandlerOrderBuilderを取得する
	 *
	 * @return array CoralCsvHandlerOrderBuilderの配列。各ビルダが1つの注文データを保持する
	 */
	public function getBuilders() {
		if( ! is_array( $this->_builders ) ) $this->_builders = array();
		return $this->_builders;
	}

	/**
	 * 指定のCoralCsvHandlerOrderBuilderをビルダリストに追加する
	 *
	 * @param CoralCsvHandlerOrderBuilder $builder 追加するビルダ
	 * @return CoralCsvHandlerOrder
	 */
	protected function addBuilder(CoralCsvHandlerOrderBuilder $builder) {
		$builders = $this->getBuilders();
		$this->_builders[] = $builder;

		return $this;
	}

	/**
	 * 現在作業中のCoralCsvHandlerOrderBuilderを取得する
	 *
	 * @return CoralCsvHandlerOrderBuilder 現在データを構築中のビルダオブジェクト
	 */
	public function getCurrentBuilder() {
		$builders = $this->getBuilders();
		$builder = $builders[ count( $builders ) - 1 ];
		if( $builder == null || $builder->isClosed() ) {
			$builder = new CoralCsvHandlerOrderBuilder(
				$this->getCsvSchema(),
				array(
					CoralCsvHandlerOrderBuilder::OPTIONS_PREFECTURE_ROWSET => $this->getPrefectures(),
					CoralCsvHandlerOrderBuilder::OPTIONS_SITE_INFO => $this->getSiteInfo()
				)
			);

			$builder->setEnterpriseAndDBAdapter($this->_ent_id, $this->_dbAdapter);  // 加盟店IDとDBアダプタの設定
			$this->addBuilder( $builder );
		}
		return $builder;
	}

	/**
	 * サイト情報を示す連想配列を取得する
	 *
	 * @return array サイト情報の連想配列
	 */
	public function getSiteInfo() {
		if( empty( $this->_sites ) ) {
		    // 指定事業者のすべての(有効な)サイトデータを取得する
			$siteTable = new TableSite($this->_dbAdapter);
            $ri = $siteTable->getValidAll($this->getEnterpriseId());

			$this->_sites = array();
			foreach( $ri as $siteRow ) {
				$this->_sites[ $siteRow['SiteId']] = array(
					'name' => $siteRow['SiteNameKj'],
					'mail_require' => $siteRow['ReqMailAddrFlg']
				);
			}
		}

		return $this->_sites;
	}

	/**
	 * 有効な都道府県情報を格納したT_Prefectureの行セットを取得する
	 *
	 * @return array
	 */
	public function getPrefectures() {
	    if( is_null( $this->_prefectures ) ) {
            $sql = " SELECT * FROM M_Prefecture WHERE PrefectureCode > 0 AND ValidFlg = 1 ORDER BY PrefectureCode ";
            $ri = $this->_dbAdapter->query($sql)->execute(null);

            $rs = new ResultSet();
            $rs->initialize($ri);
            $this->_prefectures = $rs->toArray();
		}
		return $this->_prefectures;
	}

	/**
	 * CSV処理の開始前処理を実行する
	 *
	 */
	protected function begin() {
		$this->_builders = array();
	}

	/**
	 * CSVデータ行を処理し、結果の行データを返す
	 *
	 * @param array $row CSVデータ行
	 * @param int $line 処理行カウンタ。0ベース
	 * @return null|array 処理結果のデータ行。このクラスでは正常処理時はnullを返し、検証エラーが発生した場合はエラーメッセージの配列を返す
	 */
	protected function _validate(array $row, $line) {

	    $schema = $this->getCsvSchema();

		// 1行目の場合のみヘッダ行チェックを行い、ヘッダ行なら処理をスキップ
		if( $line == 0 && $this->checkHeaderLine( $row ) ) {

			return new CoralCsvHandlerLine( $row, $line, CoralCsvHandlerLine::TYPE_HEADER );
		}

		// ビルダを取得
		$builder = $this->getCurrentBuilder();

        // [画面上修正内容を反映する]対応
        $row_ex = $row;
        if (!is_null($this->_controller_params) && isset($this->_controller_params[('MODROW' . $line . '_0')])) {
            for ($i=0; $i<count($row); $i++) {
                $row_ex[$i] = $this->_controller_params[('MODROW' . $line . '_' . $i)]; // 画面内容で上書き
            }
        }

        $errors = $builder->addRow( $row_ex );

        $obj = new CoralCsvHandlerLine( $row_ex, $line, CoralCsvHandlerLine::TYPE_DATA);   // 第３引数を[TYPE_DATA]で処理する
        if( is_array( $errors ) ) { $obj->setErrors($errors); } // エラー情報があれば設定する

        return $obj;
	}

	/**
	 * CSV処理の後処理を実行する
	 */
	protected function end($result) {

	}

	/**
	 * 指定行がヘッダ行かを検出する
	 *
	 * @param array $row 検査する行データ
	 * @return bool $rowがヘッダ行と判断できた場合はtrue、それ以外はfalse
	 */
	protected function checkHeaderLine($row) {
		$schema = $this->getCsvSchema();
		$check_cols = array(
			'ReceiptOrderDate' => null
		);
		$index = 0;
		foreach( $schema as $col_schema ) {
			if( array_key_exists( $col_schema['ColumnName'], $check_cols ) ) {
				$check_cols[ $col_schema['ColumnName'] ] = array( 'index' => $index, 'schema' => $col_schema );
			}
			$index++;
		}
		// チェック対象のカラムがスキーマに含まれない場合は無条件に非ヘッダ行
		if( BaseGeneralUtils::ArrayIsAllEmpty( $check_cols ) ) {
			return false;
		}

		$result = true;
		foreach( $check_cols as $key => $schema ) {
			// 検証がNG且つ値が空でない場合はヘッダカラムの可能性ありとみなす
			$value = $row[$schema['index']];
			$validator = $schema['schema']['ValidationRegex'];
			$match = preg_match( $validator, $value );
			$result = $result && ( ! $match && ! empty( $value ) );

		}
		return $result;
	}

	/**
	 * 処理中に発生したエラー情報を取得する
	 * (Override関数：データステータスで判断せず、保持するエラー情報で判断する)
	 *
	 * @return array
	 */
	public function getExceptions() {
        $result = array();
        foreach( $this->getAllResults() as $line ) {
            $errors = $line->getErrors();
            if( count($errors) > 0 ) $result[] = $line;
        }
        return $result;
	}
	/**
	 * 登録データの対象カラムにハイフン正規化処理を実行
	 * @param array $csvSchema
	 */
	public function hyphenNormalize($csvSchema){
        //正規化対象カラムのインデックスを取得
        $listNums = array();
        foreach($csvSchema as $col){
            if(in_array($col['ColumnName'], array('UnitingAddress', 'NameKj', 'DestUnitingAddress', 'DestNameKj'), true)){
                $listNum = $col['ListNumber'] - 1; //listNumberは1からなので-1する
                $listNums[] = $listNum;
            }
        }
        //正規化処理実行
        $lnzh = new LogicNormalizerZenHyphens();
        $lnzhc = new LogicNormalizerZenHyphenCompaction();
        //count対策
        if(isset($this->_results)){
            for($i = 0;$i < count($this->_results);$i++){
                foreach($listNums as $key => $val){
                    $data = $this->_results[$i]->getData();
                    $data[$val] = $lnzhc->normalize($lnzh->normalize($data[$val]));
                    $this->_results[$i]->setData($data);
                }
            }
        }
  }
}