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
namespace rhoone\library\providers\huiwen\widgets\assets;

use yii\web\AssetBundle;

/**
 * Class SearchResultsAssets
 * @package rhoone\library\providers\huiwen\widgets\assets
 */
class SearchResultsAsset extends AssetBundle
{
    public $sourcePath = "@rhoone/library/providers/huiwen/widgets/assets/search-results-assets";

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
