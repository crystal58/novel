<?php
class ArticlesModel extends AbstractModel {

    const ARTICLE_CLASS_STATUS = 1;     //正常
    const ARTICLE_CLASS_STATUS_DEL = -1; //删除

    protected $_table = "articles";
    protected $_database = "account";
    protected $_primary = "id"; 



    public function replaceArticle($data){
        if(empty($data['name'])){
            return false;
        }
        $data['update_time'] = time();
        $articleId = $data['id'];
        unset($data['id']);
        if($articleId > 0){
            return $this->update($data,array("id"=>$articleId));
        }
        $data['create_time'] = time();
        return $this->insert($data);
    }

    /**
     * isCount   是否返回count
     */
    public function getList($params,$offset = 0,$pageSize=null,$order=null,$isCount=false) {
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'] = array(
                $key => $value
            );
        }
        if($isCount){
            $result['cnt'] = $this->count($where);
        }
        if($pageSize){
            $where['LIMIT'] = array($offset,$pageSize);
        }
        if($order){
            $where['ORDER'] = $order;
        }
        $result['list'] = $this->fetchAll($where);
        return $result;
    }


}
