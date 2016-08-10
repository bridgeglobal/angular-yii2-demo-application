(function () {
    'use strict';
    
    angular
        .module('app.notification')
        .controller('NotificationCtrl', ['$scope', '$mdToast', NotificationCtrl]);
    
    function NotificationCtrl($scope,$mdToast) {
        $scope.closeToast = function() {
            $mdToast.hide();
        }; 

    }

})();
