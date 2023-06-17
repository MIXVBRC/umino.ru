<?php

use Bitrix\Main\Loader;
use Umino\Anime\API;
use Umino\Anime\Core;
use Umino\Anime\Import;
use Umino\Anime\Lock;
use Umino\Anime\Request;

require_once 'config.php';

Loader::includeModule('umino.anime');

$lock = new Lock('shikimori_import');

if (!$lock->lock()) die;

$file = __DIR__.'/chunks.php';

$chunks = [];

try {
    if (file_exists($file)) {
        include $file;
    } else {
        $response = current(Request::getResponse('https://shikimori.one/api/animes?limit=1&order=id_desc'));
        $params = [];
        for ($id = 1; $id <= $response['id']; $id++) {
            $params[$id] = ['shikimori_id' => $id];
        }
        $chunks = array_chunk($params, Core::getAPILimit(), true);
    }

    if (empty($chunks)) {
        die;
        if (file_exists($file)) {
            unlink($file);
        }
    } else {
        $import = new Import();
        $items = [];
        for ($i=0;$i<Core::getAPILimitPage();$i++) {
            foreach (API::searchAsync(array_shift($chunks)) as $item) {
                $items = array_merge($items, $item);
            }
        }
        $import->start($items);

        file_put_contents($file, print_r("<?php\n\n\$chunks = ".var_export($chunks, true).";\n\n?>", true));
    }
} catch (\Exception $exception) {
    var_dump($exception->getMessage());
}



