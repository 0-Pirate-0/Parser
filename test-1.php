<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
?>
<h1>Импорт комментариев к товарам</h1>
<p>
    Источник: data.csv - файл должен располагаться в <b>/upload/</b><br>
    Кодировка: UTF-8<br>
    Разделитель ячеек: |<br>
</p>
результат работы...<br>
<?
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
        //значение "не показывать комментарий"
        $status = '1';
        if ($el['status_com']=='On'){
            //значение "показывать комментарий"
            $status = '2';
        }
        $PROP = array();
        $PROP['IS_SHOW'] = $status; 
		$PROP['NAME'] = $el['name'];
		$PROP['PREVIEW'] = $el['preview_text'];
        $PROP['DETAIL'] = $el['detail_text'];
        $PROP['PROP1'] = $el['prop1'];
        $PROP['PROP2'] = $el['prop2'];
        
        $arFields = Array(
        'ACTIVE' => 'Y',
        'IBLOCK_ID' => 4,
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
?>