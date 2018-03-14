<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
$arFilter = Array("TYPE_ID" => "FEEDBACK_FORM", "ACTIVE" => "Y");
if($site !== false)
	$arFilter["LID"] = $site;

$arEvent = Array();
$dbType = CEventMessage::GetList($by="ID", $order="DESC", $arFilter);
while($arType = $dbType->GetNext())
	$arEvent[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["SUBJECT"];


// получаем типы инфоблока и инфоблок
if(!CModule::IncludeModule("iblock"))
	return;

if($arCurrentValues["IBLOCK_ID"] > 0)
{
	$arIBlock = CIBlock::GetArrayByID($arCurrentValues["IBLOCK_ID"]);

	$bWorkflowIncluded = ($arIBlock["WORKFLOW"] == "Y") && CModule::IncludeModule("workflow");
	$bBizproc = ($arIBlock["BIZPROC"] == "Y") && CModule::IncludeModule("bizproc");
}
else
{
	$bWorkflowIncluded = CModule::IncludeModule("workflow");
	$bBizproc = false;
}

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}


$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues['IBLOCK_ID']));
while ($arr=$rsProp->Fetch())
{
	$arProperty[$arr["ID"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S", "F")))
	{
		$arProperty_LNSF[$arr["ID"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}

$rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arCurrentValues['IBLOCK_ID'], "PROPERTY_TYPE" => "S"));
while ($arr=$rsProp->Fetch())
{	
	$arProperty[$arr["ID"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S", "F")))
	{
		$arProperty_validate_email[$arr["ID"]] = "[".$arr["CODE"]."] ".$arr["NAME"];
	}
}


$arComponentParameters = array(
	"GROUPS" => array(
		"FIELDS" => array(
			"NAME" => "Поля",
			"SORT" => "300",
		),
		"TITLES" => array(
			"NAME" => "Заголовки",
			"SORT" => "1000",
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("DVS_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),

		"IBLOCK_ID" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("DVS_IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
		),
		"USE_CAPTCHA" => Array(
			"NAME" => "Использовать CAPTCHA", 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y", 
			"PARENT" => "BASE",
		),
		"OK_TEXT" => Array(
			"NAME" => GetMessage("DVS_OK_MESSAGE"), 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("DVS_OK_TEXT"), 
			"PARENT" => "BASE",
		),
		"EMAIL_TO" => Array(
			"NAME" => GetMessage("DVS_EMAIL_TO"), 
			"TYPE" => "STRING",
			"DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")), 
			"PARENT" => "BASE",
		),
		"DONT_SHOW_PROPERTY_CODES" => array( 
			"PARENT" => "FIELDS",
			"NAME" => "Свойства, НЕ отображающиеся в форме",
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"SIZE" => 6,
			"VALUES" => $arProperty_LNSF,
		),
		"EMAIL_FIELD_FOR_VALIDATE" => array( 
			"PARENT" => "FIELDS",
			"NAME" => "Поле email, для валидации по маске (на него же будет уходить письмо пользователю)",
			"TYPE" => "LIST",
			"SIZE" => 6,
			"VALUES" => $arProperty_validate_email,
		),
		"MAX_FILE_SIZE" => array( 
			"PARENT" => "FIELDS",
			"NAME" => "Максимальный размер загружаемого файла в байтах (0 - размер ограничивается параметрами сервера)",
			"TYPE" => "S",
			"MULTIPLE" => "N",
			"SIZE" => 6,
			"DEFAULT"=> 0, 
		),

		"CACHE_TIME"  =>  array("DEFAULT"=>36000000),

	)
);


?>