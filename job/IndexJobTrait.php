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
use yii\base\ErrorException;

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
     * @return int
     */
    public function index($model) : int
    {
        if ($model->empty) {
            return true;
        }
        $indexClass = $this->indexClass;
        $index = $indexClass::find()->where(['marc_no' => $model->marc_no])->one();
        /* @var $index Marc */
        if (!$index) {
            $index = new $indexClass([
                'marc_no' => $model->marc_no,
            ]);
            $index->primaryKey = intval($model->marc_no);
        }
        $index->copyAttributes = $model->marcCopies;
        $index->infoAttributes = $model->marcInfos;
        $index->statusAttributes = $model->marcStatus;
        if (!$index->save()) {
            $errors = $index->getErrors();
            throw new ErrorException(first($errors));
        }
        return true;
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
            /* @var $model \rhoone\library\providers\huiwen\models\mongodb\MarcNo */
            try {
                $result = $this->index($model);
            } catch (\Exception $ex) {
                $model->error_indexing = true;
                $model->reason_indexing = $ex->getMessage();
                $model->save();
            }
        }
        file_put_contents("php://stdout", "\n");
        file_put_contents("php://stdout", count($this->marcNos) . " tasks finished.\n");
        return 0;
    }
}
