<?php


class AuthorController extends AbstractController{
    const PAGESIZE = 20;


    public function listAction(){

        try {
            $page = ((int)$this->get("page", 1)) > 0 ?: 1;
            $offset = ($page - 1) * self::PAGESIZE;
            $authorModel = new AuthorModel();
            $result = $authorModel->getList(array('status' => 1), $offset, self::PAGESIZE, true);
            $ph = new \YC\Page($result['cnt'], $page, self::PAGESIZE);
            $this->_view->pageHtml = $ph->getPageHtml();
           // $result['list'] = array();
            $this->_view->list = $result['list'];
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function addAction(){

        try {
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
                    "novel_class_id" => $this->getPost("novel_class_id")

                );
              //  echo json_encode($params);exit;
                $imgInfo = $_FILES['img'];
                if($_FILES['img']['name']){
                    $file = new \YC\File\upFile();
                    $fileId = $file->store($imgInfo);
                    $params['pic'] = $fileId;
                }
                $authorModel = new AuthorModel();
                $return = $authorModel->replaceAuthor($params);
                if(!$return){
                    throw new Exception("操作失败");
                }
                $this->redirect("/author/list");
            }
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }
    public function delAction(){
        $authorId = $this->get("author_id");
        try{
            $authorModel = new AuthorModel();
            $ret = $authorModel->update(array("status"=>AuthorModel::AUTHOR_STATUS_DEL),array("id"=>$authorId));
            $this->redirect("/author/list");
        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

}
