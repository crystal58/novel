<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/9/14
 * Time: 下午1:49
 */

class WebController extends AbstractController{

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
            $wuxiaData = $keyValuesModel->fetchRow(array("keys" => "recommend_wuxia"));
            $yanqingData = $keyValuesModel->fetchRow(array("keys" => "recommend_yanqing"));

            $this->_view->wuxia =  $wuxia = json_decode($wuxiaData['value'],true);
            $this->_view->yanqing = $yanqing = json_decode($yanqingData['value'],true);
            $wuxiaId = $yanqingId = array();
            foreach ($wuxia as $value){
                $wuxiaId[] = $value['id'];
            }
            foreach ($yanqing as $value){
                $yanqingId[] = $value['id'];
            }
            $this->_view->wuxia_id = $wuxiaId;
            $this->_view->yanqing_id = $yanqingId;

        }catch (Exception $e){
            $this->processException($this->getRequest()->getControllerName(),$this->getRequest()->getActionName(),$e);
        }
    }

    public function novelrecomendpostAction(){
        try {
            $novelIdStr = $this->get("id");
            $type = $this->get("type");
            $novelList = array();
            if (!empty($novelIdStr)) {
                $ids = explode(",", $novelIdStr);
                $novelModel = new NovelModel();
                $novelList = $novelModel->getNovelbyIds($ids);

                foreach ($novelList as &$value){
                    $value['pic'] = \YC\Common::getUrl($value['pic']);
                }
            }
            $keyValuesModel = new keyValuesModel();
            $params = array(
                "keys" => "recommend_".$type,
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
}