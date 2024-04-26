var config = {
    paths: {
    	'ktpl/goup':        	'Ktpl_Ajaxscroll/js/jquery.goup.min',
        'ktpl/ias' : 		'Ktpl_Ajaxscroll/js/jquery-ias',
        'ktpl/callbacks' : 	'Ktpl_Ajaxscroll/js/callbacks',
	'ktpl/paging' : 	'Ktpl_Ajaxscroll/js/extension/paging',
	'ktpl/history' : 	'Ktpl_Ajaxscroll/js/extension/history',
	'ktpl/trigger' : 	'Ktpl_Ajaxscroll/js/extension/trigger',
	'ktpl/spinner' : 	'Ktpl_Ajaxscroll/js/extension/spinner',
	'ktpl/noneleft' : 'Ktpl_Ajaxscroll/js/extension/noneleft',
    },
	shim: {
		'ktpl/ias': {
			deps: ['jquery']
		},
		'ktpl/goup': {
			deps: ['jquery']
		},
	}
};
