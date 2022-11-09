<?php
namespace oemadmin\Controller;

use Coral\Base\BaseGeneralUtils;
use Coral\Base\BaseHtmlUtils;
use Coral\Coral\Controller\CoralControllerAction;
use Coral\Coral\CoralCodeMaster;
use Coral\Coral\Validate\CoralValidateMultiMail;
use Coral\Coral\Validate\CoralValidatePhone;
use models\Table\TableApiUserEnterprise;
use models\Table\TableOem;
use models\Table\TableOemOperator;
use models\Table\TableSite;
use oemadmin\Application;
use models\Table\TablePricePlan;
use Zend\Json\Json;

class OemController extends CoralControllerAction {
	protected $_componentRoot = './application/views/components';

	/**
	 * @var Application
	 */
	protected $app;

	/**
	 * IndexControllerを初期化する
	 */
	public function _init() {
		$this->app = Application::getInstance();

		$this
			->addStyleSheet($this->app->getOemCss())
			->addJavaScript('../../js/prototype.js')
			->addJavaScript('../../js/json.js');

		$this->setPageTitle($this->app->getOemServiceName()." - 登録情報管理");

        $this->view->assign('userInfo', $this->app->authManagerAdmin->getUserInfo());

		// コードマスターからOEM情報向けのマスター連想配列を作成し、ビューへアサインしておく
		$codeMaster = new CoralCodeMaster($this->app->dbAdapter);
		$masters = array(
			'Prefecture' => $codeMaster->getPrefectureMaster(),
			'PreSales' => $codeMaster->getPreSalesMaster(),
			'Industry' => $codeMaster->getIndustryMaster(),
			'Plan' => $codeMaster->getPlanMaster(),
			'FixPattern' => $codeMaster->getFixPatternMaster(),
			'LimitDay' => $codeMaster->getLimitDayMaster(),
			'LimitDatePattern' => $codeMaster->getLimitDatePatternMaster(),
			'FfAccountClass' => $codeMaster->getAccountClassMaster(),
			'TcClass' => $codeMaster->getTcClassMaster(),
			'SiteForm' => $codeMaster->getSiteFormMaster(),
			'DocCollect' => $codeMaster->getDocCollectMaster(),
			'ExaminationResult' => $codeMaster->getExaminationResultMaster(),
			'AutoCreditJudgeMode' => $codeMaster->getAutoCreditJudgeModeMaster(),
			'CjMailMode' => $codeMaster->getCjMailModeMaster(),
			'CombinedClaimMode' => $codeMaster->getCombinedClaimMode(),
			'AutoClaimStopFlg' => $codeMaster->getAutoClaimStopFlgMaster()
		);

		$this->view->assign('master_map', $masters);
	}

    /**
     * OEM情報詳細画面を表示
     */
    public function detailAction()
    {
        $req = $this->params();
        $eid = $this->app->authManagerAdmin->getUserInfo()->OemId;

        $enterprises = new TableOem($this->app->dbAdapter);
        $sites = new TableSite($this->app->dbAdapter);
        $e = $enterprises->findOem2($eid)->current();

        $ops = new TableOemOperator($this->app->dbAdapter);
        $opData = $ops->findOperator($this->app->authManagerAdmin->getUserInfo()->OemOpId)->current();

        // マスターがらみの項目については、キャプションを求めてセットする。
        $codeMaster = new CoralCodeMaster($this->app->dbAdapter);
        $e['PreSales'] = $codeMaster->getPreSalesCaption((int)$e['PreSales']);
        $e['Industry'] = $codeMaster->getIndustryCaption((int)$e['Industry']);
        $e['Plan'] = $codeMaster->getPlanCaption((int)$e['Plan']);

        $e['FixPattern'] = $codeMaster->getFixPatternCaption((int)$e['FixPattern']);

        if ((int)$e['LimitDatePattern'] == 1) {
            $e['LimitDay'] = sprintf('翌月%s日', $codeMaster->getLimitDayCaption($e['LimitDay']));
        } else if ((int)$e['LimitDatePattern'] == 2) {
            $e['LimitDay'] = sprintf('当月%s日', $codeMaster->getLimitDayCaption($e['LimitDay']));
        } else {
            $e['LimitDay'] = '';
        }

        $e['LimitDatePattern'] = $codeMaster->getLimitDatePatternCaption((int)$e['LimitDatePattern']);

        $e['FfAccountClass'] = $codeMaster->getAccountClassCaption((int)$e['FfAccountClass']);
        $e['TcClass'] = $codeMaster->getTcClassCaption((int)$e['TcClass']);

        $e['AutoCreditJudgeMode'] = $codeMaster->getAutoCreditJudgeModeCaption((int)$e['AutoCreditJudgeMode']);

        $sites = $sites->getValidAll($e['EnterpriseId']);
        //請求取りまとめモードがサイト毎だった場合に対象サイト数を取得(2013.10.23 kaki)
        $num = 0;
        foreach($sites as $site) {
            $site['SiteForm'] = $codeMaster->getSiteFormCaption($site['SiteForm']);
            $site['ReqMailAddrFlg'] = $site['ReqMailAddrFlg'] == 1 ? '必須' : '';

            if($site['CombinedClaimFlg'] == 1)  {
                $num++;
            }
        }

            // 料金プランマスタのデータを取得する。
        $mdlpp = new TablePricePlan($this->app->dbAdapter);
        $ri = $mdlpp->getAll();
        $planName = ResultInterfaceToArray($ri);

        // 取得データからJSON形式の情報をdecodeする
        // プラン別決済手数料率
        $rate = Json::decode($e['SettlementFeeRatePlan'], Json::TYPE_ARRAY);

        // プラン別月額固定費
        $fee = Json::decode($e['EntMonthlyFeePlan'], Json::TYPE_ARRAY);

        // 配列にキーが存在しているか確認して配列を作りこむ。
        $plan = array();
        foreach ($planName as $key => $value) {
            // 基本の配列を作成する
            $plan[$key] = array(
                    'PricePlanId' => $value['PricePlanId'],
                    'PricePlanName' => $value['PricePlanName'],
            );
            // 決済手数料率
            // データが取得できた場合、以下処理を行う。
            if (isset($rate)) {
                if (array_key_exists($value['PricePlanId'], $rate)) {
                    foreach ($rate as $key2 => $value2) {
                        if ($value['PricePlanId'] == $key2) {
                            // 基本の配列に追加要素を足しこむ
                            $plan[$key] += array( 'SettlementFeeRate' => $value2 );
                        }
                    }
                } else {
                    $plan[$key] += array( 'SettlementFeeRate' => $value['SettlementFeeRate'] );
                }
            }
            // 店舗月額固定費
            // データが取得できた場合、以下処理を行う。
            if (isset($fee)) {
                if (array_key_exists($value['PricePlanId'], $fee)) {
                    foreach ($fee as $key2 => $value2) {
                        if ($value['PricePlanId'] == $key2) {
                            // 基本の配列に追加要素を足しこむ
                            $plan[$key] += array( 'EntMonthlyFee' => $value2 );
                        }
                    }
                } else {
                    $plan[$key] += array( 'EntMonthlyFee' => $value['MonthlyFee'] );
                }
            }
        }

        // 詳細画面からの更新処理で検証エラーが発生していたらその情報をマージする
        $e = array_merge($e, $req->fromRoute('prev_input', array()));
        $this->view->assign('error', $req->fromRoute('prev_errors', array()));
        $backTo = $req->fromRoute('prev_backto', $_SERVER['HTTP_REFERER']);

        $this->view->assign('data', $e);
        $this->view->assign('plan', $plan);
        $this->view->assign('sites', $sites);
        $this->view->assign('opData', $opData);
        $this->view->assign('backTo', $backTo);
        $this->view->assign('combinedclaimnum', $num);

        // DocCollect, ExaminationResultについて、リテラルの連想配列を廃止し
        // CodeMasterの拡張を取り込んだ（09.06.08 eda）
        $this->view->assign('docCollectSelectTag',
            BaseHtmlUtils::SelectTag(
                'DocCollect',
                $codeMaster->getDocCollectMaster(),
                $e['DocCollect'],
                'style="width: 80px"'
            )
        );

        $this->view->assign('examinationResultSelectTag',
            BaseHtmlUtils::SelectTag(
                'ExaminationResult',
                $codeMaster->getExaminationResultMaster(),
                $e['ExaminationResult'],
                'style="width: 80px"'
            )
        );

        // 審査結果メール送信URLのアサイン
        $this->view->assign('urlSendExam', $this->app->tools['url']['sendexam']);

        // 送達確認用メール送信URLのアサイン 2014.3.31
        $this->view->assign('urlSendTest', $this->app->tools['url']['sendtest']);

        // APIユーザリレーションのアサイン（09.06.17 eda）
        $mdlApiEnt = new TableApiUserEnterprise($this->app->dbAdapter);
        $apiUsers = $mdlApiEnt->findRelatedApiUsers($eid);

        $this->view->assign('apiUsers', $apiUsers == null ? array() : ResultInterfaceToArray($apiUsers));

        // OEM向け請求書同梱ツールに関する設定をアサイン（13.1.9 eda）
        //$this->view->assign('sbsettings', $this->getEntSelfBillingSettings());

        $this->view->assign( 'current_action', 'oem/detail' );
        return $this->view;
    }

	/**
	 * OEM編集画面を表示
	 */
	public function editAction() {
        $eid = $this->app->authManagerAdmin->getUserInfo()->OemId;

		$mdlOem = new TableOem($this->app->dbAdapter);

		// OEMデータを取得
		$eData = $mdlOem->findOem($eid)->current();

		$data = array('isNew' => false);
		$this->view->assign('data', array_merge($data, $eData));
		$this->view->assign('error', array());
		$this->view->assign('CurrentFixPatternMsg', $currentFixPatternMsg);

		$this->setTemplate('form');
		$this->view->assign( 'current_action', 'oem/edit' );
		return $this->view;
	}

	/**
	 * OEM登録内容の確認
	 */
	public function confirmAction() {
	    $params = $this->getParams();
	    $data = isset($params['form']) ? $params['form'] : array();

		$errors = $this->validate($data);
		if(!empty($errors)) {
			// 検証エラーは入力画面へ戻す

			$this->view->assign('data', $data);
			$this->view->assign('error', $errors);

			$this->setTemplate('form');
			return $this->view;
		}

		// フォームデータ自身をエンコード
		$formData = base64_encode(serialize($data));

		$this->view->assign('data', $data);
		$this->view->assign('encoded_data', $formData);
		$this->view->assign( 'current_action', 'oem/confirm' );
		return $this->view;

	}

	/**
	 * 確認画面からの戻り処理
	 */
	public function backAction() {
		// エンコード済みのPOSTデータを復元する
		$eData = unserialize(base64_decode($this->getRequest()->getPost('hash')));

		$this->view->assign('data', $eData);
		$this->view->assign('error', array());
		$this->view->assign('CurrentFixPatternMsg', $currentFixPatternMsg);

		$this->setTemplate('form');
		$this->view->assign( 'current_action', 'oem/back' );
		return $this->view;
	}

	/**
	 * OEM登録を実行
	 */
	public function saveAction() {
        $eData = unserialize(base64_decode($this->params()->fromPost('hash')));

		$mdlOem = new TableOem($this->app->dbAdapter);

		$db = $this->app->dbAdapter;
		$db->getDriver()->getConnection()->beginTransaction();

		try {
			if( ! $eData['isNew'] && isset($eData['OemId']) ) {
				// 編集モード時
			    $eData['UpdateId'] = $this->app->authManagerAdmin->getUserInfo()->UserId;

				$mdlOem->saveUpdate($eData, $eData['OemId']);
			} else {
				// 新規モード時
				$newId = $mdlOem->saveNew($eData);

				$eData['OemId'] = $newId;			// 獲得したプライマリキーをセットしておく
				$eData['RegistDate'] = date("Y-m-d H:i:s");	// 登録日時を設定
				$eData['ValidFlg'] = 1;				// 有効フラッグを設定

				$mdlOem->saveUpdate($eData, $newId);		// 更新保存
			}
		} catch(\Exception $err) {
			$db->getDriver()->getConnection()->rollBack();
			throw $err;
		}
		$db->getDriver()->getConnection()->commit();

		// 保存済みデータをエンコード
		$data = base64_encode(serialize($eData));

		$this->view->assign('data', $data);
		$this->view->assign( 'current_action', 'oem/save' );
		return $this->view;
	}

	/**
	 * 登録完了画面の表示
	 */
	public function completionAction() {
        $data = unserialize(base64_decode($this->params()->fromPost('hash')));

		$this->view->assign('eid', $data['OemId']);
		$this->view->assign('data', $data);
		$this->view->assign( 'current_action', 'oem/completion' );
		return $this->view;
	}

	/**
	 * OEMデータ連送配列の利率を実数に補正する
	 *
	 * @access protected
	 * @param array $data OEMデータの連想配列
	 * @return array 利率が実数に補正されたOEMデータの連想配列
	 */
	protected function fixSettelementFeeRate($data) {
        // 決済手数料率を実数化
		$data['SettlementFeeRateRKF'] = BaseGeneralUtils::ToRealRate($data['SettlementFeeRateRKF']);
        $data['SettlementFeeRateSTD'] = BaseGeneralUtils::ToRealRate($data['SettlementFeeRateSTD']);
        $data['SettlementFeeRateEXP'] = BaseGeneralUtils::ToRealRate($data['SettlementFeeRateEXP']);
        $data['SettlementFeeRateSPC'] = BaseGeneralUtils::ToRealRate($data['SettlementFeeRateSPC']);

		// 同梱を実数化
		$data['OpDkInitFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpDkInitFeeRate']);
        $data['OpDkMonthlyFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpDkMonthlyFeeRate']);

		// APIを実数化
		$data['OpApiRegOrdMonthlyFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpApiRegOrdMonthlyFeeRate']);
        $data['OpApiAllInitFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpApiAllInitFeeRate']);
        $data['OpApiAllMonthlyFeeRate'] = BaseGeneralUtils::ToRealRate($data['OpApiAllMonthlyFeeRate']);

		return $data;
	}

	/**
	 * POSTされた入力フォームに対し、未送信キーを補完する
	 *
	 * @access protected
	 * @param array $data POSTデータ
	 * @return array $dataの未送信キーを補完したデータ
	 */
	protected function fixInputForm(array $data) {
		$defaults = array(
			'FfAccountClass' => -1
		);

		return array_merge($defaults, $data);
	}

	/**
	 * 入力検証処理
	 *
	 * @access protected
	 * @param array $data
	 * @return array
	 */
	protected function validate($data = array()) {
		$isNew = $data['isNew'] ? true : false;

		$errors = array();

		// CpNameKj: 担当者名
		$key = 'CpNameKj';
		if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
		    $errors[$key] = array("'担当者名'は必須です");
		}
		if (!isset($errors[$key]) && !(strlen($data[$key]) <= 160)) {
		    $errors[$key] = array("'担当者名'は160文字以内で入力してください");
		}

		// CpNameKn: 担当者名カナ
		$key = 'CpNameKn';
		if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
		    $errors[$key] = array("'担当者名カナ'は必須です");
		}
		if (!isset($errors[$key]) && !(strlen($data[$key]) <= 160)) {
		    $errors[$key] = array("'担当者名カナ'は160文字以内で入力してください");
		}

		// DivisionName: 部署名
		$key = 'DivisionName';
		if (!isset($errors[$key]) && !(strlen($data[$key]) <= 255)) {
		    $errors[$key] = array("'部署名'は255文字以内で入力してください");
		}

		// MailAddress: メールアドレス
		$key = 'MailAddress';
		if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
		    $errors[$key] = array("'メールアドレス'は必須です");
		}
		$cvmm = new CoralValidateMultiMail();
		if (!isset($errors[$key]) && !$cvmm->isValid($data[$key])) {
		    $errors[$key] = array("'メールアドレス'が不正な形式です");
		}

		// ContactPhoneNumber: 連絡先電話番号
		$key = 'ContactPhoneNumber';
		if (!isset($errors[$key]) && !(strlen($data[$key]) > 0)) {
		    $errors[$key] = array("'連絡先電話番号'は必須です");
		}
        $cvp = new CoralValidatePhone();
		if (!isset($errors[$key]) && !$cvp->isValid($data[$key])) {
		    $errors[$key] = array("'連絡先電話番号'が不正な形式です");
		}

		// ContactFaxNumber: 連絡先FAX番号
		$key = 'ContactFaxNumber';
		if (!isset($errors[$key]) && (strlen($data[$key]) > 0) && !$cvp->isValid($data[$key])) {
		    $errors[$key] = array("'連絡先FAX番号'が不正な形式です");
		}

		return $errors;
	}
}

