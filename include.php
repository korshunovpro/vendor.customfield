<?php
/**
 * vendor.customfield - шаблон модуля для кода
 *
 * @author Sergey Korshunov <sergey@korshunov.pro>
 * @copyright 2019 Sergey Korshunov
 */

\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'iblock', 'OnIBlockPropertyBuildList',
    array(
        'Vendor\CustomField\ListDateField',
        'GetUserTypeDescription'
    )
);

