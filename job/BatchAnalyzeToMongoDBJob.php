<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2019 vistart
 * @license https://vistart.me/license/
 */

namespace rhoone\library\providers\huiwen\job;

use rhoone\library\providers\huiwen\models\mongodb\MarcCopy;
use rhoone\library\providers\huiwen\models\mongodb\MarcInfo;
use simplehtmldom_1_5\simple_html_dom;
use simplehtmldom_1_5\simple_html_dom_node;
use Sunra\PhpSimple\HtmlDomParser;
use rhoone\spider\job\BatchAnalyzeJob as baseJob;
use rhoone\library\providers\huiwen\models\mongodb\DownloadedContent;
use rhoone\library\providers\huiwen\models\mongodb\MarcNo;

/**
 * Class BatchAnalyzeToMongoDBJob
 * @package rhoone\library\providers\huiwen\job
 */
class BatchAnalyzeToMongoDBJob extends baseJob
{
    use AnalyzeJobTrait;

    /**
     * @var string
     */
    public $downloadedContentClass = DownloadedContent::class;

    /**
     * @var string
     */
    public $marcNoClass = MarcNo::class;

    /**
     * @var string
     */
    public $marcCopyClass = MarcCopy::class;

    /**
     * @var string
     */
    public $marcInfoClass = MarcInfo::class;

    /**
     * @var string
     */
    public $marcStatusClass = MarcStatus::class;
}
