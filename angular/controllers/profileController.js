/**
 * Created by Алексей on 13.01.2017.
 */
function profileController($scope,$stateParams,$state,$rootScope,newsService,pointsService,userService,formValidator){

    $scope.constraints = {

        'canAddToFriends': false,
        'canSendMessage': false,
        'addRequestResult': false

    }

    console.log('is friend',$scope.isFriend);

    $scope.NotConfirm = true;

    $scope.$watch('accessResult',function(ov,nv){
        console.log('access result',$scope.accessResult );
        $scope.constraints.canAddToFriends = $scope.accessResult != undefined && $scope.accessResult.status == 'success' && $scope.isFriend != 'wait' ? true : false;
    });

    $scope.AddToFriends = function(login){

        userService.AddToFriends(login).then(

            function(response){

                console.log('add response: ',response );

                if(response.status == 'success'){
                    $scope.constraints.addRequestResult = true;
                    $scope.constraints.canAddToFriends = false;
                    $scope.isFriend = 'wait';
                }//if

            }//f response

        );


    };

    $scope.ConfirmFriendRequest = function(login){

            userService.ConfirmFriendRequest(login).then(function(resp){

                console.log('confirm response',resp);

                if(resp.status == 'success'){

                    $scope.NotConfirm = false;

                }


            });

    }

}

angular.module('anadir.controllers')
    .controller('profileController', profileController);