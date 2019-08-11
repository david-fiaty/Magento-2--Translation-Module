var config = {
    map: {
        '*': {
            filesjs:  'Naxero_Translation/js/translation/files',
            stringsjs:  'Naxero_Translation/js/translation/strings'
        }
    },
  	paths: {
        tabulator: 'Naxero_Translation/js/tabulator/tabulator.min'
  	},
    shim: {
        tabulator: {
            deps: ['jquery', 'jquery/ui']
        }
    }
};
