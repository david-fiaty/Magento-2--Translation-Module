define([
    'jquery',
    'mage/translate',
    'Naxero_Translation/js/translation/core',
    'Naxero_Translation/js/translation/actions',
    'tabulator'
], function($, __, core, actions, tabulator) {
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
            localeData: {},
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
                langs: core.getLocaleData(self),
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
                    if (rowData.is_readable) {
                        core.loadRowDetails(self, rowData, false);
                    }
                    else {
                        alert('file is not readable');
                    }
                }
            });

            // Load the data into the table
            core.getData(this);

            // Set the toolbar actions
            this.setToolbarActions();
        },

        setToolbarActions: function() {            
            // Back button
            actions.initBackButton(this);

            // Trigger download of data.csv file
            actions.initDownloadButton(this);

            // File index update
            actions.initScanButton(this);

            // Flush cache
            actions.initCacheButton(this);
        },

        getListColumns: function() {
            return [
                {title: __('Id'), field: 'file_id', sorter: 'number', visible: false},
                {title: __('Path'), field: 'file_path', sorter: 'string', headerFilter: 'input'},
                {title: __('Read'), field: 'is_readable', sorter: 'boolean', formatter:'tickCross', width: 85, visible: true},
                {title: __('Write'), field: 'is_writable', sorter: 'boolean', formatter:'tickCross', width: 90, visible: true},
                {title: __('Created'), field: 'file_creation_time', sorter: 'string', visible: false},
                {title: __('Updated'), field: 'file_update_time', sorter: 'string', visible: false},
                {title: __('Rows'), field: 'file_count', sorter: 'number', width: 85},
                {title: __('Type'), field: 'file_type', sorter: 'string', width: 100},
                {title: __('Group'), field: 'file_group', sorter: 'string', width: 100},
                {title: __('Locale'), field: 'file_locale', sorter: 'string', width: 100}
            ];
        }
    });

    return $.mage.filesjs;
});