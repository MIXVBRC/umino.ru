<?php


namespace Umino\Anime\Parsers;


interface ParserInterface
{
    public function __construct($id);
    public function getDescription();
    public function getImage();
    public function getGenres();
    public function getType();
    public function getEpisodes();
    public function getEpisodeDuration();
}