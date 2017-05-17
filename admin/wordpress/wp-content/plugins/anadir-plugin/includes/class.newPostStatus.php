<?php

class PostStatus
{
    private $status;
    private $label;    

    public function __construct($postStatus,$label){

        $this->status = $postStatus;
        $this->label = $label;

        add_action( 'init', array($this,'registerPostStatus') );
    }

    public function registerPostStatus(){

        $args = array(
            
            'label'                     => _x( $this->label, 'post' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( $this->label.' <span class="count">(%s)</span>', $this->label.' <span class="count">(%s)</span>' ),
	
        );
        
        register_post_status( $this->status, $args );
    }


}
