<?php
namespace cbadmin\Controller;

use cbadmin\Application;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\Validate\CoralValidateRequired;
use models\Logic\LogicOemClaimAccount;
use models\Logic\Validation;;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\InArray;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;
use Zend\Zend\Json;
use Zend\Db\Adapter\Driver\ResultInterface;

/**
 * コンビニ収納代行会社設定を管理するコントローラ
 */
class ClaimAccountController extends CoralControllerAction {
    protected $_componentRoot = './application/views/components';
    /**
     * アプリケーションインスタンス
     *
     * @access protected
     * @var Application
     */
    protected $app;

    /**
     * 有効なバーコードロジック名とロジッククラスを対応付ける連想配列
     *
     * @access protected
     * @var array
     */
    protected $bcClassMap;

    /**
     * 請求口座ロジック
     *
     * @access protected
     * @var LogicOemClaimAccount
     */
    protected $accountsLogic;

    /**
     * DBアダプタ
     *
     * @access protected
     * @var Adapter
     */
    protected $db;

    /**
     * コントローラ初期化
     */
    protected function _init() {
        $app = $this->app = Application::getInstance();
        $db = $this->db = $this->app->dbAdapter;
        $this->accountsLogic = new LogicOemClaimAccount($db);
        $this->accountsLogic->setLogger($this->app->logger);

        $this->bcClassMap = LogicOemClaimAccount::getBarcodeLogicClasses();

        $this
            ->addStyleSheet('../css/default02.css')

            ->addJavaScript('../js/prototype.js')
            ->addJavaScript('../js/json+.js')
            ->addJavaScript('../js/corelib.js')

            ->setPageTitle('後払い.com - 請求口座情報');

//            ->view
//                ->assign('current_action', $this->getCurrentAction())
            $this->view->assign('current_action', $this->getActionName());
            $this->view->assign('barcodeClasses', $this->bcClassMap);

            $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());
            $this->view->assign('master_map', LogicOemClaimAccount::getCodeMap());
//            $this->view->assign('agents', $this->accountsLogic->getReceiptAgentMaster()->fetchAllAgents());
            $agents = $this->accountsLogic->getReceiptAgentMaster()->fetchAllAgents();
            $rs = new ResultSet();
            $agentsarray = $rs->initialize($agents)->toArray();
            $this->view->assign('agents', $agentsarray);
    }

    /**
     * indexAction
     * listActionのエイリアス
     */
    public function indexAction() {
        return $this->_redirect('claimaccount/list');
    }

    /**
     * listAction
     * コンビニ収納代行会社一覧を表示する
     */
    public function listAction() {
        $hisCount = $this->accountsLogic->getClaimReservedCounts();
        $list = array();
        foreach($this->accountsLogic->fetchAllClaimAccounts() as $row) {
            $fixedRow = array_merge($row, array('ReservedCount' => $hisCount[$row['OemId']]));
            $list[] = $fixedRow;
        }
        $this->view->assign('list', $list);
        //$this->view->assign('list', $this->accountsLogic->fetchAllClaimAccounts());
        $this->view->assign('current_action', $this->getActionName());

        return $this->view;
    }

    /**
     * detailAction
     * 指定OEM先の請求口座情報詳細を表示
     */
    public function detailAction() {
        //        $oid = (int)($this->getRequest()->getParam('oid', -1));
        $oid = (int)($this->params()->fromRoute('oid', -1));
        $data = $this->accountsLogic->findClaimAccountsByOemId($oid)->current();
        $cnt = $this->accountsLogic->getClaimReservedCountByOemId($oid);
        $this->view->assign('oid', $oid);
        $this->view->assign('oname', $this->getOemName($oid));
        $this->view->assign('hisCount', $cnt);
        $this->view->assign('data', $data);
        if(!$data) {
            throw new \Exception(sprintf("OEM-ID '%s' は不正な指定です", $this->params()->fromRoute('oid')));
        }

        return $this->view;
    }

    /**
     * editAction
     * 指定OEM先の請求口座情報詳細を編集
     */
    public function editAction() {

        $serialized = $this->params()->fromPost('hashed');

        $data = false;
        if($serialized) {
            // 確認画面から戻ってきた場合
            $data = @unserialize(base64_decode($serialized));
            if($data) {
                $oid = (int)($data['OemId']);
            }
        }
        // データが確定していない場合は指定されたOEM-IDでデータを取り寄せる
        if(!$data) {
            $oid = (int)($this->params()->fromRoute('oid', -1));
            $data = $this->accountsLogic->findClaimAccountsByOemId($oid);
        }
        if(!$data) {
            return $this->_redirect('error/nop');
        }
        if ($data instanceof ResultInterface ) {
            $data = $data->current();
        }

        $cnt = $this->accountsLogic->getClaimReservedCountByOemId($oid);
        $this->view->assign('oid', $oid);
        $this->view->assign('oname', $this->getOemName($oid));
        $this->view->assign('hisCount', $cnt);
        $this->view->assign('data', $data);
        $this->view->assign('current_action', $this->getActionName());
        $this->setTemplate('form');

        return $this->view;
    }

    /**
     * 指定OEM先のOEM先名を取得する
     *
     * @access protected
     * @var int $oid OEM ID
     * @return string
     */
    protected function getOemName($oid) {
        $data = $this->accountsLogic->findClaimAccountsByOemId($oid)->current();
        return $data ? $data['NameKj'] : 'undefined';
    }

    /**
     * confirmAction
     * 請求口座情報登録の確認画面を表示
     */
    public function confirmAction() {
//        $data = $this->getRequest()->getParam('form', array());
        $data = $this->params()->fromPost('form', array());
        $oid = (int)(nvl($data['OemId'], -1));
        if($oid < 0) {
//            throw new \Exception(sprintf("OEM-ID '%s' は不正な指定です", $this->getRequest()->getParam('oid')));
            throw new \Exception(sprintf("OEM-ID '%s' は不正な指定です", $this->params()->fromRoute('oid')));
        }
        $data = $this->fixFormValues($data);
        $errors = $this->validate($data);
        $cnt = $this->accountsLogic->getClaimReservedCountByOemId($oid);
        $this->view->assign('oid', $oid);
        $this->view->assign('oname', $this->getOemName($oid));
        $this->view->assign('data', $data);
        $this->view->assign('hisCount', $cnt);
        $this->view->assign('error', $errors);
        $this->view->assign('hashed', base64_encode(serialize($data)));
//        $this->_helper->viewRenderer(count($errors) ? 'form' : 'confirm');
        // count関数対策
        $this->setTemplate(!empty($errors) ? 'form' : 'confirm');

        return $this->view;
    }

    /**
     * saveAction
     * 請求口座情報を永続化する
     */
    public function saveAction() {
//        $req = $this->getRequest();

//        $serialized = $req->getParam('hashed');
        $serialized = $this->params()->fromPost('hashed');
        $data = false;
        if($serialized) {
            // 確認画面から戻ってきた場合
            $data = @unserialize(base64_decode($serialized));
            if($data) {
                $oid = (int)($data['OemId']);
            }
        }
        if(!$data) {
            throw new \Exception('不正なデータが送信されました。作業をやり直してください');
        }

        // 永続化実行 → 例外は上位でハンドル
        $this->accountsLogic->saveClaimAccounts($oid, $data);

        // 正常終了したので完了画面へリダイレクト
        return $this->_redirect('claimaccount/complete');
    }

    /**
     * completeAction
     * 登録完了画面を表示
     */
    public function completeAction() {
        return $this->view;
    }

    /**
     * 登録フォームの初期値を補完する
     *
     * @access protected
     * @param array $data
     * @return array
     */
    protected function fixFormValues(array $data) {
        $data = array_merge(
            array(
                'Bk_ServiceKind' => null,
                'Bk_DepositClass' => null,
                'Yu_ChargeClass' => null,
                'Cv_ReceiptAgentId' => null,
                'Smbc_YuusousakiKbn' => null,
                'Smbc_Yu_ChargeClass' => null
            ), $data);

        // 決済ステーションAPIバージョンはレターケースを大文字に調整
        if(isset($data['Smbc_ApiVersion'])) {
            $data['Smbc_ApiVersion'] = mb_strtoupper($data['Smbc_ApiVersion']);
        }
        return $data;
    }

    /**
     * 入力検証処理
     *
     * @access protected
     * @param array $data
     * @return array
     */
    protected function validate($data = array()) {
        $maps = LogicOemClaimAccount::getCodeMap();
        $account_ids = array();
        foreach($this->accountsLogic->getReceiptAgentMaster()->fetchAllAgents() as $agent_row) {
            $account_ids[] = $agent_row['ReceiptAgentId'];
        }

        $validators = array(
            // Bk_ServiceKind: 銀行口座 - 口座サービス区分
            'Bk_ServiceKind' => array(
                'name' => 'Bk_ServiceKind',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'銀行口座 - 口座サービス区分'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'InArray',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'haystack' => array_keys($maps[LogicOemClaimAccount::MAPKEY_BK_SERVICE_KIND]),
                            'messages' => array(
                                InArray::NOT_IN_ARRAY => "'銀行口座 - 口座サービス区分'に未定義の値が指定されました"
                            )
                        )
                    )
                )
            ),
            // Bk_BankName: 銀行口座 - 銀行名
            'Bk_BankName' => array(
                'name' => 'Bk_BankName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'銀行口座 - 銀行名'は必須です"
                            )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'銀行口座 - 銀行名'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'銀行口座 - 銀行名'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Bk_BankCode: 銀行口座 - 銀行コード
            'Bk_BankCode' => array(
                'name' => 'Bk_BankCode',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                 NotEmpty::IS_EMPTY => "'銀行口座 - 銀行コード'は必須です"
                            )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 4,
                            'max' => 4,
                            'messages' => array(
                                StringLength::TOO_LONG => "'銀行口座 - 銀行コード'は半角数字4文字で入力してください",
                                StringLength::TOO_SHORT => "'銀行口座 - 銀行コード'は半角数字4文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{4}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'銀行口座 - 銀行コード'は半角数字4文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Bk_BranchName: 銀行口座 - 支店名
            'Bk_BranchName' => array(
                'name' => 'Bk_BranchName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                        'messages' => array(
                            NotEmpty::IS_EMPTY => "'銀行口座 - 支店名'は必須です"
                            )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'銀行口座 - 支店名'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'銀行口座 - 支店名'が短すぎます"
                            )
                        )
                    )
                )
           ),
            // Bk_BranchCode: 銀行口座 - 支店コード
            'Bk_BranchCode' => array(
                'name' => 'Bk_BranchCode',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'銀行口座 - 支店コード'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 3,
                            'max' => 3,
                            'messages' => array(
                                StringLength::TOO_LONG => "'銀行口座 - 支店コード'は半角数字3文字で入力してください",
                                StringLength::TOO_SHORT => "'銀行口座 - 支店コード'は半角数字3文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{3}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'銀行口座 - 支店コード'は半角数字3文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Bk_DepositClass: 銀行口座 - 口座種別
            'Bk_DepositClass' => array(
                'name' => 'Bk_DepositClass',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'銀行口座 - 口座種別'は必須です"
                            )
                        )
                    ),
                    array(
                        'name' => 'InArray',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'haystack' => array_keys($maps[LogicOemClaimAccount::MAPKEY_BK_DEPOSIT_CLASS]),
                            'messages' => array(
                                InArray::NOT_IN_ARRAY => "'銀行口座 - 口座種別'に未定義の値が指定されました"
                            )
                        )
                    )
                )
            ),
            // Bk_AccountNumber: 銀行口座 - 口座番号
            'Bk_AccountNumber' => array(
                'name' => 'Bk_AccountNumber',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'銀行口座 - 口座番号'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 20,
                            'messages' => array(
                                StringLength::TOO_LONG => "'銀行口座 - 口座番号'は20文字以内で入力してください",
                                StringLength::TOO_SHORT => "'銀行口座 - 口座番号'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{1,20}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'銀行口座 - 口座番号'は半角数字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Bk_AccountHolder: 銀行口座 - 口座名義
            'Bk_AccountHolder' => array(
                'name' => 'Bk_AccountHolder',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                 'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'銀行口座 - 口座名義'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'銀行口座 - 口座名義'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'銀行口座 - 口座名義'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Bk_AccountHolderKn: 銀行口座 - 口座名義カナ
            'Bk_AccountHolderKn' => array(
                'name' => 'Bk_AccountHolderKn',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'銀行口座 - 口座名義カナ'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'銀行口座 - 口座名義カナ'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'銀行口座 - 口座名義カナ'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Yu_SubscriberName: ゆうちょ口座 - 加入者名
            'Yu_SubscriberName' => array(
                'name' => 'Yu_SubscriberName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                 'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'ゆうちょ口座 - 加入者名'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'ゆうちょ口座 - 加入者名'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'ゆうちょ口座 - 加入者名'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Yu_AccountNumber: ゆうちょ口座 - 口座番号
            'Yu_AccountNumber' => array(
                'name' => 'Yu_AccountNumber',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                 'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'ゆうちょ口座 - 口座番号'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 12,
                            'max' => 12,
                            'messages' => array(
                                StringLength::TOO_LONG => "'ゆうちょ口座 - 口座番号'は半角数字12文字で入力してください",
                                StringLength::TOO_SHORT => "'ゆうちょ口座 - 口座番号'は半角数字12文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{12}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'ゆうちょ口座 - 口座番号'は半角数字12文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Yu_ChargeClass: ゆうちょ口座 - 払込負担区分
            'Yu_ChargeClass' => array(
                'name' => 'Yu_ChargeClass',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'ゆうちょ口座 - 払込負担区分'は必須です"
                            )
                        )
                    ),
                    array(
                        'name' => 'InArray',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'haystack' => array_keys($maps[LogicOemClaimAccount::MAPKEY_YU_CHARGE_CLASS]),
                            'messages' =>array(
                                InArray::NOT_IN_ARRAY => "'ゆうちょ口座 - 払込負担区分'に未定義の値が指定されました"
                            )
                        )
                    )
                )
            ),
            // Yu_SubscriberData: ゆうちょ口座 - 加入者固有データ
            'Yu_SubscriberData' => array(
                'name' => 'Yu_SubscriberData',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                'isEmpty' => "'ゆうちょ口座 - 加入者固有データ'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 9,
                            'messages' => array(
                                StringLength::TOO_LONG => "'ゆうちょ口座 - 加入者固有データ'は9文字以内で入力してください",
                                StringLength::TOO_SHORT => "'ゆうちょ口座 - 加入者固有データ'は9文字以内で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{1,9}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'ゆうちょ口座 - 加入者固有データ'は半角数字9文字以内で入力してください"
                            )
                        )
                    )
                )
            ),
            // Cv_ReceiptAgentId: コンビニ収納代行 - 収納代行会社
            'Cv_ReceiptAgentId' => array(
                'name' => 'Cv_ReceiptAgentId',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'コンビニ収納代行 - 収納代行会社'は必須です"
                            )
                        )
                    ),
                    array(
                        'name' => 'InArray',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'haystack' => $account_ids,
                            'messages' => array(
                                InArray::NOT_IN_ARRAY => "'コンビニ収納代行 - 収納代行会社'に未定義の値が指定されました"
                            )
                        )
                    )
                )
            ),
            // Cv_SubscriberCode: コンビニ収納代行 - 加入者固有コード
            'Cv_SubscriberCode' => array(
                'name' => 'Cv_SubscriberCode',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                 'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'コンビニ収納代行 - 加入者固有コード'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 10,
                            'messages' => array(
                                StringLength::TOO_LONG => "'コンビニ収納代行 - 加入者固有コード'は10文字以内で入力してください",
                                StringLength::TOO_SHORT => "'コンビニ収納代行 - 加入者固有コード'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{1,10}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'コンビニ収納代行 - 加入者固有コード'は半角数字10文字以内で入力してください"
                            )
                        )
                    )
                )
            ),
            // Cv_SubscriberName: コンビニ収納代行 - 加入者名
            'Cv_SubscriberName' => array(
                'name' => 'Cv_SubscriberName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'コンビニ収納代行 - 加入者名'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'コンビニ収納代行 - 加入者名'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'コンビニ収納代行 - 加入者名'が短すぎます"
                            )
                        )
                    )
                )
            )
        );

        // 口座サービス区分がSMBC決済ステーションの場合は決済ステーションアカウント用設定を追加する
        if($data['Bk_ServiceKind'] == LogicOemClaimAccount::SERVICE_KIND_SMBC) {
            $validators = $this->fixValidatorConfigForSmbc($validators, $data);
        }

        $factory = new Factory();
        $inputfilter = $factory->createInputFilter($validators);
        $inputfilter->setData($data);

        $errors = array();
        if(!$inputfilter->isValid()) {
            $errors = $inputfilter->getMessages();
        }

        return $errors;
    }

    /**
     * 入力検証ルールにSMBC決済ステーションアカウント向けのルールをマージする
     *
     * @access protected
     * @param array $validators 入力検証基本ルール
     * @param array $data 入力データ
     * @return array
     */
    protected function fixValidatorConfigForSmbc(array $validators, array $data) {
        $maps = LogicOemClaimAccount::getCodeMap();
        $smbc_validators = array(
            // Smbc_DisplayName: SMBC決済ステーション - 連携アカウント名
            'Smbc_DisplayName' => array(
                'name' => 'Smbc_DisplayName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 連携アカウント名'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 100,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 連携アカウント名'は100文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 連携アカウント名'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Smbc_ApiVersion: SMBC決済ステーション - APIバージョン
            'Smbc_ApiVersion' => array(
                'name' => 'Smbc_ApiVersion',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - APIバージョン'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 3,
                            'max' => 3,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - APIバージョン'は半角英数字3文字で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - APIバージョン'は半角英数字3文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{3}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - APIバージョン'は半角英数字3文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Smbc_BillMethod: SMBC決済ステーション - 決済手段区分
            'Smbc_BillMethod' => array(
                'name' => 'Smbc_BillMethod',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 決済手段区分'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 2,
                            'max' => 2,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 決済手段区分'は半角数字2文字で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 決済手段区分'は半角数字2文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{2}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 決済手段区分'は半角数字2文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Smbc_KessaiId: SMBC決済ステーション - 決済種別コード
            'Smbc_KessaiId' => array(
                'name' => 'Smbc_KessaiId',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 決済種別コード'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 4,
                            'max' => 4,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 決済種別コード'は半角数字4文字で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 決済種別コード'は半角数字4文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{4}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 決済種別コード'は半角数字4文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Smbc_ShopCd: SMBC決済ステーション - 契約コード
            'Smbc_ShopCd' => array(
                'name' => 'Smbc_ShopCd',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 契約コード'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 7,
                            'max' => 7,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 契約コード'は半角英数字7文字で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 契約コード'は半角英数字7文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{7}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 契約コード'は半角英数字7文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Smbc_SyunoCoCd1: SMBC決済ステーション - 収納企業コード1（圧着用）
            'Smbc_SyunoCoCd1' => array(
                'name' => 'Smbc_SyunoCoCd1',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 収納企業コード1（圧着用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 8,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 収納企業コード1（圧着用）'は半角英数字8文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 収納企業コード1（圧着用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,8}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 収納企業コード1（圧着用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_SyunoCoCd2: SMBC決済ステーション - 収納企業コード2（封書用）
            'Smbc_SyunoCoCd2' => array(
                'name' => 'Smbc_SyunoCoCd2',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 収納企業コード2（封書用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 8,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 収納企業コード2（封書用）'は半角英数字8文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 収納企業コード2（封書用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,8}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 収納企業コード2（封書用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_SyunoCoCd3: SMBC決済ステーション - 収納企業コード3（同梱用）
            'Smbc_SyunoCoCd3' => array(
                'name' => 'Smbc_SyunoCoCd3',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 収納企業コード3（同梱用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 8,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 収納企業コード3（同梱用）'は半角英数字8文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 収納企業コード3（同梱用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,8}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 収納企業コード3（同梱用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_SyunoCoCd4: SMBC決済ステーション - 収納企業コード4（LINE Pay圧着用）
            'Smbc_SyunoCoCd4' => array(
                'name' => 'Smbc_SyunoCoCd4',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 収納企業コード4（LINE Pay圧着用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 8,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 収納企業コード4（LINE Pay圧着用）'は半角英数字8文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 収納企業コード4（LINE Pay圧着用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,8}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 収納企業コード4（LINE Pay圧着用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_SyunoCoCd5: SMBC決済ステーション - 収納企業コード5（LINE Pay封書用）
            'Smbc_SyunoCoCd5' => array(
                'name' => 'Smbc_SyunoCoCd5',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 収納企業コード5（LINE Pay封書用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 8,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 収納企業コード5（LINE Pay封書用）'は半角英数字8文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 収納企業コード5（LINE Pay封書用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,8}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 収納企業コード5（LINE Pay封書用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_SyunoCoCd6: SMBC決済ステーション - 収納企業コード6（LINE Pay同梱用）
            'Smbc_SyunoCoCd6' => array(
                'name' => 'Smbc_SyunoCoCd6',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 収納企業コード6（LINE Pay同梱用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 8,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 収納企業コード6（LINE Pay同梱用）'は半角英数字8文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 収納企業コード6（LINE Pay同梱用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,8}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 収納企業コード6（LINE Pay同梱用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_ShopPwd1: SMBC決済ステーション - アクセスパスワード1（圧着用）
            'Smbc_ShopPwd1' => array(
                'name' => 'Smbc_ShopPwd1',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - アクセスパスワード1（圧着用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 20,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - アクセスパスワード1（圧着用）'は半角英数字20文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - アクセスパスワード1（圧着用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,20}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - アクセスパスワード1（圧着用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_ShopPwd2: SMBC決済ステーション - アクセスパスワード2（封書用）
            'Smbc_ShopPwd2' => array(
                'name' => 'Smbc_ShopPwd2',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - アクセスパスワード2（封書用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 20,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - アクセスパスワード2（封書用）'は半角英数字20文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - アクセスパスワード2（封書用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,20}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - アクセスパスワード2（封書用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_ShopPwd3: SMBC決済ステーション - アクセスパスワード3（同梱用）
            'Smbc_ShopPwd3' => array(
                'name' => 'Smbc_ShopPwd3',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - アクセスパスワード3（同梱用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 20,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - アクセスパスワード3（同梱用）'は半角英数字20文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - アクセスパスワード3（同梱用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,20}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - アクセスパスワード3（同梱用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_ShopPwd4: SMBC決済ステーション - アクセスパスワード4（LINE Pay圧着用）
            'Smbc_ShopPwd4' => array(
                'name' => 'Smbc_ShopPwd4',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - アクセスパスワード4（LINE Pay圧着用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 20,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - アクセスパスワード4（LINE Pay圧着用）'は半角英数字20文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - アクセスパスワード4（LINE Pay圧着用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,20}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - アクセスパスワード4（LINE Pay圧着用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_ShopPwd5: SMBC決済ステーション - アクセスパスワード5（LINE Pay封書用）
            'Smbc_ShopPwd5' => array(
                'name' => 'Smbc_ShopPwd5',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - アクセスパスワード5（LINE Pay封書用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 20,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - アクセスパスワード5（LINE Pay封書用）'は半角英数字20文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - アクセスパスワード5（LINE Pay封書用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,20}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - アクセスパスワード5（LINE Pay封書用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_ShopPwd6: SMBC決済ステーション - アクセスパスワード6（LINE Pay同梱用）
            'Smbc_ShopPwd6' => array(
                'name' => 'Smbc_ShopPwd6',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - アクセスパスワード6（LINE Pay同梱用）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 20,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - アクセスパスワード6（LINE Pay同梱用）'は半角英数字20文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - アクセスパスワード6（LINE Pay同梱用）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9a-zA-Z]{1,20}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - アクセスパスワード6（LINE Pay同梱用）'は半角英数字のみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_SeikyuuName: SMBC決済ステーション - 請求内容
            // → 決済ステーション側制約がSJISで100バイトMAXなので1文字3バイト勘定で33文字を上限とする
            'Smbc_SeikyuuName' => array(
                'name' => 'Smbc_SeikyuuName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 請求内容'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 33,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 請求内容'は33文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 請求内容'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Smbc_SeikyuuKana: SMBC決済ステーション - 請求内容（カナ）
            // → 決済ステーション側制約が半角カナ48文字（＝48バイトMAX）で濁点を考慮する必要があるため半分の24文字を上限とする
            'Smbc_SeikyuuKana' => array(
                'name' => 'Smbc_SeikyuuKana',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - 請求内容（カナ）'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 24,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - 請求内容（カナ）'は24文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - 請求内容（カナ）'が短すぎます"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[ァ-ヾ]{1,24}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - 請求内容（カナ）'は全角カナのみ入力可能です"
                            )
                        )
                    )
                )
            ),
            // Smbc_Yu_SubscriberName: SMBC決済ステーション - ゆうちょ加入者名
            'Smbc_Yu_SubscriberName' => array(
                'name' => 'Smbc_Yu_SubscriberName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - ゆうちょ加入者名'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - ゆうちょ加入者名'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - ゆうちょ加入者名'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Smbc_Yu_AccountNumber: SMBC決済ステーション - ゆうちょ口座番号
            'Smbc_Yu_AccountNumber' => array(
                'name' => 'Smbc_Yu_AccountNumber',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - ゆうちょ口座番号'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 12,
                            'max' => 12,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - ゆうちょ口座番号'は半角数字12文字で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - ゆうちょ口座番号'は半角数字12文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{12}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - ゆうちょ口座番号'は半角数字12文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Smbc_Yu_ChargeClass: SMBC決済ステーション - ゆうちょ払込負担区分
            'Smbc_Yu_ChargeClass' => array(
                'name' => 'Smbc_Yu_ChargeClass',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - ゆうちょ払込負担区分'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'InArray',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'haystack' => array_keys($maps[LogicOemClaimAccount::MAPKEY_YU_CHARGE_CLASS]),
                            'messages' => array(
                                InArray::NOT_IN_ARRAY => "'SMBC決済ステーション - ゆうちょ払込負担区分'に未定義の値が指定されました"
                             )
                        )
                    )
                )
            ),
            // Smbc_Cv_ReceiptAgentName: SMBC決済ステーション - コンビニ収納代行会社名
            'Smbc_Cv_ReceiptAgentName' => array(
                'name' => 'Smbc_Cv_ReceiptAgentName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'continue_if_empty' => true,
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - コンビニ収納代行会社名'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 0,
                            'max' => 50,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - コンビニ収納代行会社名'は50文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - コンビニ収納代行会社名'が短すぎます"
                            )
                        )
                    )
                )
            ),
            // Smbc_Cv_ReceiptAgentCode: SMBC決済ステーション - コンビニ収納ファイナンスコード
            'Smbc_Cv_ReceiptAgentCode' => array(
                'name' => 'Smbc_Cv_ReceiptAgentCode',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'continue_if_empty' => true,
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - コンビニ収納ファイナンスコード'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 5,
                            'max' => 5,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - コンビニ収納ファイナンスコード'は数字5文字で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - コンビニ収納ファイナンスコード'は数字5文字で入力してください"
                            )
                        )
                    ),
                    array(
                        'name' => 'Regex',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'pattern' => '/^[0-9]{5}$/u',
                            'messages' => array(
                                Regex::NOT_MATCH => "'SMBC決済ステーション - コンビニ収納ファイナンスコード'は数字5文字で入力してください"
                            )
                        )
                    )
                )
            ),
            // Smbc_Cv_SubscriberName: SMBC決済ステーション - コンビニ収納代行加入者名
            'Smbc_Cv_SubscriberName' => array(
                'name' => 'Smbc_Cv_SubscriberName',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                    ),
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                NotEmpty::IS_EMPTY => "'SMBC決済ステーション - コンビニ収納代行加入者名'は必須です"
                             )
                        )
                    ),
                    array(
                        'name' => 'StringLength',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                StringLength::TOO_LONG => "'SMBC決済ステーション - コンビニ収納代行加入者名'は255文字以内で入力してください",
                                StringLength::TOO_SHORT => "'SMBC決済ステーション - コンビニ収納代行加入者名'が短すぎます"
                            )
                        )
                    )
                )
            )
        );

        return array_merge($validators, $smbc_validators);
    }
}
