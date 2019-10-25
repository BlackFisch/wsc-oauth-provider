<?php
namespace wcf\action;
use wcf\system\exception\SystemException;
use wcf\util\StringUtil;
use wcf\data\oauthtoken\Oauthtoken;

/**
 * Handles the Remove Oauth Tokens Endpoint
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthRevokeTokenAction extends AbstractAction {
	

	public $access_token;
	
	public $refresh_token;
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		try {
			
			if(isset($_POST['access_token']) && strlen($_POST['access_token'])){
				$this->access_token = StringUtil::trim($_POST['access_token']);
			}
			
			if(isset($_POST['refresh_token']) && strlen($_POST['refresh_token'])){
				$this->refresh_token = StringUtil::trim($_POST['refresh_token']);
			}
			
			if($this->access_token){
				$token = new Oauthtoken($this->access_token);
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([$token], 'delete');
				$objectAction->executeAction();			
			}
			
			if($this->refresh_token){
				$token = new Oauthtoken($this->refresh_token);
				$objectAction = new \wcf\data\oauthtoken\OauthtokenAction([$token], 'delete');
				$objectAction->executeAction();
			}

			// allow fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			
			//send response
			#echo JSON::encode($arrResponse);
		}
		catch (SystemException $e) {
			#@header('HTTP/1.1 400 Bad Request');
			// allow fetching from all domains (CORS)
			@header('Access-Control-Allow-Origin: *');
			@header('Content-type: application/json;charset=UTF-8');
			#echo JSON::encode(["error" => $e->getMessage()]);
			$e->getExceptionID(); // log error
			exit;
		}
	}
}
