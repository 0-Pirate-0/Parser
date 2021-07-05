<?php
$IBLOCK_ID=2;
$URL="/upload/test.csv";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if(!$USER->CanDoOperation('edit_php'))
    $APPLICATION->AuthForm('Доступ запрещен');

class CSV {

    private $_csv_file = null;

    /**
     * @param string $csv_file  - путь до csv-файла
     */
    public function __construct($csv_file) {
        if (file_exists($csv_file)) { //Если файл существует
            $this->_csv_file = $csv_file; //Записываем путь к файлу в переменную
        }
        else { //Если файл не найден то вызываем исключение
            throw new Exception("Файл \"$csv_file\" не найден");
        }
    }

    /**
     * Метод для чтения из csv-файла. Возвращает массив с данными из csv
     * @return array;
     */
    public function getCSV() {
        $handle = fopen($this->_csv_file, "r"); //Открываем csv для чтения

        $array_line_full = array(); //Массив будет хранить данные из csv
        //Проходим весь csv-файл, и читаем построчно. 3-ий параметр разделитель поля
        $array_title = array();//Массив будет хранить заголовки
        while (($line = fgetcsv($handle, 0, ";")) !== FALSE) {
            if(!$array_title)
            {
                $array_title=$line;
            }
            else
            {
                foreach($line as $cell=>$value)
                {
                    if($array_title[$cell])
                    {
                        $line[$array_title[$cell]]=$value;
                        unset($line[$cell]);
                    }
                }
                $array_line_full[] = $line; //Записываем строчки в массив
            }
        }
        fclose($handle); //Закрываем файл
        return $array_line_full; //Возвращаем прочтенные данные
    }

}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<?
if($_REQUEST['csv']):
    $vremiya_starta = microtime(true);//замер времени исполнения

    $csv = new CSV($_SERVER["DOCUMENT_ROOT"].$URL); //Открываем наш csv
    /**
     * Чтение из CSV
     */
    $get_csv = $csv->getCSV();
    $arFilter["IBLOCK_ID"]=$IBLOCK_ID;

    foreach($get_csv as $value)
    {
        $arFilter["PROPERTY_ARTNUMBER"][]=$value["ARTNUMBER"];
    }
    $res = CIBlockElement::GetList(Array("PROPERTY_ARTNUMBER"=>"ASC"), $arFilter, false,false,array('ID','PROPERTY_ARTNUMBER'));

    while($ar_fields = $res->GetNext())
    {
        $element[$ar_fields["PROPERTY_ARTNUMBER_VALUE"]][]=$ar_fields;
    }

    $el = new CIBlockElement;

    foreach($get_csv as $csv_element)
    {

        if($element[$csv_element["ARTNUMBER"]])
        {

            foreach($element[$csv_element["ARTNUMBER"]] as $ib_element)
            {

                $arLoadProductArray = Array(
                    "DETAIL_PICTURE" => CFile::MakeFileArray($csv_element["PICTURE"]),
                );

                    if($el->Update($ib_element['ID'], $arLoadProductArray,false,true,true))
                        echo "<br>Обновлен элемент : ".$ib_element['ID'];
                    else
                        echo "<br>Ошибка обновления в элемент ".$ib_element['ID'].": ".$el->LAST_ERROR;
            }
        }
    }

    $vremya_okonchaniya = microtime(true);
    $vremya = $vremya_okonchaniya - $vremiya_starta;
    $vremya = round($vremya, 2);

    echo "<br><br>Время выполнения скрипта $vremya секунд(ы)...";
else:
?>
    <form action="" method="post">
        <input type="submit" name="csv" value="Произвести обмен">
    </form>
<?
endif;
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");