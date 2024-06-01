/**
 * listcheckin Home version 1 controllers
 */
angular.module('starter')
    .controller('ListcheckinController', function (Dialog, Loader, $ionicHistory, $session, Application , $ionicPopup, Location, $filter, Customer, GoogleMaps, $rootScope, SB, Listcheckin, $scope, $state, $stateParams, $translate, $interval, $timeout, $ionicModal, $cordovaBarcodeScanner) {
        $scope.value_id = Listcheckin.value_id = $stateParams.value_id;
        $scope.is_logged_in = Customer.isLoggedIn();
        $scope.customer = Customer.customer;
        $scope.is_loading = false;
        $scope.is_checked_in = false;
        $scope.timeinterval = null;
        $scope.page_title = '';
		$scope.current_time = '';
		$scope.postValues = {};
        $scope.payout_data = {};
        $scope.manager_history = [];
        $scope.locationInfo = {};
        $scope.settings = Listcheckin.getSettings();
        $scope.current_year = null;
        $scope.checkedClock = {
            hours: '00',
            minutes: '00',
            seconds: '00',
        };
        $scope.action = {
            tab: 'today'
        };
        $scope.can_load_older_booking_list = true;
        
     	/**
	     *login
	     */
	    $scope.login = function(){
	        Customer.loginModal($scope);
	    }

	    $rootScope.$on(SB.EVENTS.AUTH.loginSuccess, function () {
            if($scope.value_id){
                $scope.loadContent();
            }
	        $scope.is_logged_in = Customer.isLoggedIn();
	        $scope.customer = Customer.customer;
	    });

	    $rootScope.$on(SB.EVENTS.AUTH.logoutSuccess, function () {
            if($scope.value_id){
                $scope.loadContent();
            }
	        $scope.loadContent();
	        $scope.is_logged_in = Customer.isLoggedIn();
	        $scope.customer = Customer.customer;
	    });

        $scope.isWebCheck = function () {
               $session
                   .getItem('listcheckin-last-shop-id')
                   .then(function (parts) {

                    if (parts !== null && parts.length === 4) {
                         Loader.show($translate.instant('Please Wait...', 'listcheckin'));
                        // Redirect to the module!
                        $timeout(function () {
                            Loader.hide();
                            $ionicHistory.nextViewOptions({
                                disableBack: true
                            });
                            $state.go('listcheckin-web-checkin', {
                                value_id: $scope.value_id,
                                shop_id: parts[2],
                                type: parts[3]
                            });

                        }, 100);
                    }
                });
            }

        $scope.getLocation = function() {
             
            if (Location.isEnabled) {
                Location
                    .getLocation({timeout: 10000}, true)
                    .then(function (position) {
                        $scope.postValues.latitude = position.coords.latitude;
                        $scope.postValues.longitude = position.coords.longitude;
                        $scope.loadContent();
                    }, function () {
                        $scope.postValues.latitude = 0;
                        $scope.postValues.longitude = 0;
                        $scope.requestLocation();
                    });

            } else {
                $scope.postValues.latitude = 0;
                $scope.postValues.longitude = 0;
                $scope.requestLocation();
                $scope.loadContent();
            }
        }

        $scope.requestLocation = function(){
            Location.requestLocation(function () {
                $scope.loadContent();
            }); 
        }

        /**
	     *Load content
	     */
	    $scope.loadContent = function(){
           
           $scope.postValues.phone_number = $scope.customer.mobile;
            $scope.isWebCheck();
            if(!Customer.isLoggedIn()){
                return false;
            }

            $scope.is_loading = true;
            Listcheckin.findAll().success(function (data) {	           
	    		$scope.page_title = data.page_title;
           		$scope.is_checked_in = data.is_checked_in;
                $scope.payout_data = data;
                Listcheckin.setSettings(data.settings);//Set settings
                $scope.settings = data.settings;
                $scope.locationInfo = data.locationInfo;

                if($scope.settings.isManager){
                    $scope.loadManagerBooking();
                }
                
                if($scope.settings.location_tracking && Object.keys($scope.postValues).length === 0){
                    $scope.getLocation();
                }

           		$timeout(function () {
                    if($scope.is_checked_in) {
                        if(angular.isDefined(data.timetracking.tracking_id)){
                           $scope.tracking_id =  data.timetracking.tracking_id;
                           $scope.postValues.startdate = data.timetracking.startdate;
                           initializeClock(moment(data.timetracking.startdate));
                        }                      
                    }else{   
                         $scope.currentTime(); // When not checked in 
                    } 
                }, 300);

            }).error(function () {
	            $scope.is_loading = false;
	        }).finally(function () {
	            $scope.is_loading = false;
            });
	    }

        /*Called beforeEnter for get Location*/
        $scope.$on("$ionicView.beforeEnter", function(event, data) {  
           //$scope.getLocation();
           $scope.loadContent();
        });

       
        /**
	     *Customer avatar
	     */
	    $scope.customer_avatar = function (image) {      
	        if (image != '' && image != null && image != "null") {
	            return IMAGE_URL + 'images/customer' + image;
	        } else {
	            return "./features/listcheckin/assets/media/customer-placeholder.png"
	        }
	    };


     
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
         *   date curremt
         */
        $scope.currentTime = function () {
            $scope.timeinterval = $interval(function(){
                  var today = new Date();
                  var hours = today.getHours() < 10 ?  '0'+today.getHours() : today.getHours();
                  var minutes = today.getMinutes() < 10 ?  '0' + today.getMinutes() : today.getMinutes();
                  var seconds = today.getSeconds() < 10 ?  '0' + today.getSeconds() : today.getSeconds();
                  $scope.current_time = hours + ":" +minutes + ":" + seconds;

           },1000);
        }

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
        $scope.StartFunctionTracking = function() {
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
                            Dialog.alert($translate.instant("Success", "listcheckin"), data.message, $translate.instant("OK", "listcheckin") , -1);
                        }
                        
                }, function (error) {
                    Loader.hide();
                    Dialog.alert($translate.instant("Error", "listcheckin"), error.message, $translate.instant("OK", "listcheckin") , -1);
                })
                .then(function () { // Finally!
                   Loader.hide();
                });
        }


       $scope.showCheckInScanCamera = function () {
            if (!Application.is_webview) {

                $cordovaBarcodeScanner.scan().then(function (barcodeData) {                   
                    if (barcodeData.text !== '') {
                        $timeout(function () {
                            var qrCode = barcodeData.text.replace('sendback:', ''); 
                            
                            if(qrCode != ''){

                                if(Number.isInteger(qrCode) && qrCode > 0){
                                    $scope.postValues.location_id = qrCode;
                                }else{
                                    var url = qrCode;
                                    var URL = url.split("/"); 
                                    $scope.postValues.location_id = URL[URL.length-2]+'-'+URL[URL.length-1];
                                }                               
                                
                                //verify scan
                                Loader.show();
                                Listcheckin
                                    .verifyScan($scope.postValues.location_id)
                                    .then(function (data) {
                                        if(data.is_valid == 1){
                                            $scope.locationInfo = data.locationInfo;
                                            // Next steps wiht modal windows
                                            $ionicModal.fromTemplateUrl('features/listcheckin/assets/templates/l1/modal/checkin.html', {
                                                    scope: $scope,
                                                    animation: 'slide-in-right-left'
                                            }).then(function(modal) {
                                                $scope.postValues.total_member = 1;  
                                                $scope.scanCheckInModal = modal;
                                                $scope.scanCheckInModal.show();
                                            });
                                      
                                        }else{
                                            Dialog.alert($translate.instant("Error", "listcheckin"), data.message , $translate.instant("OK", "listcheckin") , -1);
                                        }
                                    }, function (error) {
                                        Loader.hide();
                                        Dialog.alert($translate.instant("Error", "listcheckin"), error.message, $translate.instant("OK", "listcheckin") , -1);
                                    })
                                    .then(function () { // Finally!
                                        Loader.hide();
                                    });
                            }else{
                                 Dialog.alert($translate.instant("Error", "listcheckin"), $translate.instant('Invalid code.', "listcheckin") , $translate.instant('OK', "listcheckin"), -1);
                            }                            
                        });

                    }else{
                        Dialog.alert($translate.instant("Error", "listcheckin") , $translate.instant("Unreadable QRCode, sorry", "listcheckin"), $translate.instant('OK', "listcheckin"), -1, "listcheckin");
                    }
                    
                }, function (error) {
                    Dialog.alert($translate.instant("Error", "listcheckin"), 'An error occurred while reading the code.', $translate.instant('OK', "listcheckin"), -1);
                });

             } else {
                Dialog.alert($translate.instant("Info", "listcheckin") , $translate.instant("This will open the code scan camera on your device", "listcheckin"), $translate.instant("Ok", "listcheckin"), -1);
            }
        };


   /**
    * close scan modal 
    */
    $scope.closeScanCheckInModal = function (){
         $scope.scanCheckInModal.remove();
    }


    /**
    * Save scan check in 
    */
    $scope.checkInSubmit = function() {
        $scope.StartFunctionTracking();
        $scope.closeScanCheckInModal();
    }

    /**
    * Save End Content 
    */
    $scope.checkOutTracking = function() {
         
        $scope.postValues.enddate = new Date();
        $scope.postValues.tracking_id = $scope.tracking_id;
        $scope.postValues.tracking_time =  $scope.checkedClock.hours+':'+$scope.checkedClock.minutes+':'+$scope.checkedClock.seconds;

        Loader.show();
        Listcheckin
            .saveEndContent($scope.postValues)
            .then(function (data) {
                $scope.is_checked_in = false;
                //Cancel timer clock.
                if (angular.isDefined($scope.timeinterval)) {
                    $interval.cancel($scope.timeinterval);
                }
                $timeout(function () {
                    $scope.currentTime(); // When not checked in
                    $scope.checkedClock.hours = '00';
                    $scope.checkedClock.minutes = '00';
                    $scope.checkedClock.seconds = '00';
                    $scope.loadContent();
                    Loader.hide(); 
                }, 300);   
               
            }, function (error) {
                Loader.hide();
                Dialog.alert($translate.instant("Error", "listcheckin"), error.message, $translate.instant("OK", "listcheckin") , -1);
            })
            .then(function () { // Finally!
               Loader.hide();
            });
    }


    $scope.showCheckOutScanCamera = function () {
            if (!Application.is_webview) {

                $cordovaBarcodeScanner.scan().then(function (barcodeData) {                   
                    if (barcodeData.text !== '') {
                        $timeout(function () {
                            var qrCode = barcodeData.text.replace('sendback:', ''); 
                            
                            if(qrCode !=''){
                                
                                if(Number.isInteger(qrCode) && qrCode > 0){
                                    $scope.postValues.location_id = qrCode;
                                }else{
                                    var url = qrCode;
                                    var URL = url.split("/"); 
                                    $scope.postValues.location_id = URL[URL.length-2]+'-'+URL[URL.length-1];
                                } 
                                
                                //verify scan
                                Loader.show();
                                Listcheckin
                                    .verifyScan($scope.postValues.location_id)
                                    .then(function (data) {
                                        if(data.is_valid == 1){
                                            $scope.checkOutTracking();
                                     
                                        }else{
                                            Dialog.alert($translate.instant("Error", "listcheckin"), data.message , $translate.instant("OK", "listcheckin") , -1);
                                        }
                                    }, function (error) {
                                        Loader.hide();
                                        Dialog.alert($translate.instant("Error", "listcheckin"), error.message, $translate.instant("OK", "listcheckin") , -1);
                                    })
                                    .then(function () { // Finally!
                                        Loader.hide();
                                    });
                            }else{
                                 Dialog.alert($translate.instant("Error", "listcheckin"), $translate.instant('Invalid code.', "listcheckin") , $translate.instant('OK', "listcheckin"), -1);
                            }                            
                        });

                    }else{
                        Dialog.alert($translate.instant("Error", "listcheckin") , $translate.instant("Unreadable QRCode, sorry", "listcheckin"), $translate.instant('OK', "listcheckin"), -1, "listcheckin");
                    }
                    
                }, function (error) {
                    Dialog.alert($translate.instant("Error", "listcheckin"), 'An error occurred while reading the code.', $translate.instant('OK', "listcheckin"), -1);
                });

             } else {
                Dialog.alert($translate.instant("Info", "listcheckin") , $translate.instant("This will open the code scan camera on your device", "listcheckin"), $translate.instant("Ok", "listcheckin"), -1);
            }
        };

    


    /*Get all the customer */
    $scope.loadManagerBooking = function () {
        //$scope.is_loading = true;
        $scope.manager_history = [];
        Loader.show();
        Listcheckin.findManagerBookings($scope.manager_history.length, $scope.settings.locationId, $scope.action.tab).success(function (data) {
            $scope.countFiltered = data.countFiltered;
            $scope.countCheckIn = data.countCheckIn;
            $scope.countCheckOut = data.countCheckOut;
            $scope.can_load_older_booking_list = !!data.collection.length;
            $scope.manager_history = $scope.manager_history.concat(data.collection);
            //$scope.is_loading = false;
            Loader.hide();  
            $rootScope.$broadcast("refreshPageSize");        
        }).error(function (error) {
            Loader.hide();
            //$scope.is_loading = false;  
            $scope.can_load_older_booking_list = false; 
            Dialog.alert($translate.instant("Error", "listcheckin") ,error.message , "OK", -1, "listcheckin");         
        }).finally(function () {          
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

    /* Pagination load*/
    $scope.loadMoreManagerBooking = function () {
          if(!$scope.manager_history.length){
            return false;
          }
         Listcheckin.findManagerBookings($scope.manager_history.length, $scope.settings.locationId, $scope.action.tab).success(function (data) {
            $scope.countFiltered = data.countFiltered;
            $scope.can_load_older_booking_list = !!data.collection.length;
            $scope.manager_history = $scope.manager_history.concat(data.collection);
            $rootScope.$broadcast("refreshPageSize");        
        }).error(function (error ) {
            $scope.can_load_older_booking_list = false;
            Dialog.alert($translate.instant("Error", "listcheckin") ,error.message , "OK", -1, "listcheckin");          
        }).finally(function () {          
            $scope.$broadcast('scroll.infiniteScrollComplete');
        });
    };

     // for range
    $scope.range = function(min, max, step) {
        step = step || 1;
        var input = [];
        for (var i = min; i <= max; i += step) input.push(i);
        return input;
    };

    $scope.exportCsv = function (locationId) {
        Loader.show();
        Listcheckin.exportCsv(locationId, $scope.action.tab).success(function (data) {
            Loader.hide();
            Dialog.alert($translate.instant("Success", "listcheckin") , $translate.instant("Send a export file on your email address!", "listcheckin")  , "OK", -1, "listcheckin");                          
        }).error(function (error ) {
            Loader.hide();
            Dialog.alert($translate.instant("Error", "listcheckin") ,error.message , "OK", -1, "listcheckin");          
        }).finally(function () {});

    }


        /*add to cart*/
        $scope.checkOutManual = function(id) {        
             
                $ionicPopup.show({
                    title: $translate.instant('Confirmation', "listcheckin"),
                    template: $translate.instant('Are you sure want to checkout!', "listcheckin"),
                    cssClass: 'checkout',
                    scope: $scope,
                    buttons: [{
                        text: $translate.instant('No', "listcheckin"),
                        type: 'button-default',
                        onTap: function (e) {
                            return false;
                        }
                    }, {
                        text: $translate.instant('Yes', "listcheckin"),
                        type: 'button-positive',
                        onTap: function (e) {
                            return true;
                        }
                    }]
                }).then(function (result) {
                    if (result) {
                        Listcheckin.checkOutManual(id).success(function (data) {
                           Dialog.alert($translate.instant("Success", "listcheckin") ,data.message , "OK", -1, "listcheckin");   
                           $scope.loadContent()
                        }).error(function (error ) {
                            Dialog.alert($translate.instant("Error", "listcheckin") ,error.message , "OK", -1, "listcheckin");          
                        }).finally(function () {});
                    }
                });
 
        }

});