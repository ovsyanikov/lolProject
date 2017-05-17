<?php
class Entity{
    
    public $id;
    public $title;
    public $description;
    public $href;
    public $date;
    
    public function getEntityData($entityParams){
        
        $news = get_post($entityParams->ID);

        $this->title = $news->post_title;
        $this->id = $news->ID;
        $this->description =  strip_shortcodes ( $news->post_content );
        //$this->href = get_permalink( $this->id );
        $this->date =  $news->post_date_gmt;

        return $this;
        
    }
    
    public function addEntityFields($fields = array()){
        
        if(!empty($fields)){
            
            foreach ($fields as $key => $value) {
                $this->OtherFields[$key] = ( empty($value) ? '' : $value );
            }//foreach
            
        }//if
        
    }
    
}