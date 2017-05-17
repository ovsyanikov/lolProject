/**
 * Created by Алексей on 25.11.2016.
 */

/**
 * Created by Алексей on 16.11.2016.
 */
/**
 * Created by Алексей on 14.11.2016.
 */
;
function userService($http,$q,ajaxUrl,registerAction,authorizeAction,verifyAction,accessAction,updateUserAction,localStorageService){

    return({

        isAuthorized: getFromLocalStorage,
        saveUser: saveToStorage,
        authorize: authorizeUser,
        verify: verifyUser,
        register: registerUser,
        logout: logoutUser,
        haveAccess: isHaveAccess,
        updateUser: updateUser,
        updateAvatar: updateAvatar,
        updateEnvironent: updateEnvironent,
        getUserByLogin: getUserByLogin,
        getUserWall:getUserWall,
        AddToFriends: AddToFriends,
        GetUserFriendsRequest: GetUserFriendsRequest,
        GetFriendsRequestCount: GetFriendsRequestCount,
        ConfirmFriendRequest: ConfirmFriendRequest,
        GetUserFriends: GetUserFriends,
        AddNews: AddNews,
        getDialogsCount: getDialogsCount,
        addLike: addLike

    });

    function isHaveAccess(token){

        var fd = new FormData();

        fd.append("action", accessAction);
        fd.append("accessToken", token);

        return MakePromise($http,$q,ajaxUrl,fd);

    }//isHaveAccess

    function logoutUser(){

        localStorageService.remove('user');
    }

    function getFromLocalStorage(){

        var user = localStorageService.get('user');

        if(user == null)
            return false;
        else{

          return user;
        }


    }
    
    function  saveToStorage(user) {

        localStorageService.set('user',user);

    }//saveToStorage

    function authorizeUser(user){

        var fd = new FormData();

        fd.append("action", authorizeAction);
        fd.append("login", user.login);
        fd.append("password", user.password);

        return MakePromise($http,$q,ajaxUrl,fd);

    }//authorize

    function verifyUser(code){

        var fd = new FormData();

        fd.append("action", verifyAction);
        fd.append("code", code);

        return MakePromise($http,$q,ajaxUrl,fd);

    }//verifyUser

    function registerUser(user){

        var fd = new FormData();

        fd.append("action", registerAction);
        fd.append("login", user.login);
        fd.append("email", user.email);
        fd.append("password", user.password);
        fd.append("firstName", user.firstName);
        fd.append("lastName", user.lastName);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function updateUser(user){

        var fd = new FormData();

        fd.append("action", updateUserAction);
        fd.append("login", user.login);

        if(user.phone != undefined){
            fd.append("phone", user.phone);
        }//if

        if(user.webSite != undefined){
            fd.append("webSite", user.webSite);
        }//if

        if(user.email != undefined){
            fd.append("email", user.email);
        }//if


        if(user.city != undefined){
            fd.append("city", user.city);
        }//if

        if(user.skype != undefined){
            fd.append("skype", user.skype);
        }//if

        if(user.birthday != undefined){
            fd.append("birthday", user.birthday.value);
        }//if

        if(user.job != undefined){
            fd.append("job", user.job);
        }//if

        if(user.fStatus != undefined){
            fd.append("fStatus", user.fStatus);
        }//if

        return MakePromise($http,$q,ajaxUrl,fd);

    }//updateUser

    function updateAvatar(){

        var fd = new FormData();

        fd.append("action", 'changeUserAvatar');
        fd.append("login",getFromLocalStorage().login);
        fd.append("userfile", $('#image').prop('files')[0]);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function updateEnvironent(){

        var fd = new FormData();

        fd.append("action", 'changeUserEnvironment');
        fd.append("login",getFromLocalStorage().login);
        fd.append("imageEnvironment", $('#imageEnvironment').prop('files')[0]);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getUserByLogin(login){

        var fd = new FormData();

        fd.append("action", 'getUserByLogin');
        fd.append("login",login);
        fd.append("owner",getFromLocalStorage().login);


        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getUserWall(login,limit,offset,commentsCount){

        var fd = new FormData();

        fd.append("action", 'getUserWall');
        if(login != undefined)
            fd.append("login",login);
        else
            fd.append("login",getFromLocalStorage().login);

        fd.append("limit",limit);
        fd.append("offset",offset);

        if(commentsCount !== undefined)
            fd.append('commentsCount',commentsCount);


        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function AddToFriends(login){


        var fd = new FormData();

        fd.append("action", 'AddToFriends');
        fd.append("whoAdd",getFromLocalStorage().login);
        fd.append("whomAdd",login);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function GetUserFriendsRequest(){

        var fd = new FormData();

        fd.append("action", 'GetMyFriendRequests');
        fd.append("login",getFromLocalStorage().login);

        return MakePromise($http,$q,ajaxUrl,fd);

    }// GetUserFriendsRequest

    function GetFriendsRequestCount(){

        var fd = new FormData();

        fd.append("action", 'GetCountFriendRequests');
        fd.append("login",getFromLocalStorage().login);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function ConfirmFriendRequest(login){

        var fd = new FormData();

        fd.append("action", 'ConfirmFriendRequest');
        fd.append("whoAdd",getFromLocalStorage().login);
        fd.append("whomAdd",login);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function GetUserFriends(){
        var fd = new FormData();

        fd.append("action", 'GetUserFriends');
        fd.append("login",getFromLocalStorage().login);
        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function AddNews(news){

        var fd = new FormData();

        fd.append("action", 'AddNews');
        fd.append("owner",getFromLocalStorage().login);
        fd.append("content",news.content);

        if($('#newsPhotos').prop('files').length != 0){

            var photo = [];

            $.each($('#newsPhotos').prop('files'),function (i,elem) {
                //photo.push($('#newsPhotos').prop('files')[i]);
                fd.append(elem.name,$('#newsPhotos').prop('files')[i]);
            });

        }
        return MakePromise($http,$q,ajaxUrl,fd);


    }//

    function getDialogsCount(){

        var fd = new FormData();

        fd.append("action", 'getDialogsCount');
        fd.append("me",getFromLocalStorage().login);
        fd.append("accessToken",getFromLocalStorage().accessToken);

        return MakePromise($http,$q,ajaxUrl,fd);

    }//GetMessages

    function addLike(user) {

        var fd = new FormData();

        fd.append("action", 'AddNews');
        fd.append("owner",getFromLocalStorage().login);

    }//addLike

}//productService

angular.module('anadir.services')
    .service('userService',[
        '$http','$q','ajaxUrl','registerAction','authorizeAction','verifyAction','accessAction','updateUserAction','localStorageService',
        userService
    ]);