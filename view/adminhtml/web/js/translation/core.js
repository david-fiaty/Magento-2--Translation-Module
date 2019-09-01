define(
    [
        'jquery',
        'Magento_Ui/js/modal/prompt',
        'mage/translate',
        'mage/cookies'
    ],
    function ($, prompt, __) {
        'use strict';

        // Define constants
        const PAGER_SELECTOR = 'translation-paging-filter';
        const DEFAULT_PAGER_VALUE = 50;

        // Return the component
        return {
            initCache: function() {
                var collection = {};

                function get_from_cache(selector) {
                    if (undefined === collection[selector]) {
                        collection[selector] = $(selector);
                    }
    
                    return collection[selector];
                }
    
                return { _: get_from_cache };
            },

            callMethod: function(obj, method, args) {
                if (typeof obj[method] === 'function') {
                    (typeof args === 'undefined') ? obj[method]() : obj[method](args);
                }
            },

            getDownloadFileName: function() {
                var fileName = 'trans_' + Date.now() + '.csv';
                return fileName;
            },

            getLocaleData: function(com) {
                var data = com.options.localeData.replace(new RegExp("\\\\", "g"), "");
                return JSON.parse(data);
            },

            setPaging: function(com, targetTable, val) {
                // Prepare the pager value
                var val = val || $.cookie(PAGER_SELECTOR) || DEFAULT_PAGER_VALUE;

                // Set the pager value
                com.cache._(targetTable).tabulator('setPageSize', val);

                // Save the pager value
                $.cookie(PAGER_SELECTOR, val);

                // Update the pager select state
                com.cache._('.' + PAGER_SELECTOR).val(val);
            },

            getData: function(com) {
                var self = this;
                $.ajax({
                    type: 'POST',
                    url: com.options.dataUrl + '?form_key=' + window.FORM_KEY,
                    dataType: 'json',
                    showLoader: true,
                    success: function(data) {
                        // Set the table data
                        self.prepareData(com, com.options.targetTable, data);

                        // Set the table paging
                        self.setPaging(com, com.options.targetTable);

                        // Build options for the lists
                        self.buildFilters(com, data);
    
                        // Add the list events
                        self.addFilterEvents(com);

                        // Handle invalid rows display
                        if (data.error_data) {
                            self.displayFileErrors(com, data);
                        }
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            },

            prepareData: function(com, targetTable, data) {
                if (data.table_data.length != 0) {
                    com.cache._(targetTable).tabulator('setData', data.table_data);
                }
                else {
                    this.setNoResults(com, targetTable);
                }
            },

            setNoResults: function(com, targetTable) {
                com.cache._(targetTable).find('.tabulator-table')
                .text(__('No results found.'));
            },

            updateFileIndex: function(com, updateMode) {
                var self = this;
    
                // Prepare the update url
                var updateUrl = com.options.scanUrl + '?update_mode=' + updateMode  + '&form_key=' + window.FORM_KEY;
    
                // Trigger the update request
                $.ajax({
                    type: 'POST',
                    url: updateUrl,
                    dataType: 'json',
                    showLoader: true,
                    success: function(data) {
                        self.getData(com);
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            },

            buildFilters: function(com, data) {
                if (com.filters && data.filter_data) {
                    // Create the group filter
                    this.createOptions(com, com.filters.group, data.filter_data.file_group);
        
                    // Create the type filter
                    this.createOptions(com, com.filters.type, data.filter_data.file_type);
        
                    // Create the locale filter
                    this.createOptions(com, com.filters.locale, data.filter_data.file_locale);
        
                    // Create the status filter
                    this.createOptions(com, com.filters.status, data.filter_data.file_status);
                }
            },

            createOptions: function(com, sel, arr) {
                var output = [];
                $.each(arr, function(key, value) {
                    // Create the option
                    var option = '<option value="' + value + '">' + value + '</option>';
    
                    // Add it to the output
                    output.push(option);
                });
                com.cache._(sel).append(output.join(''));
            },

            addFilterEvents: function(com) {
                var self = this;

                // Pager events
                com.cache._('.' + PAGER_SELECTOR).on('change', function() {
                    let selectedKey = $(this).find(':selected').val();
                    self.setPaging(com, com.options.targetTable, selectedKey);
                });

                // Filters events
                if (com.filters) {
                    var self = this;

                    // Prepare the fields
                    var fields = [
                        {selector: com.filters.group, field: 'file_group'},
                        {selector: com.filters.type, field: 'file_type'},
                        {selector: com.filters.locale, field: 'file_locale'}
                    ];
        
                    // Assign the events
                    $.each(fields, function(k, obj) {
                        com.cache._(obj.selector).on('change', function() {
                            let selectedKey = $(this).find(':selected').val();
                            self.updateFilters(
                                com,
                                { field: obj.field, type: '=', value: selectedKey }
                            );
                        });
                    });
                }
            },

            updateFilters: function(com, newFilter) {
                // Get the existing filters
                var filters = $(com.options.targetTable).tabulator('getFilters'); 
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
                com.cache._(com.options.targetTable).tabulator('clearFilter');
                com.cache._(com.options.targetTable).tabulator('setFilter', filters);
            },

            getScanPrompt: function(com) {
                var self = this;
                prompt({
                    title: __('Scan files'),
                    content: self.getScanPromptOptions([{
                            id: 'update_add',
                            name: 'update_mode',
                            value: 'update_add',
                            label: __('Add new files'),
                            note: __('Will add only new files to the index and preserve existing content not saved to files.'),
                        },
                        {
                            id: 'update_replace',
                            name: 'update_mode',
                            value: 'update_replace',
                            label: __('Replace all files'),
                            note: __('Will reload all files in the index and override existing content not saved to files.'),
                        }
                    ]),
                    actions: {
                        confirm: function() {
                            var optChecked = com.cache._('input[name=update_mode]:checked').val();
                            self.updateFileIndex(com, optChecked);
                        }, 
                        cancel: function(){}, 
                        always: function(){}
                    }
                });
            },

            getScanPromptOptions: function(opts) {
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

            updateEntityData: function(com, data) {
                // Prepare the variables
                var fileUpdateUrl = com.options.detailViewUrl + '?action=update_data&file_id=' + data.fileId + '&form_key=' + window.FORM_KEY;
                var rowData = {
                        row_content: data.rowContent
                    };
    
                // Send the the request
                $.ajax({
                    type: 'POST',
                    url: fileUpdateUrl,
                    data: rowData,
                    dataType: 'json',
                    success: function(res) {},
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            },

            loadRowDetails: function(com, rowData, isLogView) {
                // Prepare the variables
                var self = this;
    
                // Create the detail table
                com.cache._(com.options.detailView).tabulator({
                    langs: self.getLocaleData(com),
                    pagination: 'local',
                    layout: 'fitColumns',
                    responsiveLayout: true,
                    height: '100%',
                    resizableRows: true,
                    columns: self.getDetailColumns(),
                    cellEdited: function(cell) {
                        var row = cell.getRow();
                        self.updateEntityData(
                            com,
                            {
                                fileId: rowData.file_id,
                                rowContent: row.getData()
                            }
                        );
                    },
                    initialSort:[{
                        column: 'index', 
                        dir: 'asc'
                    }]
                });

                // Set the file path
                com.cache._(com.options.detailViewFilePath).text(rowData.file_path);
    
                // Update the data
                this.getRowDetails(com, rowData.file_id, isLogView);
    
                // Move the panels
                this.togglePanes(com, rowData.file_id);
            },

            getDetailColumns: function() {
                return [
                    {title: __('#'), field: 'index', sorter: 'number', width: 70},
                    {title: __('Key'), field: 'key', sorter: 'string', headerFilter: 'input', headerFilterPlaceholder: __('Search...'), formatter: 'textarea'},
                    {title: __('Value'), field: 'value', sorter: 'string', headerFilter: 'input', headerFilterPlaceholder: __('Search...'), formatter: 'textarea', editor: 'input'} 
                ];
            },

            getRowDetails: function(com, fileId, isLogView) {
                // Prepare the variables
                var self = this;
                var fileDetailsUrl = com.options.detailViewUrl;
                fileDetailsUrl += '?action=get_data';
                fileDetailsUrl += '&file_id=' + fileId;
                fileDetailsUrl += '&form_key=' + window.FORM_KEY;
                fileDetailsUrl += '&is_log_view=' + isLogView;

                // Send the the request
                $.ajax({
                    type: 'POST',
                    url: fileDetailsUrl,
                    dataType: 'json',
                    showLoader: true,
                    success: function(data) {
                        // Set the table data
                        core.prepareData(com, com.options.detailView, data);

                        // Set the table paging
                        self.setPaging(com, com.options.detailView);
                        com.cache._('.' + PAGER_SELECTOR).on('change', function() {
                            let selectedKey = $(this).find(':selected').val();
                            self.setPaging(com, com.options.detailView, selectedKey);
                        });

                        // Handle invalid rows display
                        if (data.error_data) {
                            self.displayRowErrors(com, data);
                        }
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            },

            displayRowErrors: function(com, data) {
                // Get the table rows
                var tableRows = com.cache._(com.options.detailView).tabulator('getRows');

                // Process the error display
                this.displayErrors(tableRows, data);
            },

            displayFileErrors: function(com, data) {
                // Get the table rows
                var tableRows = com.cache._(com.options.targetTable).tabulator('getRows');

                // Process the error display
                this.displayErrors(tableRows, data);
            },

            displayErrors(tableRows, data)  {
                tableRows.forEach(function(row) {
                    var rowIndex = row.getData().index;
                    if (data.error_data.indexOf(rowIndex) != -1) {
                        row.getElement().css({'background-color':'#FF9900'});
                    }
                });
            },

            togglePanes: function(com, id) {
                if (com.isListView) {    
                    // Move main table
                    com.cache._('#translation-table-list').animate({ left: '-50px' });
                    com.cache._('#translation-table-list').hide();
    
                    // Show the details table  
                    com.cache._('#translation-table-detail').show();
    
                    // Set the detail view state
                    com.isListView = false;
                    com.detailViewid = id;
                } else {
                    // Bring the panes back
                    com.cache._('#translation-table-detail').hide();
                    com.cache._('#translation-table-list').animate({ left: '0px' });
                    com.cache._('#translation-table-list').show();
    
                    // Set the detail view state
                    com.isListView = true;
                    com.detailViewid = 0;
                }
            }
        };
    }
);