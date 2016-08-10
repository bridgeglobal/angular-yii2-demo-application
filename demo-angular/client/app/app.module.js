(function () {
    'use strict';

    angular.module('app', [
        // Core modules
         'app.core'
        ,'app.login'
        ,'app.forgot'
        ,'app.postpeople'
        ,'app.notification'

        // 3rd party feature modules
        ,'mgo-angular-wizard'
        ,'ui.tree'
        ,'ngMap'
        ,'textAngular'
        ,'ngStorage'
        ,'ngMaterial'
        ,'ngScrollable'
    ]);

})();

