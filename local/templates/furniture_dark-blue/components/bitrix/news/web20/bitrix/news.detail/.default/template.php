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
            if ($item['PROPERTY_MULTIPLE'] == 'Y') {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']][] = $item['PROPERTY_VALUE'];
            } else {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']] = $item['PROPERTY_VALUE'];
            }

            unset($item['PROPERTY_CODE'],$item['PROPERTY_VALUE'],$item['PROPERTY_MULTIPLE']);

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
            if ($item['PROPERTY_MULTIPLE'] == 'Y') {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']][] = $item['PROPERTY_VALUE'];
            } else {
                $properties[$item['XML_ID']][$item['PROPERTY_CODE']] = $item['PROPERTY_VALUE'];
            }

            unset($item['PROPERTY_CODE'],$item['PROPERTY_VALUE'],$item['PROPERTY_MULTIPLE']);

            if ($items[$item['XML_ID']]) continue;
            $items[$item['XML_ID']] = $item;
        }

        if (empty($items)) die;

        foreach ($items as &$item) {
            $item['PROPERTIES'] = $properties[$item['XML_ID']];
        } unset($item);

        $translations = $items;
        unset($items);

        pre($translations);
        pre($episodes);


        die;
        $episodes = \Umino\Anime\Tables\EpisodesTable::getList([
            'filter' => [
                'SERIAL_XML_ID' => $arResult['XML_ID'],
                'ACTIVE' => 'Y',
                [
                    'LOGIC' => 'OR',
                    ['TYPE' => false],
                    ['TYPE' => ['OVA']],
                ],
//                'EPISODES_COUNT' => $arResult['PROPERTIES']['EPISODES_AIRED']['VALUE']
            ],
        ])->fetchAll();

        $translations = [];
        foreach ($episodes as $episode) {
            $translations[] = $episode['TRANSLATION_XML_ID'];
        }

        $translations = array_unique($translations);



        $translationsDB = CIBlockElement::GetList([],[
            'XML_ID' => $translations
        ],false,false, ['NAME', 'XML_ID', 'PROPERTY_TYPE']);

        $translations = [];
        while ($translation = $translationsDB->GetNext()) {
            $translations[$translation['XML_ID']] = [
                'NAME' => $translation['NAME'],
                'XML_ID' => $translation['XML_ID'],
                'TYPE' => $translation['PROPERTY_TYPE_VALUE'],
            ];
        }



        $result = [];
        foreach ($episodes as $episode) {
            $translation = $translations[$episode['TRANSLATION_XML_ID']]['NAME'];
            if ($episode['EPISODES']) {
                $result[$translation][$episode['SEASON']] = [
                    'TYPE' => $episode['TYPE']?:'Эпизоды',
                    'EPISODES' => $episode['EPISODES'],
                ];
            } else {
                $result[$translation] = [
                    'TYPE' => $episode['TYPE'],
                    'LINK' => $episode['ANIME_LINK'],
                ];
            }
        }

        ?>

        <? foreach ($result as $translation => $seasons): ?>
            <div>
                <? if (empty($seasons['LINK'])): ?>
                    <h3><?=$translation?></h3>
                    <? foreach ($seasons as $season => $item): ?>
                        <div>
<!--                            <br>-->
<!--                            <h4>Сезон: --><?//=$season?><!--</h4>-->
                            <h5><?=$item['TYPE']?></h5>
                            <ul>
                                <? foreach ($item['EPISODES'] as $episode => $link): ?>
                                    <li>
                                        <span data-link="<?=$link?>"><?=$episode?></span>
                                    </li>
                                <? endforeach ?>
                            </ul>
                        </div>
                    <? endforeach ?>
                <? else: ?>
                    <? if ($item['TYPE']): ?>
                        <h5><?=$item['TYPE']?></h5>
                    <? endif; ?>
                    <ul>
                        <li>
                            <span data-link="<?=$seasons['LINK']?>"><?=$translation?></span>
                        </li>
                    </ul>
                <? endif ?>
            </div>
            <hr>
        <? endforeach; ?>
        </div>
        <br>
        <br>
        <div data-player></div>
<!--
        <iframe src="//kodik.info/serial/9153/cddf6e92e8e68f202dc7c8cac0c4ed7c/720p" width="878" height="493" frameborder="0" allowfullscreen=""></iframe>
        <iframe src="//kodik.info/season/82121/c9b10ebb0eb71f979dbd5518218ab5c3/720p" width="878" height="493" frameborder="0" allowfullscreen=""></iframe>
        <iframe src="//kodik.info/seria/1014571/22df9cdc28101f4acf4fb57ac537dcd5/720p" width="878" height="493" frameborder="0" allowfullscreen=""></iframe>
-->
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
        $('[data-link]').on('click', function () {
            let link = $(this).data('link')
            let player = $('[data-player]');
            let width = $(player).width();
            let height = 720 / 1280 * width;

            $('[data-player]').html('<iframe src="'+link+'?hide_selectors=true" width="'+width+'" height="'+height+'" frameborder="0" allowfullscreen=""></iframe>')

            function kodikMessageListener(message) {
                switch (message.data.key) {
                    case 'kodik_player_play':
                        console.log(message);
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
