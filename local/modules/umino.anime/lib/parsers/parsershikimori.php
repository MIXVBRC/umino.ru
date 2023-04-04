<?php


namespace Umino\Anime\Parsers;


use CFile;
use phpQuery;
use Umino\Anime\Request;

class ParserShikimori extends Parser implements ParserInterface
{
    public static $url = 'https://shikimori.one/animes/';

    public function __construct($url)
    {
        parent::__construct($url);

        $error = $this->phpQuery->find('p.error-404')->text();
        if ($error && $error == 302) {
            if ($url = $this->phpQuery->find('p a')->attr('href')) {
                if (empty($page = Request::getContent($url))) return null;
                $this->phpQuery = phpQuery::newDocument($page);
            }
        }
    }

    public function getImage()
    {
        $find = $this->phpQuery->find('picture img');
        return CFile::MakeFileArray(pq($find)->attr('src'));
    }

    public function getDescription()
    {
        $find = $this->phpQuery->find('div[itemprop="description"] div');
        $find->find('br')->replaceWith('%br%');
        $text = trim($find->text());

        $text = str_replace(['%br%%br%', '%br%'], '%br%', $text);
        $text = str_replace('%br%', '%br%%br%', $text);

        if ($text == 'Нет описания') {
            $text = '';
        }

        $text = trim($text);

        return str_replace('%br%', '<br>', $text);
    }

    protected function getInfoContainer($name)
    {
        $findList = $this->phpQuery->find('div.b-entry-info div.line-container div.line');

        $result = null;

        foreach ($findList as $find) {
            if (pq($find)->find('div.key')->text() !== $name) continue;
            $result = pq($find)->find('div.value');
        }

        return $result;
    }

    protected static function getText($item): string
    {
        if (is_null($item)) return '';
        return $item->text();
    }

    public function getGenres(): array
    {
        $genres = [];
        $result = $this->getInfoContainer('Жанры:')->find('span.genre-ru');
        foreach ($result as $genre) {
            $genres[] = self::getText(pq($genre));
        }
        return $genres;
    }

    public function getType(): string
    {
        return self::getText($this->getInfoContainer('Тип:'));
    }

    public function getEpisodes(): string
    {
        return self::getText($this->getInfoContainer('Эпизоды:'));
    }

    public function getEpisodeDuration(): string
    {
        return self::getText($this->getInfoContainer('Длительность эпизода:'));
    }
}