<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/10/9
 * Time: 下午2:24
 */

$novelContent = "aaa<IMG height=\"42\" src=\"http://ty53.com/wx/liangyusheng/flqf/tp/hb.gif\" width=\"251\" border=\"0\">bbbb<a id='eeeaaa' href='https://www.eeeaaa.cn' title='文学星空'>文学星空</a>ccc";
$novelContent = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '', $novelContent);
$novelContent = preg_replace('/<a(.*)<\/a\s*>/i', "", $novelContent);
echo $novelContent;exit;

echo md5("stevecrystal");
echo "\r\n";
exit;






