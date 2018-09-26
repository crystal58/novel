
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
                $url = $this->getPost("url");

                $pathData = parse_url($url);
                $data = file_get_contents($url);

               // preg_match("/charset=(.*)\">/is",$data,$codeData);
               // echo 111;
               // echo json_encode($codeData);exit;

                $postTitle = $this->getPost("title");
                $code = $this->get("code");
                if($code != "UTF-8"){
                    $data = iconv($code,'UTF-8//IGNORE',$data);
                }
                preg_match("/$postTitle/isU",$data,$result);
                if(empty($result)){
                    throw new Exception("正则出错了");
                }
                $reg = '/<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>/is';
                preg_match_all($reg,$result[0],$urlRet);

                $novelId = $this->getPost("novel_id");
                $content = $this->getPost("content");
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

                echo "采集目录成功，<a href='/novel/subject?id={$novelId}'>查看</a>";
             }
        }catch(Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
}
