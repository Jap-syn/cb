<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use models\Table\TableUser;
use models\Table\TableSystemProperty;

class SystemConditionController extends CoralControllerAction {
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

        $this->setPageTitle( "後払い.com - システム条件" );

        // システムプロパティデータを全て取得
        $sql = ' SELECT * FROM T_SystemProperty ORDER BY Module, Category, PropId ';
        $systemproperties = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( null ) );

        $this->view->assign( 'systemproperties', $systemproperties );
    }

    /**
     * indexアクション
     *
     */
    public function indexAction()
    {
        // システムプロパティデータを全て取得
        $sql = ' SELECT * FROM T_SystemProperty ORDER BY Module, Category, PropId ';
        $systemproperties = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( null ) );

        $this->view->assign( 'systemproperties', $systemproperties );

        return $this->view;
    }

    /**
     * upアクション
     *
     */
    public function upAction()
    {
        $params = $this->params()->fromPost();

        // リロードされたらindexへリダイレクト
        if( empty( $params ) ) {
            return $this->_redirect( 'systemcondition/index' );
        }

        $prms = array();
        $errors = array();
        $errcnt = 0;

        // propid / valid / module / category / name / propvalue / description
        foreach( $params as $key => $param ) {
            $prm = array();
            $error = array();

            if( strstr( $key , '_new' ) || strstr( $key, 'propid_' ) || strstr( $key, 'valid_' ) || strstr( $key, 'propvalue_') || strstr( $key, 'description_') ) {
                continue;
            }
            if( strstr( $key, 'module_' ) ) {
                $i = substr( $key, 7 );
                if( empty( $param ) ) {
                    $errors[$i]['Module_' . $i] = 'モジュールは必須です。';
                    $errcnt++;
                }
                else {
                    $prms[$i]['Module'] = $param;
                }
            }
            else if( strstr( $key, 'category_' ) ) {
                $i = substr(  $key, 9 );
                if( empty( $param ) ) {
                    $errors[$i]['Category_' . $i] = 'カテゴリーは必須です。';
                    $errcnt++;
                }
                else {
                    $prms[$i]['Category'] = $param;
                }
            }
            else if( strstr( $key, 'name_' ) ) {
                $i = substr( $key, 5 );
                if( empty( $param ) ) {
                    $errors[$i]['Name_' . $i] = 'プロパティ名は必須です。';
                    $errcnt++;
                }
                else {
                    $prms[$i]['Name'] = $param;
                }
            }
            $cnt = $i;
        }

        $caches = array();
        for( $i = 0; $i <= $cnt; $i++ ) {
            if( empty( $errors[$i] ) ) {
                // モジュール、カテゴリー、プロパティ名が同一のデータが既に存在したらエラー
                foreach( $caches as $cache ) {
                    if( $cache['Module'] == $prms[$i]['Module'] && $cache['Category'] == $prms[$i]['Category'] && $cache['Name'] == $prms[$i]['Name'] ) {
                        $errors[$i]['Name_' . $i] = '同一プロパティーが既に存在します。';
                        $errcnt++;
                    }
                }
                if( empty( $errors[$i]['Name_' . $i] ) ) {
                    $val['Module'] = $prms[$i]['Module'];
                    $val['Category'] = $prms[$i]['Category'];
                    $val['Name'] = $prms[$i]['Name'];
                    $caches[] = $val;
                }
            }
        }

        if( $errcnt ) {
            for( $i = 0; $i <= $cnt; $i++ ) {
                $systemproperties[$i]['PropId'] = $params['propid_' . $i];
                $systemproperties[$i]['ValidFlg'] = isset( $params['valid_' . $i] ) ? 1 : 0;
                $systemproperties[$i]['Module'] = $params['module_' . $i];
                $systemproperties[$i]['Category'] = $params['category_' . $i];
                $systemproperties[$i]['Name'] = $params['name_' . $i];
                $systemproperties[$i]['PropValue'] = $params['propvalue_' . $i];
                $systemproperties[$i]['Description'] = $params['description_' . $i];
            }

            $this->view->assign( 'systemproperties', $systemproperties );
            $this->view->assign( 'error', $errors );

            $this->setTemplate( 'index' );
            return $this->view;
        }

        try
        {
            // トランザクション開始
            $this->db->getDriver()->getConnection()->beginTransaction();

            // ユーザーIDの取得
            $user = new TableUser( $this->db );
            $userId = $user->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            // システムプロパティデータを全て取得
            $sql = ' SELECT * FROM T_SystemProperty ORDER BY Module, Category, PropId ';
            $systemproperties = ResultInterfaceToArray( $this->app->dbAdapter->query( $sql )->execute( null ) );

            // 更新データの作成
            $updateData = array();
            $i = 0;
            foreach( $systemproperties as $value ) {
                if( isset( $params['propid_' . $i] ) ) {
                    $value['PropId'] = htmlspecialchars_decode( $params['propid_' . $i], ENT_QUOTES );
                }
                if( isset( $params['valid_' . $i] ) ) {
                    $value['ValidFlg'] = 1;
                }
                else {
                    $value['ValidFlg'] = 0;
                }
                if( isset( $params['module_' . $i] ) ) {
                    $value['Module'] = htmlspecialchars_decode( $params['module_' . $i], ENT_QUOTES );
                }
                if( isset( $params['category_' . $i] ) ) {
                    $value['Category'] = htmlspecialchars_decode( $params['category_' . $i], ENT_QUOTES );
                }
                if( isset( $params['name_' . $i] ) ) {
                    $value['Name'] = htmlspecialchars_decode( $params['name_' . $i], ENT_QUOTES );
                }
                if( isset( $params['propvalue_' . $i] ) ) {
                    $value['PropValue'] = htmlspecialchars_decode( $params['propvalue_' . $i], ENT_QUOTES );
                }
                if( isset( $params['description_' . $i] ) ) {
                    $value['Description'] = htmlspecialchars_decode( $params['description_' . $i], ENT_QUOTES );
                }
                $value['UpdateId'] = $userId;
                $updateData[] = $value;
                $i++;
            }

            $mdlsp = new TableSystemProperty( $this->db );

            foreach( $updateData as $data ) {
                $mdlsp->saveUpdate( $data, $data['PropId'] );
            }

            $this->db->getDriver()->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $this->db->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        return $this->_redirect( 'systemcondition/index' );
    }

    /**
     * newアクション
     *
     */
    public function newAction()
    {
        $params = $this->params()->fromPost();

        // リロードされたらindexへリダイレクト
        if( empty( $params ) ) {
            return $this->_redirect( 'systemcondition/index' );
        }

        $errors = array();
        if( empty( $params['module_new'] ) ) {
            $errors['module_new'] = 'モジュールは必須です。';
        }
        if( empty( $params['category_new'] ) ) {
            $errors['category_new'] = 'カテゴリーは必須です。';
        }
        if( empty( $params['name_new'] ) ) {
            $errors['name_new'] = 'プロパティ名は必須です。';
        }
        // モジュール、カテゴリー、プロパティ名同一の有効フラグONデータが既に存在したらエラー
        $sql = ' SELECT * FROM T_SystemProperty WHERE Module = :Module AND Category = :Category AND Name = :Name ';
        $prm = array(
            ':Module' => $params['module_new'],
            ':Category' => $params['category_new'],
            ':Name' => $params['name_new'],
        );
        if( count( $this->db->query( $sql )->execute( $prm ) ) ) {
            $errors['name_new'] = '同一プロパティーが既に存在します。';
        }

        if( !empty( $errors ) ) {
            $this->view->assign( 'isNew', true );
            $new['Module_New'] = $params['module_new'];
            $new['Category_New'] = $params['category_new'];
            $new['Name_New'] = $params['name_new'];
            $new['PropValue_New'] = $params['propvalue_new'];
            $new['Description_New'] = $params['description_new'];

            $this->view->assign( 'new', $new );

            $this->view->assign( 'error', $errors );

            $this->setTemplate( 'index' );
            return $this->view;
        }

        try
        {
            // トランザクション開始
            $this->db->getDriver()->getConnection()->beginTransaction();

            // ユーザーIDの取得
            $user = new TableUser( $this->db );
            $userId = $user->getUserId( 0, $this->app->authManagerAdmin->getUserInfo()->OpId );

            // 新規データの作成
            $data['Module'] = $params['module_new'];
            $data['Category'] = $params['category_new'];
            $data['Name'] = $params['name_new'];
            $data['PropValue'] = $params['propvalue_new'];
            $data['Description'] = $params['description_new'];
            $data['RegistId'] = $userId;
            $data['UpdateId'] = $userId;

            $mdlsp = new TableSystemProperty( $this->db );
            $mdlsp->saveNew( $data );

            $this->db->getDriver()->getConnection()->commit();
        }
        catch( \Exception $e )
        {
            $this->db->getDriver()->getConnection()->rollBack();
            throw $e;
        }

        return $this->_redirect( 'systemcondition/index' );
    }
}
