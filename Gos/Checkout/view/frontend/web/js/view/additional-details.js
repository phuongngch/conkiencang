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
        'mage/validation',
        'Magento_Customer/js/customer-data',
        'mage/utils/objects',
        'mage/calendar',
        'Magento_Checkout/js/checkout-data'
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
        validation,
        customerData,
        mageUtils,
        calendar,
        checkoutData
    ) {
        'use strict';

        var contact = {
            title: '',
            firstName: '',
            lastName: '',
            bodDay: '',
            bodMonth: '',
            bodYear: '',
            landlineNumber: '',
            mobileNumber: '',
            landlineNumberReferred: '',
            mobileNumberReferred: '',
            email: ''
        };

        var drivingLicence = {
            licenceState: '',
            licenceNumber: '',
            licenceExpiryDay: '',
            licenceExpiryMonth: '',
            licenceExpiryYear: ''
        };

        var shippingAddress = {
            city: '',
            company: '',
            countryId: 'AU',
            customAttributes: null,
            customerId: null,
            email: '',
            fax: '',
            firstname: '',
            lastname: '',
            middlename: '',
            postcode: '',
            prefix: '',
            region: '',
            regionCode: '',
            regionId: 0,
            saveInAddressBook: 0,
            street: ['', ''],
            suffix: '',
            telephone: '',
            vatId: null
        };

        var billingAddress = {
            city: '',
            company: '',
            countryId: 'AU',
            firstname: '',
            lastname: '',
            postcode: '',
            region: '',
            regionId: 0,
            saveInAddressBook: 0,
            street: ['', ''],
            telephone: ''
        };

        var residenceHistory = {
            ownership: '',
            accommodationType: '',
            timeAddressFromMonth: '',
            timeAddressFromYear: '',
            timeAddressToMonth: '',
            timeAddressToYear: '',
        }

        var employmentHistory = {
            status: '',
            occupation: '',
            currentEmployer: '',
            employersPostcode: '',
            street: '',
            townCity: '',
            timeCurrentEmploymentFromMonth: '',
            timeCurrentEmploymentFromYear: '',
            timeCurrentEmploymentToMonth: '',
            timeCurrentEmploymentToYear: '',
        }

        /**
        *
        * additional_details - is the name of the component's .html template,
        * Gos_Checkout  - is the name of the your module directory.
        *
        */
        return Component.extend({
            defaults: {
                template: 'Gos_Checkout/additional_details'
            },

            // add here your logic to display step,
            isVisible: ko.observable(true),
            checkoutShortContent: ko.observable(''),
            currentPage: ko.observable(1),
            contact: ko.observable(contact),
            drivingLicence: ko.observable(drivingLicence),
            shippingAddress: ko.observable(shippingAddress),
            billingAddress: ko.observable(billingAddress),
            residenceHistory: ko.observable(residenceHistory),
            employmentHistory: ko.observable(employmentHistory),
            daysOfMonth: ko.observableArray([]),
            monthsOfYear: ko.observableArray([]),
            years1940ToAgo18: ko.observableArray([]),
            years1940ToNow: ko.observableArray([]),
            yearsToAgo20: ko.observableArray([]),
            yearsToNext20: ko.observableArray([]),
            dayAvailable: ko.observable(0),

            /**
			*
			* @returns {*}
			*/
            initialize: function () {
                this._super();
                // register your step
                stepNavigator.registerStep(
                    //step code will be used as step content id in the component template
                    'additional_details',
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
                    3
                );

                // Init days array
                for (var step = 1; step <= 31; step++) {
                    this.daysOfMonth.push(step);
                }

                // Init months array
                for (var step = 1; step <= 12; step++) {
                    this.monthsOfYear.push(step);
                }

                // Init years array from today to 1940
                let currentYear = (new Date()).getFullYear();
                let toYear = currentYear - 18;

                for (var step = 1940; step <= toYear; step++) {
                    this.years1940ToAgo18.push(step);
                }

                for (var step = 1940; step <= currentYear; step++) {
                    this.years1940ToNow.push(step);
                }

                let fromYear = currentYear - 20;

                for (var step = fromYear; step <= currentYear; step++) {
                    this.yearsToAgo20.push(step);
                }

                toYear = currentYear + 20;

                for (var step = currentYear + 1; step <= toYear; step++) {
                    this.yearsToNext20.push(step);
                }

                //
                let checkoutConfig = window.checkoutConfig;
                this.contact().firstName = checkoutConfig.customerData.firstname;
                this.contact().lastName = checkoutConfig.customerData.lastname;
                this.contact().email = checkoutConfig.customerData.email;
                let addresses = checkoutConfig.customerData.addresses;

                if (addresses && addresses.length) {
                    let address = addresses[addresses.length - 1];
                    this.contact().mobileNumber = address.telephone;
                    this.billingAddress().street[0] = address.street[0];
                    this.billingAddress().street[1] = address.street[1];
                    this.billingAddress().city = address.city;
                    this.billingAddress().region = address.region.region;
                    this.billingAddress().postcode = address.postcode;
                    this.billingAddress().telephone = address.telephone;
                }

                var self = this;

                // Customer data
                $.post("/car/finder/paymentsummary", {}, function(data) {
                    let checkoutSessionData = data.data;
                    self.loadContactDetailsInfo(checkoutSessionData);
                    console.log('checkoutSessionData', checkoutSessionData);
                });

                return this;
            },

            /**
            *
            * @returns void
            */
            loadContactDetailsInfo: function (checkoutSessionData) {
                if (!$.isEmptyObject(checkoutSessionData) && checkoutSessionData.email) {
                    let contact = {
                        firstName: checkoutSessionData.firstname,
                        lastName: checkoutSessionData.lastname,
                        email: checkoutSessionData.email,
                        mobileNumber: checkoutSessionData.telephone,
                        title: checkoutSessionData.title,
                        bodDay: checkoutSessionData.bodDay,
                        bodMonth: checkoutSessionData.bodMonth,
                        bodYear: checkoutSessionData.bodYear,
                        landlineNumber: checkoutSessionData.landlineNumber,
                        mobileNumber: checkoutSessionData.telephone,
                        landlineNumberReferred: checkoutSessionData.landlineNumberReferred,
                        mobileNumberReferred: checkoutSessionData.mobileNumberReferred
                    }

                    let billingAddress = {
                        city: checkoutSessionData.city,
                        company: checkoutSessionData.company,
                        countryId: checkoutSessionData.countryId,
                        firstname: checkoutSessionData.firstname,
                        lastname: checkoutSessionData.lastname,
                        postcode: checkoutSessionData.postcode,
                        region: checkoutSessionData.region,
                        regionId: checkoutSessionData.regionId,
                        saveInAddressBook: checkoutSessionData.saveInAddressBook,
                        street: checkoutSessionData.street ? checkoutSessionData.street : ['', ''],
                        telephone: checkoutSessionData.telephone
                    };

                    let drivingLicence = {
                        licenceState: checkoutSessionData.licenceState,
                        licenceNumber: checkoutSessionData.licenceNumber,
                        licenceExpiryDay: checkoutSessionData.licenceExpiryDay,
                        licenceExpiryMonth: checkoutSessionData.licenceExpiryMonth,
                        licenceExpiryYear: checkoutSessionData.licenceExpiryYear
                    };

                    this.contact(contact);
                    this.billingAddress(billingAddress);
                    this.drivingLicence(drivingLicence);
                }
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
                var newCar = undefined;
                let daysOfWeek = 7;

                _.each(quote.getItems(), function(element, index) {
                    // Attribute Set ID = 19 is Holden. It's a new product in cart
                    if (element.product.attribute_set_id == 19) {
                        newCar = element;
                    }
                });

                if (newCar && newCar.day_available > daysOfWeek) {
                    this.dayAvailable(parseInt(newCar.day_available));
                } else {
                    this.dayAvailable(daysOfWeek);
                }

                // Default selected date is today
                let beginDate = new Date();
                beginDate.setDate(beginDate.getDate() + this.dayAvailable());
                var dd = beginDate.getDate();
                var mm = beginDate.getMonth() + 1; //January is 0!
                var yyyy = beginDate.getFullYear();

                $("#calendar_inputField").calendar({
                    buttonText: "Select Date",
                    minDate: beginDate,
                    defaultDate: beginDate,
                });

                if (dd < 10) {
                    dd = '0' + dd;
                }

                if (mm < 10) {
                    mm = '0' + mm;
                }

                var beginDateToText = dd + '/' + mm + '/' + yyyy;
                $('#homeDeliveryDate').text(beginDateToText);

                //fill address from step 3
                var address = $('#billing_address_street_1').val();
                $('#delivery-address').text(address);

                var city = $('#billing_address_suburb').val();
                $('#delivery-city').text(city);

                var zipcode = $('#billing_address_postcode').val();
                $('#delivery-zipcode').text(zipcode);

                $('#calendar_inputField').change(function() {
                    var d = new Date($(this).val());
                    var dd = d.getDate();
                    var mm = d.getMonth() + 1; //January is 0!
                    var yyyy = d.getFullYear();

                    if (dd < 10) {
                        dd = '0' + dd;
                    }

                    if (mm < 10) {
                        mm = '0' + mm;
                    }

                    $('#homeDeliveryDate').text(dd + '/' + mm + '/' + yyyy);
                });

                stepNavigator.next();
                this.scrollToStep('additional_details', 'delivery');
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
                stepNavigator.navigateTo(stepCode);
            },

            nextPage: function () {
                if (this.currentPage() + 1 > this.totalPages()) {
                    this.currentPage(this.totalPages());
                } else {
                    this.currentPage(this.currentPage() + 1);
                }
            },

            previousPage: function () {
                if (this.currentPage() - 1 <= 0) {
                    this.currentPage(1);
                } else {
                    this.currentPage(this.currentPage() - 1);
                }

                if (this.totalPages() == 1) {
                    $('#additional_details .step-title .left-title').text('3 - Your details');
                    $('#additional_details #btn-continue').val('Next');
                }

                if (stepNavigator.getActiveItemIndex() == 2) {
                    this.scrollToStep('additional_details', 'additional_details');
                }
            },

            totalPages: function () {
                return parseInt($('#additional_details .left-title').first().attr('data-pages'));
            },

            clickContinue: function () {
                let numOfPages = this.totalPages();

                if (!this.validateForm('#form-checkout-step-additional-details')) {
                    return;
                }

                if (this.currentPage() >= numOfPages) {
                    $('#additional_details #btn-continue').val('Next');

                    let checkoutConfig = window.checkoutConfig;
                    let addresses = checkoutConfig.customerData.addresses;

                    let addressData = {
                        email: this.contact().email,
                        countryId: '',
                        regionId: '',
                        regionCode: '',
                        region: this.billingAddress().region,
                        customerId: '',
                        street: this.billingAddress().street,
                        company: '',
                        telephone: this.contact().mobileNumber,
                        fax: '',
                        postcode: this.billingAddress().postcode,
                        city: this.billingAddress().city,
                        firstname: this.contact().firstName,
                        lastname: this.contact().lastName,
                        middlename: '',
                        prefix: '',
                        suffix: '',
                        vatId: '',
                        saveInAddressBook: 0,
                        customAttributes: null,
                        title: this.contact().title,
                        bodDay: this.contact().bodDay,
                        bodMonth: this.contact().bodMonth,
                        bodYear: this.contact().bodYear,
                        landlineNumber: this.contact().landlineNumber,
                        landlineNumberReferred: this.contact().landlineNumberReferred,
                        mobileNumberReferred: this.contact().mobileNumberReferred,
                        licenceState: this.drivingLicence().licenceState,
                        licenceNumber: this.drivingLicence().licenceNumber,
                        licenceExpiryDay: this.drivingLicence().licenceExpiryDay,
                        licenceExpiryMonth: this.drivingLicence().licenceExpiryMonth,
                        licenceExpiryYear: this.drivingLicence().licenceExpiryYear
                    }

                    if (addresses && addresses.length) {
                        let address = addresses[addresses.length - 1];
                        addressData.countryId = address.country_id;
                        addressData.regionId = address.region.region_id;
                        addressData.regionCode = address.region.region_code;
                        addressData.customerId = address.customer_id;
                        addressData.company = address.company;
                    }

                    $.post("/car/finder/paymentsummary", addressData, function(data) {
                        getTotalsAction([]);
                    });

                    this.navigateToNextStep();
                    $("#checkout #checkout-step-shipping #customer-email").val(this.contact().email);
                    quote.guestEmail = this.contact().email;
                    $("#checkout #shipping-new-address-form input[name='firstname']").val(this.contact().firstName);
                    $("#checkout #shipping-new-address-form input[name='lastname']").val(this.contact().lastName);
                    $("#checkout #shipping-new-address-form input[name='street[0]']").val(this.billingAddress().street[0]);
                    $("#checkout #shipping-new-address-form input[name='street[1]']").val(this.billingAddress().street[1]);
                    $("#checkout #shipping-new-address-form input[name='city']").val(this.billingAddress().city);
                    $("#checkout #shipping-new-address-form input[name='region']").val(this.billingAddress().region);
                    $("#checkout #shipping-new-address-form input[name='postcode']").val(this.billingAddress().postcode);
                    this.billingAddress().telephone = this.contact().mobileNumber;
                    $("#checkout #shipping-new-address-form input[name='telephone']").val(this.billingAddress().telephone);

                    setTimeout(function() {
                        $("#checkout #checkout-step-shipping_method #s_method_example_example").prop("checked", true);
                    }, 4000);
                } else {
                    this.nextPage();
                    $('#residence_history_ownership').focus();
                    this.scrollToStep('additional_details', 'additional_details');
                }

                this.checkoutShortContent(this.contact().title + '. ' + this.contact().firstName + ' ' + this.contact().lastName + ' - ' + this.contact().email + ' - ' + this.billingAddress().postcode);
            },

            /* Validation Form */
            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);
