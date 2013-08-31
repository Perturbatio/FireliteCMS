@section('document_title')
	@parent
	Firelite User Management
@endsection

<script>
/**
 * This is a global config that will be available to all modules loaded after this file
 */
YUI.GlobalConfig = {
	groups:{
		heehawmodules: {
			base: '/js/libs/heehaw-modules/',
			modules:{
				'js-enhancements':{}
			}
			
		}
	},
	gallery: 'gallery-2013.01.16-21-05'
};
/*
	debug: true,
	filter: 'raw',
	combine: false
 */
YUI().use(
	'gallery-querybuilder', 
	'gallery-formmgr', 
	'gallery-paginator', 
	'array-extras',
	'js-enhancements', 
	'node', 
	'event', 
	"json-parse", 
	"datatable",
	'datatable-scroll', 
	"datasource-get",
	"datasource-local", 
	"datasource-jsonschema", 
	"datasource-arrayschema", 
	"datatable-datasource", 
	"button",
function(Y){
	var dataURL = '',
		var_type,
		paginator,
		query,
		queryForm,
		query_builder_items,
		filters,
		query_builder_operations,
		sortCaseInsensitive = Y.Helpers.sortCaseInsensitive,
		datatable_columns,
		userData = <?=eloquent_to_json($users);?>,
		tableDataSource,
		dataSourceSchema,
		localDataSource;


	query_builder_items =
	[
		{
			name: 'id',
			type: 'number',
			text: 'Id',
			validation: 'yiv-length:[,64]'
		},
		{
			name: 'username',
			type: 'string',
			text: 'Username',
			validation: 'yiv-length:[,64]'
		}
	];
	
	//the operations that are exposed for each datatype
	query_builder_operations = {
		string: [
			{ value: 'equal',       text: 'Is' },
			{ value: 'contains',    text: 'Contains' },
			{ value: 'starts-with', text: 'Starts with' },
			{ value: 'ends-with',   text: 'Ends with' }
		],
 
		number: [
			{ value: 'equal',         text: '= Equals' },
			{ value: 'less',          text: '< Less than' },
			{ value: 'less-equal',    text: '<= Less than or equal to' },
			{ value: 'greater',       text: '> Greater than' },
			{ value: 'greater-equal', text: '>= Greater than or equal to' }
		],
 
		select: [
			'EQUALS'
		]
	};
	
	//define the collection of filter functions available for each type
	filters = {
		string: {
			equal: function(value, filter) {
				if (!Y.Lang.isUndefined(value) && !Y.Lang.isUndefined(filter)){
					return (value.toLowerCase() == filter.toLowerCase());
				}
				return false;
			},
			contains: function(value, filter) {
				return (value.toLowerCase().indexOf(filter.toLowerCase()) >= 0);
			},
			'starts-with': function(value, filter) {
				return (value.toLowerCase().substr(0, filter.length) == filter.toLowerCase());
			},
			'ends-with': function(value, filter) {
				return (value.toLowerCase().substr(-filter.length) == filter.toLowerCase());
			}
		},
		
		number: {
			equal: function(value, filter) {
				return (parseInt(value, 10) == parseInt(filter, 10));
			},
			less: function(value, filter) {
				return (parseInt(value, 10) <= parseInt(filter, 10));
			},
			greater: function(value, filter) {
				return (parseInt(value, 10) >= parseInt(filter, 10));
			}
		}
	};
	
	//a name=>type lookup to determine the type of an item quickly
	var_type = {};
	for ( var i = 0; i < query_builder_items.length; i++ ){
		var_type[ query_builder_items[i].name ] = query_builder_items[i].type;
	}
	
	//// DATASOURCE ////
	// Wrap ordinary local data source to implement filtering and pagination. (allows for totalRecords props etc
	// (All DataSource*Schema plugins prevent DataSource._defDataFn from executing.)
	function FilterDataSource()
	{
		FilterDataSource.superclass.constructor.apply(this, arguments);
	}
	
	/**
	 *
	 */
	function generateRequest(){
		var state    = paginator.getState();
		state.filter = query.toDatabaseQuery();
		
		return state;
	}

	/**
	 * 
	 */
	function sendRequest(){
		dataTable.datasource.load(
		{
			request: generateRequest()
		});
	}
	
	
	function filterQuery(e)
	{
		e.halt();
		if (queryForm.validateForm())
		{
			paginator.setPage(1, true);
			sendRequest();
		}
	}
	
	//// FILTERING ///


	/**
	 * apply each filter to the data (by calling applyFilter)
	 * 
	 * @var array data
	 * @var array filter
	 */
	function filterData( data, filter){
		
		for (var i=0; i<filter.length; i++)
		{
			data = applyFilter(data, filter[i]);
		}
		return data;
	}

	/**
	 * apply a single filter to an array of data
	 * 
	 * @var array data
	 * @var array filter
	 */
	function applyFilter(data,filter) {
		
		var key  = filter[0],
			op   = filter[1],
			val  = filter[2],
			type = var_type[key];
			
		return Y.Array.filter(data, function(v)
		{
			return filters[type][op](v[key], val);
		});
	}

	FilterDataSource.NAME = "filterdatasource";

	Y.extend(FilterDataSource, Y.DataSource.Local,
	{
		_defRequestFn: function(e)
		{
			var self	= this,
				payload = e.details[0];

			this.get('source').sendRequest(
			{
				request: e.request,
				callback:
				{
					success: function(e1)
					{
						
						payload.data = e1.response.results;
						payload.meta = e1.response.meta;
						self.fire('data', payload);
					},
					failure: function(e1)
					{
						payload.error = e1.error;
						self.fire('data', payload);
					}
				}
			});
		},

		_defDataFn: function(e)
		{
			if ( Y.Lang.isUndefined( e.request) ){
				return;
			}
			
			var data = filterData(e.data, e.request.filter),
				response = {
					results: data.slice(e.request.recordOffset, e.request.recordOffset + e.request.rowsPerPage),
					meta:
					{
						totalRecords: data.length
					}
				};

			this.fire("response", Y.mix({response: response}, e));
		}
	});
	
	// configure data source

	dataSourceSchema = {
		resultFields:
		[
			'id', 'username', 'created_at', 'updated_at'
		]
	};

	localDataSource = new Y.DataSource.Local({source: userData});
	localDataSource.plug({
		fn:  Y.Plugin.DataSourceArraySchema,
		cfg: {schema:dataSourceSchema}
	});

	tableDataSource = new FilterDataSource({source: localDataSource});
	
	/**
	 * @var object o
	 * @return string
	 */
	function formatterActions(o){
		var result;
			result = '<a href="edit/' + o.data.id + '" title="Edit this user" class="pure-button btn_edit">Edit</a>';
		return result;
	}
	
	
	function formatDate(result){
		result = result.substring(0, result.length - 9);
		if(result == "0000-00-00"){
			result = '';
		}
		return result;
	}
	
	function formatUpdatedDate(o){
		return formatDate(o.data.updated_at);
	}
	
	
	function formatCreatedDate(o){
		return formatDate(o.data.created_at);
	}
	
	function formatSignup(o){
		//remove the time from display
		var result = o.data.created_at;
		result = result.substring(0, result.length - 9);
		return result;
	}
	
	//// DATATABLE ////
	
	datatable_columns = [
		{
			key: 'id',
			label: 'Id'
		},
		{
			key: 'username', 
			label: 'Username',
			sortFn: sortCaseInsensitive,
			allowHTML: false // to avoid HTML escaping
		},
		{
			key:  'actions',
			allowHTML: true, // to avoid HTML escaping
			label: 'Actions',
			formatter: formatterActions,
			emptyCellValue: '<span>No actions</span>'
		}
	];
	
	dataTable = new Y.DataTable({
		columns: datatable_columns,
		summary: 'Firelite Users',
		/*scrollable: 'y',*/
		height: '600px',
		sortable  : ['username'],
		sortBy: [ 'id'],
		recordType: [ 'id', 'username'],
		width: '100%'
	});
	
	dataTable.plug(Y.Plugin.DataTableDataSource, {datasource: tableDataSource});

	//// QUERY BUILDER ////
	query = new Y.QueryBuilder(query_builder_items, query_builder_operations);
	query.render('#query');


	
	//// QUERY BUILDER FORM HANDLER ////
	queryForm = new Y.FormManager('query_form');
			queryForm.prepareForm();

	Y.on('click', function(){
		query.reset();
		sendRequest();
	},
	'#reset');
	
	Y.on('click', filterQuery, '#apply');
	Y.on('submit', filterQuery, '#query-form');

	//// PAGINATOR ////

	paginator = new Y.Paginator({
		totalRecords: 0,
		rowsPerPage: 100,
		template: '{FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink} <span class="rpp">Rows per page:</span> {RowsPerPageDropdown}',
		rowsPerPageOptions:    [10,20,50],
		firstPageLinkLabel:    '|&lt;',
		previousPageLinkLabel: '&lt;',
		nextPageLinkLabel:     '&gt;',
		lastPageLinkLabel:     '&gt;|'
	});
	
	
	paginator.render('#paginator');

	paginator.on('changeRequest', function(state){
		this.setPage(state.page, true);
		
		this.setRowsPerPage(state.rowsPerPage, true);
		sendRequest();
	});

	tableDataSource.on('response', function(e){
		paginator.setTotalRecords(e.response.meta.totalRecords, true);
		paginator.render();
	});
	
	//// RENDER THE DATATABLE NOW ////
	Y.one('#users-table-container').empty();//clear the contents of the placeholder div
	dataTable.render('#users-table-container');
	dataTable.datasource.load();
	dataTable.detach('*:change');
	
	sendRequest();
	
});
</script>

<div class="panel">
	<div class="panel-header">
		<h2>Firelite User Management</h2>
	</div>
	<div class="panel-content">
		<?php

		if ( !isset( $validation ) ){
			$validation = null;
		}
		
		if ( isset( $response ) ){
			echo $response;
		}
		?>
		<div class="pure-g">
			
			<div class="pure-u-1">
				<h2>Filter</h2>
				<form id="query-form" name="query_form">
					<div id="query"></div>
				</form>
				<br />
				<div id="controls">
					<button id="apply" class="pure-button">Apply</button>
					<button id="reset" class="pure-button">Clear</button>
				</div>
				<br />
				<br />
				<div id="paginator"></div>
				<br />
				<div id="users-table-container">
					Loading...
				</div>
			</div>
			
		</div><!-- end grid -->
	</div>
</div>
