<?php
/**
 * vendor.customfield - шаблон модуля для кода
 *
 * @author Sergey Korshunov <sergey@korshunov.pro>
 * @copyright 2019 Sergey Korshunov
 */

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class vendor_customfield extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        require(__DIR__.'/version.php');

        $this->MODULE_ID = str_replace('_', '.', __CLASS__);
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('VENDOR_CUSTOMFIELD_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('VENDOR_CUSTOMFIELD_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('VENDOR_CUSTOMFIELD_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('VENDOR_CUSTOMFIELD_PARTNER_URI');

        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS='Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    public function DoInstall()
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

}