<?php
namespace wcf\data\oauthclient;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of people.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @method	Oauthclient		current()
 * @method	Oauthclient[]	getObjects()
 * @method	Oauthclient|null	search($objectID)
 * @property	Oauthclient[]	$objects
 */
class OauthclientList extends DatabaseObjectList {}
