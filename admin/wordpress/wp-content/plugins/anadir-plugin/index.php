<?php
/*
Plugin Name: anadir-plugin
Author: AVAX
*/

include("includes/class.newPostType.php");
include("includes/class.newPostStatus.php");
include("includes/class.formGenerator.php");

include("includes/ajaxApi.php");
require_once "includes/Entity.php";
include 'includes/avgEntity.php';
include_once 'includes/taxonomy-term-image.php';

include("includes/api/ApplicationAPI.php");

add_action( 'init', 'registerPostTypes', 0 );



//$entity = new avgEntity();
//
//$entity->addEntityFields(
//
//        array(
//            'anonce' => '',
//            'home'  => '',
//            'aObject' => '',
//            'htmlPath' => '',
//            'phones' => '',
//            'address' => '',
//            'email' => '',
//            'contactFace' => '',
//            'contactFaceFIO' => '',
//            'Fax' => '',
//            'Viber' => '',
//            'Skype' => '',
//            'WhatsApp' => '',
//            'siteURL' => '',
//            'addressPoint' => ''
//        )
//);
//
//ajaxApi::initializeEntity($entity);
//ajaxApi::registerApiAction('getPostWithParams');
//ajaxApi::registerApiAction('getCategories');
//ajaxApi::registerApiAction('getPoints');
//ajaxApi::registerApiAction('getPoints');
//ajaxApi::registerApiAction('register');
//ajaxApi::registerApiAction('authorize');
//
//ajaxApi::registerApiAction('sendMail');
//ajaxApi::registerApiAction('verifyUser');
//ajaxApi::registerApiAction('isHaveAccess');
//ajaxApi::registerApiAction('updateUser');
//ajaxApi::registerApiAction('changeUserAvatar');
//ajaxApi::registerApiAction('changeUserEnvironment');
//ajaxApi::registerApiAction('getUserByLogin');
//ajaxApi::registerApiAction('getUserWall');
//ajaxApi::registerApiAction('AddToFriends');
//ajaxApi::registerApiAction('GetMyFriendRequests');
//ajaxApi::registerApiAction('GetCountFriendRequests');
//ajaxApi::registerApiAction('ConfirmFriendRequest');
//ajaxApi::registerApiAction('GetUserFriends');
//ajaxApi::registerApiAction('AddNews');


//API for application

ApplicationAPI::registerApiAction('getAnadyrPosts');

ApplicationAPI::registerApiAction('getCategories');
ApplicationAPI::registerApiAction('getPoints');
ApplicationAPI::registerApiAction('getPoints');
ApplicationAPI::registerApiAction('register');
ApplicationAPI::registerApiAction('authorize');

ApplicationAPI::registerApiAction('sendMail');
ApplicationAPI::registerApiAction('verifyUser');
ApplicationAPI::registerApiAction('isHaveAccess');
ApplicationAPI::registerApiAction('updateUser');
ApplicationAPI::registerApiAction('changeUserAvatar');
ApplicationAPI::registerApiAction('changeUserEnvironment');
ApplicationAPI::registerApiAction('getUserByLogin');
ApplicationAPI::registerApiAction('getUserWall');
ApplicationAPI::registerApiAction('AddToFriends');
ApplicationAPI::registerApiAction('GetMyFriendRequests');
ApplicationAPI::registerApiAction('GetCountFriendRequests');
ApplicationAPI::registerApiAction('ConfirmFriendRequest');
ApplicationAPI::registerApiAction('GetUserFriends');
ApplicationAPI::registerApiAction('AddNews');
ApplicationAPI::registerApiAction('observeMessages');
ApplicationAPI::registerApiAction('getDialog');
ApplicationAPI::registerApiAction('getDialogs');
ApplicationAPI::registerApiAction('sendMessage');
ApplicationAPI::registerApiAction('getDialogsCount');
ApplicationAPI::registerApiAction('addComment');
ApplicationAPI::registerApiAction('getComments');
ApplicationAPI::registerApiAction('addLike');

//Мета-поля для таксономии новестей
function pippin_taxonomy_add_new_meta_field() {
    // this will add the custom meta field to the add new term page
    ?>
    <div class="form-field">
        <label for="term_meta[custom_term_meta]">Порядковый номер</label>
        <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
        <p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
    </div>
    <?php
}
add_action( 'ataxes_add_form_fields', 'pippin_taxonomy_add_new_meta_field', 10, 2 );

function pippin_taxonomy_edit_meta_field($term) {

    // put the term ID into a variable
    $t_id = $term->term_id;

    // retrieve the existing value(s) for this meta field. This returns an array
    $term_meta = get_option( "taxonomy_$t_id" ); ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="term_meta[custom_term_meta]">Порядковый номер</label></th>
        <td>
            <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
            <p class="description">Введите порядковый номер</p>
        </td>
    </tr>
    <?php
}
add_action( 'ataxes_edit_form_fields', 'pippin_taxonomy_edit_meta_field', 10, 2 );

function save_taxonomy_custom_meta( $term_id ) {
    if ( isset( $_POST['term_meta'] ) ) {
        $t_id = $term_id;
        $term_meta = get_option( "taxonomy_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ) {
            if ( isset ( $_POST['term_meta'][$key] ) ) {
                $term_meta[$key] = $_POST['term_meta'][$key];
            }
        }
        // Save the option array.
        update_option( "taxonomy_$t_id", $term_meta );
    }
}
add_action( 'edited_ataxes', 'save_taxonomy_custom_meta', 10, 2 );
add_action( 'create_ataxes', 'save_taxonomy_custom_meta', 10, 2 );

//Мета-поля для таксономии "культурной жизни"

function anadir_taxonomy_add_new_meta_field() {
    // this will add the custom meta field to the add new term page
    ?>
    <div class="form-field">
        <label for="term_meta[custom_term_meta]">Порядковый номер</label>
        <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="">
        <p class="description"><?php _e( 'Enter a value for this field','pippin' ); ?></p>
    </div>
    <?php
}
add_action( 'culturetax_add_form_fields', 'anadir_taxonomy_add_new_meta_field', 10, 2 );

function anadir_taxonomy_edit_meta_field($term) {

    // put the term ID into a variable
    $t_id = $term->term_id;

    // retrieve the existing value(s) for this meta field. This returns an array
    $term_meta = get_option( "taxonomy_$t_id" ); ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="term_meta[custom_term_meta]">Порядковый номер</label></th>
        <td>
            <input type="text" name="term_meta[custom_term_meta]" id="term_meta[custom_term_meta]" value="<?php echo esc_attr( $term_meta['custom_term_meta'] ) ? esc_attr( $term_meta['custom_term_meta'] ) : ''; ?>">
            <p class="description">Введите порядковый номер</p>
        </td>
    </tr>
    <?php
}
add_action( 'culturetax_edit_form_fields', 'anadir_taxonomy_edit_meta_field', 10, 2 );

function save_anadir_taxonomy_custom_meta( $term_id ) {
    if ( isset( $_POST['term_meta'] ) ) {
        $t_id = $term_id;
        $term_meta = get_option( "taxonomy_$t_id" );
        $cat_keys = array_keys( $_POST['term_meta'] );
        foreach ( $cat_keys as $key ) {
            if ( isset ( $_POST['term_meta'][$key] ) ) {
                $term_meta[$key] = $_POST['term_meta'][$key];
            }
        }
        // Save the option array.
        update_option( "taxonomy_$t_id", $term_meta );
    }
}
add_action( 'edited_culturetax', 'save_anadir_taxonomy_custom_meta', 10, 2 );
add_action( 'create_culturetax', 'save_anadir_taxonomy_custom_meta', 10, 2 );

function registerPostTypes()
{


    add_theme_support('post-thumbnails');
    add_image_size( 'small-size', 84, 84, true );
    add_image_size( 'big-size', 288, 142 , true);
    add_image_size( 'instagram-size', 213, 213 , true);

    $fields = array();
    $metaBox = array();

    $fields[] = array('type' => 'input-text', 'name' => 'anonce', 'placeholder' => 'Анонс', 'label' => "Введите краткое описание");

    $metaBox[] = array('name' => 'Дополнительные сведения', 'fields' => $fields);


    $aNews= new newPostType('aNews', 'Новости', array('title', 'editor', 'thumbnail'), array('aTaxes'), $metaBox);
    $aNews= new newPostType('uNews', 'Новости пользователей', array('title', 'editor', 'thumbnail'), array('aTaxes'), $metaBox);


    $fields = array();
    $metaBox = array();

    $fields[] = array('type' => 'input-text', 'name' => 'home', 'placeholder' => 'Дом', 'label' => "Введите № дома");
    $fields[] = array('type' => 'input-text', 'name' => 'aObject', 'placeholder' => 'Объект', 'label' => "Введите объект");
    $fields[] = array('type' => 'input-text', 'name' => 'htmlPath', 'placeholder' => 'Адрес на карте', 'label' => "Введите адрес на карте");


    $metaBox[] = array('name' => 'Дополнительные сведения', 'fields' => $fields);

    $Points = new newPostType('aPoint', 'Точки', array('title', 'thumbnail'), array('pTax'), $metaBox);

    //Культурная жизнь
    $fields = array();
    $metaBox = array();

    $fields[] = array('type' => 'input-text', 'name' => 'anonce', 'placeholder' => 'Анонс', 'label' => "Введите краткое описание");

    $metaBox[] = array('name' => 'Дополнительные сведения', 'fields' => $fields);

    $cultureLife = new newPostType('cultureLife', 'Культурная жизнь', array('title',  'editor' , 'thumbnail'), array('cultureTax'),$metaBox);

    //Организации

    $fields = array();
    $metaBox = array();

    $fields[] = array('type' => 'input-text', 'name' => 'anonce', 'placeholder' => 'Анонс', 'label' => "Введите краткое описание");
    $fields[] = array('type' => 'input-text', 'name' => 'phones', 'placeholder' => 'Телефоны', 'label' => "Введите телефоны");
    $fields[] = array('type' => 'input-text', 'name' => 'address', 'placeholder' => 'Адрес', 'label' => "Введите адрес");
    $fields[] = array('type' => 'input-text', 'name' => 'email', 'placeholder' => 'E-mail', 'label' => "Введите e-mail");
    $fields[] = array('type' => 'input-text', 'name' => 'contactFace', 'placeholder' => 'Введите контактное лицо', 'label' => "Введите контактное лицо");
    $fields[] = array('type' => 'input-text', 'name' => 'contactFaceFIO', 'placeholder' => 'ФИО', 'label' => "Введите ФИО");
    $fields[] = array('type' => 'input-text', 'name' => 'Fax', 'placeholder' => 'Факс', 'label' => "Введите факс");
    $fields[] = array('type' => 'input-text', 'name' => 'Viber', 'placeholder' => 'Viber', 'label' => "Введите viber");
    $fields[] = array('type' => 'input-text', 'name' => 'Skype', 'placeholder' => 'Skype', 'label' => "Введите skype");
    $fields[] = array('type' => 'input-text', 'name' => 'WhatsApp', 'placeholder' => 'WhatsApp', 'label' => "Введите WhatsApp");
    $fields[] = array('type' => 'input-text', 'name' => 'siteURL', 'placeholder' => 'Адрес сайта', 'label' => "Адрес сайта");
    $fields[] = array('type' => 'input-text', 'name' => 'addressPoint', 'placeholder' => 'Адрес точки', 'label' => "Адрес точки");

    $fields[] = array('type' => 'input-text', 'name' => 'vkURL', 'placeholder' => 'Vkontakte', 'label' => "Vkontakte");
    $fields[] = array('type' => 'input-text', 'name' => 'twitterURL', 'placeholder' => 'Twitter', 'label' => "Twitter");
    $fields[] = array('type' => 'input-text', 'name' => 'okURL', 'placeholder' => 'Одноклассники', 'label' => "Ok");
    $fields[] = array('type' => 'input-text', 'name' => 'facebookURL', 'placeholder' => 'Facebook', 'label' => "Facebook");
    $fields[] = array('type' => 'input-text', 'name' => 'instagramURL', 'placeholder' => 'Instagram', 'label' => "Instagram");

    $metaBox[] = array('name' => 'Дополнительные сведения', 'fields' => $fields);

    $organizations = new newPostType('organizations', 'Организации', array('title',  'editor' , 'thumbnail'), array('organizationsTax'),$metaBox);

    $fields = array();
    $metaBox = array();

    $fields[] = array('type' => 'input-text', 'name' => 'path', 'placeholder' => 'Путь к точке', 'label' => "Введите путь к точке");
    $metaBox[] = array('name' => 'Дополнительные сведения', 'fields' => $fields);

    $sliderCup = new newPostType('mainSlider', 'Слайдер', array('title','thumbnail'), array(),$metaBox);

//    new PostStatus('payed','Оплачен');
//    new PostStatus('notPayed','Не оплачен');
//    new PostStatus('complited','Завершен');
//    new PostStatus('canceled','Отменен');



}//

