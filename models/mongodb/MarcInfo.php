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
 * Class MarcInfo
 *
 * @property string $key
 * @property string $value
 * @property int $version
 * @property-read marcNo $marcNo
 * @package rhoone\library\providers\huiwen\models\mongodb
 */
class MarcInfo extends BaseMongoEntityModel
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
            'marc_no', 'key', 'value', 'version'
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
            [['marc_no', 'key',], 'required'],
            [['marc_no', 'key', 'value'], 'string'],
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
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
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
        $this->queryClass = MarcInfoQuery::class;
        return parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * @return MarcNoQuery
     */
    public function getMarcNo()
    {
        return $this->hasOne($this->marcNoClass, ['marc_no' => 'marc_no'])->inverseOf('marcInfos');
    }
}
