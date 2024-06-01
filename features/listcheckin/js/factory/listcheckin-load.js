/**
 * Listcheckin factory OnLoad
 */
angular
    .module('starter')
    .factory('ListcheckinLoad', function ($timeout, $translate, $ionicHistory, $state, $session, Loader, Listcheckin, Application, Pages) {
        var factory = {};

        factory.load = function (parts) {
            if (parts !== null && parts.length === 4) {
                Loader.show($translate.instant('Please Wait...', 'listcheckin'));
                // Redirect to the module!
                $timeout(function () {
                    Loader.hide();
                    $ionicHistory.nextViewOptions({
                        disableBack: true
                    });
                    $state.go('listcheckin-web-checkin', {
                        value_id: parts[1],
                        shop_id: parts[2],
                        type: parts[3]
                    });
                }, 100);
            }
        };

        factory.onStart = function () {
            var hash = HASH_ON_START.match(/\?__goto__=(.*)/);
            if (hash && hash.length >= 2) {
                var path = hash[1];
                var parts = path.match(/\/listcheckin\/([0-9]+)\/([0-9]+)\/([a-z]+)/);
                if (parts.length === 4) {
                    $session.setItem('listcheckin-last-shop-id', parts);
                    factory.load(parts);
                }
            } else {
               $session
                   .getItem('listcheckin-last-shop-id')
                   .then(function (parts) {
                       factory.load(parts);
                   });
            }


            Application.loaded.then(function () {                    
                // App runtime!
                    var listcheckin = _.find(Pages.getActivePages(), {
                        code: 'listcheckin'
                    });

                    // Module is not in the App!
                    if (!listcheckin) {
                        return;
                    }
                    
                    Listcheckin
                        .setValueId(listcheckin.value_id)
                        .fetchSettings()
                        .then(function (data) {
                            console.log('settings', data.settings);
                            Listcheckin.setSettings(data.settings);               
                        });

            });
        };

        return factory;
});
