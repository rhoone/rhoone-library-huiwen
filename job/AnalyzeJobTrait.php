<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2019 vistart
 * @license https://vistart.me/license/
 */

namespace rhoone\library\providers\huiwen\job;

use rhoone\library\providers\huiwen\models\mongodb\MarcCopy;
use rhoone\library\providers\huiwen\models\mongodb\MarcInfo;
use rhoone\library\providers\huiwen\models\mongodb\MarcNo;
use rhoone\library\providers\huiwen\models\mongodb\MarcStatus;
use rhoone\library\providers\huiwen\models\mongodb\DownloadedContent;
use simplehtmldom_1_5\simple_html_dom_node;
use Sunra\PhpSimple\HtmlDomParser;

trait AnalyzeJobTrait
{
    /**
     * @var string
     */
    public $marcSelector = '#item_detail .booklist';

    /**
     * @var string
     */
    public $bookSelector = 'div#tabs2 table#item tbody .whitetext';

    /**
     * @var string
     */
    public $statusSelector = 'div#mainbox div#container div#content_item div.book_article p#marc';

    /**
     * 解码 GBK。将 GBK 码转换为对应的文字。
     * @param $str
     * @param string $prefix
     * @param string $postfix
     * @param bool $ignore_non_gbk
     * @return string
     */
    private function gbk_decode($str, $prefix = '\&#x', $postfix = ';', $ignore_non_gbk = false)
    {
        /**
         * GBK 模式。
         * 例如 &#xffe5; 代表 ￥
         * 目前只能识别十六进制编码。
         * TODO: 识别十进制编码。
         */
        $gbk_pattern = "/" . $prefix . "[0-9a-zA-Z]{4}$postfix/";

        /**
         * 待搜索字符串偏移量。
         */
        $offset = 0;

        /**
         * 解码结果。
         */
        $result = "";
        while ($offset < strlen($str)) {
            $matches = null;
            $seperate = "";

            /**
             * 只匹配第一个匹配的字符串，同时得出偏移量。
             * 由于 $matches 中给出的偏移量并非以字节为准，故不采用其作为偏移量依据。而是每匹配一次，就排除已匹配结果。
             */
            preg_match($gbk_pattern, substr($str, $offset),$matches, PREG_OFFSET_CAPTURE);
            if (empty($matches) || empty($matches)) {
                continue;
            }
            if ($matches[0][1] > 0) { // 若条件成立，则代表第一个匹配值前有非gbk编码字符。
                $seperate .= substr($str, $offset, $matches[0][1]);
                $offset += strlen($seperate);
                if (!$ignore_non_gbk) { // 若不忽略非 GBK 字符，则附加在结果中。
                    $result .= $seperate;
                }
                continue;
            }

            /**
             * 附加单次匹配结果。
             */
            $seperate .= $matches[0][0];

            /**
             * 修改偏移量。
             * 注意，此处不使用 $matches 中提供的偏移量，因为那个值并非按字节衡量。
             */
            $offset += strlen($seperate);
            $result .= mb_chr((int)base_convert(substr($matches[0][0], 3, 4), 16, 10));
        }
        return $result;
    }

    public $exceptMarcInfoList = ['豆瓣简介'];

    /**
     * @param simple_html_dom_node[]|simple_html_dom_node|null $dom
     * @return string[]
     */
    public function analyzeMarc($dom)
    {
        $results = [];
        foreach ($dom as $i) {
            $header = $i->find('dt');
            if (empty($header[0]->text())) {
                continue;
            }
            $result = $this->analyzeMarcValue($i);
            $key = rtrim($header[0]->text(), ':：');
            if (in_array($key ,$this->exceptMarcInfoList)) {
                continue;
            }
            $value = $result[0];
            $results[] = ['key' => $key, 'value' => $value];
            //var_dump($results[$this->_currentMarcNo]);
        }
        return $results;
    }

    /**
     * @param simple_html_dom_node[]|simple_html_dom_node|null $dom
     * @return string
     */
    public function analyzeMarcValue($dom)
    {
        $contents = $dom->find('dd');
        $result = [];
        foreach ($contents as $content) {
            $result[] = $this->gbk_decode(trim($content->text()));
        }
        return $result;
    }

    /**
     * @var string[]
     */
    public $emptyMarcInfoList = ['题名/责任者'];

    /**
     * @param string[] $marcInfos
     */
    protected function isEmptyMarc(array $marcInfos)
    {
        foreach ($this->emptyMarcInfoList as $key)
        {
            foreach ($marcInfos as $info) {
                if ($key == $info['key'] && !empty($info['value'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param string $subject
     * @param string $tagName
     * @return string
     */
    protected function removeTag(string $subject, string $tagName) : string
    {
        $tagStart = "<$tagName>";
        $tagEnd = "</$tagName>";
        $result = str_replace($tagEnd, "", str_replace($tagStart, "", $subject));
        return $result;
    }

    /**
     * @var string[]
     */
    public $marcNos;

    /**
     * @param simple_html_dom_node[]|simple_html_dom_node|null $dom
     * @return array
     */
    public function analyzeBookCopy($dom)
    {
        $booksAttributes = [];
        foreach ($dom as $book) {
            $book = HtmlDomParser::str_get_html($this->removeTag($book, "span"));
            $item = $book->find('td');
            if (count($item) < 5) {
                continue;
            }
            $bookAttribute = [];
            $bookAttribute['marc_no'] = $this->_currentMarcNo;
            $bookAttribute['call_no'] = trim(str_replace('&nbsp;', ' ', htmlspecialchars_decode($item[0]->text())));
            $bookAttribute['barcode'] = trim(str_replace('&nbsp;', ' ', htmlspecialchars_decode($item[1]->text())));
            $bookAttribute['volume_period'] = trim(str_replace('&nbsp;', ' ', htmlspecialchars_decode($item[2]->text())));
            $bookAttribute['position'] = trim(str_replace('&nbsp;', ' ', htmlspecialchars_decode($item[3]->text())));
            $bookAttribute['status'] = trim(str_replace('&nbsp;', ' ', htmlspecialchars_decode($item[4]->text())));
            $booksAttributes[$bookAttribute['barcode']] = $bookAttribute;
        }
        return $booksAttributes;
    }

    /**
     * @param simple_html_dom_node[]|simple_html_dom_node|null $dom
     */
    public function analyzeStatus($dom)
    {
        if (empty($dom)) {
            return [];
        }
        return $dom[0]->innertext();
    }


    public function analyze(string $html)
    {
        try {
            $dom = HtmlDomParser::str_get_html($html);
        } catch (\Exception $ex) {
            file_put_contents("php://stderr", __LINE__ . $ex->getMessage() . "\n");
        }
        $marcNoClass = $this->marcNoClass;
        $marcNo = $marcNoClass::getOneOrCreate($this->_currentMarcNo);
        /* @var $marcNo MarcNo */
        $downloadedContent = $marcNo->downloadedContent;
        if ($downloadedContent) {
            $marcNo->last_downloaded_content_version = $downloadedContent->version;
        }
        //var_dump($marcNo->attributes);
        if (!$marcNo->save()) {
            file_put_contents("php://stderr", print_r($marcNo->getErrorSummary()));
        }
        try {
            $marcResults = $this->analyzeMarc($dom->find($this->marcSelector));
        } catch (\Exception $ex) {
            $marcNo->error_analyzing = true;
            $marcNo->reason_analyzing = $ex->getMessage();
            return $marcNo->save();
        }

        if ($this->isEmptyMarc($marcResults)) {
            $marcNo->empty = true;
            return $marcNo->save();
        }

        $marcInfoClass = $this->marcInfoClass;
        $deleted = $marcInfoClass::deleteAll(['marc_no' => $this->_currentMarcNo]);
        //file_put_contents("php://stdout", "deleted $deleted info(s) from " . $this->_currentMarcNo . "\n");
        foreach ($marcResults as $key => $result)
        {
            $marcInfo = $marcInfoClass::getOneOrCreate($this->_currentMarcNo, $result['key'], $result['value']);
            /* @var $marcInfo MarcInfo */
            if (!$marcInfo->save()) {
                file_put_contents("php://stderr", print_r($marcInfo->getErrorSummary()));
            }
        }
        //var_dump($this->isEmptyMarc($marcResults));

        try {
            $booksAttributes = $this->analyzeBookCopy($dom->find($this->bookSelector));
        } catch (\Exception $ex) {
            $marcNo->error_analyzing = true;
            $marcNo->reason_analyzing = $ex->getMessage();
            return $marcNo->save();
        }

        $marcCopyClass = $this->marcCopyClass;
        foreach ($booksAttributes as $marc_no => $attributes)
        {
            $book = $marcCopyClass::getOneOrCreate($attributes['marc_no'], $attributes['barcode'], $attributes['call_no'], $attributes['volume_period'], $attributes['position'], $attributes['status']);
            /* @var $book MarcCopy */
            if (!$book->save()) {
                file_put_contents("php://stderr", print_r($book->getErrorSummary()));
            }
        }

        try {
            $statusInnerText = $this->analyzeStatus($dom->find($this->statusSelector));
        } catch (\Exception $ex) {
            $marcNo->error_analyzing = true;
            $marcNo->reason_analyzing = $ex->getMessage();
            return $marcNo->save();
        }

        $marcStatusClass = $this->marcStatusClass;
        $marcStatus = $marcStatusClass::getOneOrCreate($this->_currentMarcNo, $statusInnerText);
        /* @var $marcStatus MarcStatus */
        if (!$marcStatus->save()) {
            $marcNo->error_analyzing = true;
            $marcNo->reason_analyzing = $ex->getMessage();
            return $marcNo->save();
        }

        return $marcNo->save();
    }

    /**
     * @var string Current Marc No.This field is only used to temporarily store the current Marc No when iterating
     * access to the Marc No list. If it is not iterative access, do not directly access it.
     */
    private $_currentMarcNo;

    /**
     * @return int
     */
    public function batchAnalyze() : int
    {
        file_put_contents("php://stdout", count($this->marcNos) . " tasks received.\n");
        $class = $this->downloadedContentClass;
        $count = 0;
        //$downloadedContents = $class::find()->where(['marc_no' => array_values($this->marcNos)])->all();
        //foreach ($downloadedContents as $downloadedContent)
        foreach ($this->marcNos as $key => $marcNo)
        {
            ///* @var $downloadedContent DownloadedContent */
            //$this->_currentMarcNo = $downloadedContent->marc_no;
            $this->_currentMarcNo = $marcNo;
            $downloadedContent = $class::find()->where(['marc_no' => $this->_currentMarcNo])->one();
            $this->analyze($downloadedContent->html);
            //file_put_contents("php://stdout", $this->_currentMarcNo . "\n");
            $count++;
            printf("progress: [%-50s] %d%% Done.\r", str_repeat('#', $count / count($this->marcNos) * 50), $count / count($this->marcNos) * 100);
        }
        file_put_contents("php://stdout", "\n");
        file_put_contents("php://stdout", count($this->marcNos) . " tasks finished.\n");
        return 0;
    }

    /**
     * @return int
     */
    public function getTtr() : int
    {
        return count($this->marcNos);
    }
}