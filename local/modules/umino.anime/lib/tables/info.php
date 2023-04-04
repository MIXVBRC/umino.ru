<?php


namespace Umino\Anime\Tables;

use Bitrix\Main\ORM\Fields\ArrayField;

/**
 * Class Info
 * @package Umino\Kodik\Tables
 *
 * --- GET ---
 *
 * @method int getId()
 * @method string getXmlId()
 * @method bool getActive()
 * @method object getDateCreate()
 * @method object getDateUpdate()
 * @method string getType()
 * @method string getTitle()
 * @method string getTitleOriginal()
 * @method array getTitleOther()
 * @method string getYear()
 * @method int getSeason()
 * @method string getKodikId()
 * @method string getShikimoriId()
 * @method string getWorldartLink()
 * @method string getKinopoiskId()
 * @method string getImdbId()
 * @method int getIblockElementId()
 *
 * --- SET ---
 *
 * @method setXmlId(string $value)
 * @method setActive(bool $value)
 * @method setDateCreate(object $value)
 * @method setDateUpdate(object $value)
 * @method setType(string $value)
 * @method setTitle(string $value)
 * @method setTitleOriginal(string $value)
 * @method setTitleOther(array $value)
 * @method setYear(string $value)
 * @method setSeason(int $value)
 * @method setKodikId(string $value)
 * @method setShikimoriId(string $value)
 * @method setWorldartLink(string $value)
 * @method setKinopoiskId(string $value)
 * @method setImdbId(string $value)
 * @method setIblockElementId(int $value)
 *
 * --- ADDITIONAL ---
 *
 * @method save()
 */
class Info extends EO_Info {}