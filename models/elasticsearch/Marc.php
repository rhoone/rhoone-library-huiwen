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
     * Extract Key-value pair from Marc-infos.
     * @param MarcInfo[] $marcInfos The objects being extracted.
     * @param string $key
     * @return string[]
     */
    protected function extractValues(array $marcInfos, string $key) : array
    {
        $results = [];
        foreach ($marcInfos as $info)
        {
            if ($info->key == $key) {
                $results[] = $info->value;
            }
        }
        return $results;
    }

    /**
     * Extract title and author from mixed-title-author marc info.
     * @param string $titleAndAuthor
     * @return string[]
     */
    protected function extractTitleAndAuthor(string $titleAndAuthor)
    {
        if (empty($titleAndAuthor)) {
            return null;
        }
        $exploded = explode("/", $titleAndAuthor);
        while (count($exploded) > 2) {
            $exploded[0] = $exploded[0] . $exploded[1];
            unset($exploded[1]);
        }
        return $exploded;
    }

    /**
     * @param MarcInfo[] $marcInfos
     * @param string $keys
     * @param int $offset
     * @return string[][]
     */
    protected function populateKeyValuePairs(array $marcInfos, array $keys, int &$offset = 0)
    {
        $index = [];
        foreach ($keys as $key)
        {
            $results = $this->extractValues($marcInfos, $key);
            foreach ($results as $result)
            {
                $index[$offset]['key'] = $key;
                $index[$offset]['value'] = $result;
                $offset++;
            }
        }
        return $index;
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setTitles(array $marcInfos)
    {
        $this->titles = [];
        $i = 0;
        $keyTitleAndAuthor = '题名/责任者';
        $titleAndAuthor = $this->extractTitleAndAuthor($this->extractValues($marcInfos, $keyTitleAndAuthor));

        $this->titles[$i]['key'] = '题名';
        $this->titles[$i]['title'] = $titleAndAuthor[0];

        $keyAdditionalList = ['并列正题名', '统一题名', '翻译提名', '封面提名', '书脊题名','前题名', '后续提名',
            '卷端题名', '变异题名', '曾用题名', '简体题名',  '丛编统一题名', '丛编题名', '作品集统一题名',
            '逐页题名', '识别提名', '附加统一题名', '附加题名页题名', '其它题名'];
        $offset = count($this->titles);
        $this->titles = array_merge($this->titles, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setAuthors(array $marcInfos)
    {
        $this->authors = [];
        $i = 0;
        $keyTitleAndAuthor = '题名/责任者';
        $titleAndAuthor = $this->extractTitleAndAuthor($this->extractValues($marcInfos, $keyTitleAndAuthor));

        $this->titles[$i]['key'] = '责任者';
        $this->titles[$i]['title'] = end($titleAndAuthor);

        $keyAdditionalList = ['个人主要责任者', '个人次要责任者', '个人责任者', '其他责任者', '团体主要责任者',
            '团体次要责任者', '团体责任者', '丛编个人名称', '丛编会议名称', '丛编团体名称'];
        $offset = count($this->authors);
        $this->authors = array_merge($this->authors, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setPresses(array $marcInfos)
    {
        $key = '出版发行项';
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setPrices(array $marcInfos)
    {
        $key = '出版发行项';
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setForms(array $marcInfos)
    {
        $key = '载体形态项';
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setISBNs(array $marcInfos)
    {
        $keyAdditionalList = ['ISBN', 'ISBN及定价', 'ISMN及定价', 'ISRC及定价', 'ISRN及定价', 'ISSN', 'ISSN及定价', 'STRN'];
        $offset = count($this->ISBNs);
        $this->ISBNs = array_merge($this->ISBNs, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setSubjects(array $marcInfos)
    {
        $keyAdditionalList = ['个人名称主题', '会议名称主题', '作者题名主题', '团体名称主题', '地名主题', '地理名称主题',
            '学科主题', '家族名称主题', '统一题名主题', '论题主题', '非控制主题', '非控制主题词', '题名主题'];
        $offset = count($this->subjects);
        $this->subjects = array_merge($this->subjects, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setClassifications(array $marcInfos)
    {
        $keyAdditionalList = ['中图法分类号', '人大法分类号', '可突发分类号', '四库分类号', '其他分类号', '杜威等其它类号'];
        $offset = count($this->classifications);
        $this->classifications = array_merge($this->classifications, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }

    /**
     * @param MarcCopy[] $marcCopies
     */
    public function setCopies(array $marcCopies)
    {
        $this->copies = [];
        foreach ($marcCopies as $copy)
        {
            $index['barcode'] = $copy->barcode;
            $index['call_no'] = $copy->call_no;
            $index['volume_period'] = $copy->volume_period;
            $index['position'] = $copy->position;
            $index['status'] = $copy->status;
            $this->copies[] = $index;
        }
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
        $keyAdditionalList = ['一般附注', '专款信息附注', '丛编附注', '主题检索附注', '书目附注', '传记或历史附注',
            '使用对象附注', '内容附注', '出版发行附注', '出版周期附注', '制作者附注', '制作附注', '前提明附注',
            '原作版本附注', '原版附注', '合订附注', '复制品附注', '学位论文附注', '引文/参考附注', '引文附注',
            '报告附注', '提要文摘附注', '摘要附注', '收藏地附注', '文献获奖附注', '时间地点附注', '标识号附注',
            '演出者附注', '版本附注', '特殊细节附注', '相关题名附注'];
        $offset = count($this->notes);
        $this->notes = array_merge($this->notes, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
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
            'forms', // 载体形态项
            'ISBNs', // ISBN
            'subjects', // 主题
            'classifications', // 分类号
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
                            'key' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
                    ],
                    'authors' => [
                        'properties' => [
                            'author' => [
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                            'key' => [
                                'type' => 'text',
                                'fielddata' => true,
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
                    'ISBNs' => [
                        'properties' => [
                            'ISBN' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                            'key' => [
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
                            'key' => [
                                'type' => 'text',
                                'fielddata' => true,
                            ],
                        ],
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
                            'key' => [
                                'type' => 'text',
                                'fielddata' => true,
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
