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
    /*
     * 诗词管理
     */
    public function managelistAction(){
        try{
            $pageSize = self::PAGESIZE;
            $page = (int)$this->get("page",1);
            $page = $page>0 ? $page :1;
            $offset = ($page-1) * $pageSize;


            $articleModel = new ArticlesModel();
            $where = array(
                "AND" => array(
                    "status" => ArticlesModel::ARTICLE_CLASS_STATUS,
                    "is_part" => 0
                    ),
            );
            $articleList = $articleModel->getList($where,$offset,$pageSize,array("id"=>"ASC"),true);

            $this->_view->list = $articleList['list'];
            $ph = new \YC\Page($articleList['cnt'],$page,self::PAGESIZE);
            $this->_view->pageHtml = $ph->getPageHtml();


        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }

    }
    /**
     * 诗词管理编辑
     */

    public function manageeditAction(){
        try{
            $id = $this->get("id");
            if($id > 0) {
                $articleModel = new ArticlesModel();
                $info = $articleModel->find($id);

//                $info['notes'] = str_replace("\r\n","<br>",$info['notes']);
//                $info['translate'] = str_replace("\r\n","<br>",$info['translate']);
//                $info['shangxi'] = str_replace("\r\n","<br>",$info['shangxi']);
               // $info['content'] = str_replace("<br><br>","<br>",$info['content']);

                $tmp = explode("font",$info['content']);
                if(count($tmp)>1){
                    $info['content'] = str_replace("<br>","\r\n",$info['content']);
                    $info['content'] = str_replace(">","",$info['content']);
                    $info['content'] = str_replace("\r\n","<br>",$info['content']);
                    $info['content'] = str_replace("<br><br>","<br>",$info['content']);

                    $tmp = explode("font",$info['content']);
                    $info['content'] = trim(strip_tags($tmp[0],"<br>"),"<br>");
                    $info['notes'] = trim(strip_tags($tmp[2],"<br>"),"<br>");
                    $info['translate'] = trim(strip_tags($tmp[4],"<br>"),"<br>");
                    $info['shangxi'] = trim(strip_tags($tmp[6],"<br>"),"<br>");
                }
               // var_dump($info);exit;
                $this->_view->info = $info;

            }else{


            }
            $authorModel = new ArticleAuthorModel();
            $where = array(
                "status" => ArticleAuthorModel::AUTHOR_STATUS
            );
            $authorList = $authorModel->fetchAll($where);
            $this->_view->authorList = $authorList;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    /**
     * 编辑添加诗词管理
     */
    public function managedoAction(){
        try{
            $classType = $this->getPost("class_type");
            $authorId = $this->getPost("author_id");
            $authorName = $this->getPost("author_name");
            $name = $this->getPost("title");
            $content = $this->getPost("content");
            $description = $this->getPost("description");
            $translate = $this->getPost("translate");
            $notes = $this->getPost("notes");
            $shangxi = $this->getPost("shangxi");
            $status = $this->getPost("status");
            $data = array(
                "name" => $name,
                "content" => $content,
                "class_type" => $classType,
                "author" => $authorName,
                "author_id" => $authorId,
                "description" => $description,
                "translate" => $translate,
                "notes" => $notes,
                "shangxi" => $shangxi,
                "status" => $status

            );
            $id = $this->getPost("id");
            $articleModel = new ArticlesModel();
            if($id > 0){
                $articleModel->update($data,array("id"=>$id));
            }else{
                $id = $articleModel->insert($data);
            }
            $config = Yaf_Registry::get("dbconfig");
            if($classType == 1){
                $typeTxt = "tangshi";
            }else{
                $typeTxt = "ciqu";
            }
            $this->redirect($config['web_url']."/".$typeTxt."/detail_".$id.".html");
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

        //$reg = '/《<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>》<a\s.*?href="\/shiren\/yuanchao\/">元朝<\/a><span>·<\/span><a class="author" href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a/is';
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
            if(isset($urlRet[4])){
                $authorName = $urlRet[4][$key];
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
        //$reg = '/《<a\s.*?href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a>》/is';
        //$reg = '/<li>([^：]*)：<a href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a><\/li>/is'; //唐诗三百首
        $reg = '/<li><a href=[\'|\"]?([^\"\']*)[\'|\"]?[^>]*>([^<]+)<\/a><\/li>/is'; //文言文
        preg_match_all($reg,$result[0],$urlRet);
//var_dump($urlRet);exit;

        $author = $this->getPost("author");
        if($author){
            $authorInfo = explode("_",$author);
            $authorId = $authorInfo[0];
            $authorName = $authorInfo[1];
        }
        $count = 0;
        $novelTmpModel = new NovelTmpModel();
        if(!empty($authorId) && $authorId>0){
            $count = $novelTmpModel->getCount(array("author_id" => $authorId,"class_type"=>2));
        }

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
            //$title = $urlRet[3][$key];
            $authorRule = "/(.*)\((.*)\)/is";
            preg_match_all($authorRule,$urlRet[2][$key],$authorData);
            $title = $authorData[1][0];
            $authorName =$authorData[2][0];
           // var_dump($s);exit;

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
                //"author_name" => $urlRet[1][$key]
                "author_name" => $authorName
            );
            $count++;
            //echo json_encode($subjectData);exit;
           // if($count == 10)break;
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
                $this->redirect("/article/author?page=".$this->get("page",1));
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

    public function  postarticleauthorAction(){
        try{
            $type = $this->getPost("post_type");
            $ids = $this->getPost("author_ids");
            $authorModel = new ArticleAuthorModel();
            $typeValue = explode("_",$type);
            if($typeValue[0] == "status"){
                $value = $typeValue[1];
                if(count($ids) > 0){
                    $params = array(
                        "id" => $ids
                    );
                    $authorModel->update(array("status" => $value),$params);
                }
            }

            $this->redirect("/article/author?page=".$this->get("page",1));
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

}
