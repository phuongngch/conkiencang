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
        'Magento_Checkout/js/action/place-order',
        'Magento_Ui/js/model/messages',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success',
        'uiLayout'
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
        placeOrderAction,
        Messages,
        additionalValidators,
        redirectOnSuccessAction,
        layout
    ) {
        'use strict';
        /**
        *
        * summary_deposit_payment - is the name of the component's .html template,
        * Gos_Checkout  - is the name of the your module directory.
        *
        */
        return Component.extend({
            defaults: {
                template: 'Gos_Checkout/summary_deposit_payment'
            },

            // add here your logic to display step,
            isVisible: ko.observable(true),
            checkoutShortContent: ko.observable(''),
            newCarImage: ko.observable(''),
            newCarName: ko.observable(''),
            newCarDescription: ko.observable(''),
            newCarColor: ko.observable(''),
            newCarColorName: ko.observable(''),
            newCarWheelType: ko.observable(''),
            newCarInterior: ko.observable(''),
            newCarEngine: ko.observable(''),
            depositPriceFormat: ko.observable(''),
            summaryTermConditionsChecked: ko.observable(''),
            redirectAfterPlaceOrder: true,
            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),
            colorMap: ko.observable({
                'Deep Sky Blue': '#769EB8',
                'Morning Blue': '#91C3DC',
                'Polor White': '#e5f0ee',
                'Summit White': '#FAFAFA',
                'Absolute Red': '#DB161D',
                'Wine Red': '#722f37',
                'Aqua Sparkling': '#028482',
                'Mineral Black': '#232323',
                'Phantom Black': '#000000',
                'Nitrate Silver': '#D3D8DC',
                'Sleek Silver': '#C0C0C0',
                'Carragreen': '#29473B',
                'Cosmic Grey': '#676668',
                'Coconut': '#473C3A' }),

            /**
			*
			* @returns {*}
			*/
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'summary_deposit_payment',
                    //step alias
                    null,
                    //step title value
                    'Choose you payment options',
                    //observable property with logic when display step or hide step
                    this.isVisible,

                    _.bind(this.navigate, this),

                    /**
					* sort order value
					* 'sort order value' < 10: step displays before shipping step;
					* 10 < 'sort order value' < 20 : step displays between shipping and payment step
					* 'sort order value' > 20 : step displays after payment step
					*/
                    5
                );

                var items = quote.getItems();
                var newCar = null;

                _.each(items, function(element, index) {
                    if (element.sku.toLowerCase().indexOf('tradein') < 0) {
                        newCar = element;
                    }
                });

                console.log(newCar);

                this.newCarImage(newCar.simple_image);
                this.newCarName(newCar.name);
                this.newCarDescription(newCar.description);
                this.newCarColor(this.colorMap()[newCar.holden_colour]);
                this.newCarColorName(newCar.holden_colour);
                this.newCarWheelType(newCar.wheel_type);
                this.newCarInterior(newCar.interior);
                this.newCarEngine(newCar.holden_engine_and_transmission);



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
            },

            /**
             * @returns void
             */
            scrollToStep: function (currentStep, nextStep, duration = 1000) {
                var bodyElem = $.browser.safari || $.browser.chrome ? $("body") : $("html");

                bodyElem.animate({ scrollTop: $('#' + currentStep).offset().top }, duration, function () {
                    $(this).animate({ scrollTop: $('#' + nextStep).offset().top }, 1000);
                });
            },

            navigateTo: function (stepCode) {
                stepNavigator.navigateTo(stepCode);
            },

            placeOrder: function (data, event) {
                if (this.summaryTermConditionsChecked() != '') {
                    var self = this;

                    if (event) {
                        event.preventDefault();
                    }

                    if (this.validate() && additionalValidators.validate()) {
                        this.isPlaceOrderActionAllowed(false);
                        stepNavigator.next();
                        this.scrollToStep('summary_deposit_payment', 'payment', 4000);
                        $("#checkout #checkout-step-shipping #customer-email").keyup();
                        $("#checkout #shipping-new-address-form input[name='firstname']").keyup();
                        $("#checkout #shipping-new-address-form input[name='lastname']").keyup();
                        $("#checkout #shipping-new-address-form input[name='street[0]']").keyup();
                        $("#checkout #shipping-new-address-form input[name='street[1]']").keyup();
                        $("#checkout #shipping-new-address-form input[name='city']").keyup();
                        $("#checkout #shipping-new-address-form input[name='region']").keyup();
                        $("#checkout #shipping-new-address-form input[name='postcode']").keyup();
                        $("#checkout #shipping-new-address-form input[name='telephone']").keyup();
                        $("#checkout #checkout-step-shipping_method #s_method_example_example").trigger('click');
                        $("#shipping").hide();
                        $("#opc-shipping_method").hide();
                        $("#checkout #shipping-method-buttons-container button.action.continue").trigger('click');
                        return true;
                    }
                } else {
                    if (this.isVisible()) {
                        alert('Please accept the Terms & Conditions before placing order. Thank you!');
                    }
                }

                return false;
            },

            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },

            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), this.messageContainer)
                );
            },

            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': 'checkmo',
                    'po_number': null,
                    'additional_data': null
                };
            },

            /**
             * Initialize child elements
             *
             * @returns {Component} Chainable.
             */
            initChildren: function () {
                this.messageContainer = new Messages();
                this.createMessagesComponent();

                return this;
            },

            /**
             * Create child message renderer component
             *
             * @returns {Component} Chainable.
             */
            createMessagesComponent: function () {

                var messagesComponent = {
                    parent: this.name,
                    name: this.name + '.messages',
                    displayArea: 'messages',
                    component: 'Magento_Ui/js/view/messages',
                    config: {
                        messageContainer: this.messageContainer
                    }
                };

                layout([messagesComponent]);

                return this;
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                // Override this function and put after place order logic here
            },

            /**
             * @return {Boolean}
             */
            validate: function () {
                return true;
            }
        });
    }
);
