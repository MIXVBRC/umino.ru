<?php


namespace Umino\Kodik\Parser;


use phpQuery;
use Umino\Kodik\Request;

class Parser
{
    protected $phpQuery;

    public function __construct($url)
    {
        if (empty($page = Request::getContent($url))) return null;

        $this->phpQuery = phpQuery::newDocument($page);
    }
}