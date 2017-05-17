/**
 * Created by Алексей on 28.04.2017.
 */

angular.module('anadir.filters').filter('descriptionFilter',function(){

    return function(description,maxSymbols){

        return description.length > maxSymbols ? description.substring(0,maxSymbols) + '...' : description;

    };

});
