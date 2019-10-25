<?php
namespace wcf\form;
use wcf\system\WCF;
use wcf\util\CryptoUtil;
use wcf\util\HeaderUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use wcf\data\oauthclient\Oauthclient;
use wcf\data\oauthauthorize\OauthauthorizeList;
use wcf\data\oauthauthorize\OauthauthorizeAction;
use wcf\data\oauthtoken\OauthtokenAction;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\data\oauthauthorize\Oauthauthorize;
use wcf\util\JWTToken;
use wcf\data\user\User;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;

/**
 * Form for Authorizing the Applications
 *
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */

class OauthConsentForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $neededModules = [];

	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;

	/**
	 * URL where the user should be redirected to
	 * 
	 * @var string
	 */
	public $url;
	
	/**
	 * Contains the allowed scopes
	 * @var array
	 */
	public $allowedScopes = ['identify', 'email', 'profile', 'openid'];
	
	/**
	 * User supplied Client-ID
	 * @var string
	 */
	public $clientID;
	/**
	 * User supplied Redirect-URI
	 * @var string
	 */
	public $redirectUri;
	/**
	 * User supplied Scopes
	 * @var string
	 */
	public $scopes;
	
	/**
	 * User supplied state
	 * @var string
	 */
	public $state;
	
	/**
	 * User supplied state
	 * @var string
	 */
	public $nonce = '';
	
	/**
	 * If User has pressed Cancel Button
	 * @var string
	 */
	public $cancel = false;
	
	/**
	 * Contains the OauthClient Object associated with Client-ID
	 * @var string
	 */
	public $oauthclient;
	
	/**
	 * If User wants to hide the Authorization Dialog
	 * @var string
	 */
	public $hide = 0;
	
	/**
	 * Where to redirect after succesful/canceled authorization
	 * @var string
	 */
	public $goToUrl;
	
	/**
	 * Objects of User Authorizes regarding the supplied Client-ID
	 * @var string
	 */
	public $userAuthorizes;
	
	/**
	 * User supplied desired response type
	 * @var string
	 */
	public $responseType = "code";
	
	/**
	 * Show my copyright
	 * @var boolean
	 */
	public $showCopyright = true;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();

		if (!empty($_GET['url'])) {
			$this->url = StringUtil::trim($_GET['url']);
		}
		
		if (!empty($_GET['client_id'])) {
			$this->clientID = StringUtil::trim($_GET['client_id']);
		} else {
			throw new NamedUserException("Client-ID not found.");
		}
		
		if (!empty($_GET['redirect_uri'])) {
			$this->redirectUri = urldecode(StringUtil::trim($_GET['redirect_uri']));
		}
		
		if (!empty($_GET['scope'])) {
			$arrScopes = explode(' ', urldecode(StringUtil::trim($_GET['scope'])));
			
			$this->scopes = $arrScopes;
		} else {
			$this->scopes[] = 'identify';
		}
		
		if (!empty($_GET['state'])) {
			$this->state = urldecode(StringUtil::trim($_GET['state']));
		}
		
		if (!empty($_GET['nonce'])) {
			$this->nonce = urldecode(StringUtil::trim($_GET['nonce']));
		}
		
		if (!empty($_GET['response_type']) && strlen($_GET['response_type'])) {
			$this->responseType = urldecode(StringUtil::trim($_GET['response_type']));
		}
		
		//Check Permission
		if(!WCF::getSession()->getPermission('user.profile.canUseOauth')){
			throw new IllegalLinkException();
		}
		
		//Check Client-ID
		$this->oauthclient = new Oauthclient($this->clientID);
				
		if (!$this->oauthclient->oauthclientID) {
			throw new NamedUserException("Client-ID not found.");
		}
		
		//Check RedirectURL
		if(strlen($this->redirectUri)){
			$strSavedRedirectUrl = $this->oauthclient->redirectUrl;

			if(!StringUtil::startsWith($this->redirectUri, $strSavedRedirectUrl, true)){
				throw new NamedUserException("Redirect-URI does not match.");
			}
		}
		
		//Check Scopes
		foreach($this->scopes as $strScope){
			if(!in_array($strScope, $this->allowedScopes)) {
				throw new NamedUserException("Scope is not valid. Available scopes: identify, email, profile, openid");
			}
		}
				
		//Check if client id is allowed for implicit
		if(($this->responseType == 'token' || $this->responseType == 'id_token') && !$this->oauthclient->implicit){
			throw new NamedUserException("Implicit Flow is not allowed for this Client-ID.");
		}

		//Check if application is already authorized
		$blnDismissMessage = false;
		
		$userAuthorizesList = new OauthauthorizeList();
		$userAuthorizesList->getConditionBuilder()->add('userID = ? AND clientID = ?', [WCF::getUser()->userID, $this->clientID]);
		$userAuthorizesList->readObjects();
		
		$this->userAuthorizes = $userAuthorizesList->getObjectIDs();
		
		if(count($this->userAuthorizes) > 0){
			$userAuthorize = new Oauthauthorize($this->userAuthorizes[0]);
			
			//Check if hidden
			if($userAuthorize->dismiss){
				//Check Scopes
				$strJsonScope = $userAuthorize->scope;
				$arrSavedScopes = [];

				if(strlen($strJsonScope)){
					$arrSavedScopes = JSON::decode($strJsonScope, true);
				}
				$intCount = 0;
				foreach($this->scopes as $strScope){
					if(in_array($strScope, $arrSavedScopes)){
						$intCount++;
					}
				}
				
				//If all scopes have already been requested
				if($intCount == count($this->scopes)){
					$blnDismissMessage = true;
				}
				
			}
		}

		if($blnDismissMessage){
			$this->hide = 1;
			$this->save();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['hide'])) {
			$this->hide = intval($_POST['hide']);
		}
		
		if (isset($_POST['cancel'])) {
			$this->cancel = 1;
		}
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$strSavedRedirectUrl = $this->oauthclient->redirectUrl;
		if(!StringUtil::startsWith($this->redirectUri, $strSavedRedirectUrl, true)){
			throw new NamedUserException("Redirect-URI does not match.");
		}
		
		$strState = (strlen($this->state)) ? '&state='.urlencode($this->state) : '';

		if($this->cancel){
			$strState = (strlen($this->state)) ? '&state='.urlencode($this->state) : '';
			
			if($this->responseType == 'token' || $this->responseType == 'id_token'){
				$strRedirectUrl = $this->redirectUri.'#error=access_denied'.$strState;
			} else {
				$strRedirectUrl = (strpos($this->redirectUri, '?') === false) ? $this->redirectUri.'?error=access_denied&error_description=The+resource+owner+or+authorization+server+denied+the+request'.$strState : $this->redirectUri.'&error=access_denied&error_description=The+resource+owner+or+authorization+server+denied+the+request'.$strState;
				
			}
			$this->goToUrl = $strRedirectUrl;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		if(!$this->cancel){
			//Save or update authorize entry
			if(count($this->userAuthorizes)){
				$userAuthorizeObject = new Oauthauthorize($this->userAuthorizes[0]);
				
				$arrCurrentSavedScopes = JSON::decode($userAuthorizeObject->scope, true);
				$arrNewScopes = array_unique(array_merge($arrCurrentSavedScopes, $this->scopes));
				
				$this->objectAction = new OauthauthorizeAction([$userAuthorizeObject], 'update', [
						'data' => [
								'dismiss' 		=> $this->hide,
								'lastUsed'		=> TIME_NOW,
								'scope'			=> JSON::encode($arrNewScopes),
						]
				]);
				$this->objectAction->executeAction();
				
				if(count($arrNewScopes) > count($arrCurrentSavedScopes)){
					//Notify user by mail
					if(OAUTHPROVIDER_SEND_EMAILS){
						try {
							$oauthclient = new Oauthclient($this->clientID);
							
							$emailData = [
									'scope' 		=> $this->getScopeString($arrNewScopes),
									'application'	=> $oauthclient->getTitle(),
							];
							
							$email = new Email();
							$email->addRecipient(new UserMailbox(WCF::getUser()));
							$email->setSubject(WCF::getLanguage()->getDynamicVariable('wcf.user.oauthprovider.mail.subject'));
							$email->setBody(new MimePartFacade([
									new RecipientAwareTextMimePart('text/html', 'email_OauthNewAuthorizeNotification', 'wcf', $emailData),
									new RecipientAwareTextMimePart('text/plain', 'email_OauthNewAuthorizeNotification', 'wcf', $emailData)
							]));
							$email->send();
							
						} catch (SystemException $e) {
							
						}
					}
				}
				
			} else {
				$this->objectAction = new OauthauthorizeAction([], 'create', [
						'data' => [
								'clientID' 		=> $this->clientID,
								'scope'			=> JSON::encode($this->scopes),
								'userID'		=> WCF::getUser()->userID,
								'time'			=> TIME_NOW,
								'lastUsed'		=> TIME_NOW,
								'dismiss' 		=> $this->hide,
						]
				]);
				$this->objectAction->executeAction();
				
				//Notify user by mail
				if(OAUTHPROVIDER_SEND_EMAILS){
					try {
						$oauthclient = new Oauthclient($this->clientID);
						
						$emailData = [
								'scope' 		=> $this->getScopeString($this->scopes),
								'application'	=> $oauthclient->getTitle(),
						];
						
						$email = new Email();
						$email->addRecipient(new UserMailbox(WCF::getUser()));
						$email->setSubject(WCF::getLanguage()->getDynamicVariable('wcf.user.oauthprovider.mail.subject'));
						$email->setBody(new MimePartFacade([
								new RecipientAwareTextMimePart('text/html', 'email_OauthNewAuthorizeNotification', 'wcf', $emailData),
								new RecipientAwareTextMimePart('text/plain', 'email_OauthNewAuthorizeNotification', 'wcf', $emailData)
						]));
						$email->send();
						
					} catch (SystemException $e) {
						
					}
				}

			}
			
			if($this->responseType == 'token'){
				if(!$this->oauthclient->implicit){
					throw new NamedUserException("Implicit Flow is not allowed for this Client-ID.");
				}
				
				$strCode = bin2hex(CryptoUtil::randomBytes(70));
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([], 'create', [
						'data' => [
								'oauthtokenID'	=> $strCode,
								'clientID' 		=> $this->clientID,
								'scope'			=> JSON::encode($this->scopes),
								'userID'		=> WCF::getUser()->userID,
								'time'			=> TIME_NOW,
								'tokenType'		=> 'bearer',
								'expires' 		=> 3600,
						]
				]);
				$objectAction->executeAction();
				
				//Build RedirectURL
				$strState = (strlen($this->state)) ? '&state='.urlencode($this->state) : '';
				$this->goToUrl = $this->redirectUri.'#access_token='.$strCode.$strState;
								
			} elseif($this->responseType == 'id_token') {
				if(!$this->oauthclient->implicit){
					throw new NamedUserException("Implicit Flow is not allowed for this Client-ID.");
				}
				
				$jwt = $this->generateNewIDToken($this->clientID, WCF::getUser()->userID, $this->scopes, $this->nonce);
								
				//Build RedirectURL
				$strState = (strlen($this->state)) ? '&state='.urlencode($this->state) : '';
				$this->goToUrl = $this->redirectUri.'#id_token='.$jwt.$strState;
				
			} elseif($this->responseType == 'code') {
				
				//Create new Code
				$strCode = bin2hex(CryptoUtil::randomBytes(50));
				$this->objectAction = new OauthtokenAction([], 'create', [
						'data' => [
								'oauthtokenID'	=> $strCode,
								'clientID' 		=> $this->clientID,
								'scope'			=> JSON::encode($this->scopes),
								'userID'		=> WCF::getUser()->userID,
								'time'			=> TIME_NOW,
								'tokenType'		=> 'code',
								'expires' 		=> 60,
								'nonce'			=> $this->nonce,
						]
				]);
				$this->objectAction->executeAction();
				
				//Build RedirectURL
				$strState = (strlen($this->state)) ? '&state='.urlencode($this->state) : '';
				$this->goToUrl = (strpos($this->redirectUri, '?') === false) ? $this->redirectUri.'?code='.$strCode.$strState : $this->redirectUri.'&code='.$strCode.$strState;	
			} else {
				throw new NamedUserException("Return type not supported.");
			}
		}
		
		$this->saved();
	}

	/**
	 * @inheritDoc
	 */
	public function saved() {
		parent::saved();
		
		if($this->goToUrl == ""){
			throw new NamedUserException("An error occured.");
		}
		
		//Redirect to target page
		HeaderUtil::redirect($this->goToUrl);
		exit;
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'showOauthProviderCopyright' 	=> $this->showCopyright,
			'oauth_clientname' 				=> $this->oauthclient->name,
			'oauth_scopes'					=> ['identify' => 1, 'email' => (in_array('email', $this->scopes)), 'profile' => (in_array('profile', $this->scopes))]
		]);
	}
		
	private function generateNewIDToken($clientID, $userID, $arrTokenscopes, $strNonce=''){
		$objUser = new User($userID);
		
		$token = array(
				"iss" => LinkHandler::getInstance()->getLink(),
				"aud" => $clientID,
				"iat" => TIME_NOW,
				"nbf" => TIME_NOW-60,
				'exp' => TIME_NOW + 3600,
				"sub" => $userID,
				"name" => $objUser->getUsername(),
				"preferred_username " => $objUser->getUsername(),
				'nonce' => $strNonce,
				"scope" => implode(' ', $arrTokenscopes),
		);
		
		if(in_array('email', $arrTokenscopes)){
			$token['email'] = $objUser->email;
		}
		
		$oauthclient = new Oauthclient($clientID);
		if(!isset($oauthclient->clientSecret)) throw new NamedUserException("An error occured.");
		
		$key = $oauthclient->clientSecret;
		$idToken = JWTToken::encode($token, $key);
		
		return $idToken;
		
	}
	
	private function getScopeString($arrScopes){
		$arrDone = array();
		$arrStrings = [];
		foreach($arrScopes as $key => $strScope){
			if($strScope === 'openid') $strScope = 'identify';
			if(in_array($strScope, $arrDone)) continue;
			
			$arrStrings[] = WCF::getLanguage()->getDynamicVariable('wcf.page.oauthclients.scope.'.$strScope);
			$arrDone[] = $strScope;
		}
		return implode(', ', $arrStrings);
	}
}