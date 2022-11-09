<?php
namespace Coral\Coral\View;

use Zend\View\Model\ViewModel;

class CoralViewModel extends ViewModel
{

    /**
     * ビューへ、指定されたキーでパラメーターを渡します
     * @param string $prmKey
     * @param void $prmVal
     */
    public function assign($prmKey,$prmVal)
    {
        $this->setVariable($prmKey, $prmVal);
    }
}