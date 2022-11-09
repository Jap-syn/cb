<?php
namespace oemmember\classes;

use Coral\Base\IO\BaseIOCsvReader;

class OrderDataBuilder {
	/**
	 * CSVファイルを処理するBaseIOCsvReader
	 *
	 * @var BaseIOCsvReader
	 */
	protected $_reader;

	/**
	 * カラム定義の配列
	 *
	 * @var array
	 */
	protected $_columns;

	/**
	 * CSVから生成された注文情報の連想配列
	 *
	 * @var array
	 */
	protected $_buffer;

	/**
	 * OrderDataBuilderの新しいインスタンスを初期化する
	 *
	 * @param BaseIOCsvReader $reader CSVリーダー
	 * @param array $columns カラム定義リスト
	 *
	 */
	public function __construct(BaseIOCsvReader $reader, $columns = array()) {
		$this->_reader = $reader;

		$this->_columns = array();

		if( ! is_array( $columns ) ) {
			if( $columns instanceof OrderColumnInfo ) {
				$columns = array( $columns );
			} else {
				$columns = array();
			}
		}

		foreach($columns as $columnInfo) {
			$this->addColumnInfo( $columnInfo );
		}

		$this->_buffer = array();
	}

	/**
	 * 指定のインデックス位置のカラム定義を取得する
	 *
	 * @param int $index カラム定義リストのインデックス位置
	 * @return OrderColumnInfo
	 */
	public function getColumnInfo($index) {
		$index = (int)$index;

		return $this->_columns[ $index ];
	}

	/**
	 * 現在のカラム定義リストを取得する
	 *
	 * @return array0
	 */
	public function getAllColumns() {
		return $this->_columns;
	}

	/**
	 * 指定のカラム定義情報を指定位置に設定する。
	 * 指定位置がリストの範囲外の場合は末尾に追加される
	 *
	 * @param int $index $columnInfoを設定するインデックス位置
	 * @param OrderColumnInfo|array $columnInfo 設定するカラム定義またはOrderColumnInfoの初期化パラメータ配列
	 * @return OrderDataBuilder
	 */
	public function setColumnInfo($index, $columnInfo) {
		$index = (int)$index;

		if( ! isset( $index ) ) {
			$this->addColumnInfo( $columnInfo );
		} else {
			$this->_columns[ $index ] = $this->createColumnInfo( $columnInfo );
		}

		return $this;
	}

	/**
	 * 指定の位置にあるカラム定義情報をリストから除外する
	 *
	 * @param int $index 削除するカラム定義情報の位置または削除する定義情報
	 * @return OrderDataBuilder
	 */
	public function removeColumnInfo($index) {
		$info = ( $index instanceof OrderColumnInfo ) ? $index : $this->getColumnInfo($index);

		$result = array();
		foreach( $this->_columns as $column ) {
			if( $column != $info ) $result[] = $column;
		}

		$this->_columns = $result;

		return $this;
	}

	/**
	 * 指定のカラム定義情報をリストに追加する
	 *
	 * @param OrderColumnInfo|array $columnInfo 追加するカラム定義情報またはOrderColumnInfoの初期化パラメータ配列
	 * @return OrderDataBuilder
	 */
	public function addColumnInfo($columnInfo) {
		$this->_columns[] = $this->createColumnInfo( $columnInfo );

		return $this;
	}

	public function build() {
		$old_callback = $this->_reader->getCallback();
		$this->_reader->setCallback( array( $this, 'handleRow' ) );

		$this->_buffer = array();
		$this->_reader->read();

		$this->_reader->setCallback( $old_callback );

		return array_merge( array(), $this->_buffer );
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $row
	 * @param unknown_type $line
	 * @param unknown_type $reader
	 */
	public function handleRow($row, $line, $reader) {

	}

	/**
	 * $infoからOrderColumnInfoのインスタンスを取得する。
	 *
	 * @param OrderColumnInfo|array $info カラム定義情報またはOrderColumnInfoの初期化パラメータ配列
	 * @return OrderColumnInfo
	 */
	protected function createColumnInfo($info) {
		if( $info instanceof OrderColumnInfo  ) return $info;
		return new OrderColumnInfo( $info );
	}
}
