define([
    'jquery', 'mage/url', 'tabulator'
], function($, urlBuilder, tabulator) {

    'use strict';

    // Build the widget
    $.widget('mage.corejs', {

        // Prepare the options
        cache: null,
        isListView: true,
        options: {
            targetTable: '#translation-table-content',
            detailView:  '#translation-table-detail-content',
            targetLocale: '',
            dataUrl: '',
            scanUrl: '',
            detailViewUrl: '',
            detailViewid: 0,
            paging: 30
        },

        _create: function() {
            this.cache = new this._cache();
            this._bind();
        },

        _cache: function() {
            var collection = {};

            function get_from_cache( selector ) {
                if ( undefined === collection[ selector ] ) {
                    collection[ selector ] = $( selector );
                }

                return collection[ selector ];
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
                selectable: true,
                layout: "fitColumns",
                responsiveLayout:true,
                height:"100%",
                columns: self.getListColumns(),
                rowClick:function(e, row){
                    self.loadRowDetails(row.getData());
                },
                rowSelectionChanged:function(data, rows){
                    self.cache._("#select-stats span").text(data.length);
                },
            });

            //Load the data into the table
            $.ajax({
                type: "GET",
                url: self.options.dataUrl,
                dataType: 'json',
                showLoader: true,
                success: function(data) {
                    self.cache._(self.options.targetTable).tabulator("setData", data);
                },
                error: function (request, status, error) {
                    console.log(error);
                }   
            });

            // Configure the behavior
            this.setBehavior();
        },

        setBehavior: function () {
            // Set the language
            this.setLocale();

            // Set the toolbar actions
            this.setToolbarActions();

            // Set the pagination
            this.setPaging();

            // Set selectable rows
            this.setSelectable();
        },

        setLocale: function () {
            // Todo : map m2 locales to tabulator js locales
            //$(this.options.targetTable).tabulator("setLocale", this.options.targetLocale);
            this.cache._(this.options.targetTable).tabulator("setLocale", 'en-us');
        },

        setPaging: function () {
            this.cache._(this.options.targetTable).tabulator("setPage", 1);
        },

        setSelectable: function () {
            // Assign this to self
            var self = this;

            // Selectable rows
            this.cache._("#select-row").click(function(){
                self.cache._(self.options.targetTable).tabulator("selectRow", 1);
            });

            // Deselect row on "deselect" button click
            this.cache._("#deselect-row").click(function(){
                self.cache._(self.options.targetTable).tabulator("deselectRow", 1);
            });
        },

        setToolbarActions: function () {
            // Assign this to self
            var self = this;

            // Back button
            this.cache._("#button-back").click(function(){
                self.togglePanes(0);
                self.cache._(self.options.detailView).tabulator("destroy");
            });

            // Trigger download of data.csv file
            this.cache._("#download-file").click(function(){
                // Todo : improve file naming from metadata
                $(self.options.detailView).tabulator("download", "csv", "translation_strings.csv");
            });

            // File index update
            this.cache._("#update-files").click(function(){
                $.ajax({
                    type: "GET",
                    url: self.options.scanUrl,
                    dataType: 'json',
                    showLoader: true,
                    success: function(data) {
                    },
                    error: function (request, status, error) {
                        console.log(error);
                    }   
                }).done(function (data) {
                    self.cache._(self.options.targetTable).tabulator("setData", data);
                });         
            });  

            // File strings update
            this.cache._("#update-strings").click(function(){
                self.updateRowDetails(self.detailViewid);
            });  
        },

        getListColumns: function () {
            return [
                {title:"Id", field: "file_id", sorter:"number", width:25},
                {title:"Path", field: "file_path", sorter:"string"},
                {title:"Created", field: "file_creation_time", sorter:"string"},
                {title:"Updated", field: "file_update_time", sorter:"string"},
                {title:"Lines", field: "file_count", sorter:"string"},
                {title:"Type", field: "file_type", sorter:"string"},
                {title:"Group", field: "file_group", sorter:"string"},
                {title:"Locale", field: "file_locale", sorter:"string"}
            ];
        },

        getDetailColumns: function () {
            return [
                {title:"Key", field: "key", sorter:"string"},
                {title:"Value", field: "value", sorter:"string"}
            ];
        },

        togglePanes: function (id) {
            if (this.isListView) {
                 // Get main table width
                var tableWidth = this.cache._('#translation-table-list').outerWidth() + 'px';

                // Move main table
                this.cache._('#translation-table-list').animate({left: '-50px'}); 
                this.cache._('#translation-table-list').hide();

                // Show the details table  
                this.cache._('#translation-table-detail').show();

                // Set the detail view state
                this.isListView = false;
                this.detailViewid = id;
            }
            else {
                // Bring the panes back
                this.cache._('#translation-table-detail').hide();
                this.cache._('#translation-table-list').animate({left: '0px'}); 
                this.cache._('#translation-table-list').show(); 

                // Set the detail view state
                this.isListView = true;
                this.detailViewid = 0;
            }
        },

        loadRowDetails: function (fileObj) {
            // Prepare the variables
            var self = this;

            // Move the panels
            this.togglePanes(fileObj.file_id);

            // Create the detail table
            this.cache._(self.options.detailView).tabulator({
                pagination: "local",
                paginationSize: self.options.paging,
                selectable: true,
                layout: "fitColumns",
                responsiveLayout:true,
                height:"100%",
                columns: self.getDetailColumns(),
                rowSelectionChanged:function(data, rows){
                    self.cache._("#select-stats span").html(data.length);
                },
            });

            // Set the file path
            this.cache._('#translation-file-path').text(fileObj.file_path);

            // Update the data
            this.updateRowDetails(fileObj.file_id);
        },

        updateRowDetails: function (fileId) {
            // Prepare the variables
            var self = this;
            var fileDetailsUrl = self.options.detailViewUrl + '?file_id=' + fileId;

            // Get the detail view data
            $.ajax({
                type: "GET",
                url: fileDetailsUrl,
                dataType: 'json',
                showLoader: true,
                success: function(data) {
                   self.cache._(self.options.detailView).tabulator("setData", data);
                },
                error: function (request, status, error) {
                    console.log(error);
                }   
            });         
        },
    })

    return $.mage.corejs;
});
