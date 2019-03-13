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

namespace rhoone\library\providers\huiwen\models\mongodb;

use rhosocial\base\models\models\BaseMongoEntityModel;

/**
 * Class MarcStatus
 *
 * @property-read MarcNo $marcNo
 * @package rhoone\library\providers\huiwen\models\mongodb
 */
class MarcStatus extends BaseMongoEntityModel
{
    public $enableIP = 0;

    public function init()
    {
        $this->queryClass = StatusQuery::class;
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['page_visit'], 'integer', 'min' => 0],
            [['status', 'type'], 'string', 'max' => 255],
            [['marc_no'], 'exist', 'skipOnError' => true, 'targetClass' => Marc::class, 'targetAttribute' => ['marc_no' => 'marc_no']],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'GUID',
            'marc_no' => 'MARC编号',
            'status' => 'MARC状态',
            'type' => '文献类型',
            'page_visit' => '浏览次数',
        ];
    }

    public function setMarcStatus($marc_status)
    {
        $marc_status = trim(str_replace("<span></span>", "", $marc_status));
        $status = $this->extractStatus($marc_status);
        if ($status !== false) {
            $this->status = $status;
        }
        $type = $this->extractType($marc_status);
        if ($type !== false)
        {
            $this->type = $type;
        }
        $pageVisit = $this->extractPageVisit($marc_status);
        if ($pageVisit !== false)
        {
            $this->page_visit = $pageVisit;
        }
    }

    public $statusRegex = "~MARC状态：[\x{4e00}-\x{9fa5}]+~u";

    /**
     * @param $marc_status
     * @return bool|array
     */
    public function extractStatus($marc_status)
    {
        $matches = [];
        if (preg_match($this->statusRegex, $marc_status, $matches))
        {
            return str_replace("MARC状态：", "", $matches[0]);
        }
        return false;
    }

    public $typeRegex = "~文献类型：[\x{4e00}-\x{9fa5}]+~u";

    /**
     * @param $marc_status
     * @return bool|array
     */
    public function extractType($marc_status)
    {
        $matches = [];
        if (preg_match($this->typeRegex, $marc_status, $matches))
        {
            return str_replace("文献类型：", "", $matches[0]);
        }
        return false;
    }

    public $pageVisitRegex = "~浏览次数：\d*~u";

    /**
     * @param $marc_status
     * @return bool|array
     */
    public function extractPageVisit($marc_status)
    {
        $matches = [];
        if (preg_match($this->pageVisitRegex, $marc_status, $matches))
        {
            return str_replace("浏览次数：", "", $matches[0]);
        }
        return false;
    }

    /**
     * @return MarcNoQuery
     */
    public function getMarcNo()
    {
        return $this->hasOne(MarcNo::class, ['marc_no' => 'marc_no'])->inverseOf('marcStatus');
    }
}
