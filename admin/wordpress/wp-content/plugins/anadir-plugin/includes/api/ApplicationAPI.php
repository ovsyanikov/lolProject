<?php

/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 19.01.2017
 * Time: 12:33
 */
require_once 'model.php';

class ApplicationAPI
{

    public static function echoDataWithHeader($data){

        header("Content-Type: application/json");

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Headers: application/json');

        if(isset($data['fields']) && isset($data['header']) && $data['header'] == 'json'){
            echo json_encode($data['fields']);
        }//if
        else if(isset($data['fields'])){
            echo $data['fields'];
        }//else
        else{
            echo json_encode(array());
        }//else



        exit();

    }

    public static function registerApiAction($action){

        add_action( "wp_ajax_$action", array('ApplicationAPI', $action));
        add_action( "wp_ajax_nopriv_$action", array('ApplicationAPI', $action));


    }

    public static function getPosts($params = array()){

        $postParams = array(
            'numberposts'     => isset($params['numberposts'])?$params['numberposts']:-1,
            'offset'          => isset($params['offset'])?$params['offset']:0,
            'orderby'         => isset($params['orderby'])?$params['orderby']:'post_date',
            'order'           => isset($params['order'])?$params['order']:'DESC',
            'include'         => isset($params['ID'])?$params['ID']:'',
            'exclude'         => '',
            'meta_key'        => isset($params['meta_key']) ? $params['meta_key']: '',
            'meta_value'      => isset($params['meta_value']) ? $params['meta_value']: '',
            'post_type'       => isset($params['type'])?$params['type']: array('production','News','Slider','Partners','portfolio'),
            'post_mime_type'  => '',
            'post_parent'     => '',
            'name' => isset($params['slug'])?$params['slug']:'',
            'post_status'     => array('new','buy','delivered','publish')
        );

        if(isset($params['author'])){
            $postParams['author'] = $params['author'];
        }//if

        if(isset($params['category'])){
            $postParams['tax_query'] = array(
                array(
                    'taxonomy' => $params['taxonomy'],
                    'field'    => 'id',
                    'terms'    => $params['category'],
                ),
            );
        }

        if(isset($params['meta_query_set'])){

            $postParams['meta_query'] = array();

            foreach($params['meta_query_values'] as $value){
                $postParams['meta_query'][]['key'] = $value['key'];
                $postParams['meta_query'][]['value'] = $value['value'];
                $postParams['meta_query'][]['relation'] = 'AND';

            }

        }

        $posts = array('posts' , 'count');

        if($params['type'] != 'apoint'){
            $posts['posts'] = get_posts($postParams);
            $the_query = new WP_Query( $postParams );
            $posts['count'] = $the_query->found_posts;
        }//if
        else
            $posts = get_posts($postParams);

        return $posts;

    }//getPosts

    public static function getAnadyrPosts($params = array()){

        $isEcho = true;
        $commentsOffset = 0;

        $currentUser = $_REQUEST['user'];

        if(!empty($currentUser))
            $user = get_user_by('login',$currentUser);

        //Если в параметре пустой массив
        if(empty($params)){
            $posts = self::getPosts($_REQUEST) ;
            if(isset($_REQUEST['commentsCount']))
                $commentsOffset = intval($_REQUEST['commentsCount']);

            $pType = $_REQUEST['type'];

        }//if
        else{
            $posts = self::getPosts($params) ;
            if(isset($params['commentsCount']))
                $commentsOffset = intval($params['commentsCount']);

            $pType = $params['type'];
            $isEcho = false;
        }

        $enumerablePosts = (isset($posts['posts']) ? $posts['posts'] : $posts);

        if(isset($posts['posts'])){
            $resultProducts = array('posts' => array());
        }
        else
            $resultProducts = array();

        foreach ($enumerablePosts  as $postObj){
            $model = new model();
            $model->id = $postObj->ID;
            $modelMetaFields = array();

            switch ($pType){

                case 'anews': case 'unews': case 'culturelife':
                    $modelMetaFields['anonce'] = '';
                    $modelMetaFields['newsPhotos'] = '';
                    $owner = get_userdata( $postObj->post_author);

                    $model->owner =  array(
                        'login' => $owner->user_login,
                        'firstName' => $owner->first_name,
                        'lastName' => $owner->last_name,
                        'image' => get_user_meta($postObj->post_author, 'avatar', true)
                    );

                    $model->comments = self::getComments(
                        array(
                            'post' => $model->id,
                            'number' => $commentsOffset,
                            'offset' => $commentsOffset)
                    );

                    $model->likes = intval( get_post_meta($model->id,'likesCount',true) );

                    $likeUsers = get_post_meta($model->id,'likes',true);

                    if(in_array($user->ID,$likeUsers))
                        $model->likeActive = true;
                    else
                        $model->likeActive = false;

                    $model->reposts = intval( get_post_meta($model->id,'reposts',true) );

                    break;

                case 'apoint':
                    $modelMetaFields['home']='';
                    $modelMetaFields['aObject']='';
                    $modelMetaFields['htmlPath']='';
                    break;

                case 'organizations':
                    $modelMetaFields['anonce'] = '';
                    $modelMetaFields['phones'] = '';
                    $modelMetaFields['address'] = '';
                    $modelMetaFields['email'] = '';
                    $modelMetaFields['contactFace'] = '';
                    $modelMetaFields['contactFaceFIO'] = '';
                    $modelMetaFields['Fax'] = '';
                    $modelMetaFields['Skype'] = '';
                    $modelMetaFields['WhatsApp'] = '';
                    $modelMetaFields['siteURL'] = '';
                    $modelMetaFields['addressPoint'] = '';

                    $modelMetaFields['vkURL'] = '';
                    $modelMetaFields['twitterURL'] = '';
                    $modelMetaFields['okURL'] = '';
                    $modelMetaFields['facebookURL'] = '';
                    $modelMetaFields['instagramURL'] = '';


                    break;

                case 'mainslider':
                    $modelMetaFields['path'] = '';
                    break;

            }//switch


            $model->addMetaFields($modelMetaFields);

            $model->title = $postObj->post_title;
            $model->type = $postObj->post_type;

            if($pType != 'apoint'){
//            ini_set("display_errors",1);
//            error_reporting(E_ALL);
                $partArray =  explode(' ',$postObj->post_date);

                $date = explode('-',$partArray[0]);
                $time = explode(':',$partArray[1]);

                $model->date = "{$date[2]}.{$date[1]}.{$date[0]} / {$time[0]}:{$time[1]}";

                $postTime = new DateTime("{$date[0]}-{$date[1]}-{$date[2]} {$time[0]}:{$time[1]}");

                $nowTime = new DateTime( current_time("Y-m-d H:i"));

                //$model->time = array($nowTime , $postTime,date("Y-m-d"));
                 $diffObject = $nowTime->diff($postTime);

                 $model->time = array(
                     'days' => $diffObject->format("%a"),
                     'hours' => $diffObject->format("%h"),
                     'minutes' => $diffObject->format("%i"),
                     'nowTime' => $nowTime->format("%h:%i:%s"),
                     'postTime' => $postTime->format("%h:%i:%s")
                 );

            }//if


            $thumb_id = get_post_thumbnail_id($model->id);

            $model->description = html_entity_decode(strip_tags( $postObj->post_content));//html_entity_decode(strip_tags( ));//strip_tags(strip_shortcodes ( $news->post_content ));

            $model->image = wp_get_attachment_image_src($thumb_id,'full');//

            $model->image = $model->image[0];

            $queryTaxResult = wp_get_post_terms( $model->id, 'ataxes', array() );

            $model->taxonomy = (count($queryTaxResult) == 0 ? wp_get_post_terms( $model->id, 'culturetax', array() ) : $queryTaxResult);

            $model->taxonomy = (count($model->taxonomy) == 0 ?  wp_get_post_terms( $model->id, 'organizationstax', array() ) : $model->taxonomy);

            $model->pTax = wp_get_post_terms( $model->id, 'ptax', array() )[0];



            if(isset($posts['posts']))
                $resultProducts['posts'][] = $model;
            else
                $resultProducts[] = $model;

        }//foreach

        if(isset($posts['posts']))
            $resultProducts['count'] = $posts['count'];

        if($isEcho){


            self::echoDataWithHeader(array(
                'header' => 'json',
                'fields' => $resultProducts
            ));

        }
        else{

            return $resultProducts;

        }//else



    }

    public static function getPoints(){

        $categories = get_categories(array(
                'taxonomy'     => 'ptax',
                'pad_counts'   => false)
        );
        $points = array();

        foreach ($categories as $category){

            $points[] = self::getAnadyrPosts(array(
                'type' => 'apoint',
                'numberposts' => -1,
                'category' => $category->term_id,
                'taxonomy' => 'ptax'
            ));

        }

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $points
            ));

    }

    public static function getCategoriesR($parent = null, $tax = null){

        $args = array(
            'orderby'           => 'name',
            'order'             => 'ASC',
            'hide_empty'        => false,
            'exclude'           => array(),
            'exclude_tree'      => array(),
            'include'           => isset($_REQUEST['ID'])?$_REQUEST['ID']:array(),
            'fields'            => 'all',
            'slug'              => '',
            'parent'            => $parent == null ? 0 : $parent,
            'hierarchical'      => false,
            'childless'         => false,
            'get'               => '',
            'name__like'        => '',
            'description__like' => '',
            'pad_counts'        => false,
            'offset'            => '',
            'search'            => '',
            'cache_domain'      => 'core'
        );


        $taxParam = $tax == null ? 'ataxes' : $tax;
        $categories = get_terms($taxParam,$args);

        if(count($categories) == 0){

            return array();

        }

        $resultCategories = array();


        foreach ($categories as $category) {

            //if($category->count == 0)continue;

            $categoryObject = new stdClass();
            $categoryObject->name = html_entity_decode($category->name);
            $categoryObject->term_id = $category->term_id;
            $categoryObject->slug = $category->slug;
            $categoryObject->parent = $parent!=null ? $parent : '';
            $categoryObject->image = get_option( 'productstax_term_images');
            $categoryObject->position = get_option( "taxonomy_{$category->term_id}" );
            $categoryObject->subCats = self::getCategoriesR($category->term_id,$taxParam);
            $categoryObject->count = $category->count;

            $resultCategories[] = $categoryObject;

        }//foreach
        if($taxParam != 'organizationstax')
            usort($resultCategories, "compare");

        return $resultCategories;

    }

    public static function getSingleCategory(){
        $slug = $_REQUEST['slug'];

        $args = array(
            'orderby'           => 'name',
            'order'             => 'ASC',
            'hide_empty'        => false,
            'exclude'           => array(),
            'exclude_tree'      => array(),
            'include'           => isset($_REQUEST['ID'])?$_REQUEST['ID']:array(),
            'fields'            => 'all',
            'slug'              => $slug,
            'parent'            => '',
            'hierarchical'      => false,
            'childless'         => false,
            'get'               => '',
            'name__like'        => '',
            'description__like' => '',
            'pad_counts'        => false,
            'offset'            => '',
            'search'            => '',
            'cache_domain'      => 'core'
        );

        $categories = get_terms('productstax',$args);

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $categories[0]
            )
        );

    }//getSingleCategory

    public static function getCategories($parent=null , $tax = null){

        if($tax == null){

            $tax = $_POST['tax'];

        }

        $categories = self::getCategoriesR(null , $tax);
        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $categories
            )
        );

    }

    public static function getSubCategories(){
        $parent = $_REQUEST['parent'];

        $args = array(
            'orderby'           => 'name',
            'order'             => 'ASC',
            'hide_empty'        => true,
            'exclude'           => array(),
            'exclude_tree'      => array(),
            'include'           => isset($_REQUEST['ID'])?$_REQUEST['ID']:array(),
            'fields'            => 'all',
            'slug'              => '',
            'parent'            => $parent,
            'hierarchical'      => false,
            'childless'         => false,
            'get'               => '',
            'name__like'        => '',
            'description__like' => '',
            'pad_counts'        => false,
            'offset'            => '',
            'search'            => '',
            'cache_domain'      => 'core'
        );

        $categories = get_terms('productstax',$args);

        $resultCategories = array();

        foreach ($categories as $category) {

            $categoryObject = new stdClass();
            $categoryObject->name = html_entity_decode($category->name);
            $categoryObject->term_id = $category->term_id;
            $categoryObject->slug = $category->slug;
            $categoryObject->parent = $category->parent;
            $categoryObject->image = get_option( 'productstax_term_images');

            foreach(get_option( 'productstax_term_images') as $catId=>$imageId){
                if($catId == $categoryObject->term_id){
                    $imageResult = wp_get_attachment_image_src( $imageId, 'thumbnail' );
                    $categoryObject->image = $imageResult[0];
                }//if
            }//foreach

            $resultCategories[] = $categoryObject;

        }//foreach

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $resultCategories
            )
        );

    }//getSubCategories

    public static function getComments($needEcho = null){

//        ini_set("display_errors",1);
//        error_reporting(E_ALL);

        $postID = intval(filter_input(INPUT_POST,'id',FILTER_SANITIZE_STRING));
        $numberposts = intval(filter_input(INPUT_POST,'number',FILTER_SANITIZE_STRING));
        $offset = intval(filter_input(INPUT_POST,'offset',FILTER_SANITIZE_STRING));

        if(isset($needEcho['post']))
            $commentsObject = wp_count_comments($needEcho['post']);
        else
            $commentsObject = wp_count_comments($postID);

        $numPosts = intval($needEcho == null ? $numberposts : $needEcho['number']);

        $params = array(
            'post_id' => $needEcho == null ? $postID : $needEcho['post'],
            'number' =>  $numPosts,
            'orderby'             => 'post_id',
            'order'               => 'ASC',


        );

        if($needEcho != null){
            $params['offset'] = $commentsObject->total_comments - $numPosts < 0 ? 0 :$commentsObject->total_comments - $numPosts;
        }//if
        else{
            $params['offset'] = 0;
        }//else

        $comments = array();
        $comments['posts'] = get_comments(
            $params
        );



        $comments['allCount'] = $commentsObject->total_comments;
        $comments['$numPosts'] = $numPosts;
        $comments['offset'] = $params['offset'];

        $resultComments = array();

        foreach ($comments['posts'] as &$comment ){


            $user = get_user_by('login',$comment->comment_author);

            if($user == null || $user == false)
                continue;

            $partArray =  explode(' ',$comment->comment_date);

            $date = explode('-',$partArray[0]);
            $time = explode(':',$partArray[1]);

            $postTime = new DateTime("{$date[0]}-{$date[1]}-{$date[2]} {$time[0]}:{$time[1]}");

            $nowTime = new DateTime( current_time("Y-m-d H:i"));

            //$model->time = array($nowTime , $postTime,date("Y-m-d"));
            $diffObject = $nowTime->diff($postTime);

            $likeUsers = get_comment_meta($comment->comment_ID,'likes',true);

            $resultComments[] = array(
                'id' => $comment->comment_ID,
                'text' => $comment->comment_content,
                'login' =>  $comment->comment_author,
                'avatar' =>  get_user_meta($user->ID, 'avatar', true),
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'time' => array(
                            'days' => $diffObject->format("%a"),
                            'hours' => $diffObject->format("%h"),
                            'minutes' => $diffObject->format("%i"),
                            'nowTime' => $nowTime->format("%h:%i:%s"),
                            'postTime' => $postTime->format("%h:%i:%s"),
                            'date' => "{$date[2]}.{$date[1]}.{$date[0]} / {$time[0]}:{$time[1]}"
                ),
                'likesCount' => intval( get_comment_meta($comment->comment_ID,'likesCount',true) ),
                'likeActive' => in_array($user->ID,$likeUsers) ? true : false

            );

        }
        $comments['posts']  = $resultComments;

        if($needEcho == null)
            self::echoDataWithHeader(
                array(
                    'header' => 'json',
                    'fields' => $comments
                )
            );
        else
            return $comments;

    }

    public static function addLike(){

        $name = trim(filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING));
        $postID = trim(filter_input(INPUT_POST,'id',FILTER_SANITIZE_STRING));
        $comment = false;

        if(isset($_POST['comment']) && $_POST['comment'] == 'comment'){
            $comment = true;
        }

        if(empty($name) || empty($postID)){

            self::echoDataWithHeader(
                array(
                    'header' => 'json',
                    'fields' => array(
                        'type' => 'error',
                        'message' => 'Не хватает параметров при запросе!'
                    )
                )
            );

        }//if

        $user = get_user_by('login',$name);

        if($user == false){

            self::echoDataWithHeader(
                array(
                    'header' => 'json',
                    'fields' => array(
                        'status' => 'error',
                        'message' => 'Пользователь не найден!'
                    )
                )
            );

        }//if

        $likes = !$comment ? get_post_meta($postID,'likes',true) : get_comment_meta($postID,'likes',true);
        $likesCount = !$comment ? intval(get_post_meta($postID,'likesCount','true')) : intval(get_comment_meta($postID,'likesCount','true'));

        $likesResult = 0;

        if(is_array($likes)){

            if(in_array($user->ID,$likes)){

                $likes = array_diff($likes,[$user->ID]);
                $likesCount--;
            }//if
            else{
                array_push($likes,$user->ID);
                $likesCount++;
            }//else

            if(!$comment)
                update_post_meta($postID,'likesCount',$likesCount);
            else
                update_comment_meta($postID,'likesCount',$likesCount);

            if(!$comment)
                update_post_meta($postID,'likes',$likes);
            else
                update_comment_meta($postID,'likes',$likes);

            $likesResult = $likesCount;

        }//if
        else{
            if(!$comment)
                update_post_meta($postID,'likes',array($user->ID));
            else
                update_comment_meta($postID,'likes',array($user->ID));

            if(!$comment)
                update_post_meta($postID,'likesCount',1);
            else
                update_comment_meta($postID,'likesCount',1);

            $likesResult = 1;

        }//else

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => array(
                    'status' => 'success',
                    'likes' => $likesResult
                )
            )
        );

    }// addLike

    public static function addComment(){

        $name = trim(filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING));
        $message =trim(filter_input(INPUT_POST,'message',FILTER_SANITIZE_STRING));
        $postID = trim(filter_input(INPUT_POST,'id',FILTER_SANITIZE_STRING));

        if(empty($name) || empty($message) || empty($postID)){

            self::echoDataWithHeader(
                array(
                    'header' => 'json',
                    'fields' => array(
                        'type' => 'error',
                        'message' => 'Не хватает параметров при запросе!'
                    )
                )
            );

        }//if

        $commentdata = array(
            'comment_post_ID'      => $postID,
            'comment_author'       => $name,
            'comment_author_email' => '',
            'comment_author_url'   => '',
            'comment_content'      => $message,
            'comment_type'         => '',
            'comment_parent'       => 0,
            'user_ID'              => 0,
        );

        // добавляем данные в Базу Данных
        $commentId = wp_new_comment( $commentdata );

        $result = array();

        if($commentId == false){
            $result['type'] = 'error';
            $result['message'] = 'Не удалось добавить комментарий';
        }//if
        else{
            $result['type'] = 'success';
        }//else

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $result
            )
        );
    }

    public static function updateUser(){

        $result = array();

        $userLogin = filter_input(INPUT_POST,'login',FILTER_SANITIZE_STRING);

        $user = get_user_by('login',$userLogin);

        if( is_wp_error($user) ){

            $result['status'] = 'error';
            $result['message'] = 'Пользователь не найден';

            self::echoDataWithHeader(array(
                'header' => 'json',
                'fields' => $result
            ));


        }//if

        $userFirstName = filter_input(INPUT_POST,'firstName',FILTER_SANITIZE_STRING);
        $userLastName = filter_input(INPUT_POST,'lastName',FILTER_SANITIZE_STRING);
        $userPhone = filter_input(INPUT_POST,'phone',FILTER_SANITIZE_STRING);
        $userStatus = filter_input(INPUT_POST,'status',FILTER_SANITIZE_STRING);
        $userWebSite = filter_input(INPUT_POST,'webSite',FILTER_SANITIZE_STRING);

        $userBirthday = filter_input(INPUT_POST,'birthday',FILTER_SANITIZE_STRING);

        $userCity= filter_input(INPUT_POST,'city',FILTER_SANITIZE_STRING);

        $userFStatus= filter_input(INPUT_POST,'fStatus',FILTER_SANITIZE_STRING);

        $userSkype= filter_input(INPUT_POST,'skype',FILTER_SANITIZE_STRING);

        $userJob= filter_input(INPUT_POST,'job',FILTER_SANITIZE_STRING);

        if(! empty($userFirstName)){
            update_user_meta($user->ID,'firstName',$userFirstName);
            $result['firstName'] = $userFirstName;
        }//if

        if(! empty($userLastName)){
            update_user_meta($user->ID,'lastName',$userLastName);
            $result['lastName'] = $userLastName;
        }//if

        if(! empty($userPhone)){
            update_user_meta($user->ID,'userPhone',$userPhone);
            $result['phone'] = $userPhone;
        }//if

        if(! empty($userStatus)){
            update_user_meta($user->ID,'userStatus',$userStatus);
            $result['status'] = $userStatus;
        }//if

        if(! empty($userWebSite)){
            update_user_meta($user->ID,'userWebSite',$userWebSite);
            $result['webSite'] = $userWebSite;
        }//if
        //$result['user'] = $user;

        if(! empty($userBirthday)){
            update_user_meta($user->ID,'birthday',$userBirthday);
            $result['birthday'] = $userBirthday;
        }//if

        if(! empty($userCity)){
            update_user_meta($user->ID,'city',$userCity);
            $result['userCity'] = $userCity;
        }//if

        if(! empty($userFStatus)){
            update_user_meta($user->ID,'fStatus',$userFStatus);
            $result['fStatus'] = $userFStatus;
        }//if

        if(! empty($userSkype)){
            update_user_meta($user->ID,'skype',$userSkype);
            $result['skype'] = $userSkype;
        }//if

        if(! empty($userJob)){
            update_user_meta($user->ID,'job',$userJob);
            $result['job'] = $userJob;
        }//if


        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }//updateUser

    public static function sendMail($userEmail,$userVefifyKey){

//        $userEmail     = $_GET['mail'];
//        $userVefifyKey = $_GET['key'];

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
        $headers .= "From: 3dAnadyr <admin@3danadyr.ru>\r\n"; // от кого письмо
        $subject = 'Регистрация пользователя';
        $message = "Для подтверждения перейдите по ссылке: <a href='http://3danadyr.ru/verification/$userVefifyKey'>Подтвердить</a>";
        $message .= "<br>";
        $message .= "Спасибо за регистрацию!";

        $result = mail($userEmail,$subject,$message,$headers);

//        require_once "SendMailSmtpClass.php";
//
//        $mailSMTP = new SendMailSmtpClass('admin@3danadyr.ru', 'Wolfall97','smtp.beget.com','admin',2525);
//

//

//
//        $result =  $mailSMTP->send($userEmail, $subject, $message, $headers); // отправляем письмо

        // print_r($result);

        return $result;

    }

    public static function getOptions(){

        $options = array('address','officeNumber');
        $optionValue = array();

        foreach($options as $singleOption){
            $option = get_option($singleOption);

            $optionValue[$singleOption] = $option;

        }//foreach

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $optionValue
            )
        );


    }//getOptions

    public static function register(){


        $user_name = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);
        $user_email = filter_input(INPUT_POST, 'email',FILTER_SANITIZE_STRING);
        $user_firstName = filter_input(INPUT_POST, 'firstName',FILTER_SANITIZE_STRING);
        $user_lastName = filter_input(INPUT_POST, 'lastName',FILTER_SANITIZE_STRING);


        $password = filter_input(INPUT_POST, 'password',FILTER_SANITIZE_STRING);

        $result = array();

        $user_id = username_exists( $user_name );

        if ( !$user_id and email_exists($user_email) == false ) {

            $user_id = wp_create_user( $user_name, $password, $user_email );

            if(! is_wp_error( $user_id )){

                $result['message'] = 'register!';
                $result['type'] = 'success';
                $verification = md5($user_id + 'register');

                update_user_meta($user_id,'verify_key',$verification);
                update_user_meta($user_id,'verify','error');
                update_user_meta($user_id,'friends',array());

                wp_update_user(array(

                    'ID'       =>   $user_id,
                    'first_name'    =>   $user_firstName,
                    'last_name'     =>   $user_lastName,
                    'user_login'    =>   $user_name

                ));

                $result['sendResult'] = self::sendMail($user_email,$verification);

            }//if
            else{
                $result['message'] = 'user not created';
                $result['type'] = 'error';
                $result['errorMessage'] = $user_id->get_error_message();

            }//else

        } else {
            $result['message'] = 'К сожалению данный email/login уже используется ';
            $result['type'] = 'error';
        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));
    }//register

    public static function makeUser($user){

        $userResult = new stdClass();

        $userResult->firstName = $user->first_name;
        $userResult->lastName = $user->last_name;
        $userResult->login = $user->user_login;
        $userResult->email = $user->user_email;
        $userResult->accessToken = md5($user->ID + time());
        update_user_meta($user->ID, 'token' ,$userResult->accessToken );
        $userResult->phone = get_user_meta($user->ID,'userPhone',true);
        $userResult->status = get_user_meta($user->ID,'userStatus',true);
        $userResult->webSite = get_user_meta($user->ID,'userWebSite',true);
        $userResult->image = get_user_meta($user->ID, 'avatar', true);
        $userResult->environment = get_user_meta($user->ID, 'environment', true);
        $userResult->subscribersCount = intval(get_user_meta($user->ID, 'subscribersCount', true));
        $userResult->mySubscribes = intval(get_user_meta($user->ID, 'mySubscribes', true));
        $friends = get_user_meta($user->ID, 'friends', true);

        $userResult->postsCount = count_user_posts($user->ID,'anews');

        $userResult->phone = $userResult->phone ? $userResult->phone : '';
        $userResult->status = $userResult->status ? $userResult->status : 'статус не установлен';
        $userResult->webSite = $userResult->webSite ? $userResult->webSite : '';
        $userResult->image = $userResult->image ? $userResult->image : '';
        $userResult->environment = $userResult->environment ? $userResult->environment : 'img/user/home.jpg';
        $userResult->friends = empty($friends) ? array() : $userResult->friends;
        $userResult->friendsAccounts = array();

        if(!empty($userResult->friends)){

            $userResult->friends = array_slice($userResult->friends,0,10);

            foreach($userResult->friends as $friend){

                $fiendObject = get_user_by("ID",$friend);
                $toAddUser = new stdClass();
                $toAddUser->firstName = $fiendObject->first_name;
                $toAddUser->lastName = $fiendObject->last_name;
                $toAddUser->image = get_user_meta($friend, 'avatar', true);
                $userResult->friendsAccounts[] = $toAddUser;

            }

        }

        return $userResult;

    }

    public static function authorize(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password',FILTER_SANITIZE_STRING);
        $result = array();

        if(empty($login) || empty($password)){

            $result['status'] = 'error';
            $result['message'] = 'Пользователь не найден! Данные заполнены неверно!';

            self::echoDataWithHeader(array(
                'header' => 'json',
                'fields' => $result
            ));

        }

        $user = wp_signon(array(

            'user_login'    =>$login,
            'user_password' => $password,

        ),false);

        if( is_wp_error($user) ){

            $result['status'] = 'error';
            $result['message'] = 'Пользователь не найден!';

        }//if
        else{

            $verification = get_user_meta($user->ID,'verify',true);
            if($verification != 'true'){

                $result['status'] = 'error';
                $result['message'] = 'Пользователь, вы не прошли верификацию! Пожалуйста, проверьте указанный вами email';

            }//if
            else{

                $authroziedUser = self::makeUser($user);


                $result['status'] = 'success';
                $result['user'] = $authroziedUser;
                $result['login'] = $login;
                //$result['password'] = $password;

            }//else

        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }//authorize

    public static function isHaveAccess($needEcho = null){

        $token = filter_input(INPUT_POST, 'accessToken',FILTER_SANITIZE_STRING);
        $result = array();

        global $wpdb;

        $res = $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} where `meta_key` = 'token' AND `meta_value` = '$token'");


        if(count($res) > 0){
            $result['status'] = 'success';

        }//if
        else{
            $result['status'] = 'error';

        }//else

        if($needEcho != null){
            return $result;
        }

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));


    }

    public static function verifyUser(){

        $code = filter_input(INPUT_POST, 'code' ,FILTER_SANITIZE_STRING);
        $result = array();

        global $wpdb;

        $verifyResult = $wpdb->get_results( "SELECT * FROM {$wpdb->usermeta} WHERE `meta_key` = 'verify_key' AND `meta_value` = '$code'" );

        if(count($verifyResult) != 0){
            update_user_meta($verifyResult[0]->user_id,'verify','true');
            $result['status'] = 'success';

        }//if
        else{
            $result['status'] = 'error';
            $result['$verifyResult'] = $verifyResult;
            $result['$key'] = $code;
        }//else


        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }//authorize

    public static function adminAuthorize(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password',FILTER_SANITIZE_STRING);
        $result = array();

        $user = wp_signon(
            array(
                'user_login' => $login,
                'user_password' => $password
            ));

        if ( is_wp_error($user) ){
            $result['status']='error';
            $result['message']='auth error';
        }
        else{
            $result['status']='success';
        }

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }

    public static function changeUserAvatar(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);

        $result = array();

        $uplDir = wp_upload_dir();
        $user = get_user_by('login', $login);
        $oldfile = get_user_meta($user->ID,'avatarPath',true);
        unlink($oldfile);
        $uploadDir = $uplDir['basedir'] . DIRECTORY_SEPARATOR . "users/";
        $date = date("Y-m-d H:i:s");

        if(isset($_POST['android'])){

//            ini_set("display_errors",1);
//            error_reporting(E_ALL);

            $filePath = $uploadDir . "avatar_{$login}_{$date}.jpeg";

            $image = $_POST["image"];

            $decoded = base64_decode($image);

            unlink($filePath);

            $putContents = file_put_contents($filePath,$decoded );

            if($putContents != false ){

                $avatar = $uplDir['baseurl'] . "/users/avatar_{$login}_{$date}.jpeg";
                update_user_meta($user->ID, 'avatar', $avatar);
                update_user_meta($user->ID, 'avatarPath', $filePath);

                $result['type'] = 'success';
                $result['imgSource'] = $avatar;
                $result['$filePath'] = $filePath;
                $result['$putContents'] = $putContents;

                $si = new SimpleImage();

                $si->load($filePath);
                $imWidthg = $si->getWidth();
                $imgHeight = $si->getHeight();

                if($imgHeight > $imWidthg){

                    $si->resizeToWidth(150);

                }
                else
                    $si->resizeToHeight(150);

                $si->cutFromCenter(150 ,150);

                $si->save($filePath);

            }//if
            else{
                $result['type'] = 'error';
                $result['message'] = array('fPath' => $filePath , 'files' => $_POST);
            }//else

            self::echoDataWithHeader(array(
                'header' => 'json',
                'fields' => $result
            ));

        }//if

        $extention = end(explode('.',$_FILES['userfile']['name']));
        $extention = strtolower($extention);



        $filePath = $uploadDir . "avatar_{$login}_{$date}.{$extention}";

        $avatar = $uplDir['baseurl'] . "/users/avatar_{$login}_{$date}.{$extention}";

        $result = array();

        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filePath )) {

            $result['type'] = 'success';
            $result['imgSource'] = $avatar;
            $result['$filePath'] = $filePath;


            update_user_meta($user->ID, 'avatar', $avatar);
            update_user_meta($user->ID, 'avatarPath', $filePath);

            $si = new SimpleImage();

            $si->load($filePath);
            $imWidthg = $si->getWidth();
            $imgHeight = $si->getHeight();

            if($imgHeight > $imWidthg){

                $si->resizeToWidth(150);

            }
            else
                $si->resizeToHeight(150);

            $si->cutFromCenter(150 ,150);

            $si->save($filePath);
        }
        else{
            $result['type'] = 'error';
            $result['message'] = array('fPath' => $filePath , 'files' => $_FILES, 'tmp_name' => $_FILES['userfile']['tmp_name']);
        }//else

//        $result['folder'] = $uploadDir;
//        $result['file'] = $_FILES['userfile']['tmp_name'];
//        $result['name'] = $_FILES['userfile']['name'];



        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }

    public static function changeUserEnvironment(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);

        $uplDir = wp_upload_dir();
        $user = get_user_by('login', $login);
        $oldfile = get_user_meta($user->ID,'environmentPath',true);
        unlink($oldfile);

        $uploadDir = $uplDir['basedir'] . DIRECTORY_SEPARATOR . "users/environment/";
        $extention = end(explode('.',$_FILES['imageEnvironment']['name']));
        $extention = strtolower($extention);

        $date = date("Y-m-d H:i:s");

        $filePath = $uploadDir . "environment_{$login}_{$date}.{$extention}";

        $avatar = $uplDir['baseurl'] . "/users/environment/environment_{$login}_{$date}.{$extention}";

        $result = array();

        if (move_uploaded_file($_FILES['imageEnvironment']['tmp_name'], $filePath )) {

            $result['type'] = 'success';
            $result['imgSource'] = $avatar;
            $result['$filePath'] = $filePath;


            update_user_meta($user->ID, 'environment', $avatar);
            update_user_meta($user->ID, 'environmentPath', $filePath);

            $si = new SimpleImage();

            $si->load($filePath);
            $si->resizeToWidth(1300);

            $si->save($filePath);
        }
        else{
            $result['type'] = 'error';
            $result['message'] = array('fPath' => $filePath , 'files' => $_FILES, 'tmp_name' => $_FILES['userfile']['tmp_name']);
        }//else

//        $result['folder'] = $uploadDir;
//        $result['file'] = $_FILES['userfile']['tmp_name'];
//        $result['name'] = $_FILES['userfile']['name'];



        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }

    public static function getUserByLogin(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);
        $owner = filter_input(INPUT_POST, 'owner',FILTER_SANITIZE_STRING);

        $user = get_user_by('login', $login);
        $ownerObject = get_user_by('login', $owner);

        $result = array();
        $userResult = new stdClass();

        if( $user == false){

            $result['status'] = 'error';
            $result['message'] = 'Пользователь не найден!';

        }//if
        else{

            //$userResult->ID = $user->ID;
            $userResult->firstName = $user->first_name;
            $userResult->lastName = $user->last_name;
            $userResult->login = $user->user_login;
            $userResult->email = $user->user_email;
            $userResult->phone = get_user_meta($user->ID,'userPhone',true);
            $userResult->status = get_user_meta($user->ID,'userStatus',true);
            $userResult->webSite = get_user_meta($user->ID,'userWebSite',true);
            $userResult->image = get_user_meta($user->ID, 'avatar', true);
            $userResult->environment = get_user_meta($user->ID, 'environment', true);
            $userResult->subscribersCount = intval(get_user_meta($user->ID, 'subscribersCount', true));
            $userResult->mySubscribes = intval(get_user_meta($user->ID, 'mySubscribes', true));

            $userResult->postsCount = count_user_posts($user->ID,'anews');

            $userResult->phone = $userResult->phone ? $userResult->phone : '';
            $userResult->status = $userResult->status ? $userResult->status : 'статус не установлен';
            $userResult->webSite = $userResult->webSite ? $userResult->webSite : '';
            $userResult->image = $userResult->image ? $userResult->image : '';
            $userResult->environment = $userResult->environment ? $userResult->environment : 'img/user/home.jpg';

            $userResult->skype = get_user_meta($ownerObject->ID,'skype',true);
            $userResult->birthday = get_user_meta($ownerObject->ID,'birthday',true);
            $userResult->city = get_user_meta($ownerObject->ID,'city',true);
            $userResult->fStatus = get_user_meta($ownerObject->ID,'fStatus',true);
            $userResult->job = get_user_meta($ownerObject->ID,'job',true);

            $userResult->skype = $userResult->skype != false ? $userResult->skype : 'не указан';
            $userResult->birthday = $userResult->birthday != false ? $userResult->birthday : 'не указан';
            $userResult->city = $userResult->city != false ? $userResult->city : 'не указан';
            $userResult->fStatus = $userResult->fStatus != false ? $userResult->fStatus : 'не указан';
            $userResult->job = $userResult->job != false ? $userResult->job : 'не указано';

            $friendsOwner = get_user_meta($ownerObject->ID, 'friends', true);
            $friendsUser = get_user_meta($user->ID, 'friends', true);

            $friendsRequestsUser = get_user_meta($user->ID,'friendsRequests',true);
            $friendsRequestsOwner = get_user_meta($ownerObject->ID,'friendsRequests',true);

            if($user->ID == $ownerObject->ID){
                $userResult->isFriend = 'self';
            }
            else if(in_array($user->ID,$friendsRequestsOwner) || in_array($ownerObject->ID,$friendsRequestsUser)){
                $userResult->isFriend = 'wait';
            }
            else if(in_array($user->ID,$friendsOwner) || in_array($ownerObject->ID,$friendsUser)){
                $userResult->isFriend = 'friend';
            }else{
                $userResult->isFriend = 'not';
            }

            $result['status'] = 'success';
            $result['user'] = $userResult;

            $result['compareParams'] = array(
                'ID' => $user->ID ,
                'login'=>$user->user_login ,

                'oID' => $ownerObject->ID,
                'oLogin' => $ownerObject->user_login
            );


        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }//getUserByLogin

    public static function getUserWall(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);
        $limit = intval(filter_input(INPUT_POST, 'limit',FILTER_SANITIZE_STRING));
        $offset = intval(filter_input(INPUT_POST, 'offset',FILTER_SANITIZE_STRING));
        $commentsCount = intval(filter_input(INPUT_POST, 'commentsCount',FILTER_SANITIZE_STRING));

        $user = get_user_by('login', $login);

        $result = array();

        if( $user == false){

            $result['status'] = 'error';
            $result['message'] = 'Пользователь не найден!';

        }//if
        else{

            $wall = self::getAnadyrPosts(array(
                'numberposts' => $limit,
                'offset' => $offset,
                'author' => $user->ID,
                'type' => $user->roles[0] == 'administrator' ? 'anews' : 'unews',
                'commentsCount' => $commentsCount
            ));


            foreach ($wall as $sWall){



            }

            $result['status'] = 'success';
            $result['userWall'] = $wall;

        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }//getUserByLogin

    public static  function AddToFriends(){

        //Кто добавляет
        $whoAdd = filter_input(INPUT_POST, 'whoAdd',FILTER_SANITIZE_STRING);

        //Кого добавляет
        $whomAdd = filter_input(INPUT_POST, 'whomAdd',FILTER_SANITIZE_STRING);

        $whoUser = get_user_by('login', $whoAdd);
        $whomUser = get_user_by('login', $whomAdd);

        $friendsRequests = get_user_meta($whomUser->ID,'friendsRequests',true);
        $resultArray = array();

        $result = array();

        if($friendsRequests == false || !in_array($whoUser->ID,$friendsRequests)) {

            if (!is_array($friendsRequests)){
                $resultArray[] = $whoUser->ID;
                update_user_meta($whomUser->ID,'friendsRequests',$resultArray);
            }
            else {
                array_push($friendsRequests, $whoUser->ID);
                update_user_meta($whomUser->ID,'friendsRequests',$friendsRequests);
            }//else

            $result['status'] = 'success';

        }//if
        else{
            $result['status'] = 'error';
            $result['params'] = array( 'fr' => $friendsRequests , 'ra' => $resultArray);
        }

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));


    }

    public static function GetMyFriendRequests(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);

        $user = get_user_by('login',$login);
        $result = array();

        if($user != false){

            $friendsRequests = get_user_meta($user->ID,'friendsRequests',true);

            if($friendsRequests != false){

                $result['users'] = array();

                foreach ($friendsRequests as $friend){
                    $sUser = get_user_by('ID',$friend);
                    $result['users'][] = self::makeUser($sUser);
                }//foreach

                $result['status'] = 'success';


            }//if
            else{
                $result['status'] = 'empty';
            }//else

        }//if
        else{
            $result['status'] = 'error';
            $result['message'] = 'user not found';
        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));


    }

    public static function GetCountFriendRequests(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);

        $user = get_user_by('login',$login);
        $result = array();

        if($user != false){

            $friendsRequests = get_user_meta($user->ID,'friendsRequests',true);

            if($friendsRequests != false){

                $result['count'] = count($friendsRequests);
                $result['status'] = 'success';


            }//if
            else{
                $result['status'] = 'empty';
            }//else

        }//if
        else{
            $result['status'] = 'error';
            $result['message'] = 'user not found';
        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));


    }

    public static  function ConfirmFriendRequest(){

        $whomAdd = filter_input(INPUT_POST, 'whomAdd',FILTER_SANITIZE_STRING);
        $whoAdd = filter_input(INPUT_POST, 'whoAdd',FILTER_SANITIZE_STRING);

        $whoUser = get_user_by('login', $whoAdd);//Кто добавляет
        $whomUser = get_user_by('login', $whomAdd);//Кого добавляет

        $result = array();

        if($whoUser != false && $whomUser != false){

            $friendsRequests = get_user_meta($whoUser->ID,'friendsRequests',true);

            $rIndex = -1;

            foreach($friendsRequests as $fIndiex=>$value){

                if($whomUser->ID == $value){
                    $rIndex = $fIndiex;
                    break;
                }

            }//foreach

            unset($friendsRequests[$rIndex]);

            update_user_meta($whoUser->ID,'friendsRequests',$friendsRequests);

            $friends = get_user_meta($whoUser->ID,'friends',true);
            $friends = !is_array($friends) ? array() : $friends;

            array_push($friends,$whomUser->ID);

            update_user_meta($whoUser->ID,'friends',$friends);

            $friends = get_user_meta($whomUser->ID,'friends',true);
            $friends = !is_array($friends) ? array() : $friends;

            array_push($friends,$whoUser->ID);
            update_user_meta($whomUser->ID,'friends',$friends);

            $result['status'] = 'success';
            $result['fRequests'] = $friendsRequests;

        }//if
        else{
            $result['status'] = 'error';
            $result['message'] = 'Не достаточно параметров';

        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }

    public static function GetUserFriends(){

        $login = filter_input(INPUT_POST, 'login',FILTER_SANITIZE_STRING);

        $user = get_user_by('login', $login);//Кто добавляет

        $friends = get_user_meta($user->ID, 'friends', true);

        $friends = array_slice($friends,0,10);
        $result = array();

        foreach($friends as $friend){

            $fiendObject = get_user_by("ID",$friend);
            $toAddUser = new stdClass();
            $toAddUser->firstName = $fiendObject->first_name;
            $toAddUser->lastName = $fiendObject->last_name;
            $toAddUser->login = $fiendObject->user_login;
            $toAddUser->image = get_user_meta($friend, 'avatar', true);
            $result[] = $toAddUser;

        }//foreach

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));

    }

    public static function AddNews(){


        $result = array();

        $owner = trim(filter_input(INPUT_POST, 'owner',FILTER_SANITIZE_STRING));

        $newsContent = filter_input(INPUT_POST, 'content',FILTER_SANITIZE_STRING);

        if(empty($owner)){
            $result['status'] = 'error';
            $result['message'] = 'Пользовател не определен!';
        }//if

        if(empty($newsContent)){
            $result['status'] = 'error';
            $result['message'] = 'Описание новости пусто!';
        }//else

        if(isset($result['status'])){
            self::echoDataWithHeader(
                array(
                    'header' => 'json',
                    'fields' => $result
                )
            );
        }

        $ownerObject = get_user_by('login',$owner);

        if($ownerObject == false){

            $result['status'] = 'error';
            $result['message'] = 'Пользовател не найден!';

        }//if
        else{


            $post_data = array(
                'post_title'    => wp_strip_all_tags($ownerObject->user_login + " : " + date("now")),
                'post_content'  => $newsContent,
                'post_status'   => 'publish',
                'post_author'   => $ownerObject->ID,
                'post_type' => $ownerObject->roles[0] == 'administrator' ? 'anews' : 'unews'
            );

            // Вставляем запись в базу данных
            $post_id = wp_insert_post( $post_data ,true);

           // update_post_meta($post_id,'anonce',$newsAnonce);

            if(count($_FILES) != 0){

                $uplDir = wp_upload_dir();
                $uploadDir = $uplDir['basedir'] . DIRECTORY_SEPARATOR . "news-photo/$owner";
                $date = date("Y-m-d H:i:s");

                if(!is_dir($uploadDir)){
                    mkdir($uploadDir);
                }
                $uploadPath = "$uploadDir/$date";
                $middleImage = "/$uploadPath/middle";
                $smallImage = "/$uploadPath/small";

                mkdir($uploadPath);
                mkdir("/$uploadPath/full");
                mkdir($middleImage);
                mkdir($smallImage);

                $newPhotos = array();
                $index = 1;

                foreach ($_FILES as $file){

                    $extention = end(explode('.',$file['name']));
                    $extention = strtolower($extention);
                    $filePath =  "$uploadPath/$index.$extention";

                    if(move_uploaded_file($file['tmp_name'],$filePath)){

                        $si = new SimpleImage();

                        $si->load($filePath);

                        $si->resizeToWidth(738);
                        $si->resizeToHeight(554);

                        $si->save("$middleImage/$index.$extention");

                        $si->resizeToWidth(369);
                        $si->resizeToHeight(277);

                        $si->save("$smallImage/$index.$extention");

                        $newPhotos[] = array(
                            'path' => $filePath,
                            'url' => $uplDir['baseurl'] . "/news-photo/{$owner}/$date/$index.$extention",
                            'middlePath' => $middleImage,
                            'middleUrl' => $uplDir['baseurl'] . "/news-photo/{$owner}/$date/middle/$index.$extention",
                            'smallPath' => $smallImage,
                            'smallUrl' => $uplDir['baseurl'] . "/news-photo/{$owner}/$date/small/$index.$extention"
                        );
                    }//if

                    $index++;

                }//foreach

                update_post_meta($post_id,'newsPhotos',$newPhotos);

//                $result['newsPhotos'] = $newPhotos;
                //$result['FILES'] = $_FILES;
            }//if

            $result['status'] = 'success';
            $result['message'] = 'Новость добавлена!';
            $result['post'] = self::getAnadyrPosts(array(
                'type'=> 'unews',
                'ID' => $post_id
            ));

        }//else


        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $result
            )
        );

    }//AddNews

    public static function getDialogsCount(){

        global $wpdb;

        $me = filter_input(INPUT_POST, 'me',FILTER_SANITIZE_STRING);//alexey

        $result = array();

        $meObject = get_user_by('login', $me);
        $access = self::isHaveAccess(1);

        if(!$meObject || $access['status'] != 'success'){
            $result['status'] = 'error';
            $result['message'] = 'access denied';
        }//if
        else{

            $dialogItems = $wpdb->get_results("SELECT COUNT(DISTINCT `FromUser`) as `dCount` FROM `LastMessage` WHERE `ToUser` = {$meObject->ID} OR `FromUser` = {$meObject->ID}");
            $result['status'] = 'success';

            if(count($dialogItems) > 0){
                $result['dCount'] = $dialogItems[0]->dCount;
            }//if

        }//else

        self::echoDataWithHeader(array(
            'header' => 'json',
            'fields' => $result
        ));


    }//getDialogsCount

    //MESSAGE SECTION

    //Получить последнее сообщения от кого?
//    public static function getLastMessageId($fromID, $whomID){
//
//        $fromUserID = intval($fromID);
//        $ToUserID = intval($whomID);
//
//        global $wpdb;
//
//        $result = $wpdb->get_results("SELECT `messageID` FROM `LastMessage` WHERE (`FromUser` = $fromUserID AND `ToUser` = $ToUserID)");
//
//        return count($result) > 0 ? $result[0]->messageID : -1;
//
//    }//
//
//    public static function getMessageByID($mID){
//
//        $mID = intval($mID);
//        global $wpdb;
//
//        $result = $wpdb->get_results("SELECT * FROM `UserMessages` WHERE `mID` = $mID ");
//
//        return count($result) > 0 ? $result[0] : -1;
//
//
//    }
//
//    public static function getMessagesStartWith($fromID, $whomID, $limit, $offset, $lastMessageID = null){
//
//        global $wpdb;
//        $fromID = intval($fromID);
//        $whomID = intval($whomID);
//        $limit = intval($limit);
//        $offset = intval($offset);
//        if($lastMessageID == null){
//            $result = $wpdb->get_results("SELECT DISTINCT `mID` FROM `UserMessages` WHERE (`FromUserID` = $fromID and `ToUserID` = $whomID) or (`ToUserID` = $fromID and `FromUserID` = $whomID) LIMIT $offset,$limit ");
//        }
//        else{
//            $lastMessageID = intval($lastMessageID);
//            $result = $wpdb->get_results("SELECT DISTINCT `mID` FROM `UserMessages` WHERE ((`FromUserID` = $fromID and `ToUserID` = $whomID) or (`ToUserID` = $fromID and `FromUserID` = $whomID)) and `mID` >= $lastMessageID LIMIT $offset,$limit ");
//
//        }
//
//        $ids = array();
//
//        foreach ( $result as $mIdObject ){
//
//            $ids[] = $mIdObject->mID;
//
//        }//foreach
//
//        $mIds = implode(',',$ids);
//        $mIds = rtrim($mIds,',');
//
//        $result = $wpdb->get_results("SELECT * FROM `UserMessages` WHERE `mID` in ($mIds) LIMIT $offset,$limit ");
//
//
//
//        return $result;
//
//    }//getMessagesStartWith
//
//    public static function getMessages($me,$some,$echo=null){
//
//        $meObject = get_user_by('login', $me);
//        $someObject = get_user_by('login', $some);
//        $result = array();
//        global $wpdb;
//
//        if(!$meObject){
//
//            return array('status' => 'empty','message' => 'not set user');
//
//        }//if
//
//        //Сообщения, отправленные мне
//        $lm = self::getLastMessageId($someObject->ID,$meObject->ID);
//        $forMeLastMessage = $lm;
//
//        //Если мне не отправляли сообщениния
//        if($forMeLastMessage == -1){
//            //Получаем мои сообщения, адресованные собеседнику:
//            $forHimLastMessage = self::getLastMessageId($meObject->ID,$someObject->ID);
//
//            //Если и от меня не было сообщений - диалог пуст
//            if($forHimLastMessage == -1){
//
//                $result['status'] = 'empty';
//
//            }//if
////            else{//Есть старый диалог
////                //Достаю сообщения
////
////                $messages = self::getMessagesStartWith($meObject->ID,$someObject->ID,20,0,  $forHimLastMessage);
////                $result['process'] = 'Есть старый диалог';
////            }//else
//
//        }//if
//        else{
//            //Мне отправили сообщения
//            $messages = self::getMessagesStartWith($someObject->ID,$meObject->ID,20,0,$forMeLastMessage);
//            $result['process'] = 'Есть новые сообщения!';
//            if(count($messages) > 0){
//
//                $upResult = $wpdb->update(
//                    'LastMessage',
//                    array(
//                        'FromUser' => $someObject->ID,
//                        'ToUser' => $meObject->ID,
//                        'messageID' => -1
//                    ),
//                    array(
//                        'FromUser' => $someObject->ID,
//                        'ToUser' => $meObject->ID,
//                    ),
//                    array('%d','%d','%d')
//                );
//            }
//
//        }//else
//
//        $result['messages'] = $messages;
//        $result['$forHimLastMessage'] = $forHimLastMessage;
//        $result['$forMeLastMessage'] = $forMeLastMessage;
//
//        if($echo == null)
//            return $result;
//        else{
//            self::echoDataWithHeader(
//                array(
//                    'header' => 'json',
//                    'fields' => $result
//                )
//            );
//        }//else
//
//    }//getMessages

    public static function getLastMessageId($fromID, $whomID){

        $fromUserID = intval($fromID);
        $ToUserID = intval($whomID);

        global $wpdb;

        $result = $wpdb->get_results("SELECT `messageID` FROM `LastMessage` WHERE (`FromUser` = $fromUserID AND `ToUser` = $ToUserID)");

        return count($result) > 0 ? $result[0]->messageID : -1;

    }//

    public static function getMessageByID($mID){

        $mID = intval($mID);
        global $wpdb;

        $result = $wpdb->get_results("SELECT * FROM `UserMessages` WHERE `mID` = $mID ");

        return count($result) > 0 ? $result[0] : -1;


    }

    public static function getMessagesStartWith($fromID, $whomID, $limit, $offset, $lastMessageID = null){

        global $wpdb;
        $fromID = intval($fromID);
        $whomID = intval($whomID);
        $limit = intval($limit);
        $offset = intval($offset);
        if($lastMessageID == null){
            $result = $wpdb->get_results("SELECT DISTINCT `mID` FROM `UserMessages` WHERE (`FromUserID` = $fromID and `ToUserID` = $whomID) or (`ToUserID` = $fromID and `FromUserID` = $whomID) LIMIT $offset,$limit ");
        }
        else{
            $lastMessageID = intval($lastMessageID);
            $result = $wpdb->get_results("SELECT DISTINCT `mID` FROM `UserMessages` WHERE ((`FromUserID` = $fromID and `ToUserID` = $whomID) or (`ToUserID` = $fromID and `FromUserID` = $whomID)) and `mID` >= $lastMessageID LIMIT $offset,$limit ");

        }

        $ids = array();

        foreach ( $result as $mIdObject ){

            $ids[] = $mIdObject->mID;

        }//foreach

        $mIds = implode(',',$ids);
        $mIds = rtrim($mIds,',');

        $result = $wpdb->get_results("SELECT * FROM `UserMessages` WHERE `mID` in ($mIds) LIMIT $offset,$limit ");



        return $result;

    }//getMessagesStartWith

    public static function getMessages($me,$some,$echo=null){

        $meObject = get_user_by('login', $me);
        $someObject = get_user_by('login', $some);
        $result = array();
        global $wpdb;

        if(!$meObject){

            return array('status' => 'empty','message' => 'not set user');

        }//if


        if(!$someObject){
            //Собеседник не установлен
            //Вынужден просмотреть всех своих друзей и найти новые сообщения

            $lastMessagesIds = array();

            $friends = get_user_meta($meObject->ID,'friends',true);

            if(count($friends) == 0){//Друзей нет => следовательно сообщений не может быть
                $messages = array();
            }//if
            else{
                $messages = array();

                //Иначе идем по друзьям и достаем новые сообщения
                foreach ($friends as $friend){

                    $lm = intval(self::getLastMessageId($friend,$meObject->ID));

                    if($lm != -1){
                        //Есть сообщение от друга
                        $friendMessages = self::getMessagesStartWith($friend,$meObject->ID,20,0,$lm);
                        if(count($friendMessages) > 0){

                            $upResult = $wpdb->update(
                                'LastMessage',
                                array(
                                    'FromUser' => $friend,
                                    'ToUser' => $meObject->ID,
                                    'messageID' => -1
                                ),
                                array(
                                    'FromUser' => $friend,
                                    'ToUser' => $meObject->ID,
                                ),
                                array('%d','%d','%d')
                            );

                            $fiendObject = get_user_by("ID",$friend);
                            $toAddUser = new stdClass();
                            $toAddUser->login = $fiendObject->user_login;
                            $toAddUser->image = get_user_meta($friend, 'avatar', true);
                            $messages[] = array('message' => $friendMessages, 'user' => $toAddUser);

                        }//if


                    }//if

                }//foreach

            }//else

        }//if
        else{
            //Сообщения, отправленные мне (текущий диалог с пользователем)
            $lm = self::getLastMessageId($someObject->ID,$meObject->ID);
            $forMeLastMessage = $lm;

            //Если мне не отправляли сообщениния
            if($forMeLastMessage == -1){
                //Получаем мои сообщения, адресованные собеседнику:
                $forHimLastMessage = self::getLastMessageId($meObject->ID,$someObject->ID);

                //Если и от меня не было сообщений - диалог пуст
                if($forHimLastMessage == -1){

                    $result['status'] = 'empty';

                }//if
//            else{//Есть старый диалог
//                //Достаю сообщения
//
//                $messages = self::getMessagesStartWith($meObject->ID,$someObject->ID,20,0,  $forHimLastMessage);
//                $result['process'] = 'Есть старый диалог';
//            }//else

            }//if
            else{
                //Мне отправили сообщения
                $messages = self::getMessagesStartWith($someObject->ID,$meObject->ID,20,0,$forMeLastMessage);
                $result['process'] = 'Есть новые сообщения!';
                if(count($messages) > 0){

                    $upResult = $wpdb->update(
                        'LastMessage',
                        array(
                            'FromUser' => $someObject->ID,
                            'ToUser' => $meObject->ID,
                            'messageID' => -1
                        ),
                        array(
                            'FromUser' => $someObject->ID,
                            'ToUser' => $meObject->ID,
                        ),
                        array('%d','%d','%d')
                    );
                }

            }//else

        }//else



        $result['messages'] = $messages;

        if($echo == null)
            return $result;
        else{
            self::echoDataWithHeader(
                array(
                    'header' => 'json',
                    'fields' => $result
                )
            );
        }//else

    }//getMessages

    public static function getDialog(){

        $token= filter_input(INPUT_POST, 'accessToken',FILTER_SANITIZE_STRING);
        $me = filter_input(INPUT_POST, 'me',FILTER_SANITIZE_STRING);//alexey
        $some = filter_input(INPUT_POST, 'some',FILTER_SANITIZE_STRING);//alexTest

        $meObject = get_user_by('login', $me);
        $someObject = get_user_by('login', $some);

        $result = array();
        $access = self::isHaveAccess(1);

        if( $access['status'] == 'success' ){
            $result['status'] = 'success';

            $dialog = self::getMessagesStartWith($meObject->ID,$someObject->ID,20,0);

            foreach($dialog as &$message){

                if($message->FromUserID == $meObject->ID){

                    $message->owner = 'me';


                }//if
                else{
                    $message->owner = 'who';
                }//else

                $message->name = $meObject->first_name;

            }//foreach

            $result['dialog'] = $dialog;

        }//if
        else{
            $result['status'] = 'error';
            $result['message'] = 'access denied';
        }//else

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $result
            )
        );

    }//getDialog

    public static function getDialogs(){

        $token= filter_input(INPUT_POST, 'accessToken',FILTER_SANITIZE_STRING);
        $me = filter_input(INPUT_POST, 'me',FILTER_SANITIZE_STRING);//alexey

        $meObject = get_user_by('login', $me);

        $result = array();
        $access = self::isHaveAccess(1);

        if( $access['status'] == 'success' && $meObject){
            $result['status'] = 'success';

            $dialogs = array();

            global $wpdb;

            $dialogItems = $wpdb->get_results("SELECT DISTINCT `FromUser` FROM `LastMessage` WHERE `ToUser` = {$meObject->ID} 
                                         UNION SELECT DISTINCT `ToUser` FROM `LastMessage` WHERE `FromUser` = {$meObject->ID}"
            );


            foreach($dialogItems as $user){

                $userItem = get_user_by("id",$user->FromUser);
                $userResult = new stdClass();

                $userResult->firstName = $userItem->first_name;
                $userResult->lastName = $userItem->last_name;
                $userResult->login = $userItem->user_login;
                $userResult->image = get_user_meta($userItem->ID, 'avatar', true);
                $userResult->image = $userResult->image ? $userResult->image : '';

                $dialogs[] = $userResult;

            }//foreach


            $result['dialogs'] = $dialogs;

        }//if
        else{
            $result['status'] = 'error';
            $result['message'] = 'access denied';
        }//else

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $result
            )
        );

    }//getDialogs


    public static function sendMessage(){

        $token= filter_input(INPUT_POST, 'accessToken',FILTER_SANITIZE_STRING);
        $from = filter_input(INPUT_POST, 'from',FILTER_SANITIZE_STRING);//alexey
        $to = filter_input(INPUT_POST, 'to',FILTER_SANITIZE_STRING);//alexTest

        $result = array();

        $access = self::isHaveAccess(1);

        if( $access['status'] == 'success' ){



            $fromUserMessage = get_user_by('login', $from);
            $toUserMessage = get_user_by('login', $to);

            if(!$fromUserMessage || !$toUserMessage){
                $result['status'] = 'error';
                $result['message'] = 'Получатель и отправитель не получены!';
            }//if
            else{

                global $wpdb;

                $message = filter_input(INPUT_POST, 'message',FILTER_SANITIZE_STRING);

                 $insResult = $wpdb->insert(
                    'UserMessages',
                    array(
                        'FromUserID'=>$fromUserMessage->ID,
                        'ToUserID' => $toUserMessage->ID,
                        'Message' => $message,
                        'time' => current_time( 'mysql' , 1 )
                     )
                 );

                if($insResult){//Если сообщение добавлено

                    $res = $wpdb->insert_id;//Полуаю id последнего сообщения

                    $upResult = $wpdb->update(
                        'LastMessage',
                        array(
                            'FromUser' => $fromUserMessage->ID,
                            'ToUser' => $toUserMessage->ID,
                            'messageID' => $res
                        ),
                        array(
                            'FromUser' => $fromUserMessage->ID,
                            'ToUser' => $toUserMessage->ID,
                        ),
                        array('%d','%d','%d')
                    );

                    if($upResult == 0){//Данных для обновления - нет

                        //Добавляем новые данные о последнем сообщении
                        $insResult = $wpdb->insert(
                            'LastMessage',
                            array(
                                'FromUser' => $fromUserMessage->ID,
                                'ToUser' => $toUserMessage->ID,
                                'messageID' => $res
                            ),
                            array('%d','%d','%s')
                        );

                        if($insResult){//Если сообщение добавлено
                            $result['status'] = 'success';
                            $result['message'] = self::getMessageByID($res);
                        }//if
                        else{
                            $result['status'] = 'error';
                            $result['message'] = $wpdb->last_error;
                        }//else

                    }//if
                    else if($upResult == false){
                        $result['status'] = 'error';
                        $result['message'] = $wpdb->last_error;
                    }//else if
                    else{
                        $result['status'] = 'success';
                        $result['message'] = self::getMessageByID($res);
                    }//else

                }//if
                else{
                    //Сообщение добавить не удалось
                    $result['status'] = 'error';
                    $result['message'] = $wpdb->last_error;
                }//else

            }//else

        }//if
        else{
            $result['status'] = 'error';
            $result['message'] = 'access denied';
        }//else

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $result
            )
        );





    }

    public static function observeMessages(){

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
        header('Access-Control-Allow-Headers: text/event-stream');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

        $id = -1;

        $me = $_REQUEST['me'];
        $some = $_REQUEST['some'];

        if( !self::isHaveAccess(1) ){
            $id = 0;
            $data = 0;
        }
        else{

            $data = self::getMessages($me,$some);

        }//else

        echo "id: $id" . PHP_EOL;
        echo "data: " .json_encode($data) . PHP_EOL;
        echo PHP_EOL;


    }

}