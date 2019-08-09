<?php
/**
 * vendor.customfield - шаблон модуля для кода
 *
 * @author Sergey Korshunov <sergey@korshunov.pro>
 * @copyright 2019 Sergey Korshunov
 */

namespace Vendor\CustomField;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 *
 * Class ListDateField
 * @package Vendor\CustomField
 */
class ListDateField extends \Bitrix\Main\UserField\TypeBase
{
    const USER_TYPE_ID = 'VendorListDateField';

    /**
     * @var array
     */
    static $data = [];
    static $event = [];

    /**
     * @return array
     */
    public function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'L',
            'USER_TYPE'     => 'HTML',
            'USER_TYPE_ID'  => self::USER_TYPE_ID,
            'DESCRIPTION'   => GetMessage('USER_TYPE_ENUM_DESCRIPTION'),
            'BASE_TYPE'     => \CUserTypeManager::BASE_TYPE_ENUM,
            'GetPropertyFieldHtmlMulty' => array('Vendor\CustomField\ListDateField', 'GetPropertyFieldHtml'),
            'GetPropertyFieldHtml'      => array('Vendor\CustomField\ListDateField', 'GetPropertyFieldHtml'),
            'ConvertToDB'               => array('Vendor\CustomField\ListDateField', 'ConvertToDB'),
            'ConvertFromDB'             => array('Vendor\CustomField\ListDateField', 'ConvertFromDB'),
        );
    }

    /**
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    public function ConvertFromDb($arProperty, $value)
    {
        return $value;
    }

    /**
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    public function ConvertToDB($arProperty, $value)
    {
        if (isset($value['VALUE']['VALUE'])) {
            $value['VALUE'] = $value['VALUE']['VALUE'];
        }

        /**
         * save DESCRIPTION 
         */
        if ($arProperty['LIST_TYPE'] === 'C') {
            if (!empty($value['VALUE'])) {
                self::$data[$arProperty['ID']][$value['VALUE']] = [
                    'VALUE' => $value['VALUE'],
                    'DESCRIPTION' => (!empty($value['DESCRIPTION']) ? $value['DESCRIPTION'] : ''),
                ];
            }

            if (!empty(self::$data[$arProperty['ID']]) && !in_array($arProperty['ID'], self::$event)) {
                \Bitrix\Main\EventManager::getInstance()->addEventHandler(
                    'iblock', 'OnAfterIBlockElementSetPropertyValues',
                    function($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $PROPERTY_CODE) use ($arProperty) {
                        self::saveDescription($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $arProperty);
                    }
                );
                \Bitrix\Main\EventManager::getInstance()->addEventHandler(
                    'iblock', 'OnAfterIBlockElementSetPropertyValuesEx',
                    function($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $PROPERTY_CODE) use ($arProperty) {
                        self::saveDescription($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $arProperty);
                    }
                );
                self::$event[] = $arProperty['ID'];
            }
        }

        return $value;
    }

    /**
     * @param $ELEMENT_ID
     * @param $IBLOCK_ID
     * @param $PROPERTY_VALUES
     * @param $arProperty
     */
    public static function saveDescription($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $arProperty) {
        global $DB;

        $result = [];
        \CIBlockElement::GetPropertyValuesArray(
            $result,
            $IBLOCK_ID,
            ['ID' => $ELEMENT_ID],
            ['ID' => [$arProperty['ID']]],
            []
        );

        $VALUE_ENUM_ID = $result[$ELEMENT_ID][$arProperty['CODE']]['VALUE_ENUM_ID'];
        $PROPERTY_VALUE_ID = $result[$ELEMENT_ID][$arProperty['CODE']]['PROPERTY_VALUE_ID'];

        if($arProperty['MULTIPLE'] != 'Y') {
            $VALUE_ENUM_ID = [$VALUE_ENUM_ID];
            $PROPERTY_VALUE_ID = [$PROPERTY_VALUE_ID];
        }

        foreach($VALUE_ENUM_ID as $k=>$valueEnum) {
            if (!empty(self::$data[$arProperty['ID']][$valueEnum])) {
                $valueId = (int)$PROPERTY_VALUE_ID[$k];
                $valueDescription = self::$data[$arProperty['ID']][$valueEnum]['DESCRIPTION'];
                /**
                 * SQL
                 * @todo: невозможно сделать по api на данный момент, еще возможное решение, хранить доп поля в отдельной таблице
                 */
                $sql = 'UPDATE b_iblock_element_property'
                    . ' SET DESCRIPTION=\'' . $DB->ForSql($valueDescription, 255) . '\''
                    . ' WHERE ID=' . $valueId;
                $DB->Query($sql);
            }
        }
    }


    /**
     * @param $arProperty
     * @param $values
     * @param $strHTMLControlName
     * @return false|string
     */
    public function GetPropertyFieldHtml($arProperty, $values, $strHTMLControlName)
    {

        if (!is_array($values)) {
            $values = [];
        } elseif (isset($values['VALUE'])) {// если 1 элемент, а не массив значений
            $values = [$values];
        }        

        $name = htmlspecialcharsbx('PROP[' . $arProperty['ID'] . ']');

        // если чекбокс
        if ($arProperty['LIST_TYPE'] === 'C') {
            $bInitDef = false;

            $descValues = [];
            foreach($values as $key => $value) {
                if (is_array($value) && array_key_exists('DESCRIPTION', $value)) {
                    $descValues[$value['VALUE']] = $value['DESCRIPTION'];
                }
            }
            
            foreach($values as $key => $value) {
                if (is_array($value) && array_key_exists('VALUE', $value)) {
                    $values[$key] = $value['VALUE'];
                }
            }

            $id = $arProperty['ID'];
            $multiple = $arProperty['MULTIPLE'];
            $html = '';
            $prop_enums = \CIBlockProperty::GetPropertyEnum($id, ['SORT' => 'ASC', 'VALUE' => 'ASC', 'ID' => 'ASC']);

            $cnt = 0;
            $checked = false;
            while ($ar_enum = $prop_enums->Fetch()) {

                if ($bInitDef) {
                    $sel = ($ar_enum['DEF'] == 'Y');
                } else {
                    $sel = in_array($ar_enum['ID'], $values);
                }

                if ($sel) $checked = true;

                $name_desc = htmlspecialcharsbx('DESCRIPTION_PROP[' . $arProperty['ID'] . '][' . $cnt . ']');

                $uniq = self::getUniq();
                $html .= '<tr>' 
                        . '<td width="50%">' 
                            . '<input '
                                . 'type="' . (($multiple == 'Y') ? 'checkbox' : 'radio') . '" '
                                . 'name="' . $name . '[' . $cnt . ']" '
                                . 'id="' . $uniq . '" '
                                . 'value="' . htmlspecialcharsbx($ar_enum['ID']) . '"' . ($sel ? ' checked' : '') . '>'
                            . '<label for="' . $uniq . '">' . htmlspecialcharsex($ar_enum['VALUE']) . '</label>'
                        . '</td>' 
                        . '<td width="50%">'
                            . \CAdminCalendar::CalendarDate(
                                $name_desc,
                                $descValues[$ar_enum['ID']],
                                19,
                                true
                            )
                        . '</td>' 
                    . '</tr>';

                $cnt++;
            }

            $noInput = '';
            $name_desc = htmlspecialcharsbx('DESCRIPTION_PROP[' . $arProperty['ID'] . '][' . $cnt . ']');
            if ($multiple != 'Y') {
                $uniq = self::getUniq();
                $noInput = '<tr>'
                    . '<td width="50%">' 
                            . '<input '
                                . 'type="radio"'
                                . 'name="' . $name . '[' . $cnt . ']" '
                                . 'id="' . $uniq . '" '
                                . 'value=""' . (!$checked ? ' checked' : '') . '>'
                            . '<label for="' . $uniq . '">' . GetMessage('USER_TYPE_ENUM_NO_VALUE') . '</label>'
                        . '</td>' 
                        . '<td width="50%">'
                            . '<input type="hidden" name="' . $name_desc . '" value="">'
                        . '</td>' 
                    . '</tr>';
            }

            $return = '<table>' . $noInput.  $html . '</table>';
            
            if(!empty($strHTMLControlName['MODE']) && $strHTMLControlName['MODE'] == 'iblock_element_admin') {
                return $return;
            } else {
                echo $return;
            }
        } else { //if ($arProperty['LIST_TYPE'] === 'C') {
            if(!empty($strHTMLControlName['MODE']) && $strHTMLControlName['MODE'] == 'iblock_element_admin') {
                ob_start();
                _ShowListPropertyField($name, $arProperty, $values);
                $return = ob_get_contents();
                ob_end_clean();
                return $return;
            } else {
                _ShowListPropertyField($name, $arProperty, $values);
            }
        }
    }

    /**
     * @return string
     */
    protected static function getUniq()
    {
        return md5(uniqid(rand(), true));
    }

}