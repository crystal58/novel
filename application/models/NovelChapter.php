<?php
class NovelChapterModel extends AbstractModel {


    protected $_table = "novel_chapters";
    protected $_database = "account";
    protected $_primary = "id";


    public function replaceNovelChapter($data){
        if(empty($data['title'])){
            return false;
        }
        $id = isset($data['id']) ? $data['id'] : 0;

        if($id > 0){
            unset($data['id']);
            return $this->update($data,array("id"=>$id));
        }
        return $this->insert($data);
    }

    /**
     * isCount   是否返回count
     */
    public function getList($params,$offset = 0,$pageSize = null,$order=null,$isCount = false) {
        if(empty($params) && empty($pageSize)){
            return array();
        }
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'][$key] = $value;

        }

        $result = array();
        if($isCount){
            $result['cnt'] = $this->count($where);
        }
        if($pageSize){
            $where['LIMIT'] = array($offset,$pageSize);
        }

        if($order){
            $where['ORDER'] = $order;
        }
        $result['list'] = $this->fetchAll($where,array("id","title","novel_id","keywords","chapter_order","status"));
        return $result;
    }

}
