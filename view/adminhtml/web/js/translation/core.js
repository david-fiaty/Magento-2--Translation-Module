define(
    [
        'jquery'
    ],
    function ($) {
        'use strict';

        return {
            test: function() {
                $( document ).ready(function() {
                    alert('test');
                });
            }
        };
    }
);