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
        'Magento_Catalog/js/price-utils'
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
        priceUtils
    ) {
        'use strict';
        /**
        *
        * payment_options - is the name of the component's .html template,
        * Gos_Checkout  - is the name of the your module directory.
        *
        */
        return Component.extend({
            defaults: {
                template: 'Gos_Checkout/payment_options'
            },

            // add here your logic to display step,
            isVisible: ko.observable(true),
            checkoutShortContent: ko.observable(''),

            /**
			*
			* @returns {*}
			*/
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'payment_options',
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
                    2
                );

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
                var popUp = this.getPopUp();
                popUp.openModal();
            },

            navigateTo: function(stepCode) {
                stepNavigator.navigateTo(stepCode);
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

                var popUp = modal(options, $('#payment-term-conditional-pop-up'));
                return popUp;
            },

            /**
             * Event click on Agree button
             */
            clickAgreePaymentOptionsButton: function () {
                $('#btn-disagree').trigger('click');
                stepNavigator.next();
                this.scrollToStep('payment_options', 'additional_details');
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
        });
    }
);
