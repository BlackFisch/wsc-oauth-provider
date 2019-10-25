<?php
namespace wcf\data\oauthtoken;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit oauthtokens.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 * 
 * @method static	Oauthtoken	create(array $parameters = [])
 * @method		Oauthtoken	getDecoratedObject()
 * @mixin		Oauthtoken
 */
class OauthtokenEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Oauthtoken::class;
}
