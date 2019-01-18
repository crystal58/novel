<?php
/**
 * Created by PhpStorm.
 * User: zhangyapin
 * Date: 2018/9/14
 * Time: 下午1:49
 */

class WebController extends AbstractController
{

    /**
     * 预览
     */
    public function novelrecomendAction()
    {
        try {
            $novelIdStr = $this->get("id");
            $novelList = array();
            if (!empty($novelIdStr)) {
                $ids = explode(",", $novelIdStr);
                $novelModel = new NovelModel();
                $novelList = $novelModel->getNovelbyIds($ids);
                foreach ($novelList as &$value) {
                    $value['pic'] = \YC\Common::getUrl($value['pic']);
                }
            }
            $result = array(
                "code" => 200,
                "result" => $novelList
            );
        } catch (Exception $e) {
            $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
            );
        }

        $this->renderJson($result);
    }

    public function recommendAction()
    {
        try {
            $keyValuesModel = new keyValuesModel();
            $type = $this->get("type") ?: 1;

            $xiaoshuoData = $keyValuesModel->fetchRow(array("keys" => "recommend_" . NovelModel::$_novel_class_pinxie[$type]));
            $this->_view->xiaoshuo_list = $xiaoshuo = $xiaoshuoData ? json_decode($xiaoshuoData['value'], true) : array();

            $articleType = $this->get("article_type") ?: 1;
            $wenxueData = $keyValuesModel->fetchRow(array("keys" => "recommend_" . ArticlesTypeModel::$article_type_pinyin[$articleType]));
            $this->_view->wenxue_list = $wenxue = $wenxueData ? json_decode($wenxueData['value'], true) : array();
//            $wuxiaData = $keyValuesModel->fetchRow(array("keys" => "recommend_wuxia"));
//            $yanqingData = $keyValuesModel->fetchRow(array("keys" => "recommend_yanqing"));
//
//            $this->_view->wuxia =  $wuxia = $wuxiaData ? json_decode($wuxiaData['value'],true):array();
//            $this->_view->yanqing = $yanqing = $yanqingData ? json_decode($yanqingData['value'],true) : array();
            $xiaoshuoId = array();
            foreach ($xiaoshuo as $value) {
                $xiaoshuoId[] = $value['id'];
            }

            $wenxueId = array();
            foreach ($wenxue as $value) {
                $wenxueId[] = $value['id'];
            }

            $this->_view->xiaoshuo_id = implode(",", $xiaoshuoId);
            $this->_view->wenxue_id = implode(",", $wenxueId);

        } catch (Exception $e) {
            $this->processException($this->getRequest()->getControllerName(), $this->getRequest()->getActionName(), $e);
        }
    }

    public function novelrecomendpostAction()
    {
        try {
            $novelIdStr = $this->get("id");
            $type = $this->get("type");
            if (!isset(NovelModel::$_novel_class_pinxie[$type])) {
                throw new Exception("参数错误");
            }
            $novelList = array();
            if (!empty($novelIdStr)) {
                $ids = explode(",", $novelIdStr);
                $novelModel = new NovelModel();
                $novelData = $novelModel->getNovelbyIds($ids);

                foreach ($novelData as $value) {
                    $novelTmp[$value['id']] = array(
                        "name" => $value['name'],
                        "author_name" => $value['author_name'],
                        "pic" => \YC\Common::getUrl($value['pic']),
                        "id" => $value['id']
                    );
                }
                foreach ($ids as $value) {
                    if (isset($novelTmp[$value])) {
                        $novelList[] = $novelTmp[$value];
                    }
                }
            }

            $keyValuesModel = new keyValuesModel();
            $params = array(
                "keys" => "recommend_" . NovelModel::$_novel_class_pinxie[$type],
                "value" => json_encode($novelList)
            );
            $keyValuesModel->replaceKeys($params);
            $result = array(
                "code" => 200,
            );
        } catch (Exception $e) {
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
    public function articlerecomendAction()
    {
        try {
            $authorIdStr = $this->get("id");
            $authorList = array();
            if (!empty($authorIdStr)) {
                $ids = explode(",", $authorIdStr);
                $articleModel = new ArticleAuthorModel();
                $authorList = $articleModel->getAuthorsbyIds($ids);
                foreach ($authorList as &$value) {
                    $value['pic'] = \YC\Common::getUrl($value['pic']);
                }
            }
            $result = array(
                "code" => 200,
                "result" => $authorList
            );
        } catch (Exception $e) {
            $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
            );
        }

        $this->renderJson($result);
    }

    public function articlerecomendpostAction()
    {
        try {
            $authorIdStr = $this->get("id");
            $type = $this->get("article_type");
            if (!isset(ArticlesTypeModel::$ArticleType[$type])) {
                throw new Exception("参数错误");
            }
            $authorList = array();
            if (!empty($authorIdStr)) {
                $ids = explode(",", $authorIdStr);
                $articleAuthorModel = new ArticleAuthorModel();
                $authorData = $articleAuthorModel->getAuthorsbyIds($ids);

                foreach ($authorData as $value) {
                    $authorTmp[$value['id']] = array(
                        "author_name" => $value['author_name'],
                        "pic" => \YC\Common::getUrl($value['pic']),
                        "id" => $value['id']
                    );
                }
                foreach ($ids as $value) {
                    if (isset($authorTmp[$value])) {
                        $authorList[] = $authorTmp[$value];
                    }
                }
            }

            $keyValuesModel = new keyValuesModel();
            $params = array(
                "keys" => "recommend_" . ArticlesTypeModel::$article_type_pinyin[$type],
                "value" => json_encode($authorList)
            );
            $keyValuesModel->replaceKeys($params);
            $result = array(
                "code" => 200,
            );
        } catch (Exception $e) {
            $result = array(
                "code" => $e->getCode(),
                "msg" => $e->getMessage()
            );
        }

        $this->renderJson($result);
    }

    public function createAllUserAction()
    {
        try {
            $authorModel = new AuthorModel();
            $authorList = $authorModel->getAllAuthor();

            $sortList = $other = array();
            foreach($authorList as $value){
                $letter = $this->_getFirstCharter($value['author_name']);
                if($letter){
                    $sortList[$letter][] = $value;
                }else{
                    $other['其他'][] = $value;
                }
            }
            $novelAuthorList = array_merge($sortList,$other);
            $articleAuthorModel = new ArticleAuthorModel();
            $articleList = $articleAuthorModel->getAllAuthor();

            $sortList = $other = array();
            foreach($articleList as $value){
                $letter = $this->_getFirstCharter($value['author_name']);
                if($letter){
                    $sortList[$letter][] = $value;
                }else{
                    $other['其他'][] = $value;
                }
            }
            $articleAuthorList = array_merge($sortList,$other);

        } catch (Exception $e) {
            $this->processException($this->getRequest()->getControllerName(), $this->getRequest()->getActionName(), $e);
        }
    }

    public function _getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) return 'A';
        if ($asc >= -20283 && $asc <= -19776) return 'B';
        if ($asc >= -19775 && $asc <= -19219) return 'C';
        if ($asc >= -19218 && $asc <= -18711) return 'D';
        if ($asc >= -18710 && $asc <= -18527) return 'E';
        if ($asc >= -18526 && $asc <= -18240) return 'F';
        if ($asc >= -18239 && $asc <= -17923) return 'G';
        if ($asc >= -17922 && $asc <= -17418) return 'H';
        if ($asc >= -17417 && $asc <= -16475) return 'J';
        if ($asc >= -16474 && $asc <= -16213) return 'K';
        if ($asc >= -16212 && $asc <= -15641) return 'L';
        if ($asc >= -15640 && $asc <= -15166) return 'M';
        if ($asc >= -15165 && $asc <= -14923) return 'N';
        if ($asc >= -14922 && $asc <= -14915) return 'O';
        if ($asc >= -14914 && $asc <= -14631) return 'P';
        if ($asc >= -14630 && $asc <= -14150) return 'Q';
        if ($asc >= -14149 && $asc <= -14091) return 'R';
        if ($asc >= -14090 && $asc <= -13319) return 'S';
        if ($asc >= -13318 && $asc <= -12839) return 'T';
        if ($asc >= -12838 && $asc <= -12557) return 'W';
        if ($asc >= -12556 && $asc <= -11848) return 'X';
        if ($asc >= -11847 && $asc <= -11056) return 'Y';
        if ($asc >= -11055 && $asc <= -10247) return 'Z';
        return null;
    }
}