<?php
namespace Coral\Base\Shell;

require_once 'NetB/Shell/Command/Builder.php';

class BaseShellCommand {
	/**
	 * @static
	 *
	 * NetB_Shell_Command_Builderのインスタンスを新規に作成するファクトリメソッド。
	 * このスタティックメソッドは単にコンストラクタへのエイリアスだが、
	 * これでインスタンスを生成するとすぐにメソッドチェーンによるパラメータ構築を
	 * 行うことができる。
	 *
	 * @param string $command 実行するコマンドラインのコマンド
	 * @param null|array $options オプション引数
	 * @return NetB_Shell_Command_Builder
	*/
	public static function create($command, $options = array()) {
	   return new NetB_Shell_Command_Builder( $command, $options );
	}


}

