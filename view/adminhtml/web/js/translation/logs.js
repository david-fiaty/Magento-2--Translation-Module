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
            cacheUrl: '',
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
                    {column:"index", dir:"desc"}
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

        },

        getListColumns: function() {
            return [
                {title: "Index", field: "index", sorter: "number", visible: false},
                {title: "Key", field: "key", sorter: "string", headerFilter:"input"},
                {title: "Value", field: "value", sorter: "string", headerFilter:"input", editor: "input"},
                {title: "Path", field: "file_path", sorter: "string", headerFilter:"input", width: 200},
                {title: "Created", field: "file_creation_time", sorter: "string", visible: false},
                {title: "Updated", field: "file_update_time", sorter: "string", visible: false},
                {title: "Type", field: "file_type", sorter: "string", width: 100},
                {title: "Group", field: "file_group", sorter: "string", width: 100},
                {title: "Locale", field: "file_locale", sorter: "string", width: 100}
            ];
        },

        updateEntityData: function(data) {
            // Prepare the variables
            var fileUpdateUrl = this.options.detailViewUrl + '?action=update_data&file_id=' + data.fileId + '&form_key=' + window.FORM_KEY;
            var rowData = {
                    row_content: data.rowContent,
                    row_id: data.rowId 
                };

            // Send the the request
            $.ajax({
                type: "POST",
                url: fileUpdateUrl,
                data: rowData,
                dataType: 'json',
                success: function(res) {},
                error: function(request, status, error) {
                    console.log(error);
                }
            });
        }
    });

    return $.mage.logsjs;
});