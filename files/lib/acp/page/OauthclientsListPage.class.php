<?php
namespace wcf\acp\page;
use wcf\data\oauthclient\OauthclientList;
use wcf\page\SortablePage;

/**
 * Shows the list of Oauth Clients.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthclientsListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.oauthclients.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.canManageOauthclients'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = OauthclientList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['name', 'oauthclientID', 'redirectUrl', 'lastModified'];
}
