define(
    [
        'jquery'
    ],
    function ($) {
        'use strict';

        return {
            initCache: function() {
                var collection = {};

                function get_from_cache(selector) {
                    if (undefined === collection[selector]) {
                        collection[selector] = $(selector);
                    }
    
                    return collection[selector];
                }
    
                return { _: get_from_cache };
            }
        };
    }
);