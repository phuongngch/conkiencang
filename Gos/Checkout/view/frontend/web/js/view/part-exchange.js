define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Catalog/js/price-utils',
        'nouislider'
    ],
    function (
        $,
        ko,
        Component,
        _,
        stepNavigator,
        modal,
        quote,
        getTotalsAction,
        fullScreenLoader,
        priceUtils,
        nouislider
    ) {
        'use strict';
        /**
         *
         * part_exchange - is the name of the component's .html template,
         * Gos_Checkout  - is the name of the your module directory.
         *
         */
        return Component.extend({
            defaults: {
                template: 'Gos_Checkout/part_exchange'
            },

            // add here your logic to display step,
            isVisible: ko.observable(true),
            checkTradeIn: ko.observable(false),
            tradeInState: ko.observable(''),
            tradeInPlate: ko.observable(''),
            tradeInMake: ko.observable(0),
            tradeInModel: ko.observable(0),
            tradeInYear: ko.observable(0),
            tradeInMileage: ko.observable(''),
            entityId: ko.observable(0),
            selectedTradeInId: ko.observable(0),
            tradeInPrice: ko.observable(0),
            checkoutShortContent: ko.observable(''),
            standingFinance: ko.observable(0),
            finalise_state: ko.observable(''),
            finalise_plate: ko.observable(''),



            /**
             *
             * @returns {*}
             */
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'part_exchange',
                    //step alias
                    null,
                    //step title value
                    'Your Part exchange',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
                     * sort order value
                     * 'sort order value' < 10: step displays before shipping step;
                     * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                     * 'sort order value' > 20 : step displays after payment step
                     */
                    1
                );

                if (!this.isVisible()) {
                    this.checkoutShortContent('Passed Part exchange.');
                }

                var self = this;
                this.tradeInPrice(this.getFormattedPrice(0));
                fullScreenLoader.startLoader();

                var params = {};

                jQuery.post(window.currentBaseUrl+'tradein/valuation/session', params, function(data) {
                            var objData = data;
                            //console.log(objData);
                            self.tradeInPrice(self.getFormattedPrice(objData.tradeInValuationSession));

                            if (objData.tradeInValuationSession > 0) {
                                self.checkTradeIn(true);
                                self.finalise_plate(objData.tradein_plate);
                                self.finalise_state(objData.tradein_state);

                            }

                });

                $.get('/tradein/glass/index', function(data) {

                    var objCarYears = data;
                    var optionItems = '<option value="0" selected>Vehicle Year</option>';
                    var step = 0;

                    if (!$.isEmptyObject(objCarYears)) {
                        for (step = 0; step < objCarYears.length; step++) {
                            optionItems += '<option value="' + objCarYears[step].glass_year + '">' + objCarYears[step].glass_year + '</option>';
                        }
                    }

                    jQuery('#tradein-year').empty().append(optionItems);
                    $("#tradein-make").attr("disabled",true);
                    $("#tradein-model").attr("disabled",true);
                    $("#tradein-variant").attr("disabled",true);
                    $("#tradein-style").attr("disabled",true);
                    $("#tradein-series").attr("disabled",true);
                    $("#tradein-engine").attr("disabled",true);
                    $("#tradein-size").attr("disabled",true);
                    $("#tradein-transmission").attr("disabled",true);
                    $("#tradein-month").attr("disabled",true);

                    var items = quote.getItems();

                    var tradeInItem = _.find(items, function(value) {
                        return value.sku.toLowerCase().indexOf('tradein') >= 0;
                    });

                    if (tradeInItem) {
                        self.selectedTradeInId(tradeInItem.product_id);

                        var params = {
                            car_id: self.selectedTradeInId()
                        };

                        jQuery.post('/quickar/finder/car', params, function(data) {
                            var objData = jQuery.parseJSON(data);
                            self.tradeInMake(objData.category_id);
                            self.changeTradeInMake(objData.product.tradein_model, objData.product.tradein_year);
                            self.tradeInPrice(self.getFormattedPrice(objData.product.tradein_trade_min));
                            fullScreenLoader.stopLoader();
                        });
                    } else {
                        self.checkoutShortContent('No Trade-in.');
                        fullScreenLoader.stopLoader();
                    }
                });

                this.standingFinance(standingFinance);
                this.tradeInMileage(tradeinMileage);

                return this;
            },

            /**
             * The navigate() method is responsible for navigation between checkout step
             * during checkout. You can add custom logic, for example some conditions
             * for switching to your custom step
             */
            navigate: function () {
                //
            },

            /**
             * @returns void
             */
            navigateToNextStep: function () {
                stepNavigator.next();
                this.initPaymentOption();
                this.scrollToStep('part_exchange', 'payment_options');
            },

            /**
             * @returns void
             */
            scrollToStep: function (currentStep, nextStep) {
                var bodyElem = $.browser.safari || $.browser.chrome ? $("body") : $("html");

                bodyElem.animate({ scrollTop: $('#' + currentStep).offset().top }, 1000, function () {
                    $(this).animate({ scrollTop: $('#' + nextStep).offset().top }, 1000);
                });
            },

            navigateTo: function(stepCode) {
                stepNavigator.navigateTo(stepCode, stepCode);
            },

            /**
             * Init Pop Up
             */
            getPopUp: function () {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                };

                var popUp = modal(options, jQuery('#get_valuation'));
                return popUp;
            },

            /**
             * Change event of Select Trade In Make
             */
            changeTradeInMake: function(selectedValue, selectedTradeInYear) {
                var self = this;

                if (!selectedValue) {
                    selectedValue = 0;
                }

                if (this.tradeInMake() != 0) {
                    var params = {
                        car_make_id: this.tradeInMake()
                    };

                    fullScreenLoader.startLoader();

                    jQuery.post('/quickar/finder/carmodel', params, function(data) {
                        var objCarModels = jQuery.parseJSON(data);
                        var optionItems = '<option value="0" selected>Select Car Model</option>';

                        if (!jQuery.isEmptyObject(objCarModels)) {
                            for (var step = 0; step < objCarModels.length; step++) {
                                optionItems += '<option value="' + objCarModels[step].name + '">' + objCarModels[step].name + '</option>';
                            }
                        }

                        jQuery('#tradein-model').empty().append(optionItems);
                        self.tradeInModel(selectedValue);

                        if (selectedTradeInYear) {
                            self.changeTradeInModel(selectedTradeInYear);
                        }

                        fullScreenLoader.stopLoader();
                    });

                    var optionItems = '<option value="0" selected>Select Year</option>';
                    jQuery('#tradein-year').empty().append(optionItems);
                }
            },

            /**
             * Change event of Select Trade In Model
             */
            changeTradeInModel: function(selectedValue) {
                var self = this;

                if (!selectedValue) {
                    selectedValue = 0;
                }

                if (this.tradeInMake() != 0 && this.tradeInModel() != 0) {
                    var params = {
                        car_make_id: this.tradeInMake(),
                        car_model: this.tradeInModel()
                    };

                    fullScreenLoader.startLoader();

                    jQuery.post('/quickar/finder/caryear', params, function(data) {
                        var objCarYears = jQuery.parseJSON(data);
                        var optionItems = '<option value="0" selected>Select Year</option>';

                        if (!jQuery.isEmptyObject(objCarYears)) {
                            for (var step = 0; step < objCarYears.length; step++) {
                                optionItems += '<option value="' + objCarYears[step].year + '">' + objCarYears[step].year + '</option>';
                            }
                        }

                        jQuery('#tradein-year').empty().append(optionItems);
                        self.tradeInYear(selectedValue);
                        self.clickValuationButton(false);
                        fullScreenLoader.stopLoader();
                    });
                }
            },

            updateBrand: function() {

                $("#tradein-make").attr("disabled",false);

                var optionItems = '<option value="0" selected>Loading...</option>';
                $('#tradein-make').empty().append(optionItems);
                $('#tradein-model').val(0);
                $('#tradein-variant').val(0);

                var params = {
                    car_make_year: $('#tradein-year').val()
                };

                var step = 0;

                $.post('/tradein/glass/make', params, function(data) {
                    var objCarMakes = data;
                    var optionItems = '<option value="0" selected>Brand</option>';

                    if (!$.isEmptyObject(objCarMakes)) {
                        for (step = 0; step < objCarMakes.length; step++) {
                            optionItems += '<option value="' + objCarMakes[step].glass_make + '">' + objCarMakes[step].glass_make + '</option>';
                        }
                    }

                    $('#tradein-make').empty().append(optionItems);
                });



            },
            // Start update models
            updateModel: function() {

                $("#tradein-model").attr("disabled",false);

                var optionItems = '<option value="0" selected>Loading...</option>';
                $('#tradein-model').empty().append(optionItems);
                $('#tradein-variant').val(0);

                var params = {
                    car_make_id: $('#tradein-make').val(),
                    car_year: $('#tradein-year').val()
                };

                var step = 0;

                $.post('/tradein/glass/family', params, function(data) {
                    var objCarModels = data;
                    var optionItems = '<option value="0" selected>Model</option>';

                    if (!$.isEmptyObject(objCarModels)) {
                        for (step = 0; step < objCarModels.length; step++) {
                            optionItems += '<option value="' + objCarModels[step].glass_model + '">' + objCarModels[step].glass_model + '</option>';
                        }
                    }

                    $('#tradein-model').empty().append(optionItems);
                });
            },

            // Start update Variant
            updateVariant: function() {

                $("#tradein-variant").attr("disabled",false);
                $("#tradein-style").attr("disabled",false);
                $("#tradein-series").attr("disabled",false);
                $("#tradein-engine").attr("disabled",false);
                $("#tradein-size").attr("disabled",false);
                $("#tradein-transmission").attr("disabled",false);
                $("#tradein-month").attr("disabled",false);

                var optionItems = '<option value="0" selected>Loading...</option>';
                $('#tradein-variant').empty().append(optionItems);
                $('#tradein-style').empty().append(optionItems);
                $('#tradein-series').empty().append(optionItems);
                $('#tradein-engine').empty().append(optionItems);
                $('#tradein-size').empty().append(optionItems);
                $('#tradein-transmission').empty().append(optionItems);
                $('#tradein-month').empty().append(optionItems);

                //Variant
                var params = {
                    car_make: $('#tradein-make').val(),
                    car_year: $('#tradein-year').val(),
                    car_model_id: $('#tradein-model').val()
                };

                var step = 0;
                $.post('/tradein/glass/variant', params, function(data) {
                    var objCarVariants = data;
                    var optionItems = '<option value="0" selected>Variant</option>';

                    if (!$.isEmptyObject(objCarVariants)) {
                        for (step = 0; step < objCarVariants.length; step++) {
                            optionItems += '<option value="' + objCarVariants[step].glass_variant + '">' + objCarVariants[step].glass_variant + '</option>';
                        }
                    }

                    $('#tradein-variant').empty().append(optionItems);
                });

                // Style

                $.post('/tradein/glass/style', params, function(data) {
                    var objCarAtt = data;
                    var optionItems = '<option value="0" selected>Style</option>';

                    if (!$.isEmptyObject(objCarAtt)) {
                        for (step = 0; step < objCarAtt.length; step++) {
                            optionItems += '<option value="' + objCarAtt[step].glass_style + '">' + objCarAtt[step].glass_style + '</option>';
                        }
                    }

                    $('#tradein-style').empty().append(optionItems);
                });

                // Series

                $.post('/tradein/glass/series', params, function(data) {
                    var objCarAtt = data;
                    var optionItems = '<option value="0" selected>Series</option>';

                    if (!$.isEmptyObject(objCarAtt)) {
                        for (step = 0; step < objCarAtt.length; step++) {
                            optionItems += '<option value="' + objCarAtt[step].glass_series + '">' + objCarAtt[step].glass_series + '</option>';
                        }
                    }

                    $('#tradein-series').empty().append(optionItems);
                });

                // Car Engine
                $.post('/tradein/glass/engine', params, function(data) {
                    var objCarAtt = data;
                    var optionItems = '<option value="0" selected>Engine</option>';

                    if (!$.isEmptyObject(objCarAtt)) {
                        for (step = 0; step < objCarAtt.length; step++) {
                            optionItems += '<option value="' + objCarAtt[step].glass_engine + '">' + objCarAtt[step].glass_engine + '</option>';
                        }
                    }

                    $('#tradein-engine').empty().append(optionItems);
                });
                // Car size
                $.post('/tradein/glass/size', params, function(data) {
                    var objCarAtt = data;
                    var optionItems = '<option value="0" selected>Size</option>';

                    if (!$.isEmptyObject(objCarAtt)) {
                        for (step = 0; step < objCarAtt.length; step++) {
                            optionItems += '<option value="' + objCarAtt[step].glass_size + '">' + objCarAtt[step].glass_size + '</option>';
                        }
                    }

                    $('#tradein-size').empty().append(optionItems);
                });

                // Car Transmission

                $.post('/tradein/glass/transmission', params, function(data) {
                    var objCarAtt = data;
                    var optionItems = '<option value="0" selected>Transmission</option>';

                    if (!$.isEmptyObject(objCarAtt)) {
                        for (step = 0; step < objCarAtt.length; step++) {
                            optionItems += '<option value="' + objCarAtt[step].glass_transmission + '">' + objCarAtt[step].glass_transmission + '</option>';
                        }
                    }

                    $('#tradein-transmission').empty().append(optionItems);
                });

                // Car Month

                $.post('/tradein/glass/month', params, function(data) {
                    var objCarAtt = data;
                    var optionItems = '<option value="0" selected>Month</option>';

                    if (!$.isEmptyObject(objCarAtt)) {
                        for (step = 0; step < objCarAtt.length; step++) {
                            optionItems += '<option value="' + objCarAtt[step].glass_mth + '">' + objCarAtt[step].glass_mth + '</option>';
                        }
                    }

                    $('#tradein-month').empty().append(optionItems);
                });


            },


            // Go - Start to trade-in

            clickButtonGo: function (openPopUp) {

                //console.log("State: "+window.currentBaseUrl);

                $('#trade_in_your_old_car').click(function() {
                    //console.log('trade_in_your_old_car is clicked.');

                    if ($('#state').val() == '') {
                        $('#state').addClass('checkout-required');
                    }else{
                        $('#state').removeClass('checkout-required');
                    }

                    if ($('#license_plate').val() == '') {
                        $('#license_plate').addClass('checkout-required');
                    }else{
                        $('#license_plate').removeClass('checkout-required');
                        var current_license = $('#license_plate').val();
                        $('#license_plate').val(current_license.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '').toUpperCase());

                    }

                });

                var self = this;
                var params = {
                    state: this.tradeInState(),
                    license: $('#license_plate').val()
                };

                if (this.tradeInState() != '' && this.tradeInPlate() != '' ) {
                        // Start jQuery POST
                        $.post("/tradein/request/index", params, function(data) {

                            if (!jQuery.isEmptyObject(data)) {

                                $('#toolbar-confirmation').show();
                                $('#get_valuation_content').show();
                                $('#get_tradein_valuation').hide();
                                $('#manual_trade_in').hide();
                                $("#txt-tradein-product").val(data.content);

                                $('#tradein-yourcar').html("Your Car? <span>"+data.content + "</span>");
                                $('#tradein-yourcar-specs').html(data.vehicleInformation);
                                $('#txt-vehicle-year').val(data.tradein_year);

                                if (openPopUp) {
                                    var options = {
                                        type: 'popup',
                                        responsive: true,
                                        innerScroll: true,
                                    };

                                    var popUp = modal(options, jQuery('#tradein-modal'));
                                    popUp.openModal();
                                }
                            } else {
                                alert('Sorry, We could not find your car!');
                            }
                        });
                        // End jQuery



                }// End If

            },

            // Finalise Tradin
            finaliseTradein: function(openPopUp) {

                var self = this;
                var params = {
                    state: $('#finalise_state').val(),
                    license: $('#finalise_plate').val()
                };

                // Start jQuery POST
                if ($('#finalise_state').val() != '' && $('#finalise_plate').val() != '') {

                $.post("/tradein/request/index", params, function(data) {

                    if (!jQuery.isEmptyObject(data)) {

                        $('#toolbar-confirmation').hide();
                        $('#get_valuation_content').show();
                        $('#get_tradein_valuation').show();
                        $('#manual_trade_in').hide();
                        $("#txt-tradein-product").val(data.content);

                        $('#tradein-yourcar').html("Your Car? <span>"+data.content + "</span>");
                        $('#tradein-yourcar-specs').html(data.vehicleInformation);
                        $('#txt-vehicle-year').val(data.tradein_year);

                        if (openPopUp) {
                            var options = {
                                type: 'popup',
                                responsive: true,
                                innerScroll: true,
                            };

                            var popUp = modal(options, jQuery('#tradein-modal'));
                            popUp.openModal();
                        }
                    } else {
                        alert('Sorry, We could not find your car!');
                    }
                });
                // End jQuery

                }// end if

            },

            // Confirm the car and start to get KMs

            confirmTradein: function() {
                $('#toolbar-confirmation').hide();
                $('#vehicle_value').hide();
                $('#estimated_value').hide();
                $('#btn_save_tradein_li').hide();
                $('#get_tradein_valuation').show();
                $('#btn_get_valuation_li').show();

            },


            manualTradein: function() {
                 $('#manual_trade_in').show();
                 $('#get_valuation_content').hide();

            },

            cancelTradein: function() {
                var self = this;
                var action = true;
                console.log('Start Cancel Session');

                if (action) {
                    self.tradeInPrice(self.getFormattedPrice(0));
                    var ajaxurl = window.currentBaseUrl+'tradein/valuation/destroy';

                    $.ajax({
                        url:ajaxurl,
                        type:'POST',
                        showLoader: true,
                        dataType:'json',
                        data: {},
                        success:function(response){
                            if (response.status == "1") {
                                getTotalsAction([]);
                            }
                        }
                    });
                }
            },
            // Get Trade In valuation from Manual form
            getTradeinValuationManual: function() {

                var car_year = $('#tradein-year').val();
                var car_make = $('#tradein-make').val();
                var car_model = $('#tradein-model').val();
                var car_variant = $('#tradein-variant').val();
                var car_style = $('#tradein-style').val();
                var car_series = $('#tradein-series').val();
                var car_engine = $('#tradein-engine').val();
                var car_size = $('#tradein-size').val();
                var car_transmission = $('#tradein-transmission').val();
                var car_month = $('#tradein-month').val();

                var ajaxurl = '/tradein/glass/vehicle';
                    $.ajax({
                        url:ajaxurl,
                        type:'POST',
                        showLoader: true,
                        dataType:'json',
                        data: {car_year:car_year,car_make:car_make,car_model:car_model,car_variant:car_variant,car_style:car_style,car_series:car_series,car_engine:car_engine,car_size:car_size,car_transmission:car_transmission,car_month:car_month},
                        success:function(response){
                            if(response.status == "1")
                            {
                                //console.log(response);

                                $('#toolbar-confirmation').show();
                                $('#get_valuation_content').show();
                                $('#get_tradein_valuation').hide();
                                $('#manual_trade_in').hide();

                                $("#txt-tradein-product").val(response.vechicle);

                                $('#tradein-yourcar').html("Your Car? <span>"+response.vechicle + "</span>");
                                $('#tradein-yourcar-specs').html(response.vehicleInformation);
                                $('#txt-vehicle-year').val(response.tradein_year);

                                $("#txt-vehicle-year").val(response.tradein_year);
                                $("#txt-glass-code").val(response.glass_code);

                            }
                        }
                    });

            },
            // Get Trade-in valuation base Kms

            getTradeinValuation: function() {

                    if($('#exellent_condition').attr('checked')) {
                        $('#exellent_condition').val(1);
                    } else {
                        $('#exellent_condition').val(0);
                    }

                    if($('#two_key').attr('checked')) {
                        $('#two_key').val(1);
                    } else {
                        $('#two_key').val(0);
                    }

                    if($('#private_import').attr('checked')) {
                        $('#private_import').val(1);
                    } else {
                        $('#private_import').val(0);
                    }

                    if($('#personalised_plates').attr('checked')) {
                        $('#personalised_plates').val(1);
                    } else {
                        $('#personalised_plates').val(0);
                    }

                    if($('#one_owner').attr('checked')) {
                        $('#one_owner').val(1);
                    } else {
                        $('#one_owner').val(0);
                    }

                    if($('#registered').attr('checked')) {
                        $('#registered').val(1);
                    } else {
                        $('#registered').val(0);
                    }

                    if($('#written_off').attr('checked')) {
                        $('#written_off').val(1);
                    } else {
                        $('#written_off').val(0);
                    }

                    if($('#service_history').attr('checked')) {
                        $('#service_history').val(1);
                    } else {
                        $('#service_history').val(0);
                    }

                    if($('#commerially').attr('checked')) {
                        $('#commerially').val(1);
                    } else {
                        $('#commerially').val(0);
                    }

                    if($('#transmission').attr('checked')) {
                        $('#transmission').val(1);
                    } else {
                        $('#transmission').val(0);
                    }

                    if($('#engine').attr('checked')) {
                        $('#engine').val(1);
                    } else {
                        $('#engine').val(0);
                    }

                    var select_basic = $('#state').val();
                    var input_license_plate = $('#license_plate').val();

                    if (select_basic == '') {
                        select_basic = $('#finalise_state').val();
                    }

                    if (input_license_plate == '') {
                        input_license_plate = $('#finalise_plate').val();
                    }

                    var car_year = $('#txt-vehicle-year').val();
                    var glass_code = $('#txt-glass-code').val();

                    var valuation_kms = $('#valuation_kms').val();
                    var exellent_condition = $("input[name='condition']:checked").val();
                    var two_key = $('#two_key').val();
                    var private_import = $('#private_import').val();
                    var personalised_plates = $('#personalised_plates').val();
                    var one_owner = $('#one_owner').val();
                    var registered = $('#registered').val();
                    var service_history = $('#service_history').val();
                    var written_off = $('#written_off').val();
                    var commerially = $('#commerially').val();
                    var transmission = $('#transmission').val();
                    var engine = $('#engine').val();

                    var ajaxurl = '/tradein/valuation/index';

                    if (valuation_kms == 0) {

                        $('#valuation_kms').addClass('checkout-required');
                        return false;
                    }

                    $.ajax({
                      url:ajaxurl,
                      type:'POST',
                      showLoader: true,
                      dataType:'json',
                      data: {state:select_basic,license:input_license_plate,glass_code:glass_code,car_year:car_year,valuation_kms:valuation_kms,exellent_condition:exellent_condition,two_key:two_key,private_import:private_import,personalised_plates:personalised_plates,one_owner:one_owner,registered:registered,service_history:service_history,written_off:written_off,commerially:commerially,transmission:transmission,engine:engine},
                        success:function(response){
                          if(response.status == "1")
                          {
                            //console.log(response);
                            $('.accept-terms-conditions').removeClass('displaynone');
                            $('#vehicle_value').show();
                            $('#estimated_value').show();
                            $('#btn_get_valuation_li').hide();
                            $('#btn_save_tradein_li').show();
                            $('#btn_back_manual_li').hide();


                            $("#vehicle-value").html(response.content);
                            $("#txt-tradein-valuation").val(response.valuation);
                            $("#amount_owing").val('');
                            $("#valuation_message").html(response.message);

                          }
                        },
                        statusCode: {
                            500: function() {
                                $("#estimated_valuation").html('$0.00');
                                $("#txt-tradein-valuation").val(0);
                                $("#valuation_message").html('Sorry we cannot complete your trade in from our website. There is no information for your vehicle, please contact us for more details.');
                            }
                        }
                    }); // end ajax requests
            },

            reloadValuation: function() {
                var currentValuation = $('#txt-tradein-valuation').val();
                var checkCondition = $("#txt-check-condition").val();
                if (currentValuation > 0 || checkCondition == 1) {

                    reloadValuationAction();

                }

                return true;
            },

            acceptTerms: function() {

                if($('#accept').attr('checked')) {
                    $('#btn_save_tradein').prop("disabled", false); // Element(s) are now enabled.
                    $('#btn_save_tradein').removeClass('opacity30'); // remove opacity class

                } else {
                    $('#btn_save_tradein').prop("disabled", true); // Element(s) are now disabled.
                    $('#btn_save_tradein').addClass('opacity30'); // Add Opacity class

                }

                 return true;
            },

            // Save Trade In Valuation

            saveTradeinValuation: function () {
                var self = this;

                var amount_owing = $('#amount_owing').val();
                var tradein_value = $('#txt-tradein-valuation').val();
                var tradein_state = $('#state').val();
                var license_plate = $('#license_plate').val();
                var car_year = $('#txt-vehicle-year').val();
                var tradein_vehicle = $('#txt-tradein-product').val();

                    if (tradein_state == '') {
                        tradein_state = $('#finalise_state').val();
                    }

                    if (license_plate == '') {
                        license_plate = $('#finalise_plate').val();
                    }

                var ajaxurl = window.currentBaseUrl+'tradein/valuation/save';
                   $.ajax({
                      url:ajaxurl,
                      type:'POST',
                      showLoader: true,
                      dataType:'json',
                      data: {car_year:car_year,license_plate:license_plate,tradein_state:tradein_state,amount_owing:amount_owing,tradein_value:tradein_value,tradein_vehicle:tradein_vehicle},
                      success:function(response){
                          if(response.status == "1")
                          {

                            //console.log(response);
                            var params = {};

                            jQuery.post(window.currentBaseUrl+'tradein/valuation/session', params, function(data) {
                                var objData = data;
                                //console.log(objData);
                                self.tradeInPrice(self.getFormattedPrice(objData.tradeInValuationSession));
                                // Set title name
                                self.checkoutShortContent(tradein_vehicle + ' - ' + self.getFormattedPrice(objData.tradeInValuationSession));
                                self.checkTradeIn(true);
                                // Let's go to next step
                                self.navigateToNextStep();
                                fullScreenLoader.stopLoader();
                            });

                            $('#closebtn').trigger('click');

                            getTotalsAction([]);


                          }
                       }
                    });  // End AJAX calls
            },


            /**
             * Click event of Valuation button
             */
            clickValuationButton: function(openPopUp) {
                var self = this;

                var params = {
                    make: this.tradeInMake(),
                    model: this.tradeInModel(),
                    year: this.tradeInYear(),
                    mileage: this.tradeInMileage()
                };

                if (this.tradeInMake() != 0 && this.tradeInModel() != 0 && this.tradeInYear() != 0) {
                    jQuery.post("/quickar/finder/valuation", params, function(data) {
                        var objTradeIn = jQuery.parseJSON(data);
                        var description = objTradeIn.description.replace(/(<([^>]+)>)/ig,"");

                        if (!jQuery.isEmptyObject(objTradeIn)) {
                            jQuery('.get_valuation_content div .description').text(description);
                            jQuery('.get_valuation_content div .price').text('$' + objTradeIn.tradein_trade_min);
                            self.tradeInPrice(self.getFormattedPrice(objTradeIn.tradein_trade_min));
                            self.entityId(objTradeIn.entity_id);
                            self.checkoutShortContent(jQuery('#tradein-make option:selected').text() + ' ' + objTradeIn.tradein_model + ' ' + description + ' - ' + self.tradeInPrice());

                            if (openPopUp) {
                                var options = {
                                    type: 'popup',
                                    responsive: true,
                                    innerScroll: true,
                                };

                                var popUp = modal(options, jQuery('#get_valuation'));
                                popUp.openModal();
                            }
                        } else {
                            alert('Invalid data. Please select your car again!');
                        }
                    });
                }
            },

            /**
             * Click event OK button in Pop Up Trade In
             */
            clickOkTradeInButton: function() {
                var self = this;

                if (this.entityId() != 0) {
                    _.each(quote.getItems(), function(element, index) {
                        if (element.sku.toLowerCase().indexOf('tradein') >= 0) {
                            var params = { item_id: element.item_id };

                            jQuery.post("/quickar/cart/removeitem", params, function(data) {
                                // console.log(data.message);
                            });
                        }
                    });

                    var params = {
                        product: this.entityId()
                    };

                    fullScreenLoader.startLoader();

                    jQuery.post("/quickar/finder/tradein", params, function(data) {
                        // var objTradeIn = jQuery.parseJSON(data);
                        jQuery('#btn-no-tradein').trigger('click');
                        getTotalsAction([]);
                        fullScreenLoader.stopLoader();
                    });
                }
            },

            /**
             * Click event Continue Without button
             */
            clickContinueWithout: function() {
                var self = this;
                // Update Order Summary
                fullScreenLoader.startLoader();

                _.each(quote.getItems(), function(element, index) {
                    if (element.sku.toLowerCase().indexOf('tradein') >= 0) {
                        var params = { item_id: element.item_id };

                        jQuery.post("/quickar/cart/removeitem", params, function(data) {
                            getTotalsAction([]);
                        });
                    }
                });

                var params = {
                    trade_in: 0,
                    tradein_mileage: 0
                };

                jQuery.post("/car/finder/paymentsummary", params, function(data) {
                    getTotalsAction([]);
                });

                // Update selects
                jQuery.get('/car/finder/carmake', function(data) {
                    var objCarMakes = jQuery.parseJSON(data);
                    var optionItems = '<option value="0" selected>Select Car Make</option>';

                    if (!jQuery.isEmptyObject(objCarMakes)) {
                        for (var step = 0; step < objCarMakes.length; step++) {
                            optionItems += '<option value="' + objCarMakes[step].id + '">' + objCarMakes[step].name + '</option>';
                        }
                    }

                    jQuery('#tradein-make').empty().append(optionItems);
                    optionItems = '<option value="0" selected>Select Car Model</option>';
                    jQuery('#tradein-model').empty().append(optionItems);
                    optionItems = '<option value="0" selected>Select Car Year</option>';
                    jQuery('#tradein-year').empty().append(optionItems);
                    self.tradeInMake(0);
                    self.tradeInModel(0);
                    self.tradeInYear(0);
                    self.tradeInMileage('');
                    self.tradeInPrice(self.getFormattedPrice(0));
                });

                this.checkoutShortContent('No Trade-in.');
                this.navigateToNextStep();
                fullScreenLoader.stopLoader();
            },

            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },

            initPaymentOption: function(){
                //var noUiSlider = require('nouislider');
                var slider2 = document.getElementById('slider-2');
                jQuery('#summary_deposit_payment .deposit .price .format').text(this.getFormattedPrice(cashDepo));
                var self = this;
                //var payDuration = <?php echo !empty($_GET['period']) ? $_GET['period'] : 12 ?>;

                //var popupAmount = 10000;
                //var popupCashDepo = 500;
                //var popupDuration = 24;
                //var popupPaymentOption = 2;

                if (!jQuery('#slider-2').has('div').length) {
                    nouislider.create(slider2, {
                        start: [payDuration],
                        connect: [true, false],
                        step: 12,
                        range: {
                            'min': 12,
                            'max': 60
                        }
                    });
                }

                slider2.noUiSlider.on('update', function( values, handle ) {

                    var value = values[handle];
                    value = Math.floor(value);
                    $('#slider-2-value').text(value + ' months');

                    payDuration = value;

                    //calculateAmount();
                    updatePaymentOption();
                });


                var slider3 = document.getElementById('slider-3');

                if (!jQuery('#slider-3').has('div').length) {
                    nouislider.create(slider3, {
                        start: [cashDepo],
                        connect: [true, false],
                        step: 50,
                        range: {
                            'min': 250,
                            'max': 15000
                        }
                    });
                }

                slider3.noUiSlider.on('update', function( values, handle ) {


                    var value = values[handle];
                    value = Math.floor(value);
                    $('#slider-3-value').text('$'+value.formatMoney(2, '.', ','));

                    cashDepo = value;

                    //calculateAmount();
                    updatePaymentOption();
                });

                slider3.noUiSlider.on('end', function(values, handle) {
                    var value = values[handle];
                    value = Math.floor(value);

                    // Update total deposit
                    var params = {
                        init_payment: value,
                        monthly_payment: jQuery('#popup-monthly-payment').text()
                    };

                    jQuery('#summary_deposit_payment .deposit .price .format').text(self.getFormattedPrice(value));

                    jQuery.post("/car/finder/paymentsummary", params, function(data) {
                        getTotalsAction([]);
                    });
                });

                $('#full-payment').click(function(){
                    $('#full-payment').addClass('active');
                    $('#balloon-payment').removeClass('active');
                    $('#car-loan-payment').removeClass('active');

                    $('#payment-title').text('Full Payment Example');

                    paymentOption = 3;
                    //calculateAmount();
                    updatePaymentOption();
                    $('#payment_options .checkout-short-content').text('Full Payment');
                    $('#additional_details .step-title .left-title').text('3 - Your details');
                    $('#additional_details .step-title .left-title').attr('data-pages', 1);
                    $('#additional_details #btn-continue').val('Next');
                    $('#additional_details .back-contact-details').trigger('click');
                });

                $('#balloon-payment').click(function(){
                    $('#balloon-payment').addClass('active');
                    $('#car-loan-payment').removeClass('active');
                    $('#full-payment').removeClass('active');

                    $('#payment-title').text('Balloon Payment Example');

                    paymentOption = 2;
                    //calculateAmount();
                    updatePaymentOption();
                    $('#payment_options .checkout-short-content').text('Balloon Payment');
                    $('#additional_details .step-title .left-title').text('3 - Your details (1 of 2)');
                    $('#additional_details .step-title .left-title').attr('data-pages', 2);
                    $('#additional_details #btn-continue').val('Continue');
                    $('#additional_details .back-contact-details').trigger('click');
                });

                $('#car-loan-payment').click(function(){
                    $('#car-loan-payment').addClass('active');
                    $('#balloon-payment').removeClass('active');
                    $('#full-payment').removeClass('active');

                    $('#payment-title').text('Car Loan Payment Example');

                    paymentOption = 1;
                    //calculateAmount();
                    updatePaymentOption();
                    $('#payment_options .checkout-short-content').text('Car loan Payment');
                    $('#additional_details .step-title .left-title').text('3 - Your details (1 of 2)');
                    $('#additional_details .step-title .left-title').attr('data-pages', 2);
                    $('#additional_details #btn-continue').val('Continue');
                    $('#additional_details .back-contact-details').trigger('click');
                });

                if(paymentOption == 2){
                    $('#balloon-payment').click();
                }else{
                    $('#car-loan-payment').click();
                }
            }
        });
    }
);

function reloadValuationAction () {

    var $ = jQuery;

    if($('#exellent_condition').attr('checked')) {
        $('#exellent_condition').val(1);
    } else {
        $('#exellent_condition').val(0);
    }

    if($('#two_key').attr('checked')) {
        $('#two_key').val(1);
    } else {
        $('#two_key').val(0);
    }

    if($('#private_import').attr('checked')) {
        $('#private_import').val(1);
    } else {
        $('#private_import').val(0);
    }

    if($('#personalised_plates').attr('checked')) {
        $('#personalised_plates').val(1);
    } else {
        $('#personalised_plates').val(0);
    }

    if($('#one_owner').attr('checked')) {
        $('#one_owner').val(1);
    } else {
        $('#one_owner').val(0);
    }

    if($('#registered').attr('checked')) {
        $('#registered').val(1);
    } else {
        $('#registered').val(0);
    }

    if($('#written_off').attr('checked')) {
        $('#written_off').val(1);
    } else {
        $('#written_off').val(0);
    }

    if($('#service_history').attr('checked')) {
        $('#service_history').val(1);
    } else {
        $('#service_history').val(0);
    }

    if($('#commerially').attr('checked')) {
        $('#commerially').val(1);
    } else {
        $('#commerially').val(0);
    }

    if($('#transmission').attr('checked')) {
        $('#transmission').val(1);
    } else {
        $('#transmission').val(0);
    }

    if($('#engine').attr('checked')) {
        $('#engine').val(1);
    } else {
        $('#engine').val(0);
    }

    var select_basic = $('#state').val();
    var input_license_plate = $('#license_plate').val();

    var car_year = $('#txt-vehicle-year').val();
    var glass_code = $('#txt-glass-code').val();

    var valuation_kms = $('#valuation_kms').val();
    var exellent_condition = $("input[name='condition']:checked").val();
    var two_key = $('#two_key').val();
    var private_import = $('#private_import').val();
    var personalised_plates = $('#personalised_plates').val();
    var one_owner = $('#one_owner').val();
    var registered = $('#registered').val();
    var service_history = $('#service_history').val();
    var written_off = $('#written_off').val();
    var commerially = $('#commerially').val();
    var transmission = $('#transmission').val();
    var engine = $('#engine').val();

    var ajaxurl = '/tradein/valuation/index';
    $.ajax({
      url:ajaxurl,
      type:'POST',
      showLoader: true,
      dataType:'json',
      data: {state:select_basic,license:input_license_plate,glass_code:glass_code,car_year:car_year,valuation_kms:valuation_kms,exellent_condition:exellent_condition,two_key:two_key,private_import:private_import,personalised_plates:personalised_plates,one_owner:one_owner,registered:registered,service_history:service_history,written_off:written_off,commerially:commerially,transmission:transmission,engine:engine},
        success:function(response){
          if(response.status == "1")
          {
            //console.log(response);

            $("#vehicle-value").html(response.content);
            $("#txt-tradein-valuation").val(response.valuation);
            $("#amount_owing").val('');
            $("#txt-check-condition").val(1);// Set this value for future using
            $("#valuation_message").html(response.message);

          }
        },
        statusCode: {
            500: function() {
                $("#estimated_valuation").html('$0.00');
                $("#valuation_message").html('Sorry we cannot complete your trade in from our website. There is no information for your vehicle, please contact us for more details.');
            }
        }
    }); // end ajax requests
}

function updatePaymentOption(){

    //Balance Finance
    var balanceFinance = totalAmount - tradeInPrice - cashDepo + standingFinance;

    //pay per month
    var payPerMonth = getMonthlyPayment(interestRate, payDuration, totalAmount, balanceFinance, paymentOption);

    // full payment

    if(paymentOption == 3) {

        jQuery('.loan').hide();
        jQuery('#cash-settlement').html('Cash Settlement');

    }else{
        jQuery('.loan').show();
        jQuery('#cash-settlement').html('Amount of credit');
    }
    //balloon payment
    if(paymentOption == 2){
        var balloonValue = getBalloonValue( totalAmount, payDuration);
        jQuery('#balloon-info').show();
        jQuery('#balloon-value').text(balloonValue.formatMoney(2, '.', ','));
    }else{
        jQuery('#balloon-info').hide();
    }

    //for popup
    jQuery('#popup-period').text(payDuration);
    jQuery('#popup-monthly-payment').text(payPerMonth.formatMoney(2, '.', ','));
    jQuery('#popup-cash-price').text(totalAmount.formatMoney(2, '.', ','));
    jQuery('#popup-customer-deposit').text(cashDepo.formatMoney(2, '.', ','));

    //total deposit
    var totalDeposit = cashDepo + tradeInPrice;
    jQuery('#popup-total-deposit').text(totalDeposit.formatMoney(2, '.', ','));

    //amount credit
    var amountCredit = balanceFinance;
    jQuery('#popup-amount-credit').text(amountCredit.formatMoney(2, '.', ','));

    var amountPayable = payPerMonth * payDuration + getBalloonValue(totalAmount, payDuration, paymentOption);

    var interestCharge = parseInt(amountPayable - balanceFinance);
    interestCharge = Math.round(interestCharge * 100) / 100;
    jQuery('#popup-interest-charge').text(interestCharge.formatMoney(2, '.', ','));

    amountPayable = Math.round(amountPayable * 100) / 100;
    jQuery('#popup-total-amount-payable').text(amountPayable.formatMoney(2, '.', ','));

    jQuery('#popup-duration').text(payDuration);
    jQuery('#popup-rate').text(interestRate+'%');
    jQuery('#popup-mileage').text(numMiles);
}


function PMT(interest, numOfPayments, pv, fv = 0.00, type = 0){
    if (numOfPayments == 0) return 0;
    var xp = Math.pow( (1 + interest), numOfPayments);
    var result = ( pv * interest * xp / ( xp - 1) +  interest / (xp - 1) * fv) * ( type == 0 ? 1 : 1/( interest + 1));
    result = Math.round(result * 100) / 100;
    return result;
}

function getMonthlyPayment(rate, term, price, loan, paymentOption){
    var result = 0;
    rate = rate / 1200;
    //rate = rate / 100;

    if(paymentOption != 1){
        var balloonValue = getBalloonValue( price, term);
        //loan = loan - balloonValue;
        result = PMT(rate, term, loan, -balloonValue);
        if(result < 0){
            result = PMT(rate, term, loan);
        }
    }else{
        result = PMT(rate, term, loan);
    }
    if(result < 0){
        result = 0;
    }

    return result;
}


function getBalloonValue(price, term , option = 2){
    if(option == 1){
        return 0;
    }
    var balloon = 60;
    if(financeTable[term]){
        balloon = financeTable[term];
    }
    return price * balloon / 100;
}



