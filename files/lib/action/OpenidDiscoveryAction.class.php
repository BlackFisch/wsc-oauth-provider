<?php
namespace wcf\action;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\util\JSON;


/**
 * Handles the Oauth User API
 *
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OpenidDiscoveryAction extends AbstractAction {
	
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		try {
			$arrResponse = array(
					"issuer" =>  LinkHandler::getInstance()->getLink(),
					"authorization_endpoint" => LinkHandler::getInstance()->getLink('OauthConsent'),
					"token_endpoint" => LinkHandler::getInstance()->getLink('OauthToken'),
					"userinfo_endpoint" => LinkHandler::getInstance()->getLink('OauthUser'),
					"scopes_supported" => array('openid', 'identify', 'email', 'profile'),
					"response_types_supported" => array('code', 'token', 'id_token'),
					"id_token_signing_alg_values_supported" => array('HS256'),
					"subject_types_supported" => array('public'),
					"token_endpoint_auth_methods_supported" => array('client_secret_post', 'client_secret_basic'),
					"jwks_uri" => 'https://www.googleapis.com/oauth2/v3/certs',
					"claims_supported" => array('aud', 'email', 'exp', 'iat', 'iss', 'name', 'sub', 'nonce', 'nbf', 'data', 'preferred_username', 'picture', 'scope', 'profile'),
			);
			
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
	
}