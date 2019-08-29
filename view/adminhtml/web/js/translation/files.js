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
            detailViewFilePath: '#translation-file-path',
            targetLocale: '',
            dataUrl: '',
            scanUrl: '',
            detailViewUrl: '',
            fileUpdateUrl: '',
            cacheUrl: '',
            detailViewid: 0,
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
                pagination: 'local',
                persistentSort: true,
                layout: 'fitColumns',
                responsiveLayout: true,
                height: '100%',
                resizableRows:true,
                columns: self.getListColumns(),
                initialSort:[{
                    column: 'file_count', 
                    dir: 'desc'
                }],
                rowClick: function(e, row) {
                    var rowData = row.getData();
                    if (rowData.is_readable == 1) {
                        core.loadRowDetails(self, rowData, false);
                    }
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
                self.cache._(self.options.detailView).tabulator('download', 'csv', fileName);
            });

            // File index update
            this.cache._('#update-files').click(function() {
                core.getScanPrompt(self);
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
                {title: __('Id'), field: 'file_id', sorter: 'number', visible: false},
                {title: __('Path'), field: 'file_path', sorter: 'string', headerFilter: 'input'},
                {title: __('Readable'), field: 'is_readable', visible: false},
                {title: __('Writable'), field: 'is_writable', visible: false},
                {title: __('Created'), field: 'file_creation_time', sorter: 'string', visible: false},
                {title: __('Updated'), field: 'file_update_time', sorter: 'string', visible: false},
                {title: __('Lines'), field: 'file_count', sorter: 'number', width: 100},
                {title: __('Type'), field: 'file_type', sorter: 'string', width: 100},
                {title: __('Group'), field: 'file_group', sorter: 'string', width: 100},
                {title: __('Locale'), field: 'file_locale', sorter: 'string', width: 100}
            ];
        }
    });

    return $.mage.filesjs;
});