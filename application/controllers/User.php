<?php
use \YC\File\upFile;

class UserController extends AbstractController{
    const PAGESIZE = 20;
    const USER_STATUS = 1;     //正常
    const USER_STATUS_DEL = 0; //删除
    public function listAction(){
        $page =((int)$this->get("page",1)) >0 ?:1;
        $offset = ($page-1) * self::PAGESIZE;
        $user = new UserModel();
        $result = $user->getList(array('status' =>1),$offset,self::PAGESIZE,true);
        $ph = new \YC\Page($result['cnt'],$page,self::PAGESIZE);
        $this->_view->pageHtml = $ph->getPageHtml();
        $this->_view->list =$result['list'];
    }
    public function addAction(){
        if($_POST){
            $fileInfo = "";
            if($_FILES){
                $file = new upFile();
                $fileInfo = $file->store($_FILES['uploadFile']);
            }
            $loginName = $this->getPost("login_name");
            $params = array(
                "login_name" => $loginName,
                "user_name" => $this->getPost("user_name"),
                "email" => $this->getPost("email"),
                "image" => $fileInfo,
                "createtime"=>time(),
                "updatetime"=>time(),
                "pwd" => md5($loginName)
            );
            $user = new UserModel();
            $user->insert($params);
        } 
    }
    public function delAction(){
        $userId = $this->getPost("user_id");
        try{
            $user = new UserModel();
            $ret = $user->update(array("status"=>self::USER_STATUS_DEL),array("id"=>$userId));
            $result = array(
                "ret_code" => 200,
                "result" => $ret
            );
        }catch(Exception $e){
            $result = array(
                "ret_code" => $e->getCode(),
                "ret_msg" => $e->getMessage()
            );
        }
        echo json_encode($result);
    }
    public function editAction(){
        try{
            $request = $this->getRequest(); 
            $userId = $this->get("user_id");
            
            $file = new upFile();
            $user = new UserModel();
            if($request->isPost()){
                $userId = $this->getPost("user_id");
                if($_FILES){
                    $fileInfo = $file->store($_FILES['uploadFile']);
                }
               //echo $fileInfo;exit; 
                $data = array(
                    "user_name" => $this->getPost("user_name"),
                    "email" => $this->getPost("email"),
                    "image" => $fileInfo
                );
                $user->update($data,array("id"=>$userId));
            }
            $result = $user->find($userId);
            $result['image'] = $file->get($result['image']);   
            $this->_view->data = $result;   
        }catch(Exception $e){
            $result = array(
                "ret_code" => $e->getCode(),
                "ret_msg" => $e->getMessage()
            );
            error_log(json_encode($result));
        }
    }
}
