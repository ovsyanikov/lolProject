/**
 * Created by Алексей on 27.11.2016.
 */

/*

constraint struct
constraintName: { minLength, maxLength, containSymbol }

 */
;
function formValidator(){

    var constraints = [];

    return({

        validateField : fieldValidate,
        addConstraint : addConstraint
    });

    function fieldValidate(field, constraintName){

        var currentConstraint = undefined;

        for(var i = 0; i < constraints.length ; i++){

            if(constraints[i].name == constraintName){
                currentConstraint = constraints[i];
            }

        }

        if(currentConstraint == undefined){

            throw "Ограничение не найдено по имени!";

        }//if

        var minLength = currentConstraint.constraints.minLength;
        var maxLength = currentConstraint.constraints.maxLength;
        var containSymbol = currentConstraint.constraints.containSymbol != undefined  ? currentConstraint.constraints.containSymbol : undefined;

        field = new String(field).trim();

        if(field.length < minLength){
            return false;
        }

        if(field.length > maxLength){

            return false;

        }

        if(containSymbol != undefined){

            if( field.indexOf(containSymbol)== -1 ) {
                return false;
            }

        }

        return true;

    }

    function addConstraint(constraintName,constraintParams){
        constraints.push({'name':constraintName, 'constraints':constraintParams });
    }//addConstraint

}//productService

angular.module('anadir.services')
    .service('formValidator',formValidator);