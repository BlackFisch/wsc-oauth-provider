<?php
namespace wcf\data\oauthauthorize;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of people.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @method	Oauthauthorize		current()
 * @method	Oauthauthorize[]	getObjects()
 * @method	Oauthauthorize|null	search($objectID)
 * @property	Oauthauthorize[]	$objects
 */
class OauthauthorizeList extends DatabaseObjectList {}
