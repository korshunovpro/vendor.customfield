<?php
/**
 * vendor.customfield - дополнительные пользовательские типы полей
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

