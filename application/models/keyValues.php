<?php
class keyValuesModel extends AbstractModel {

    protected $_table = "keyvalues";
    protected $_database = "account";
    protected $_primary = "id"; 



    public function replaceKeys($data){
        if(empty($data['keys'])){
            return false;
        }
        $keys = $data['keys'];
        $ret = $this->fetchRow(array("keys"=>$keys));
        if($ret){
            unset($data['keys']);
            $result = $this->update($data,array("keys" => $keys));
        }else{
            $result = $this->insert($data);
        }
        return $result;
    }

    public function getData($params) {
        if(empty($params)){
            return array();
        }

        $result = array();
        $result['list'] = $this->fetchRow($params);
        return $result;
    }

}
