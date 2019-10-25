<?php
namespace wcf\data\oauthauthorize;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\system\request\IRouteController;
use wcf\util\JSON;
use wcf\data\oauthclient\Oauthclient;

/**
 * Represents a Oauthauthorize.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @property-read	integer		$OauthauthorizeID	unique id of the Oauthauthorize
 * @property-read	string		$firstName	first name of the Oauthauthorize
 * @property-read	string		$lastName	last name of the Oauthauthorize
 */
class Oauthauthorize extends DatabaseObject implements IRouteController {
	/**
	 * Returns the first and last name of the Oauthauthorize if a Oauthauthorize object is treated as a string.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {

	}
	
	/**
	 * Returns the name of the client
	 * 
	 * @return string
	 */
	public function getClientName(){
		$objClient = new Oauthclient($this->clientID);
		$strName = $objClient->name;
		return $strName;
	}
	
	/**
	 * Returns the scope with HTML for the application list
	 * 
	 * @return string
	 */
	public function getScopesHtml(){
		$arrScopes = JSON::decode($this->scope);
		$arrDone = array();
		$out = '<ul>';
		foreach($arrScopes as $key => $strScope){
			if($strScope === 'openid') $strScope = 'identify';
			if(in_array($strScope, $arrDone)) continue;
			
			$out .= '<li>'.WCF::getLanguage()->getDynamicVariable('wcf.page.oauthclients.scope.'.$strScope).'</li>';
			$arrDone[] = $strScope;
		}
		$out .= '</ul>';
		return $out;
	}
}
