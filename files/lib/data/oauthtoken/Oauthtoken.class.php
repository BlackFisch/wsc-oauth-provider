<?php
namespace wcf\data\oauthtoken;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;

/**
 * Represents an oauthtoken.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @property-read	integer		$oauthtokenID	unique id of the Oauthtoken
 */
class Oauthtoken extends DatabaseObject implements IRouteController {
	/**
	 * Returns the first and last name of the Oauthtoken if a Oauthtoken object is treated as a string.
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
}
