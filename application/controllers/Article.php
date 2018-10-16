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

            if($_FILES['img']['name']){
                $file = new \YC\File\upFile();
                $imgInfo = $_FILES['img'];$fileId = $file->store($imgInfo);
                $data['pic'] = $fileId;
            }
            $articleTypeModel = new ArticlesTypeModel();
            $articleTypeModel->replaceClass($data);

            $this->redirect("/article/articletype");

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    /**
     * 真实目录
     */
    public function listAction(){
        try{
            $id = $this->get("id");
            $articleModel = new ArticlesModel();
            $params = array(
                "status" => ArticlesModel::ARTICLE_CLASS_STATUS,
                "class_type" => $id
            );
            $page = (int)$this->get("page",1);
            $page = $page>0 ? $page :1;
            $offset = ($page-1) * self::PAGESIZE;

            $articleList = $articleModel->getList($params,$offset,self::PAGESIZE,array("article_order"=>"ASC"),true);

            $this->_view->list = $articleList['list'];
            $ph = new \YC\Page($articleList['cnt'],$page,self::PAGESIZE);
            $this->_view->pageHtml = $ph->getPageHtml();

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function editarticleAction(){
        try{
            $id = $this->get("id");
            $classType = $this->get("class_type");
//            if($id <= 0){
//                throw new Exception("参数错误（id={$id}）",400);
//            }
            $params = array(
                "name" => $this->get("title"),
                "article_order" => $this->get("order"),
                "status" => $this->get("status"),
                "class_type" => $classType,
                "is_part" => $this->get("is_part")
            );
            $content = $this->get("content");
            if($content){
                $params['content'] = $content;
            }

            if($id >0){
                $params['id'] = $id;
            }
            //echo json_encode($params);exit;
            $articleModel = new ArticlesModel();
            $result = $articleModel->replaceArticle($params);
            $this->redirect("/article/list?id=".$classType."&class_type=2");
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
                        echo "采集目录成功，<a href='/novel/subject?id={$novelId}&class_type=2'>查看</a><br>";
                        // exit;
                    }
                    exit;
                }else{
                    $url = $this->getPost("url");
                    $classId = $this->getPost("class_id");
                    $this->caiJi($url,$classId);
                    echo "采集目录成功，<a href='/novel/subject?id={$classId}&class_type=2'>查看</a>";
                }


            }
        }catch(Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    private function caiJi($url,$classId){


        $content = $this->getPost("content");
        $pathData = parse_url($url);
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
           // echo json_encode($subjectData);exit;
            $count++;
           // break;
        }
        if(!empty($subjectData)){
            $ret = $novelTmpModel->batchInsert($subjectData);
        }
    }


    public function specialContentAction(){
//        $find = array("Hello","world");
//        $replace = array("B","A");
//        $arr = "Hello,world,!";
//        print_r(str_replace($find,"",$arr));
//        exit;
        $novelTmp = new NovelTmpModel();
        $where = array(
            "AND" => array(
               //"id" => 1416,
                 "novel_id" => 16,
                "status" => 0,
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
            if($value['code'] == "gb2312"){
                $code = "GBK";
            }
            if($value['code']!= "UTF-8"){
                $data = iconv($code,'UTF-8//IGNORE',$data);
                //$data = mb_convert_encoding($data,'UTF-8',$code);
            }
            $contentRule = json_decode($value["content_url"],true);
            $rule = preg_replace('/\s[\s]+/', '', $contentRule['content']);
            preg_match("#$rule#isU", $data, $contentRet);
            $content = $contentRet[$contentRule['num']];
            $author_name = isset($contentRet[2])?$contentRet[2]:"";
            //var_dump($contentRet);exit;

            //全唐诗

            $rule = "」(.*)<br>";
            preg_match("#$rule#isU", $content, $authorData);
            $author_name = trim($authorData[1]);


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
                "author" => $author_name
            );
            $result = $articleModel->insert($sqlData);
            if($result){
                $novelTmp->update(array("status"=>NovelTmpModel::NOVEL_TMP_STATUS_FINISH),array("id"=>$value['id']));
            }

        }

    }

}
