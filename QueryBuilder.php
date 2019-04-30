<?php

/**
 *
 *    _   __ __ _____ _____ ___  ____  _____
 *   | | / // // ___//_  _//   ||  __||_   _|
 *   | |/ // /(__  )  / / / /| || |     | |
 *   |___//_//____/  /_/ /_/ |_||_|     |_|
 *   @link https://vistart.name/
 *   @copyright Copyright (c) 2016 - 2019 vistart
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
     * @deprecated 
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
     * @param int $boost
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-match-all-query.html
     */
    public function buildMatchAllQueryClause($boost = 1)
    {
        if ($boost != null && $boost != 1)
        {
            return [
                'match_all' => ['boost' => $boost],
            ];
        }
        return ['match_all' => []];
    }

    /**
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-match-all-query.html
     */
    public function buildMatchNoneQueryClause()
    {
        return ['match_none' => []];
    }

    /**
     * @param string $type
     * @param string $key
     * @param string $value_attribute
     * @param mixed $value
     * @param bool $expanded
     * @return array
     */
    public function buildQueryClause(string $type, string $key, string $value_attribute, $value, $expanded = false)
    {
        if ($expanded) {
            return [
                $type => [
                    $key => [
                        $value_attribute => $value,
                    ],
                ],
            ];
        }
        return [
            $type => [
                $key => $value,
            ],
        ];
    }

    /**
     * @param array $filters
     * @param int $boost
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-constant-score-query.html
     */
    public function buildConstantScoreQuery(array $filters, $boost = 1)
    {
        return [
            'constant_score' => [
                'filter' => $filters,
                'boost' => $boost,
            ]
        ];
    }

    /**
     * @param array|null $musts
     * @param array|null $filters
     * @param array|null $must_nots
     * @param array|null $shoulds
     * @param int $boost
     * @param string|null $minimum_should_match
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-bool-query.html
     */
    public function buildBoolQuery(array $musts = null, array $filters = null, array $must_nots = null, array $shoulds = null, $boost = 1, string $minimum_should_match = null)
    {
        $clause = [
            'bool' => [
            ]
        ];
        if ($musts != null) {
            $clause['bool']['must'] = $musts;
        }
        if ($filters != null) {
            $clause['bool']['filter'] = $filters;
        }
        if ($must_nots != null) {
            $clause['bool']['must_not'] = $must_nots;
        }
        if ($shoulds != null) {
            $clause['bool']['should'] = $shoulds;
        }
        if ($boost != null && $boost != 1) {
            $clause['bool']['boost'] = $boost;
        }
        if ($minimum_should_match != null) {
            $clause['bool']['minimum_should_match'] = $minimum_should_match;
        }
        return $clause;
    }

    /**
     * @param array $queries
     * @param int $boost
     * @param double|null $tie_breaker
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-dis-max-query.html
     */
    public function buildDisMaxQuery(array $queries, $boost = 1, double $tie_breaker = null)
    {
        $clause = [
            'dis_max' => [
                'queries' => $queries,
            ],
        ];
        if ($boost != null && $boost != 1)
        {
            $clause['dis_max']['boost'] = $boost;
        }
        if ($tie_breaker != null)
        {
            $clause['dis_max']['tie_breaker'] = $tie_breaker;
        }
        return $clause;
    }

    /**
     * @param $query
     * @param array $functions
     * @param int $boost
     * @param array $other
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-function-score-query.html
     * @todo weight
     * @todo random
     * @todo field value factor
     * @todo decay functions
     */
    public function buildFunctionScoreQuery($query, array $functions, $boost = 1, array $other)
    {
        $clause = [
            'function_score' => [
                'query' => $query,
            ],
        ];
        if ($functions != null)
        {
            $clause['function_score']['functions'] = $functions;
        }
        if ($boost != null && $boost != 1)
        {
            $clause['function_score']['boost'] = $boost;
        }
        if ($other != null && is_array($other))
        {
            foreach ($other as $k => $v)
            {
                $clause['function_score'][$k] = $v;
            }
        }
    }

    /**
     * @param array $positive
     * @param array $negative
     * @param $negative_boost
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-boosting-query.html
     */
    public function buildBoostingQuery(array $positive, array $negative, $negative_boost)
    {
        return [
            'boosting' => [
                'positive' => $positive,
                'negative' => $negative,
                'negative_boost' => $negative_boost,
            ]
        ];
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $other
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-match-query.html
     * @see QueryBuilder::buildQueryClause()
     */
    public function buildMatchClause(string $field, $value, array $other = null)
    {
        $clause = $this->buildQueryClause('match', $field, 'query', $value, true);
        if ($other != null && is_array($other))
        {
            foreach ($other as $k => $v)
            {
                $clause['match'][$field][$k] = $v;
            }
        }
        return $clause;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array|null $other
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-match-query-phrase.html
     * @see QueryBuilder::buildQueryClause()
     */
    public function buildMatchPhraseClause(string $field, $value, array $other = null)
    {
        $clause = $this->buildQueryClause('match_phrase', $field, 'query', $value, true);
        if ($other != null && is_array($other))
        {
            foreach ($other as $k => $v)
            {
                $clause['match_phrase'][$field][$k] = $v;
            }
        }
        return $clause;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array|null $other
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-match-query-phrase-prefix.html
     * @see QueryBuilder::buildQueryClause()
     */
    public function buildMatchPhrasePrefixClause(string $field, $value, array $other = null)
    {
        $clause = $this->buildQueryClause('match_phrase_prefix', $field, 'query', $value, true);
        if ($other != null && is_array($other))
        {
            foreach ($other as $k => $v)
            {
                $clause['match_phrase_prefix'][$field][$k] = $v;
            }
        }
        return $clause;
    }

    /**
     * @param mixed $value
     * @param array $fields
     * @param string|null $type
     * @param double|null $tie_breaker
     * @param string|null $operator
     * @param string|null $minimum_should_match
     * @param array|null $other
     * @return array
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/query-dsl-multi-match-query.html
     * @see QueryBuilder::buildQueryClause()
     */
    public function buildMultiMatchClause($value, array $fields, string $type = null, double $tie_breaker = null, string $operator = null, string $minimum_should_match = null, array $other = null)
    {
        $clause = $this->buildQueryClause('multi_match', $field, 'query', $value, true);
        if ($tie_breaker != null)
        {
            $clause['multi_match'][$field]['tie_breaker'] = $tie_breaker;
        }
        if ($operator != null)
        {
            $clause['multi_match'][$field]['operator'] = $operator;
        }
        if ($minimum_should_match != null)
        {
            $clause['multi_match'][$field]['minimum_should_match'] = $minimum_should_match;
        }
        if ($other != null && is_array($other))
        {
            foreach ($other as $k => $v)
            {
                $clause['multi_match'][$field][$k] = $v;
            }
        }
        return $clause;
    }

    /**
     * @param string $type
     * @param string $key
     * @param mixed $value
     * @param int|float $boost
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
     * @param mixed $value
     * @param int|float $boost
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
     * @param int|float $boost
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
     * @param int|float $boost
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
     * @param int|float $boost
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
     * @param mixed $value
     * @param int|float $boost
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
     * @param mixed $value
     * @param int|float $boost
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
     * @param mixed $value
     * @param int|float $boost
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
     * @param mixed $value
     * @param int|float $boost
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

    /**
     * @return array
     * @deprecated
     */
    public function getQueryArray()
    {
        return ([]);
    }
}
