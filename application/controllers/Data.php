
<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/7/24
 * Time: 上午11:37
 */

class DataController extends AbstractController{

    public function postDataAction(){
        try{
            $request = $this->getRequest();
            if($request->isPost()){
                $url = $this->getPost("url");
                $data = file_get_contents($url);
//                $postTitle = $this->getPost("title");
//                preg_match("#$postTitle#",$data,$result);
//                $titleNum = $this->getPost("title_num");
//                $title = $result[$titleNum];

                $postContent = $this->getPost("note");
                preg_match("#$postContent#is",$data, $contentRet);
                $contentNum = $this->getPost("note_num");
                //$content = $contentRet[$contentNum];
                $r = array(
                    //"r" => $title,
                    "content"=> $contentRet[1],
                    "d" => $data,
                    "u"=>$postContent
                );
                echo json_encode($r);
            }

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function getDataAction(){
        try{

        }catch(Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function getSubjectAction(){
        try{
//            $authorModel = new AuthorModel();
//            $authorList = $authorModel->getAllAuthor();
//            $this->_view->author_list = $authorList;

            $novelModel = new NovelModel();
            $novelList = $novelModel->novelList(array("record_status[!]"=>1));
            $this->_view->novel_list = $novelList;

            $authorModel = new AuthorModel();
            $authorList = $authorModel->getAllAuthor();
            $this->_view->author_list = $authorList;

        }catch(Exception $e){
            $result = array(
                "ret_code" => $e->getCode(),
                "ret_msg" => $e->getMessage()
            );       
        }
    }
    public function postSubjectAction(){
        set_time_limit(0);
        try{
            $request = $this->getRequest();
            if($request->isPost()){
                $authorUrl = $this->getPost("author_url");
                $code = $this->getPost("code");
                $type = $this->getPost("gettype");
                if($type == 1){
                   // echo $authorUrl."<br>";
                    $authorData = file_get_contents($authorUrl);

                    if($code != "UTF-8"){
                        $authorData = iconv($code,'UTF-8//IGNORE',$authorData);
                    }
                     //echo $authorData."<br>";
                    $authorRule = $this->getPost("author_rule");
                    preg_match("/$authorRule/isU", $authorData,$authorNovel);
                    //var_dump($authorNovel[1]);exit;
                    $reg = '/<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>/is';
                    preg_match_all($reg,$authorNovel[1],$authorUrlRet);
                    $authorInfo = explode("_",$this->getPost("author"));
                    $novelClass = explode("_",$this->getPost("novel_class"));

                    foreach ($authorUrlRet[1] as $key=>$value){
                        $novel = array(
                            "name" => $authorUrlRet[2][$key],
                            "author_id" => $authorInfo[0],
                            "author_name"=> $authorInfo[1],
                            "content" => "",
                            "novel_class_id" => $novelClass[0],
                            "novel_class_name" => $novelClass[1],
                            "operator_id" => $this->_operatorId,
                            "create_time" => time(),
                            "update_time" => time(),

                        );
                        $novelModel = new NovelModel();
                        $novelId = $novelModel->insert($novel);
                        $url = $value;
                        $this->caiJi($url,$novelId,$type);
                        echo "采集目录成功，<a href='/novel/subject?id={$novelId}'>查看</a><br>";
                       // exit;
                    }
                    exit;
                }else{
                    $url = $this->getPost("url");
                    $novelId = $this->getPost("novel_id");
                    $this->caiJi($url,$novelId,$type);
                    echo "采集目录成功，<a href='/novel/subject?id={$novelId}'>查看</a>";
                }




             }
        }catch(Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    private function caiJi($url,$novelId,$type){


        $content = $this->getPost("content");
        $pathData = parse_url($url);
        $data = file_get_contents($url);
        $data = preg_replace('/\s[\s]+/', '', file_get_contents($url));

        // preg_match("/charset=(.*)\">/is",$data,$codeData);
        // echo 111;
        // echo json_encode($codeData);exit;

        $postTitle = $this->getPost("title");
        $code = $this->getPost("code");
        if($code != "UTF-8"){
            $data = iconv($code,'UTF-8//IGNORE',$data);
        }


//echo $data;
        preg_match("/$postTitle/isU",$data,$result);

        if(empty($result)){
            throw new Exception("正则出错了");
        }
        preg_match('/<p>(.*)<\/p>/isU',$data,$retContent);
        if ($type == 1){
            if($retContent){
                $novelContent = $retContent[1];
                $novelModel = new NovelModel();
                $novelModel->update(array("content"=>$novelContent),array("id" =>$novelId));
            }else{
                throw new Exception("简介正则出错了");
            }
        }

        $reg = '/<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>/is';
        preg_match_all($reg,$result[0],$urlRet);


        $novelTmpModel = new NovelTmpModel();
        $count = $novelTmpModel->getCount(array("novel_id" => $novelId));
        $count = $count + 1;
        $subjectData = array();
        foreach ($urlRet[1] as $key=>$value){

            if(stripos($value,"http") === 0 || stripos($value,"https") === 0){
                $subjectUrl = $value;
            }else{
                $host = isset($pathData['host']) ? $pathData['host'] : "";
                $scheme = isset($pathData['scheme']) ? $pathData['scheme'] : "";
                $path = "";
                if(stripos($value,"/") !== 0){
                    $pathArr = explode("/",$pathData['path']);
                    unset($pathArr[count($pathArr)-1]);
                    $path = "/".implode("/",$pathArr)."/";
                }
                $subjectUrl = $scheme."://".$host.$path.$value;
            }
            $subjectData[] = array(
                "novel_id" => $novelId,
                "title" => $urlRet[2][$key],
                "url" => $subjectUrl,
                "create_time" => time(),
                "content_url" => json_encode(array("content"=>$content,"num"=>$this->getPost("content_num"))),
                "order" => $count,
                "code" => $code
            );
            $count++;
        }
        if(!empty($subjectData)){
            $ret = $novelTmpModel->batchInsert($subjectData);
        }
    }
}
