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

namespace rhoone\library\providers\huiwen\targets;

use rhoone\library\targets\MarcTarget as baseMarcTarget;

/**
 * Class MarcTarget
 * @package rhoone\library\targets
 * @author vistart <i@vistart.me>
 */
abstract class MarcTarget extends baseMarcTarget
{
    public $relativeUrl = "/opac/item.php";
}
