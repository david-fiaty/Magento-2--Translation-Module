define([
    'jquery',
    'mage/translate',
    'Naxero_Translation/js/translation/core',
    'Naxero_Translation/js/translation/actions',
    'tabulator'
], function($, __, core, actions, tabulator) {
    'use strict';

    // Build the widget
    $.widget('mage.stringsjs', {
        // Prepare the options
        cache: null,
        isListView: true,
        options: {
            targetTable: '#translation-table-content',
            detailView: '#translation-table-detail-content',
            localeData: {},
            dataUrl: '',
            scanUrl: '',
            detailViewUrl: '',
            fileUpdateUrl: '',
            cacheUrl: '',
            detailViewid: 0
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
                cellEdited: function(cell) {
                    var row = cell.getRow();
                    var rowData = row.getData();
                    if (rowData.is_writable == '1') {
                        core.updateEntityData(
                            self,
                            {
                                fileId: row.getData().file_id,
                                rowContent: row.getData()
                            }
                        );
                    }
                    else {
                        alert(__('This file is not writable. Please check the file permissions.'));
                    }
                },
                initialSort:[{
                    column: 'index',
                    dir: 'desc'
                }]
            });

            // Load the data into the table
            core.getData(this);

            // Set the toolbar actions
            this.setToolbarActions();
        },

        setToolbarActions: function() {
            // File index update
            actions.initScanButton(this);

            // Flush cache
            actions.initCacheButton(this);
        },

        getListColumns: function() {
            return [
                {title: __('#'), field: 'index', sorter: 'number', visible: false},
                {title: __('Key'), field: 'key', sorter: 'string', headerFilter:'input', formatter: 'textarea'},
                {title: __('Value'), field: 'value', sorter: 'string', headerFilter:'input', formatter: 'textarea', editor: 'input'},
                {title: __('Path'), field: 'file_path', sorter: 'string', headerFilter:'input', width: 200},
                {title: __('Created'), field: 'file_creation_time', sorter: 'string', visible: false},
                {title: __('Updated'), field: 'file_update_time', sorter: 'string', visible: false},
                {title: __('Type'), field: 'file_type', sorter: 'string', width: 100},
                {title: __('Group'), field: 'file_group', sorter: 'string', width: 100},
                {title: __('Locale'), field: 'file_locale', sorter: 'string', width: 100}
            ];
        }
    });

    return $.mage.stringsjs;
});