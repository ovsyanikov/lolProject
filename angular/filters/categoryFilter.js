/**
 * Created by Алексей on 16.11.2016.
 */
angular.module('anadir.filters').filter('categoryFilter',function(){

    return function(pList,termID){

        var resultPList = [];

        for(var i = 0; i < pList.length ; i++){
            var category = pList[i].taxonomy[0];

            if(termID == category.term_id){
                resultPList.push(pList[i]);
            }//if

        }//for i

        return resultPList;

    };

});