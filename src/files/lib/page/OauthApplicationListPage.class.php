<?php
namespace wcf\page;
use wcf\data\oauthauthorize\OauthauthorizeList;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Page that lists the authorized Oauth Applications
 *
 * @author		GodMod
 * @copyright	2019 GodMod / EQdkp Plus Team
 * @license		AGPLv3 <https://eqdkp-plus.eu/en/about/license-agpl.html>
 */
class OauthApplicationListPage extends MultipleLinkPage {
    /**
	 * @inheritDoc
	 */
	public $neededModules = [];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.profile.canUseOauth'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = OauthauthorizeList::class;
	
	/**
	 * @inheritDoc
	 */
	public $sortField = 'lastUsed';
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = 'DESC';
	
	/**
	 * Show my copyright
	 * @var boolean
	 */
	public $showCopyright = true;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('userID = ?', [WCF::getUser()->userID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.security.oauthApplicationList');
		
		parent::show();
	}

	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'showOauthProviderCopyright' => $this->showCopyright,
		]);
	}
}