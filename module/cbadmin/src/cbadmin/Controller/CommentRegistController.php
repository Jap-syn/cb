<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralValidate;
use models\Table\TableUser;
use models\Table\TableCodeManagement;
use models\Table\TableCode;

class CommentRegistController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';

    /**
     * アプリケーションオブジェクト
     * @var Application
     */
    private $app;

    /**
     * Adapter
     *
     * @var Adapter
     */
    private $db;

    /**
     * クラス固有の初期化処理
     */
    protected function _init()
    {
        $this->app = Application::getInstance();
        $this->view->assign( 'userInfo', $this->app->authManagerAdmin->getUserInfo() );
        $this->db = $this->app->dbAdapter;

        $this
            ->addStyleSheet( '../css/default02.css' )
            ->addJavaScript( '../js/prototype.js' )
            ->addJavaScript( '../js/json.js' );

        $this->setPageTitle( "後払い.com - コメント管理" );
    }

    /**
     * indexアクション
     *
     */
    public function indexAction()
    {
        //パラメータ取得
        $params = $this->getParams();

        // 有効なコード識別管理マスターデータを全て取得
        $codeManagement = new TableCodeManagement( $this->db );
        $codeManagementList = ResultInterfaceToArray( $codeManagement->getAll() );

        // コードIDが選択されている場合
        if( isset( $params['codeid'] ) && $params['codeid'] > 0 ) {
            $codeId = $params['codeid'];
        }
        // コードIDが選択されていない場合はコード識別管理マスターの先頭コードIDを取得
        else {
            foreach( $codeManagementList as $value ) {
                $codeId = $value['CodeId'];
                break;
            }
        }

        // コードIDを元にコードマスターデータを取得
        $code = new TableCode( $this->db );
        $codeList = ResultInterfaceToArray( $code->getMasterByClassAll( $codeId ) );

        $this->view->assign( 'selectCodeId', $codeId );
        $this->view->assign( 'codeManagementList', $codeManagementList );
        $this->view->assign( 'codeList', $codeList );
        $this->view->assign( 'newCode', array() );

        return $this->view;
    }

    /**
     * upアクション
     *
     */
    public function upAction()
    {
        $params = $this->getParams();

        // 内容チェック
        $result = $this->validate($params);
		
        // エラーがある場合終了
        // count関数対策
        if (!empty($result)) {
            // 画面に戻すようにパラメータ整形

            // 既存データ
            $codeId = $params['codeid'];
            $code = new TableCode( $this->db );
            $codeList = ResultInterfaceToArray( $code->getMasterByClassAll( $codeId ) );
            $i = 0;
            foreach( $codeList as $value ) {
                if( isset( $params['valid_' . $i] ) && $params['valid_' . $i] == 'on') {
                    $value['ValidFlg'] = 1;
                }
                else {
                    $value['ValidFlg'] = 0;
                }
                if( isset( $params['keycontent_' . $i] ) ) {
                    $value['KeyContent'] = htmlspecialchars_decode( $params['keycontent_' . $i], ENT_QUOTES );
                }
                if( isset( $params['keycode_' . $i] ) ) {
                    $value['KeyCode'] = $params['keycode_' . $i];
                }
                if( isset( $params['class1_' . $i] ) ) {
                    $value['Class1'] = htmlspecialchars_decode( $params['class1_' . $i], ENT_QUOTES );
                }
                if( isset( $params['class2_' . $i] ) ) {
                    $value['Class2'] = htmlspecialchars_decode( $params['class2_' . $i], ENT_QUOTES );
                }
                if( isset( $params['class3_' . $i] ) ) {
                    $value['Class3'] = htmlspecialchars_decode( $params['class3_' . $i], ENT_QUOTES );
                }
                if( isset( $params['note_' . $i] ) ) {
                    $value['Note'] = htmlspecialchars_decode( $params['note_' . $i], ENT_QUOTES );
                }
                $updateData[] = $value;
                $i++;
            }
            // 新規データ
            $newData = array();
            if( isset( $params['keycontent_new'] ) && $params['keycontent_new'] != '' ) {
                $newData['KeyContent'] = htmlspecialchars_decode( $params['keycontent_new'], ENT_QUOTES );
            }
            if( isset( $params['keycode_new'] ) && $params['keycode_new'] != '' ) {
                $newData['KeyCode'] = $params['keycode_new'];
            }
            if( isset( $params['class1_new'] ) && $params['class1_new'] != '' ) {
                $newData['Class1'] = htmlspecialchars_decode( $params['class1_new'], ENT_QUOTES );
            }
            if( isset( $params['class2_new'] ) && $params['class2_new'] != '' ) {
                $newData['Class2'] = htmlspecialchars_decode( $params['class2_new'], ENT_QUOTES );
            }
            if( isset( $params['class3_new'] ) && $params['class3_new'] != '' ) {
                $newData['Class3'] = htmlspecialchars_decode( $params['class3_new'], ENT_QUOTES );
            }
            if( isset( $params['note_new'] ) && $params['note_new'] != '' ) {
                $newData['Note'] = htmlspecialchars_decode( $params['note_new'], ENT_QUOTES );
            }

            $codeManagement = new TableCodeManagement( $this->db );
            $codeManagementList = ResultInterfaceToArray( $codeManagement->getAll() );

            $this->setTemplate('index');
            $this->view->assign('selectCodeId', $codeId);
            $this->view->assign('codeManagementList', $codeManagementList);
            $this->view->assign('codeList', $updateData);
            $this->view->assign('newCode', $newData);
            $this->view->assign('errors', $result);

            return $this->view;
        }

        try
        {
            // トランザクション開始
            $this->db->getDriver()->getConnection()->beginTransaction();

            $codeId = $params['codeid'];

            // 有効なコード識別管理マスターデータを全て取得
            $codeManagement = new TableCodeManagement( $this->db );
            $codeManagementList = $codeManagement->find( $codeId )->current();

            // コードIDを元にコードマスターデータを取得
            $code = new TableCode( $this->db );
            $codeList = ResultInterfaceToArray( $code->getMasterByClassAll( $codeId ) );

            // ユーザーIDの取得
            $user = new TableUser( $this->db );
            $userId = $user->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            // 更新データの作成
            $updateData = array();
            $i = 0;
            foreach( $codeList as $value ) {
                // システム予約フラグが1の場合は更新しない
                if( $value['SystemFlg'] == 1 ) {
                    continue;
                }
                else {
                    if( isset( $params['valid_' . $i] ) && $params['valid_' . $i] == 'on') {
                        $value['ValidFlg'] = 1;
                    }
                    else {
                        $value['ValidFlg'] = 0;
                    }
                    if( isset( $params['keycontent_' . $i] ) ) {
                        $value['KeyContent'] = htmlspecialchars_decode( $params['keycontent_' . $i], ENT_QUOTES );
                    }
                    if( isset( $params['keycode_' . $i] ) ) {
                        $value['KeyCode'] = $params['keycode_' . $i];
                    }
                    if( isset( $params['class1_' . $i] ) ) {
                        $value['Class1'] = htmlspecialchars_decode( $params['class1_' . $i], ENT_QUOTES );
                    }
                    if( isset( $params['class2_' . $i] ) ) {
                        $value['Class2'] = htmlspecialchars_decode( $params['class2_' . $i], ENT_QUOTES );
                    }
                    if( isset( $params['class3_' . $i] ) ) {
                        $value['Class3'] = htmlspecialchars_decode( $params['class3_' . $i], ENT_QUOTES );
                    }
                    if( isset( $params['note_' . $i] ) ) {
                        $value['Note'] = htmlspecialchars_decode( $params['note_' . $i], ENT_QUOTES );
                    }
                    $value['UpdateId'] = $userId;
                    $updateData[] = $value;
                    $i++;
                }
            }

            foreach( $updateData as $data ) {
                $code->saveUpdate( $data, $data['CodeId'], $data['KeyCode'] );
            }

            // 新規データの作成
            $newData = array();
            if( isset( $params['keycontent_new'] ) && $params['keycontent_new'] != '' ) {
                $newData['KeyContent'] = htmlspecialchars_decode( $params['keycontent_new'], ENT_QUOTES );
            }
            if( isset( $params['keycode_new'] ) && $params['keycode_new'] != '' ) {
                $newData['KeyCode'] = $params['keycode_new'];
            }
            if( isset( $params['class1_new'] ) && $params['class1_new'] != '' ) {
                $newData['Class1'] = htmlspecialchars_decode( $params['class1_new'], ENT_QUOTES );
            }
            if( isset( $params['class2_new'] ) && $params['class2_new'] != '' ) {
                $newData['Class2'] = htmlspecialchars_decode( $params['class2_new'], ENT_QUOTES );
            }
            if( isset( $params['class3_new'] ) && $params['class3_new'] != '' ) {
                $newData['Class3'] = htmlspecialchars_decode( $params['class3_new'], ENT_QUOTES );
            }
            if( isset( $params['note_new'] ) && $params['note_new'] != '' ) {
                $newData['Note'] = htmlspecialchars_decode( $params['note_new'], ENT_QUOTES );
            }

            if( !empty( $newData ) ) {
                $newData['CodeId'] = $codeId;
                $newData['RegistId'] = $userId;
                $newData['UpdateId'] = $userId;
                $code->saveNew( $newData );
            }

            $this->db->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->db->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        return $this->_forward( 'index' );
    }

    protected function validate($data) {
        // 結果（メッセージ）
        $result = array();

        // コードID
        $codeId = $data['codeid'];

        // 既存データチェック
        $code = new TableCode( $this->db );
        $i = 0;
        while (isset($data['keycode_' . $i])) {
            $codeList = $code->find($codeId, $data['keycode_' . $i])->current();
            if(!$codeList) {
                $result[] = "更新対象が存在しません。";
                break;
            }
            $i++;
        }

        // 新規データ
        // 科目コード
        $keycode_new = $data['keycode_new'];
        if (strlen($keycode_new) > 0 && !CoralValidate::isInt($keycode_new)) {
            $result[] = "科目コードは整数で入力してください。";
        }

        return $result;
    }
}
