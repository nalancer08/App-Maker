// Declare an App class
App = Class.extend({
	tabBarTemplate: null,
	navBarTemplate: null,
	imageTemplate: null,
	textViewTemplate: null,
	buttonTemplate: null,
	screenComponents: {},
	init: function() {
		var obj = this;
		jQuery(document).ready(function($) {
			obj.onDomReady();
			obj.getElements();
		});
		//
		// Add any additional initialization here
		//
	},
	onDomReady: function() {

		var obj = this;
		var cont = 0;

		// $.getJSON('./data/tabBar.json', function(json) {
		// 	console.log(json);
		// 	// console.log(json.tabBar.buttons);
		// 	// console.log(json.tabBar.bg_color);
		// 	// console.log(jsonn.tabBar.buttons[2]);

		// 	// var start = new Date();
			
		// 	// $.each(json.tabBar, function(key, val) {

		// 	// 	console.log($.type(key) + " : " + $.type(val) );
		// 	// 	console.log(key + " : " + val + " \n");
		// 	// 	if ( $.type(val) === "array" ) {
		// 	// 		for ( var i = 0; i < val.length; i++ ) {
		// 	// 			console.log("      => " + $.parseJSON(JSON.stringify(val[i])) );
		// 	// 			$.each(val[i], function(key2, val2) {
		// 	// 				console.log("             => " + $.type(key2) + " : " + $.type(val2) );
		// 	// 				console.log("             => " + key2 + " : " + val2);
		// 	// 			});
		// 	// 		}
		// 	// 	}
		// 	// });

		// 	// var time = new Date() - start;
		// 	// console.log("Tiempo tomado = " + time);


		// 	var start2 = new Date();

		// 	var as = json.tabBar;
		// 	for ( var key in as  ) {
		// 		console.log($.type(key) + " : " + $.type(as[key]) );
		// 		console.log(key + " : " + as[key] + " \n");
		// 		if ( $.type(as[key]) === "array" ) {
		// 			for ( var i = 0; i < as[key].length; i++ ) {
		// 				for ( var key2 in as[key][i] ) {
		// 					var val2 = as[key][i][key2];
		// 					console.log("         => " + $.type(key2) + " : " + $.type(val2) );
		// 					console.log("         => " + key2 + " : " + val2);
		// 				}
		// 			}
		// 		}
		// 	}

		// 	var time2 = new Date() - start2;
		// 	console.log("Tiempo tomado = " + time2);

		// });


		$('.tab_bar').on('click', function(e){

			e.preventDefault();

			var el = $(this);

			// el.addClass('hide');
			$('.screen .tab_screen').append('<div class="item tab_item item_' + cont +'"></div>');

			// Create the logic object
			var tab = obj.generateNewElement('tabBar');
			var la = '.item_' + cont;
			obj.screenComponents.tabBar = tab;
			cont = cont + 1;

			$(la).pep({

				droppable: '.tab_screen',
				constrainTo: '.screen',
			 	drag: function(ev, obj) {
			 		// console.log(obj);
			 		var item = $('.tab_item');
			 		var inspector = $('.inspector');

			 		$('.opts').remove();

			 		inspector.append('<div class="opts"> <p>Width: ' + item.width() + 
						'</p> <br> <p>Height: ' + item.height() +
						'</p> <br> <p>X: ' + item.position().left + 
						'</p> <br> <p>Y: ' + item.position().top + '</p> </div>'
					);
			  	}
			});
		});


		$('.screen').on('click', '.tab_item', function(e){
			var el = $(this);

			var lala = '.opts';
	 		$(lala).remove();	

			console.log(el.position());
			$('.inspector').append('<div class="opts"> <p>Width: ' + el.width() + 
				'</p> <br> <p>Height: ' + el.height() +
				'</p> <br> <p>X: ' + el.position().left + 
				'</p> <br> <p>Y: ' + el.position().top + '</p> </div>'
			);
		}); 
	},
	getElements: function() {

		var obj = this;

		$.getJSON('./data/tabBar.json', function(json) {
			// console.log(json);
			obj.tabBarTemplate = json.tabBar;
		});
	},
	generateNewElement: function(type, conditions) {

		// Initialize variables
		var obj = this,
			element = null;

		// Get the type of the object
		switch(type) {

			case 'tabBar':
				element = obj.tabBarTemplate;
			break;
		}

		// Clean the object
		for ( var key in element ) {
			console.log($.type(key) + " : " + $.type(element[key]) );
			console.log(key + " : " + element[key] + " \n");
			if ( $.type(element[key]) == "array" ) {
				for ( var i = 0; i < element[key].length; i++ ) {
					for ( var key2 in element[key][i] ) {
						var val2 = element[key][i][key2];
						console.log("         => " + $.type(key2) + " : " + $.type(val2) );
						console.log("         => " + key2 + " : " + val2);
						element[key][i][key2] = "";			
					}
				}
			} else {
				element[key] = "";
			}
		}

		return element;
	},
	printInInspector: function(object, inspector) {

	}
});

// Declare a global app object
var app = new App();