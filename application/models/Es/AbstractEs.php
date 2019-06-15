<?php
require __DIR__.'/../../vendor/autoload.php';
use Elasticsearch\ClientBuilder;
class AbstractEsModel{

    private $_client;
    private $_hosts;

    protected $_index ="my_index";
    protected $_type = "my_type";

    public function __construct()
    {
        $this->_hosts = \Yaf_Registry::get('config')['es'];
        $clientBuilder = ClientBuilder::create();   // Instantiate a new ClientBuilder
        $clientBuilder->setHosts($this->_hosts)->setRetries(2);    // Set the hosts
        $this->_client = $clientBuilder->build();


    }

    public function search($params){
        if(empty($params['index'])){
            $params['index'] = $this->_index;
        }
        if(empty($params['type'])){
            $params['type'] = $this->_type;
        }
        $result = $this->_client->search($params);
       // echo var_dump($result);exit;
        $hits = array();
        if(isset($result['hits'])){
            $hits = $result['hits'];
        }

        if($hits && $hits['total']>0 && !empty($hits['hits'])){
            return $hits;
        }
        return false;
    }

    public function aggs_search($params){

        $result = $this->_client->search($params);
        $aggregations = array();

        if(isset($result['aggregations']['log_over_time']) && $result['hits']['total']>0){
            $aggregations = $result['aggregations']['log_over_time'];
            $aggregations['total'] = $result['hits']['total'];
            return $aggregations;

        }

        return $aggregations;

    }

    public function addIndex($params){
        if(empty($params['index'])){
            return false;
        }
        $result = $this->_client->indices()->create($params);
        return $result['acknowledged'];
    }

    public function delIndex($params){
        if(empty($params['index'])){
            throw new Exception("no index name");
        }
        $result = $this->_client->indices()->delete($params);
        return $result['acknowledged'];
    }
    public function putIndexMapping($params){
        if(empty($params['index'])){
            return false;
        }
        $result = $this->_client->indices()->putMapping($params);
        return $result['acknowledged'];
    }

    public function insertData($params){
        if(empty($params['index'])){
            $params['index'] = $this->_index;
        }
        if(empty($params['type'])){
            $params['type'] = $this->_type;
        }

        if(empty($params['body'])){
            throw new Exception("body empty");
        }
        $result = $this->_client->index($params);
        return $result;
    }

    /**
     * @param $params
     * @param int $return   1 true or false 2.todo
     * @return bool
     * @throws Exception
     */
    public function insertBatchData($params,$return = 1){
        if(empty($params['body'])){
            throw new Exception("batch insert params body empty");
        }
        $batchParams = array();
        foreach ($params['body'] as $value){

            $batchParams['body'][] = array(
                'index' => [
                    '_index' =>empty($params['index'])?$this->_index:$params['index'],
                    '_type'  => empty($params['type'])?$this->_type:$params['type'],
                ]
            );

            $batchParams['body'][] = $value;

        }

        $result = $this->_client->bulk($batchParams);
        if($return == 1){
            $ret = $result['errors']?true:false;
        }
        return $ret;
    }

    public function get($params){
        if(empty($params['index'])){
            $params['index'] = $this->_index;
        }
        if(empty($params['type'])){
            $params['type'] = $this->_type;
        }
        if(empty($params['relate_id'])){
            throw new Exception("id empty");
        }
    }


}