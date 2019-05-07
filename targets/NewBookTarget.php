<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.name/
 * @copyright Copyright (c) 2016-2019 vistart
 * @license https://vistart.name/license/
 */
namespace rhoone\library\providers\huiwen\targets;

/**
 * Class NewBookTarget
 * @package rhoone\library\providers\huiwen\targets
 * @author vistart<i@vistart.me>
 */
abstract class NewBookTarget extends \rhoone\library\targets\NewBookTarget
{
    /**
     * @var string
     */
    public $relativeUrl = "/newbook/newbook_rss.php";

    /**
     * @var array
     */
    public $categories = [
        "A" => "马列主义、毛泽东思想、邓小平理论",
        "B" => "哲学、宗教",
        "C" => "社会科学总论",
        "D" => "政治、法律",
        "E" => "军事",
        "F" => "经济",
        "G" => "文化、科学、教育、体育",
        "H" => "语言、文字",
        "I" => "文学",
        "J" => "艺术",
        "K" => "历史、地理",
        "N" => "自然科学总论",
        "O" => "数理科学与化学",
        "P" => "天文学、地球科学",
        "Q" => "生物科学",
        "R" => "医药、卫生",
        "S" => "农业科学",
        "T" => "工业技术",
        "U" => "交通运输",
        "V" => "航空、航天",
        "X" => "环境科学,安全科学",
        "Z" => "综合性图书",
    ];

    public $backDays = 1;

    /**
     * @return array
     */
    public function getAbsoluteUrls()
    {
        $urls = [];
        foreach ($this->categories as $key => $category)
        {
            $urls[$key] = $this->getAbsoluteUrl([
                'type' => 'cls',
                's_doctype' => 'ALL',
                'back_days' => $this->backDays,
                'cls' => $key,
                'loca_code' => 'ALL',
                'clsname' => $category
            ]);
        }
        return $urls;
    }
}
