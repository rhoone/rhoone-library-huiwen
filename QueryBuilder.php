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

    /**
     * @param string $keywords
     */
    public function setKeywords(string $keywords)
    {
        $this->_keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->_keywords;
    }

    /**
     * @param string $delimiter
     * @return array
     */
    public function getSeperatedKeywords(string $delimiter = ' ')
    {
        return explode($delimiter, $this->keywords);
    }

    /**
     * @param array $keywords
     * @param string $glue
     */
    public function setSeperatedKeywords(array $keywords, string $glue = " ")
    {
        $this->keywords = implode($glue, $keywords);
    }

    /**
     * @param array $keywords
     * @return array
     */
    protected function selectBarcode(array $keywords)
    {
        return [];
    }

    /**
     * @param array $keywords
     * @return array
     */
    protected function selectMarcNo(array $keywords)
    {
        return [];
    }

    /**
     * @param array $keywords
     * @return array
     */
    protected function selectISBNs(array $keywords)
    {
        $isbn10Regex = '/^\d{9}(\d|[xX])$/';
        $isbn13Regex = '/^(97(8|9))?\d{9}(\d|[xX])$/';
        $issn8Regex = '/^\d{8}$/';
        $isrc12Regex = '/^[a-zA-Z]{3}\d{9}$/';
        $pointers = [];
        foreach ($keywords as $i => $keyword)
        {
            $keyword = str_replace([' ', '-'], '', trim($keyword));
            if ((strlen($keyword) == 10 && preg_match($isbn10Regex, $keyword) && substr($keyword, 0, 3) != '000') ||
                (strlen($keyword) == 13 && preg_match($isbn13Regex, $keyword)) ||
                (strlen($keyword) == 8 && preg_match($issn8Regex, $keyword)) ||
                (strlen($keyword) == 12 && preg_match($isrc12Regex, $keyword))
            )
            {
                $pointers[] = $i;
            }
        }
        \Yii::info("ISBN Pointers in Keyword Array: " . implode(", ", $pointers));
        return $pointers;
    }

    /**
     * @param array $keywords
     * @return array
     */
    protected function selectCallNo(array $keywords)
    {
        return [];
    }

    /**
     * @param string $type
     * @param string $key
     * @param $value
     * @param int $boost
     * @param bool $expanded
     * @return array
     */
    public function buildTermLevelQueryClause(string $type, string $key, $value, $boost = 1, bool $expanded = false)
    {
        if ($boost = 1 && !$expanded) {
            return [$type => [$key => $value]];
        }
        return [$type => [$field => [
            'value' => $value,
            'boost' => $boost,
        ]]];
    }

    /**
     * @param string $key
     * @param $value
     * @param int $boost
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-term-query.html
     * @see QueryBuilder::buildTermLevelQueryClause()
     */
    public function buildTermQueryClause(string $field, $value, $boost = 1)
    {
        return $this->buildTermLevelQueryClause('term', $field, $value, $boost);
    }

    /**
     * @param string $field
     * @param array $value
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-terms-query.html
     */
    public function buildTermsQueryClause(string $field, array $value)
    {
        return [
            'terms' => [
                $field => $value,
            ]
        ];
    }

    /**
     * @param string $field
     * @param array $value
     * @param string|null $minimum_should_match_field
     * @param array|null $minimum_should_match_script
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-terms-set-query.html
     */
    public function buildTermsSetQueryClause(string $field, array $value, string $minimum_should_match_field = null, array $minimum_should_match_script = null)
    {
        $clause = [
            'terms_set' => [
                $field => $value,
            ],
        ];
        if (!empty($minimum_should_match_field))
        {
            $clause['terms_set']['minimum_should_match_field'] = $minimum_should_match_field;
        }
        if (!empty($minimum_should_match_script))
        {
            $clause['terms_set']['minimum_should_match_script'] = $minimum_should_match_script;
        }
        return $clause;
    }

    /**
     * @param string $field
     * @param mixed $gte
     * @param mixed $gt
     * @param mixed $lte
     * @param mixed $lt
     * @param int $boost
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-range-query.html
     */
    public function buildRangeQueryClause(string $field, $gte = null, $gt = null, $lte = null, $lt = null, $boost = 1)
    {
        $clause = [
            'range' => [
                $field => [

                ]
            ]
        ];
        if ($gte != null)
        {
            $clause['range'][$field]['gte'] = $gte;
        }
        if ($gt != null)
        {
            $clause['range'][$field]['gt'] = $gt;
        }
        if ($lte != null)
        {
            $clause['range'][$field]['lte'] = $lte;
        }
        if ($lt != null)
        {
            $clause['range'][$field]['lt'] = $lt;
        }
        if ($boost != 1)
        {
            $clause['range'][$field]['boost'] = $boost;
        }
        return $clause;
    }

    /**
     * @param string $field
     * @param mixed $gte
     * @param mixed $gt
     * @param mixed $lte
     * @param mixed $lt
     * @param int $boost
     * @param string|null $format
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-range-query.html#_date_format_in_range_queries
     */
    public function buildDateRangeQueryClause(string $field, $gte = null, $gt = null, $lte = null, $lt = null, $boost = 1, string $format = null)
    {
        $clause = $this->buildRangeQueryClause($field, $gte, $gt, $lte, $lt, $boost);
        if ($format !== null) {
            $clause['range'][$field]['format'] = $format;
        }
        return $clause;
    }

    /**
     * @param string $field
     * @param mixed $gte
     * @param mixed $gt
     * @param mixed $lte
     * @param mixed $lt
     * @param int $boost
     * @param string|null $time_zone
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-range-query.html#_date_format_in_range_queries
     */
    public function buildTimeZoneRangeQueryClause(string $field, $gte = null, $gt = null, $lte = null, $lt = null, $boost = 1, string $time_zone = null)
    {
        $clause = $this->buildRangeQueryClause($field, $gte, $gt, $lte, $lt, $boost);
        if ($format !== null) {
            $clause['range'][$field]['time_zone'] = $time_zone;
        }
        return $clause;
    }

    /**
     * @param string $field
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-exists-query.html
     */
    public function buildExistsQueryClause(string $field)
    {
        return [
            'exists' => [
                'field' =>$field,
            ],
        ];
    }

    /**
     * @param string $field
     * @param $value
     * @param int $boost
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-prefix-query.html
     * @see QueryBuilder::buildTermLevelQueryClause()
     */
    public function buildPrefixQueryClause(string $field, $value, $boost = 1)
    {
        return $this->buildTermLevelQueryClause('prefix', $field, $value, $boost);
    }

    /**
     * @param string $field
     * @param $value
     * @param int $boost
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-wildcard-query.html
     * @see QueryBuilder::buildTermLevelQueryClause()
     */
    public function buildWildcardQueryClause(string $field, $value, $boost = 1)
    {
        return $this->buildTermLevelQueryClause('wildcard', $field, $value, $boost);
    }

    /**
     * @param string $field
     * @param $value
     * @param int $boost
     * @param string|null $flags
     * @param string|null $max_determinized_states
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-regexp-query.html
     * @see QueryBuilder::buildTermLevelQueryClause()
     */
    public function buildRegexpQueryClause(string $field, $value, $boost = 1, string $flags = null, string $max_determinized_states = null)
    {
        $clause = $this->buildTermLevelQueryClause('regexp', $field, $value, $boost, true);
        if ($flags != null)
        {
            $clause['regexp'][$field]['flags'] = $flags;
        }
        if ($max_determinized_states != null)
        {
            $clause['regexp'][$field]['max_determinized_states'] = $max_determinized_states;
        }
        return $clause;
    }

    /**
     * @param string $field
     * @param $value
     * @param int $boost
     * @param int|null $prefix_length
     * @param int|null $max_expansions
     * @param bool|null $transpositions
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-fuzzy-query.html
     * @see QueryBuilder::buildTermLevelQueryClause()
     */
    public function buildFuzzyQueryClause(string $field, $value, $boost = 1, int $prefix_length = null, int $max_expansions = null, bool $transpositions = null)
    {
        $clause = $this->buildTermLevelQueryClause('fuzzy', $field, $value, $boost, true);
        if ($prefix_length != null) {
            $clause['fuzzy'][$field]['prefix_length'] = $prefix_length;
        }
        if ($max_expansions != null) {
            $clause['fuzzy'][$field]['max_expansions'] = $max_expansions;
        }
        if ($transpositions != null) {
            $clause['fuzzy'][$field]['transpositions'] = $transpositions;
        }
        return $clause;
    }

    /**
     * @return array
     * @deprecated This function has been deprecated since ElasticSearch 7.0 released.
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-type-query.html
     * @see QueryBuilder::buildTermLevelQueryClause()
     */
    public function buildTypeQueryClause()
    {
        return $this->buildTermLevelQueryClause('type', 'value', '_doc');
    }

    /**
     * @param array $values
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-ids-query.html
     * @see QueryBuilder::buildTermLevelQueryClause()
     */
    public function buildIdsQueryClause(array $values)
    {
        return $this->buildTermLevelQueryClause('ids', 'values', $values);
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
        $pointersCallNo = $this->selectCallNo($this->seperatedKeywords);
        $pointersISBN = $this->selectISBNs($this->seperatedKeywords);
        $pointersBarcode = $this->selectBarcode($this->seperatedKeywords);
        $pointersMarcNo = $this->selectMarcNo($this->seperatedKeywords);

        if (empty($pointersCallNo) && empty($pointersISBN) && empty($pointersBarcode) && empty($pointersMarcNo))
        {
            $fields = [
                'titles.value^5',
                'authors.author^5',
                'presses.press^3',
                'subjects.value^2',
                'notes.value',
                //'copies.position^1.2',
                //'copies.status^1.2',
                //'copies.volume_period^1.2',
                //'classifications.value.1^5',
                //'status',
            ];

            \Yii::info("Search fields: " . implode(", ", $fields));

            return [
                'multi_match' => [
                    'query' => $this->seperatedKeywords,
                    'fields' => $fields,
                    'tie_breaker' => 0.3,
                    'minimum_should_match' => '30%',
                ]
            ];
        }
        $should = [];

        $unsetKeywords = $this->seperatedKeywords;
        if (!empty($pointersMarcNo)) {
            foreach ($pointersMarcNo as $pointer) {
                $should[] = ['term' => ['marc_no' => $this->seperatedKeywords[$pointer]]];
                unset($unsetKeywords[$pointer]);
            }
        }
        if (!empty($pointersBarcode)) {
            foreach ($pointersBarcode as $pointer) {
                $should[] = ['term' => ['copies.barcode' => $this->seperatedKeywords[$pointer]]];
                unset($unsetKeywords[$pointer]);
            }
        }
        if (!empty($pointersISBN)) {
            foreach ($pointersISBN as $pointer) {
                $should[] = ['term' => ['ISBNs.compressed' => str_replace([' ', '-'], '', trim($this->seperatedKeywords[$pointer]))]];
                unset($unsetKeywords[$pointer]);
            }
        }
        if (!empty($pointersCallNo)) {
            foreach ($pointersCallNo as $pointer) {
                $should[] = ['match' => ['copies.call_no' => $this->seperatedKeywords[$pointer]]];
                unset($unsetKeywords[$pointer]);
            }
        }

        $fields = [
            'titles.value^10',
            'authors.author^5',
            'presses.press^3',
            'subjects.value^2',
            //'copies.position^1.2',
            //'copies.status^1.2',
            //'copies.volume_period^1.2',
            //'classifications.value.1^5',
            //'status',
        ];

        foreach ($unsetKeywords as $keyword)
        {
            $should[] = [
                'term' => [
                    'titles.value^5' => $keyword
                ]
            ];
            $should[] = [
                'term' => [
                    'authors.author^5' => $keyword
                ]
            ];
            $should[] = [
                'term' => [
                    'presses.press^3' => $keyword
                ]
            ];
            $should[] = [
                'term' => [
                    'subjects.value' => $keyword
                ]
            ];
        }

        return [
            'bool' => [
                'should' => $should,
            ],
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
}
