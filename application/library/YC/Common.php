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

    /**
     * 采集读取网页
     * @param $url
     * @return mixed
     *
     */
    public static function readfile($url){
        $pathData = parse_url($url);
        $header = array (
            "GET {$pathData['path']} HTTP/1.1",
            "Host: {$pathData['host']}",
            "Connection: keep-alive",
            "Cache-Control: max-age=0",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.152 Safari/537.22",
            "Accept-Encoding: gzip,deflate,sdch",
            "Accept-Language: en-US,zh-CN;q=0.8,zh;q=0.6",
            "Accept-Charset: UTF-8,*;q=0.5",
            "Cookie: JSESSIONID=BD1418BC3F4CA9084F0C022A98687A09; track_id=117.25.173.111363310326444; seekstr=*78H*..; seekshot=78H..1..75..8..112; __utma=191189370.2036196682.1363308553.1363308553.1363308553.1; __utmb=191189370.3.10.1363308553; __utmc=191189370; __utmz=191189370.1363308553.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); arp_scroll_position=900"
        );
        // 初始化一个 cURL 对象
        $curl = curl_init();
        // 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置header  // 设置header显示方式
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 运行cURL，请求网页
        $data = curl_exec($curl);
        // 关闭URL请求
        curl_close($curl);
        // 显示获得的数据
        return $data;
    }
}