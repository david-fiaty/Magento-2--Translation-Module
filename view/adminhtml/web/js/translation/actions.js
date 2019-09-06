/**
 * Naxero.com
 * Professional ecommerce integrations for Magento
 *
 * PHP version 7
 *
 * @category  Magento2
 * @package   Naxero
 * @author    Platforms Development Team <contact@naxero.com>
 * @copyright Naxero.com
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

define(
    [
        'jquery',
        'Naxero_Translation/js/translation/core'
    ],
    function ($, core) {
        'use strict';

        // Return the component
        return {
            initBackButton: function(com) {
                com.cache._('#button-back').click(function() {
                    core.togglePanes(com, 0);
                    com.cache._(com.options.detailView).tabulator('destroy');
                });
            },

            initDownloadButton: function(com) {
                com.cache._('#download-file').click(function() {
                    com.cache._(com.options.detailView).tabulator(
                        'download',
                        'csv',
                        core.getDownloadFileName()
                    );
                });
            },

            initScanButton: function(com) {
                com.cache._('#update-files').click(function() {
                    core.getScanPrompt(com);
                });
            },

            initCacheButton: function(com) {
                com.cache._('button[id^="flush-cache"]').click(function() {
                    // Prepare the data
                    var requestData = {
                        action: 'flush_cache',
                        form_key: window.FORM_KEY
                    };

                    // Send the request
                    $.ajax({
                        type: 'POST',
                        url: com.options.cacheUrl,
                        showLoader: true,
                        data: requestData,
                        success: function(data) {
                            var success = JSON.parse(data.success);
                            if (!success) {
                                alert(data.message);
                            }
                        },
                        error: function(request, status, error) {
                            console.log(error);
                        }
                    });
                });   
            },

            initLogsButton: function(com) {
                com.cache._('#clear-logs').click(function() {
                    // Prepare the data
                    var requestData = {
                        form_key: window.FORM_KEY
                    };

                    // Send the request
                    $.ajax({
                        type: 'POST',
                        url: com.options.clearLogsUrl,
                        showLoader: true,
                        data: requestData,
                        success: function(data) {
                            var success = JSON.parse(data.success);
                            if (!success) {
                                alert(data.message);
                            }
                            else {
                                core.getData(com);
                            }
                        },
                        error: function(request, status, error) {
                            console.log(error);
                        }
                    });   
                });
            }
        };
    }
);