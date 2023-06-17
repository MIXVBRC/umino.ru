<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Umino");
?>


<?

function getAnimeCalendar(int $day)
{
    CModule::IncludeModule('iblock');

    $query = new Bitrix\Main\ORM\Query\Query(\Bitrix\Iblock\ElementTable::getEntity());
    $query
        ->setFilter([
            'IBLOCK_ID' => \Umino\Anime\Core::getAnimeIBlockID(),
            [
                'LOGIC' => 'AND',
                ['>ELEMENT_PROPERTY.VALUE' => (new \Bitrix\Main\Type\DateTime())->add($day . ' day')->format($day > 0 ? 'Y-m-d' : 'Y-m-d H:i:s')],
                ['<ELEMENT_PROPERTY.VALUE' => (new \Bitrix\Main\Type\DateTime())->add((++$day) . ' day')->format('Y-m-d')],
            ],
        ])
        ->setOrder([
            'DATE' => 'ASC'
        ])
        ->setSelect([
            'ID',
            'IBLOCK_CODE' => 'IBLOCK.CODE',
            'NAME',
            'ELEMENT_CODE'=>'CODE',
            'LINK' => 'IBLOCK.DETAIL_PAGE_URL',
            'IMAGE' => 'DETAIL_PICTURE',
            'DATE' => 'ELEMENT_PROPERTY.VALUE',
        ])
        ->registerRuntimeField('PROPERTY', [
            'data_type' => \Bitrix\Iblock\PropertyTable::class,
            'reference' => \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_ID', 'this.IBLOCK_ID')
                ->whereIn('ref.CODE', ['NEXT_EPISODE_AT']),
            'join_type' => 'left',
        ])
        ->registerRuntimeField('ELEMENT_PROPERTY', [
            'data_type' => \Bitrix\Iblock\ElementPropertyTable::class,
            'reference' => \Bitrix\Main\ORM\Query\Join::on('ref.IBLOCK_PROPERTY_ID', 'this.PROPERTY.ID')
                ->whereColumn('ref.IBLOCK_ELEMENT_ID', 'this.ID'),
            'join_type' => 'left',
        ]);

    return $query->fetchAll();
}
$days = 6;
$itemsList = [];
for ($day = 0; $day <= $days; $day++) {
    $items = getAnimeCalendar($day);
    if (empty($items)) continue;
    $itemsList[$day] = $items;
}
CJSCore::Init(array("jquery"));
?>

<div class="tabs">

    <h2>Календарь выхода серий</h2>
    <ul class="tabs__list">
        <? foreach ($itemsList as $day => $items): ?>
            <?
            $week = FormatDate([
                'today' => 'today',
                '' => 'l',
            ], MakeTimeStamp((new \Bitrix\Main\Type\DateTime())->add($day.'day')))
            ?>
            <li class="tabs__item" data-tab-select="<?=$day?>"><?=$week?></li>
        <? endforeach; ?>
    </ul>
    <div class="tabs__body">
    <? foreach ($itemsList as $day => $items): ?>

        <div class="tabs__content" data-tab-content="<?=$day?>">
            <div class="tabs__anime-list">

                <? foreach ($items as $item): ?>

                    <a class="tabs__anime-link" target="_blank" href="<?=CIBlock::ReplaceDetailUrl($item['LINK'], $item)?>">
                        <img class="tabs__anime-image" src="<?= CFile::GetPath($item['IMAGE'])?>" alt="<?=$item['NAME']?>">
                        <h3 class="tabs__anime-name"><?=$item['NAME']?></h3>
                        <span class="tabs__anime-date"><?=$item['DATE']?></span>
                    </a>

                <? endforeach; ?>

            </div>
        </div>

    <? endforeach; ?>
    </div>
    <style>
        .tabs__list {
            display: flex;
            /*grid-template-columns: repeat(*/<?//=count($itemsList)?>/*, 1fr);*/
            width: 100%;
            flex-direction: row;
            justify-content: space-between;
            padding: unset;
            grid-gap: 15px;
        }
        .tabs__item {
            display: block;
            text-align: center;
            background-color: #fff;
            padding: 5px 10px;
            cursor: pointer;
            color: #000;
            border: 1px solid #000;
            width: 100%;
        }
        .tabs__item[data-tabs-selected] {
            background-color: #000;
            color: #fff;
        }

        .tabs__content {
            display: none;
        }
        .tabs__content[data-tabs-show] {
            display: block;
        }
        .tabs__anime-list {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 15px;
        }
        .tabs__anime-link {
            position: relative;
            display: block;
            height: 300px;
            overflow: hidden;
        }
        .tabs__anime-link:hover {
            background-color: rgba(0,0,0,0.2);
            box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
        }
        .tabs__anime-link:hover .tabs__anime-image {
            width: 104%;
            height: 104%;
            left: -2%;
            top: -2%;
        }
        .tabs__anime-image {
            height: 100%;
            width: 100%;
            object-fit: cover;
            object-position: center;
            position: absolute;
            left: 0;
            top: 0;
            z-index: -1;
        }
        .tabs__anime-name {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            background-color: #000;
            z-index: 2;
            padding: 5px 10px;
            margin: unset;
            font-size: 12px;
            box-sizing: border-box;
            color: #fff;
            text-decoration: none;
        }
        .tabs__anime-date {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 5px 10px;
            background-color: red;
            color: #fff;
            text-align: center;
            box-sizing: border-box;
        }
    </style>
    <script>
        $(document).ready(function () {
            if ($('[data-tab-select][data-tabs-selected]').length <= 0) {
                $('[data-tab-select]').first().attr('data-tabs-selected', '');
                $('[data-tab-content]').first().attr('data-tabs-show', '');
            }
            $('[data-tab-select]').on('click', function () {
                let day = $(this).data('tab-select');
                $('[data-tab-select]').removeAttr('data-tabs-selected');
                $(this).attr('data-tabs-selected', '');
                $('[data-tab-content]').removeAttr('data-tabs-show');
                $('[data-tab-content='+day+']').attr('data-tabs-show','');
            });
        });
    </script>
</div>





<!--    --><?//CJSCore::Init(array("jquery"));?>
<!--    <div data-player data-link="//kodik.info/serial/9153/cddf6e92e8e68f202dc7c8cac0c4ed7c/720p"></div>-->
<!--    <script>-->
<!--        document.addEventListener('DOMContentLoaded', function () {-->
<!--            let player = $('[data-player]');-->
<!--            let link = $(player).data('link');-->
<!--            let width = $(player).width();-->
<!--            let height = 720 / 1280 * width;-->
<!---->
<!--            $('[data-player]').html('<iframe src="'+link+'" width="'+width+'" height="'+height+'" frameborder="0" allowfullscreen=""></iframe>')-->
<!--        });-->
<!--    </script>-->

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>