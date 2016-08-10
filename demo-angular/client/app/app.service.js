(function() {
    'use strict';

    angular.module('app')
        .factory('appService', ['$http', '$rootScope', '$localStorage', '$mdToast', '$document', appService]);

    /**
     * Service to handle common operations
     *
     * @author Harish K
     * @date 09-07-2016
     */
    function appService($http, $rootScope, $localStorage, $mdToast, $document) {

        return {

            //Service which handles notification
            showMessage: function(msg) {

                var vm = this;
                var last = {
                    bottom: true,
                    top: false,
                    left: true,
                    right: false
                };
                var toastPosition = angular.extend({}, last);

                var current = toastPosition;

                if (current.bottom && last.top) current.top = false;
                if (current.top && last.bottom) current.bottom = false;
                if (current.right && last.left) current.left = false;
                if (current.left && last.right) current.right = false;

                last = angular.extend({}, current);
                var pos = Object.keys(toastPosition)
                    .filter(function(pos) {
                        return toastPosition[pos];
                    })
                    .join(' ');
                $rootScope.message = msg;
                $mdToast.show({
                    controller: 'NotificationCtrl',
                    templateUrl: 'toast-template.html',
                    parent: $document[0].querySelector('#toastBounds'),
                    hideDelay: 6000,
                    position: pos
                });
            },

            //Service which handles ng-tasty listing
            showList: function(params, $scope, url, header, name) {
                var header = (!angular.isUndefined(header)) ? header : $scope.header;
                var paramObj = JSON.parse('{"' + params.replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
                paramObj.page = (paramObj.page == 0) ? 1 : paramObj.page;
                $scope.page = paramObj.count * (paramObj.page - 1) + 1;
                return $http({
                    method: 'POST',
                    url: $rootScope.server_url + '/web/' + url,
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    data: { userId: $localStorage.userInfo.id, authKey: $localStorage.userInfo.authentication_key, params: paramObj }
                }).then(function(response) {
                    if (!angular.isUndefined(name))
                        $scope.records = response.data.data;
                    else
                        $scope.rows = response.data.data;

                    return {
                        'rows': response.data.data,
                        'header': header,
                        'pagination': response.data.pagination,
                        'sortBy': response.data.sortBy,
                        'sortOrder': response.data.sortOrder
                    }
                });
            },

            //Service which handles all other api calls
            apiRequest: function(params, url) {
                return $http({
                    method: 'POST',
                    url: $rootScope.server_url + '/web/' + url,
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    data: { params: params, userId: $localStorage.userInfo.id, authKey: $localStorage.userInfo.authentication_key }
                });
            },

            //Check email exist
            emailExists: function(email, id) {
                return $http({
                    method: 'POST',
                    url: $rootScope.server_url + '/web/emailExists',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    data: { userId: $localStorage.userInfo.id, authKey: $localStorage.userInfo.authentication_key, params: { user_id: id, email: email } }
                });
            },

            //add more fields
            addField: function(type, $scope) {
                if (type == 'Phone') {
                    var newItemNo = $scope.phoneData.length + 1;
                    $scope.phoneData.push({ value_type: 'Work' });
                } else if (type == 'Email') {
                    var newItemNo = $scope.emailData.length + 1;
                    $scope.emailData.push({ value_type: 'Work' });
                } else if (type == 'Url') {
                    var newItemNo = $scope.webData.length + 1;
                    $scope.webData.push({ value_type: 'Select', url_type: 'Select' });
                } else if (type == 'Address') {
                    var newItemNo = $scope.addressData.length + 1;
                    $scope.addressData.push({ address_type: 'Work' });
                }
            },

            //remove fields
            removeField: function(type, index, $scope) {
                if (type == 'Phone') {
                    $scope.phoneData.splice(index, 1);
                } else if (type == 'Email') {
                    $scope.emailData.splice(index, 1);
                } else if (type == 'Url') {
                    $scope.webData.splice(index, 1);
                } else if (type == 'Address') {
                    $scope.addressData.splice(index, 1);
                }

            },
        
            // to get the difference of two arrays
            diffArray: function(areas, selected_areas) {
                var result = areas.filter(function(item1) {
                    for (var i in selected_areas) {
                        if (item1.id === selected_areas[i].id) {
                            return false;
                        }
                    };
                    return true;
                });
                return result;
            }


        }
    }
})();
