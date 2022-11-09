<?php
namespace Coral\Base\Extra\FFmpeg\Information;

require_once 'NetB/Extra/FFmpeg/Exception.php';

/**
 * @interface
 *
 * ffmpegの実行結果を処理するためのインターフェイスで、
 * バージョン情報や入力ファイルなどのカテゴリ単位で処理を行う。
 * インターフェイスとしては、与えられた業文字列を、
 * 自分のカテゴリの属性としての処理を試みるparseLine()のみを実装するが、
 * 実装クラスは他に自分のカテゴリの最初の行であるかを判断するための
 * canHandle()スタティックメソッドを実装する必要がある。
 */
interface BaseExtraFFmpegInformationInterface {
	/**
	 * @abstract
	 *
	 * 指定の行文字列を自分のカテゴリのプロパティとして処理を試みる。
	 * 処理が成功した場合は自身のプロパティを更新した上でtrueを返し、
	 * 処理できない場合はfalseを返す必要がある。
	 *
	 * @param string $line 処理対象の行文字列
	 * @return bool $lineの処理に成功し自分のプロパティが更新された場合はtrue、それ以外はfalse
	 */
	public function parseLine($line);

	/**
	 * @abstract
	 *
	 * 解析済みの情報を連想配列で取得する
	 *
	 * @return array
	 */
	public function toArray();
}

