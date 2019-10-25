<?php
namespace wcf\data\oauthclient;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes Oauthclient-related actions.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 *
 * @method	Oauthclient		create()
 * @method	OauthclientEditor[]	getObjects()
 * @method	OauthclientEditor	getSingleObject()
 */
class OauthclientAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.canManageOauthclients'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['delete'];
}
