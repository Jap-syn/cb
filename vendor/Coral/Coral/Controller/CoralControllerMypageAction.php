<?php
namespace Coral\Coral\Controller;

use Coral\Coral\Controller\CoralControllerAction;

/**
 * mypage、orderpageでマイページ向けの継承クラス
 */
abstract class CoralControllerMypageAction extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';


	protected $app;
	protected $_moduleName;

    protected function isOemAccess(){
        return $this->app->isOemActive();
    }

    /**
     * Layoutに指定されたスタイルシートを追加します。
     * @param string $prmStyleSheet
     */
    protected function addStyleSheet($prmStyleSheet = null)
    {
        if ($prmStyleSheet == null)
        {
            return;
        }

        if ($this->isOemAccess()) {
            $prmStyleSheet = '../../' . $this->_moduleName . '/' . $prmStyleSheet;
        }
        return parent::addStyleSheet($prmStyleSheet);
    }

}
