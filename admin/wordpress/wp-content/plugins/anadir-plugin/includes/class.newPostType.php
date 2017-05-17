<?php
//include 'class.mmOrders.php';

class newPostType
{
    private $name;
    private $type;
    private $supports;
    private $taxonomies = '';
    private $metaBox;

    public function __construct($postType,$name,$supports,$tax=array(),$metaBox = array()){

        $this->name = $name;
        $this->type = $postType;
        $this->supports = $supports;
        $this->taxonomies = $tax;
        $this->metaBox = $metaBox;

        $this->registerPostType();

    }

    public function registerPostType(){

        if( count($this->taxonomies)>= 1 ){
            foreach($this->taxonomies as &$tax){
                if($tax != 'post_tag'){

                    $taxLabel = $tax;
                    $tax = str_replace(' ','',$tax);
                    $tax = mb_strtolower($tax,'UTF-8');

                    if(!get_taxonomy($tax)){
                        $this->registerTaxonomy($tax,$taxLabel);
                    }//if

                }//if

            }//foreach
        }

        $labels = array(
            'name'                => __( $this->name, 'Post Type General Name', 'migrantMag' ),
            'singular_name'       => __( $this->name, 'Post Type Singular Name', 'migrantMag' ),
            'menu_name'           => __( $this->name, 'migrantMag' ),
            'name_admin_bar'      => __( $this->name, 'migrantMag' ),
            'parent_item_colon'   => __( 'Parent Item:', 'migrantMag' ),
            'all_items'           => __( 'All Items', 'migrantMag' ),
            'add_new_item'        => __( 'Add New Item', 'migrantMag' ),
            'add_new'             => __( 'Add New', 'migrantMag' ),
            'new_item'            => __( 'New Item', 'migrantMag' ),
            'edit_item'           => __( 'Edit Item', 'migrantMag' ),
            'update_item'         => __( 'Update Item', 'migrantMag' ),
            'view_item'           => __( 'View Item', 'migrantMag' ),
            'search_items'        => __( 'Search Item', 'migrantMag' ),
            'not_found'           => __( 'Not found', 'migrantMag' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'migrantMag' ),
        );
        $args = array(
            'label'               => __( $this->name, 'migrantMag' ),
            'labels'              => $labels,
            'supports'            => $this->supports,
            'taxonomies'          => $this->taxonomies,
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 5,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'show_tagcloud' => true,
            'can_export'          => false,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page'
        );
        register_post_type( $this->type, $args );
        
        if(!empty($this->metaBox)){

            add_action('admin_init',array($this,'metaBoxInizialisation'));

        }
        
        add_action('save_post',array($this,'savePostData'),999,2);
    }

    public function metaBoxInizialisation(){
        
        add_action( 'add_meta_boxes', $this->createMetaBox(), 10, 2 );

    }

    private function createMetaBox(){

        foreach($this->metaBox as $metaBoxItem){

            if(function_exists('add_meta_box')){
                add_meta_box( 'meta-'.$metaBoxItem['name'],$metaBoxItem['name'] , array($this,'createForm'), $this->type, 'advanced','default', $metaBoxItem['fields']);
            }
        }


    }

    public function createForm($post,$fields){

        $returnHtml = '';
        
        foreach($fields['args'] as $field){


            if($field['name'] == 'orderStatus'){
                $meta = $post->post_status;
            }else{
                $meta = get_post_meta( $post->ID, $field['name'], true );
            }

            if($field['type'] == 'editor'){

//
//                print_r($field);
//                exit();
//
//
            }

            if($meta != false){
               
                if($field['type'] != 'select' && $field['type'] != 'button'){
                    $field['values'] = $meta;

                }else if($field['type'] == 'select'){
                    foreach($field['values'] as $key=>$value){
                        if($meta == $key){
                            $field['values'][$key]['active'] = true;
                        }else{
                            $field['values'][$key]['active'] = false;
                        }
                    }
                }
            }
            
            $field['id'] = $field['name'];
            $field['name'] = 'meta['.$field['name'].']';

            $returnHtml .= new formGenerator($field);

        }

        
        echo $returnHtml;

    }

    public function addMetaBox(){

        $generateHtml = new formGenerator();

        add_meta_box(
            'productSettings',
            __( 'Settings', 'migranshop' ),
            $generateHtml,
            'products'
        );

    }

    private function registerTaxonomy($tax,$taxLabel){

        register_taxonomy($tax, $this->type, array(

            'hierarchical' => true,

            'labels' => array(
                'name' => __( $taxLabel, 'taxonomy general name' ),
                'singular_name' => __( $taxLabel, 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Category' ),
                'all_items' => __( 'All Category' ),
                'parent_item' => __( 'Parent Category' ),
                'parent_item_colon' => __( 'Parent Category:' ),
                'edit_item' => __( 'Edit Category' ),
                'update_item' => __( 'Update Category' ),
                'add_new_item' => __( 'Add New Category' ),
                'new_item_name' => __( 'New Category Name' ),
                'menu_name' => __( $taxLabel ),
            ),

            'rewrite' => array(
                'slug' => $tax,
                'with_front' => false,
                'hierarchical' => true
            ),
        ));
    }

    public function savePostData($post_id) {
        GLOBAL $wpdb;
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        
        if(isset($_POST['meta'])){
//            echo '<pre>';
//            print_r($_POST);
//            echo '</pre>';
//            exit();

            foreach ($_POST['meta'] as $key=>$value){
                if($key=='orderStatus'){
                    $wpdb->update( $wpdb->posts,array('post_status'=>$value),array('ID'=>$post_id),$format = null, $where_format = null );
                    //$this->sendMail($post_id);
                    //ajaxApi::sendMail($post_id, $value);
                } else {

                    update_post_meta($post_id,$key,$value);

                }
            }
            
        }//if
        
        
    }

}
