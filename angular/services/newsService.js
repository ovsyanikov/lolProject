/**
 * Created by Алексей on 14.11.2016.
 */
;
function newsService($http,$q,ajaxUrl,getPostsAction,getCategoriesAction,userService){

    return({
        getNews: getNewsList,
        getCultures: getCultureList,
        getNewsByID: getNewsByID,
        getCategories: getCategories,
        getCultureByID: getCultureByID,
        getOrganizations: getOrganizationsList,
        getOrganizationByID:getOrganizationByID,
        getSlider: getSlider,
        addComment: addComment,
        addLike: addLike,
        getMoreComments: getMoreComments

    });

    function getNewsList(offset,limit,category,type,commentsCount){

        var fd = new FormData();
        fd.append("action", getPostsAction);

        if(commentsCount !== undefined)
            fd.append('commentsCount',commentsCount);

        if(type === undefined)
            fd.append('type','anews');
        else
            fd.append('type',type);

        fd.append('orderby' , 'ID');

        if(limit != undefined){
            fd.append('numberposts' , limit);
        }
        else{
            fd.append('numberposts' , 5);
        }
        if(offset != undefined){
            fd.append('offset' , offset);
        }//if

        if(category != undefined){
            fd.append('category' , category.term_id);
            fd.append('taxonomy' ,  'ataxes');

        }//if

        if(userService.isAuthorized() != false)
            fd.append('user',userService.isAuthorized().login);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getCultureList(offset,limit,category){


        var fd = new FormData();
        fd.append("action", getPostsAction);
        fd.append('type','culturelife');
        fd.append('orderby' , 'ID');

        if(limit != undefined){
            fd.append('numberposts' , limit);
        }
        else{
            fd.append('numberposts' , 5);
        }
        if(offset != undefined){
            fd.append('offset' , offset);
        }//if

        if(category != undefined){
            fd.append('category' , category.slug);
            fd.append('taxonomy' ,  'culturetax');

        }//if

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getOrganizationsList(offset,limit,category){

        var fd = new FormData();
        fd.append("action", getPostsAction);
        fd.append('type','organizations');
        fd.append('orderby' , 'ID');

        if(limit != undefined){
            fd.append('numberposts' , limit);
        }
        else{
            fd.append('numberposts' , 5);
        }
        if(offset != undefined){
            fd.append('offset' , offset);
        }//if

        if(category != undefined){
            fd.append('category' , category.term_id);
            fd.append('taxonomy' ,  'organizationstax');

        }//if

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getOrganizationByID(id){

        var fd = new FormData();
        fd.append("action", getPostsAction);
        fd.append("type", 'organizations');
        fd.append("ID", id);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getNewsByID(id){

        var fd = new FormData();
        fd.append("action", getPostsAction);
        fd.append("type", 'anews');
        fd.append("ID", id);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getCultureByID(id){

        var fd = new FormData();
        fd.append("action", getPostsAction);
        fd.append("type", 'culturelife');
        fd.append("ID", id);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getCategories(tax){

        var categoriesParams = new FormData();
        categoriesParams.append("action", getCategoriesAction);

        if(tax != undefined){

            categoriesParams.append("tax", tax);

        }

        return MakePromise($http,$q,ajaxUrl,categoriesParams);
    }

    function getSlider() {

        var fd = new FormData();
        fd.append("action", getPostsAction);
        fd.append('type','mainslider');
        fd.append('orderby','post_title');
        fd.append('order','ASC');

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function addComment(user,messageText,postId) {

        var fd = new FormData();
        fd.append("action", "addComment");
        fd.append('name',user.login);
        fd.append('message',messageText);
        fd.append('id',postId);

        return MakePromise($http,$q,ajaxUrl,fd);

    }

    function getMoreComments(post,allCount,nowPosts) {


        var fd = new FormData();
        fd.append("action", "getComments");
        fd.append('id',post.id);
        fd.append('number',allCount-nowPosts);
        fd.append('offset',0);

        return MakePromise($http,$q,ajaxUrl,fd);


    }//getComments

    function addLike(user,postId,comment) {

        var fd = new FormData();
        fd.append("action", "addLike");
        fd.append('name',user.login);
        fd.append('id',postId);

        if(comment)
            fd.append('comment','comment');

        return MakePromise($http,$q,ajaxUrl,fd);

    }

}//productService

angular.module('anadir.services')
    .service('newsService',[
        '$http','$q','ajaxUrl','getPostsAction','getCategoriesAction','userService',
        newsService]);