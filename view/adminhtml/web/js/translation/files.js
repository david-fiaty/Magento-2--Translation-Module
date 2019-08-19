define([
    'jquery',
    'Magento_Ui/js/modal/prompt',
    'mage/translate',
    'tabulator'
], function($, prompt, __, tabulator) {
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
                    {column:"file_count", dir:"desc"}
                ],
                rowClick: function(e, row) {
                    self.loadRowDetails(row);
                }
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
                {selector: self.filters.group, field: 'file_group'},
                {selector: self.filters.type, field: 'file_type'},
                {selector: self.filters.locale, field: 'file_locale'}
            ];

            // Assign the events
            $.each(fields, function(k, obj) {
                self.cache._(obj.selector).on('change', function() {
                    let selected = $(this).find(":selected").text();
                    let selectedKey = $(this).find(":selected").val();
                    self.updateFilters({ field: obj.field, type: '=', value: selectedKey });
                });
            });
        },

        updateFilters: function(newFilter) {
            var self = this;
            var filters = $(self.options.targetTable).tabulator('getFilters'); 

            var found = filters.find(function(element) {
                return element.field == newFilter.field;
            });

            // Process the new filter
            if (filters.length == 0 || typeof found === 'undefined') {
                filters.push(newFilter);
            } else {
                for (var i = 0; i < filters.length; i++) {
                    if (filters[i].field == newFilter.field) {
                        if (newFilter.value === 'alltx') {
                            filters.splice(i, 1);
                        } 
                        else if (newFilter.value !== 'alltx' && newFilter.field === filters[i].field) {
                            filters[i] = newFilter;
                        }
                    } 
                    else if (filters[i].field == newFilter.field && newFilter.value !== 'alltx') {
                        filters.push(newFilter);
                    }                    
                }
            }

            // Clear filters and set the new one
            self.cache._(self.options.targetTable).tabulator('clearFilter');
            self.cache._(self.options.targetTable).tabulator('setFilter', filters);
        },

        createOptions: function(sel, arr) {
            var self = this;
            var output = [];
            $.each(arr, function(key, value) {
                // Create the option
                var option = (sel == self.filters.status) ? '<option value="' + key + '">' : '<option value="' + value + '">';
                option += value + '</option>';

                // Add it to the output
                output.push(option);
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
                    title: __('Update files list'),
                    content: self.getPromptOptions([{
                            id: "update_add",
                            name: "update_mode",
                            value: "update_add",
                            label: __('Add new files'),
                            note: __('Will add only new files to the index and preserve existing content not saved to files.'),
                        },
                        {
                            id: "update_replace",
                            name: "update_mode",
                            value: "update_replace",
                            label: __('Replace all files'),
                            note: __('Will reload all files in the index and override existing content not saved to files.'),
                        }
                    ]),
                    actions: {
                        confirm: function(){
                            var optChecked = self.cache._('input[name=update_mode]:checked').val();
                            self.updateFileIndex(optChecked);
                        }, 
                        cancel: function(){}, 
                        always: function(){}
                    }
                });
            });

            // File strings reload
            this.cache._("#get-strings").click(function() {
                self.getRowDetails(self.detailViewid);
            });

            // Flush cache
            this.cache._("#flush-cache").click(function() {
                $.ajax({
                    type: "POST",
                    url: self.options.cacheUrl + '?form_key=' + window.FORM_KEY,
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

        updateFileIndex: function(updateMode) {
            var self = this;

            // Prepare the update url
            var updateUrl = this.options.scanUrl + '?update_mode=' + updateMode  + '&form_key=' + window.FORM_KEY;

            // Trigger the update request
            $.ajax({
                type: "POST",
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
            html += '<form id="prompt_form" action="">';
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
                {title: "Id", field: "file_id", sorter: "number", visible: false},
                {title: "Path", field: "file_path", sorter: "string", headerFilter:"input"},
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
                    self.updateEntityData({
                        fileId: fileObj.file_id,
                        rowContent: row.getData()
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

    return $.mage.filesjs;
});