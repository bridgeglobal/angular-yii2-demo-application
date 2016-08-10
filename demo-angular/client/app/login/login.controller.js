(function() {
    'use strict';

    angular.module('app.login')
        .controller('LoginCtrl', ['$scope', '$location', '$rootScope', '$localStorage', 'appService', 'loginService', LoginCtrl]);

    /**
     * Controller which handles login actions
     *
     * @url /login
     *
     * @author Bridge Global
     * @date 08/10/2016
     */
    function LoginCtrl($scope, $location, $rootScope, $localStorage, appService, loginService) {
        var vm = this;

        vm.username = '';
        vm.password = '';
        vm.submitForm = submitForm;
        vm.clearMsg = clearMsg;

        //function to check to valid login or not
        function submitForm() {

            //calling loginservice
            var con = loginService.login({ username: vm.username, password: vm.password });
            con.success(function(response, status) {
                if (response.success) {

                    //setting local storage values
                    $localStorage.userInfo = response.data.userInfo;
                    $localStorage.userInfo.authentication_key = '1v1sc39q7bthllf9kaj2n087ll5';
                    $localStorage.userPermissions = response.data.userPermissions;
                    $rootScope.permissions = $localStorage.userPermissions;
                    $rootScope.userInfo = $localStorage.userInfo;
                    $location.path('/post-people');

                } else {
                    $scope.showError = true;
                }
            });

        };

        //function to clear error message
        function clearMsg() {
            $scope.showError = false;
        }
    }

})();
