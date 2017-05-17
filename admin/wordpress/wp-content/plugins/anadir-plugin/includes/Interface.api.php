<?php

interface interfaceApi
{
    
    public static function getPostWithParams($params = array());
    
    public static function getProductKey();
    
    public static function getPosts($params = array());
        
    public static function echoDataWithHeader($data);

    public static function getCategories();

}