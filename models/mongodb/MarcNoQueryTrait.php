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

namespace rhoone\library\providers\huiwen\models\mongodb;

/**
 * Trait MarcNoQueryTrait
 * @package rhoone\library\providers\huiwen\models\mongodb
 */
trait MarcNoQueryTrait
{
    /**
     * @param string $marcNo
     * @return static
     */
    public function marcNo(string $marcNo)
    {
        return $this->andWhere(['marc_no' => $marcNo]);
    }
}
