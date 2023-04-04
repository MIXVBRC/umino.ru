<?php


namespace Umino\Anime\Parsers;


use phpQuery;
use Umino\Anime\Request;

class Parser
{
    protected $phpQuery;

    public function __construct($url)
    {
        if (empty($page = Request::getContent($url))) return null;

        $this->phpQuery = phpQuery::newDocument($page);
    }
}