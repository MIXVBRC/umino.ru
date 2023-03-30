<?php


namespace Umino\Kodik\Parser;


interface ParserInterface
{
    public function __construct($id);
    public function getDescription();
    public function getImage();
}