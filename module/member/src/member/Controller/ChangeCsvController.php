<?php
namespace member\Controller;

use member\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableTemplateField;

class ChangeCsvController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    protected function _init() {
        $this->app = Application::getInstance();
    }

    /**
     * CSV設定変更を行う
    */
    public function indexAction() {
        $db = $this->app->dbAdapter;

        $params = $this->params()->fromPost();

        $templateSeq = $params['templateseq'];
        $validList = explode( ',', $params['validlistData']);
        $invalidList = explode( ',', $params['invalidlistData']);
        $userId = $params['userid'];

        // ListNumber順にTemplateFieldを取り出す
        $templateField = new TableTemplateField( $db );
        $oldTemplateFieldList = ResultInterfaceToArray( $templateField->get( $templateSeq ) );

        $newTemplateField = array();
        $newTemplateFieldList = array();
        $tempTemplateField = array();
        $creditFlg = false;


        $validItems = count( $validList ) - 1;
        // validListのアイテム詰め直し
        if (!empty($validList)) {
            for( $i = 1; $i <= $validItems; $i++ ) {
                foreach( $oldTemplateFieldList as $oldTemplateField ) {
                    // クレジット決済対応
                    /*if (($oldTemplateField['PhysicalName'] == 'ExtraPayKey' || $oldTemplateField['PhysicalName'] == 'CreditLimitDate') && !$creditFlg) {
                        $tempTemplateField = $oldTemplateField;
                        $creditFlg = true;
                    }*/
                    if( $validList[$i] == $oldTemplateField['PhysicalName'] ) {
                        $newTemplateField = $oldTemplateField;
                        $newTemplateField['ListNumber'] = $i;
                        $newTemplateField['UpdateId'] = $userId;
                        $newTemplateField['ValidFlg'] = 1;
                        $newTemplateFieldList[] = $newTemplateField;
                    }
                }
            }
        } else {
            $validItems = -1;
        }

        // invalidListのアイテム詰め直し
        $invalidItems = count( $invalidList ) - 1;
        if (!empty($invalidList)) {
            for( $i = 1; $i <= $invalidItems; $i++ ) {
                foreach( $oldTemplateFieldList as $oldTemplateField ) {
                    if( $invalidList[$i] == $oldTemplateField['PhysicalName'] ) {
                        $newTemplateField = $oldTemplateField;
                        $newTemplateField['ListNumber'] = $i + $validItems;
                        $newTemplateField['UpdateId'] = $userId;
                        $newTemplateField['ValidFlg'] = 0;
                        $newTemplateFieldList[] = $newTemplateField;
                    }
                }
            }
        }
        $listItemPost = array();
        foreach ($newTemplateFieldList as $item) {
            $listItemPost[] = $item['PhysicalName'];
        }

        $listItemInDbNotPost = array();
        foreach ($oldTemplateFieldList as $item) {
            if (!in_array($item['PhysicalName'], $listItemPost)) {
                $listItemInDbNotPost[] = $item['PhysicalName'];
            }
        }
        // クレジット決済対応
        $countOld = count($newTemplateFieldList);
         if ($countOld != count($oldTemplateFieldList)){
             $count = count($listItemInDbNotPost);
             for ($i = 0; $i < $count; $i++) {
                 foreach( $oldTemplateFieldList as $oldTemplateField ) {
                     if( $listItemInDbNotPost[$i] == $oldTemplateField['PhysicalName'] ) {
                         $newTemplateField = $oldTemplateField;
                         $newTemplateField['ListNumber'] = $i + $countOld + 1;
                         $newTemplateField['UpdateId'] = $userId;
                         $newTemplateField['ValidFlg'] = 0;
                         $newTemplateFieldList[] = $newTemplateField;
                     }
                 }
             }
         }
        
        try
        {
            // トランザクション開始
            $db->getDriver()->getConnection()->beginTransaction();

            foreach( $newTemplateFieldList as $newTemplateField ) {
                $templateField->saveUpdate( $newTemplateField, $templateSeq, $newTemplateField['ListNumber'] );
            }
            $db->getDriver()->getConnection()->commit();
        }
        catch( \Exception $e )
        {
            $db->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        $redirect = explode( '/', $params['redirect'] );
        $route = 'member/default/wildcard';

        if(!empty($redirect)) {
            for( $i = 0; $i < count( $redirect ); $i++ ){
                if( $i == 0 ) {
                    // --------------------
                    // コントローラ
                    // --------------------
                    $route_array['controller'] = $redirect[$i];
                }
                elseif( $i == 1 ) {
                    // --------------------
                    // アクション
                    // --------------------
                    $route_array['action'] = $redirect[$i];
                }
                else {
                    // --------------------
                    // パラメーター
                    // --------------------
                    if ( $i % 2 == 0 ) {
                        // キー
                        $key = $redirect[$i];
                    }
                    else {
                        // 値
                        $route_array[$key] = $redirect[$i];
                    }
                }
            }
        }

        return $this->redirect()->toRoute( $route, $route_array );
    }
}
