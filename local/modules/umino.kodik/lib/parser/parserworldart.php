<?php


namespace Umino\Kodik\Parser;


use CFile;
use phpQuery;

class ParserWorldArt extends Parser implements ParserInterface
{
    protected $url = 'http://world-art.ru/animation/animation.php?id=';

    protected $urlDescription = 'http://world-art.ru/animation/animation_update_synopsis.php?id=';
    protected $phpQueryDescription;

    public function getImage()
    {
        $find = $this->phpQuery->find('div.comment_block a img');
        return CFile::MakeFileArray(pq($find)->attr('src'));
    }

    public function getDescription()
    {
        $this->urlDescription .= $this->id;
        $this->phpQueryDescription = phpQuery::newDocument(self::getPageContent($this->urlDescription));

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
}