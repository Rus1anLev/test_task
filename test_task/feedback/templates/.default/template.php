<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>
<div class="mfeedback">
<?php 
if ($_REQUEST['application'] == 'added') 
	{ ?>
	<h3 class="success"><?=$arParams["OK_TEXT"]?></h3>
<? }
else 
{

if (!$arResult['ERRORS']['required_dont_show'] && !$arResult['ERRORS']["EMAIL_FIELD_FOR_VALIDATE"]) { // Если нет ошибок в настройке компонента
	if (!empty($arResult["ERRORS"])) // Если нет ошибок для заполнения формы
	{?>
		<div class="errors_block">
			<h4>Заявка не отправлена из-за ошибок при заполнении формы. </h4>
		</div>
	<? }
?>

<form action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">
	<?=bitrix_sessid_post()?>
	<?php foreach ($arResult["FORM_FIELDS"] as $key => $value): ?>
		
		<?php 
			switch ($value['PROPERTY_TYPE']) {

				case 'string': ?>
					<label for="<?=$value['ID']?>">
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					<input type="text" id="<?=$value['ID']?>" name="PROPERTY[<?=$value['ID']?>]" value="<?=$value["DEFAULT_VALUE"]?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
					</label>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;

				case 'number': ?>
					<label for="<?=$value['ID']?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					<input type="number" id="<?=$value['ID']?>" name="PROPERTY[<?=$value['ID']?>]" value="<?=$value["DEFAULT_VALUE"]?>">
					</label>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;

				case 'email': ?>
					<label for="<?=$value['ID']?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					<input type="email" id="<?=$value['ID']?>" name="PROPERTY[<?=$value['ID']?>]" value="<?=$value["DEFAULT_VALUE"]?>">
					</label>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;
				
				case 'dropdown': ?>
					<label for="<?=$value['ID']?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					<select name="PROPERTY[<?=$value['ID']?>]" id="<?=$value['ID']?>">
						<option>(выбрать)</option>
						<?php foreach ($value['VALUES'] as $prop_key => $prop_value): ?>
							<option value="<?=$prop_value["ID"]?>" <?=$prop_value["DEF"] ? " selected=\"selected\"" : ""?>><?=$prop_value["VALUE"]?></option>
						<?php endforeach ?>
					</select>
					</label>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;

				case 'multiselect': ?>
					<label for="<?=$value['ID']?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					</label>
					<select name="PROPERTY[<?=$value['ID']?>][]" id="<?=$value['ID']?>" multiple>
						<option>(выбрать)</option>
						<?php foreach ($value['VALUES'] as $prop_key => $prop_value): ?>
							<option value="<?=$prop_value["ID"]?>" <?=$prop_value["DEF"] ? " selected=\"selected\"" : ""?>><?=$prop_value["VALUE"]?></option>
						<?php endforeach ?>
					</select>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;

				case 'checkboxes': ?>
					<label for="<?=$value['ID']?>">
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					
						</label>
						<br>
						<?php foreach ($value['VALUES'] as $prop_key => $prop_value): ?>
							<label for="checkbox_<?=$prop_value["ID"]?>"><?=$prop_value["VALUE"]?></label>
							<?=$prop_value["NAME"]?>
							<input type="checkbox" name="PROPERTY[<?=$value['ID']?>][<?=$prop_value["ID"]?>]" id="checkbox_<?=$prop_value["ID"]?>" value="<?=$prop_value["ID"]?>" <?=$prop_value["DEF"] ? " checked=\"checked\"" : ""?>>

						<?php endforeach ?>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;
				

				case 'radio': ?>
					<label for="<?=$value['ID']?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					</label>
						<br>
						<?php foreach ($value['VALUES'] as $prop_key => $prop_value): ?>
							
							<label for="radio_<?=$prop_value["ID"]?>"><?=$prop_value["VALUE"]?></label>
							<?=$prop_value["NAME"]?>
							<input type="radio" name="PROPERTY[<?=$value['ID']?>]" id="radio_<?=$prop_value["ID"]?>" value="<?=$prop_value['ID']?>" <?=$prop_value["DEF"] ? " checked=\"checked\"" : ""?>>

						<?php endforeach ?>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;


				case 'textarea': ?>
					<label for="<?=$value['ID']?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
						<?=$value['NAME']?><?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					</label>
					<textarea name="PROPERTY[<?=$value['ID']?>]" id="<?=$value['ID']?>" cols="30" rows="<?=$value['ROW_COUNT']?>"><?=$value["DEFAULT_VALUE"]?></textarea>
					<br>
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;

				
				case 'file': ?>
					<label for="<?=$value['ID']?>" <?=$value["IS_REQUIRED"] ? " required=\"required\"" : ""?>>
						<?=$value['NAME']?> 
						<?php if ($value['FILE_TYPE']): ?>
						(Поддерживаемые типы файлов: <?=implode(', ', $value['FILE_TYPE'])?>)	
						<?php endif ?>
						<?=$value["IS_REQUIRED"] ? " <span class='mf-req'>*</span>" : ""?>
					</label>
					<input type="file" id="<?=$value['ID']?>" name="<?=$value['ID']?>">
					<?=$arResult["ERRORS"][$value["ID"]] ? "<div class='error'>".$arResult["ERRORS"][$value["ID"]]."</div>" : ""?>
					<? break;
				
				
				

				case 'undefined': ?>
					<p>Тип поля не поддерживается компонентом</p>
					<? break;
				
				default: ?>
					
					<? break;
			}
		 ?>

	<?php endforeach ?>
	
	<?if($arParams["USE_CAPTCHA"] == "Y"):?>
	<div class="mf-captcha">
		<div class="mf-text">Защита от роботов</div>
		<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>">
		<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA">
		<div class="mf-text">Введите текст с картинки<span class="mf-req">*</span></div>
		<input type="text" name="captcha_word" size="30" maxlength="50" value="">
		<?=$arResult["ERRORS"]['captcha'] ? "<div class='error'>".$arResult["ERRORS"]['captcha']."</div>" : ""?>
	</div>
	<?endif;?>

	<input type="submit" name="submit" value="Отправить">

</form>
<?
}}
 ?>
</div>

