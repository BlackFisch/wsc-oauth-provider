<?php
namespace wcf\data\oauthtoken;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of Oauthtokens.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @method	Oauthtoken		current()
 * @method	Oauthtoken[]	getObjects()
 * @method	Oauthtoken|null	search($objectID)
 * @property	Oauthtoken[]	$objects
 */
class OauthtokenList extends DatabaseObjectList {}
