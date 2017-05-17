<?php

class mmInstitution
{

    public $institutionID;    
    public $title;

    public function __construct($institution = 0){

        if(is_object($institution)){
            $institutionObj = $institution;
        }else{
            $institutionObj = get_post($institution);
        }

        $this->institutionID = $institutionObj->ID;
        $this->title = $institutionObj->post_title;

    }
}