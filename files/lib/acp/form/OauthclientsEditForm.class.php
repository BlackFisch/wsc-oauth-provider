<?php
namespace wcf\acp\form;
use wcf\data\oauthclient\Oauthclient;
use wcf\data\oauthclient\OauthclientAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Shows the form to edit an existing Oauth Client
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthclientsEditForm extends OauthclientsAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.oauthclients';
	
	/**
	 * edited person object
	 * @var	Person
	 */
	public $oauthclient = null;
	
	/**
	 * The Client Secret
	 * @var string
	 */
	public $clientSecret = "";
	
	/**
	 * The JWT Secret
	 * @var string
	 */
	public $jwtSecret = "";
	
	/**
	 * id of the edited person
	 * @var	integer
	 */
	public $personID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.canManageOauthclients'];
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'oauthclientsEdit';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' 			=> 'edit',
			'oauthclients'		=> $this->oauthclient,
			'clientID'			=> $this->clientID,
			'clientSecret'		=> $this->clientSecret,
			'jwtSecret'			=> $this->jwtSecret,
			'implicit'			=> $this->implicit,
			'password'			=> $this->password,
			'oauth_tls_warning' => (StringUtil::startsWith($this->redirectUrl, 'http:/')),
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->name = $this->oauthclient->name;
			$this->redirectUrl = $this->oauthclient->redirectUrl;
			$this->clientSecret = $this->oauthclient->clientSecret;
			$this->jwtSecret = $this->oauthclient->jwtSecret;
			$this->implicit = $this->oauthclient->implicit;
			$this->password = $this->oauthclient->password;
		}
		
		$this->clientID = $this->oauthclient->oauthclientID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->oauthclientID = $_REQUEST['id'];
		$this->oauthclient = new Oauthclient($this->oauthclientID);
		if (!$this->oauthclient->oauthclientID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		if(isset($_POST['refresh'])){
			$strNewClientSecret = PasswordUtil::getRandomPassword(128);
			$strNewJWTSecret = PasswordUtil::getRandomPassword(64);
			$this->objectAction = new OauthclientAction([$this->oauthclient], 'update', [
					'data' => array_merge($this->additionalFields, [
							'clientSecret' 		=> $strNewClientSecret,
							'lastModified'		=> TIME_NOW,
							'jwtSecret'			=> $strNewJWTSecret,
					])
			]);
			$this->objectAction->executeAction();
		
			$this->clientSecret = $strNewClientSecret;
			$this->jwtSecret = $strNewJWTSecret;
		} else {
			$this->objectAction = new OauthclientAction([$this->oauthclient], 'update', [
					'data' => array_merge($this->additionalFields, [
							'name' 				=> $this->name,
							'redirectUrl'		=> $this->redirectUrl,
							'lastModified'		=> TIME_NOW,
							'implicit'			=> $this->implicit,
							'password'			=> $this->password,
					])
			]);
			$this->objectAction->executeAction();
			$this->clientSecret = $this->oauthclient->clientSecret;
			$this->jwtSecret = $this->oauthclient->jwtSecret;
		}
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
