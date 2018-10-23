<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/9/14
 * Time: 下午1:49
 */

class WebController extends AbstractController{

    /**
     * 预览
     */
    public function novelrecomendAction(){
        try {
            $novelIdStr = $this->get("id");
            $novelList = array();
            if (!empty($novelIdStr)) {
                $ids = explode(",", $novelIdStr);
                $novelModel = new NovelModel();
                $novelList = $novelModel->getNovelbyIds($ids);
                foreach ($novelList as &$value){
                    $value['pic'] = \YC\Common::getUrl($value['pic']);
                }
            }
            $result = array(
                "code" => 200,
                "result" => $novelList
            );
        }catch (Exception $e){
            $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
            );
        }

        $this->renderJson($result);
    }

    public function recommendAction(){
        try {
            $keyValuesModel = new keyValuesModel();
            $type = $this->get("type")?:1;

            $xiaoshuoData = $keyValuesModel->fetchRow(array("keys"=>"recommend_".NovelModel::$_novel_class_pinxie[$type]));
            $this->_view->xiaoshuo_list = $xiaoshuo = $xiaoshuoData ? json_decode($xiaoshuoData['value'],true) :array();

            $articleType = $this->get("article_type")?:1;
            $wenxueData = $keyValuesModel->fetchRow(array("keys"=>"recommend_".ArticlesTypeModel::$article_type_pinyin[$articleType]));
            $this->_view->wenxue_list =$wenxue = $wenxueData ? json_decode($wenxueData['value'],true):array();
//            $wuxiaData = $keyValuesModel->fetchRow(array("keys" => "recommend_wuxia"));
//            $yanqingData = $keyValuesModel->fetchRow(array("keys" => "recommend_yanqing"));
//
//            $this->_view->wuxia =  $wuxia = $wuxiaData ? json_decode($wuxiaData['value'],true):array();
//            $this->_view->yanqing = $yanqing = $yanqingData ? json_decode($yanqingData['value'],true) : array();
            $xiaoshuoId = array();
            foreach ($xiaoshuo as $value){
                $xiaoshuoId[] = $value['id'];
            }

            $wenxueId = array();
            foreach ($wenxue as $value){
                $wenxueId[] = $value['id'];
            }

            $this->_view->xiaoshuo_id = implode(",",$xiaoshuoId);
            $this->_view->wenxue_id = implode(",",$wenxueId);

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function novelrecomendpostAction(){
        try {
            $novelIdStr = $this->get("id");
            $type = $this->get("type");
            if(!isset(NovelModel::$_novel_class_pinxie[$type])){
                throw new Exception("参数错误");
            }
            $novelList = array();
            if (!empty($novelIdStr)) {
                $ids = explode(",", $novelIdStr);
                $novelModel = new NovelModel();
                $novelData = $novelModel->getNovelbyIds($ids);

                foreach ($novelData as $value){
                    $novelTmp[$value['id']] = array(
                        "name" => $value['name'],
                        "author_name" => $value['author_name'],
                        "pic" =>  \YC\Common::getUrl($value['pic']),
                        "id" => $value['id']
                    );
                }
                foreach ($ids as $value){
                    if(isset($novelTmp[$value])) {
                        $novelList[] = $novelTmp[$value];
                    }
                }
            }

            $keyValuesModel = new keyValuesModel();
            $params = array(
                "keys" => "recommend_".NovelModel::$_novel_class_pinxie[$type],
                "value" => json_encode($novelList)
            );
            $keyValuesModel->replaceKeys($params);
            $result = array(
                "code" => 200,
            );
        }catch (Exception $e){
            $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
            );
        }

        $this->renderJson($result);
    }

    /**
     * 预览
     */
    public function articlerecomendAction(){
        try {
            $authorIdStr = $this->get("id");
            $authorList = array();
            if (!empty($authorIdStr)) {
                $ids = explode(",", $authorIdStr);
                $articleModel = new ArticleAuthorModel();
                $authorList = $articleModel->getAuthorsbyIds($ids);
                foreach ($authorList as &$value){
                    $value['pic'] = \YC\Common::getUrl($value['pic']);
                }
            }
            $result = array(
                "code" => 200,
                "result" => $authorList
            );
        }catch (Exception $e){
            $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
            );
        }

        $this->renderJson($result);
    }

    public function articlerecomendpostAction(){
        try {
            $authorIdStr = $this->get("id");
            $type = $this->get("article_type");
            if(!isset(ArticlesTypeModel::$ArticleType[$type])){
                throw new Exception("参数错误");
            }
            $authorList = array();
            if (!empty($authorIdStr)) {
                $ids = explode(",", $authorIdStr);
                $articleAuthorModel = new ArticleAuthorModel();
                $authorData = $articleAuthorModel->getAuthorsbyIds($ids);

                foreach ($authorData as $value){
                    $authorTmp[$value['id']] = array(
                        "author_name" => $value['author_name'],
                        "pic" =>  \YC\Common::getUrl($value['pic']),
                        "id" => $value['id']
                    );
                }
                foreach ($ids as $value){
                    if(isset($authorTmp[$value])) {
                        $authorList[] = $authorTmp[$value];
                    }
                }
            }

            $keyValuesModel = new keyValuesModel();
            $params = array(
                "keys" => "recommend_".ArticlesTypeModel::$article_type_pinyin[$type],
                "value" => json_encode($authorList)
            );
            $keyValuesModel->replaceKeys($params);
            $result = array(
                "code" => 200,
            );
        }catch (Exception $e){
            $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
            );
        }

        $this->renderJson($result);
    }
}