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
abstract class Library extends \rhoone\library\Library implements ILibraryQueryOperation
{
    public $marcClass = \rhoone\library\providers\huiwen\models\elasticsearch\Marc::class;

    public function search($keywords, array $config = null)
    {
        //$queryArray = $this->buildQueryArray($keywords);
        $config['keywords'] = $keywords;
        $queryBuilder = new $this->queryBuilderClass($config);
        /* @var $queryBuilder QueryBuilder */
        /* @var $query \rhoone\library\providers\huiwen\models\elasticsearch\MarcQuery */
        $query = $this->marcClass::find()->query($this->buildQueryArray($queryBuilder->seperatedWords, $queryBuilder))->highlight($queryBuilder->highlight);
        \Yii::info("Seperated Words: " . implode(", ", $queryBuilder->seperatedWords));
        $provider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $provider;
    }

    /**
     * @param array $keywords
     * @param QueryBuilder $queryBuilder
     * @return array
     */
    public function buildQueryArray(array $keywords, QueryBuilder $queryBuilder)
    {
        $queryArray = ['bool' => [
            'boost' => 1,
        ]];
        $filter = [];
        $callNos = ['bool' => ['should' => []]];
        $ISBNs = ['bool' => ['should' => []]];
        $barcodes = ['bool' => ['should' => []]];
        $marcNos = ['bool' => ['should' => []]];

        $should = [];
        $titles = [];
        $authors = [];
        $presses = [];
        $subjects = [];
        $notes = [];
        foreach ($keywords as $keyword)
        {
            if ($this->queryCallNo($keyword)->exists())
            {
                $callNos['bool']['should'][] = $queryBuilder->buildWildcardQueryClause('copies.call_no', "*$keyword*");
                \Yii::info("Keyword: `$keyword`, Call No matched.");
            }

            if ($this->queryISBN($keyword)->exists())
            {
                $ISBNs['bool']['should'][] = $queryBuilder->buildWildcardQueryClause('ISBNs.compressed', sprintf("*%s*", str_replace([' ', '-'], '', $keyword)));
                \Yii::info("Keyword: `$keyword`, ISBN matched.");
            }

            if ($this->queryBarcode($keyword)->exists())
            {
                $barcodes['bool']['should'][] = $queryBuilder->buildWildcardQueryClause('copies.barcode', "*$keyword*");
                \Yii::info("Keyword: `$keyword`, Barcode matched.");
            }

            if ($this->queryMarcNo($keyword)->exists())
            {
                $marcNos['bool']['should'][] = $queryBuilder->buildTermQueryClause('marc_no', $keyword);
                \Yii::info("Keyword: `$keyword`, MARC NO matched.");
            }

            if ($this->queryTitle($keyword)->exists())
            {
                $titles[] = $queryBuilder->buildMatchPhraseClause('titles.value', $keyword, ['boost' => 5]);
                \Yii::info("Keyword: `$keyword`, title phrase matched.");
            }

            if ($this->queryAuthor($keyword)->exists())
            {
                $authors[] = $queryBuilder->buildMatchPhraseClause('authors.author', $keyword, ['boost' => 5]);
                \Yii::info("Keyword: `$keyword`, author matched.");
            }

            if ($this->queryPress($keyword)->exists())
            {
                $presses[] = $queryBuilder->buildMatchPhraseClause('presses.press', $keyword, ['boost' => 3]);
                \Yii::info("Keyword: `$keyword`, press matched.");
            }
        }
        if (!empty($callNos['bool']['should'])) {
            $filter[] = $callNos;
        }
        if (!empty($ISBNs['bool']['should'])) {
            $filter[] = $ISBNs;
        }
        if (!empty($barcodes['bool']['should'])) {
            $filter[] = $barcodes;
        }
        if (!empty($marcNos['bool']['should'])) {
            $filter[] = $marcNos;
        }
        if (!empty($filter)) {
            $queryArray['bool']['filter'][] = $filter;
        }

        if (empty($titles) && empty($authors) && empty($presses)) {
            foreach ($keywords as $keyword) {
                if ($this->querySubject($keyword)->exists())
                {
                    $subjects[] = $queryBuilder->buildTermQueryClause('subjects.value', $keyword);
                    \Yii::info("Keyword: `$keyword`, subject matched.");
                }
            }
            if (empty($subjects)) {
                foreach ($keywords as $keyword) {
                    if ($this->queryNote($keyword)->exists())
                    {
                        $notes[] = $queryBuilder->buildMatchPhraseClause('notes.value', $keyword);
                        \Yii::info("Keyword: `$keyword`, note matched.");
                    }
                }
            }
        }

        $should = array_merge($should, $titles, $authors, $presses, $subjects, $notes);
        if (empty($should) && empty($filter)) {
            $queryArray = ['match_none' => (Object)array()];
        } else {
            $queryArray['bool']['should'] = $should;
        }
        return $queryArray;
    }
}
