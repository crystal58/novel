<?php
namespace Es;
class testModel extends \AbstractEsModel {
        public function createIndex(){
            $params = [
                'index' => $this->_index,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 3,
                        'number_of_replicas' => 1
                    ],
                    'mappings' => [
                        'my_type' => [
                            '_source' => array("enabled" => true),
                            'properties' => [
                                'relate_id' => ['type'=>'integer'],
                                'relate_type' => ['type'=>'integer'],
                                'title' => ['type'=>'text','analyzer'=>'standard'],
                                'author' => array('type'=>'text','analyzer'=>'standard'),
                                'description' => array('type'=>'text','analyzer'=>'standard'),
                            ]
                        ],

                    ]
                ],
            ];

           // echo json_encode($params);exit;

            $result = $this->addIndex($params);
            return $result;
        }

        public function updateIndexMapping(){
            $params = [
                'index' => $this->_index,
                'type'  => $this->_type,
                'body'  => [
                    'my_type'=>[
                            '_source' => ['enabled' => true],
                            'properties' => [
                                'relate_id' => ['type'=>'integer'],
                                'type' => ['type'=>'integer'],
                                'title' => ['type'=>'text','analyzer'=>'standard'],
                                'author' => array('type'=>'text','analyzer'=>'standard'),
                                'description' => array('type'=>'text','analyzer'=>'standard'),
                            ]
                        ]
                ]

            ];
            $result = parent::putIndexMapping($params);
            return $result;
        }

}