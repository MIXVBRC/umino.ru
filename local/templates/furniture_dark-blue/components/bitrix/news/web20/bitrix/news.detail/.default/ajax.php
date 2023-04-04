<? require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Umino\Anime\Player;
use Umino\Anime\Tables\DataTable;

CModule::IncludeModule('umino.anime');

$xml_id = $_GET['XML_ID'];
$season = $_GET['SEASON'];

$player = new Player(612);
$data = DataTable::getList([
    'filter' => [
        'XML_ID' => $xml_id
    ],
    'select' => [
        'LINK',
        'TRANSLATION_ID',
    ],
])->fetch();

echo $player->getPlayer($data['LINK'], $season, 1, $data['TRANSLATION_ID']);