#!/usr/bin/php
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www"; // или можно еще так: realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);

//пишем код
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
$handle = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/test.csv', 'r');
if ($handle) {
    $counter = 0;
    $keys = array();
    $data = array();
    while (($buffer = fgets($handle)) !== false) {
        $counter++; 
        //удаляем из прочитанной строки все символы переноса
        $buffer = str_replace(array("\r\n", "\r", "\n"), '', $buffer); 
        $str =explode(';', $buffer);
        if ($counter==1){
            $keys = $str;
        }
        else{
            $el = array();
            foreach ($str as $key=>$item){
                $el[$keys[$key]] = $item;
            }
            $data[] = $el;
        }
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
    
    CModule::IncludeModule('iblock');
    
    foreach ($data as $key=>$el){
        $bs = new CIBlockElement;
        $PROP = array();
		    $PROP['NAME'] = $el['name'];
		    $PROP['PREVIEW'] = $el['preview_text'];
        $PROP['DETAIL'] = $el['detail_text'];
        $PROP['PROP1'] = $el['prop1'];
        $PROP['PROP2'] = $el['prop2'];
        
        $arFields = Array(
        'ACTIVE' => 'Y',
        'IBLOCK_ID' => 22, // Нужно заменить на нужный id инфоблока 
        'NAME' => 'cmt_'.$el['id'],
        'XML_ID' => 'cmt_'.$el['id'],
        'PROPERTY_VALUES'=> $PROP,
        );
        if($PRODUCT_ID = $bs->Add($arFields)){
            echo $key.'.New ID: '.$PRODUCT_ID.'(XML_ID = cmt_'.$el['id'].')<br>';
        }
        else{
            echo $key.'.Error: '.$bs->LAST_ERROR.'<br>';
        }
    }
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"); //если ругается на него php, то комментим и дальше пользуемся