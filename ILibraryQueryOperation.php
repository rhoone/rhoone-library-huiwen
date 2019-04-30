<?php

/**
 *
 *    _   __ __ _____ _____ ___  ____  _____
 *   | | / // // ___//_  _//   ||  __||_   _|
 *   | |/ // /(__  )  / / / /| || |     | |
 *   |___//_//____/  /_/ /_/ |_||_|     |_|
 *   @link https://vistart.name/
 *   @copyright Copyright (c) 2016 - 2019 vistart
 *   @license https://vistart.name/license/
 *
 */
namespace rhoone\library\providers\huiwen;

/**
 * Interface ILibraryQueryOperation
 * @package rhoone\library\providers\huiwen
 */
interface ILibraryQueryOperation
{
    public function queryCallNo(string $keyword, array $config = null);

    public function queryISBN(string $keyword, array $config = null);

    public function queryBarcode(string $keyword, array $config = null);

    public function queryMarcNo(string $keyword, array $config = null);

    public function queryTitle(string $keyword, array $config = null);

    public function queryAuthor(string $keyword, array $config = null);

    public function queryPress(string $keyword, array $config = null);

    public function querySubject(string $keyword, array $config = null);

    public function queryNote(string $keyword, array $config = null);
}