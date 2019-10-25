<?php
namespace wcf\data\oauthclient;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit people.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @method static	Oauthclient	create(array $parameters = [])
 * @method		Oauthclient	getDecoratedObject()
 * @mixin		Oauthclient
 */
class OauthclientEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Oauthclient::class;
}
