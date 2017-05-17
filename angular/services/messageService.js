/**
 * Created by Алексей on 24.01.2017.
 */
;
function messageService($http,$q,ajaxUrl,localStorageService){

    return({

        SendMessage: SendMessage,
        GetMessages: GetMessages,
        getDialogs: getDialogs

    });


    function SendMessage(message,to){

        var fd = new FormData();

        fd.append("action", 'sendMessage');
        fd.append("accessToken", localStorageService.get('user').accessToken);
        fd.append("from", localStorageService.get('user').login);
        fd.append("to", to);
        fd.append("message", message);

        return MakePromise($http,$q,ajaxUrl,fd);


    }//

    function GetMessages(from,to){

        var fd = new FormData();

        fd.append("action", 'getDialog');
        fd.append("accessToken", localStorageService.get('user').accessToken);
        fd.append("me", localStorageService.get('user').login);
        fd.append("some", from);

        return MakePromise($http,$q,ajaxUrl,fd);


    }//GetMessages

    function getDialogs(){

        var fd = new FormData();

        fd.append("action", 'getDialogs');
        fd.append("accessToken", localStorageService.get('user').accessToken);
        fd.append("me", localStorageService.get('user').login);

        return MakePromise($http,$q,ajaxUrl,fd);


    }//GetMessages

}//productService

angular.module('anadir.services')
    .service('messageService',['$http','$q','ajaxUrl','localStorageService',messageService]);