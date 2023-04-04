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

CJSCore::Init(array("jquery"));
?>
<div class="news-detail">
	<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arResult["PREVIEW_PICTURE"])):?>
		<img
			class="detail_picture"
			border="0"
			src="<?=$arResult["PREVIEW_PICTURE"]["SRC"]?>"
			width="<?=$arResult["PREVIEW_PICTURE"]["WIDTH"]?>"
			height="<?=$arResult["PREVIEW_PICTURE"]["HEIGHT"]?>"
			alt="<?=$arResult["PREVIEW_PICTURE"]["ALT"]?>"
			title="<?=$arResult["PREVIEW_PICTURE"]["TITLE"]?>"
			/>
	<?endif?>
	<?if($arParams["DISPLAY_DATE"]!="N" && $arResult["DISPLAY_ACTIVE_FROM"]):?>
		<span class="news-date-time"><?=$arResult["DISPLAY_ACTIVE_FROM"]?></span>
	<?endif;?>
	<?if($arParams["DISPLAY_NAME"]!="N" && $arResult["NAME"]):?>
		<h3><?=$arResult["NAME"]?></h3>
	<?endif;?>
	<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arResult["FIELDS"]["PREVIEW_TEXT"]):?>
		<p><?=$arResult["FIELDS"]["PREVIEW_TEXT"];unset($arResult["FIELDS"]["PREVIEW_TEXT"]);?></p>
	<?endif;?>
	<?if($arResult["NAV_RESULT"]):?>
		<?if($arParams["DISPLAY_TOP_PAGER"]):?><?=$arResult["NAV_STRING"]?><br /><?endif;?>
		<?echo $arResult["NAV_TEXT"];?>
		<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?><br /><?=$arResult["NAV_STRING"]?><?endif;?>
	<?elseif($arResult["DETAIL_TEXT"] <> ''):?>
		<?echo $arResult["DETAIL_TEXT"];?>
	<?else:?>
		<?echo $arResult["PREVIEW_TEXT"];?>
	<?endif?>
	<div style="clear:both"></div>
	<br />
	<?
	foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty):?>

		<?=$arProperty["NAME"]?>:&nbsp;
		<?if(is_array($arProperty["DISPLAY_VALUE"])):?>
			<?=implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);?>
		<?else:?>
			<?=$arProperty["DISPLAY_VALUE"];?>
		<?endif?>
		<br />
	<?endforeach;
	if(array_key_exists("USE_SHARE", $arParams) && $arParams["USE_SHARE"] == "Y")
	{
		?>
		<div class="news-detail-share">
			<noindex>
			<?
			$APPLICATION->IncludeComponent("bitrix:main.share", "", array(
					"HANDLERS" => $arParams["SHARE_HANDLERS"],
					"PAGE_URL" => $arResult["~DETAIL_PAGE_URL"],
					"PAGE_TITLE" => $arResult["~NAME"],
					"SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
					"SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
					"HIDE" => $arParams["SHARE_HIDE"],
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);
			?>
			</noindex>
		</div>
		<?
	}
	?>
    <br>
    <br>
    <style>
        .translation-list {
            padding: 0;
            margin: 0;
        }
        .translation-list li {
            display: inline-block;
            padding: 0;
            margin: 5px;
        }
        .translation-list span {
            padding: 5px 10px;
            margin: 0;
            border: 1px solid #0A3A68;
            display: inline-block;
        }
        .translation-list span[data-active],
        .translation-list span:hover {
            background-color: #0A3A68;
            color: #fff;
        }
        [data-translation] {
            cursor: pointer;
        }
    </style>
    <div>
        <div><b>Выберите озвучку: </b></div>
        <ul class="translation-list">
        <?
        $dataList = \Umino\Anime\Tables\DataTable::getList([
                'filter' => [
                    'INFO.XML_ID' => $arResult['EXTERNAL_ID']
                ],
            'select' => [
                'XML_ID',
                'TRANSLATION_TITLE' => 'TRANSLATION.TITLE',
            ],
        ])->fetchAll();

        foreach ($dataList as $data): ?>
            <li><span data-translation data-season="" data-xml-id="<?=$data['XML_ID']?>"><?=$data['TRANSLATION_TITLE']?></span></li>
        <? endforeach; ?>
        </ul>
        <br>
        <br>
        <div data-player></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('[data-translation]').on('click', function () {
            if ($(this).attr('data-active') !== undefined) return;
            $('[data-translation]').removeAttr('data-active');
            $(this).attr('data-active','');

            $.ajax({
                url: '<?=$templateFolder?>/ajax.php',
                method: 'get',
                dataType: 'html',
                data: {
                    XML_ID: $(this).data('xml-id'),
                    SEASON: <?=$arResult['PROPERTIES']['SEASON']['VALUE']?>,
                },
                async: false,
                success: function(data) {
                    $('[data-player]').html(data);
                }
            });

            $('html, body').animate({
                scrollTop: $('[data-player]').offset().top // класс объекта к которому приезжаем
            }, 300);
        });
    });
</script>
