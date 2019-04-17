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

namespace rhoone\library\providers\huiwen\models\elasticsearch;

/**
 * Class MarcQuery
 * @package rhoone\library\providers\huiwen\models\elasticsearch
 */
class MarcQuery extends \yii\elasticsearch\ActiveQuery
{
    /**
     * @param $marc_no
     * @return MarcQuery
     */
    public function marcNo($marc_no)
    {
        return $this->andWhere(['marc_no' => $marc_no]);
    }
}
