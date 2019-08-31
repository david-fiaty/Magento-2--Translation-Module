define([
    'jquery',
    'mage/translate',
    'Naxero_Translation/js/translation/core',
    'Naxero_Translation/js/translation/actions',
    'tabulator'
], function($, __, core, actions, tabulator) {
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
            actions.initBackButton(this);

            // Trigger download of data.csv file
            actions.initDownloadButton(this);

            // File index update
            actions.initScanButton(this);

            // Clear logs
            actions.initLogsButton(this);

            // Flush cache
            actions.initCacheButton(this);
        },

        getListColumns: function() {
            return [
                {title: __('Id'), field: 'id', sorter: 'number', visible: false},
                {title: __('File Id'), field: 'file_id', sorter: 'string', visible: false},
                {title: __('Path'), field: 'file_path', sorter: 'string', headerFilter: 'input', width: 550},
                {title: __('Row Id'), field: 'row_id', sorter: 'string', visible: false},
                {title: __('Row Index'), field: 'index', sorter: 'string', width: 130},
                {title: __('Comments'), field: 'comments', formatter: 'textarea'}
            ];
        }
    });

    return $.mage.logsjs;
});