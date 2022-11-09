<?php
namespace models\Logic\BarcodeData;
/**
 * バーコードデータ生成インターフェイス
 */
interface LogicBarcodeDataInterface {
	/**
	 * バーコードデータを生成する
	 * @return string
	 */
	public function generate();

	/**
	 * 表示用バーコードデータ文字列を生成する
	 * @return string
	 */
	public function generateString();

}

