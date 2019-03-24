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

use rhoone\library\providers\huiwen\models\mongodb\MarcCopy;
use rhoone\library\providers\huiwen\models\mongodb\MarcInfo;
use rhoone\library\providers\huiwen\models\mongodb\MarcStatus;

/**
 * Class Marc
 * @property string $marc_no
 * @property string[] $titles
 * @property string[] $authors
 * @property string[] $presses
 * @property string[] $prices
 * @property string[] $forms
 * @property string[] $ISBNs
 * @property string[] $subjects
 * @property string[] $classifications
 * @property string $abstract
 * @property string[][] $copies
 * @property string[] $notes
 * @property string $type
 * @property string $page_visit
 * @package rhoone\library\providers\huiwen\models\elasticsearch
 */
class Marc extends \yii\elasticsearch\ActiveRecord
{
#region set attributes
    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setTitles(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setAuthors(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setPresses(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setPrices(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setForms(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setISBNs(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setSubjects(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setClassifications(array $marcInfos)
    {

    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setAbstract(array $marcInfos)
    {

    }

    /**
     * @param MarcCopy[] $marcCopies
     */
    public function setCopies(array $marcCopies)
    {

    }

    /**
     * @param MarcStatus $marcStatus
     */
    public function setType(MarcStatus $marcStatus)
    {
        $this->type = $marcStatus->type;
    }

    /**
     * @param MarcStatus $marcStatus
     */
    public function setPageVisit(MarcStatus $marcStatus)
    {
        $this->page_visit = $marcStatus->page_visit;
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setNotes(array $marcInfos)
    {
        
    }
#endregion
    /**
     * @return array|string[]
     */
    public function attributes()
    {
        return [
            '_id',
            'marc_no', // MARC 编号
            'titles', // 题名
            'authors', // 责任者
            'presses', // 出版社
            'prices', // 价格
            'forms', // 载体形态项
            'ISBNs', // ISBN
            'subjects', // 主题
            'classifications', // 分类号
            'abstract', // 摘要
            'type', // 类型
            'status', // 状态
            'copies', // 副本
            'notes', // 附注
        ];
    }

    /**
     * @return array|string[]
     */
    public function safeAttributes()
    {
        return $this->attributes();
    }

    /**
     * @return array|string[]
     */
    public function arrayAttributes()
    {
        return [
            'titles',
            'presses',
            'authors',
            'prices',
            'forms',
            'ISBNs',
            'subjects',
            'classifications',
            'copies',
            'notes',
        ];
    }
#region operation
    /**
     * @return array
     */
    public static function mapping()
    {
        return [
            static::type() => [
                'properties' => [
                    'marc_no' => [
                        'type' => 'text',
                        'fielddata' => true,
                    ],
                    'titles' => [
                        'properties' => [
                            'title' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                                'search_analyzer' => 'ik_max_word',
                            ],
                        ]
                    ],
                    'authors' => [
                        'properties' => [
                            'author' => [
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                        ],
                    ],
                    'presses' => [
                        'properties' => [
                            'press' => [
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                            'location' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                            'date' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
                    ],
                    'prices' => [
                        'properties' => [
                            'price' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
                    ],
                    'forms' => [
                        'properties' => [
                            'form' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
                    ],
                    'ISBNs' => [
                        'properties' => [
                            'ISBN' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
                    ],
                    'subjects' => [
                        'properties' => [
                            'subject' => [
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_max_word',
                            ],
                        ],
                    ],
                    'classifications' => [
                        'properties' => [
                            'classification' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
                    ],
                    'abstract' => [
                        'type' => 'text',
                        'analyzer' => 'ik_max_word',
                        'search_analyzer' => 'ik_max_word',
                    ],
                    'copies' => [
                        'properties' => [
                            'barcode' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                            'call_no' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                            'position' =>[
                                'type' => 'text',
                            ],
                            'status' => [
                                'type' => 'text',
                            ],
                            'volume_period' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
                    ],
                    'notes' => [
                        'properties' => [
                            'note' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                                'search_analyzer' => 'ik_max_word',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Set (update) mappings for this model
     */
    public static function updateMapping()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->setMapping(static::index(), static::type(), static::mapping());
    }

    /**
     * Create this model's index
     */
    public static function createIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->createIndex(static::index(), [
            // 'settings' => [ /* ... */ ],
            'mappings' => static::mapping(),
            //'warmers' => [ /* ... */ ],
            //'aliases' => [ /* ... */ ],
            //'creation_date' => '...'
        ]);
    }

    /**
     * Delete this model's index
     */
    public static function deleteIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->deleteIndex(static::index(), static::type());
    }

    /**
     * @return string
     */
    public static function type()
    {
        return 'library';
    }
#endregion
}
