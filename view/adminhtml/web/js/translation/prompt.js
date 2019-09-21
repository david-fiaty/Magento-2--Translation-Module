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
        'Magento_Ui/js/modal/prompt',
        'Naxero_Translation/js/translation/core',
        'mage/url',
        'mage/translate',
        'jquery/ui'
    ],
    function ($, prompt, core, url,  __) {
        'use strict';

        // Return the component
        return {
            importData: function(com) {
                // Prepare the data
                var requestData = {
                    block_type: 'prompt',
                    template_name: 'import-data',
                    form_key: window.FORM_KEY
                };

                // Send the request
                $.ajax({
                    type: 'POST',
                    url: com.options.promptUrl,
                    showLoader: true,
                    data: requestData,
                    success: function(data) {
                        // Trigger the prompt
                        prompt({
                            title: __('Import translation data'),
                            content: data.html,
                            actions: {
                                confirm: function() {
                                }, 
                                cancel: function() {}, 
                                always: function() {}
                            }
                        });
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            },

            newFile: function(com) {
                // Prepare the data
                var requestData = {
                    block_type: 'prompt',
                    template_name: 'new-file',
                    form_key: window.FORM_KEY
                };

                // Send the request
                $.ajax({
                    type: 'POST',
                    url: com.options.promptUrl,
                    showLoader: true,
                    data: requestData,
                    success: function(data) {
                        // Trigger the prompt
                        prompt({
                            title: __('New translation file'),
                            content: data.html,
                            actions: {
                                confirm: function() {
                                    core.createFile(
                                        com,
                                        {
                                            file_path: com.cache._('#new_file_path').val(),
                                            file_name: com.cache._('#new_file_name').val()
                                        }
                                    );
                                }, 
                                cancel: function() {
                                    window.location.reload(false);  
                                }, 
                                always: function() {}
                            }
                        });

                        // Prepare the autocomplete fields variables
                        var filePathList = [];
                        var fileNameList = [];
                        var tableRows = com.cache._(com.options.targetTable).tabulator('getRows');

                        // Build the autocomplete data
                        tableRows.forEach(function(row) {
                            // Prepare the variables
                            var filePath = row.getData().file_path;
                            var pathArray = filePath.split('/');
                            var fileName = pathArray[pathArray.length - 1];
                            var cleanFilePath = filePath.replace(fileName, '');

                            // Add the file path
                            if (filePathList.indexOf(cleanFilePath) == -1) {
                                filePathList.push(cleanFilePath);
                            }
                    
                            // Add the file name
                            if (fileNameList.indexOf(fileName) == -1) {
                                fileNameList.push(fileName);
                            }
                        });

                        // Initialize the file path field
                        com.cache._('#new_file_path').autocomplete({
                            source: filePathList
                        });

                        // Initialize the file name field
                        com.cache._('#new_file_name').autocomplete({
                            source: fileNameList
                        });
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            },

            newScan: function(com) {
                // Prepare the data
                var requestData = {
                    block_type: 'prompt',
                    template_name: 'scan-files',
                    form_key: window.FORM_KEY
                };

                // Send the request
                $.ajax({
                    type: 'POST',
                    url: com.options.promptUrl,
                    showLoader: true,
                    data: requestData,
                    success: function(data) {
                        // Trigger the prompt
                        prompt({
                            title: __('Scan files'),
                            content: data.html,
                            actions: {
                                confirm: function() {
                                    var optChecked = com.cache._('input[name=update_mode]:checked').val();
                                    core.updateFileIndex(com, optChecked);
                                }, 
                                cancel: function(){}, 
                                always: function(){}
                            }
                        });
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            }
        };
    }
);