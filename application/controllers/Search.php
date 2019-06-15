<?php


class SearchController extends \YC\ControllerEx {

    public function esAction(){
        try{
            $testModel = new \Es\testModel();
//删除索引
//            $params = ['index' => 'my_index'];
//            $result = $testModel->delIndex($params);
// 创建索引
//            $result  = $testModel->createIndex();
//            exit;
            //更新索引mapping
           // $result = $testModel->updateIndexMapping();
            //索引数据
//            $params['body'] = array(
//
//                'relate_id' => 7,
//                'type' => 2,
//                'title' => "test",
//                'author' => 'crystal',
//                'description' => "testaa",
//            );
//            $result = $testModel->insertData($params);
//exit;
            //批量索引数据
//            $arr = array(
//                '有时使用原生 JSON 来进行测试会十分方便，',
//                '或者用原生 JSON 来进行不同系统的移植也同样方便。',
//                '你可以在 body 中用原生 JSON 字符串，',
//                '这样客户端会自动进行检查操作'  ,
//                '而 PHP 程序则重复上述操作构建文档数据。'
//            );
//            for($i = 11;$i<16;$i++){
//
//                $params['body'][] = array(
//                        'relate_id' => $i+1,
//                        'relate_type' => 1,
//                        'title' => "原生json批量",
//                        'author' => 'stevecrystal',
//                        'description' => $arr[$i-11],
//                );
//            }
//            $result = $testModel->insertBatchData($params);exit;
            //search 单个条件查询
//            $params = array(
//                'body' => array(
//                    'query' => array(
//                        "match" => array(
//                            "description" => "PHP",
//                        )
//                    )
//                )
//
//            );
            //search 多条件查询 AND
//            $params = array(
//                'body' => array(
//                    'query' => array(
//                        'bool' => array(
//                            'must' => array(
//                                array('match' => array('description'=>'php')),
//                                array('match' => array('title'=>'批量'))
//                            )
//                        )
//                    )
//                )
//            );
         //   search  多条件查询 或
            $params = array(
                //'scroll' => '30s', //建议页数顺序的时候，跳页的时候消耗比较大

                'body' => array(
                    'from' => 6,
                    'size' => 2,
                    'query' => array(
                        'bool' => array(
                            'should' => array(
                                array('match' => array('description' =>'php')),
                                array('match' => array('title' => '批量')),
                                array('match' => array('author' => 'crystal'))
                            )
                        )
                    ),
                    "highlight" => [
                        'pre_tags' => ["<em style='color: red'>"],
                        'post_tags' => ["</em>"],
                        "fields" => [
                            "title" => new stdClass(),
                            "description" => new stdClass()
                        ]
                    ]


                )
            );
            //search aggs
//            $params = array(
//
//            );

            $result = $testModel->search($params);
            foreach ($result['hits'] as $value){
                var_dump($value['highlight']);
                echo $value['highlight']['title'][0]."<br>";
            }
            exit;
            var_dump($result);exit;
            echo json_encode($result);
        }catch (Exception $e){
            $e = array('code' => $e->getCode(),'msg'=>$e->getMessage());
            echo json_encode($e);
        }
         exit;

    }
}
