/**
 * Listcheckin factory
 */
angular
    .module('starter')
    .factory('Listcheckin', function ($state, $pwaRequest) {
        var factory = {};
        factory.value_id = null;
        factory.settings = {};
  
        factory.setValueId = function (valueId) {
            factory.value_id = valueId;
            return factory;
        };

        factory.getValueId = function () {
            return factory.value_id;
        };

        factory.setSettings = function (settings) {
            factory.settings = settings;
            return factory;
        };

        factory.getSettings = function () {
            return factory.settings;
        };

        factory.findAll = function () {  
            return $pwaRequest.post('/listcheckin/mobile_view/findall', {
                urlParams: {
                    value_id: factory.value_id                   
                },               
                cache: false
            });
        };

        factory.findManagerBookings = function (offset, location_id, tab) {  
            return $pwaRequest.post('/listcheckin/mobile_view/fetch-manager-booking', {
                urlParams: {
                    value_id: factory.value_id,
                    offset: offset,
                    location_id: location_id,
                    tab: tab             
                },               
                cache: false
            });
        };

        factory.verifyScan = function (location_id) {  
            return $pwaRequest.post('/listcheckin/mobile_view/verify-scan', {
                urlParams: {
                    value_id: factory.value_id,
                    location_id : location_id                
                },
                cache: false,
                refresh: true
            });
        };

        factory.exportCsv = function (location_id, tab) {  
            return $pwaRequest.post('/listcheckin/mobile_view/export-csv', {
                urlParams: {
                    value_id: factory.value_id,
                    location_id : location_id ,
                    tab: tab                
                },
                cache: false,
                refresh: true
            });
        };

        factory.checkOutManual = function (tracking_id) {  
            return $pwaRequest.post('/listcheckin/mobile_view/checkout-manual', {
                urlParams: {
                    value_id: factory.value_id,
                    tracking_id : tracking_id                 
                },
                cache: false,
                refresh: true
            });
        };

        factory.verifyWebScan = function (location_id, type) {  
            return $pwaRequest.post('/listcheckin/mobile_view/verify-web-scan', {
                urlParams: {
                    value_id: factory.value_id,
                    location_id : location_id,
                    type : type                 
                },
                cache: false,
                refresh: true
            });
        };

        factory.saveStartContent = function (values) {
            return $pwaRequest.post('listcheckin/mobile_view/save-start', {
                data: {
                    value_id  : factory.value_id,
                    startdate : values.startdate,
                    latitude  : values.latitude,
                    longitude : values.longitude,
                    location_id: values.location_id,
                    note: values.note,
                    phone_number: values.phone_number,
                    total_member: values.total_member,
                    personal_id: values.personal_id
                },
                refresh: true,
                cache: false
            });
        };

        factory.saveEndContent = function (values) {
            return $pwaRequest.post('listcheckin/mobile_view/save-end', {
                data: {
                    value_id    : factory.value_id,
                    startdate   : values.startdate,
                    latitude    : values.latitude,
                    longitude   : values.longitude,
                    enddate     : values.enddate,
                    tracking_id : values.tracking_id,
                    totaltime   : values.tracking_time
                },
                refresh: true,
                cache: false
            });
        };


        factory.fetchSettings = function (appointment_id) {
            return $pwaRequest.post('listcheckin/mobile_view/fetch-settings', {
                urlParams: {
                    value_id:  this.getValueId(),
                },
                cache: false,
                refresh: true
            });
        };
 
        return factory;
});