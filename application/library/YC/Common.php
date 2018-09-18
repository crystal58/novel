<?php
namespace YC;
use YC\File\upFile;

/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/9/17
 * Time: 下午6:23
 */
class Common
{
    public static function getUrl($fileId)
    {
        $file = new upFile();
        return $file->get($fileId);
    }
}