<?php
namespace wcf\action;
use wcf\system\WCF;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\util\CryptoUtil;
use wcf\util\JSON;
use wcf\util\JWTToken;
use wcf\util\StringUtil;
use wcf\util\UserUtil;
use wcf\data\oauthtoken\Oauthtoken;
use wcf\data\oauthclient\Oauthclient;
use wcf\data\user\User;
use wcf\data\user\authentication\failure\UserAuthenticationFailure;
use wcf\data\oauthauthorize\OauthauthorizeList;
use wcf\data\oauthauthorize\OauthauthorizeAction;
use wcf\data\oauthauthorize\Oauthauthorize;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;

/**
 * Handles the Oauth Tokens Endpoint
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthTokenAction extends AbstractAction {
	
	/**
	 * User supplied Client ID
	 * @var string
	 */
	public $clientID;
	/**
	 * User supplied Client Secret
	 * @var string
	 */
	public $clientSecret;
	/**
	 * User supplied Grant Type
	 * @var string
	 */
	public $grantType;
	/**
	 * User supplied Code
	 * @var string
	 */
	public $code;
	/**
	 * User supplied refresh token
	 * @var string
	 */
	public $refresh_token;
	/**
	 * User supplied Redirect URI
	 * @var string
	 */
	public $redirectUri; 
	/**
	 * User supplied Scope
	 * @var string
	 */
	public $scope;
	
	/**
	 * User supplied Nonce
	 * @var string
	 */
	public $nonce = '';
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		try {
			$arrResponse = array();
			//Check if every needed param is here
			if(!isset($_POST['client_id']) || !strlen($_POST['client_id'])){
				throw new SystemException('invalid_request');
			}
			$this->clientID = urldecode(StringUtil::trim($_POST['client_id']));
						
			if(isset($_POST['grant_type'])){
				$this->grantType = $_POST['grant_type'];
			} else {
				$this->grantType = 'authorization_code';
			}
			
			if($this->grantType === 'authorization_code'){
				if(isset($_POST['client_secret']) && strlen($_POST['client_secret'])){
					$this->clientSecret = urldecode(StringUtil::trim($_POST['client_secret']));
				}
				if(isset($_SERVER['PHP_AUTH_PW']) && strlen($_SERVER['PHP_AUTH_PW'])){
					$this->clientSecret = StringUtil::trim($_SERVER['PHP_AUTH_PW']);
				}
				if(!$this->clientSecret){
					throw new SystemException('invalid_request');
				}
				
				if(!isset($_POST['code']) || !strlen($_POST['code'])){
					throw new SystemException('invalid_request');
				}
				$this->code = urldecode(StringUtil::trim($_POST['code']));
				
				if(!isset($_POST['redirect_uri']) || !strlen($_POST['redirect_uri'])){
					throw new SystemException('invalid_request');
				}
				$this->redirectUri = urldecode(StringUtil::trim($_POST['redirect_uri']));
				
				//check if code exists
				$oauthtoken = new Oauthtoken($this->code);
				if(!$oauthtoken->oauthtokenID){
					throw new SystemException('invalid_grant');
				}
				if($oauthtoken->tokenType != "code"){
					throw new SystemException('invalid_grant');
				}
				
				//check if code is not outdated
				$intExpires = $oauthtoken->time + $oauthtoken->expires;
				if($intExpires < TIME_NOW){
					throw new SystemException('invalid_grant');
				}
				
				$clientID = $oauthtoken->clientID;
				$oauthclient = new Oauthclient($clientID);
				if(!$oauthclient->oauthclientID){
					throw new SystemException('unauthorized_client');
				}
				
				//check if client_id matches given clientID
				if($clientID != $this->clientID){
					throw new SystemException('unauthorized_client');
				}
				
				//check if client_secret matches given secret
				if($oauthclient->clientSecret != $this->clientSecret){
					throw new SystemException('unauthorized_client');
				}
				
				//check if redirect_uri matches saved value
				if(!StringUtil::startsWith($this->redirectUri, $oauthclient->redirectUrl, true)){
					throw new SystemException('unauthorized_client');
				}
				
				$arrTokenscopes = JSON::decode($oauthtoken->scope);
				
				//Return normal token
				$strCode = bin2hex(CryptoUtil::randomBytes(70));
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([], 'create', [
						'data' => [
								'oauthtokenID'	=> $strCode,
								'clientID' 		=> $this->clientID,
								'scope'			=> $oauthtoken->scope,
								'userID'		=> $oauthtoken->userID,
								'time'			=> TIME_NOW,
								'tokenType'		=> 'bearer',
								'expires' 		=> 3600,
						]
				]);
				$objectAction->executeAction();
				
				//Generate a refresh token
				$strRefreshToken = bin2hex(CryptoUtil::randomBytes(70));
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([], 'create', [
						'data' => [
								'oauthtokenID'	=> $strRefreshToken,
								'clientID' 		=> $this->clientID,
								'scope'			=> $oauthtoken->scope,
								'userID'		=> $oauthtoken->userID,
								'time'			=> TIME_NOW,
								'tokenType'		=> 'refresh',
								'expires' 		=> 3630,
						]
				]);
				$objectAction->executeAction();
				
				//Delete Code Token
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([$oauthtoken], 'delete');
				$objectAction->executeAction();
				
				
				//Create access token and send it
				$arrResponse = array("access_token" => $strCode,
						"token_type" 	=> "Bearer",
						"expires_in" 	=> 3600,
						"refresh_token" => $strRefreshToken,
						"scope" 		=> implode(' ', $arrTokenscopes));
				
				if(in_array('openid', $arrTokenscopes)){
					//Return ID Token when openid is used
					$arrResponse['id_token'] = $this->generateIDToken($oauthtoken, $arrTokenscopes);
				}
				
				
						
			} elseif($this->grantType === 'refresh_token'){
				
				if(isset($_POST['client_secret']) && strlen($_POST['client_secret'])){
					$this->clientSecret = urldecode(StringUtil::trim($_POST['client_secret']));
				}
				if(isset($_SERVER['PHP_AUTH_PW']) && strlen($_SERVER['PHP_AUTH_PW'])){
					$this->clientSecret = StringUtil::trim($_SERVER['PHP_AUTH_PW']);
				}
				if(!$this->clientSecret){
					throw new SystemException('invalid_request');
				}
				
				if(!isset($_POST['refresh_token']) || !strlen($_POST['refresh_token'])){
					throw new SystemException('invalid_request');
				}
				$this->refresh_token = urldecode(StringUtil::trim($_POST['refresh_token']));
								
				if(!isset($_POST['redirect_uri']) || !strlen($_POST['redirect_uri'])){
					throw new SystemException('invalid_request');
				}
				$this->redirectUri = urldecode(StringUtil::trim($_POST['redirect_uri']));
				
				if(isset($_POST['nonce'])){
					$this->nonce = urldecode(StringUtil::trim($_POST['nonce']));
				}
				
				//check if code exists
				$oauthtoken = new Oauthtoken($this->refresh_token);
				if(!$oauthtoken->oauthtokenID){
					throw new SystemException('invalid_grant');
				}
				if($oauthtoken->tokenType != "refresh"){
					throw new SystemException('invalid_grant');
				}
				
				//check if code is not outdated
				$intExpires = $oauthtoken->time + $oauthtoken->expires;
				if($intExpires < TIME_NOW){
					throw new SystemException('invalid_grant');
				}
				
				$clientID = $oauthtoken->clientID;
				$oauthclient = new Oauthclient($clientID);
				if(!$oauthclient->oauthclientID){
					throw new SystemException('unauthorized_client');
				}
				
				//check if client_id matches code
				if($clientID != $this->clientID){
					throw new SystemException('unauthorized_client');
				}
				
				//check if client_secret matches code
				if($oauthclient->clientSecret != $this->clientSecret){
					throw new SystemException('unauthorized_client');
				}
				
				//check if redirect_uri matches saved value
				if(!StringUtil::startsWith($this->redirectUri, $oauthclient->redirectUrl, true)){
					throw new SystemException('unauthorized_client');
				}
				
				$arrTokenscopes = JSON::decode($oauthtoken->scope);
								
				if(in_array('openid', $arrTokenscopes)){
					//Return ID Token when openid is used
					$strCode = $this->generateIDToken($oauthtoken, $arrTokenscopes, 3600, $this->nonce);
				} else {
					$strCode = bin2hex(CryptoUtil::randomBytes(70));
					$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([], 'create', [
							'data' => [
									'oauthtokenID'	=> $strCode,
									'clientID' 		=> $this->clientID,
									'scope'			=> $oauthtoken->scope,
									'userID'		=> $oauthtoken->userID,
									'time'			=> TIME_NOW,
									'tokenType'		=> 'bearer',
									'expires' 		=> 3600,
							]
					]);
					$objectAction->executeAction();
				}	
				
				$strRefreshToken = bin2hex(CryptoUtil::randomBytes(70));
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([], 'create', [
						'data' => [
								'oauthtokenID'	=> $strRefreshToken,
								'clientID' 		=> $this->clientID,
								'scope'			=> $oauthtoken->scope,
								'userID'		=> $oauthtoken->userID,
								'time'			=> TIME_NOW,
								'tokenType'		=> 'refresh',
								'expires' 		=> 3630,
						]
				]);
				$objectAction->executeAction();
				
				//Delete Refresh Token
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([$oauthtoken], 'delete');
				$objectAction->executeAction();
				
				
				//Create access token and send it
				$arrResponse = array("access_token" => $strCode,
						"token_type" 	=> "Bearer",
						"expires_in" 	=> 3600,
						"refresh_token" => $strRefreshToken,
						"scope" 		=> implode(' ', $oauthtoken->scope));
				
				
				
			} elseif($this->grantType === 'password'){
				
				$arrAllowedScopes = ['openid', 'identify', 'email', 'profile'];
				
				if(!isset($_POST['password']) || !strlen($_POST['password'])){
					throw new SystemException('invalid_request');
				}
				
				if(!isset($_POST['username']) || !strlen($_POST['username'])){
					throw new SystemException('invalid_request');
				}
				
				$oauthclient = new Oauthclient($this->clientID);
				if(!$oauthclient->oauthclientID){
					throw new SystemException('unauthorized_client');
				}
				
				if(!$oauthclient->password){
					throw new SystemException('unauthorized_client');
				}
				
				if(isset($_POST['nonce'])){
					$this->nonce = urldecode(StringUtil::trim($_POST['nonce']));
				}
				
				if (!empty($_POST['scope'])) {
					$arrScopes = explode(' ', urldecode(StringUtil::trim($_POST['scope'])));
					
					$this->scopes = $arrScopes;
				} else {
					$this->scopes[] = 'identify';
				}
				
				//Check Scopes
				foreach($this->scopes as $strScope){
					if(!in_array($strScope, $arrAllowedScopes)) {
						throw new SystemException('invalid_request');
					}
				}
				
				// check authentication failures
				if (ENABLE_USER_AUTHENTICATION_FAILURE) {
					$failures = UserAuthenticationFailure::countIPFailures(UserUtil::getIpAddress());
					if (USER_AUTHENTICATION_FAILURE_IP_BLOCK && $failures >= USER_AUTHENTICATION_FAILURE_IP_BLOCK) {
						throw new SystemException('invalid_grant');
					}
					if (USER_AUTHENTICATION_FAILURE_IP_CAPTCHA && $failures >= USER_AUTHENTICATION_FAILURE_IP_CAPTCHA) {
						throw new SystemException('invalid_grant');
					}
					else if (USER_AUTHENTICATION_FAILURE_USER_CAPTCHA) {
						if (isset($_POST['username'])) {
							$user = User::getUserByUsername(StringUtil::trim($_POST['username']));
														
							if ($user->userID) {
								$failures = UserAuthenticationFailure::countUserFailures($user->userID);
								if (USER_AUTHENTICATION_FAILURE_USER_CAPTCHA && $failures >= USER_AUTHENTICATION_FAILURE_USER_CAPTCHA) {
									throw new SystemException('invalid_grant');
								}
							}
						}
					}
				}		
				
				
				//Check username and password
				try {
					$objUser = UserAuthenticationFactory::getInstance()->getUserAuthentication()->loginManually($_POST['username'], $_POST['password']);
				}
				catch (UserInputException $e) {
					throw new SystemException('invalid_grant');
				}
				
				$strCode = bin2hex(CryptoUtil::randomBytes(70));
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([], 'create', [
						'data' => [
								'oauthtokenID'	=> $strCode,
								'clientID' 		=> $this->clientID,
								'scope'			=> JSON::encode($this->scopes),
								'userID'		=> $objUser->userID,
								'time'			=> TIME_NOW,
								'tokenType'		=> 'bearer',
								'expires' 		=> 3600,
						]
				]);
				$objectAction->executeAction();					

				
				$strRefreshToken = bin2hex(CryptoUtil::randomBytes(70));
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([], 'create', [
						'data' => [
								'oauthtokenID'	=> $strRefreshToken,
								'clientID' 		=> $this->clientID,
								'scope'			=> JSON::encode($this->scopes),
								'userID'		=> $objUser->userID,
								'time'			=> TIME_NOW,
								'tokenType'		=> 'refresh',
								'expires' 		=> 3630,
						]
				]);
				$objectAction->executeAction();
				
				//Insert authorize to inform about authorization
				$userAuthorizesList = new OauthauthorizeList();
				$userAuthorizesList->getConditionBuilder()->add('userID = ? AND clientID = ?', [$objUser->userID, $this->clientID]);
				$userAuthorizesList->readObjects();
				
				$userAuthorizes = $userAuthorizesList->getObjectIDs();
				
				if(count($userAuthorizes)){
					$userAuthorizeObject = new Oauthauthorize($userAuthorizes[0]);
					
					$strCurrentSavedScopes = JSON::decode($userAuthorizeObject->scope, true);
					$strNewScopes = array_merge($strCurrentSavedScopes, $this->scopes);
					
					$this->objectAction = new OauthauthorizeAction([$userAuthorizeObject], 'update', [
							'data' => [
									'lastUsed'		=> TIME_NOW,
									'scope'			=> JSON::encode(array_unique($strNewScopes)),
							]
					]);
					$this->objectAction->executeAction();
					
				} else {
					$this->objectAction = new OauthauthorizeAction([], 'create', [
							'data' => [
									'clientID' 		=> $this->clientID,
									'scope'			=> JSON::encode($this->scopes),
									'userID'		=> $objUser->userID,
									'time'			=> TIME_NOW,
									'lastUsed'		=> TIME_NOW,
									'dismiss' 		=> 0,
							]
					]);
					$this->objectAction->executeAction();
					
					//Notify user by mail
					if(OAUTHPROVIDER_SEND_EMAILS){
						try {
							$emailData = [
									'scope' => $this->getScopeString($this->scopes),
									'application' => $oauthclient->getTitle(),
							];
							
							$email = new Email();
							$email->addRecipient(new UserMailbox($objUser));
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
				
				//Create access token and send it
				$arrResponse = array(
						"access_token"	=> $strCode,
						"token_type"	=> "Bearer",
						"refresh_token" => $strRefreshToken,
						"expires_in"	=> 3600,
						"scope"			=> implode(' ', $this->scopes),
				);
				
				if(in_array('openid', $this->scopes)){
					//Return ID Token when openid is used
					$arrResponse['id_token'] = $this->generateNewIDToken($this->clientID, $objUser->userID, $this->scopes, $this->nonce);	
				}
				
			}

			// allow fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			
			//send response
			echo JSON::encode($arrResponse);
		}
		catch (SystemException $e) {
			@header('HTTP/1.1 400 Bad Request');
			// allow fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			echo JSON::encode(["error" => $e->getMessage()]);
			$e->getExceptionID(); // log error
			exit;
		}
	}
	
	private function generateIDToken($objToken, $arrTokenscopes, $intValidFor=3600, $strNonce=''){
		$objUser = new User($objToken->userID);
		
		$token = array(
				"iss" => LinkHandler::getInstance()->getLink(),
				"aud" => $objToken->clientID,
				"iat" => TIME_NOW,
				"nbf" => TIME_NOW-60,
				'exp' => TIME_NOW + $intValidFor,
				"sub" => $objToken->userID,
				"name" => $objUser->getUsername(),
				'nonce' => (strlen($strNonce)) ? $strNonce : $objToken->nonce,
				"scope" => implode(' ', $arrTokenscopes),
		);
		
		if(in_array('email', $arrTokenscopes)){
			$token['email'] = $objUser->email;
		}
		
		$oauthclient = new Oauthclient($objToken->clientID);
		if(!isset($oauthclient->clientSecret)) throw new SystemException("unauthorized_client");
		
		$key = $oauthclient->clientSecret;
		$idToken = JWTToken::encode($token, $key);
		
		return $idToken;
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
				'nonce' => $strNonce,
				"scope" => implode(' ', $arrTokenscopes),
		);
		
		if(in_array('email', $arrTokenscopes)){
			$token['email'] = $objUser->email;
		}
		
		$oauthclient = new Oauthclient($clientID);
		if(!isset($oauthclient->clientSecret)) throw new SystemException("unauthorized_client");
		
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
