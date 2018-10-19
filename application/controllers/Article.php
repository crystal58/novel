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
//            $params = array(
//                "status" => ArticlesTypeModel::ARTICLE_CLASS_STATUS,
//            );
            $typeData = $articleTypeModel->getAllClass();
            $typeList = $firstTypeList = array();
            foreach ($typeData as $value){
                if($value['parent_id'] == 0){
                    $firstTypeList[] = $value;
                    continue;
                }
                $typeList[$value['parent_id']][] = $value;
            }
            $articleAuthorModel = new ArticleAuthorModel();
            $author = $articleAuthorModel->getAllAuthor();
            $this->_view->author = $author;
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

                    $url = $webUrl = $this->getPost("url");
                    $classId = $this->getPost("class_id") ?:$this->getPost("first_class");
                    $page = $this->getPost("page");
                    $offset = $this->getPost("offset")?:1;
                    for ($i=$offset;$i<= $page; $i++) {
                        if($i > 1) {
                            $url = $webUrl . "page".$i."/";
                        }
                        if($getDataType == 2){
                            $this->caiJi($url,$classId);
                        }else{
                            $this->zhuticaiji($url,$classId);
                        }


                    }
                    echo "采集目录成功，<a href='/novel/subject?id={$classId}&class_type=2'>查看</a>";
            }
        }catch(Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

/*
 * 古诗词名句 作者列表采集
 */

    private function caiJi($url,$classId){

        $content = $this->getPost("content");
        $pathData = parse_url($url);

        $data = \YC\Common::readfile($url);
        $data = preg_replace('/\s[\s]+/', '', $data);


        $postTitle = $this->getPost("title");
        $code = $this->getPost("code");
        if($code != "UTF-8"){
            $data = iconv($code,'UTF-8//IGNORE',$data);
        }

        preg_match("/$postTitle/isU",$data,$result);
        if(empty($result)){
            throw new Exception("正则出错了");
        }
        $reg = '/《<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>》/is';
        preg_match_all($reg,$result[0],$urlRet);

        $author = $this->getPost("author");
        if($author){
            $authorInfo = explode("_",$author);
            $authorId = $authorInfo[0];
            $authorName = $authorInfo[1];
        }

        $novelTmpModel = new NovelTmpModel();
        $params = array(
            "novel_id" => $classId,
            "class_type" => 2
        );
        if(isset($authorId) && $authorId >0){
            $params['author_id'] = $authorId;
        }
        $count = $novelTmpModel->getCount($params);
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


            $title = $urlRet[2][$key];
            $subjectData[] = array(
                "novel_id" => $classId,
                "title" => $title,
                "url" => $subjectUrl,
                "create_time" => time(),
                "content_url" => json_encode(array("content"=>$content,"num"=>$this->getPost("content_num"))),
                "order" => $count,
                "code" => $code,
                "class_type" => 2,
                "author_id" => isset($authorId) ? $authorId : 0,
                "author_name" => isset($authorName) ? $authorName : ""
            );
           // echo json_encode($subjectData);exit;
            $count++;
            //if($count == 10)break;
           // break;
        }
        //var_dump($subjectData);exit;
        if(!empty($subjectData)){
            $ret = $novelTmpModel->batchInsert($subjectData);
        }
    }

    /**
     * @param $url
     * @param $classId
     * @throws Exception
     * 古诗词名句网 专题列表采集 唐诗三百首
     */
    private function zhuticaiji($url,$classId){

        $content = $this->getPost("content");
        $pathData = parse_url($url);

        $data = \YC\Common::readfile($url);
        $data = preg_replace('/\s[\s]+/', '', $data);


        $postTitle = $this->getPost("title");
        $code = $this->getPost("code");
        if($code != "UTF-8"){
            $data = iconv($code,'UTF-8//IGNORE',$data);
        }

        preg_match("/$postTitle/isU",$data,$result);
        if(empty($result)){
            throw new Exception("正则出错了");
        }
        //var_dump($result[0]);
        //$reg = '/《<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>》/is';
        $reg = '/<li>([^：]*)：<a href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a><\/li>/is';

        preg_match_all($reg,$result[0],$urlRet);
        //var_dump($urlRet);exit;

        $author = $this->getPost("author");
        if($author){
            $authorInfo = explode("_",$author);
            $authorId = $authorInfo[0];
            $authorName = $authorInfo[1];
        }
        $novelTmpModel = new NovelTmpModel();
        $count = $novelTmpModel->getCount(array("author_id" => $authorId,"class_type"=>2));
        $count = $count + 1;
        $subjectData = array();
        foreach ($urlRet[2] as $key=>$value){

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

            $title = $urlRet[3][$key];

            $subjectData[] = array(
                "novel_id" => $classId,
                "title" => $title,
                "url" => $subjectUrl,
                "create_time" => time(),
                "content_url" => json_encode(array("content"=>$content,"num"=>$this->getPost("content_num"))),
                "order" => $count,
                "code" => $code,
                "class_type" => 2,
                "author_id" => isset($authorId) ? $authorId : 0,
                "author_name" => $urlRet[1][$key]
            );
            $count++;
            //if($count == 10)break;
            // break;
        }

       // var_dump($subjectData);exit;
        if(!empty($subjectData)){
            $ret = $novelTmpModel->batchInsert($subjectData);
        }
    }

    public function authorAction(){
        try {
            $page = ((int)$this->get("page", 1)) > 0 ? (int)$this->get("page", 1) : 1;
            $offset = ($page - 1) * self::PAGESIZE;
            $articleAuthorModel = new ArticleAuthorModel();
            $author = $articleAuthorModel->getList(array(), $offset, self::PAGESIZE, true);
            $this->_view->list = $author['list'];

            $articleTypeModel = new ArticlesTypeModel();
            $articleType = $articleTypeModel->getList(array("parent_id"=>0,"status"=>ArticlesTypeModel::ARTICLE_CLASS_STATUS));
            $articleTypeList = array();
            foreach ($articleType['list'] as $value){
                $articleTypeList[$value['id']] = $value;
            }

            $this->_view->article_type =$articleTypeList;


            $ph = new \YC\Page($author['cnt'], $page, self::PAGESIZE);
            $this->_view->pageHtml = $ph->getPageHtml();
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function postauthorAction(){
        try{
            if ($this->getRequest()->isPost()) {
                $authorName = $this->getPost("name");
                if (empty($authorName)) {
                    throw new Exception("参数错误", 400);
                }
                $description = $this->getPost("description");
                $params = array(
                    "author_name" => $authorName,
                    "description" => $description,
                    "operator_id" => $this->_operatorId,
                    "create_time" => time(),
                    "update_time" => time(),
                    "author_id" => $this->getPost("author_id"),
                    "class_type_id" => $this->getPost("novel_class_id"),
                    "status" => $this->getPost("author_status")

                );
                //  echo json_encode($params);exit;
                $imgInfo = $_FILES['img'];
                if($_FILES['img']['name']){
                    $file = new \YC\File\upFile();
                    $fileId = $file->store($imgInfo);
                    $params['pic'] = $fileId;
                }

                //echo json_encode($params);exit;
                $authorModel = new ArticleAuthorModel();
                $return = $authorModel->replaceAuthor($params);
                if(!$return){
                    throw new Exception("操作失败");
                }
                $this->redirect("/article/author");
            }
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
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
               "id" => 1454,
               //  "novel_id" => 16,
               // "status" => 0,
                "class_type" => 2
            ),
            "LIMIT" => array(0,50)
        );
        $list = $novelTmp->fetchAll($where);
        //var_dump($list);
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
//            $contentRule = json_decode($value["content_url"],true);
//            $rule = preg_replace('/\s[\s]+/', '', $contentRule['content']);
         //   echo $data;


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


//            $a = array(
//                "title" => $title,
//                "content" => $content,
//                "description" => $description,
//                "translate" =>$translate,
//                "zhushi" => $zhushi,
//                "shangxi" => $shangxi
//            );
//            echo json_encode($a);exit;
            //$content = $contentRet[$contentRule['num']];
            //$author_name = isset($contentRet[2])?$contentRet[2]:"";
            //var_dump($contentRet);exit;

            //全唐诗

//            $rule = "」(.*)<br>";
//            preg_match("#$rule#isU", $content, $authorData);
//            $author_name = trim($authorData[1]);


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

    }

}
