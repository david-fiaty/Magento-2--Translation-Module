define([
    'jquery',
    'mage/translate',
    'Naxero_Translation/js/translation/core',
    'tabulator'
], function($, __, core, tabulator) {
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

            // Set the toolbar actions
            this.setToolbarActions();
        },

        setToolbarActions: function() {
            var self = this;

            // File index update
            this.cache._("#update-files").click(function() {
                core.getScanPrompt(self);
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