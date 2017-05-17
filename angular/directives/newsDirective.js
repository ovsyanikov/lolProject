/**
 * Created by Алексей on 14.11.2016.
 */

angular.module('anadir.directives').directive('newsitem',function($rootScope) {
    return {
        restrict: 'A',
        templateUrl: 'angular/views/directive/news-item.html',
        scope: {
            cnews:"=",
            index: "=",
            itemlength: "="
        },

        link: function($scope, element, attr) {

            //componentHandler.upgradeAllRegistered();

        }// link
    };
});
