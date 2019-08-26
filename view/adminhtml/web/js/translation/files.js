define([
    'jquery',
    'mage/translate',
    'Naxero_Translation/js/translation/core',
    'tabulator'
], function($, __, core, tabulator) {
    'use strict';

    // Build the widget
    $.widget('mage.filesjs', {
        // Prepare the options
        cache: null,
        isListView: true,
        options: {
            targetTable: '#translation-table-content',
            detailView: '#translation-table-detail-content',
            targetLocale: '',
            dataUrl: '',
            scanUrl: '',
            detailViewUrl: '',
            fileUpdateUrl: '',
            cacheUrl: '',
            detailViewid: 0,
            paging: 30,
            pagingSize: [10, 20, 30, 40, 50, 60, 70, 80, 90, 100]
        },

        filters: {
            group: '#translation-group-filter',
            type: '#translation-type-filter',
            locale: '#translation-locale-filter',
            status: '#translation-status-filter'
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
                    column: 'file_count', 
                    dir: 'desc'
                }],
                rowClick: function(e, row) {
                    core.loadRowDetails(self, row);
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
            this.cache._("#button-back").click(function() {
                core.togglePanes(0);
                self.cache._(self.options.detailView).tabulator("destroy");
            });

            // Trigger download of data.csv file
            this.cache._("#download-file").click(function() {
                // Todo : improve file naming from metadata
                self.cache._(self.options.detailView).tabulator("download", "csv", "trans_" + Date.now() + ".csv");
            });

            // File index update
            this.cache._("#update-files").click(function() {
                core.getScanPrompt(self);
            });

            // Flush cache
            this.cache._("button[id^='flush-cache']").click(function() {
                $.ajax({
                    type: "POST",
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
                {title: 'Id', field: 'file_id', sorter: 'number', visible: false},
                {title: 'File path', field: 'file_path', sorter: 'string', headerFilter: 'input'},
                {title: 'Created', field: 'file_creation_time', sorter: 'string', visible: false},
                {title: 'Updated', field: 'file_update_time', sorter: 'string', visible: false},
                {title: 'Lines', field: 'file_count', sorter: 'number', width: 100},
                {title: 'Type', field: 'file_type', sorter: 'string', width: 100},
                {title: 'Group', field: 'file_group', sorter: 'string', width: 100},
                {title: 'Locale', field: 'file_locale', sorter: 'string', width: 100}
            ];
        }
    });

    return $.mage.filesjs;
});