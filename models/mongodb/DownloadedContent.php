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

use rhoone\spider\destinations\IDestinationModel;
use rhosocial\base\models\models\BaseMongoEntityModel;
use Yii;
use yii\base\NotSupportedException;

/**
 * This is the model class for collection "downloaded_content".
 * But you can't use this class directly, you need to declare a class that inherits from this class and make a
 * collection name.
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property string $guid
 * @property string $marc_no
 * @property string $html
 * @property string $created_at
 * @property string $updated_at
 * @property int $version
 * @property string $downloadedContent
 */
class DownloadedContent extends BaseMongoEntityModel implements IDestinationModel
{
    public $enableIP = 0;

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
            'marc_no', 'html', 'version'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        $noInit = static::buildNoInitModel();
        return [$noInit->guidAttribute, $noInit->idAttribute, 'marc_no'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $parent = parent::rules();
        return array_merge($parent,[
            [['marc_no', 'html'], 'required'],
            ['marc_no', 'string'],
            ['html', 'string'],
            ['version', 'integer', 'min' => 0],
            ['version', 'default', 'value' => 0],
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
            'html' => Yii::t('app', 'Html'),
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
     * @param string $content the downloaded content to be saved.
     */
    public function setDownloadedContent(string $content)
    {
        $this->html = $content;
    }
}