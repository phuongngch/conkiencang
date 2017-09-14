/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Gos_Quickar/js/view/summary/abstract-total'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @return {*}
             */
            isDisplayed: function () {
                return this.isFullMode();
            }
        });
    }
);
