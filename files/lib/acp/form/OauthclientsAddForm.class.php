<?php
namespace wcf\acp\form;
use wcf\data\oauthclient\OauthclientAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new Oauth Client
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthclientsAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.oauthclients.add';
	
	/**
	 * name of the client
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * Redirect URL for the Client
	 * @var	string
	 */
	public $redirectUrl = '';
	
	/**
	 * If Implicit grant type is allowed
	 * @var integer
	 */
	public $implicit = 0;
	
	/**
	 * If password grant type is allowed
	 * @var integer
	 */
	public $password = 0;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.canManageOauthclients'];
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' 		=> 'add',
			'name' 			=> $this->name,
			'redirectUrl'	=> $this->redirectUrl,
			'implicit'		=> $this->implicit,
			'password'		=> $this->password,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['redirectUrl'])) $this->redirectUrl = StringUtil::trim($_POST['redirectUrl']);
		if (isset($_POST['implicit'])) $this->implicit = intval($_POST['implicit']);
		if (isset($_POST['password'])) $this->password = intval($_POST['password']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		if(PHP_INT_MAX > 2147483647 ){
			$strClientID = CryptoUtil::randomInt(100000000000000,999999999999999);
		} else {
			$strClientID = CryptoUtil::randomInt(1000000000,PHP_INT_MAX);
		}
		
		
		$this->objectAction = new OauthclientAction([], 'create', [
				'data' => array_merge($this->additionalFields, [
				'oauthclientID' => $strClientID,
				'name' => $this->name,
				'redirectUrl' => $this->redirectUrl,
				'time' => TIME_NOW,
				'clientSecret' => PasswordUtil::getRandomPassword(128),
				'lastModified' => TIME_NOW,
				'jwtSecret' 	=> PasswordUtil::getRandomPassword(64),
				'implicit'		=> $this->implicit,
				'password'		=> $this->password,
			])
		]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// reset values
		$this->name = '';
		$this->redirectUrl = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate first name
		if (empty($this->name)) {
			throw new UserInputException('name');
		}
		if (mb_strlen($this->name) > 255) {
			throw new UserInputException('name', 'tooLong');
		}
		
		// validate last name
		if (empty($this->redirectUrl)) {
			throw new UserInputException('redirectUrl');
		}
		if (mb_strlen($this->redirectUrl) > 255) {
			throw new UserInputException('redirectUrl', 'tooLong');
		}
	}
}
