<?php
/**
 * Created by PhpStorm.
 * User: Jordy
 * Date: 2019/12/13
 * Time: 8:22 PM
 */

/**
 * @param $key
 * @return array|null
 * @throws \Exception
 */
function env($key)
{
    static $config;
    if (!$config) {
        $config = All\Config\Config::getInstance()->setPath(__DIR__ . '/config');
    }

    return $config->get($key);
}