define([
    'jquery',
    'Magento_Ui/js/modal/prompt',
    'mage/translate',
    'Naxero_Translation/js/translation/core',
    'tabulator'
], function($, prompt, __, core, tabulator) {
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
            this.cache = new core.initCache();
            this._bind();
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
                initialSort:[{
                    column: 'id',
                    dir: 'desc'
                }]
            });

            // Load the data into the table
            core.getData(this);
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
                            core.updateFileIndex(self, optChecked);
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
                        else {
                            core.getData(self);
                        }
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });   
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
                {title: 'Id', field: 'id', sorter: 'number', visible: false},
                {title: 'File Id', field: 'file_id', sorter: 'string', visible: false},
                {title: 'File path', field: 'file_path', sorter: 'string', headerFilter: 'input'},
                {title: 'Row', field: 'row_id', sorter: 'string', width: 100},
                {title: 'Comments', field: 'comments'}
            ];
        }
    });

    return $.mage.logsjs;
});