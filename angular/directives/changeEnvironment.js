/**
 * Created by Алексей on 13.01.2017.
 */
angular.module('anadir.directives').directive("environment", ['userService',function (userService) {
    return {
        restrict: 'A',
        scope: {
            filepath: "="
        },
        link: function (scope, element, attributes) {
            element.bind("change", function (changeEvent) {

                var reader = new FileReader();
                reader.onload = function (loadEvent) {
                    scope.$apply(function () {
                        userService.updateEnvironent().then(function(resp){

                            //console.log('avatar change response',resp);
                            if(resp.type == 'success'){

                                scope.filepath.src = resp.imgSource;
                                console.log('new photo',scope.filepath);
                            }
                        });

                    });
                }

                reader.readAsDataURL(changeEvent.target.files[0]);
            });
        }
    }
}]);