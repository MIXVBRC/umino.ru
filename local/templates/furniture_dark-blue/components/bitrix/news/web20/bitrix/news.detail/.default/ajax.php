<? require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Umino\Anime\Logger;
use Umino\Anime\Player;
use Umino\Anime\Tables\EpisodesTable;

CModule::IncludeModule('umino.anime');

try {

    $xml_id = $_GET['XML_ID'];
    $season = $_GET['SEASON'];
    $episode = $_GET['EPISODE'];

    $player = new Player(612);

    $result = EpisodesTable::getList([
        'filter' => [
            'DATA.XML_ID' => $xml_id,
            'SEASON' => $season,
            'EPISODE' => $episode,
        ],
        'select' => [
            'EPISODE_LINK',
            'TRANSLATION_ID' => 'DATA.TRANSLATION_ID',
        ],
    ])->fetch();

    if ($result) {

        echo $player->getPlayer($result['EPISODE_LINK'], $season, $episode, $result['TRANSLATION_ID']);

    } else {

        Logger::log([
            'message' => 'Эпизода аниме нет в базе с данной озвучкой',
            'get' => $_GET
        ]);

        $result = EpisodesTable::getList([
            'filter' => [
                'DATA.XML_ID' => $xml_id,
                'SEASON' => $season,
            ],
            'order' => ['EPISODE' => 'ASC'],
            'select' => [
                'SEASON_LINK',
                'EPISODE',
                'TRANSLATION_ID' => 'DATA.TRANSLATION_ID',
            ],
            'limit' => 1,
        ])->fetch();

        if ($result) {

            echo 'Похоже что у нас нет ' . $episode . ' серии ' . $season . ' сезона с этой озвучкой :"(';

            echo $player->getPlayer($result['SEASON_LINK'], $season, $result['EPISODE'], $result['TRANSLATION_ID']);

        } else {

            Logger::log([
                'message' => 'Сезона аниме нет в базе с данной озвучкой',
                'get' => $_GET
            ]);

            echo 'Похоже что у нас нет ' . $season . ' сезона с этой озвучкой :"(';
        }
    }
} catch (Exception $exception) {

    pre($exception->getMessage());

}
