<?php


namespace Umino\Anime\Parsers;


use CFile;
use phpQuery;
use Umino\Anime\Request;

class ParserWorldArt extends Parser implements ParserInterface
{
    protected $id;
    protected $urlDescription = 'http://world-art.ru/animation/animation_update_synopsis.php?id=';
    protected $phpQueryDescription;

    public function __construct($url)
    {
        $this->id = explode('?id=', $url)[1];
        parent::__construct($url);
    }

    public function getImage()
    {
        $find = $this->phpQuery->find('div.comment_block a img');
        return CFile::MakeFileArray(pq($find)->attr('src'));
    }

    public function getDescription()
    {
        $this->urlDescription .= $this->id;
        $this->phpQueryDescription = phpQuery::newDocument(Request::getContent($this->urlDescription));

        $result = '';

        $commentBlockList = $this->phpQueryDescription->find('div.comment_block');

        foreach ($commentBlockList as $commentBlock) {
            $commentBlock = pq($commentBlock);
            $reviewList = $commentBlock->find('td.review');

            foreach ($reviewList as $review) {
                $review = pq($review);

                $review->find('i')->remove();
                $review->find('br')->replaceWith('%br%');

                $text = trim($review->text());

                $text = str_replace(['%br%%br%', '%br%'], '%br%', $text);
                $text = str_replace('%br%', '%br%%br%', $text);

                $text = trim($text, [' ', '%br%']);

                $text = str_replace('%br%', '<br>', $text);

                $result = $text;

                if (empty($result)) {
                    break;
                } else {
                    break 2;
                }
            }

        }

        return $result;
    }

    public function getGenres() {}
    public function getType() {}
    public function getEpisodes() {}
    public function getEpisodeDuration() {}
}