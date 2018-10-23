<?php
class ArticlesTypeModel extends AbstractModel {

    const ARTICLE_CLASS_STATUS = 1;     //正常
    const ARTICLE_CLASS_STATUS_DEL = -1; //删除

    protected $_table = "article_type";
    protected $_database = "account";
    protected $_primary = "id";

    const ARTICLE_TYPE_TANG = 1;
    const ARTICLE_TYPE_SONG = 2;

    public static $ArticleType = array(
        self::ARTICLE_TYPE_TANG => "隋唐",
        self::ARTICLE_TYPE_SONG=>"宋元"
    );
    public static $article_type_pinyin = array(
        self::ARTICLE_TYPE_TANG =>"tang",
        self::ARTICLE_TYPE_SONG => "song",

    );



    public function replaceClass($data){
        if(empty($data['name'])){
            return false;
        }
        $data['update_time'] = time();
        $classId = $data['id'];
        unset($data['id']);
        if($classId > 0){
            return $this->update($data,array("id"=>$classId));
        }
        $data['create_time'] = time();
        return $this->insert($data);
    }

    /**
     * isCount   是否返回count
     */
    public function getList($params,$offset = 0,$pageSize = null,$isCount = false) {
        $where = array();
        foreach($params as $key=>$value){
            $where['AND'][$key] = $value;
        }
        if($isCount){
            $result['cnt'] = $this->count($where);
        }
        if($pageSize){
            $where['AND']['LIMIT'] = array($offset,$pageSize);
        }
        $result['list'] = $this->fetchAll($where);

        return $result;
    }

    public function getAllClass(){
        $param = array(
//            "AND" => array(
//                "status"=>self::ARTICLE_CLASS_STATUS
//            ),
            "ORDER" => array(
                "id" => "DESC"
            )
        );
        return $this->fetchAll($param);
    }

}
