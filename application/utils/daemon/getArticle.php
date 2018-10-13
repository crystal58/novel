<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/7/30
 * Time: 下午5:37
 */
require __DIR__."/../autoload.php";


try{
    $novelTmp = new NovelTmpModel();
    $where = array(
        "AND" => array(
            //"id" => 1111,
            "status" => 1,
            "class_type" => 2
        ),
        "LIMIT" => array(0,50)
    );
    $list = $novelTmp->fetchAll($where);
    foreach ($list as $value){
        if(empty($value['title']))continue;
        $url = $value["url"];
        //$data = file_get_contents($url);
        $data = preg_replace('/\s[\s]+/', '', file_get_contents($url));
        if($value['code']!= "UTF-8"){
            $data = iconv($value['code'],'UTF-8//IGNORE',$data);
        }
        $contentRule = json_decode($value["content_url"],true);
        $rule = preg_replace('/\s[\s]+/', '', $contentRule['content']);
        preg_match("#$rule#isU", $data, $contentRet);
        var_dump($contentRet);

        $content = $contentRet[$contentRule['num']];
        $articleModel = new ArticlesModel();
        if(empty($content)){
            \YC\LoggerHelper::ERR('CRON_NOVELCONTENT_getDATA_empty', $value['novel_id']."_".$value['title']."_".$value['order']);
        }
        $content = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', '', $content);
        $content = preg_replace("/<a[^>]*>(.*?)<\/a*>/is", "", $content);
        $content = preg_replace("/<script[^>]*>(.*?)<\/script*>/is", "", $content);
        $content = str_replace(array("<td>","<tr>","</td>","</tr>"), "", $content);
        $content = str_replace('<hr color="#FFFFFF">', "", $content);

        $sqlData = array(
            "class_type" => $value['novel_id'],
            "name" => $value['title'],
            "content" => $content,
            "article_order" => $value['order'],
            "create_time" => time(),
            "update_time" => time(),
            "status" => 1,
            "author" => $contentRet[2]
        );
        $result = $articleModel->insert($sqlData);
        if($result){
            $novelTmp->update(array("status"=>NovelTmpModel::NOVEL_TMP_STATUS_FINISH),array("id"=>$value['id']));
        }



    }
}catch (Exception $e){
    \YC\LoggerHelper::ERR('CRON_NOVELCONTENT_getDATA', $e->__toString());
}


