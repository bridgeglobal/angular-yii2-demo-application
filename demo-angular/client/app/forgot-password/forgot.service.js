(function () {
    'use strict';

    angular.module('app.forgot')
     	.factory('forgotService',['$http','$rootScope','$localStorage',forgotService]);

    /**
    * Service to handle post people operations
    * Author :  Harish
    * Date   :  06/15/2016
    */

    function forgotService($http,$rootScope,$localStorage){

    	return {

            // forgot password
            forgotPassword : function(data) {
                return $http({
                    method : 'POST',
                    url : $rootScope.server_url+'/forgotPassword',
                    headers : {'Content-Type': 'application/x-www-form-urlencoded'},
                    data: { params:data }
                });
            },

            // forgot password
            resetPassword : function(data) {
                return $http({
                    method : 'POST',
                    url : $rootScope.server_url+'/newPassword',
                    headers : {'Content-Type': 'application/x-www-form-urlencoded'},
                    data: { params:data }
                });
            },


    	}
    }
})(); 