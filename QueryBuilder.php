<?php

/**
 *
 *    _   __ __ _____ _____ ___  ____  _____
 *   | | / // // ___//_  _//   ||  __||_   _|
 *   | |/ // /(__  )  / / / /| || |     | |
 *   |___//_//____/  /_/ /_/ |_||_|     |_|
 *   @link https://vistart.name/
 *   @copyright Copyright (c) 2016 vistart
 *   @license https://vistart.name/license/
 *
 */
namespace rhoone\library\providers\huiwen;

use yii\base\Component;

/**
 * Build Query Array for ElasticSearch.
 * @property-read array $queryArray
 * @property-read array $queryOptions
 * @property string $keywords
 * @property string[] $seperatedKeywords
 *
 * @package rhoone\library\providers\huiwen\targets\tongjiuniversity
 */
class QueryBuilder extends Component
{
    private $_keywords = "";

    public function setKeywords(string $keywords)
    {
        $this->_keywords = $keywords;
    }

    public function getKeywords()
    {
        return $this->_keywords;
    }

    public function getSeperatedKeywords(string $delimiter = ' ')
    {
        return explode($delimiter, $this->keywords);
    }

    public function setSeperatedKeywords(array $keywords, string $glue = " ")
    {
        $this->keywords = implode($glue, $keywords);
    }

    protected function selectISBNs(array $keywords)
    {

    }

    protected function selectCallNo(array $keywords)
    {
        $classRegex = '/^{?\[?([A-K]|[N-V]|X|Z)[A-U]?\d{0,3}(?:\/\d{1,3})?a?(?:[.|\-|=|+]\d{1,3}|\(\d{1,3}\)|"\d{1,3}"|<\d{1,3}>)*\]?(?:}<(?:\[?([A-K]|[N-V]|X|Z)[A-U]?\d{0,3}(?:\/\d{1,3})?a?(?:[.|\-|=|+]\d{1,3}|\(\d{1,3}\)|"\d{1,3}"|<\d{1,3}>)*\]?)>)?(?:[:+](?:{?\[?([A-K]|[N-V]|X|Z)[A-U]?\d{0,3}(?:\/\d{1,3})?a?(?:[.|\-|=|+]\d{1,3}|\(\d{1,3}\)|"\d{1,3}"|<\d{1,3}>)*\]?(?:}<(?:\[?([A-K]|[N-V]|X|Z)[A-U]?\d{0,3}(?:\/\d{1,3})?a?(?:[.|\-|=|+]\d{1,3}|\(\d{1,3}\)|"\d{1,3}"|<\d{1,3}>)*\]?)>)?))*$/';
        $pointers = [];
        foreach ($keywords as $i => $keyword)
        {
            $seperated = explode('/', $keyword);
            if (empty($seperated)) {
                continue;
            }
            if (!preg_match($classRegex, $seperated[0], $matches)) {
                continue;
            }
            if ($matches[0] != $seperated[0]) {
                continue;
            }
            \Yii::info("Call No Matched: " . $keyword);
            $pointers[] = $i;
        }
        \Yii::info("Call No Pointers in Keyword Array: " . implode(", ", $pointers));
        return $pointers;
    }

    public function getQueryOptions()
    {
        return ([
            'highlight' => [
                'fields' => [
                    'titles.title' => [],
                ],
            ],
        ]);
    }

    public function getQueryArray()
    {
        $pointers = $this->selectCallNo($this->seperatedKeywords);
        $fields = [
            'titles.value^10',
            'authors.author^5',
            'presses.press^3',
            'marc_no^2',
            'subjects.value^2',
            //'books.position^1.2',
            //'books.status^1.2',
            'copies.barcode^3.8',
            //'books.volume_period^1.2',
            //'classes.1^5',
            'copies.call_no^5',
            'ISBNs.value^2.8',
            //'abstract',
            //'target_readers',
            //'status',
        ];

        return ([
            'multi_match' => [
                'query' => $this->keywords,
                'type' => 'best_fields',
                'fields' => $fields,
                'tie_breaker' => 0.3,
                'minimum_should_match' => '30%',
            ]
        ]);
    }

    /**
     * 构建检索条件矩阵。
     * @param string|array $keywords 关键词。若关键词是字符串，将转换为单元素数组。
     * @param null|array $fields 域键值对。
     * @return array 检索条件矩阵。
     */
    protected function buildQueryArray($keywords, $fields = null, $type = 'best_fields', $tie_breaker = 0.3, $minimum_should_match = '30%')
    {
        /**
         * 若 $fields 为空，将指定默认域和权重。
         */
        if (empty($fields)) {
            $fields = [
                'titles.value^10',
                'authors.author^5',
                'presses.press^3',
                'marc_no^2',
                'subjects.value^2',
                //'books.position^1.2',
                //'books.status^1.2',
                'copies.barcode^3.8',
                //'books.volume_period^1.2',
                //'classes.1^5',
                'copies.call_no^5',
                'ISBNs.value^2.8',
                //'abstract',
                //'target_readers',
                //'status',
            ];
        }

        return ([
            'multi_match' => [
                'query' => implode(' ', (array)$keywords),
                'type' => $type,
                'fields' => $fields,
                'tie_breaker' => $tie_breaker,
                'operator' => 'or',
                'minimum_should_match' => $minimum_should_match,
            ]
        ]);
    }
}
