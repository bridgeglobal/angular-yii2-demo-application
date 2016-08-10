(function() {
    'use strict';

    angular.module('app')
        .config(['$stateProvider', '$urlRouterProvider', '$locationProvider', function($stateProvider, $urlRouterProvider, $locationProvider) {

            $urlRouterProvider
                .when('/', '/login')
                .otherwise('/login');

            $stateProvider.state('login', {
                    url: '/login',
                    templateUrl: 'app/login/login.html'
                })
                .state('forgot-password', {
                    url: '/forgot-password',
                    templateUrl: 'app/forgot-password/forgot-password.html',
                    controller: 'ForgotCtrl'
                })
                .state('reset-password', {
                    url: '/reset-password',
                    templateUrl: 'app/forgot-password/reset-password.html',
                    controller: 'ResetPasswordCtrl'
                })

                .state('post-people', {
                    url: '/post-people',
                    templateUrl: 'app/post-people/list.html'
                })
                .state('post-people/:id/view', {
                    url: '/post-people/:id/view',
                    templateUrl: 'app/post-people/view.html',
                    controller: 'PostPeopleViewCtrl'
                })
                .state('post-people/:type/:id', {
                    url: '/post-people/:type/:id',
                    templateUrl: 'app/post-people/add_edit.html',
                    controller: 'PostPeopleAddEditCtrl'
                });

        }]);

})();
