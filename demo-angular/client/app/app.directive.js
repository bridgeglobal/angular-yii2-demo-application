angular.module('app')

/**
 * A validation directive to ensure that the model contains a unique email address
 * @param  Users service to provide access to the server's user database
 */
.directive('uniqueEmail', ["appService", function(appService) {
    return {
        require: 'ngModel',
        restrict: 'A',
         scope: {
            id: "@userid",
        },
        link: function(scope, el, attrs, ctrl) {

            ctrl.$parsers.push(function(viewValue) {
              console.log(scope.id);

                var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;  
                if(viewValue.match(mailformat)){
                var $con = appService.emailExists(viewValue,scope.id);
                $con.success(function(response, status) {
                    console.log(response);
                    if (response.success == 1) {
                        ctrl.$setValidity('uniqueEmail', true);

                    } else {
                        ctrl.$setValidity('uniqueEmail', false);
                    }

                });
                return viewValue;
                }
            });
        }
    };
}])

/**
* Href tag for button 
* @param $location , path to redirect
*/
.directive('goClick', ["$location", function($location) {
    return {

        link: function(scope, element, attrs) {
            var path;

            attrs.$observe('goClick', function(val) {
                path = val;
            });

            element.bind('click', function() {
                scope.$apply(function() {
                    $location.path(path);
                });
            });
        }
    };
}]);
