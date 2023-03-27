<?php


namespace Umino\Kodik;


Class Player
{

    protected $src;
    protected $width;
    protected $height;

    /**
     * Player constructor.
     * @param int $width - ширина плеера (0 - не выводить) [стандартно: 1280]
     * @param int $height - высота плеера (0 - не выводить) [стандартно: 720]
     */
    public function __construct(int $width = 1280, int $height = 720)
    {
        $this->width = $width;
        $this->height = $height;
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

        if ($this->width > 0) {
            $iframe .= '" width="';
            $iframe .= $this->width;
        }

        if ($this->height > 0) {
            $iframe .= '" height="';
            $iframe .= $this->height;
        }

        $iframe .= '" frameborder="0" allowfullscreen=""></iframe>';

        return $iframe;
    }
}