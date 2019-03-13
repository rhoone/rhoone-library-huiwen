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
 * @property string $marc_no
 * @property string $status
 * @property string $type
 * @property int $page_visit
 * @property int $version
 * @property-read MarcNo $marcNo
 * @property-write string $marcStatus
 * @package rhoone\library\providers\huiwen\models\mongodb
 */
class MarcStatus extends BaseMongoEntityModel
{
    public $enableIP = 0;

    /**
     * @var string
     */
    public $marcNoClass = MarcNo::class;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->queryClass = MarcStatusQuery::class;
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
            [['marc_no'], 'exist', 'skipOnError' => true, 'targetClass' => $this->marcNoClass, 'targetAttribute' => ['marc_no' => 'marc_no']],
        ]);
    }

    /**
     * {@inheritdoc}
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function attributes()
    {
        $parent = parent::attributes();
        return array_merge($parent, [
            'marc_no', 'status', 'type', 'page_visit',
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

    /**
     * @param $marc_status
     */
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
            $this->page_visit = intval($pageVisit);
        }
    }

    /**
     * @var string
     */
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

    /**
     * @var string
     */
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

    /**
     * @var string
     */
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
        return $this->hasOne($this->marcNoClass, ['marc_no' => 'marc_no'])->inverseOf('marcStatus');
    }

    /**
     * @param string $marc_no
     * @param string $innertext
     * @return static
     */
    public static function getOneOrCreate(string $marc_no, string $innertext)
    {
        $status = static::find()->where(['marc_no' => $marc_no])->one();
        if (!$status) {
            $status = new static(['marc_no' => $marc_no]);
        }
        /* @var $status static */
        $status->marcStatus = $innertext;
        return $status;
    }
}
