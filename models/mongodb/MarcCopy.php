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

namespace rhoone\library\providers\huiwen\models\mongodb;

use rhosocial\base\models\models\BaseMongoEntityModel;

/**
 * Class MarcCopy
 *
 * @property string $marc_no
 * @property string $call_no
 * @property string $barcode
 * @property string $volume_period
 * @property string $position
 * @property string $status
 * @property int $version
 * @property-read MarcNo $marcNo
 * @package rhoone\library\providers\huiwen\models\mongodb
 */
class MarcCopy extends BaseMongoEntityModel
{
    public $enableIP = 0;

    /**
     * @var string
     */
    public $marcNoClass = MarcNo::class;

    /**
     * {@inheritdoc}
     */
    public static function collectionName()
    {
        throw new NotSupportedException("This method has not been implemented yet. Please specify collection name for your collection class.");
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        $parent = parent::attributes();
        return array_merge($parent, [
            'marc_no', 'call_no', 'barcode', 'volume_period', 'position', 'status', 'version'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        $noInit = static::buildNoInitModel();
        return [$noInit->guidAttribute, $noInit->idAttribute];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $parent = parent::rules();
        return array_merge($parent,[
            [['marc_no', 'call_no', 'barcode', 'position', 'status'], 'required'],
            [['marc_no', 'call_no', 'barcode', 'volume_period', 'position', 'status'], 'string'],
            ['version', 'integer', 'min' => 0],
            ['version', 'default', 'value' => 0],
            [['marc_no'], 'exist', 'skipOnError' => true, 'targetClass' => $this->marcNoClass, 'targetAttribute' => ['marc_no' => 'marc_no']],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', 'ID'),
            'guid' => Yii::t('app', 'Guid'),
            'marc_no' => Yii::t('app', 'Marc No'),
            'call_no' => Yii::t('app', 'Call No'),
            'barcode' => Yii::t('app', 'Barcode'),
            'volume_period' => Yii::t('app', 'Volume Period'),
            'position' => Yii::t('app', 'Position'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'version' => Yii::t('app', 'Version'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function optimisticLock()
    {
        return 'version';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->queryClass = MarcCopyQuery::class;
        return parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * @return MarcNoQuery
     */
    public function getMarcNo()
    {
        return $this->hasOne($this->marcNoClass, ['marc_no' => 'marc_no'])->inverseOf('marcCopies');
    }
}
