<?php
namespace api\classes\Service;

use api\Application;
use api\classes\Service\ServiceAbstract;
use api\classes\Service\Sitemod\ServiceSitemodConst;
use api\classes\Service\Response\ServiceResponseSitemod;
use zend\Db\ResultSet\ResultSet;
use models\Table\TableSite;
use models\Table\TableEnterprise;
use Coral\Coral\Validate\CoralValidatePhone;
use models\Table\TableUser;

/**
 * サイト情報更新サービスクラス
 */
class ServiceSitemod extends ServiceAbstract {
	/**
	 * サイト情報更新APIのサービスID
	 * @var string
	 */
	protected $_serviceId = "07";

	/**
	 * 修正データ
	 *
	 * @var array
	 */
	public $params;

	/**
	 * 初期化処理
	 *
	 * @access protected
	 */
	protected function init() {
		// サイトIDチェックは認証としては行わない（入力検証で実施）
		$this->_checkSiteId = false;

		// レスポンスを初期化
        $this->_response = new ServiceResponseSitemod();

		// 認証用
        $this->_apiUserId = $this->_data[ServiceSitemodConst::API_USER_ID];
        $this->_enterpriseId = $this->_data[ServiceSitemodConst::ENTERPRISE_ID];

		// 登録向けデータ
		$this->_siteId = $this->_data[ServiceSitemodConst::SITE_ID];
        $this->params = $this->_data[ServiceSitemodConst::REQ_PARAMS];

		// ログ出力
		Application::getInstance()->logger->info(
			get_class($this) . '#init() ' .
			join(', ', array(
				sprintf('%s: %s', ServiceSitemodConst::ENTERPRISE_ID, $this->_enterpriseId),
				sprintf('%s: %s', ServiceSitemodConst::API_USER_ID, $this->_apiUserId),
				sprintf('RemoteAddr: %s', f_get_client_address())       // 2015/09/23 Y.Suzuki Mod f_get_client_address をｺｰﾙするように変更
			)) );
	}

	/**
	 * 入力に対する検証を行う
	 *
	 * @access protected
	 * @return boolean 検証結果
	 */
	protected function check() {
        // サイトIDの有無をチェック
        if($this->_siteId === null || strlen(trim($this->_siteId)) == 0) {
            $this->_response->addMessage(
                sprintf('E%s201', $this->_serviceId),
                'サイトIDの指定は必須です'
            );
            return false;   // 検証エラー確定
        }

        // サイトID情報を取得
        // → 現在の事業者配下のサイト情報のみ返却されるので、他事業者のサイトIDを指定した場合は見つからない
        if($this->getSiteData() == null) {
            $this->_response->addMessage(
                // TODO: 3桁コードが不適切。仕様書を変更して301にする
                sprintf('E%s304', $this->_serviceId),
                '指定のサイトIDは登録されていません'
            );
            return false;   // 検証エラー確定
        }

		// サイトIDまで確定したのでレスポンスデータを初期化しておく
		try {
			$this->fillResultData();
		} catch(\Exception $err) {
			// ここでの例外はなにもしない
		}

		$result = true;
        // サイト名が指定されている場合はサイト名のチェック
        if(isset($this->params[ServiceSitemodConst::SITE_NAME])) {
            $siteName = trim(nvl($this->params[ServiceSitemodConst::SITE_NAME]));
            if(strlen($siteName)) {
                // 空白除去後の値を再設定
                $this->params[ServiceSitemodConst::SITE_NAME] = $siteName;
                if(mb_strlen($siteName) > 160) {
                    $this->_response->addMessage(
                        sprintf('E%s203', $this->_serviceId),
                        // TODO: メッセージが不適切。仕様書を変更して文字数オーバーである旨の通知にする
                        'サイト名 : 入力が不正です'
                    );
                    $result = false;	// 検証エラー確定だが値はまだ返さない
                }
            } else {
                // 0文字データの場合は削除する
                unset($this->params[ServiceSitemodConst::SITE_NAME]);
            }
        }

        // サイトURLが指定されている場合はサイトURLのチェック
        if(isset($this->params[ServiceSitemodConst::SITE_URL])) {
            $siteUrl = trim(nvl($this->params[ServiceSitemodConst::SITE_URL]));
            if(strlen($siteUrl)) {
                // 空白除去後の値を再設定
                $this->params[ServiceSitemodConst::SITE_URL] = $siteUrl;
                if(mb_strlen($siteUrl) > 255) {
                    $this->_response->addMessage(
                        sprintf('E%s203', $this->_serviceId),
                        // TODO: メッセージが不適切。仕様書を変更して文字数オーバーである旨の通知にする
                        'サイトURL : 入力が不正です'
                    );
                    $result = false;	// 検証エラー確定だが値はまだ返さない
                }
            } else {
                // 0文字データの場合は削除する
                unset($this->params[ServiceSitemodConst::SITE_URL]);
            }
        }

        // 連絡先電話番号が指定されている場合は連絡先電話番号のチェック
        if(isset($this->params[ServiceSitemodConst::PHONE])) {
            $phone = trim(nvl($this->params[ServiceSitemodConst::PHONE]));
            if(strlen($phone)) {
                if(mb_strlen($phone) > 50) {
                    $this->_response->addMessage(
                        sprintf('E%s203', $this->_serviceId),
                        // TODO: メッセージが不適切。仕様書を変更して文字数オーバーである旨の通知にする
                        '連絡先電話番号 : 入力が不正です'
                    );
                    $result = false;	// 検証エラー確定だが値はまだ返さない
                } else {
					// 文字数チェックと属性チェックは排他で行う
					$validator = new CoralValidatePhone();
					if(!$validator->isValid($phone)) {
						$this->_response->addMessage(
							sprintf('E%s202', $this->_serviceId),
							'連絡先電話番号 : 入力が不正です'
						);
						$result = false;	// 検証エラー確定だが値はまだ返さない
					}
				}
            } else {
                // 0文字データは削除する
                unset($this->params[ServiceSitemodConst::PHONE]);
            }
        }

		return $result;
	}

	/**
	 * サービスを実行する
	 *
	 * @access protected
	 * @return boolean サービス実行結果
	 */
	protected function exec() {
		$params = $this->params;

		$db = $this->_db;
		$siteTable = new TableSite($db);
		$entTable = new TableEnterprise($db);
		$mdluser = new TableUser($this->_db);

		$db->getDriver()->getConnection()->beginTransaction();

		try {
		    // APIユーザーID
		    $opId = $mdluser->getUserId(3, $this->_apiUserId);

			// サイトデータの更新
			$siteData = array();
			if(isset($params[ServiceSitemodConst::SITE_NAME])) {
				$siteData['SiteNameKj'] = $params[ServiceSitemodConst::SITE_NAME];
			}
			if(isset($params[ServiceSitemodConst::SITE_URL])) {
				$siteData['Url'] = $params[ServiceSitemodConst::SITE_URL];
			}
			if(!empty($siteData)) {
			    $siteData['UpdateId'] = $opId;
				// 更新データが設定されている場合のみsaveUpdateを実行
				$siteTable->saveUpdate($siteData, $this->_siteId);
			}

			// 事業者データの更新
			$entData = array();
			if(isset($params[ServiceSitemodConst::PHONE])) {
				// 電話番号が連絡先電話番号である点に注意！
				$entData['ContactPhoneNumber'] = $params[ServiceSitemodConst::PHONE];
			}
			if(!empty($entData)) {
			    $entData['UpdateId'] = $opId;
				// 更新データが設定されている場合のみsaveUpdateを実行
				$entTable->saveUpdate($entData, $this->_enterpriseId);
			}

			$db->getDriver()->getConnection()->commit();
		} catch(\Exception $err) {
			// 例外発生時はロールバックだけ行って上位に再スロー
			$db->getDriver()->getConnection()->rollBack();
			throw $err;
		}

		try {
			// レスポンスデータを更新
			$this->fillResultData();
		} catch(\Exception $err) {
			// 例外発生時は上位に再スロー
			throw $err;
		}

		return true;
	}

	/**
	 * 処理結果を文字列として返却する
	 *
	 * @access protected
	 * @return string 処理結果
	 */
	protected function returnResponse() {
		return $this->_response->serialize();
	}

    /**
     * 現在の要求対象の事業者データを取得する
     *
     * @access protected
     * @return array | null
     */
    protected function getEnterpriseData() {
        $tbl = new TableEnterprise($this->_db);
        foreach($tbl->find($this->_enterpriseId) as $row) {
            return $row;
        }
        return null;
    }

    /**
     * 現在の要求対象のサイトデータを取得する
     *
     * @access protected
     * @return array | null
     */
    protected function getSiteData() {
        $tbl = new TableSite($this->_db);
        foreach($tbl->findSite($this->_siteId) as $row) {
            // 現在の要求対象事業者に関連付けられたサイトの場合のみ値を返す
            if($row['EnterpriseId'] == $this->_enterpriseId) {
                return $row;
            }
        }
        return null;
    }

	protected function fetchResultData() {
		$db = $this->_db;
		$q = <<<EOQ
SELECT
	s.SiteId AS siteId,
	s.SiteNameKj AS siteName,
	s.Url AS siteUrl,
	e.ContactPhoneNumber AS phone
FROM
	T_Enterprise e INNER JOIN
	T_Site s ON s.EnterpriseId = e.EnterpriseId
WHERE
	%s
EOQ;
		$where = join(' AND ', array(
			sprintf('e.EnterpriseId = %s', $this->_enterpriseId) ,
			sprintf('s.SiteId = %s',  $this->_siteId)
		));
		$q = sprintf($q, $where);
		foreach($db->query($q)->execute() as $row) {
			return $row;
		}
		return array();
	}

	protected function fillResultData() {
		foreach($this->fetchResultData() as $key => $value) {
			$this->_response->$key = $value;
		}
	}
}
