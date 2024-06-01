/**
 * ListcheckinWebController Home version 1 controllers
 */
angular.module('starter')
    .controller('ListcheckinWebController', function (Dialog, Loader, $session, $ionicHistory, $ionicSideMenuDelegate, Application , $ionicPopup, Location, $filter, Customer, GoogleMaps, $rootScope, SB, Listcheckin, $scope, $state, $stateParams, $translate, $interval, $timeout, $ionicModal, $cordovaBarcodeScanner) {
        $scope.value_id = Listcheckin.value_id = $stateParams.value_id;
        $scope.location_id = $stateParams.shop_id;
        $scope.is_valid = true;
        $scope.type = $stateParams.type;
        $scope.is_logged_in = Customer.isLoggedIn();
        $scope.customer = Customer.customer;
        $scope.is_loading = false;
        $scope.is_checked_in = false;
        $scope.is_checkout_success = false;
        $scope.is_checked_in_status = false;
        $scope.timeinterval = null;
        $scope.page_title = '';
        $scope.error_message = '';
		$scope.current_time = '';
		$scope.postValues = {
            location_id : $scope.location_id
        };
        $scope.payout_data = {};
        $scope.locationInfo = {};
        $scope.settings = {};
        $scope.current_year = null;
        $scope.checkedClock = {
            hours: '00',
            minutes: '00',
            seconds: '00',
        };
       /**
         *login
         */
        $scope.login = function(){
            Customer.loginModal($scope);
        }

        $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
            $scope.initLoad();
            $scope.is_logged_in = Customer.isLoggedIn();
            $scope.customer = Customer.customer;
        });

        $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
            $scope.initLoad();
            $scope.is_logged_in = Customer.isLoggedIn();
            $scope.customer = Customer.customer;
        });
    
        $scope.initLoad = function () {
            if(!Customer.isLoggedIn()){
                 $scope.login();
            }

            $timeout(function () {
                $ionicHistory.nextViewOptions({
                    disableBack: true
                });
                $ionicSideMenuDelegate.canDragContent(false);
            }, 100);
            console.log('customer web',  $scope.customer);
            Loader.show();
            Listcheckin
                .verifyWebScan($scope.postValues.location_id, $scope.type)
                .then(function (data) {                    
                    if(data.is_valid != 1){
                        $scope.is_valid = false;
                        $scope.error_message = data.message;
                        Dialog.alert($translate.instant("Error", "listcheckin"), data.message , $translate.instant("OK", "listcheckin") , -1);
                    }else{
                        $scope.page_title = data.page_title;
                        $scope.is_checked_in = data.is_checked_in;
                        $scope.payout_data = data;
                        Listcheckin.setSettings(data.settings);//Set settings
                        $scope.settings = data.settings;
                        $scope.locationInfo = data.locationInfo;
                        
                        if($scope.is_logged_in){
                            if($scope.is_checked_in) {
                                if($scope.type == 'checkin'){
                                    $scope.is_valid = false;
                                    $scope.error_message = $translate.instant("You have already checked in, Please scan a valid Check-out QR code!", "listcheckin");
                                 }
                                if(angular.isDefined(data.timetracking.tracking_id)){
                                   $scope.tracking_id =  data.timetracking.tracking_id;
                                   $scope.postValues.startdate = data.timetracking.startdate;
                                }                      
                            }else{
                                if($scope.type == 'checkout'){
                                    $scope.is_valid = false;
                                    $scope.error_message = $translate.instant("Please scan a valid QR code!", "listcheckin");
                                }
                                if($scope.payout_data.oldInfo){
                                    $scope.postValues.phone_number = $scope.payout_data.oldInfo.phone_number;
                                    $scope.postValues.personal_id = $scope.payout_data.oldInfo.personal_id;
                                }

                                if(angular.isDefined($scope.customer.mobile) && $scope.customer.mobile != ''){
                                    $scope.postValues.phone_number = $scope.customer.mobile;
                                }
                            }
                        }
                        
                    }

                }, function (error) {
                    Loader.hide();
                    $scope.is_valid = false;
                    $scope.error_message = error.message;
                    Dialog.alert($translate.instant("Error", "listcheckin"), error.message, $translate.instant("OK", "listcheckin") , -1);
                })
                .then(function () { // Finally!
                    Loader.hide();
                });
        }

        $scope.initLoad();

        function getTimeRemaining(startdate) {
          var t =  Date.parse(new Date()) - Date.parse(startdate);
          var seconds = Math.floor((t / 1000) % 60);
          var minutes = Math.floor((t / 1000 / 60) % 60);
          var hours = Math.floor((t / (1000 * 60 * 60)));     
          return {
            'total': t,
            'hours': hours,
            'minutes': minutes,
            'seconds': seconds
          };
        }
        
        function initializeClock(startdate) {
            $scope.timeinterval = $interval(function(){
                var t = getTimeRemaining(startdate); 
                $scope.checkedClock.hours = ('0' + t.hours).slice(-2);
                $scope.checkedClock.minutes = ('0' + t.minutes).slice(-2);
                $scope.checkedClock.seconds = ('0' + t.seconds).slice(-2);
            },1000);
        }

         /**
        * Save Start Content 
        */
        $scope.checkInNow = function() {

            if($scope.type != 'checkin'){
                Dialog.alert($translate.instant("Error", "listcheckin"), $translate.instant("Please scan the QR code!", "listcheckin"), $translate.instant("OK", "listcheckin") , -1);
                return false;
            }

            $scope.postValues.startdate =  new Date();
            Loader.show();
            Listcheckin
                .saveStartContent($scope.postValues)
                .then(function (data) {
                        $scope.tracking_id = data.tracking_id;
                        $scope.is_checked_in = data.is_checked_in;
                       if($scope.is_checked_in){
                            $scope.payout_data.timetracking = data.timetracking;
                            $scope.payout_data.locationInfo = data.locationInfo;
                            initializeClock($scope.postValues.startdate);
                        }else{
                            $scope.is_checked_in_status = true;
                            //Dialog.alert($translate.instant("Success", "listcheckin"), data.message, $translate.instant("OK", "listcheckin") , -1);
                        }
                }, function (error) {
                    Loader.hide();
                    Dialog.alert($translate.instant("Error", "listcheckin"), error.message, $translate.instant("OK", "listcheckin") , -1);
                })
                .then(function () { // Finally!
                   Loader.hide();
                });
        }

        /**
         *   date curremt
         */
        $scope.currentDate = function () {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth();         
            var yyyy = today.getFullYear();
            if(dd < 10) 
            {
                dd = "0"+dd ;
            } 
            
            var weekday = new Array(7);
            weekday[0] = $translate.instant("Sun", "listcheckin");
            weekday[1] = $translate.instant("Mon", "listcheckin");
            weekday[2] = $translate.instant("Tue", "listcheckin");
            weekday[3] = $translate.instant("Wed", "listcheckin");
            weekday[4] = $translate.instant("Thu", "listcheckin");
            weekday[5] = $translate.instant("Fri", "listcheckin");
            weekday[6] = $translate.instant("Sat", "listcheckin");
            var days = weekday[today.getDay()];
            var months = [  $translate.instant("Jan", "listcheckin"), 
                            $translate.instant("Feb", "listcheckin"), 
                            $translate.instant("Mar", "listcheckin"), 
                            $translate.instant("Apr", "listcheckin"), 
                            $translate.instant("May", "listcheckin"), 
                            $translate.instant("Jun", "listcheckin"), 
                            $translate.instant("Jul", "listcheckin"), 
                            $translate.instant("Aug", "listcheckin"), 
                            $translate.instant("Sep", "listcheckin"), 
                            $translate.instant("Oct", "listcheckin"), 
                            $translate.instant("Nov", "listcheckin"), 
                            $translate.instant("Dec", "listcheckin")
                        ];

            return days+', '+dd+' '+months[mm]+' '+yyyy;        
        }; 


    /**
    * Save End Content 
    */
    $scope.checkOutNow = function() {
        if($scope.type != 'checkout'){
            Dialog.alert($translate.instant("Error", "listcheckin"), $translate.instant("Please scan the QR code!", "listcheckin"), $translate.instant("OK", "listcheckin") , -1);
            return false;
        }

        $scope.postValues.enddate = new Date();
        $scope.postValues.tracking_id = $scope.tracking_id;
        $scope.postValues.tracking_time =  $scope.checkedClock.hours+':'+$scope.checkedClock.minutes+':'+$scope.checkedClock.seconds;

        Loader.show();
        Listcheckin
            .saveEndContent($scope.postValues)
            .then(function (data) {
                $scope.is_checked_in = false;
                $scope.is_checkout_success = true;
                $session.removeItem('listcheckin-last-shop-id');
            }, function (error) {
                Loader.hide();
                Dialog.alert($translate.instant("Error", "listcheckin"), error.message, $translate.instant("OK", "listcheckin") , -1);
            })
            .then(function () { // Finally!
               Loader.hide();
            });
    }

});