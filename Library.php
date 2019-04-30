<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.name/
 * @copyright Copyright (c) 2016-2018 vistart
 * @license https://vistart.name/license/
 */
namespace rhoone\library\providers\huiwen;

use yii\elasticsearch\ActiveDataProvider;

/**
 * Class Library
 * @package rhoone\library\providers\huiwen
 */
abstract class Library extends \rhoone\library\Library
{
    public $marcClass = \rhoone\library\providers\huiwen\models\elasticsearch\Marc::class;

    public function search($keywords, array $config = null)
    {
        //$queryArray = $this->buildQueryArray($keywords);
        $config['keywords'] = $keywords;
        $queryBuilder = new $this->queryBuilderClass($config);
        /* @var $queryBuilder QueryBuilder */
        $query = $this->marcClass::find()->query($queryBuilder->queryArray)->explain(false)->options($queryBuilder->queryOptions);
        /* @var $query \rhoone\library\providers\huiwen\models\elasticsearch\MarcQuery */
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $provider;
    }
}
