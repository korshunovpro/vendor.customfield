# vendor.customfield - дополнительные пользовательские типы полей

Список полей: 
1) Чекбоксы с датой. Как стандартный список, но с опцией выводить как флажки, появится поле для ввода даты. 

По сути, обертка вокруг типа Список, можно переключить тип Список на новый тип, все значения сохранятся.



Пример:
------

1) Загрузить в /local/modules/

2) Установить в админке

3) Для использования подключить в init.php

```php
\Bitrix\Main\Loader::includeModule('vendor.customfield');
````