/**
 * Created by Алексей on 16.11.2016.
 */
/**
 * Created by Алексей on 14.11.2016.
 */
function pointsService($http,$q,ajaxUrl,getPointsAction){

    return({
        getPoints: getPoints
    });

    function getPoints(){

        var fd = new FormData();
        fd.append("action", getPointsAction);
        return MakePromise($http,$q,ajaxUrl,fd);

    }//getPoints

}//productService

angular.module('anadir.services')
    .service('pointsService',[
        '$http','$q','ajaxUrl','getPointsAction',
        pointsService]);