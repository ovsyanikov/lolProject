<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 19.01.2017
 * Time: 12:29
 */

class model implements  JsonSerializable {

    private $data;

    public function __construct(){

        $this->data = array();
    }

    public function  __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {

        $this->data[$name] = $value;

    }

    public function jsonSerialize() {
        return $this->data;
    }

    public function addMetaFields($fields = array()){

        foreach ($fields as $key=>$value){

            $value = get_post_meta($this->id,$key,true);
            if($value)
                $this->{$key} = $value;
            else
                $this->{$key} = '';

        }//foreach

    }

}