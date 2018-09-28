<?php


class NovelController extends AbstractController{
    const PAGESIZE = 50;

    /**
     * 小说列表
     */
    public function listAction(){

        try {
            $page = ((int)$this->get("page", 1)) > 0 ?: 1;
            $offset = ($page - 1) * self::PAGESIZE;
            $novelModel = new NovelModel();
            $result = $novelModel->novelList(array(), $offset, self::PAGESIZE, true);
            $this->_view->novel_list = $result['list'];

            $ph = new \YC\Page($result['cnt'], $page, self::PAGESIZE);
            $this->_view->pageHtml = $ph->getPageHtml();

            $authorModel = new AuthorModel();
            $authorList = $authorModel->getAllAuthor();
            $this->_view->author_list = $authorList;

           // $result['list'] = array();
            $this->_view->list = $result['list'];

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function addAction(){

        try {
            if ($this->getRequest()->isPost()) {

                $name = $this->getPost("name");
                if (empty($name)) {
                    throw new Exception("参数错误(name={$name})", 400);
                }
                $author = $this->getPost("author");
                $authorData = explode("_",$author);
                if(count($authorData)!= 2){
                    throw new Exception("参数错误（author={$author}）",400);
                }
                $description = $this->getPost("description");
                $novelClassId = $this->getPost("novel_class");
                $params = array(
                    "name" => $name,
                    "author_id" => $authorData[0],
                    "author_name" => $authorData[1],
                    "content" => $description,
                    "operator_id" => $this->_operatorId,
                    "create_time" => time(),
                    "update_time" => time(),
                    "novel_class_id" => $novelClassId,
                    "novel_class_name" => NovelModel::$_novel_class_type[$novelClassId],
                    "novel_id" => $this->getPost("novel_id"),
                    "record_status" => $this->getPost("record_status"),
                    "status" => $this->getPost("novel_status")
                );
                $imgInfo = $_FILES['img'];
                if($_FILES['img']['name']){
                    $file = new \YC\File\upFile();
                    $fileId = $file->store($imgInfo);
                    $params['pic'] = $fileId;
                }
                $novelModel = new NovelModel();
                $return = $novelModel->replaceNovel($params);
                if(!$return){
                    throw new Exception("操作失败");
                }
                $this->redirect("/novel/list");
            }
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function editStatusAction(){
        $type = $this->get("type");
        $id = $this->get("id");
        try{
            if(!in_array($type,array("del","limit"))){
                throw new Exception("参数错误（type={$type}）",400);
            }
            if($type == "del"){
                $status = NovelModel::NOVEL_STATUS_DEL;
            }else if($type == "limit"){
                $status = NovelModel::NOVEL_STATUS_LIMIT;
            }else{
                $status = NovelModel::NOVEL_STATUS_OK;
            }
            $novelModel = new NovelModel();
            $ret = $novelModel->update(array("status"=>$status),array("id"=>(int)$id));
            //var_dump($ret);exit;
            $this->redirect("/novel/list");
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    /**
     * 目录管理
     */
    public function subjectAction(){
        try{
            $id = $this->get("id");
            if($id <= 0){
                throw new Exception("参数错误（id={$id}）",400);
            }
            $page = (int)$this->get("page",1);
            $page = $page>0 ? $page :1;
            $offset = ($page-1) * self::PAGESIZE;

            $novelModel = new NovelTmpModel();
            $result = $novelModel->getList(array("novel_id" => (int)$id),$offset,self::PAGESIZE,true);
            $this->_view->list = $result['list'];
            $ph = new \YC\Page($result['cnt'],$page,self::PAGESIZE);
            //echo json_encode(array($result,$page,self::PAGESIZE));
            //exit;
            $this->_view->pageHtml = $ph->getPageHtml();
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function subjectStatusAction(){
        try{
            $novelId = $this->get("id");
            if($novelId > 0){
                $novelModel = new NovelTmpModel();
                $where = array(
                    "AND" => array(
                        "novel_id" => $novelId,
                        "status" => NovelTmpModel::NOVEL_TMP_STATUS_INIT
                    )
                );
                $tmpId = $this->get("tmp_id");
                if($tmpId > 0){
                    $where['AND']['id'] = $tmpId;
                }
                $result = $novelModel->update(array("status" => NovelTmpModel::NOVEL_TMP_STATUS_READY),$where);
            }
            $this->redirect("/novel/subject?id=".$novelId);
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function editTempAction(){
        try{
            $id = $this->get("id");
            if($id > 0){
                $params = array(
                    "title" => $this->get("title"),
                    "url" => $this->get("url"),
                    "status" => $this->get("status"),
                    "content_url" => json_encode(array("content" => $this->get("rule"),"num" => $this->get("num"))),
                    "order" => $this->get("order"),
                    "code" => $this->get("code"),
                );
                $novelModel = new NovelTmpModel();
                $where['id'] = $id;
                $result = $novelModel->update($params,$where);
            }
            $this->redirect("/novel/subject?id=".$this->get("novel_id"));
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    /**
     * 真实目录管理
     */
    public function realsubjectAction(){
        try{
            $id = $this->get("id");
            if($id <= 0){
                throw new Exception("参数错误（id={$id}）",400);
            }
            $page = (int)$this->get("page",1);
            $page = $page>0 ? $page :1;
            $offset = ($page-1) * self::PAGESIZE;

            $novelChapterModel = new NovelChapterModel();
            $result = $novelChapterModel->getList(array("novel_id" => (int)$id),$offset,self::PAGESIZE,array("chapter_order"=>"ASC"),true);
            $this->_view->list = $result['list'];
            $ph = new \YC\Page($result['cnt'],$page,self::PAGESIZE);
            $this->_view->pageHtml = $ph->getPageHtml();
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function editChapterAction(){
        try{
            $id = $this->get("id");
            $novelId = $this->get("novel_id");
//            if($id <= 0){
//                throw new Exception("参数错误（id={$id}）",400);
//            }
            $params = array(
                "title" => $this->get("title"),
                "chapter_order" => $this->get("order"),
                "status" => $this->get("status"),
                "novel_id" => $novelId,
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
            $novelChapterModel = new NovelChapterModel();
            $result = $novelChapterModel->replaceNovelChapter($params);
            $this->redirect("/novel/realsubject?id=".$this->get("novel_id"));
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

}
