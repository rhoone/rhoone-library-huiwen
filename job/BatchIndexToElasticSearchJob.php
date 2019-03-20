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
use rhoone\library\providers\huiwen\models\mongodb\MarcNo;
use rhoone\library\providers\huiwen\models\mongodb\MarcStatus;
use rhoone\spider\job\BatchIndexJob as baseIndexJob;

/**
 * Class BatchIndexToElasticSearchJob
 * @package rhoone\library\providers\huiwen\job
 */
class BatchIndexToElasticSearchJob extends baseIndexJob
{
    use IndexJobTrait;

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
