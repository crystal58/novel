<?php
class ArticlesTypeModel extends AbstractModel {

    const ARTICLE_CLASS_STATUS = 1;     //正常
    const ARTICLE_CLASS_STATUS_DEL = -1; //删除

    protected $_table = "article_type";
    protected $_database = "account";
    protected $_primary = "id";

    const ARTICLE_TYPE_TANG = 1;
    const ARTICLE_TYPE_SONG = 2;
    const ARTICLE_TYPE_YUAN = 4;
    const ARTICLE_TYPE_WENYAN = 5;
    const ARTICLE_TYPE_SANBAI = 15;
    const ARTICLE_TYPE_QUAN = 16;
    const ARTICLE_TYPE_SANBAI2 = 17;
    const ARTICLE_TYPE_SONGSANBAI = 18;
    const ARTICLE_TYPE_YUANSANBAI = 19;

    public static $ArticleType = array(
        self::ARTICLE_TYPE_TANG => "隋唐",
        self::ARTICLE_TYPE_SONG=>"宋元",
    );
    public static $article_type_pinyin = array(
        self::ARTICLE_TYPE_TANG =>"tang",
        self::ARTICLE_TYPE_SONG => "song",

    );

    public static $articleTypeTxt = array(
        self::ARTICLE_TYPE_TANG => "唐诗",
        self::ARTICLE_TYPE_SONG=>"宋词",
        self::ARTICLE_TYPE_YUAN=>"元曲",
        self::ARTICLE_TYPE_WENYAN => "文言文",
        self::ARTICLE_TYPE_SANBAI => "唐诗三百首",
        self::ARTICLE_TYPE_QUAN => "全唐诗",
        self::ARTICLE_TYPE_SANBAI2 => "唐诗三百首",
        self::ARTICLE_TYPE_SONGSANBAI => "宋词三百首",
        self::ARTICLE_TYPE_YUANSANBAI => "元曲三百首"
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
