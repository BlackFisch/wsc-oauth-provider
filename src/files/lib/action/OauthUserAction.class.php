<?php
namespace wcf\action;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\SystemException;
use wcf\system\menu\user\profile\UserProfileMenu;
use wcf\system\payment\type\IPaymentType;
use wcf\util\CryptoUtil;
use wcf\util\HTTPRequest;
use wcf\util\JSON;
use wcf\util\StringUtil;
use wcf\data\oauthtoken\Oauthtoken;
use wcf\data\oauthclient\Oauthclient;
use wcf\data\user\User;
use wcf\util\JWTToken;

/**
 * Handles the Oauth User API
 *
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthUserAction extends AbstractAction {
	
	/**
	 * Object that contains the User associated with the access token
	 * @var object
	 */
	public $userProfile;
	
	/**
	 * Which token type is used
	 * @var string
	 */
	public $tokenType = 'token';
	
	/**
	 * Contains the allowed scopes
	 * @var array
	 */
	protected $allowedScopes = ['identify', 'openid', 'email', 'profile'];
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		try {
			$strBearer = "";
			
			//Get Bearer Token from Header
			$strHeaderBearer = $this->getBearerToken();
			if($strHeaderBearer == null){
				//Get Bearer Token from Param
				if(isset($_REQUEST['access_token']) && strlen($_REQUEST['access_token'])){
					$strBearer = $_REQUEST['access_token'];
				}
				
			} else {
				$strBearer = $strHeaderBearer;
			}
			
			if($strBearer === ""){
				throw new SystemException("invalid_authorization");
			}
			
			if(strpos($strBearer, '.') !== false){
				$this->tokenType = 'jwt';
			}
			
			if($this->tokenType == 'jwt'){
				$tokenParts = JWTToken::decode($strBearer);
				
				if(isset($tokenParts['body']->aud)){
					$oauthclient = new Oauthclient($tokenParts['body']->aud);
					if(!isset($oauthclient->clientSecret)){
						throw new SystemException("invalid_authorization");
					}
					
					$key = $oauthclient->clientSecret;
					
					if(!JWTToken::verify($strBearer, $key)){
						throw new SystemException("invalid_authorization");
					}
					
				} else {
					throw new SystemException("invalid_authorization");
				}
				
				//Check scopes
				$arrScopes = (isset($tokenParts['body']->scope)) ? explode(' ', $tokenParts['body']->scope) : array('identify');
				
				foreach($arrScopes as $strScope){
					if(!in_array($strScope, $this->allowedScopes)) {
						throw new SystemException("invalid_authorization");
					}
				}
				
				$userID = $tokenParts['body']->sub;
				
				$objUser = new User($userID);
				if(!$objUser->userID){
					throw new SystemException("invalid_authorization");
				}
				
			} else {
				//Check if Bearer exists
				$oauthtoken = new Oauthtoken($strBearer);
				if(!$oauthtoken->oauthtokenID){
					throw new SystemException("invalid_authorization");
				}
				
				//Check if Bearer is outdated
				$intExpires = $oauthtoken->time + $oauthtoken->expires;
				if($intExpires < TIME_NOW){
					throw new SystemException("invalid_authorization");
				}
				
				//Deliver the data from the scope
				$arrScopes = JSON::decode($oauthtoken->scope);
				
				$arrResponse = [];
				
				$objUser = new User($oauthtoken->userID);
				if(!$objUser->userID){
					throw new SystemException("invalid_authorization");
				}

			}
			
			$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($objUser->userID);
			
			if(in_array('identify', $arrScopes) || in_array('openid', $arrScopes)){
				$arrResponse['sub'] = $objUser->userID;
				$arrResponse['userID'] = $objUser->userID;
				$arrResponse['name'] = $objUser->getUsername();
				$arrResponse['username'] = $objUser->getUsername();
				$arrResponse['preferred_username'] = $objUser->getUsername();
				
				$arrResponse['picture'] = $this->userProfile->getAvatar()->getURL();
				$arrResponse['picture_height'] = $this->userProfile->getAvatar()->getHeight();
				$arrResponse['picture_width'] = $this->userProfile->getAvatar()->getWidth();		
			}
			
			if(in_array('email', $arrScopes)){
				$arrResponse['email'] = $objUser->email;
			}

			if(in_array('profile', $arrScopes)){
				$arrProfile = [];
				
				$arrAllowedKeys = ['banned', 'banReason', 'banExpires', 'oldUsername', 'registrationDate', 'cssClassName', 'groupID'];
				
				$arrProfileData = $this->userProfile->getData();
				foreach($arrProfileData as $key => $val){
					if(StringUtil::startsWith($key, "userOption") || in_array($key, $arrAllowedKeys)){
						$arrProfile[$key] = $val;
					}
				}
				
				$arrProfile['birthday'] = $this->userProfile->getBirthday();
				$arrProfile['age'] = $this->userProfile->getAge();
				$arrProfile['rank'] = $this->userProfile->getRank();
				$arrProfile['title'] = $this->userProfile->getTitle();
				$arrProfile['userTitle'] = $this->userProfile->getUserTitle();
				$arrProfile['signature'] = $this->userProfile->getSignature();
				
				$arrResponse['profile'] = $arrProfile;
			}
			
			// allow font fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			
			echo JSON::encode($arrResponse);
		}
		catch (SystemException $e) {
			@header('HTTP/1.1 400 Bad Request');
			// allow font fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			echo JSON::encode(["error" => $e->getMessage()]);
			$e->getExceptionID(); // log error
			exit;
		}
	}
	
	/**
	 * Helper function to get the Authorization Header
	 * 
	 * @return NULL|string
	 */
	private function getAuthorizationHeader(){
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		}
		else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			//print_r($requestHeaders);
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}
	

	/**
	 * Extracts the Authorization Header and returns the Bearer Token
	 * 
	 * @return mixed|NULL
	 */
	public function getBearerToken() {
		$headers = $this->getAuthorizationHeader();
		$matches = [];
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}
