<?php


namespace Umino\Kodik\Parser;


use CFile;
use phpQuery;

class ParserShikimori extends Parser implements ParserInterface
{
    protected $url = 'https://shikimori.one/animes/';

    public function __construct($id)
    {
        parent::__construct($id);

        $error = $this->phpQuery->find('p.error-404')->text();
        if ($error && $error == 302) {
            if ($this->url = $this->phpQuery->find('p a')->attr('href')) {
                if (empty($page = self::getPageContent($this->url))) return null;
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

        return str_replace('%br%', '<br>', $text);
    }
}