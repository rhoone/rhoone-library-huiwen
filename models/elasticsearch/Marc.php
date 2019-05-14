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
 * @property string[] $titles 题名
 * @property string[] $authors 责任者
 * @property string[] $presses 出版发行项
 * @property string[] $forms 载体形态项
 * @property string[] $ISBNAndPrices ISBN或其它出版编号及定价
 * @property string[] $subjects 主题
 * @property string[] $classifications 分类号
 * @property string $type 馆藏类型
 * @property string $status 状态
 * @property string[][] $copies 副本
 * @property string[] $notes 附注
 * @property string[] $electronicResources 电子资源
 * @property-write MarcInfo[] infoAttributes
 * @property-write MarcCopy[] copyAttributes
 * @property-write MarcStatus statusAttributes
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
     * @param string|string[] $titleAndAuthor If the argument passed in is an array, only the first element is used.
     * @return string[]
     */
    protected function extractTitleAndAuthor($titleAndAuthor)
    {
        if (empty($titleAndAuthor)) {
            return null;
        }
        if (is_array($titleAndAuthor)) {
            $titleAndAuthor = current($titleAndAuthor);
        }
        // The "TitleAndAuthor" string is divided into several segments by "/" as a separator.
        $exploded = explode("/", $titleAndAuthor);

        // If more than one result fragment is separated, all the clips except the last one are merged.
        $counter = 1;
        while (count($exploded) > 2) {
            $exploded[0] = $exploded[0] . $exploded[$counter];
            unset($exploded[$counter]);
            $counter++;
        }
        return $exploded;
    }

    /**
     * @param MarcInfo[] $marcInfos
     * @param string $keys
     * @param int $offset
     * @return string[][]
     */
    protected function populateKeyValuePairs(array $marcInfos, array $keys, int &$offset = 0) : array
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

        $this->titles = array_merge($this->titles, [0 => [
            'key' => '题名',
            'value' => $titleAndAuthor[0],
        ]]);

        $keyAdditionalList = ['并列正题名', '统一题名', '翻译提名', '封面提名', '书脊题名', '前题名', '后续提名',
            '卷端题名', '变异题名', '曾用题名(连续出版物)', '展开题名(连续出版物)', '简体题名',  '丛编说明',
            '丛编统一题名', '丛编题名', '丛编项', '主丛编', '作品集统一题名', '逐页题名', '识别提名(连续出版物)',
            '附加统一题名', '附加非控制题名', '附加题名页题名', '其它题名', '先前出版物', '后续出版物', '编目员补充题名'];
        $offset = count($this->titles);
        $this->titles = array_merge($this->titles, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setAuthors(array $marcInfos)
    {
        /* Does not need to extract from it.
        $i = 0;
        $keyTitleAndAuthor = '题名/责任者';
        $titleAndAuthor = $this->extractTitleAndAuthor($this->extractValues($marcInfos, $keyTitleAndAuthor));

        $this->titles[$i]['key'] = '责任者';
        $this->titles[$i]['title'] = end($titleAndAuthor);
        */
        $keyAdditionalList = ['个人责任者', '个人主要责任者', '个人次要责任者', '团体责任者', '团体主要责任者',
            '团体次要责任者', '丛编个人名称', '丛编团体名称', '丛编会议名称', '其他责任者'];
        $dutyList = ['著', '编', '译', '编著', '主编', '原著', '编写', '作诗', '编译', '撰', '辑注', '评述', '等编',
            '选编', '撰稿', '批', '编选', '口述', '校注', '执笔', '评析', '插图', '重撰', '本册主编', '编注', '点校',
            '辑录', '改编', '缩编', '整理', '编撰者', '编纂', '标点', '等译', '译编', '节译', '注释', '作曲', '校点',
            '修订', '汇编', '编印', '前辑', '收集', '选注', '配画', '标校', '譯', '纂修', '編纂', '點校', '革', '集錄',
            '注', '編', '注釋', '選注', '翻譯', '編次', '譯注', '校訂', '編輯', '绘', '編製', '绘画', '集校', '輯校',
            '撰书', '編校整理', '標校整理', '輯本', '纂注', '錄', '主編', '編審', '订', '搜輯', '撰述', '校阅', '纂述',
            '述', '出品人', '制片人', '摄制', '导演', '出品', '制作', '录制', '制片', '解说', '主办', '审定', '选释',
            '译注', '编辑', '翻译', '选译', '译校', '主讲', '创作', '作者', '编剧', '指导', '授予'];
        if (empty($this->authors)) {
            $this->authors = [];
        }
        $offset = count($this->authors);
        $index = [];
        foreach ($keyAdditionalList as $key)
        {
            $results = $this->extractValues($marcInfos, $key);
            foreach ($results as $result)
            {
                $index[$offset]['key'] = $key;
                $exploded = explode(' ', $result);
                if (in_array(trim(end($exploded)), $dutyList) || count($exploded) > 1) {
                    $index[$offset]['author'] = implode(' ', explode(' ', $result, -1));
                    $index[$offset]['duty'] = trim(end($exploded));
                } else {
                    $index[$offset]['author'] = $result;
                    $index[$offset]['duty'] = '';
                }
                $offset++;
            }
        }
        $this->authors = array_merge($this->authors, $index);
    }

    /**
     * @param string[] $presses
     */
    protected function extractPresses(array $presses)
    {
        $results = [];
        foreach ($presses as $press)
        {
            $result = [];
            $exploded = explode(':', $press, 2);
            if (count($exploded) == 1) {
                $result['press'] = trim($exploded[0]);
                $result['location'] = '';
                $result['date'] = '';
            } else {
                if (empty(trim($exploded[0])) && mb_strpos($exploded[1], "：")) {
                    $exploded = explode($exploded[1], '：', 2);
                }
                $exploded[0] = trim($exploded[0]);
                $exploded[1] = trim($exploded[1]);

                $result['location'] = $exploded[0];
                $exploded = explode(',', $exploded[1], 2);
                $result['press'] = trim($exploded[0]);
                $result['date'] = trim($exploded[1]);
            }
            $results[] = $result;
        }
        return $results;
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setPresses(array $marcInfos)
    {
        $key = '出版发行项';
        $i = 0;
        $extracted = $this->extractValues($marcInfos, $key);
        if (empty($extracted)) {
            $this->presses = null;
            return;
        }
        if ($this->presses == null) {
            $this->presses = [];
        }
        $this->presses = array_merge($this->presses, $this->extractPresses($extracted));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setForms(array $marcInfos)
    {
        $key = '载体形态项';
        if (empty($this->forms)) {
            $this->forms = [];
        }
        $offset = count($this->forms);
        $this->forms = array_merge($this->forms, array_values($this->populateKeyValuePairs($marcInfos, [$key], $offset)));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setISBNAndPrices(array $marcInfos)
    {
        $keyAdditionalList = ['ISBN', 'ISBN及定价', 'ISMN及定价', 'ISRC及定价', 'ISRN及定价', 'ISSN', 'ISSN及定价', 'STRN'];
        if (empty($this->ISBNs)) {
            $this->ISBNs = [];
        }
        $offset = count($this->ISBNs);
        $additional = $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset);
        foreach ($additional as $i => $isbn) {
            $seperated = explode('/', $isbn['value']);
            $additional[$i]['value'] = $seperated[0];
            $additional[$i]['compressed'] = str_replace([' ', '-'], '', trim($seperated[0]));
            $additional[$i]['price']  = (isset($seperated[1]) && !empty($seperated[1])) ? $seperated[1] : '';
        }
        $this->ISBNs = array_merge($this->ISBNs, $additional);
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    /*
    public function setPrices(array $marcInfos)
    {
        $keyAdditionalList = ['ISBN', 'ISBN及定价', 'ISMN及定价', 'ISRC及定价', 'ISRN及定价', 'ISSN', 'ISSN及定价', 'STRN'];
        $offset = count($this->ISBNs);
        $this->ISBNs = array_merge($this->ISBNs, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }
    */

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setSubjects(array $marcInfos)
    {
        $keyAdditionalList = ['个人名称主题', '会议名称主题', '作者题名主题', '团体名称主题', '地名主题', '地理名称主题',
            '学科主题', '家族名称主题', '统一题名主题', '论题主题', '非控制主题', '非控制主题词', '题名主题'];
        if (empty($this->subjects)) {
            $this->subjects = [];
        }
        $offset = count($this->subjects);
        $results = $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset);
        $splitted = [];
        foreach ($results as $result) {
            $subjects = (array_unique(explode('-', $result['value'])));
            foreach ($subjects as $key => $subject) {
                if (empty($subject)) {
                    unset($subjects[$key]);
                }
            }
            if (!array_key_exists($result['key'], $splitted)) {
                $splitted[$result['key']] = $subjects;
            } else {
                $splitted[$result['key']] = array_merge($splitted[$result['key']], $subjects);
            }
        }
        $subjects = [];
        foreach ($splitted as $key => $s) {
            $splitted[$key] = array_flip($s);
            foreach ($splitted[$key] as $k => $subject) {
                $subjects[] = [
                    'key' => $key,
                    'value' => $k,
                ];
            }
        }
        $this->subjects = $subjects;
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setClassifications(array $marcInfos)
    {
        $keyAdditionalList = ['中图法分类号', '人大法分类号', '科图法分类号', '四库分类号', '其他分类号', '杜威等其它类号'];
        if (empty($this->classifications)) {
            $this->classifications = [];
        }
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
            $this->copies = array_merge($this->copies, [$index]);
        }
    }

    /**
     * @param MarcStatus|string $marcStatus
     */
    public function setType($marcStatus)
    {
        if ($marcStatus instanceof MarcStatus) {
            $marcStatus = $marcStatus->type;
        }
        $this->type = $marcStatus;
    }

    /**
     * @param MarcStatus|string $marcStatus
     */
    public function setStatus($marcStatus)
    {
        if ($marcStatus instanceof MarcStatus) {
            $marcStatus = $marcStatus->status;
        }
        $this->status = $marcStatus;
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setNotes(array $marcInfos)
    {
        $keyAdditionalList = ['一般附注', '专款信息附注', '丛编附注', '主题检索附注', '书目附注', '传记或历史附注',
            '使用对象附注', '内容附注', '出版发行附注', '出版周期附注', '制作者附注', '制作附注', '前题名附注',
            '原作版本附注', '原版附注', '合订附注', '复制品附注', '学位论文附注', '引文/参考附注', '引文附注',
            '报告附注', '提要文摘附注', '摘要附注', '收藏地附注', '文献获奖附注', '时间地点附注', '标识号附注',
            '演出者附注', '版本附注', '特殊细节附注', '相关题名附注', '系统细节附注', '索引文摘附注', '累积索引附注',
            '编号特点附注', '著录信息附注', '补编附注', '表演者附注', '装订获得附注', '计算机文件附注', '计算机类型附注',
            '计算机细节附注', '语种附注', '读者对相附注', '责任者附注', '载体形态附注', '连接字段附注', '连接款目附注',
            '采访附注', '题名责任附注'];
        if (empty($this->notes)) {
            $this->notes = [];
        }
        $offset = count($this->notes);
        $this->notes = array_merge($this->notes, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
    }

    /**
     * @param MarcInfo[] $marcInfos
     */
    public function setElectronicResources(array $marcInfos)
    {
        $keyAdditionalList = ['电子资源'];
        if (empty($this->electronicResources))
        {
            $this->electronicResources = [];
        }
        $offset = count($this->electronicResources);
        $this->electronicResources = array_merge($this->electronicResources, $this->populateKeyValuePairs($marcInfos, $keyAdditionalList, $offset));
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
            'electronicResources', // 电子资源
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
            'authors',
            'presses',
            'forms',
            'ISBNs',
            'subjects',
            'classifications',
            'copies',
            'notes',
            'electronicResources',
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
                        'type' => 'keyword',
                    ],
                    'titles' => [
                        'properties' => [
                            'value' => [
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                            'key' => [
                                'type' => 'keyword',
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
                            'duty' => [
                                'type' => 'keyword',
                            ],
                            'key' => [
                                'type' => 'keyword',
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
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                            'date' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'ISBNs' => [
                        'properties' => [
                            'key' => [
                                'type' => 'keyword',
                            ],
                            'value' => [
                                'type' => 'keyword',
                            ],
                            'compressed' => [
                                'type' => 'keyword',
                            ],
                            'price' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'forms' => [
                        'properties' => [
                            'form' => [
                                'type' => 'text',
                            ],
                        ],
                    ],
                    'subjects' => [
                        'properties' => [
                            'key' => [
                                'type' => 'keyword',
                            ],
                            'value' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'classifications' => [
                        'properties' => [
                            'key' => [
                                'type' => 'keyword',
                            ],
                            'value' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'copies' => [
                        'properties' => [
                            'barcode' => [
                                'type' => 'keyword',
                            ],
                            'call_no' => [
                                'type' => 'keyword',
                            ],
                            'position' =>[
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                            'status' => [
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                            'volume_period' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'notes' => [
                        'properties' => [
                            'key' => [
                                'type' => 'keyword',
                            ],
                            'value' => [
                                'type' => 'text',
                                'analyzer' => 'ik_smart',
                                'search_analyzer' => 'ik_smart',
                            ],
                        ],
                    ],
                    'electronicResources' => [
                        'properties' => [
                            'key' => [
                                'type' => 'keyword',
                            ],
                            'value' => [
                                'type' => 'keyword',
                            ],
                        ]
                    ]
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
        return true;
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

    public function setInfoAttributes(array $infos)
    {
        $this->setTitles($infos);
        $this->setAuthors($infos);
        $this->setPresses($infos);
        $this->setISBNAndPrices($infos);
        $this->setForms($infos);
        $this->setClassifications($infos);
        $this->setNotes($infos);
        $this->setSubjects($infos);
        $this->setElectronicResources($infos);
    }

    public function setCopyAttributes(array $copies)
    {
        $this->setCopies($copies);
    }

    public function setStatusAttributes(MarcStatus $status)
    {
        $this->setType($status);
        $this->setStatus($status);
    }

    /**
     * @return string|MarcQuery
     */
    public static function find()
    {
        return \Yii::createObject(MarcQuery::class, [get_called_class()]);
    }
#endregion
}
