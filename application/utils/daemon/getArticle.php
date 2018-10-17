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
        $data = \YC\Common::readfile($url);
        //var_dump($data);exit;
        $data = preg_replace('/\s[\s]+/', '', $data);
        if($value['code'] == "gb2312"){
            $code = "GBK";
        }
        if($value['code']!= "UTF-8"){
            $data = iconv($code,'UTF-8//IGNORE',$data);
            //$data = mb_convert_encoding($data,'UTF-8',$code);
        }


        $key = array();
        $rule = '(.*)<h1>(.*)</h1>(.*)<\/span><\/div><div class="shici-content">(.*)';
        $i = 5;
        if(strpos($data,'<h2 class="inline">古诗简介</h2>')){
            $rule .= '古诗简介(.*)';
            $key['description'] = $i;
            $i++;
        }
        if(strpos($data,'<h2 class="inline">翻译/译文</h2>')){
            $rule .= '翻译/译文(.*)';
            $key['translate'] = $i;
            $i++;
        }
        if(strpos($data,'<h2 class="inline">注释</h2>')){
            $rule .= '注释(.*)';
            $key['zhushi'] = $i;
            $i++;
        }
        if(strpos($data,'<h2 class="inline">赏析/鉴赏</h2>')){
            $rule .= '赏析/鉴赏(.*)';
            $key['shangxi'] = $i;
            $i++;
        }
        $rule .= "<div class=\"main-content\"><div class=\"title text-dark-red\"><h2 class=\"inline\">";

        preg_match("#$rule#isU", $data, $contentRet);
        //var_dump($contentRet);exit;
        $title = strip_tags($contentRet[2],"<p>");
        $content = strip_tags($contentRet[4],"<p>");

        $description = isset($key['description']) ? strip_tags($contentRet[$key['description']],"<p>") : "";
        $translate = isset($key['translate']) ? strip_tags($contentRet[$key['translate']],"<p>") : "";
        $zhushi = isset($key['zhushi']) ? strip_tags($contentRet[$key['zhushi']],"<p>"):"";
        $shangxi = isset($key['shangxi']) ? strip_tags($contentRet[$key['shangxi']],"<p>"): "";


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
            "author" => $value['author_name'],
            "author_id" => $value['author_id'],
            "description" => $description,
            "translate" => $translate,
            "notes" => $zhushi,
            "shangxi" => $shangxi
        );
        //var_dump($sqlData);exit;
        $result = $articleModel->insert($sqlData);
        if($result){
            $novelTmp->update(array("status"=>NovelTmpModel::NOVEL_TMP_STATUS_FINISH),array("id"=>$value['id']));
        }

    }
}catch (Exception $e){
    \YC\LoggerHelper::ERR('CRON_NOVELCONTENT_getDATA', $e->__toString());
}


