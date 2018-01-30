define([
    'jquery', 'tabs', 'Magento_Ui/js/modal/prompt', 'mage/url', 'mage/translate', 'tabulator', 
], function($, tabs, prompt, urlBuilder, __, tabulator) {
    'use strict';

    // Build the widget
    $.widget('mage.corejs', {
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
            detailViewid: 0,
            paging: 30
        },

        filters: {
            group: '#translation-group-filter',
            type: '#translation-type-filter',
            locale: '#translation-locale-filter',
            status: '#translation-status-filter'
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
                layout: "fitColumns",
                responsiveLayout: true,
                height: "100%",
                columns: self.getListColumns(),
                initialSort:[
                    {column:"file_count", dir:"desc"},
                ],
                rowClick: function(e, row) {
                    self.loadRowDetails(row.getData());
                }
            });

            //Load the data into the table
            $.ajax({
                type: "GET",
                url: self.options.dataUrl,
                dataType: 'json',
                showLoader: true,
                success: function(data) {
                    // Set the table data
                    self.cache._(self.options.targetTable).tabulator("setData", data.table_data);

                    // Build options for the lists
                    self.buildFilters(data);

                    // Add the list events
                    self.addFilterEvents();
                },
                error: function(request, status, error) {
                    console.log(error);
                }
            });

            // Configure the features
            this.setFeatures();
        },

        buildFilters: function(data) {
            // Create the group filter
            this.createOptions(this.filters.group, data.filter_data.file_group);

            // Create the type filter
            this.createOptions(this.filters.type, data.filter_data.file_type);

            // Create the locale filter
            this.createOptions(this.filters.locale, data.filter_data.file_locale);

            // Create the status filter
            this.createOptions(this.filters.status, data.filter_data.file_status);
        },

        addFilterEvents: function() {
            var self = this;

            // Prepare the fields
            var fields = [
                { selector: self.filters.group, field: 'file_group' },
                { selector: self.filters.type, field: 'file_type' },
                { selector: self.filters.locale, field: 'file_locale' },
                { selector: self.filters.status, field: 'file_status' },
            ];

            // Assign the events
            $.each(fields, function(k, obj) {
                self.cache._(obj.selector).on('change', function() {
                    let selected = $(this).find(":selected").text();
                    let selectedKey = $(this).find(":selected").val();
                    self.updateFilters({ field: obj.field, type: '=', value: selected, key: selectedKey });
                });
            });
        },

        updateFilters: function(newFilter) {
            var self = this;
            var filters = $(self.options.targetTable).tabulator('getFilters');

            // Process the new filter
            if (filters.length == 0) {
                filters.push(newFilter);
            } else {
                for (var i = 0; i < filters.length; i++) {
                    if (newFilter.key == 'alltx') {
                        filters.splice(i, 1);
                    } else {
                        if (filters[i].field == newFilter.field) {
                            filters[i] = newFilter;
                        } else if (filters[i].field == newFilter.field && filters[i].value == newFilter.value) {} else {
                            filters.push(newFilter);
                        }
                    }
                }
            }

            // Clea filters and set the new one
            self.cache._(self.options.targetTable).tabulator('clearFilter');
            self.cache._(self.options.targetTable).tabulator('setFilter', filters);
        },

        createOptions: function(sel, arr) {
            var output = [];
            $.each(arr, function(key, value) {
                output.push('<option value="' + key + '">' + value + '</option>');
            });
            this.cache._(sel).append(output.join(''));
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
                // Trigger the prompt
                prompt({
                    title: __('Update file index'),
                    content: self.getPromptOptions([{
                            id: "update_add",
                            name: "update_mode",
                            value: "update_add",
                            label: __('Add new files'),
                            note: __('Maximum 255 chars. Meta Description should optimally be between 150-160 characters'),
                        },
                        {
                            id: "update_replace",
                            name: "update_mode",
                            value: "update_replace",
                            label: __('Replace all files'),
                            note: __('Maximum 255 chars. Meta Description should optimally be between 150-160 characters'),
                        }
                    ]),
                    actions: {
                        confirm: function(){
                            self.updateFileIndex();
                        }, 
                        cancel: function(){}, 
                        always: function(){}
                    }
                });

                // Activate the prompt behavior

             });

            // File strings reload
            this.cache._("#get-strings").click(function() {
                self.getRowDetails(self.detailViewid);
            });

            // File strings save
            this.cache._("#save-strings").click(function() {
                self.saveRowDetails(self.detailViewid);
            });            
        },

        updateFileIndex: function(updateMode) {
            // Prepare the update url
            var updateUrl = self.options.scanUrl + '?update_mode=' + updateMode;

            // Trigger the update request
            $.ajax({
                type: "GET",
                url: updateUrl,
                dataType: 'json',
                showLoader: true,
                success: function(data) {},
                error: function(request, status, error) {
                    console.log(error);
                }
            }).done(function(data) {
                self.cache._(self.options.targetTable).tabulator("setData", data.table_data);
            });
        },

        getPromptOptions: function(opts) {
            var html = '';
            html += '<form action="">';
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
                { title: "Id", field: "file_id", sorter: "number", visible: false},
                { title: "Path", field: "file_path", sorter: "string", headerFilter:"input", width: 450},
                { title: "Created", field: "file_creation_time", sorter: "string", visible: false},
                { title: "Updated", field: "file_update_time", sorter: "string", visible: false},
                { title: "Lines", field: "file_count", sorter: "number"},
                { title: "Type", field: "file_type", sorter: "string"},
                { title: "Group", field: "file_group", sorter: "string"},
                { title: "Locale", field: "file_locale", sorter: "string"},
                { title: "Status", field: "file_is_active", sorter: "number", formatter: "tickCross"}
            ];
        },

        getDetailColumns: function() {
            return [
                { title: "Key", field: "key", sorter: "string", headerFilter:"input"},
                { title: "Value", field: "value", sorter: "string", headerFilter:"input", editor: "input" }
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

        loadRowDetails: function(fileObj) {
            // Prepare the variables
            var self = this;

            // Create the detail table
            this.cache._(self.options.detailView).tabulator({
                pagination: "local",
                paginationSize: self.options.paging,
                layout: "fitColumns",
                responsiveLayout: true,
                height: "100%",
                columns: self.getDetailColumns(),
                cellEdited: function(cell) {
                    self.updateEntityData({
                        fileId: fileObj.file_id,
                        fileContent: self.cache._(self.options.detailView).tabulator("getData")
                    });
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
            var fileDetailsUrl = this.options.detailViewUrl + '?action=get_data&file_id=' + fileId;

            // Send the the request
            $.ajax({
                type: "GET",
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
        },

        updateEntityData: function(row) {
            // Prepare the variables
            var self = this;
            var fileUpdateUrl = this.options.detailViewUrl + '?action=update_data&file_id=' + row.fileId;
            var file_content = { file_content: row.fileContent };

            // Send the the request
            $.ajax({
                type: "POST",
                url: fileUpdateUrl,
                dataType: 'json',
                data: file_content,
                success: function(res) {},
                error: function(request, status, error) {
                    console.log(error);
                }
            });
        },

        saveRowDetails: function(fileId) {
            // Prepare the variables
            var self = this;
            var fileUpdateUrl = this.options.detailViewUrl + '?action=save_data&file_id=' + fileId;

            // Send the the request
            $.ajax({
                type: "GET",
                url: fileUpdateUrl,
                dataType: 'json',
                showLoader: true,
                success: function(res) {},
                error: function(request, status, error) {
                    console.log(error);
                }
            });
        }

    });

    return $.mage.corejs;
});