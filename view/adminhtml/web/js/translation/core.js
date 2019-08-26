define(
    [
        'jquery'
    ],
    function ($) {
        'use strict';

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

            setLocale: function(com) {
                // Todo : map m2 locales to tabulator js locales
                //$(this.options.targetTable).tabulator("setLocale", this.options.targetLocale);
                com.cache._(com.options.targetTable).tabulator("setLocale", 'en-us');
            },

            setPaging: function(com) {
                com.cache._(com.options.targetTable).tabulator("setPage", 1);
            },

            getData: function(com) {
                var self = this;
                $.ajax({
                    type: "POST",
                    url: com.options.dataUrl + '?form_key=' + window.FORM_KEY,
                    dataType: 'json',
                    showLoader: true,
                    success: function(data) {
                        // Set the table data
                        com.cache._(com.options.targetTable).tabulator("setData", data.table_data);
    
                        // Set the toolbar actions
                        com.setToolbarActions();

                        // Build options for the lists
                        self.buildFilters(com, data);
    
                        // Add the list events
                        self.addFilterEvents(com);

                        // Set the table locale
                        self.setLocale(com);

                        // Set the table paging
                        self.setPaging(com);
                    },
                    error: function(request, status, error) {
                        console.log(error);
                    }
                });
            },

            updateFileIndex: function(com, updateMode) {
                var self = this;
    
                // Prepare the update url
                var updateUrl = com.options.scanUrl + '?update_mode=' + updateMode  + '&form_key=' + window.FORM_KEY;
    
                // Trigger the update request
                $.ajax({
                    type: "POST",
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
                if (com.hasOwnProperty('filters') && data.hasOwnProperty('filter_data')) {
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
                if (com.hasOwnProperty('filters')) {
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
                            let selected = $(this).find(":selected").text();
                            let selectedKey = $(this).find(":selected").val();
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
            }
        };
    }
);