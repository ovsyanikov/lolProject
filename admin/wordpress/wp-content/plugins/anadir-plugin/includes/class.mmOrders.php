<?php

class mmOrders
{

    public $orderID;   
    
    public $recipientName;
    public $recipientEmail;
    public $recipientPhone;

    public $senderName;
    public $senderEmail;
    public $senderPhone;
    public $date;
    public $time;
    public $filePath = null;

    public $address = null;
    public $city = null;
    public $cityTitle = null;
    public $cinemaTitle = null;

    public $head = null;
    public $isOnWall;
    public $image;

    public $confession = null;
    public $isTemplate = false;
    public $confessionText = null;
    public $templateText;

    public $promo = null;
    
    public $cart;
    public $cartHtml = null;

    public $orderName;
    public $orderStatus;
    public $orderStatusLabel;

    public $totalPrice;

    public $discount = null;

    public function __construct($params = 0){

        if(!is_object($params) && isset($params['orderID']) && $params['orderID']!= 0){
            
            $this->orderID = $params['orderID'];
            $this->getOrder();
            
        }else if(is_object($params)){
            
            $this->orderID = $params->ID;
            $this->getOrder();
            
        }else{
            
            $orderPostParams = array(
                'post_status' => 'publish',
                'post_type' => 'myorder',
                'post_title' => 'order'
            );
            
            if(isset($params['author'])){
                $orderPostParams['post_author'] = intval($params['author']);
                
            }
            
            $this->orderID = wp_insert_post($orderPostParams,true);
            
            $this->recipientName = $params['recipientName'];
            $this->recipientEmail =  $params['recipientMail'];
            $this->recipientPhone =  $params['recipientPhone'];


            $this->senderName = $params['senderName'];
            $this->senderEmail =  $params['senderMail'];
            $this->senderPhone =  $params['senderPhone'];


            $this->date = date("Y-m-d");
            $this->time = date("H:i");

            if(isset($params['confession'])){

                $this->confession = $params['confession'];
                $this->confessionText = $params['templateConfession'];
                $this->isTemplate = true;

            }//if
            else{

                $this->confession = $params['confessionText'];
                $this->isTemplate = false;

            }//else


            if(isset($params['deliveryArrdess'])){

                $this->address = $params['deliveryArrdess'];

            }


            if(isset($params['promo'])){
                
                $this->promo = $params['promo'];
                
            }//if

            if(isset($params['city'])){

                $this->city = $params['city']['id'];
                $this->cityTitle = $params['city']['title'];
            }

            $this->cart = $params['productList'];
            $this->orderStatus = 'notPayed';

            $this->generateName();
            $this->generateOrderLabel();

            $this->totalPrice = $params['totalPrice'];

            if(isset($_FILES['photo'])){
                $uplDir = wp_upload_dir();

                $file = $uplDir['basedir'] . "/userconfessions/" .basename($_FILES['photo']['name']);
                $this->filePath = $file;

                copy($_FILES['photo']['tmp_name'],$file);
                $filetype = wp_check_filetype( basename( $_FILES['photo']['name'] ), null );
                $wp_upload_dir = wp_upload_dir();

                $attachment = array(
                    'guid'           => $file,
                    'post_mime_type' => $filetype['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file )),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );

                $attach_id = wp_insert_attachment( $attachment, $file, $this->orderID );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                add_post_meta($this->orderID , '_thumbnail_id', $attach_id, true);
                $this->filePath = $attach_id;
            }//if

            if(isset($params['isOnWall'])){
                $this->isOnWall = 'Да';
            }//if
            else{
                $this->isOnWall = 'Нет';
            }//else

            $this->saveOrder();
            
        }//else

    }//if
    
    public function generateOrderLabel(){
        if($this->orderStatus == 'payed'){$this->orderStatusLabel = 'Оплачен';}
        else if($this->orderStatus == 'complited'){$this->orderStatusLabel = 'Завершен';}
        else if($this->orderStatus == 'canceled'){$this->orderStatusLabel = 'Отменен';}
        else {$this->orderStatus = 'notPayed'; $this->orderStatusLabel = 'Не оплачен';}
    }
    
    public function getOrder(){
        
        $post = get_post($this->orderID); 
        $this->orderName = $post->post_title;

        $this->cart = get_post_meta($this->orderID, 'cart', true);

        $this->totalPrice = get_post_meta($this->orderID, 'totalPrice', true);

        $this->recipientName  =  get_post_meta($this->orderID, 'recipientName', true);
        $this->recipientEmail =  get_post_meta($this->orderID, 'recipientMail', true);

        $this->date  =  get_post_meta($this->orderID, 'orderDate', true);
        $this->time =  get_post_meta($this->orderID, 'orderTime', true);

        $this->recipientPhone =  get_post_meta($this->orderID, 'recipientPhone', true);

        $this->senderName = get_post_meta($this->orderID, 'senderName', true);
        $this->senderEmail = get_post_meta($this->orderID, 'senderMail', true);
        $this->senderPhone =  get_post_meta($this->orderID, 'senderPhone', true);
        $this->confession =  intval(get_post_meta($this->orderID, 'templateNumber', true));
        $this->promo =  get_post_meta($this->orderID, 'promo', true);

        if($this->promo != '' && $this->promo != null){
            $promoObj = get_page_by_title( $this->promo ,OBJECT , 'PromotionalCodes' );
            $promoId = $promoObj->ID;
            $this->discount = intval(get_post_meta($promoId,'disCount',true));
        }



        if(!$this->confession){
            $this->templateText = get_post_meta($this->orderID, 'templateTextNotStandart', true);
        }
        else{
            $this->confessionText = get_post_meta($this->orderID, 'templateText', true);
        }
        $this->filePath = intval(get_post_meta($this->orderID, 'filePath', true));

        $this->image = wp_get_attachment_image_src($this->filePath,'full');//

        $this->image = $this->image[0];

        $this->city = intval(get_post_meta($this->orderID, 'city', true));
        $post = get_post($this->city);
        $cityNumber = intval(get_post_meta($post->ID, 'cityNumber', true));

        if($post->post_type != 'cinemas'){
            $this->cityTitle = get_post_meta($this->orderID, 'cityTitle', true);
        }


        $this->isOnWall = get_post_meta($this->orderID, 'isOnWall', true);

        if($post->post_type == 'cities'){
            $this->cityTitle = $post->post_title;

            $head = ajaxApi::getPostWithParams(array(
                'type'=>'cityworkers',
                'meta_key' => 'city',
                'meta_value' => $cityNumber
            ));


            $this->head = $head[0]->OtherFields;

        }//if
        else{
            $this->cinemaTitle = $post->post_title;

            $head = ajaxApi::getPostWithParams(array(
                'type'=>'cinemaworkers',
                'meta_key' => 'cinemas',
                'meta_value' => $this->city
            ));

            $this->head = $head[0]->OtherFields;


        }//else



        $this->generateOrderLabel();
    }

    public function setStatus($orderStatus){

        $this->orderStatus = $orderStatus;
        $this->generateOrderLabel();
        
    }

    public function generateName(){

        $this->orderName = "{$this->orderID} - {$this->senderName} - {$this->recipientName}";


    }

    public function saveOrder(){
        global $wpdb;

        $orderPostParams = array(
            'ID' => $this->orderID,
            'post_status' => 'publish',
            'post_type' => 'myorder',
            'post_title' => $this->orderName
        );
        $orderID = wp_update_post($orderPostParams,true);


        if(!empty($this->promo)){
            update_post_meta($orderID, 'promo', $this->promo);
        }//if

        update_post_meta($this->orderID, 'recipientName', $this->recipientName);
        update_post_meta($this->orderID, 'recipientMail', $this->recipientEmail);

        update_post_meta($this->orderID, 'recipientPhone', $this->recipientPhone);

        $phone = $wpdb->get_results("SELECT * from {$wpdb->postmeta} where meta_key = 'recipientPhone' and meta_value = '{$this->recipientPhone}'");

        if(count($phone) == 0){//Добавляем клиента в БД



        }//if

        update_post_meta($this->orderID, 'cart', $this->cart );

        update_post_meta($this->orderID, 'city', $this->city);
        update_post_meta($this->orderID, 'cityTitle', $this->cityTitle);

        update_post_meta($this->orderID, 'senderName', $this->senderName);
        update_post_meta($this->orderID, 'senderMail', $this->senderEmail);
        update_post_meta($this->orderID, 'senderPhone', $this->senderPhone);

        update_post_meta($this->orderID, 'totalPrice',  $this->totalPrice);

        update_post_meta($this->orderID, 'deliveryAddress', $this->address);
        update_post_meta($this->orderID, 'isOnWall', $this->isOnWall);
        
        update_post_meta($this->orderID, 'orderDate',  $this->date);
        update_post_meta($this->orderID, 'orderTime',  $this->time);

        update_post_meta($this->orderID, 'userOrderStatus', $this->orderStatus);

        if($this->isTemplate){

            update_post_meta($this->orderID, 'templateNumber', $this->confession);
            update_post_meta($this->orderID, 'templateText',  $this->confessionText);

        }
        else{
            update_post_meta($this->orderID, 'templateTextNotStandart',  $this->confession);

        }


        update_post_meta($this->orderID, 'filePath',  $this->filePath);

        $this->orderID = $orderID;



         
    }



}