<?php
namespace wcf\data\oauthclient;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;

/**
 * Represents an Oauthclient.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @property-read	integer		$OauthclientID	unique id of the Oauthclient
 * @property-read	string		$firstName	first name of the Oauthclient
 * @property-read	string		$lastName	last name of the Oauthclient
 */
class Oauthclient extends DatabaseObject implements IRouteController {
	/**
	 * Returns the first and last name of the Oauthclient if a Oauthclient object is treated as a string.
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
		return $this->name;
	}
}
