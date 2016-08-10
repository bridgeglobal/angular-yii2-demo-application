(function () {
    'use strict';
    angular.module('app.forgot')
        .controller('ForgotCtrl',['$scope','toaster','forgotService',ForgotCtrl])
        .controller('ResetPasswordCtrl',['$scope','$location','toaster','forgotService', ResetPasswordCtrl]);

    /*
        Controller to perform forgot password operations
        Author : Bridge Global
        Date : 5/31/2015
    */
    function ForgotCtrl($scope,toaster,forgotService) {
        var original;
        $scope.user = {
            email: ''
        }   
        var original = angular.copy($scope.user);
  
        //function to manage forgot password functionality
        $scope.submitForm = function() {
          var $con = forgotService.forgotPassword($scope.user);
          $con.success(function(data,status) {
            if(data.success==1){
                toaster.pop('success', "Success", data.message);
                $scope.user = angular.copy(original);
                $scope.forgot_form.$setPristine();
                $scope.forgot_form.$setUntouched();
            }
            else{
                 console.log('error')   ;
                 toaster.pop('error', "Error", data.message);
            }

          });
        };                 
    }

    /*
        Controller to perform reset password operations
        Author : Bridge Global
        Date : 5/31/2015
    */
    function ResetPasswordCtrl($scope,$location,toaster,forgotService){
        $scope.user = {
            password: ''
        }  
        var original = angular.copy($scope.user); 

        //function to manage reset password functionality       
        $scope.resetPassword = function(){
            $scope.user.token = $location.search()['t'];

            //calling service to reset password
            var $con = forgotService.resetPassword($scope.user);
            $con.success(function(data,status) {
                if(data.success==1){
                   toaster.pop('success', "Success",data.message);
                        setTimeout(function(){ 
                            $location.path('/login') ;
                        }, 1000);
                }
                else{
                     toaster.pop('error', "Error",data.message);
                }
            });
        };
    }

})(); 