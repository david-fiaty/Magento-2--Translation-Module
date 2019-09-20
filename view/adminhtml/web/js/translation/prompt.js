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
        'jquery/ui'
    ],
    function ($, prompt) {
        'use strict';

        // Return the component
        return {
            newFile: function(com) {
                var self = this;
                var html = '';

                // Path field
                html += '<form id="prompt_form" action="">';
                html += '<div class="admin__field-control">';
                html += '<div class="class="admin__field admin__field-option">';
                html += '<label class="admin__field-label" for="new_file_path"><span>' + __('New file path') + '</span></label>';
                html += '<input type="text" id="new_file_path" name="new_file_path" value="">';
                html += '</div>';
                html += '<div class="admin__field-note">';
                html += '<span>' + __('Select a path for the new file to be created') + '</span>';
                html += '</div>';
                html += '</div>';

                // File name field
                html += '<div class="admin__field-control">';
                html += '<div class="class="admin__field admin__field-option">';
                html += '<label class="admin__field-label" for="new_file_name"><span>' + __('New file name') + '</span></label>';
                html += '<input type="text" id="new_file_name" name="new_file_name" value="">';
                html += '</div>';
                html += '<div class="admin__field-note">';
                html += '<span>' + __('Select a file name for the new file to be created') + '</span>';
                html += '</div>';
                html += '</div>';
                html += '</form>';
                
                prompt({
                    title: __('New translation file'),
                    content: html,
                    actions: {
                        confirm: function() {
                            // AJAX code to create the file
                        }, 
                        cancel: function(){}, 
                        always: function(){}
                    }
                });
            },

            newScan: function(com) {
                var self = this;
                prompt({
                    title: __('Scan files'),
                    content: self.getScanOptions([{
                            id: 'update_add',
                            name: 'update_mode',
                            value: 'update_add',
                            label: __('Add new files'),
                            note: __('Add only new files to the index and preserve existing indexed content.'),
                        },
                        {
                            id: 'update_replace',
                            name: 'update_mode',
                            value: 'update_replace',
                            label: __('Reload all files'),
                            note: __('Clear the indexed content and reload all files into the index.'),
                        }
                    ]),
                    actions: {
                        confirm: function() {
                            var optChecked = com.cache._('input[name=update_mode]:checked').val();
                            self.updateFileIndex(com, optChecked);
                        }, 
                        cancel: function(){}, 
                        always: function(){}
                    }
                });
            },

            getScanOptions: function(opts) {
                var html = '';
                html += '<form id="prompt_form" action="">';
                html += '<div class="admin__field-control">';
                for (var i = 0; i < opts.length; i++) {
                    html += '<div class="class="admin__field admin__field-option">';
                    html += '<input type="radio" id="' + opts[i].id + '" name="' + opts[i].name + '" value="' + opts[i].value + '">';
                    html += '<label class="admin__field-label" for="' + opts[i].id + '"><span>' + opts[i].label + '</span></label>';
                    html += '</div>';
                    html += '<div class="admin__field-note">';
                    html += '<span>' + opts[i].note + '</span>';
                    html += '</div>';
                }
                html += '</div>';
                html += '</form>';
    
                return html;
            }

        };
    }
);