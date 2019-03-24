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

namespace rhoone\library\providers\huiwen\job;

use rhoone\library\providers\huiwen\models\elasticsearch\Marc;
use rhoone\library\providers\huiwen\models\mongodb\MarcNo;

/**
 * Trait IndexJobTrait
 * @package rhoone\library\providers\huiwen\job
 */
trait IndexJobTrait
{
    /**
     * @var string[]
     */
    public $marcNos;

    /**
     * @var string
     */
    public $_currentMarcNo;

    /**
     * @param MarcNo $model
     * @return array
     */
    protected function populateCopies(MarcNo $model) : array
    {
        $model->marcCopies;
    }

    /**
     * @param MarcNo $model
     * @return int
     */
    public function index($model) : int
    {
        $indexClass = $this->indexClass;
        $index = new $indexClass();
        /* @var $index Marc */
        $index->attributes = [
            'marc_no' => $model->marc_no,
            'title' => $model->marcInfos['title']
        ];
    }

    /**
     * @return int
     */
    public function batchIndex() : int
    {
        file_put_contents("php://stdout", count($this->marcNos) . " tasks received.\n");
        file_put_contents("php://stdout", "start from " . current($this->marcNos) . ", at " . date('Y-m-d H:i:s') . "\n");
        $class = $this->marcNoClass;
        $count = 0;
        foreach ($this->marcNos as $key => $marcNo)
        {
            $count++;
            printf("progress: [%-50s] %d%% Done.\r", str_repeat('#', $count / count($this->marcNos) * 50), $count / count($this->marcNos) * 100);
            $this->_currentMarcNo = $marcNo;
            $model = $class::find()->marcNo($this->_currentMarcNo)->one();
            $this->index($model);
        }
        file_put_contents("php://stdout", "\n");
        file_put_contents("php://stdout", count($this->marcNos) . " tasks finished.\n");
        return 0;
    }
}
