/**
 * Created by Алексей on 14.11.2016.
 */
;'use strict';

angular.module('anadir.controllers', []);
angular.module('anadir.directives', []);
angular.module('anadir.filters', []);
angular.module('anadir.services', []);
angular.module('anadir.constants', []);
angular.module('anadir.animation', []);

angular.module('anadir.constants').constant('ajaxUrl','http://3danadyr.ru/admin/wordpress/wp-admin/admin-ajax.php');
angular.module('anadir.constants').constant('getPostsAction','getAnadyrPosts');
angular.module('anadir.constants').constant('getCategoriesAction','getCategories');
angular.module('anadir.constants').constant('getPointsAction','getPoints');
angular.module('anadir.constants').constant('registerAction','register');
angular.module('anadir.constants').constant('authorizeAction','authorize');
angular.module('anadir.constants').constant('verifyAction','verifyUser');
angular.module('anadir.constants').constant('accessAction','isHaveAccess');
angular.module('anadir.constants').constant('updateUserAction','updateUser');
angular.module('anadir.constants').constant('postsPerPage','2');
angular.module('anadir.constants').constant('commentsCount',3);
angular.module('anadir.constants').constant('tapePostsCount',5);

angular.module('anadir.constants').constant('postsPerPageOffset','2');
angular.module('anadir.animation').animation('.repeated-item', function() {
    return {
        enter: function(element, done) {
            element.css('opacity',0);
            jQuery(element).animate({
                opacity: 1
            }, done);

            // optional onDone or onCancel callback
            // function to handle any post-animation
            // cleanup operations
            return function(isCancelled) {
                if(isCancelled) {
                    jQuery(element).stop();
                }
            }
        },
        leave: function(element, done) {
            element.css('opacity', 0);
            jQuery(element).animate({
                opacity: 0
            }, done);

            // optional onDone or onCancel callback
            // function to handle any post-animation
            // cleanup operations
            return function(isCancelled) {
                if(isCancelled) {
                    jQuery(element).stop();
                }
            }
        },
        move: function(element, done) {
            element.css('opacity', 0);
            jQuery(element).animate({
                opacity: 1
            }, done);

            // optional onDone or onCancel callback
            // function to handle any post-animation
            // cleanup operations
            return function(isCancelled) {
                if(isCancelled) {
                    jQuery(element).stop();
                }
            }
        },

        // you can also capture these animation events
        addClass: function(element, className, done) {},
        removeClass: function(element, className, done) {}
    }
});
angular.module('anadir.animation').animation('.slide', [function() {
    return {
        // make note that other events (like addClass/removeClass)
        // have different function input parameters
        enter: function(element, doneFn) {
            jQuery(element).fadeIn(1000, doneFn);

            // remember to call doneFn so that AngularJS
            // knows that the animation has concluded
        },

        move: function(element, doneFn) {
            jQuery(element).fadeIn(1000, doneFn);
        },

        leave: function(element, doneFn) {
            jQuery(element).fadeOut(1000, doneFn);
        }
    }
}]);

angular.module('anadir', ['ngAnimate']);

function GetApplicationConfig($stateProvider,$urlRouterProvider,$locationProvider,cfpLoadingBarProvider,localStorageServiceProvider){

    $locationProvider.html5Mode(true).hashPrefix('!');
    $urlRouterProvider.otherwise('/');

    localStorageServiceProvider
        .setPrefix('anadir')
        .setStorageType('localStorage');

    localStorageServiceProvider
        .setStorageCookie(45, '/', false);

    cfpLoadingBarProvider.includeSpinner = true;

    $stateProvider.state("home", {
        'url': '/',
        'views': {
            "main": {
                'templateUrl': 'angular/views/main/home.html',
                'controller':
                    ['$scope','$sce','$rootScope', 'newsService','news','tape', function($scope,$sce,$rootScope,newsService,news,tape){

                        $scope.news = news.posts;
                        console.log(news);

                        $scope.allNewsCount = news.count;
                        $scope.tape = tape.posts;

                        $scope.tape.forEach(function (elem,index) {

                            $scope.tape[index].contentStrict = 200;

                        });

                        $scope.addLike = function (post) {

                            newsService.addLike($scope.user,post.id).then(function (response) {

                                if(response.type == 'success'){

                                    post.likes = response.likes;

                                }//if
                                else{
                                    console.log('response',response);
                                }

                            });

                        }//addLike()

                    }],
                'resolve': {
                    'news': ['newsService',function (newsService) {
                        return newsService.getNews(0,12);
                    }],
                    'tape':['newsService',function (newsService) {
                        return newsService.getNews(0,12,undefined,'unews');
                    }]
                }
            }
        }//views

    });

    $stateProvider.state("single-news", {
        'url': '/single-news/:newsID',
        'views': {
            "main": {
                'templateUrl': 'angular/views/single/singleNews.html',
                'controller':
                    ['$scope','$compile','$sce','$rootScope','$stateParams', 'newsService','news', function($scope,$compile,$sce,$rootScope,$stateParams,newsService,news){

                        $scope.news = news[0];
                        //console.log('single-news',$scope.news);

                        $scope.news.description = $sce.trustAsHtml($scope.news.description);

                        var template = angular.element('<a ui-sref="news">Новости \\  </a>');
                        var linkFn = $compile(template);
                        var element = linkFn($scope);

                        $scope.breadCumbs.push(
                            {
                                'title': 'Новости',
                                'stateLink': $sce.trustAsHtml(element[0].outerHTML)
                            });

                        $scope.term = $scope.news.taxonomy[0].term_id;
                        template = angular.element('<a ui-sref="news({termID: term})">' + $scope.news.taxonomy[0].name + '\\  </a>');
                        linkFn = $compile(template);
                        element = linkFn($scope);

                        $scope.breadCumbs.push({
                            'title': $scope.news.title,
                            'stateLink': $sce.trustAsHtml(element[0].outerHTML)
                        });

                        $scope.newsIdentity = $scope.news.id;

                        template = angular.element('<a ui-sref="single-news({newsID: newsIdentity})">' + $scope.news.title + '</a>');
                        linkFn = $compile(template);
                        element = linkFn($scope);

                        $scope.breadCumbs.push({
                            'title': $scope.news.title,
                            'stateLink': $sce.trustAsHtml(element[0].outerHTML)
                        });

                       // console.log( $scope.breadCumbs,'$scope.breadCumbs');


                    }],
                'resolve': {
                    'news': ['newsService','$stateParams',function (newsService,$stateParams) {
                        return newsService.getNewsByID($stateParams.newsID);
                    }]

                }
            }
        }//views

    });

    $stateProvider.state("news", {
        'url': '/news/:termID',
        'views': {
            "main": {
                'templateUrl': 'angular/views/main/news.html',
                'controller':
                    ['$scope','$sce','$rootScope', 'newsService','postsPerPage','postsPerPageOffset','news','categories',
                        function($scope,$sce,$rootScope,newsService,postsPerPage,postsPerPageOffset,news,categories){

                        $scope.news = news.posts;
                        $scope.allPosts = news.count;
                        $scope.postsPerPage = +postsPerPage;

                        $scope.categories = categories;

                        $scope.category = undefined;

                        $scope.reloadPagination = function (allposts) {
                            $scope.postsPerPage = +postsPerPage;

                            $scope.pages = Math.ceil(allposts / postsPerPage);
                            $scope.pages = range(1,$scope.pages);
                            $scope.currentPage = 1;
                        }

                        $scope.reloadPagination($scope.allPosts);

                        $scope.NextNews = function (offset,toPage) {

                            event.stopPropagation();

                            if(toPage ==  $scope.currentPage)
                                return;

                            newsService.getNews(+offset,+postsPerPage,$scope.category).then(function (response) {

                                $scope.news = response.posts.length > 0 ? response.posts : [];
                                $scope.currentPage = toPage;

                            });



                        };//NextNews

                        $scope.filterNews = function (category) {

                                if($scope.category != undefined && $scope.category.term_id == category.term_id)
                                    return;

                                newsService.getNews(0,postsPerPage,category).then(function (response) {

                                    console.log('response',response);
                                    $scope.news = response.posts.length > 0 ? response.posts : [];
                                    $scope.allPosts = response.count;
                                    $scope.reloadPagination(response.count);
                                    $scope.category = category;
                                    $scope.$parent.category = $scope.category;

                                });



                            }

                        $scope.$parent.reloadPagination = $scope.reloadPagination;
                        $scope.$parent.NextNews = $scope.NextNews;
                        $scope.$parent.filterNews = $scope.filterNews;
                        $scope.$parent.categories = $scope.categories;
                        $scope.$parent.category = $scope.category;

                    }],
                'resolve': {
                    'news': ['newsService','postsPerPage',function (newsService,postsPerPage) {
                        return newsService.getNews(0,postsPerPage);
                    }],
                    'categories':['newsService',function (newsService) {
                        return newsService.getCategories();
                    }]


                }
            }
        }//views

    });

    $stateProvider.state("culture", {
        'url': '/culture/:cultureID',
        'views': {
            "main": {
                'templateUrl': 'angular/views/main/culture.html',
                'controller':
                    ['$scope','$sce','$rootScope', 'newsService','cultures', function($scope,$sce,$rootScope,newsService,cultures){

                        $scope.culturesArray = cultures;
                        //console.log('cultures',$scope.culturesArray);

                    }],
                'resolve': {
                    'cultures': ['newsService',function(newsService){
                        return  newsService.getCultures(0,12)
                    }]

                }
            }
        }//views

    });

    $stateProvider.state("single-culture", {
        'url': '/single-culture/:cultureID',
        'views': {
            "main": {
                'templateUrl': 'angular/views/single/singleNews.html',
                'controller':
                    ['$scope','$compile','$sce','$rootScope', 'newsService','news', function($scope,$compile,$sce,$rootScope,newsService,news){

                        $scope.news = news[0];
                        //console.log('single-culture',$scope.news);
                        $scope.news.description = $sce.trustAsHtml($scope.news.description);

                        var template = angular.element('<a ui-sref="culture">Культурная жизнь \\  </a>');
                        var linkFn = $compile(template);
                        var element = linkFn($scope);

                        $scope.breadCumbs.push(
                            {
                                'title': 'Культурная жизнь',
                                'stateLink': $sce.trustAsHtml(element[0].outerHTML)
                            });

                        $scope.term = $scope.news.taxonomy[0].term_id;
                        template = angular.element('<a ui-sref="culture({cultureID: term})">' + $scope.news.taxonomy[0].name + '\\  </a>');
                        linkFn = $compile(template);
                        element = linkFn($scope);

                        $scope.breadCumbs.push({
                            'title': $scope.news.title,
                            'stateLink': $sce.trustAsHtml(element[0].outerHTML)
                        });

                        $scope.newsIdentity = $scope.news.id;

                        template = angular.element('<a ui-sref="single-culture({cultureID: newsIdentity})">' + $scope.news.title + '</a>');
                        linkFn = $compile(template);
                        element = linkFn($scope);

                        $scope.breadCumbs.push({
                            'title': $scope.news.title,
                            'stateLink': $sce.trustAsHtml(element[0].outerHTML)
                        });


                    }],
                'resolve': {
                    'news': ['newsService','$stateParams',function (newsService,$stateParams) {
                        return newsService.getCultureByID($stateParams.cultureID);
                    }]

                }
            }
        }//views

    });

    $stateProvider.state("organizations", {
        'url': '/organizations/:orgID',
        'views': {
            "main": {
                'templateUrl': 'angular/views/main/organizations.html',
                'controller':
                    ['$scope','$sce','$rootScope', 'newsService','organizations','organizationsCategories',
                        function($scope,$sce,$rootScope,newsService,organizations,organizationsCategories){

                        $scope.organizationsArray = organizations.posts;;

                        $scope.reloadPhoneOrganizations = function () {
                            $scope.organizationsArray.forEach(function (elem,index) {

                                var phones = elem.phones.split(',');
                                $scope.organizationsArray[index].phones = phones;

                            });
                        }

                        $scope.category = {term_id: -1};
                        $scope.childCategory = {term_id: -1};

                        $scope.reloadPhoneOrganizations();

                        $scope.organizationsCategories = organizationsCategories;
                        $scope.$parent.organizationsCategories = $scope.organizationsCategories;
                        $scope.changeParent = function (category) {
                            $scope.category.term_id = category.term_id;
                        }

                        $scope.filterOrganizations = function (category,needStop) {

                            $scope.childCategory.term_id = category.term_id;

                            newsService.getOrganizations(0,12,category).then(function (response) {

                                $scope.organizationsArray.length = 0;
                                $scope.organizationsArray = response.posts.length > 0 ? response.posts : [];
                                $scope.reloadPhoneOrganizations();

                            });



                        }
                        $scope.$parent.changeParent = $scope.changeParent;
                        $scope.$parent.filterOrganizations = $scope.filterOrganizations;
                        $scope.$parent.category = $scope.category;
                        $scope.$parent.childCategory = $scope.childCategory;

                    }],
                'resolve': {
                    'organizations': ['newsService',function (newsService) {
                        return newsService.getOrganizations(0,12);
                    }],
                    'organizationsCategories':['newsService',function (newsService) {
                            return newsService.getCategories('organizationstax');
                        }
                    ]


                }
            }
        }//views

    });

    $stateProvider.state("single-organization", {
        'url': '/single-organization/:orgID',
        'views': {
            "main": {
                'templateUrl': 'angular/views/single/singleOrganization.html',
                'controller':
                    ['$scope','$compile','$sce','$rootScope', 'newsService','organization','organizationsCategories',
                        function($scope,$compile,$sce,$rootScope,newsService,organization,organizationsCategories){

                        if(organization.posts.length != 0){

                            $scope.organization = organization.posts[0];



                            $scope.organizationsCategories = organizationsCategories;

                            $scope.parentCategory = $scope.organization.taxonomy[0].parent;
                            $scope.childCategory = $scope.organization.taxonomy[0].term_id;

                            $scope.organization.phones = $scope.organization.phones.split(',');
                            $scope.organization.description = $sce.trustAsHtml($scope.organization.description);
                            $scope.errorNotFound = false;
                        }
                        else{
                            $scope.errorNotFound = true;
                        }


                    }],
                'resolve': {
                    'organization': ['newsService','$stateParams',function (newsService,$stateParams) {
                        return newsService.getOrganizationByID($stateParams.orgID);
                    }],
                'organizationsCategories':['newsService',function (newsService) {
                        return newsService.getCategories('organizationstax');
                    }]

                }
            }
        }//views

    });

    $stateProvider.state("tape", {
        'url': '/tape',
        'views': {
            "main": {
                'templateUrl': 'angular/views/main/tape.html',
                'controller':
                    ['$scope','$sce','$rootScope', 'newsService','userService','tape','commentsCount','tapePostsCount',
                        function($scope,$sce,$rootScope,newsService,userService,tape,commentsCount,tapePostsCount){
                                $scope.tape = tape.posts;
                                console.log('tape', $scope.tape );

                                $scope.tape.forEach(function (elem,index) {

                                    $scope.tape[index].contentStrict = 200;

                                });

                                $scope.newPosts = [];

                                $scope.message = '';
                                $scope.commentsCount = commentsCount;
                                $scope.Math = window.Math;

                                $scope.nowPosts = $scope.tape.length;

                                $scope.news = {
                                    content: ''
                                };

                                $scope.msgChange = function (m) {
                                    $scope.message =  m ;
                                };

                                $scope.contentChange = function (content) {
                                    $scope.news.content = content;
                                }

                                $scope.addComment = function (postId,comments) {

                                    if($scope.message.trim().length <= 0)
                                        return;

                                    newsService.addComment($scope.user,$scope.message ,postId ).then(function (response) {

                                        if(response.type == 'success'){

                                          comments.push({
                                              'avatar': $scope.user.image,
                                              'login':  $scope.user.login,
                                              'firstName': $scope.user.lastName,
                                              'lastName': $scope.user.firstName,
                                              'text' : $scope.message.trim(),

                                              'time': {
                                                  'days' : 0,
                                                  'hours' : 0,
                                                  'minutes': 0
                                              }

                                          });

                                            $scope.msgChange('');

                                        }//if
                                        else{
                                            alert(response.message);
                                        }//else

                                    });

                                }//addComment()

                                $scope.addLike = function (post,comment) {

                                    if(!$scope.isUserInfoShow)
                                        return;


                                    newsService.addLike($scope.user,post.id,comment).then(function (response) {

                                        if(response.status == 'success'){

                                            post.likes = response.likes;
                                            post.likesCount = response.likes;

                                            if(post.likeActive)
                                                post.likeActive = false;
                                            else
                                                post.likeActive = true;
                                        }//if
                                        else{
                                            console.log('response',response);
                                        }//else

                                    });

                                }//addLike()

                                $scope.moreComments = function (post) {

                                        newsService.getMoreComments(post,post.comments.allCount,commentsCount).then(function (response) {

                                            if(response.posts){

                                                console.log('response',response);

                                                [].forEach.call(response.posts,function (elem,index) {
                                                    post.comments.posts.splice(index,0,elem);
                                                });

                                            }//if

                                        });

                                }//moreComments

                                $scope.AddUserNews = function(){

                                    userService.AddNews($scope.news).then(function(response){

                                        console.log('add response', response);

                                        if(response.status == 'success'){

                                            $scope.newPosts.push(response.post.posts[0]);

                                            $scope.message = '';

                                        }//if

                                    });

                                };

                                $scope.moreNews = function () {

                                    newsService.getNews($scope.tape.length,tapePostsCount,undefined,'unews',commentsCount).then(function (response) {

                                        console.log('response',response);

                                        if(response.posts.length != 0){

                                            [].forEach.call(response.posts,function (elem,index) {

                                                elem.contentStrict = 200;
                                                $scope.tape.push(elem);

                                            });

                                        }//if
                                        else{
                                            $scope.nowPosts = 0;
                                        }
                                    });

                                }//moreNews

                        }],

                'resolve': {
                    'tape':['newsService','commentsCount','tapePostsCount',function (newsService,commentsCount,tapePostsCount) {
                        return newsService.getNews(0,tapePostsCount,undefined,'unews',commentsCount);
                    }]
                }//resolve

            }//main

        }//views

    });

    $stateProvider.state("verification", {
        'url': '/verification/:code',
        'views': {
            "main": {
                'templateUrl': 'angular/views/main/verification.html',
                'controller':
                    ['$scope', '$stateParams' , '$state', 'userService', function($scope,$stateParams,$state,userService){

                        //console.log('code',$stateParams.code);
                        userService.verify($stateParams.code).then(function(verifyResult){

                            console.log(verifyResult);
                            $scope.VerifyMessage = '';

                            if(verifyResult.status == 'success'){
                                $scope.VerifyMessage = 'Вы прошли верификацию! Воспользуйтесь авторизацией!';
                                //$state.go('home',{'reload':true});
                            }//if
                            else{
                                $scope.VerifyMessage = 'Вы не прошли верификацию!';
                            }//else

                        });


                    }]
            }
        }//views

    });

    $stateProvider.state("account", {
        'url': '/account',
        'views': {
            "main": {
                'templateUrl': 'angular/views/user/profile.html',
                'controller':
                    ['$scope', '$sce', '$stateParams' , '$state','newsService', 'userService','commentsCount','tapePostsCount','user','wall', function($scope,$sce,$stateParams,$state,newsService,userService,commentsCount,tapePostsCount,user,wall){

                        $scope.$parent.news = {
                            content: ''
                        };

                        $scope.$parent.message = '';

                        $scope.$parent.msgChange = function (m) {
                            $scope.$parent.message =  m ;
                        };

                        $scope.$parent.addComment = function (postId,comments) {

                            if($scope.$parent.message.trim().length <= 0)
                                return;

                            newsService.addComment($scope.userProfile,$scope.$parent.message ,postId ).then(function (response) {

                                if(response.type == 'success'){

                                    comments.push({
                                        'avatar': $scope.userProfile.image,
                                        'login':  $scope.userProfile.login,
                                        'firstName': $scope.userProfile.lastName,
                                        'lastName': $scope.userProfile.firstName,
                                        'text' : $scope.$parent.message.trim(),

                                        'time': {
                                            'days' : 0,
                                            'hours' : 0,
                                            'minutes': 0
                                        }

                                    });

                                    $scope.$parent.msgChange('');

                                }//if
                                else{
                                    alert(response.message);
                                }//else

                            });

                        }//addComment()

                        $scope.$parent.addLike = function (post,comment) {

                            if(!$scope.isUserInfoShow)
                                return;


                            newsService.addLike($scope.userProfile,post.id,comment).then(function (response) {

                                if(response.status == 'success'){

                                    post.likes = response.likes;
                                    post.likesCount = response.likes;

                                    if(post.likeActive)
                                        post.likeActive = false;
                                    else
                                        post.likeActive = true;
                                }//if
                                else{
                                    console.log('response',response);
                                }//else

                            });

                        }//addLike()

                        $scope.$parent.contentChange = function (content) {
                            $scope.$parent.news.content = content;
                        }

                        $scope.userProfile = user.user;

                        $scope.newPosts = [];

                        $scope.userProfile.birthday = {

                            value: new Date($scope.userProfile.birthday)

                        };

                        $scope.birthdayChange = function (b) {
                            $scope.userProfile.birthday.value = b;
                            console.log('birthday',$scope.userProfile.birthday);
                        }

                        $scope.Save = function () {

                            //console.log('userProfile',$scope.userProfile);

                            userService.updateUser($scope.userProfile).then(function (response) {

                                console.log('response update user',response);

                            });

                        };

                        if($scope.userProfile == undefined)
                            $state.go('state404',{reload: true});

                        console.log('user',$scope.userProfile);

                        $scope.isFriend = $scope.userProfile.isFriend;

                        //Стена пользователя

                        for(var i = 0; i < wall.userWall.posts.length ; i++){

                            wall.userWall.posts[i].contentStrict = 200;

                        }//for i

                        $scope.$parent.wall = wall.userWall;
                        $scope.$parent.newPosts = $scope.newPosts;

                        $scope.nowPosts = wall.userWall.posts.length;
                        $scope.$parent.nowPosts = $scope.nowPosts;

                        $scope.$parent.message = '';

                        $scope.$parent.msgChange = function (m) {
                            $scope.message =  m ;
                        };

                        //Достать больше комментариев для записи
                        $scope.$parent.moreComments = function (post) {

                            newsService.getMoreComments(post,post.comments.allCount,commentsCount).then(function (response) {

                                if(response.posts){

                                    console.log('response',response);

                                    [].forEach.call(response.posts,function (elem,index) {
                                        post.comments.posts.splice(index,0,elem);
                                    });

                                }//if

                            });

                        }//moreComments

                        $scope.$parent.moreNews = function () {

                            userService.getUserWall(
                                $stateParams.login,
                                tapePostsCount,
                                $scope.nowPosts,
                                commentsCount
                            ).then(function (response) {

                                console.log('response',response);

                                if(response.userWall.posts.length != 0){

                                    [].forEach.call(response.userWall.posts,function (elem,index) {

                                        elem.contentStrict = 200;
                                        $scope.$parent.wall.posts.push(elem);

                                    });
                                    $scope.nowPosts += response.userWall.posts.length;
                                    $scope.$parent.nowPosts = $scope.nowPosts;
                                    console.log('nowPosts',$scope.$parent.nowPosts);

                                }//if
                                else{
                                    $scope.$parent.nowPosts += 10;
                                    console.log('nowPosts',$scope.$parent.nowPosts);
                                }
                            });

                        }//moreNews
                        $scope.$parent.commentsCount = commentsCount;

                        console.log('wall',$scope.wall);

                        for(var i = 0; i < $scope.wall.length ; i++){

                            $scope.wall[i].description = $sce.trustAsHtml($scope.wall[i].description);

                        }//for i

                        $scope.$parent.addComment = function (postId,comments) {

                            if($scope.message.trim().length <= 0)
                                return;

                            newsService.addComment($scope.user,$scope.message ,postId ).then(function (response) {

                                if(response.type == 'success'){

                                    comments.push({
                                        'avatar': $scope.user.image,
                                        'login':  $scope.user.login,
                                        'firstName': $scope.user.lastName,
                                        'lastName': $scope.user.firstName,
                                        'text' : $scope.message.trim(),

                                        'time': {
                                            'days' : 0,
                                            'hours' : 0,
                                            'minutes': 0
                                        }

                                    });

                                    $scope.msgChange('');

                                }//if
                                else{
                                    alert(response.message);
                                }//else

                            });

                        }//addComment()

                        $scope.$parent.addLike = function (post,comment) {

                            if(!$scope.isUserInfoShow)
                                return;


                            newsService.addLike($scope.user,post.id,comment).then(function (response) {

                                if(response.status == 'success'){

                                    post.likes = response.likes;
                                    post.likesCount = response.likes;

                                    if(post.likeActive)
                                        post.likeActive = false;
                                    else
                                        post.likeActive = true;
                                }//if
                                else{
                                    console.log('response',response);
                                }//else

                            });

                        }//addLike()

                        $scope.$parent.AddUserNews = function(){

                            userService.AddNews($scope.news).then(function(response){

                                console.log('add response', response);

                                if(response.status == 'success'){

                                    $scope.$parent.newPosts.push(response.post.posts[0]);

                                    $scope.message = '';

                                }//if

                            });

                        };

                    }],
                'resolve':{

                    'user': ['userService', function(userService){
                        return userService.getUserByLogin(userService.isAuthorized().login);

                    }],

                    'wall': ['userService','commentsCount', function(userService,commentsCount){
                        return userService.getUserWall(userService.isAuthorized().login,10,0,commentsCount);

                    }]

                }//resolve
            }
        }//views

    });

    $stateProvider.state("subscribers", {
        'url': '/subscribers',
        'views': {
            "main": {
                'templateUrl': 'angular/views/user/subscribers.html',
                'controller':
                    ['$scope', '$stateParams' , '$state', 'userService', 'subscribers', function($scope,$stateParams,$state,userService,subscribers){


                        componentHandler.upgradeAllRegistered();

                        $scope.subscribers = subscribers;
                        console.log('subs',$scope.subscribers);

                    }],
                'resolve':{

                    'subscribers': ['userService',function GetSubscribers(userService){ return userService.GetUserFriendsRequest(); }]

                }//resolve
            }
        }//views

    });

    $stateProvider.state("user", {
        'url': '/user/:login',
        'views': {
            "main": {
                'templateUrl': 'angular/views/user/account.html',
                'controller':
                    ['$scope', '$sce', '$stateParams' , '$state','commentsCount', 'tapePostsCount', 'userService','newsService','user','wall', function($scope,$sce,$stateParams,$state,commentsCount,tapePostsCount,userService,newsService,user,wall){

                        if(user.status != 'success'){
                            $state.go('state404',{'reload': true});
                        }

                        $scope.userProfile = user.user;
                        console.log('user',$scope.userProfile);

                        $scope.isFriend = $scope.userProfile.isFriend;

                        //Стена пользователя

                        for(var i = 0; i < wall.userWall.posts.length ; i++){

                            wall.userWall.posts[i].contentStrict = 200;

                        }//for i

                        $scope.$parent.wall = wall.userWall;
                        $scope.nowPosts = wall.userWall.posts.length;
                        $scope.$parent.nowPosts = $scope.nowPosts;

                        $scope.$parent.message = '';

                        $scope.$parent.msgChange = function (m) {
                            $scope.message =  m ;
                        };

                        //Достать больше комментариев для записи
                        $scope.$parent.moreComments = function (post) {

                            newsService.getMoreComments(post,post.comments.allCount,commentsCount).then(function (response) {

                                if(response.posts){

                                    console.log('response',response);

                                    [].forEach.call(response.posts,function (elem,index) {
                                        post.comments.posts.splice(index,0,elem);
                                    });

                                }//if

                            });

                        }//moreComments

                        $scope.$parent.moreNews = function () {

                            userService.getUserWall(
                                $stateParams.login,
                                tapePostsCount,
                                $scope.nowPosts,
                                commentsCount
                            ).then(function (response) {

                                console.log('response',response);

                                if(response.userWall.posts.length != 0){

                                    [].forEach.call(response.userWall.posts,function (elem,index) {

                                        elem.contentStrict = 200;
                                        $scope.$parent.wall.posts.push(elem);

                                    });
                                    $scope.nowPosts += response.userWall.posts.length;
                                    $scope.$parent.nowPosts = $scope.nowPosts;
                                    console.log('nowPosts',$scope.$parent.nowPosts);

                                }//if
                                else{
                                    $scope.$parent.nowPosts += 10;
                                    console.log('nowPosts',$scope.$parent.nowPosts);
                                }
                            });

                        }//moreNews
                        $scope.$parent.commentsCount = commentsCount;

                        console.log('wall',$scope.wall);

                        for(var i = 0; i < $scope.wall.length ; i++){

                            $scope.wall[i].description = $sce.trustAsHtml($scope.wall[i].description);

                        }//for i

                        $scope.$parent.addComment = function (postId,comments) {

                            if($scope.message.trim().length <= 0)
                                return;

                            newsService.addComment($scope.user,$scope.message ,postId ).then(function (response) {

                                if(response.type == 'success'){

                                    comments.push({
                                        'avatar': $scope.user.image,
                                        'login':  $scope.user.login,
                                        'firstName': $scope.user.lastName,
                                        'lastName': $scope.user.firstName,
                                        'text' : $scope.message.trim(),

                                        'time': {
                                            'days' : 0,
                                            'hours' : 0,
                                            'minutes': 0
                                        }

                                    });

                                    $scope.msgChange('');

                                }//if
                                else{
                                    alert(response.message);
                                }//else

                            });

                        }//addComment()

                        $scope.$parent.addLike = function (post,comment) {

                            if(!$scope.isUserInfoShow)
                                return;


                            newsService.addLike($scope.user,post.id,comment).then(function (response) {

                                if(response.status == 'success'){

                                    post.likes = response.likes;
                                    post.likesCount = response.likes;

                                    if(post.likeActive)
                                        post.likeActive = false;
                                    else
                                        post.likeActive = true;
                                }//if
                                else{
                                    console.log('response',response);
                                }//else

                            });

                        }//addLike()

                        //console.log('wall',wall)

                    }],
                'resolve':{
                        'user': ['userService','$stateParams', function(userService,$stateParams){

                            return userService.getUserByLogin($stateParams.login);

                        }],

                        'wall': ['userService','$stateParams','commentsCount', function(userService,$stateParams,commentsCount){

                            return userService.getUserWall($stateParams.login,10,0,commentsCount);

                        }]

                }//resolve

            }//main

        }//views

    });

    $stateProvider.state("state404", {
        'url': '/error',
        'views': {
            "main": {
                'templateUrl': 'angular/views/main/404NotFound.html',
                'controller':
                    ['$scope', function($scope){


                    }]

            }//main

        }//views

    });

    $stateProvider.state("dialog", {
        'url': '/dialog/:user',
        'views': {
            "main": {
                'templateUrl': 'angular/views/user/dialog.html',
                'controller':
                    ['$scope', '$sce', '$stateParams' , '$state', 'userService', 'user','messages', function($scope,$sce,$stateParams,$state,userService,user,messages){


                        $scope.whom = user.user;
                        $scope.messages.length = 0;

                        for(var i = 0; i < messages.dialog.length; i++){
                            $scope.messages.push(messages.dialog[i]);
                        }//for

                        console.log('messages', $scope.messages);


                        //console.log($scope.whom);
                        //console.log('messages',messages);

                    }],
                'resolve':{

                    'user': ['userService','$stateParams', function(userService,$stateParams){

                        return userService.getUserByLogin($stateParams.user);

                    }],
                    'messages':['messageService','$stateParams', function(messageService,$stateParams){

                        return messageService.GetMessages($stateParams.user);

                    }]


                }//resolve

            }//main

        }//views

    });

    $stateProvider.state("dialogs", {
        'url': '/dialogs',
        'views': {
            "main": {
                'templateUrl': 'angular/views/user/dialogList.html',
                'controller':
                    ['$scope', '$sce', '$stateParams' , '$state', 'messageService','dialogs', function($scope,$sce,$stateParams,$state,messageService,dialogs){

                        $scope.dialogs = dialogs.dialogs;
                        console.log('dialogs',$scope.dialogs );

                    }],
                'resolve':{

                    'dialogs': ['messageService',function(messageService){

                        return messageService.getDialogs();

                    }]

                }//resolve

            }//main

        }//views

    });

}//GetApplicationConfig

var app = angular.module('anadir', [
    'anadir.filters',
    'anadir.directives',
    'anadir.controllers',
    'anadir.services',
    'anadir.constants',
    'anadir.animation',
    'ui.router',
    'chieffancypants.loadingBar',
    'LocalStorageModule',
    'ngAnimate'
]).config(['$stateProvider','$urlRouterProvider','$locationProvider','cfpLoadingBarProvider','localStorageServiceProvider',GetApplicationConfig]);

app.run(
    [          '$rootScope', '$state', '$stateParams',
        function ($rootScope,   $state,   $stateParams) {

            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;

        }//
    ]);
