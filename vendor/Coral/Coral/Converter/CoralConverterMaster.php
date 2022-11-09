<?php
namespace Coral\Coral\Converter;

use Coral\Coral\CoralCodeMaster;
use cbadmin\Application;

/**
 * {@link CoralCodeMaster}による区分値展開を行う{@link CoralConverterInterface}実装クラス
 */
class CoralConverterMaster implements CoralConverterInterface {
	/**
	 * マスター名称を指定して、{@link CoralConverterMaster}の新しい
	 * インスタンスを生成する。
	 * ここで指定されたマスター名称は、{@link CoralCodeMaster}のキャプション取得メソッドに
	 * 関連付けられる。ex. マスター名：FixPattern → getFixPatternCaption()
	 * @static
	 * @param string $masterName マスター名称
	 * @return CoralConverterInterface このクラスの新しいインスタンス
	 */
	public static function create($masterName) {
		return new self($masterName);
	}

	/**
	 * マスター解決に使用する{@link CoralCodeMaster}
	 * @var CoralCodeMaster
	 */
	protected $codeMaster;

	/**
	 * マスター名称
	 * @var string
	 */
	protected $masterName;

	/**
	 * マスター名称を指定して、{@link CoralConverterMaster}の新しい
	 * インスタンスを初期化する
	 * ここで指定されたマスター名称は、{@link CoralCodeMaster}のキャプション取得メソッドに
	 * 関連付けられる。ex. マスター名：FixPattern → getFixPatternCaption()
	 * @param string $masterName マスター名称
	 */
	public function __construct($masterName) {
		$this->codeMaster = new CoralCodeMaster(Application::getInstance()->dbAdapter);
		$this->masterName = $masterName;
	}

	/**
	 * 入力値を変換します。
	 * このクラスでは、{@link CoralCodeMaster}を使用して区分値をキャプションに展開します
	 * @param mixed $value 入力値
	 * @return string $valueを変換した文字列
	 */
	public function convert($value) {
		$value = (int)$value;
		$method = 'get' . $this->masterName . 'Caption';
//		return call_user_method( $method, $this->codeMaster, $value );
        return call_user_func( array($this->codeMaster, $method), $value );
	}
}
