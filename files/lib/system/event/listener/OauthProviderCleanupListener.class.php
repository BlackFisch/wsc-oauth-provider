<?php
namespace wcf\system\event\listener;
use wcf\system\WCF;

/**
 * Listener for Cleanup-Cronjob
 *
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthProviderCleanupListener implements IParameterizedEventListener {
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		
        // clean up codes
		$sql = "DELETE FROM wcf".WCF_N."_oauthtoken WHERE (`time` + `expires`) < ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            TIME_NOW
		]);
		
	}
}