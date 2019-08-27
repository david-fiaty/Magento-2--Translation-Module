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
            detailView: '#translation-table-detail-content',
            detailViewFilePath: '#translation-file-path',
            targetLocale: '',
            dataUrl: '',
            scanUrl: '',
            detailViewUrl: '',
            fileUpdateUrl: '',
            cacheUrl: '',
            detailViewid: 0,
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
                pagination: 'local',
                paginationSize: self.options.paging,
                paginationSizeSelector: self.options.pagingSize,
                persistentSort: true,
                layout: 'fitColumns',
                responsiveLayout: true,
                height: '100%',
                resizableRows:true,
                columns: self.getListColumns(),
                initialSort:[{
                    column: 'id',
                    dir: 'desc'
                }],
                rowClick: function(e, row) {
                    core.loadRowDetails(self, row, true);
                }
            });

            // Load the data into the table
            core.getData(this);

            // Set the toolbar actions
            this.setToolbarActions();
        },

        setToolbarActions: function() {
            var self = this;

            // Back button
            this.cache._('#button-back').click(function() {
                core.togglePanes(self, 0);
                self.cache._(self.options.detailView).tabulator('destroy');
            });

            // Trigger download of data.csv file
            this.cache._('#download-file').click(function() {
                // Todo : improve file naming from metadata
                self.cache._(self.options.detailView).tabulator('download', 'csv', 'trans_' + Date.now() + '.csv');
            });

            // File index update
            this.cache._('#update-files').click(function() {
                core.getScanPrompt(self);
            });

            // Clear logs
            this.cache._('#clear-logs').click(function() {
                $.ajax({
                    type: 'POST',
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

            // Flush cache
            this.cache._('button[id^="flush-cache"]').click(function() {
                $.ajax({
                    type: 'POST',
                    url: self.options.cacheUrl + '?action=flush_cache&form_key=' + window.FORM_KEY,
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

        getListColumns: function() {
            return [
                {title: 'Id', field: 'id', sorter: 'number', visible: false},
                {title: 'File Id', field: 'file_id', sorter: 'string', visible: false},
                {title: 'Path', field: 'file_path', sorter: 'string', headerFilter: 'input', width: 400},
                {title: 'Row Id', field: 'row_id', sorter: 'string', visible: false},
                {title: 'Row Index', field: 'index', sorter: 'string', width: 100},
                {title: 'Comments', field: 'comments'}
            ];
        }
    });

    return $.mage.logsjs;
});