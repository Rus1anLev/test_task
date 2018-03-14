<?php
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */


if(!CModule::IncludeModule("iblock")) // если модуль инфоблоков не установлен - выдаем ошибку
{
	ShowError("Модуль инфоблоков не установлен");
	return;
}

if (empty($arParams["EMAIL_FIELD_FOR_VALIDATE"])) {
	$error["EMAIL_FIELD_FOR_VALIDATE"] = true;
	echo "<div class='error'><h4>Не отмечено поле email, для валидации по маске в настройках компонента</h4></div>";
}

$IBLOCK_ID = $arParams['IBLOCK_ID'];

$obCache = new CPHPCache();

$cacheLifetime = $arParams["CACHE_TIME"]; //время кеширования

$cache_id = intval($IBLOCK_ID).implode('_', $arParams["DONT_SHOW_PROPERTY_CODES"]); //Идентификатор кеша

$cachePath = "/test_task_feedback_cache/"; //Директория кеша

if ($obCache->InitCache($cacheLifetime, $cache_id, $cachePath)) // если кеш уже существует
{ 
	$arVars = $obCache->GetVars();
	$arResult = $arVars['arResult'];
}
elseif ($obCache->StartDataCache()) // если кеша еще не существует - делаем выборку и помещаем $arResult в кеш 
{ 

	$properties = CIBlockProperty::GetList( // 1. получаем все свойства инфоблока.
					Array("sort"=>"asc"),
					Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID)
				);

	while ($prop = $properties->GetNext())
	{	

		// начало наполнения массива значения свойств инфоблока по умолчанию

		if ($prop["PROPERTY_TYPE"] == "S" || $prop["PROPERTY_TYPE"] == "N") 
		{
			if ($prop["DEFAULT_VALUE"] != "") 
			{
				if ($prop["DEFAULT_VALUE"]["TYPE"] == "TEXT" || $prop["DEFAULT_VALUE"]["TYPE"] == "HTML") {
					$default_prop_values[$prop["ID"]] = $prop["DEFAULT_VALUE"]["TEXT"];
				}
				else 
				{
					$default_prop_values[$prop["ID"]] = $prop["DEFAULT_VALUE"];
				}
				
			} 
			else 
			{
				$default_prop_values[$prop["ID"]] = "";
			}
		}
		if ($prop["PROPERTY_TYPE"] == "N") 
		{
			if ($prop["DEFAULT_VALUE"] != "") 
			{
				$default_prop_values[$prop["ID"]] = $prop["DEFAULT_VALUE"];
			} 
			else 
			{
				$default_prop_values[$prop["ID"]] = "";
			}
		}
		if ($prop["PROPERTY_TYPE"] == "L") 
		{	
			$property_enums = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_ID"=>$prop["ID"])); 
	
			while($enum_fields = $property_enums->GetNext())
			{
				if ($enum_fields["DEF"] != "N") {
					$default_prop_values[$prop["ID"]][] = $enum_fields["ID"];
				}
			}
			if (!isset($default_prop_values[$prop["ID"]])) {
				$default_prop_values[$prop["ID"]] = "";
			}
		}
		if ($prop["PROPERTY_TYPE"] == "F") 
		{
			$default_prop_values[$prop["ID"]] = "";
		}
	
		$arResult["DEFAULT_PROP_VALUERS"] = $default_prop_values;

		// конец наполнения массива значения свойств инфоблока по умолчанию


		// наполнение массива со свойствами, обязательными для заполнения

		if ($prop["IS_REQUIRED"] == "Y") 
		{
			$arResult["REQUIRED_PROPS"][] = $prop["ID"];
		}

		// конец наполнения массива со свойствами, обязательными для заполнения

		// защита от дурака: если в настройках компонента отмечены свойства, которые не выводить в форму,
		// после чего такие свойства были отмечены в инфоблоке как обязательные - выводим ошибку.

		if ($prop["IS_REQUIRED"] == "Y" && in_array($prop["ID"], $arParams["DONT_SHOW_PROPERTY_CODES"])) {
			echo  "<div class='error'><h4>Поле ".$prop["NAME"]." — обязателное и должно учавствовать в форме.</h4></div> Необходимо сделать данное поле необязательным в настройках инфоблока, либо в настройках компонента в поле 'Свойства, НЕ отображающиеся в форме' снять отметку с данного поля.";
			$errors['required_dont_show'] = true;
		}

		// начало наполнения массива для построения формы

		if (in_array($prop["ID"], $arParams["DONT_SHOW_PROPERTY_CODES"])) 
		{   // Если в параметрах указано "не выводить свойство" - оно не будет учавствовать в форме.
			
			continue;
		}

		$form_fields[$prop["ID"]]["ID"] = $prop["ID"];
		$form_fields[$prop["ID"]]["NAME"] = $prop["NAME"];
	
		if ($prop["IS_REQUIRED"] != "N") 
		{
			$form_fields[$prop["ID"]]["IS_REQUIRED"] = $prop["IS_REQUIRED"];
		}
	
		$property_type = getPropertyType( $prop, $arParams );
	
		$form_fields[$prop["ID"]]["PROPERTY_TYPE"] = $property_type;
	
		if ($property_type == "textarea") 
		{
			$form_fields[$prop["ID"]]["ROW_COUNT"] = $prop["ROW_COUNT"];
		}
		if ($prop["DEFAULT_VALUE"] != "") 
		{
			
			if ($prop["DEFAULT_VALUE"]["TYPE"] == "TEXT" || $prop["DEFAULT_VALUE"]["TYPE"] == "HTML") 
			{
				$form_fields[$prop["ID"]]["DEFAULT_VALUE"] = $prop["DEFAULT_VALUE"]["TEXT"];
			} 
			else
			{
				$form_fields[$prop["ID"]]["DEFAULT_VALUE"] = $prop["DEFAULT_VALUE"];
			}
		}

	
		if ($prop['PROPERTY_TYPE'] == "L" ) // если свойство тип список - извлекаем значения
		{
			$property_enums = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC"), Array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_ID"=>$prop["ID"])); 
			// 2. получаем все значения свойств.
	
			while($enum_fields = $property_enums->GetNext())
			{
				$form_fields[$prop["ID"]]["VALUES"][$enum_fields["ID"]]["ID"] = $enum_fields["ID"];
	
				$form_fields[$prop["ID"]]["VALUES"][$enum_fields["ID"]]["VALUE"] = $enum_fields["VALUE"];
	
				if ($enum_fields["DEF"] != "N") 
				{
					$form_fields[$prop["ID"]]["VALUES"][$enum_fields["ID"]]["DEF"] = $enum_fields["DEF"];
				}
			}
	
			if ($property_enums->SelectedRowsCount() == 1 && $form_fields[$prop["ID"]]["PROPERTY_TYPE"] == 'radio') 
			{ // переопределение radio на checkbox, если у свойства только одно значение.

				$form_fields[$prop["ID"]]["PROPERTY_TYPE"] = "checkboxes";
			}
			
		}
		if($prop['PROPERTY_TYPE'] == "F" ) // Если свойство - файл, добавим типы файлов, позовленные для загрузки
		{
			if (!empty($prop['FILE_TYPE'])) 
			{
				$form_fields[$prop["ID"]]['FILE_TYPE'] = explode(', ', $prop['FILE_TYPE']);
			}
		}
	
		$arResult["FORM_FIELDS"] = $form_fields;
		}
		
		// конец наполнения массива для построения формы

		$obCache->EndDataCache(
			array(
				"arResult" => $arResult
			)
		);

} // Конец кеширования данных.

// на выходе имеем 3 подмассива в $arResult:
// $arResult["DEFAULT_PROP_VALUERS"] = в этом массиве хранятся все свойства инфоблока по умолчанию
// $arResult["REQUIRED_PROPS"] = массив с обязательными полями. Согласно этому массиву проводим валидацию
// $arResult["FORM_FIELDS"] = массив со всеми полями формы. Согласно этому массиву строим форму


function getPropertyType( $prop_fields, $arParams ) // метод возвращает тип свойства
{
	switch ($prop_fields['PROPERTY_TYPE']) 
	{
		case 'S':

			if ($prop_fields['ROW_COUNT'] > 1 || $prop_fields["DEFAULT_VALUE"]["TYPE"] == "TEXT" || $prop_fields["DEFAULT_VALUE"]["TYPE"] == "HTML") 
			{
				$property_type = "textarea";
			} 
			else 
			{
				$property_type = "string";
			}
			if (in_array($prop_fields["ID"], $arParams["EMAIL_FIELD_FOR_VALIDATE"])) {
				$property_type = "email";
			}

			break;
		
		case 'L':
			if ($prop_fields['LIST_TYPE'] == 'L' && $prop_fields['MULTIPLE'] == 'N') 
			{
				$property_type = "dropdown";
			}
			if ($prop_fields['LIST_TYPE'] == 'L' && $prop_fields['MULTIPLE'] == 'Y') 
			{	
				$property_type = "multiselect";
			}
			if ($prop_fields['LIST_TYPE'] == 'C' && $prop_fields['MULTIPLE'] == 'Y') 
			{
				$property_type = "checkboxes";
			}
			if ($prop_fields['LIST_TYPE'] == 'C' && $prop_fields['MULTIPLE'] == 'N')
			{
				$property_type = "radio";
			}
			break;
		
		case 'N':
			$property_type = "number";
			break;
		
		case 'F':
			$property_type = "file";
			break;
		
		default:
			$property_type = "undefined";
			break;
	}
	
	return $property_type;
}


// если форма была отправлена
if (check_bitrix_sessid() && isset($_POST['submit'])) 
{	
// начало валидации

	// проверка обязательных полей на непустоту
	foreach ($arResult["REQUIRED_PROPS"] as $key => $value) 
	{ 
	// проверяем, заполнены ли обязательные поля

		if ($arResult["FORM_FIELDS"][$value]["PROPERTY_TYPE"] != "file") // Если свойство не является файлом
		{
			if (!isset($_POST["PROPERTY"][$value]) || empty($_POST["PROPERTY"][$value])) 
			{
				$errors[$value] = "Не заполнено поле ".$arResult["FORM_FIELDS"][$value]["NAME"];
			}
		} 
		else // Если свойство — файл
		{
			if (!isset($_FILES[$value]["name"]) || empty($_FILES[$value]["name"])) 
			{
				$errors[$value] = "Не заполнено поле ".$arResult["FORM_FIELDS"][$value]["NAME"];
			}
		}

	}

	// Проверка POST на предмет правильного заполнения email
	if (!empty($arParams["EMAIL_FIELD_FOR_VALIDATE"])) 
	{
		foreach ($_POST["PROPERTY"] as $key => $value) 
		{
			if ($key == $arParams["EMAIL_FIELD_FOR_VALIDATE"]) 
			{
				if (!isset($errors[$key]) || empty($errors[$key]) ) 
				{
				// Проверка, была ошибка для этого типа поля. Если ошибка уже была - ее и выводим.
					if (!preg_match('/\A[^@]+@([^@\.]+\.)+[^@\.]+\z/', $value)) 
					{
						$errors[$key] = "Некорректно заполнено поле ".$arResult["FORM_FIELDS"][$key]["NAME"];
					}
				}
			}
			if ($arResult['FORM_FIELDS'][$key]['PROPERTY_TYPE'] == 'number') // проверка на поле тип номер
			{
				if (!isset($errors[$key]) || empty($errors[$key]) ) 
				{
					if (!is_numeric($value)) 
					{
						$errors[$key] = "В поле ".$arResult["FORM_FIELDS"][$key]["NAME"]." должно быть число";
					}
				}
			}
		}
	}
	
	// Проверка массива $_FILES на правильность типа

	foreach ($_FILES as $key => $value) 
	{
		if ($_FILES[$key]['name'] != "") // Если файл заполнен
		{
			if (!empty($arResult["FORM_FIELDS"][$key]["FILE_TYPE"])) // Если есть ограничение на тип файла
			{
				if (!in_array(end(explode(".", $value['name'])), $arResult["FORM_FIELDS"][$key]["FILE_TYPE"])) // если тип 	файла не верный
				{
					if (!isset($errors[$key]) || empty($errors[$key]) ) 
					{
					// Проверка, была ошибка для этого типа поля. Если ошибка уже была - ее и выводим.
	
						$errors[$key] = "Неверный тип файла для поля ".$arResult["FORM_FIELDS"][$key]["NAME"];
					}
				}
			}
		}
	}

	if ($arParams["MAX_FILE_SIZE"] > 0) // Проверяем максимальный размер файла
	{ 
		foreach ($_FILES as $key => $value) 
		{
			if ($value['size'] > $arParams["MAX_FILE_SIZE"]) 
			{
				if (!isset($errors[$key]) || empty($errors[$key]) ) 
				{	
					$max_file_size_MB = $arParams["MAX_FILE_SIZE"] / 1000 / 1000;
					$errors[$key] = "Превышен размер файла для загрузки в поле ".$arResult["FORM_FIELDS"][$key]["NAME"].". Максимальный размер: ".$max_file_size_MB."МБ";
				}
			}
		}
	}
	// проверка капчи 
	if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"])) 
	{	
		if (empty($_REQUEST["captcha_word"])) {
			$errors['captcha'] = "Введите текст с картинки";
		} else {
			$errors['captcha'] = "Тескт с картинки введен не верно";
		}
	}


	if (!empty($errors)) // Если нет ошибок - валидация прошла успешно. Если есть - отдаем их в шаблон компонента.
	{
		$arResult["ERRORS"] = $errors;
		$data_is_valid = false;
	}
	else 
	{
		$data_is_valid = true;
	}
// конец валидации


	if ($data_is_valid) // если валидация прошла успешно
	{	

		// Переопределяем массив со свойствами по умолчанию значениями из $_POST
		foreach ($_POST["PROPERTY"] as $key => $value) 
		{
			$arResult["DEFAULT_PROP_VALUERS"][$key] = $value;
		}
		// Складываем файлы в массив свойств по умолчанию
		foreach ($_FILES as $key => $value) 
		{
			if ($value['error'] == 0) 
			{
				$arResult["DEFAULT_PROP_VALUERS"][$key] = $value;
			}
		}

		// Добавляем новый элемент в инфоблок
		$el = new CIBlockElement;
		$arLoadProductArray = Array(
		  "IBLOCK_ID"      => $IBLOCK_ID,
		  "PROPERTY_VALUES"=> $arResult["DEFAULT_PROP_VALUERS"],
		  "NAME"           => "Заявка с формы обратной связи",
		  "ACTIVE"         => "N",
		  );
		
		if($application_id = $el->Add($arLoadProductArray))
		{
		  	// Отправляем пиьсма
			mail($arParams["EMAIL_TO"], "Новая заявка с сайта", "Поступила новая заявка с сайта. Номер заявки: ".$application_id);
			mail($_POST[$arParams["EMAIL_FIELD_FOR_VALIDATE"]], "Новая заявка с сайта", "Ваша заявка принята, ей присвоен номер ".$application_id);

			// Редирект на Ok page

			LocalRedirect($APPLICATION->GetCurPageParam("application=added"));
		}
		else
		{
		  $arResult["ERRORS"]['IB_ERROR'] = $el->LAST_ERROR;
		}

		
	}


}

if (!empty($errors)) 
{
	$arResult["ERRORS"] = $errors;
}

// генерация капчи

if($arParams["USE_CAPTCHA"] == "Y")
	$arResult["capCode"] =  htmlspecialcharsbx($APPLICATION->CaptchaGetCode());

$this->IncludeComponentTemplate();

?>