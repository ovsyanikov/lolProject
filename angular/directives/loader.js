/**
 * Created by Алексей on 18.04.2017.
 */

angular.module('anadir.directives').directive('loading',   ['$http' ,function ($http) {
        return {
            restrict: 'A',
            link: function (scope, elm, attrs)
            {
                // scope.isLoading = function () {
                //     return $http.pendingRequests.length > 0;
                // };
                //
                // scope.$watch(scope.isLoading, function (v) {
                //
                //     if(v){
                //         $(elm).show();
                //         //$('body').css({'overflow': 'hidden'});
                //     }//if
                //     else{
                //         $(elm).hide();
                //         //$('body').css({'overflow': ''});
                //     }//else
                //
                // });//watch
            }
        };

    }]);