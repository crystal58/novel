<?php


class ArticleController extends AbstractController{
    const PAGESIZE = 50;

    public function articletypeAction(){
        $articleTypeModel = new ArticlesTypeModel();
        $classList = $articleTypeModel->getAllClass();
        $this->_view->class_list = $classList;
        $firstClass = array();
        foreach($classList as $value){
            if($value['parent_id'] == 0){
                $firstClass[$value['id']] = $value['name'];
            }
        }
        $this->_view->first_class = $firstClass;

    }

    public function replaceAction(){
        try {
            $name = $this->getPost("class_name");
            $parentId = $this->getPost("parent_class");
            $content = $this->getPost("description");
            $id = $this->getPost("class_id");
            $status = $this->getPost("class_status");

            if(empty($name)){
                throw new Exception("参数错误");
            }

            $data = array(
                "id" => $id,
                "name" => $name,
                "parent_id" => $parentId,
                "content" => $content,
                "status" => $status
            );
            $articleTypeModel = new ArticlesTypeModel();
            $articleTypeModel->replaceClass($data);

            $this->redirect("/article/articletype");

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    /**
     * 采集目录
     */
    public function getsubjectAction(){

        try {
            $articleTypeModel = new ArticlesTypeModel();
            $params = array(
                "status" => ArticlesTypeModel::ARTICLE_CLASS_STATUS,
            );
            $typeData = $articleTypeModel->getList($params);
            $typeList = $firstTypeList = array();
            foreach ($typeData['list'] as $value){
                if($value['parent_id'] == 0){
                    $firstTypeList[] = $value;
                    continue;
                }
                $typeList[$value['parent_id']][] = $value;
            }
            $this->_view->type_list = $typeList;
            $this->_view->first_type_list = $firstTypeList;
            //echo json_encode($firstTypeList);exit;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function postSubjectAction(){
        set_time_limit(0);
        try{
            $request = $this->getRequest();
            if($request->isPost()){
                $getDataType = $this->getPost("getdatatype");
                $code = $this->get("code");
                if($getDataType == 1){
                    // echo $authorUrl."<br>";
                    $authorUrl = $this->getPost("author_url");
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
                        $this->caiJi($url,$novelId);
                        echo "采集目录成功，<a href='/novel/subject?id={$novelId}'>查看</a><br>";
                        // exit;
                    }
                    exit;
                }else{
                    $url = $this->getPost("url");
                    $classId = $this->getPost("class_id");
                    $this->caiJi($url,$classId);
                    echo "采集目录成功，<a href='/article/subject?id={$classId}'>查看</a>";
                }


            }
        }catch(Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    private function caiJi($url,$classId){


        $content = $this->getPost("content");
        $pathData = parse_url($url);
       // $data = file_get_contents($url);
        $data = preg_replace('/\s[\s]+/', '', file_get_contents($url));
        // preg_match("/charset=(.*)\">/is",$data,$codeData);
        // echo 111;
        // echo json_encode($codeData);exit;

        $postTitle = $this->getPost("title");
        $code = $this->getPost("code");
        if($code != "UTF-8"){
            $data = iconv($code,'UTF-8//IGNORE',$data);
        }
        preg_match("/$postTitle/isU",$data,$result);

        if(empty($result)){
            throw new Exception("正则出错了");
        }
        $reg = '/<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>/is';
        preg_match_all($reg,$result[0],$urlRet);
        var_dump($urlRet);exit;

        $novelTmpModel = new NovelTmpModel();
        $count = $novelTmpModel->getCount(array("novel_id" => $classId));
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
                "novel_id" => $classId,
                "title" => $urlRet[2][$key],
                "url" => $subjectUrl,
                "create_time" => time(),
                "content_url" => json_encode(array("content"=>$content,"num"=>$this->getPost("content_num"))),
                "order" => $count,
                "code" => $code,
                "class_type" => 2
            );
            echo json_encode($subjectData);exit;
            $count++;
        }
        if(!empty($subjectData)){
            $ret = $novelTmpModel->batchInsert($subjectData);
        }
    }

}
