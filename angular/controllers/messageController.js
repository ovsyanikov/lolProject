/**
 * Created by Алексей on 24.01.2017.
 */
function messageController($scope,userService,messageService,ajaxUrl){

    $scope.userMessage = '';
    //console.log("messages.messageController",$scope.messages);
    $scope.SendMessage =    function(){


        if($scope.userMessage.trim().length == 0 )
            return;

        messageService.SendMessage($scope.userMessage,  $scope.whom.login).then(function(response){

            console.log('message send result',response);
            if(response.status == 'success'){
                response.message.owner = 'me';
                $scope.messages.push(response.message);
                console.log('insert!');
                console.log('messages',$scope.messages);
            }
            else{
                console.log('not inserted');
            }
        });

    }//SendMessage





}


angular.module('anadir.controllers')
    .controller('messageController', ['$scope','userService','messageService','ajaxUrl', messageController]);