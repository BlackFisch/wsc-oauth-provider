<?php
namespace wcf\action;
use wcf\system\exception\SystemException;
use wcf\util\JSON;
use wcf\util\JWTToken;
use wcf\util\StringUtil;
use wcf\data\oauthtoken\Oauthtoken;
use wcf\data\oauthclient\Oauthclient;


/**
 * Handles the Remove Oauth Tokens Endpoint
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthTokeninfoAction extends AbstractAction {
	
	public $tokenType = 'bearer';
	
	public $accessToken = '';
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		try {
			
			$arrResponse = array();
			//Check if every needed param is here
			if(!isset($_POST['access_token']) || !strlen($_POST['access_token'])){
				throw new SystemException('');
			}
			
			$this->accessToken = StringUtil::trim($_POST['access_token']);
			
			if(strpos($this->accessToken, '.') !== false){
				$this->tokenType = 'jwt';
			}
			
			if($this->tokenType == 'jwt'){
				$tokenParts = JWTToken::decode($this->accessToken);
				
				if(isset($tokenParts['body']->aud)){
					$oauthclient = new Oauthclient($tokenParts['body']->aud);
					
					$key = $oauthclient->clientSecret;
					
					if(!JWTToken::verify($this->accessToken, $key)){
						throw new SystemException("");
					}
					
					$arrResponse = array(
							'sub'		=> $tokenParts['body']->sub,
							'aud'		=> $tokenParts['body']->aud,
							'scope'		=> $tokenParts['body']->scope,
							'type' 		=> 'jwt',
							'exp'		=> $tokenParts['body']->exp,
							"iss" 		=> $tokenParts['body']->iss,
							"aud" 		=> $tokenParts['body']->aud,
							"iat" 		=> $tokenParts['body']->iat,
							"nbf" 		=> $tokenParts['body']->nbf,
							'name'		=> $tokenParts['body']->name,
							'email'		=> (isset($tokenParts['body']->email)) ? $tokenParts['body']->email : '',
					);
					
				} else {
					throw new SystemException("");
				}
				
				
			} else {
				//Check if Bearer exists
				$oauthtoken = new Oauthtoken($this->accessToken);
				if(!$oauthtoken->oauthtokenID){
					throw new SystemException("");
				}
				
				//Check if Bearer is outdated
				$intExpires = $oauthtoken->time + $oauthtoken->expires;
				if($intExpires < TIME_NOW){
					throw new SystemException("");
				}
				
				$arrResponse = array(
						'sub'		=> $oauthtoken->userID,
						'aud'		=> $oauthtoken->clientID,
						'scope'		=> implode(' ', JSON::decode($oauthtoken->scope)),
						'exp'		=> $oauthtoken->time + $oauthtoken->expires,
						'type' 		=> 'bearer',
				);
			}

			// allow fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			
			//send response
			echo JSON::encode($arrResponse);
		}
		catch (SystemException $e) {
			@header('HTTP/1.1 404 Not found');
			// allow fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			#echo JSON::encode(["error" => $e->getMessage()]);
			$e->getExceptionID(); // log error
			exit;
		}
	}
}
