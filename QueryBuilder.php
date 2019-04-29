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
                'titles.value^10',
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
                    'query' => $this->keywords,
                    'type' => 'best_fields',
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
