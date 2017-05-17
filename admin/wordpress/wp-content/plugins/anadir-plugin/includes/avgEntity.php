<?php

class avgEntity extends Entity{
    
    public $image;

    public $OtherFields;

    public $type;

    public $date;

    public function getEntityData($entityParams = array()){ 
       
        $news = get_post($entityParams->ID);

        $this->title = apply_filters('the_title', $news->post_title);
        $this->count = 1;
        $this->id = $news->ID;
        $this->type = $news->post_type;

        $partArray =  explode(' ',$news->post_date);

        $date = explode('-',$partArray[0]);
        $time = explode(':',$partArray[1]);

        $this->date = "{$date[2]}.{$date[1]}.{$date[0]} / {$time[0]}:{$time[1]}";

        $thumb_id = get_post_thumbnail_id($this->id);

        $this->description = $news->post_content;//html_entity_decode(strip_tags( ));//strip_tags(strip_shortcodes ( $news->post_content ));

        $this->image = wp_get_attachment_image_src($thumb_id,'full');//

        $this->image = $this->image[0];

        $queryTaxResult = wp_get_post_terms( $this->id, 'ataxes', array() );

        $this->taxonomy = (count($queryTaxResult) == 0 ? wp_get_post_terms( $this->id, 'culturetax', array() ) : $queryTaxResult);

        $this->taxonomy = (count($this->taxonomy) == 0 ?  wp_get_post_terms( $this->id, 'organizationstax', array() ) : $this->taxonomy);

        $this->pTax = wp_get_post_terms( $this->id, 'ptax', array() )[0];

        foreach ($this->OtherFields as $key => $value) {
                $this->OtherFields[$key] = get_post_meta($this->id,$key,true);//;
        }//foreach

        return $this;

    }//getNews

}