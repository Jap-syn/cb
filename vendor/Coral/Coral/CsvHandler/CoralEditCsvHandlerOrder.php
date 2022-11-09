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
use Coral\Coral\CsvHandler\Order\CoralEditCsvHandlerOrderBuilder;

class CoralEditCsvHandlerOrder extends CoralCsvHandlerOrder {

    /**
     * CSV行を解析・スタックし、注文データを構築するビルダ
     *
     * @var CoralEditCsvHandlerOrderBuilder
     */
    protected $_builders;

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

        // 加盟店の注文修正CSV取込み指定の有無を確認
        $sql = " SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01001_2' AND TemplateClass = 2 AND Seq = :Seq AND ValidFlg = 1 ";
        $row_hdr = $this->_dbAdapter->query($sql)->execute(array(':Seq' => $this->_ent_id))->current();
        if (!$row_hdr) {
            // 加盟店定義なし
            $sql = " SELECT TemplateSeq FROM M_TemplateHeader WHERE TemplateId = 'CKA01001_2' AND TemplateClass = 0 AND Seq = 0 AND ValidFlg = 1 ";
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
	 * CSVデータから作成された注文データを保持するすべてのCoralEditCsvHandlerOrderBuilderを取得する
	 *
	 * @return array CoralEditCsvHandlerOrderBuilderの配列。各ビルダが1つの注文データを保持する
	 */
	public function getBuilders() {
	    if( ! is_array( $this->_builders ) ) $this->_builders = array();
	    return $this->_builders;
	}

	/**
	 * 指定のCoralEditCsvHandlerOrderBuilderをビルダリストに追加する
	 *
	 * @param CoralEditCsvHandlerOrderBuilder $builder 追加するビルダ
	 * @return CoralEditCsvHandlerOrder
	 */
	// 20180906 引数が異なるので、オーバーライドしない（使ってないし・・・）
	//protected function addBuilder(CoralEditCsvHandlerOrderBuilder $builder) {
	protected function addBuilder2(CoralEditCsvHandlerOrderBuilder $builder) {
	    $builders = $this->getBuilders();
	    $this->_builders[] = $builder;

	    return $this;
	}

	/**
	 * 現在作業中のCoralEditCsvHandlerOrderBuilderを取得する
	 *
	 * @return CoralEditCsvHandlerOrderBuilder 現在データを構築中のビルダオブジェクト
	 */
	public function getCurrentBuilder() {
	    $builders = $this->getBuilders();
	    $builder = $builders[ count( $builders ) - 1 ];
	    if( $builder == null || $builder->isClosed() ) {
	        $builder = new CoralEditCsvHandlerOrderBuilder(
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
}