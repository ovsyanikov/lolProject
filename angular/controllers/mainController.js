/**
 * Created by Алексей on 15.11.2016.
 *
 * weather request http://api.pogoda.com/index.php?api_lang=ru&localidad=13519&affiliate_id=mz2amlp38h8j&v=2&h=1
 *
 */

function mainController($scope,$state,$rootScope,newsService,userService,formValidator,ajaxUrl){

    //message section

    $scope.liveMessages = [];

    $scope.includeSlider = function () {
        if($state.current.name == 'home'){
            return '/angular/views/main/slider.html';
        }//if

    }

    $scope.CloseMessage = function(index){

        $scope.liveMessages.splice(index,1);

    };

    //user section

    formValidator.addConstraint('login',{

        'minLength': 6, 'maxLength': 20

    });

    formValidator.addConstraint('password',{

        'minLength': 6, 'maxLength': 20

    });

    formValidator.addConstraint('email',{

        'minLength': 6, 'maxLength': 90, 'containSymbol': '@'

    });

    formValidator.addConstraint('firstName',{

        'minLength': 1, 'maxLength': 50

    });

    formValidator.addConstraint('lastName',{

        'minLength': 1, 'maxLength': 50

    });

    $scope.validationRegister = {

        'type': '', 'message': ''

    };

    $scope.validationAuthorize = {

        'type': '', 'message': ''

    };

    $scope.ValidateRegister = function(){

        var validateResult = formValidator.validateField($scope.user.login,'login');

        if(!validateResult){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Неверно заполнено поле логин!';
            return false;

        }//if

        validateResult = formValidator.validateField($scope.user.firstName,'firstName');

        if(!validateResult){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Неверно заполнено имя пользователя!';
            return false;

        }//if

        validateResult = formValidator.validateField($scope.user.lastName,'lastName');

        if(!validateResult){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Неверно заполнено фамилия пользователя!';
            return false;

        }//if

        validateResult = formValidator.validateField($scope.user.lastName,'lastName');

        if(!validateResult){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Неверно заполнено фамилия пользователя!';
            return false;

        }//if

        validateResult = formValidator.validateField($scope.user.password,'password');

        if(!validateResult){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Неверно заполнен пароль';
            return false;

        }//if

        validateResult = formValidator.validateField($scope.user.confirmPassword,'password');

        if(!validateResult){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Неверно заполнено подтверждение пароля';
            return false;

        }//if

        if ($scope.user.confirmPassword.trim() != $scope.user.password.trim()){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Введенные пароли не совпадают';
            return false;

        }//if

        validateResult = formValidator.validateField($scope.user.email,'email');

        if(!validateResult){

            $scope.validationRegister.type = 'error';
            $scope.validationRegister.message = 'Неверно заполнен email';
            return false;

        }//if

        $scope.validationRegister.type = 'success';
        $scope.validationRegister.message = '';
        return true;

    }//Validate

    $scope.user = userService.isAuthorized();

    $scope.isUserInfoShow = $scope.user ? true : false;
    $scope.authorizeShow = false;
    $scope.registerShow = false;

    $scope.reloadUser = function(){

        $scope.user = {

            'login': '',
            'email': '',
            'firstName' : '',
            'lastName'  : '',
            'password'  : '',
            'confirmPassword' : '',
            'accessToken' : '',
            'phone' : ''

        };

    }

    $scope.startObserveMessages = function(whom){

        var observeParams = {

            'action': 'observeMessages',
            'accessToken': 'observeMessages',
            'me':  $scope.user.login

        };

        if(whom != undefined)
            observeParams.some = whom;

        // if($scope.lastMessageID != 0){
        //     observeParams.lastID = $scope.lastMessageID;
        // }//if

        var queryString = $.param(observeParams);

        $scope.eventSource = new EventSource(ajaxUrl + '?' + queryString) ;
        $scope.eventSource.addEventListener('message',function(e){

            try{
                var messages = e;

                var newMessage = JSON.parse(e.data);
                //console.log( 'newMessage',newMessage );

                if(newMessage.messages.length != 0){

                    if($state.current.name == 'dialog'){
                        $scope.messages.push(newMessage.messages[0].message[0]);
                        //console.log('pushed to messages');
                    }//if
                    else{
                        for(var i = 0; i < newMessage.messages.length ; i++){

                            for(var j = 0; j <  newMessage.messages[i].message.length; j++){
                                var testMessage = new String(newMessage.messages[i].message[j].Message);
                                if(testMessage.length >= 20){
                                    testMessage = testMessage.substring(0,17);
                                    testMessage+= '...';
                                }
                                $scope.liveMessages.push({ 'image': newMessage.messages[i].user.image , 'Message': testMessage});
                            }//for j

                        }//for i

                    }//else


                    $scope.$apply();
                    //console.log('message pushed!',newMessage.messages[$scope.messagesOffset]);

                }//if
                else{
                    //console.log('not pushed!');
                }
            }//try
            catch(ex){

            }//cathc

        },false);

        $scope.eventSource.onmessage = function(e) {

            if(e.data === 'auth error'){
                $scope.eventSource.close();
                console.log("Опрос сервера остановлен!");
            }
            else if(e.data != 'no messages'){

            }
        };

        $scope.eventSource.onopen = function(e) {
            //console.log("Соединение открыто");
        };

        $scope.eventSource.onerror = function(e) {
            if (this.readyState == EventSource.CONNECTING) {

                //console.log("Соединение порвалось, пересоединяемся...");

            } else {
                console.log("Ошибка, состояние: " + this.readyState);
            }
        }

    }

    if($scope.user == false){

        $scope.reloadUser();

    }//if
    else{
        $scope.startObserveMessages();
    }
    $scope.Authorize = function(){

        //console.log('user',$scope.user);

        userService.authorize($scope.user).then(function(userResponse){

            if(userResponse.status == 'success'){

                userService.saveUser(userResponse.user);
                $scope.user = userResponse.user;
                console.log('authorize.scope.user',$scope.user);
                $scope.isUserInfoShow = true;

                //$state.go('account',{reload: true});

                // var observeParams = {
                //
                //     'action': 'observeMessages',
                //     'accessToken': 'observeMessages',
                //     'me':  $scope.user.login,
                //     'some': $scope.whom.login
                //
                // };
                //
                // // if($scope.lastMessageID != 0){
                // //     observeParams.lastID = $scope.lastMessageID;
                // // }//if
                //
                // var queryString = $.param(observeParams);
                //
                // $scope.eventSource = new EventSource(ajaxUrl + '?' + queryString) ;
                // $scope.eventSource.addEventListener('message',function(e){
                //
                //     try{
                //         var messages = e;
                //
                //         var newMessage = JSON.parse(e.data);
                //         console.log( 'newMessage',newMessage );
                //
                //         if(newMessage.messages.length != 0){
                //
                //             $scope.messages.push(newMessage.messages[0]);
                //             $scope.$apply();
                //             //console.log('message pushed!',newMessage.messages[$scope.messagesOffset]);
                //
                //         }//if
                //         else{
                //             //console.log('not pushed!');
                //         }
                //     }//try
                //     catch(ex){
                //
                //     }//cathc
                //
                // },false);
                //
                // $scope.eventSource.onmessage = function(e) {
                //
                //     if(e.data === 'auth error'){
                //         $scope.eventSource.close();
                //         console.log("Опрос сервера остановлен!");
                //     }
                //     else if(e.data != 'no messages'){
                //
                //     }
                // };
                //
                // $scope.eventSource.onopen = function(e) {
                //     //console.log("Соединение открыто");
                // };
                //
                // $scope.eventSource.onerror = function(e) {
                //     if (this.readyState == EventSource.CONNECTING) {
                //
                //         //console.log("Соединение порвалось, пересоединяемся...");
                //
                //     } else {
                //         console.log("Ошибка, состояние: " + this.readyState);
                //     }
                // }

            }//if
            else{

                $scope.validationAuthorize.type = 'error';
                $scope.validationAuthorize.message = userResponse.message;

            }//else


        });


    }//Authorize

    $scope.AuthKeyPress = function (keyEvent) {
        if (keyEvent.which === 13){
            $scope.Authorize();
        }//

    }//AuthKeyPress

    $scope.messages =[];

    $scope.Register = function(){

        if(!$scope.ValidateRegister()){
            alert('Неверно заполнены пля регистрации');
            return;
        }

        userService.register($scope.user).then(function(registerData){

            console.log('register message',registerData);

            if(registerData.type == 'success'){

                $scope.validationRegister.type = 'success';
                $scope.validationRegister.message = 'Вы зарегистрированны. Проверьте почту!';

                //userService.saveUser($scope.user);


            }//if
            else {

                $scope.validationRegister.type = 'error';
                $scope.validationRegister.message = 'Вы зарегистрированны('+registerData.message+')';
                console.log('register info:',registerData );

            }//else

        });

    }//Register

    $scope.Logout = function(){

        userService.logout();
        $scope.reloadUser();

        $scope.isUserInfoShow = false;

        // $state.go('home',{'reload':true});

    }

    $scope.includeNoAuthorize = function(){

        if( $scope.isUserInfoShow == false ){
            return '/angular/views/header/noAuthorize.html';
        }//if

    };

    $scope.includeAuthorizeSuccess = function(){

        if( $scope.isUserInfoShow != false ){
            return '/angular/views/header/successAuthorize.html';
        }//if

    }




    $scope.includeScripts = function(index,length){

        if( index == length-1 ){
            return '/angular/views/main/scripts.html';
        }//if
        else
            return '';
    }

    //main section

    $rootScope.$on('$stateChangeSuccess',function(event, toState, toParams, fromState, fromParams){

        $scope.stateName = toState.name;

        if($scope.user){

            userService.haveAccess($scope.user.accessToken).then(function(response){

                $scope.accessResult = response;

                if($scope.accessResult.status != 'success' && $state.current.name == 'account'){

                    $state.go('home',{reload:'true'});

                }

            });
        }//if


    });

    $scope.slidersItems = [];

    newsService.getSlider().then(function (sliderItems) {

        [].forEach.call(sliderItems.posts,function (elem,index) {

            $scope.slidersItems.push(elem);

        });

        console.log('slidersItems',$scope.slidersItems );
    });

    $scope.date = new Date();

    $scope.date = $scope.date.toLocaleString('ru', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long',
        timezone: 'UTC',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric'
    }).split('.')[0].toUpperCase();

    $scope.includeScriptsAcordion = function(index,length){

        if( index == length-1 ){

            return '/angular/views/main/acordionScripts.html';
        }//if
        else
            return '';

    }//includeScriptsAcordion

    $scope.includeOwlScripts = function(index,length,id){

        if( index < length){
            return '/angular/views/main/owl-scripts.html';
        }//if
        else
            return '';
    }

};



angular.module('anadir.controllers')
    .controller('mainController',['$scope','$state','$rootScope','newsService','userService','formValidator','ajaxUrl',mainController]);