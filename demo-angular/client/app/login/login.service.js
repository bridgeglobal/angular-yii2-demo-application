(function () {
    'use strict';
 
    angular
        .module('app.login')
        .factory('loginService',['$http','$rootScope',loginService]);
 
         /* Service to handle login operations
            Author : Harish K
            Date   : 03/07/2016
         */
         function loginService($http,$rootScope) {
            return {

                //function to check valid login or not
                login: function(data) {
                    return $http({
                        method: 'POST', 
                        url:  $rootScope.server_url+'/login',
                        headers : {'Content-Type': 'application/x-www-form-urlencoded'},
                        data : {params: data}
                    })
                },

                //function to manage edit Profile
                editProfile : function(details,authKey,userId) {
                    return $http({
                        method : 'POST',
                        url : $rootScope.server_url+'/editProfile',
                        headers : {'Content-Type': 'application/x-www-form-urlencoded'},
                        data : { userId:userId, authKey:authKey, params: details}
                    });
                }
            }; 
        
        }
})();