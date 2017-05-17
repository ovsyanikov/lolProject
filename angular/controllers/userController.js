/**
 * Created by Алексей on 12.01.2017.
 */
function userController($scope,userService){

    //console.log('user',$scope.user);

    //Новые данные, временная копия пользователя
    $scope.userUpdateFields = {

        'firstName': $scope.user.firstName,
        'lastName': $scope.user.lastName,
        'phone': $scope.user.phone,
        'status': $scope.user.status,
        'webSite': $scope.user.webSite,
        'email' : $scope.user.email,
        'image': $scope.user.image,
        'environment': $scope.user.environment,
        'postsCount': $scope.user.postsCount,
        'subscribersCount': $scope.user.subscribersCount,
        'mySubscribes': $scope.user.mySubscribes

    };
    $scope.userfile = { 'src': ''};
    $scope.userenvironment = {'src': ''};

    $scope.changeCount = 1;
    $scope.environmentChange = 1;

    $scope.news = {
        'title': '',
        'anonce': '',
        'description': ''
    };

    $scope.$watch('userfile.src',function(newUF, oldUF){

        if($scope.changeCount != 1){

            $scope.user.image = newUF;
            $scope.userUpdateFields.image =  newUF;

            userService.saveUser($scope.user);

        }//if

        $scope.changeCount++;

    });

    $scope.$watch('userenvironment.src',function(newUF, oldUF){

        if($scope.environmentChange != 1){

            $scope.user.environment = newUF;
            $scope.userUpdateFields.environment =  newUF;

            userService.saveUser($scope.user);

        }//if

        $scope.environmentChange++;

    });
    //console.log('$scope.userUpdateFields',$scope.userUpdateFields);

    //Обновление статуса пользователя
    $scope.updateStatus = function(){

        //console.log('file change', $scope.userfile);
        if( $scope.user.status != $scope.userUpdateFields.status && $scope.userUpdateFields.status.trim().length != 0){

            userService.updateUser({'status': $scope.userUpdateFields.status, 'login': $scope.user.login}).then(function(response){

                console.log('update message',response);
                $scope.user.status = $scope.userUpdateFields.status;
                userService.saveUser($scope.user);

            });

        }//if
        else{

            console.log('update imposible');
            $scope.userUpdateFields.status =  $scope.user.status;
        }//else
    };

    //Обновление email,phone,web-site
    $scope.updateOtherFields = function(){

        var toUpdateUser = {};
        toUpdateUser.login = $scope.user.login;

        if($scope.userUpdateFields.phone != $scope.user.phone && $scope.userUpdateFields.phone.trim().length > 0){
            toUpdateUser.phone = $scope.userUpdateFields.phone.trim();
            $scope.user.phone = toUpdateUser.phone;
        }//if

        if($scope.userUpdateFields.webSite != $scope.user.webSite && $scope.userUpdateFields.webSite.trim().length > 0){
            toUpdateUser.webSite = $scope.userUpdateFields.webSite.trim();
            $scope.user.webSite = toUpdateUser.webSite;
        }//if

        if($scope.userUpdateFields.email != $scope.user.email && $scope.userUpdateFields.email.trim().length > 0){
            toUpdateUser.email = $scope.userUpdateFields.email.trim();
            $scope.user.email = toUpdateUser.email;
        }//if


        userService.updateUser(toUpdateUser).then(function(response){

            console.log('update message',response);

        });

    }//updateOtherFields

    $scope.AddUserNews = function(){

        $scope.news.description =  $scope.editor.instanceById('userNews').getContent();

        userService.AddNews($scope.news).then(function(response){

            console.log('add response', response);

        });

    };

}


angular.module('anadir.controllers')
    .controller('userController', ['$scope','userService', userController]);