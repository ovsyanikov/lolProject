<?php

include 'Interface.api.php';
include 'ImageCorrector.php';

function compare($left , $right){

    return $left->position < $right->position ? -1 : 1;

}

class ajaxApi implements interfaceApi{
    
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
    
    public static $entity;
    
    public static function initializeEntity(Entity $entity){
        
        self::$entity = $entity;
        
    }
    
    public static function registerApiAction($action){
          
        add_action( "wp_ajax_$action", array('ajaxApi', $action));
        add_action( "wp_ajax_nopriv_$action", array('ajaxApi', $action));

        
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

        $posts = get_posts($postParams);
        
        return $posts;
        
    }
    
    public static function getProductKey(){
        
        return 'pKey';
        
    }

    public static function getPostWithParams($params = array()){
        
        $resultProducts = array();
        $myArray = array();
        $isEcho = true;
        
        //Если в параметре пустой массив 
        if(empty($params)){
            
            parse_str($_REQUEST[self::getProductKey()],$myArray );
            $params = $myArray;
            
        }//if
        else{
            
            $isEcho = false;
            
        }
        
        foreach (self::getPosts($params) as $postObj){
            
            if(isset($params['class'])){
                $entityData = new $params['class']($postObj);
            }//if
            else{
                $entityData = clone self::$entity->getEntityData($postObj);
            }//else
            
            $resultProducts[] = $entityData;
        }//foreach
        
        if($isEcho){
            
            self::echoDataWithHeader(
                array(
                    'header' => 'json',
                    'fields' => $resultProducts
                    )//array
            );

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

            $points[] = self::getPostWithParams(array(
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
            $categoryObject->parent = $category->term_id;
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

    public static function getComments(){

        $productId = filter_input(INPUT_POST,'id',FILTER_SANITIZE_STRING);
        $numberposts = filter_input(INPUT_POST,'number',FILTER_SANITIZE_STRING);
        $offset = filter_input(INPUT_POST,'offset',FILTER_SANITIZE_STRING);

        $comments = get_comments( array(
            'post_id' => $productId,
             'number' => $numberposts,
             'offset' => $offset

            )
        );

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $comments
            )
        );


    }

    public static function addComment(){

        $name = filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST,'message',FILTER_SANITIZE_STRING);
        $productId = filter_input(INPUT_POST,'id',FILTER_SANITIZE_STRING);

        $commentdata = array(
            'comment_post_ID'      => $productId,
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

        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $commentId
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

    public static function isHaveAccess(){

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

        $uplDir = wp_upload_dir();
        $user = get_user_by('login', $login);
        $oldfile = get_user_meta($user->ID,'avatarPath',true);
        unlink($oldfile);

        $uploadDir = $uplDir['basedir'] . DIRECTORY_SEPARATOR . "users/";
        $extention = end(explode('.',$_FILES['userfile']['name']));
        $extention = strtolower($extention);

        $date = date("Y-m-d H:i:s");

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

        $user = get_user_by('login', $login);

        $result = array();

        if( $user == false){

            $result['status'] = 'error';
            $result['message'] = 'Пользователь не найден!';

        }//if
        else{

            $wall = self::getPostWithParams(array(
                'numberposts' => $limit,
                'offset' => $offset,
                'author' => $user->ID,
                'type' => $user->roles[0] == 'administrator' ? 'anews' : 'unews'
            ));


            $result['status'] = 'success';
            $result['userWall'] = $wall;
            $result['params'] = array(
                'numberposts' => $limit,
                'offset' => $offset,
                'author' => $user->ID,
                'type' => 'anews'
            );

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

        $owner = filter_input(INPUT_POST, 'owner',FILTER_SANITIZE_STRING);
        $newTitle = filter_input(INPUT_POST, 'title',FILTER_SANITIZE_STRING);
        $newsAnonce = filter_input(INPUT_POST, 'anonce',FILTER_SANITIZE_STRING);
        $newsContent = $_POST['content'];

        $ownerObject = get_user_by('login',$owner);

        if($ownerObject == false){

            $result['status'] = 'error';
            $result['message'] = 'Пользовател не найден!';

        }//if
        else{

            $post_data = array(
                'post_title'    => wp_strip_all_tags($newTitle),
                'post_content'  => $newsContent,
                'post_status'   => 'publish',
                'post_author'   => $ownerObject->ID,
                'post_type' => $ownerObject->roles[0] == 'administrator' ? 'anews' : 'unews'
            );

            // Вставляем запись в базу данных
            $post_id = wp_insert_post( $post_data );

            update_post_meta($post_id,'anonce',$newsAnonce);

            $result['status'] = 'success';
            $result['message'] = 'Новость добавлена!';

        }//else


        self::echoDataWithHeader(
            array(
                'header' => 'json',
                'fields' => $result
            )
        );

    }//AddNews


}