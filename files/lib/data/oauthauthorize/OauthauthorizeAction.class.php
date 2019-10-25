<?php
namespace wcf\data\oauthauthorize;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Executes Oauthauthorize-related actions.
 * 
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 *
 * @method	Oauthauthorize		create()
 * @method	OauthauthorizeEditor[]	getObjects()
 * @method	OauthauthorizeEditor	getSingleObject()
 */
class OauthauthorizeAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['user.profile.canUseOauth'];
	
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $object) {
			if ($object->userID != WCF::getUser()->userID) throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		foreach($this->objectIDs as $intObjectID){
			//get client-ID and User-ID
			$authorize = new Oauthauthorize($intObjectID);
			$clientID = $authorize->clientID;
			$userID = $authorize->userID;
			
			$sql = "DELETE FROM wcf".WCF_N."_oauthtoken WHERE userID = ? AND clientID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
					$userID,
					$clientID
			]);
		}

		return parent::delete();
	}
}
