/**
 * Created by Алексей on 15.11.2016.
 */
angular.module('anadir.directives').directive('newsitemlist',function($rootScope) {
    return {
        restrict: 'A',
        templateUrl: 'angular/views/directive/newsItemList.html',
        scope: {
            cnews:"=",
            index: "=",
            itemlength: "="
        },

        link: function($scope, element, attr) {

            //componentHandler.upgradeAllRegistered();
            $scope.cnews.isShowContacts = false;
            if($scope.cnews.type == 'organizations')
                $scope.cnews.phones = $scope.cnews.phones.split(',');

            $scope.toggleContacts = function(news){

                news.isShowContacts = (news.isShowContacts ? false : true);
                //$('[data-org-id=\''+news.id+'\']').toggleClass('active-contacts');
                //$('.organization-cart').toggleClass('active-contacts');


            }

        }// link
    };
});
