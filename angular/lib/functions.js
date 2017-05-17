
var REQUESTPARAMS = {
    headers: {'Content-Type': undefined },
    transformRequest: angular.identity
};



function MakePromise($http,$q,ajaxUrl,fd){

    var promise = $http.post(
        ajaxUrl,
        fd,
        REQUESTPARAMS

    );
    var deferObj = $q.defer();

    promise.then(
        function(answer){
            deferObj.resolve(answer.data);
        },
        function(reason){
            deferObj.reject(reason);
        }

    );
    return deferObj.promise;

}


function range(start,end) {

    var array = [];

    for(var i = start; i <= end ; i++){

        array.push(i);

    }

    return array;

}
