/**
 * Created by Алексей on 08.05.2017.
 */


module.exports = function (grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),
        concat: {

            dist:{

                src:[
                    'scripts/jquery.min.js',
                    'scripts/jR3DCarousel.min.js',
                    'scripts/jquery.touchSwipe.min.js',
                    'angular/lib/angular.js',
                    'angular/lib/angular-local-storage.js',
                    'angular/lib/angular-ui-router.min.js',
                    'angular/lib/loading-bar.js',
                    'angular/lib/functions.js',
                    'angular/application.js',
                    'angular/services/userService.js',
                    'angular/services/newsService.js',
                    'angular/services/pointsService.js',
                    'angular/services/formValidator.js',
                    'angular/services/messageService.js',
                    'angular/filters/descriptionFilter.js',
                    'angular/filters/categoryFilter.js',
                    'angular/controllers/mainController.js',
                    'angular/controllers/userController.js',
                    'angular/controllers/profileController.js',
                    'angular/controllers/messageController.js',
                    'scripts/owl.carousel.min.js',
                    'angular/lib/angular-animate.js',

                ],
                dest: 'scripts/production.js'

            }//dist

        },//concat

        uglify: {

            build:{

                src: 'scripts/production.js',
                dest: 'scripts/production.min.js'

            }//build

        },//uglify

        cssmin: {
            options: {
                mergeIntoShorthands: false,
                roundingPrecision: -1
            },
            target: {
                files: {
                    'styles/output.css': [
                        'styles/flexboxgrid.css',
                        'styles/fontello.css',
                        'styles/style.css',
                        'styles/owl.carousel.min.css',
                        'styles/owl.theme.default.min.css',
                        'styles/loading-bar.css'
                    ]
                }
            }
        }

    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('default',['concat','uglify','cssmin']);



};