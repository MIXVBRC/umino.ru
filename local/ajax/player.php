<? require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Umino\Anime\Player;
use Umino\Anime\Tables\DataTable;

CModule::IncludeModule('umino.anime');

$xml_id = $_POST['XML_ID'];
$season = $_POST['SEASON'];

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
pre([
    $data['LINK'],
    $season,
    $data['TRANSLATION_ID'],
]);
echo 1;
die;
echo $player->getPlayer($data['LINK'], $season, 1, $data['TRANSLATION_ID']);
