<?php
namespace wcf\data\oauthauthorize;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit people.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @method static	Oauthauthorize	create(array $parameters = [])
 * @method		Oauthauthorize	getDecoratedObject()
 * @mixin		Oauthauthorize
 */
class OauthauthorizeEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Oauthauthorize::class;
}
