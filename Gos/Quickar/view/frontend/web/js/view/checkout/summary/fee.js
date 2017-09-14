/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Gos_Quickar/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Gos_Quickar/checkout/summary/fee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = prices[4];//amount credit
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_fee;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            },
            getTotalDeposit: function(){
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = parseFloat(prices[1]) + parseFloat(prices[3]);
                }
                return this.getFormattedPrice(price);
            },
            getDuration: function(){
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = prices[0];
                }

                return price;
            },
            getMonthlyPayment: function(){
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = prices[2];
                }
                return this.getFormattedPrice(price);
            },
            getWeeklyPayment: function(){
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = prices[2] * 12 / 52;
                }
                return this.getFormattedPrice(price);
            },
            getTradeIn: function(){
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = prices[3];
                }
                return this.getFormattedPrice(price);
            },
            getPriceData: function(index){
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = prices[index];
                }
                return this.getFormattedPrice(price);
            },
            getData: function(index){
                var data = '';
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    data = prices[index];
                }
                return data;
            },
            getCustomerDeposit: function(){
                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = parseInt(prices[1]) + parseInt(prices[3]) - parseInt(prices[6]);
                }
                return this.getFormattedPrice(price);
            },
            getBalanceOwning: function(){

                var price = 0;
                if (this.totals()) {
                    var prices = totals.getSegment('fee').value;
                    price = prices[7] - ( parseInt(prices[1]) + parseInt(prices[3]) - parseInt(prices[6]) );
                }

                //set edit link
                document.getElementById("checkout-edit-product").href=productUrl;

                return this.getFormattedPrice(price);
                
            },

        });
    }
);