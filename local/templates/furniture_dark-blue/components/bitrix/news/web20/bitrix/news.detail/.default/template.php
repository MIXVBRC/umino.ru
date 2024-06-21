<? use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Umino\Anime\Core;
use Umino\Anime\Shikimori\Tables\ShikimoriLoadTable;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
	<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arResult["DETAIL_PICTURE"])):?>
		<img
			class="detail_picture"
			border="0"
			src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>"
			width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>"
			height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>"
			alt="<?=$arResult["DETAIL_PICTURE"]["ALT"]?>"
			title="<?=$arResult["DETAIL_PICTURE"]["TITLE"]?>"
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
	foreach($arResult["DISPLAY_PROPERTIES"] as $pid=>$arProperty): break?>

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
        /*[data-tabs] [data-tabs-header] [data-tabs-footer] [data-tabs-item] [data-tabs-active]*/
        [data-tabs] [data-tabs-header] [data-tabs-active] {
            display: inline-block !important;
            background-color: #0A3A68;
            color: #fff;
        }

        [data-tabs] [data-tabs-footer] [data-tabs-active] {
            display: block !important;
        }

        [data-tabs] [data-tabs-footer] [data-tabs-item] {
            display: none;
        }

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

        .video-player ul {
            padding: unset;
        }

        .video-player li {
            cursor: pointer;
        }

        [data-player] {
            padding: 15px;
        }

        /*.video-player span:not([data-active]) + ul {*/
        /*    display: none;*/
        /*}*/
    </style>
    <div class="video-player">
        <div><b>Выберите озвучку: </b></div>
        <div class="translation-list">
        <?

        $episodeIBID = \Umino\Anime\Shikimori\Manager::getIBID(\Umino\Anime\Shikimori\Import\Episode::getName());

        $entity = ElementTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => $episodeIBID,
                'ELEMENT_PROPERTY.VALUE' => $arResult['XML_ID']
            ])
            ->setSelect([
                'XML_ID',
            ])
            ->registerRuntimeField('PROPERTY', [
                'data_type' => PropertyTable::class,
                'reference' => Join::on('ref.IBLOCK_ID', 'this.IBLOCK_ID')
                    ->whereIn('ref.CODE', ['ANIME']),
                'join_type' => 'left',
            ])
            ->registerRuntimeField('ELEMENT_PROPERTY', [
                'data_type' => ElementPropertyTable::class,
                'reference' => Join::on('ref.IBLOCK_PROPERTY_ID', 'this.PROPERTY.ID')
                    ->whereColumn('ref.IBLOCK_ELEMENT_ID', 'this.ID'),
                'join_type' => 'inner',
            ]);

        $episodesXmlIds = array_column($query->fetchAll(), 'XML_ID');

        $entity = ElementTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => $episodeIBID,
                'XML_ID' => $episodesXmlIds
            ])
            ->setSelect([
                'XML_ID',
                'NAME',
                'PROPERTY_MULTIPLE' => 'PROPERTY.MULTIPLE',
                'PROPERTY_CODE' => 'PROPERTY.CODE',
                'PROPERTY_VALUE' => 'ELEMENT_PROPERTY.VALUE',
                'PROPERTY_DESCRIPTION' => 'ELEMENT_PROPERTY.DESCRIPTION',
            ])
            ->registerRuntimeField('PROPERTY', [
                'data_type' => PropertyTable::class,
                'reference' => Join::on('ref.IBLOCK_ID', 'this.IBLOCK_ID'),
                'join_type' => 'left',
            ])
            ->registerRuntimeField('ELEMENT_PROPERTY', [
                'data_type' => ElementPropertyTable::class,
                'reference' => Join::on('ref.IBLOCK_PROPERTY_ID', 'this.PROPERTY.ID')
                    ->whereColumn('ref.IBLOCK_ELEMENT_ID', 'this.ID'),
                'join_type' => 'left',
            ]);

        $items = [];
        $properties = [];
        foreach ($query->fetchAll() as $item) {

            if ($item['PROPERTY_DESCRIPTION']) {
                $item['PROPERTY_VALUE'] = [
                    'VALUE' => $item['PROPERTY_VALUE'],
                    'DESCRIPTION' => $item['PROPERTY_DESCRIPTION'],
                ];
            }

            if ($item['PROPERTY_MULTIPLE'] == 'Y') {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']][] = $item['PROPERTY_VALUE'];
            } else {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']] = $item['PROPERTY_VALUE'];
            }

            unset($item['PROPERTY_CODE'],
                $item['PROPERTY_VALUE'],
                $item['PROPERTY_MULTIPLE'],
                $item['PROPERTY_DESCRIPTION']
            );

            if ($items[$item['XML_ID']]) continue;
            $items[$item['XML_ID']] = $item;
        }

        if (empty($items)) die;

        foreach ($items as &$item) {
            $item['PROPERTIES'] = $properties[$item['XML_ID']];
        } unset($item);

        $episodes = $items;
        unset($items);

        $translationsXmlIds = array_column(array_column($episodes, 'PROPERTIES'), 'TRANSLATION');

        $translationsIBID = \Umino\Anime\Shikimori\Manager::getIBID(\Umino\Anime\Shikimori\Import\Translation::getName());

        $entity = ElementTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => $translationsIBID,
                'XML_ID' => $translationsXmlIds
            ])
            ->setSelect([
                'XML_ID',
                'NAME',
                'PROPERTY_MULTIPLE' => 'PROPERTY.MULTIPLE',
                'PROPERTY_CODE' => 'PROPERTY.CODE',
                'PROPERTY_VALUE' => 'ELEMENT_PROPERTY.VALUE',
                'PROPERTY_DESCRIPTION' => 'ELEMENT_PROPERTY.DESCRIPTION',
            ])
            ->registerRuntimeField('PROPERTY', [
                'data_type' => PropertyTable::class,
                'reference' => Join::on('ref.IBLOCK_ID', 'this.IBLOCK_ID'),
                'join_type' => 'left',
            ])
            ->registerRuntimeField('ELEMENT_PROPERTY', [
                'data_type' => ElementPropertyTable::class,
                'reference' => Join::on('ref.IBLOCK_PROPERTY_ID', 'this.PROPERTY.ID')
                    ->whereColumn('ref.IBLOCK_ELEMENT_ID', 'this.ID'),
                'join_type' => 'inner',
            ]);

        $items = [];
        $properties = [];
        foreach ($query->fetchAll() as $item) {

            if ($item['PROPERTY_DESCRIPTION']) {
                $item['PROPERTY_VALUE'] = [
                    'VALUE' => $item['PROPERTY_VALUE'],
                    'DESCRIPTION' => $item['PROPERTY_DESCRIPTION'],
                ];
            }

            if ($item['PROPERTY_MULTIPLE'] == 'Y') {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']][] = $item['PROPERTY_VALUE'];
            } else {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']] = $item['PROPERTY_VALUE'];
            }

            unset($item['PROPERTY_CODE'],
                $item['PROPERTY_VALUE'],
                $item['PROPERTY_MULTIPLE'],
                $item['PROPERTY_DESCRIPTION']
            );

            if ($items[$item['XML_ID']]) continue;
            $items[$item['XML_ID']] = $item;
        }

        if (empty($items)) die;

        foreach ($items as &$item) {
            $item['PROPERTIES'] = $properties[$item['XML_ID']];
        } unset($item);

        $translations = $items;
        unset($items);

        foreach ($episodes as $episode) {

            $translation = $episode['PROPERTIES']['TRANSLATION'];
            $type = $episode['PROPERTIES']['TYPE'];

            $translations[$translation]['ANIME'][$type][] = $episode;
        }



        ?>

            <br>
            <div class="tab" data-tabs>
                <div class="tab_header" data-tabs-header>
                    <? foreach ($translations as $xmlId => $translation): ?>
                        <span style="margin: 5px; cursor: pointer" data-tabs-item="<?= $xmlId ?>"><?= $translation['NAME'] ?></span>
                    <? endforeach ?>
                </div>
                <div class="tab_body" data-tabs-footer>

                    <? foreach ($translations as $xmlId => $translation): ?>

                        <div data-tabs-item="<?= $xmlId ?>">
                            <hr>
                            <ul>

                            <? foreach ($translation['ANIME'] as $type => $items): ?>

                                <? if ($type == 'anime'): ?>
                                    <h3>Фильмы</h3>
                                <? else: ?>
                                    <h3>Эпизоды</h3>
                                <? endif ?>

                                <? foreach ($items as $item): ?>

                                    <? if ($type == 'anime'): ?>
                                        <li>
                                            <span data-link="<?=$item['PROPERTIES']['LINK']?>"
                                                  title="<?=$item['PROPERTIES']['KODIK_TITLE_ORIG']?>">
                                                <?=$item['PROPERTIES']['KODIK_TITLE']?>
                                            </span>
                                        </li>
                                    <? else: ?>
                                        <? foreach ($item['PROPERTIES']['EPISODES'] as $episode): ?>
                                            <li>
                                                <span data-link="<?=$episode['VALUE']?>"><?=$episode['DESCRIPTION']?></span>
                                            </li>
                                        <? endforeach; ?>
                                    <? endif ?>

                                <? endforeach; ?>

                            <? endforeach; ?>

                            </ul>
                            <hr>
                        </div>
                    <? endforeach; ?>

                </div>

            </div>



        </div>
        <br>
        <br>
        <div data-player></div>

        <script type="text/javascript">

        </script>
    </div>
</div>

<!--<script type="application/ld+json">-->
<!--    {-->
<!--        "@context": "https://schema.org",-->
<!--        "@type": "TVSeries",-->
<!--        "actor": [-->
<!--            {-->
<!--                "@type": "Person",-->
<!--                "name": "Justin Chambers"-->
<!--            },-->
<!--            {-->
<!--                "@type": "Person",-->
<!--                "name": "Jessica Capshaw"-->
<!--            }-->
<!--        ],-->
<!--        "author": {-->
<!--            "@type": "Person",-->
<!--            "name": "Shonda Rimes"-->
<!--        },-->
<!--        "name": "Greys Anatomy",-->
<!--        "containsSeason": [-->
<!--            {-->
<!--                "@type": "TVSeason",-->
<!--                "datePublished": "2005-05-22",-->
<!--                "name": "Season 1",-->
<!--                "numberOfEpisodes": "14"-->
<!--            },-->
<!--            {-->
<!--                "@type": "TVSeason",-->
<!--                "datePublished": "2006-05-14",-->
<!--                "episode": {-->
<!--                    "@type": "TVEpisode",-->
<!--                    "episodeNumber": "1",-->
<!--                    "name": "Episode 1"-->
<!--                },-->
<!--                "name": "Season 2",-->
<!--                "numberOfEpisodes": "27"-->
<!--            }-->
<!--        ]-->
<!--    }-->
<!--</script>-->

<script>
    document.addEventListener('DOMContentLoaded', function () {

        $('[data-tabs] [data-tabs-header] [data-tabs-item]').on('click', function () {
            $('[data-tabs] [data-tabs-active]').removeAttr('data-tabs-active');
            $(this).attr('data-tabs-active', '');
            $('[data-tabs] [data-tabs-footer] [data-tabs-item='+$(this).data('tabs-item')+']').attr('data-tabs-active', '');
        });



        $('[data-link]').on('click', function () {
            let link = $(this).data('link')
            let player = $('[data-player]');
            let width = $(player).width();
            let height = 720 / 1280 * width;

            $('[data-player]').html('<iframe src="'+link+'?hide_selectors=true" width="'+width+'" height="'+height+'" frameborder="0" allowfullscreen=""></iframe>')

            function kodikMessageListener(message) {
                switch (message.data.key) {
                    // case 'kodik_player_play':
                    //     console.log(message.data);
                    case 'kodik_player_time_update':
                        console.log(message.data);
                }
            }

            if (window.addEventListener) {
                window.addEventListener('message', kodikMessageListener);
            } else {
                window.attachEvent('onmessage', kodikMessageListener);
            }
        });

        //$('[data-translation]').on('click', function () {
        //    if ($(this).attr('data-active') !== undefined) return;
        //    $('[data-translation]').removeAttr('data-active');
        //    $(this).attr('data-active','');
        //
        //    $.ajax({
        //        url: '<?//=$templateFolder?>///ajax.php',
        //        method: 'get',
        //        dataType: 'html',
        //        data: {
        //            XML_ID: $(this).data('xml-id'),
        //            SEASON: <?//=$arResult['PROPERTIES']['SEASON']['VALUE']?>//,
        //            EPISODE: 1,
        //        },
        //        async: false,
        //        success: function(data) {
        //            $('[data-player]').html(data);
        //        }
        //    });
        //
        //    $('html, body').animate({
        //        scrollTop: $('[data-player]').offset().top // класс объекта к которому приезжаем
        //    }, 300);
        //});
    });
</script>
