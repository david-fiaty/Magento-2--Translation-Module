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
                    self.loadRowDetails(row);
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
                self.togglePanes(0);
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
                {title: "Id", field: "file_id", sorter: "number", visible: false},
                {title: "File path", field: "file_path", sorter: "string", headerFilter:"input"},
                {title: "Created", field: "file_creation_time", sorter: "string", visible: false},
                {title: "Updated", field: "file_update_time", sorter: "string", visible: false},
                {title: "Lines", field: "file_count", sorter: "number", width: 100},
                {title: "Type", field: "file_type", sorter: "string", width: 100},
                {title: "Group", field: "file_group", sorter: "string", width: 100},
                {title: "Locale", field: "file_locale", sorter: "string", width: 100}
            ];
        },

        getDetailColumns: function() {
            return [
                {title: "Index", field: "index", sorter: "number", visible: false},
                {title: "Key", field: "key", sorter: "string", headerFilter:"input"},
                {title: "Value", field: "value", sorter: "string", headerFilter:"input", editor: "input"}
            ];
        },

        togglePanes: function(id) {
            if (this.isListView) {
                // Get main table width
                var tableWidth = this.cache._('#translation-table-list').outerWidth() + 'px';

                // Move main table
                this.cache._('#translation-table-list').animate({ left: '-50px' });
                this.cache._('#translation-table-list').hide();

                // Show the details table  
                this.cache._('#translation-table-detail').show();

                // Set the detail view state
                this.isListView = false;
                this.detailViewid = id;
            } else {
                // Bring the panes back
                this.cache._('#translation-table-detail').hide();
                this.cache._('#translation-table-list').animate({ left: '0px' });
                this.cache._('#translation-table-list').show();

                // Set the detail view state
                this.isListView = true;
                this.detailViewid = 0;
            }
        },

        loadRowDetails: function(row) {
            // Prepare the variables
            var self = this;
            var fileObj = row.getData();

            // Create the detail table
            this.cache._(self.options.detailView).tabulator({
                pagination: "local",
                paginationSize: self.options.paging,
                layout: "fitColumns",
                responsiveLayout: true,
                height: "100%",
                columns: self.getDetailColumns(),
                cellEdited: function(cell) {
                    var row = cell.getRow();
                    core.updateEntityData(
                        self,
                        {
                            fileId: fileObj.file_id,
                            rowContent: row.getData()
                        }
                    );
                }
            });

            // Set the file path
            this.cache._('#translation-file-path').text(fileObj.file_path);

            // Update the data
            this.getRowDetails(fileObj.file_id);

            // Move the panels
            this.togglePanes(fileObj.file_id);
        },

        getRowDetails: function(fileId) {
            // Prepare the variables
            var self = this;
            var fileDetailsUrl = this.options.detailViewUrl + '?action=get_data&file_id=' + fileId  + '&form_key=' + window.FORM_KEY;

            // Send the the request
            $.ajax({
                type: "POST",
                url: fileDetailsUrl,
                dataType: 'json',
                showLoader: true,
                success: function(data) {
                    self.cache._(self.options.detailView).tabulator("setData", data);
                },
                error: function(request, status, error) {
                    console.log(error);
                }
            });
        }
    });

    return $.mage.filesjs;
});