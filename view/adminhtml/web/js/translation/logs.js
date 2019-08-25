define([
    'jquery',
    'Magento_Ui/js/modal/prompt',
    'mage/translate',
    'tabulator'
], function($, prompt, __, tabulator) {
    'use strict';

    // Build the widget
    $.widget('mage.logsjs', {
        // Prepare the options
        cache: null,
        isListView: true,
        options: {
            targetTable: '#translation-table-content',
            targetLocale: '',
            dataUrl: '',
            scanUrl: '',
            fileUpdateUrl: '',
            clearLogsUrl: '',
            paging: 30,
            pagingSize: [10, 20, 30, 40, 50, 60, 70, 80, 90, 100]
        },

        _create: function() {
            this.cache = new this._cache();
            this._bind();
        },

        _cache: function() {
            var collection = {};

            function get_from_cache(selector) {
                if (undefined === collection[selector]) {
                    collection[selector] = $(selector);
                }

                return collection[selector];
            }

            return { _: get_from_cache };
        },

        _bind: function() {
            // Assign this to self
            var self = this;

            // Create the table
            this.cache._(this.options.targetTable).tabulator({
                pagination: "local",
                paginationSize: self.options.paging,
                paginationSizeSelector: self.options.pagingSize,
                persistentSort: true,
                layout: "fitColumns",
                responsiveLayout: true,
                height: "100%",
                columns: self.getListColumns(),
                initialSort:[
                    {column:"id", dir:"desc"}
                ]
            });

            // Load the data into the table
            $.ajax({
                type: "POST",
                url: self.options.dataUrl + '?form_key=' + window.FORM_KEY,
                dataType: 'json',
                showLoader: true,
                success: function(data) {
                    // Set the table data
                    self.cache._(self.options.targetTable).tabulator("setData", data.table_data);
                },
                error: function(request, status, error) {
                    console.log(error);
                }
            });

            // Configure the features
            this.setFeatures();
        },

        setFeatures: function() {
            // Set the language
            this.setLocale();

            // Set the toolbar actions
            this.setToolbarActions();

            // Set the pagination
            this.setPaging();
        },

        setLocale: function() {
            // Todo : map m2 locales to tabulator js locales
            //$(this.options.targetTable).tabulator("setLocale", this.options.targetLocale);
            this.cache._(this.options.targetTable).tabulator("setLocale", 'en-us');
        },

        setPaging: function() {
            this.cache._(this.options.targetTable).tabulator("setPage", 1);
        },

        setToolbarActions: function() {
            var self = this;

            // File index update
            this.cache._("#update-files").click(function() {
                // Trigger the prompt
                prompt({
                    title: __('Scan files'),
                    content: self.getPromptOptions([{
                            id: "update_add",
                            name: "update_mode",
                            value: "update_add",
                            label: __('Add new files'),
                            note: __('Will add only new files to the index and preserve existing content not saved to files.'),
                        },
                        {
                            id: "update_replace",
                            name: "update_mode",
                            value: "update_replace",
                            label: __('Replace all files'),
                            note: __('Will reload all files in the index and override existing content not saved to files.'),
                        }
                    ]),
                    actions: {
                        confirm: function(){
                            var optChecked = self.cache._('input[name=update_mode]:checked').val();
                            self.updateFileIndex(optChecked);
                        }, 
                        cancel: function(){}, 
                        always: function(){}
                    }
                });
            });

            // Clear logs
            this.cache._("#clear-logs").click(function() {
                $.ajax({
                    type: "POST",
                    url: self.options.clearLogsUrl + '?form_key=' + window.FORM_KEY,
                    showLoader: true,
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

        updateFileIndex: function(updateMode) {
            var self = this;

            // Prepare the update url
            var updateUrl = this.options.scanUrl + '?update_mode=' + updateMode  + '&form_key=' + window.FORM_KEY;

            // Trigger the update request
            $.ajax({
                type: "POST",
                url: updateUrl,
                dataType: 'json',
                showLoader: true,
                success: function(data) {
                    self.cache._(self.options.targetTable).tabulator("setData", data.table_data);
                },
                error: function(request, status, error) {
                    console.log(error);
                }
            });
        },

        getPromptOptions: function(opts) {
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
        },

        getListColumns: function() {
            return [
                {title: "Id", field: "id", sorter: "number", visible: false},
                {title: "File Id", field: "file_id", sorter: "string", visible: false},
                {title: "File path", field: "file_path", sorter: "string", headerFilter:"input"},
                {title: "Row", field: "row_id", sorter: "string", width: 100},
                {title: "Comments", field: "comments"}
            ];
        }
    });

    return $.mage.logsjs;
});