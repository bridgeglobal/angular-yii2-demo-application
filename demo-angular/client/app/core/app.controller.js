(function () {
    'use strict';

    angular.module('app')
    .controller('AppCtrl', [ '$scope', '$rootScope', '$state', '$document', 'appConfig','$localStorage','$location',AppCtrl]) 
    
    /**
    * Overall controller which handles the current application
    *
    * @author :Bridge Global
    * @date : 08/10/2016
    */
    function AppCtrl($scope, $rootScope, $state, $document, appConfig,$localStorage,$location) {

        $rootScope.server_url = "http://localhost/demoyii/web";
        $rootScope.customTheme = {
          iconUp: 'fa fa-angle-up',
          iconDown: 'fa fa-angle-down',
        };
        $rootScope.permissions = $localStorage.userPermissions;
        $rootScope.userInfo = $localStorage.userInfo;
        $scope.pageTransitionOpts = appConfig.pageTransitionOpts;
        $scope.main = appConfig.main;
        $scope.color = appConfig.color;

        $scope.$watch('main', function(newVal, oldVal) {

            if (newVal.menu === 'horizontal' && oldVal.menu === 'vertical') {
                $rootScope.$broadcast('nav:reset');
            }
            if (newVal.fixedHeader === false && newVal.fixedSidebar === true) {
                if (oldVal.fixedHeader === false && oldVal.fixedSidebar === false) {
                    $scope.main.fixedHeader = true;
                    $scope.main.fixedSidebar = true;
                }
                if (oldVal.fixedHeader === true && oldVal.fixedSidebar === true) {
                    $scope.main.fixedHeader = false;
                    $scope.main.fixedSidebar = false;
                }
            }
            if (newVal.fixedSidebar === true) {
                $scope.main.fixedHeader = true;
            }
            if (newVal.fixedHeader === false) {
                $scope.main.fixedSidebar = false;
            }
        }, true);


        $rootScope.$on("$stateChangeSuccess", function (event, currentRoute, previousRoute) {
            $document.scrollTo(0, 0);

        });

        $rootScope.$on('$stateChangeSuccess', function (ev, to, toParams, from, fromParams) {
            $rootScope.backUrl = from.url;
        });
        
        // check whether the user has access right
        $rootScope.$on('$locationChangeStart', function (event,currentRoute) {
            if($location.path()!='/reset-password'&&$location.path()!='/forgot-password'){ 
                if($location.path()=='/login'&&$localStorage.userInfo)
                     $location.path('post-people') ;
                if(!$localStorage.userInfo)
                    $location.path('login') ;
            }
        });

        //Logout 
        $rootScope.logout = function() {
            $localStorage.$reset();
            $location.path('login') ;
        };
  
    }

})(); 