<?php
namespace wcf\data\oauthtoken;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes oauthtoken-related actions.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 *
 * @method	Oauthtoken		create()
 * @method	OauthtokenEditor[]	getObjects()
 * @method	OauthtokenEditor	getSingleObject()
 */
class OauthtokenAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = [];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = [];
}
