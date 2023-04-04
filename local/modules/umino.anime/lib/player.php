<?php


namespace Umino\Anime;


class Player
{

    protected $src;
    protected $width = 1280;
    protected $height = 720;

    /**
     * Player constructor.
     * @param int $width - ширина плеера (0 - не выводить) [стандартно: 1280]
     */
    public function __construct(int $width = 0)
    {
        if ($width <= 0) return $this;

        $this->height = $this->height / $this->width * $width;
        $this->width = $width;
    }

    /**
     * @param string $url - ссылка на видео
     * @param int $season - сезон
     * @param int $episode - эпизод
     * @param int $translator_id - id студии перевода
     * @param bool $selectors - переключатели [стандартно: false]
     */
    public function getPlayer(string $url, int $season, int $episode, int $translator_id, bool $selectors = false): string
    {
        $params = [
            'hide_selectors' => $selectors?'true':'false',
            'season' => $season,
            'episode' => $episode,
            'only_translations' => $translator_id,
        ];

        $this->src = $url . '?' . http_build_query($params);

        $iframe = '<iframe src="';
        $iframe .= $this->src;

        $iframe .= '" width="';
        $iframe .= $this->width;

        $iframe .= '" height="';
        $iframe .= $this->height;

        $iframe .= '" frameborder="0" allowfullscreen=""></iframe>';

        return $iframe;
    }
}