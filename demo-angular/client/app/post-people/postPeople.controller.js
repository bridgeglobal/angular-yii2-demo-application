(function() {
    'use strict';

    angular.module('app.postpeople')
        .controller('PostPeopleCtrl', ['appService', PostPeopleCtrl])
        .controller('PostPeopleAddEditCtrl', ['$scope', '$compile', '$location', 'uiCalendarConfig', '$mdDialog', '$uibModal', '$rootScope', '$localStorage', '$stateParams', 'appService', PostPeopleAddEditCtrl])
        .controller('PostPeopleViewCtrl', ['$scope', '$compile', '$stateParams', '$localStorage', '$uibModal', '$mdDialog', 'uiCalendarConfig', '$filter', 'appService', PostPeopleViewCtrl])
        .controller('ChangeImageCtrl', ['$scope', '$uibModalInstance', 'params', 'appService', ChangeImageCtrl])
        .controller('PostPeopleInstanceCtrl', ['$scope', '$uibModalInstance', 'areaInfo', 'areas', 'composites', 'most', 'moderate', 'least', 'appService', PostPeopleInstanceCtrl])
        .controller('NotesCtrl', ['$scope', '$uibModalInstance', 'params', 'appService', NotesCtrl]);

    /**
     * Controller to handle post people listing
     *
     * @url /post-people
     *
     * @Author :  Bridge Global
     * @Date   :  06/03/2016
     */

    function PostPeopleCtrl(appService) {

        var vm = this;

        // Post people server side listing using ng-tasty plugin
        vm.status_yn = '1';
        vm.header = [{ id: "Sl No" }, { name: "Full Name" }, { town: "Town" }, { telephone: "Phone Number" }, { username: "Username" }, { email: "Email" }];
        vm.filterBy = {
            'name': ''
        };
        vm.notSortBy = ['id', 'actions'];

        vm.reloadCallback = reloadCallback;
        vm.getResource = getResource;
        vm.getPostpeopleByStatus = getPostpeopleByStatus;

        function reloadCallback() {}

        //list postpeople
        function getResource(params, paramsObj) {
            params = params + '&status_yn=' + vm.status_yn;
            return appService.showList(params, vm, 'listPostpeople');
        }

        //list postpeople by active or inactive status
        function getPostpeopleByStatus() {
            vm.filterBy = {
                'name': '',
                'id': Math.random()
            }
            vm.reloadCallback();
        }

    }


    /**
     * Controller to add or edit post person
     *
     * @url /post-people/add
     *
     * @Author :  Bridge Global
     * @Date   :  06/03/2016
     */

    function PostPeopleAddEditCtrl($scope, $compile, $location, uiCalendarConfig, $mdDialog, $uibModal, $rootScope, $localStorage, $stateParams, appService) {

        $scope.action = $stateParams.type;
        $scope.heading = ($scope.action) == "add" ? "Add Post Person" : "Edit Post Person";
        $scope.image = {};
        $scope.activity = {};
        $scope.areaInfo = { type: '0' };
        $scope.postPeople = {
            status_yn: 1,
            payment_method: '1',
            driver_yn: '1',
            gps_yn: '1',
            franchisee_id: $localStorage.userInfo.franchisee_id,
            image_type: 'img',
            link: ''
        };
        $scope.areas = new Array();
        $scope.rows = new Array();
        $scope.webDataOptions = {};
        $scope.webData = [{ value_type: 'Select', url_type: 'Select' }];
        $scope.emailData = [{ value_type: 'Work' }];
        $scope.phoneData = [{ value_type: 'Work' }];
        $scope.addressData = [{ address_type: 'Work' }];
        $scope.mostSuitable = [];
        $scope.moderatelySuitable = [];
        $scope.leastSuitable = [];
        $scope.urlTypes = ['Select', 'Website', 'Social'];
        $scope.urlTypes.Select = ['Select'];
        $scope.urlTypes.Website = ['Select', 'Work', 'Home'];
        $scope.urlTypes.Social = ['Select', 'Twitter', 'Facebook', 'Google', 'Pinterest', 'Instagram', 'RSS'];

        //Get all composites for the franchisee
        var $con = appService.apiRequest({}, 'getCompositeNames');
        $con.success(function(result) {
            $scope.composites = result.data;

        });


        //Settings and operations for post people area selection 
        $scope.header = [
            { "composite_name": "Composite Name" },
            { "area_name": "Area Name" },
            { "area_number": "Area Number" },
            { "postcode": "Postal Code" },
            { "total_households": "Total Households" },
            { "actions": "Actions" }
        ];

        $scope.resourceMost = { "header": $scope.header, "rows": $scope.mostSuitable };
        $scope.resourceModerate = { "header": $scope.header, "rows": $scope.moderatelySuitable };
        $scope.resourceLeast = { "header": $scope.header, "rows": $scope.leastSuitable };
        $scope.itemsPerPage = 10;
        $scope.listItemsPerPage = [1, 10, 40, 80];

        //function to refresh the delivery area list
        $scope.reloadList = function() {
            $scope.resourceMost = { "header": $scope.header, "rows": $scope.mostSuitable };
            $scope.resourceModerate = { "header": $scope.header, "rows": $scope.moderatelySuitable };
            $scope.resourceLeast = { "header": $scope.header, "rows": $scope.leastSuitable };

        }

        //function to remove entries from selected areas list
        $scope.removeArea = function(data) {
            var idx = $scope.rows.indexOf(data);
            if ($scope.areaInfo.type == 0) {
                $scope.mostSuitable.splice(idx, 1);
                $scope.reloadList();

            }
            if ($scope.areaInfo.type == 1) {
                $scope.moderatelySuitable.splice(idx, 1);
                $scope.reloadList();

            }
            if ($scope.areaInfo.type == 2) {
                $scope.leastSuitable.splice(idx, 1);
                $scope.reloadList();
            }
        }

        // Function to filter areas
        $scope.filterArray = function(resultArray) {
            var ids = {};
            _.each(resultArray, function(bb) { ids[bb.id] = true; });

            $scope.areas = _.filter($scope.areas, function(val) {
                return !ids[val.id];
            }, resultArray);

        }


        // function to display select area pop up
        $scope.selectAreas = function() {

            //Get all areas for the franchisee
            var data = { pagination: 0 };
            var $con = appService.apiRequest(data, 'listAreas');
            $con.success(function(response) {
                $scope.areas = response.data;
                $scope.filterArray($scope.mostSuitable);
                $scope.filterArray($scope.moderatelySuitable);
                $scope.filterArray($scope.leastSuitable);

                var modalInstance = $uibModal.open({
                    animation: true,
                    templateUrl: 'postPeopleArea.html',
                    controller: 'PostPeopleInstanceCtrl',
                    size: 'lg',
                    resolve: {
                        areaInfo: function() {
                            return $scope.areaInfo;
                        },
                        areas: function() {
                            return $scope.areas;

                        },
                        composites: function() {
                            return $scope.composites;

                        },
                        most: function() {
                            return $scope.mostSuitable;
                        },
                        moderate: function() {
                            return $scope.moderatelySuitable;
                        },
                        least: function() {
                            return $scope.leastSuitable;
                        }
                    }
                });
                modalInstance.result.then(function(response) {
                    if ($scope.areaInfo.type == 0) {
                        $scope.mostSuitable = response;
                        $scope.reloadList();
                    }
                    if ($scope.areaInfo.type == 1) {
                        $scope.moderatelySuitable = response;
                        $scope.reloadList();
                    }
                    if ($scope.areaInfo.type == 2) {
                        $scope.leastSuitable = response;
                        $scope.reloadList();
                    }
                });
            });
        }

        //function to change delivery area type
        $scope.changeAreaType = function(type) {
            $scope.areaInfo.type = type;

        }


        //Calendar settings - Start

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        $scope.changeTo = 'Hungarian';

        //event source that pulls from google.com
        $scope.eventSource = {
            url: "http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic",
            className: 'gcal-event', // an option!
            currentTimezone: 'America/Chicago' // an option!
        };

        //event source that contains custom events on the scope 
        $scope.activity.events = [];

        // event source that calls a function on every view switch
        $scope.eventsF = function(start, end, timezone, callback) {
            var s = new Date(start).getTime() / 1000;
            var e = new Date(end).getTime() / 1000;
            var m = new Date(start).getMonth();
            var events = [{ title: 'Feed Me ' + m, start: s + (50000), end: s + (100000), allDay: false, className: ['customFeed'] }];
            callback(events);
        };

        //function to remove holiday
        $scope.alertOnEventClick = function(event) {
            var confirm = $mdDialog.confirm()
                .title('Remove Holiday')
                .content('Are you sure you want to remove holiday?')
                .ariaLabel('Status')
                .ok('Ok')
                .cancel('Cancel');
            $mdDialog.show(confirm).then(function() {
                //check whether date is exist in the event array
                var index = -1;
                for (var i = 0, len = $scope.activity.events.length; i < len; i++) {
                    if ($scope.activity.events[i].start === event.start._i) {
                        index = i;
                        break;
                    }
                }
                $scope.activity.events.splice(index, 1);
                $scope.renderCalender('myCalendar');
            });
        };

        //function to display apply holiday button
        $scope.alertOnDayClick = function(date, jsEvent, view) {
            //console.log(jsEvent);
            $scope.holiday = date.format();
            var dateExist = false;

            for (var i = 0; i < $scope.activity.events.length; i++) {
                if ($scope.activity.events[i].start == $scope.holiday)
                    dateExist = true;
            }

            if (dateExist)
                $scope.applyHoliday = false;
            else
                $scope.applyHoliday = true;


        };

        // function to add holiday
        $scope.addEvent = function() {

            $scope.activity.events.push({
                title: 'Holiday',
                activity: 'H',
                start: $scope.holiday,
                className: ['openSesame']
            });
            $scope.applyHoliday = false;
            $scope.renderCalender('myCalendar');

        };

        //function to render calendar
        $scope.renderCalender = function(calendar) {
            if (uiCalendarConfig.calendars[calendar]) {
                //uiCalendarConfig.calendars[calendar].fullCalendar('render');
                uiCalendarConfig.calendars[calendar].fullCalendar('removeEventSource', $scope.activity.events);
                uiCalendarConfig.calendars[calendar].fullCalendar('addEventSource', $scope.activity.events);
            }
        };

        //Render Tooltip
        $scope.eventRender = function(event, element, view) {
            element.attr({
                'tooltip': event.title,
                'tooltip-append-to-body': true
            });
            $compile(element)($scope);
        };

        //setting calendar event source
        $scope.eventSources = [
            [], $scope.eventSource, $scope.eventsF
        ];

        //config object
        $scope.uiConfig = {
            calendar: {
                height: 450,
                editable: false,
                selectable: true,
                selectHelper: true,
                eventClick: $scope.alertOnEventClick,
                eventRender: $scope.eventRender,
                dayClick: $scope.alertOnDayClick
            }
        };


        //Delivery location type head - Start
        $scope.querySearch = function(searchText) {

            $scope.params = { name: searchText, pagination: 0 }
            return appService.apiRequest($scope.params, 'listAreas')
                .then(function(result) {
                    return result.data.data.map(function(data) {
                        var value = data.id + ',' + data.composite_name;
                        if (data.region == null)
                            value = value + ','.data.region

                        return {
                            value: value,
                            display: value
                        };
                    });
                })
        }

        // to get the locations
        $scope.selectedItemChange = function(item) {
            var delivererLoc = item;
            if (delivererLoc) {
                delivererLoc = delivererLoc.split(",");
                $scope.postPeople.area_id = delivererLoc[0];
                $scope.postPeople.composite_name = delivererLoc[1];
                $scope.postPeople.region = delivererLoc[2];
            }

        }



        //Save or Edit Post People Data - Start 

        //function to add new post people
        $scope.savePostPeople = function(flow) {

            //check if delivery areas is filled
            if ($scope.mostSuitable.length == 0 && $scope.moderatelySuitable.length == 0 && $scope.leastSuitable == 0) {
                appService.showMessage('Please fill delivery areas');
                return false;
            }

            if (flow.files.length > 0 && $scope.postPeople.image_type == 'img') {
                $rootScope.$broadcast('preloader:active');
                var res = flow.upload();
                $scope.success = function($file, $message, $flow) {
                    $scope.postPeople.link = $message;
                    $scope.saveData();
                }
            } else {
                $scope.postPeople.link = $scope.image.urlField;
                $scope.saveData();
            }
        };

        //function to Save/Edit post people
        $scope.saveData = function() {
            $scope.postPeople.emails = $scope.emailData;
            $scope.postPeople.telephones = $scope.phoneData;
            $scope.postPeople.telephones = $scope.phoneData;
            $scope.postPeople.addresses = $scope.addressData;
            $scope.postPeople.websites = $scope.webData;
            $scope.areaInfo.most = $scope.mostSuitable;
            $scope.areaInfo.moderate = $scope.moderatelySuitable;
            $scope.areaInfo.least = $scope.leastSuitable;

            $con = ($scope.action == "add") ? appService.apiRequest($scope.postPeople, 'createPostpeople') : appService.apiRequest($scope.postPeople, 'savePostpeople');
            $con.success(function(data, status) {
                if (data.success == 0) {
                    appService.showMessage(data.message);
                } else {
                    $scope.areaInfo.postPeopleId = data.data.id;
                    $scope.activity.postPeopleId = data.data.id;
                    $con = appService.apiRequest($scope.areaInfo, 'saveDeliveryAreas');
                    $con = appService.apiRequest($scope.activity, 'saveActivity');
                    appService.showMessage(data.message);
                    $location.path('post-people');
                }
            });

        }


        // Get the details of a particular post people
        if ($stateParams.id) {
            $scope.selectedItem = '';
            var data = { id: $stateParams.id };
            var $con = appService.apiRequest(data, 'editPostpeople');
            $con.success(function(data, status) {
                $scope.areasParams = { pagination: 0, postpeopleId: data.data.basic.id }
                $scope.postPeople = data.data.basic;

                if (data.data.basic.area_id)
                    $scope.selectedItem = data.data.basic.area_id;
                if (data.data.basic.composite_name)
                    $scope.selectedItem += ',' + data.data.basic.composite_name;
                if (data.data.basic.region)
                    $scope.selectedItem += ',' + data.data.basic.region;

                $scope.mostSuitable = data.data.areas.most;
                $scope.reloadList();
                $scope.moderatelySuitable = data.data.areas.moderate;
                $scope.reloadList();
                $scope.leastSuitable = data.data.areas.least;
                $scope.reloadList();
                $scope.activity.events = data.data.activity;
                $scope.emailData = data.data.emails;
                $scope.phoneData = data.data.telephones;
                $scope.addressData = data.data.addresses;
                $scope.webData = data.data.websites;
                $scope.renderCalender('myCalendar');

            });
        }

        //function to add dynamic fields
        $scope.addField = function(type) {
            appService.addField(type, $scope);
        }

        //function to remove dynamic fields
        $scope.removeField = function(type, index) {
            appService.removeField(type, index, $scope);
        }

        //Reset url types sub options
        $scope.urlTypeChange = function(key) {
            $scope.webData[key].value_type = "Select";
        }

        //Reset password
        $scope.resetPassword = function() {
            $('#passwordLabel').html('Password');
            $('#password').removeAttr('disabled');
        }

    }

    /**
     * Controller to view post people details
     *
     * @url post-people/{id}/show
     * @param id, postpeople id
     *
     * @author :  Bridge Global
     * Date   :  06/15/2016
     */

    function PostPeopleViewCtrl($scope, $compile, $stateParams, $localStorage, $uibModal, $mdDialog, uiCalendarConfig, $filter, appService) {

        // Get the details of a particular post people
        $scope.most = new Array();
        $scope.moderate = new Array();
        $scope.least = new Array();
        $scope.flag = false;
        var data = { id: $stateParams.id };
        var con = appService.apiRequest(data, 'editPostpeople');
        con.success(function(response, status) {
            $scope.postPeople = response.data.basic;
            $scope.user_id = response.data.basic.id;
            $scope.flag = true;
            $scope.most = response.data.areas.most;
            $scope.moderate = response.data.areas.moderate;
            $scope.least = response.data.areas.least;
            $scope.activity = response.data.activity;
            $scope.addresses = response.data.addresses;
            $scope.emails = response.data.emails;
            $scope.telephones = response.data.telephones;
            $scope.web = response.data.web;
            $scope.social = response.data.social;
            $scope.resourceMost = { "header": $scope.header, "rows": $scope.most };
            $scope.resourceModerate = { "header": $scope.header, "rows": $scope.moderate };
            $scope.resourceLeast = { "header": $scope.header, "rows": $scope.least };
            $scope.renderCalender('myCalendar');

        });


        $scope.header = [{ composite_name: "Composite Name" }, { area_name: "Area Name" }, { area_number: "Area Number" }, { postal_code: "Postal Code" }, { total_households: "Total Households" }];

        $scope.itemsPerPage = 10;
        $scope.listItemsPerPage = [1, 10, 40, 80];

        $scope.resourceMost = { "header": $scope.header, "rows": $scope.most };
        $scope.resourceModerate = { "header": $scope.header, "rows": $scope.moderate };
        $scope.resourceLeast = { "header": $scope.header, "rows": $scope.least };

        //Activity calendar management - start
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        //event source that pulls from google.com 
        $scope.eventSource = {
            url: "http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic",
            className: 'gcal-event', // an option!
            currentTimezone: 'America/Chicago' // an option!
        };

        // event source that calls a function on every view switch
        $scope.eventsF = function(start, end, timezone, callback) {
            var s = new Date(start).getTime() / 1000;
            var e = new Date(end).getTime() / 1000;
            var m = new Date(start).getMonth();
            var events = [{ title: 'Feed Me ' + m, start: s + (50000), end: s + (100000), allDay: false, className: ['customFeed'] }];
            callback(events);
        };

        $scope.eventSources = [
            [], $scope.eventSource, $scope.eventsF
        ];

        $scope.renderCalender = function(calendar) {
            if (uiCalendarConfig.calendars[calendar]) {
                uiCalendarConfig.calendars[calendar].fullCalendar('addEventSource', $scope.activity);
            }
        };

        // Render Tooltip 
        $scope.eventRender = function(event, element, view) {
            element.attr({
                'tooltip': event.title,
                'tooltip-append-to-body': true
            });
            $compile(element)($scope);
        };

        //config object
        $scope.uiConfig = {
            calendar: {
                height: 450,
                editable: false,
                selectable: true,
                selectHelper: true,
                eventClick: $scope.alertOnEventClick,
                eventRender: $scope.eventRender,
                dayClick: $scope.alertOnDayClick
            }
        };



        // Notes management 
        $scope.headerNote = [{ id: "Sl No" }, { note: "Note" }, { created_at: "Created at" }, { action: "Actions" }];
        $scope.filterBy = {
            'name': ''
        }
        $scope.notSortBy = ['actions', 'id'];

        $scope.getResource = function(params, paramsObj) {
            params = params + '&user_id=' + $scope.user_id;
            return appService.showList(params, $scope, 'listNotes', $scope.headerNote);
        }

        // Function to add notes modal using uimodal plugin
        $scope.addEditNotes = function(type, noteid) {
            var params = { action: type, user_id: $scope.user_id, note_id: noteid };
            $scope.action = type;
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'addEditNote.html',
                controller: "NotesCtrl",
                size: 'md',
                resolve: {
                    params: function() {
                        return params;
                    }
                }

            });
            modalInstance.result.then(function(res) {
                if (res) {
                    $scope.filterBy = {
                        'name': '',
                        'id': Math.random()
                    }
                }
            });
        }

        //Function to delete note
        $scope.delete = function(ev, id) {
            var confirm = $mdDialog.confirm()
                .title('Delete Note')
                .content('Are you sure you want to delete?')
                .ariaLabel('Note')
                .targetEvent(ev)
                .ok('Ok')
                .cancel('Cancel');
            $mdDialog.show(confirm).then(function() {
                var data = { id: id };
                var $con = appService.apiRequest(data, 'deleteNote');
                $con.success(function(data, status) {
                    if (data.success == 1) {
                        appService.showMessage(data.message);
                        $scope.filterBy = {
                            'name': '',
                            'id': Math.random()
                        }
                    }
                });
            });
        }

        // function to change Image
        $scope.uploadPostPeopleImage = function(link, img_type, user_id) {
            var params = { link: link, image_type: img_type, user_id: user_id };
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'changeImage.html',
                controller: "ChangeImageCtrl",
                size: 'md',
                resolve: {
                    params: function() {
                        return params;
                    }
                }

            });
            modalInstance.result.then(function(link) {
                if (link) {
                    $scope.postPeople.link = link;
                }
            });

        }
    }

    /**
     * Controller which handles image change
     *
     * @author Bridge Global
     * @date 08/10/2016
     */
    function ChangeImageCtrl($scope, $uibModalInstance, params, appService) {

        $scope.details = params;
        $scope.heading = "Change Image";

        //upload image
        $scope.uploadImage = function(flow, details) {

            if (flow.files.length > 0) {
                var res = flow.upload();
                $scope.success = function($file, $message, $flow) {
                    details.link = $message;
                    details.image_type = 'img';
                    details.user_id = $scope.details.user_id;
                    var con = appService.apiRequest(details, 'saveImage');
                    con.success(function(response) {
                        if (response.success == 1) {
                            $flow.cancel();
                            $uibModalInstance.close($message);
                            appService.showMessage(response.message);
                        } else {
                            appService.showMessage(response.message);
                        }

                    })


                }
            }
        }

        $scope.cancel = function() {
            $uibModalInstance.dismiss("cancel");
        };
    }

    /**
     * Controller to  manage delivery areas
     *
     * @author Bridge Global
     * @date 08/10/2016
     */
    function PostPeopleInstanceCtrl($scope, $uibModalInstance, areaInfo, areas, composites, most, moderate, least, appService) {
        $scope.areaTemp = new Array();
        $scope.areas = areas;
        $scope.composites = composites;
        console.log(composites);
        $scope.search = {};
        $scope.search.type = 'mixed';

        //Deliverer area management
        $scope.filterArray = function(resultArray) {
            var ids = {};
            _.each(resultArray, function(bb) { ids[bb.id] = true; });

            $scope.areas = _.filter($scope.areas, function(val) {
                return !ids[val.id];
            }, resultArray);

        }

        // Filter areas
        $scope.filter = function() {
            var $con = appService.apiRequest($scope.search, 'listAreas');
            $con.success(function(response) {
                $scope.areas = response.data;
                if (areaInfo.type == 0)
                    $scope.areaTemp = most.slice();
                if (areaInfo.type == 1)
                    $scope.areaTemp = moderate.slice();
                if (areaInfo.type == 2)
                    $scope.areaTemp = least.slice();

                $scope.filterArray(most);
                $scope.filterArray(moderate);
                $scope.filterArray(least);
                $scope.filterArray($scope.areaTemp);
            });

        }

        // To get the selected areas
        $scope.moveRight = function(data) {
            var idx = $scope.areas.indexOf(data);
            $scope.areas.splice(idx, 1);
            $scope.areaTemp.push(data);
        }

        //to get non selected areas
        $scope.moveLeft = function(data) {
            var idx = $scope.areaTemp.indexOf(data);
            $scope.areaTemp.splice(idx, 1);
            $scope.areas.push(data);
        }

        // when ok button is clicked
        $scope.ok = function() {
            $uibModalInstance.close($scope.areaTemp);
        };

        //when cancel button is clicked
        $scope.cancel = function() {
            $uibModalInstance.dismiss("cancel");
        };
    }

    /**
     * Controller to  manage postpeople notes
     *
     * @author Bridge Global
     * @date 08/10/2016
     */
    function NotesCtrl($scope, $uibModalInstance, params, appService) {
        $scope.heading = (params.action == 'add') ? 'Add Notes' : 'Edit Note';
        var con;

        // add a note
        $scope.addNote = function(data) {
            data.user_id = params.user_id;
            con = appService.apiRequest(data, 'createNote');
            con.success(function(data) {
                if (data.success == 1) {

                    appService.showMessage(data.message);
                    $uibModalInstance.close(true);
                } else {
                    appService.showMessage(data.message);
                }
            })
        }

        // when cancel button is clicked
        $scope.cancel = function() {
            $uibModalInstance.dismiss("cancel");
        };
    }

})();
