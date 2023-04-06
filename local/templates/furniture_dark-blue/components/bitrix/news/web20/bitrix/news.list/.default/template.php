<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div style="display: grid; grid-template-columns: 1fr 1fr;gap: 15px;">
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>
<?foreach($arResult["ITEMS"] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
        <div id="<?=$this->GetEditAreaId($arItem['ID']);?>" style="padding: 3px; border: 1px solid rgba(0,0,0,0.2)">
            <img    style="object-fit: cover; object-position: center;"
                    border="0"
                    width="100%"
                    height="400"
                    src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
                    alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>"
                    title="<?=$arItem["PREVIEW_PICTURE"]["TITLE"]?>"
            />
            <div>
                <?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
                    <?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
                        <a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><b><?echo $arItem["NAME"]?></b></a><br />
                    <?else:?>
                        <b><?echo $arItem["NAME"]?></b><br />
                    <?endif;?>
                <?endif;?>
                <?foreach($arItem["FIELDS"] as $code=>$value):?>
                    <?=GetMessage("IBLOCK_FIELD_".$code)?>:&nbsp;<?=$value;?>
                <?endforeach;?>
                <?foreach($arItem["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>
                    <div>
                        <b><?=$arProperty["NAME"]?>:</b>
                        <?if(is_array($arProperty["DISPLAY_VALUE"])):?>
                            <?=implode(" / ", $arProperty["DISPLAY_VALUE"]);?>
                        <?else:?>
                            <?=$arProperty["DISPLAY_VALUE"];?>
                        <?endif?>
                    </div>
                <?endforeach;?>
            </div>
        </div>

<?endforeach;?>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>
