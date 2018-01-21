var config = {
    map: {
        '*': {
            corejs:  'Naxero_Translation/js/translation/core'
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
