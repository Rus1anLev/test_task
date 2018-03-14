<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule('iblock'))
    die("Модуль инфоблоков не установлен");

    $iblocktype = "test_task";   
   
    $obIBlockType =  new CIBlockType;
    $arFields = Array(
      "ID"=>$iblocktype,
      "SECTIONS"=>"N",
      "LANG"=>Array(
         "ru"=>Array(
            "NAME"=>"Тестовое задание",               
         )   
      )
    );
    $res = $obIBlockType->Add($arFields);
    if(!$res){ 
       $error = $obIBlockType->LAST_ERROR;
    } else {
       $obIblock = new CIBlock;
       $arFields = Array(
          "NAME"=> "Заявки из формы обратной связи",
          "CODE"=> "test_feedback_form_iblock",
          "ACTIVE" => "Y",
          "IBLOCK_TYPE_ID" => $iblocktype,
          "SITE_ID" => Array("s1") //Массив ID сайтов
       );
        $newIblockID = $obIblock->Add($arFields);
        if ($newIblockID <= 0) {
          die("Произошла ошибка, нфоблок не создан");
        } else {
          echo "Создан новый инфоблок, с ID ".$newIblockID;
        }
    }


    // Создание свойств
    
    $arFields = Array( //1. Имя - обязательное, строка; 
      "NAME" => "Имя",
      "ACTIVE" => "Y",
      "SORT" => "100",
      "CODE" => "NAME",
      "PROPERTY_TYPE" => "S",
      "IS_REQUIRED" => "Y",
      "IBLOCK_ID" => $newIblockID
    );
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( //2. Телефон - обязательное, строка; 
      "NAME" => "Телефон",
      "ACTIVE" => "Y",
      "SORT" => "200",
      "CODE" => "PHONE",
      "PROPERTY_TYPE" => "S",
      "IS_REQUIRED" => "Y",
      "IBLOCK_ID" => $newIblockID
    );
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( //3. Email - обязательное, строка с проверкой на корректность введенных данных 
      "NAME" => "Email",
      "ACTIVE" => "Y",
      "SORT" => "300",
      "CODE" => "EMAIL",
      "PROPERTY_TYPE" => "S",
      "IS_REQUIRED" => "Y",
      "IBLOCK_ID" => $newIblockID
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( //4. Индекс - число 
      "NAME" => "Индекс",
      "ACTIVE" => "Y",
      "SORT" => "400",
      "CODE" => "INDEX",
      "PROPERTY_TYPE" => "N",
      "IBLOCK_ID" => $newIblockID
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( //5. Город - выпадающий список, значения: Москва, Владимир, Тула, Тверь; 
      "NAME" => "Город",
      "ACTIVE" => "Y",
      "SORT" => "500",
      "CODE" => "CITY",
      "PROPERTY_TYPE" => "L",
      "IBLOCK_ID" => $newIblockID
      );
    $arFields["VALUES"][0] = Array(
      "VALUE" => "Москва",
      "XML_ID" => "MOSKOW",
      "SORT" => "100"
    );
    $arFields["VALUES"][1] = Array(
      "VALUE" => "Владимир",
      "XML_ID" => "VLADIMIR",
      "SORT" => "200"
    );
    $arFields["VALUES"][2] = Array(
      "VALUE" => "Тула",
      "XML_ID" => "TULA",
      "SORT" => "300"
    );
    $arFields["VALUES"][3] = Array(
      "VALUE" => "Тверь",
      "XML_ID" => "TVER",
      "SORT" => "400"
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( // 6. Адрес - обязательное, текст;
      "NAME" => "Адрес",
      "ACTIVE" => "Y",
      "SORT" => "600",
      "CODE" => "ADDRESS",
      "PROPERTY_TYPE" => "S",
      "ROW_COUNT" => "3",
      "IS_REQUIRED" => "Y",
      "IBLOCK_ID" => $newIblockID
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( //7. Тема обращения - выпадающий список, значения: вопрос, жалоба, предложение, другая (значение по умолчанию); 
      "NAME" => "Тема обращения",
      "ACTIVE" => "Y",
      "SORT" => "700",
      "CODE" => "SUBJECT",
      "PROPERTY_TYPE" => "L",
      "IBLOCK_ID" => $newIblockID
      );
    $arFields["VALUES"][0] = Array(
      "VALUE" => "вопрос",
      "XML_ID" => "QUESTION",
      "SORT" => "100"
    );
    $arFields["VALUES"][1] = Array(
      "VALUE" => "жалоба",
      "XML_ID" => "COMPLAINT",
      "SORT" => "200"
    );
    $arFields["VALUES"][2] = Array(
      "VALUE" => "предложение",
      "XML_ID" => "SUGGESTION",
      "SORT" => "300"
    );
    $arFields["VALUES"][3] = Array(
      "VALUE" => "другая",
      "DEF" => "Y",
      "XML_ID" => "OTHER",
      "SORT" => "400"
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( //8. Сообщение - обязательное, текст; 
      "NAME" => "Сообщение",
      "ACTIVE" => "Y",
      "SORT" => "800",
      "CODE" => "MESSAGE",
      "PROPERTY_TYPE" => "S",
      "ROW_COUNT" => "3",
      "IS_REQUIRED" => "Y",
      "IBLOCK_ID" => $newIblockID
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);
    
    
    $arFields = Array( //9. Согласны ли вы на обработку ваших персональных данных - обязательное, список / флажки (чекбокс). 
      "NAME" => "Согласны ли вы на обработку ваших персональных данных",
      "ACTIVE" => "Y",
      "SORT" => "900",
      "CODE" => "AGREEMENT",
      "PROPERTY_TYPE" => "L",
      "LIST_TYPE" => "C",
      "IS_REQUIRED" => "Y",
      "IBLOCK_ID" => $newIblockID
      );
    
    $arFields["VALUES"][0] = Array(
      "VALUE" => "Да",
      "XML_ID" => "YES",
      "SORT" => "100"
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);

    
    $arFields = Array( //10. Свойство "опубликовано"
      "NAME" => "Опубликовано",
      "ACTIVE" => "Y",
      "SORT" => "1000",
      "CODE" => "PUBLISHED",
      "PROPERTY_TYPE" => "L",
      "LIST_TYPE" => "C",
      "IBLOCK_ID" => $newIblockID
      );
    
    $arFields["VALUES"][0] = Array(
      "VALUE" => "Да",
      "XML_ID" => "YES",
      "SORT" => "100"
    );
    
    $ibp = new CIBlockProperty;
    $PropID = $ibp->Add($arFields);

?>
