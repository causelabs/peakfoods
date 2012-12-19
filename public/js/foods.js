	var facebookId, removeItems;
	var nutritionListLeftItems = {
		'calories' : 'Calories',
		'protein' : 'Protein',
		'charbohydrate' : 'Carbohydrates',
		'total_daily_fiber' : 'Fiber',
		'sugar' : 'Sugar',
		'calcium' : 'Calcium',
		'iron' : 'Iron',
		'magnesium' : 'Magnesium',
		'phosphorus' : 'Phosphorus',
		'potassium' : 'Potassium',
		'sodium' : 'Sodium',
		'zinc' : 'Zinc',
		'copper' : 'Copper',
		'manganese' : 'Manganese'
	};
	var nutritionListRightItems = {
		'selenium' : 'Selenium',
		'vitamin_c' : 'Vitamin C',
		'thiamin' : 'Thiamin',
		'riboflavin' : 'Riboflavin',
		'niacin' : 'Niacin',
		'vitamin_b12' : 'Vitamin B12',
		'vitamin_a' : 'Vitamin A',
		'vitamin_e' : 'Vitamin E',
		'vitamin_k' : 'Vitamin K',
		'saturated_fat' : 'Saturated Fat',
		'mono_unsaturated_fat' : 'Mono-unsaturated Fat',
		'poly_unsaturated_fat' : 'Poly-unsaturated Fat',
		'cholesterol' : 'Cholesterol'
	};
	var nutritionInfo = [];
	//get the foods for the selected month and state
	function getFoodList(){
		var month=$("#month").val();
		var state=$("#location").val();
		
		$("#noFoods").fadeOut();
		
		$.ajax({
			async:true,
			url: jsBase + '/index/foods',
			type:'POST',
			data:'format=json&state=' + state + '&month=' + month,
			success:function(data){
				
				if(data.foods.length > 0){
					//loop through each food item
					var newItems='';
					//add a removeMe class to all items
					//$('#foods').isotope('remove' , $("#foods div.food"));
					//$('#foods').html('');
					$('#foods div.food').addClass("removeMe");
					
					for(var i in data.foods){
						var id=data.foods[i].id;
						//if the item exists, remove the ".removeMe" class
						if($("div#food" + id).length>0){
							$("div#food" + id + ",div#recipe" + id + ",div#nutrition" + id).removeClass("removeMe");
						}
						//otherwise, add the item
						else {
							newItems += '<div class="food overview ' + data.foods[i].type + '" data-category="' + data.foods[i].type + '" id="food' + id + '" food="' + data.foods[i].name + '">' + 
								
								'<h2 class="name" style="background-color:' + data.foods[i].background_color + '">' + data.foods[i].name + '' + 
								'<div class="buttons"><a href="#" class="recipeButton"><span>Recipes</span></a>';
							if(data.foods[i].nutrition){
								newItems += '<a href="#" class="nutritionButton"><span>Nutrition</span></a>';
							}
							newItems += '</div></h2>' + 
							'</div>';
							if(data.foods[i].nutrition && !nutritionInfo[data.foods[i].name]){
								nutritionInfo[data.foods[i].name] = [];
								nutritionInfo[data.foods[i].name].header = data.foods[i].name + ' <span>' + data.foods[i].nutrition['unit'] + '</span>';
								nutritionInfo[data.foods[i].name].content = '';
								
								
								
								nutritionInfo[data.foods[i].name].content = '<ul class="nutritionListLeft">';
								for(var nutrient in nutritionListLeftItems){
									if(data.foods[i].nutrition[nutrient]){
									
										nutritionInfo[data.foods[i].name].content += '<li><span class="left">' + nutritionListLeftItems[nutrient] + '</span><span class="center"></span><span class="right">' + 
											data.foods[i].nutrition[nutrient] + (nutrient=='calories'?'':'g') + '</span></li>';	
									}
								}
								nutritionInfo[data.foods[i].name].content += '</ul><ul class="nutritionListRight">';
								for(nutrient in nutritionListRightItems){
									if(data.foods[i].nutrition[nutrient]){
									
										nutritionInfo[data.foods[i].name].content += '<li><span class="left">' + nutritionListRightItems[nutrient] + '</span><span class="center"></span><span class="right">' + 
											data.foods[i].nutrition[nutrient] +  'g</span></li>';	
									}
								}
								nutritionInfo[data.foods[i].name].content += '</ul>';
							}
						}
					}
					//remove any items that don't have the justAdded class
					removeItems=$('.removeMe');
					$('#foods').isotope('remove' , removeItems);
					//go back to viewing the overivews
					var isotopeOptions = {filter : '.overview'};
					$('#foods').isotope(isotopeOptions);
					
					if(newItems.length>0){
						$('#foods').isotope('insert' , $(newItems),function(){
							//go back to the overviews
							//$("#foods").isotope('reLayout');;
							filterFoodTiles();
							
							//set up the food buttons
							$(".food").on('mouseenter',function(){
								$(this).children('.overview h2').addClass('fill');
							}).on('click',function(){
								$(this).children('.overview h2').addClass('fill');
							}).on('mouseleave',function(){
								$(this).children('.overview h2').removeClass('fill');
							});
							
							$('.recipeButton').on('click',function(){
								$(".recipe").show();
								var id = $(this).parents('.food').attr('id');
								id = id.replace(/[^0-9]/g,'');
								var color = id = $(this).parents('.food').css('background-color');
								isotopeOptions = {filter : '#recipes'};
								$('#foods').isotope(isotopeOptions);
								var foodName = $(this).parents('.food').attr("food");
								$('#recipes .cardColorOverlay').css('background-color',color);
								$('#recipes').attr('food',foodName);
								$('#recipes h2').html('Recipes with ' + foodName);
								scrollToTop();
								yummly.setRecipeCard(foodName,id);
								yummly.getRecipiesFromApi();
								return false;
							});
							
							$('.nutritionButton').on('click',function(){
								$(".nutrition").show();
								var id = $(this).parents('.food').attr('id');
								id = id.replace(/[^0-9]/g,'');
								var foodName = $(this).parents('.food').attr("food");
								var color = id = $(this).parents('.food').css('background-color');
								$('#nutritionInfo .cardColorOverlay').css('background-color',color);
								$('#nutritionInfo').attr('food',foodName);
								if(nutritionInfo[foodName]){
									$("#nutritionInfo h2").html(nutritionInfo[foodName].header);
									$("#nutritionContent").html(nutritionInfo[foodName].content);
									isotopeOptions = {filter : '#nutritionInfo'};
									$('#foods').isotope(isotopeOptions);
								}
								scrollToTop();
								return false;
							});
							
							$('.closeButton').on('click',function(){
								yummly.setRecipeCard(null,null);
								filterFoodTiles();
								$("#foods").isotope('reLayout');
							});
							
						});
					}
					
					//make sure everything displays properly
					//window.setTimeout(function(){
					//	$("#foods").isotope('reLayout');
					//},1000);
					
				}	
				else{
					$("#noFoods").fadeIn('normal');
					
					//remove all items
					removeItems = $("#foods div.food");
					if(removeItems.length > 0){
						$('#foods').isotope('remove' , removeItems);
					}
				}
				
			}
		});
	}
	
	var $foods;

	var geocoder;
	$(function() {

		$foods = $('#foods');

		$foods.isotope({
			itemSelector : '.food,#recipes,#nutritionInfo',
			getSortData : {
				name : function(el){
					try{
						var foodName = el.attr('food');
						return foodName;
					}
					catch(e){
						return '';
					}
				}
			},
			sortBy : 'name'
		});

		var $optionSets = $('#options .option-set'), $optionLinks = $optionSets.find('a');

		$optionLinks.click(function() {
			var $this = $(this);
			// don't proceed if already selected
			if($this.hasClass('selected')) {
				return false;
			}
			var $optionSet = $this.parents('.option-set');
			$optionSet.find('.selected').removeClass('selected');
			$this.addClass('selected');

			filterFoodTiles();

			return false;
		});
		
		//trigger window resize to fix the layout issue
		$(window).resize();
		
		$("#location,#month").bind("change",function(){
			getFoodList();
		});
		
		//get the initial foods
		//getFoodList();
		
		//create the geocoder object
		geocoder = new google.maps.Geocoder();
		
		//try to find their location
		if(navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(positionSuccess, positionError);
		}
		else{
			getFoodList();
		}

	});

	function filterFoodTiles(){
		var $optionSets = $('#options .option-set'), $optionLinks = $optionSets.find('a'), $selectedFood = $optionSets.find('.selected');
		// make option object dynamically, i.e. { filter: '.my-filter-class' }
		var isotopeOptions = {}, key = $optionSets.attr('data-option-key'), value = $selectedFood.attr('data-option-value');
		// parse 'false' as false boolean
		value = value === 'false' ? false : value;
		isotopeOptions[key] = value;
		if(key === 'layoutMode' && typeof changeLayoutMode === 'function') {
			// changes in layout modes need extra logic
			changeLayoutMode($this, isotopeOptions);
		} else {
			// otherwise, apply new isotopeOptions
			$('#foods').isotope(isotopeOptions);
		}
	}
	
	//Get the latitude and the longitude;
	function positionSuccess(position) {
		var lat = position.coords.latitude;
		var lng = position.coords.longitude;
		codeLatLng(lat, lng);
	}

	function positionError() {
		//just go with the current state
		getFoodList();
	}

	function codeLatLng(lat, lng) {

		var latlng = new google.maps.LatLng(lat, lng);
		var state;
		geocoder.geocode({
			'latLng' : latlng
		}, function(results, status) {
			if(status == google.maps.GeocoderStatus.OK) {
				//console.log(results)
				if(results[1]) {
					//formatted address
					//alert(results[0].formatted_address)
					//find country name
					for(var i = 0; i < results[0].address_components.length; i++) {
						for(var b = 0; b < results[0].address_components[i].types.length; b++) {

							//there are different types that might hold a city admin_area_lvl_1 usually does in come cases looking for sublocality type will be more appropriate
							if(results[0].address_components[i].types[b] == "administrative_area_level_1") {
								//this is the object you are looking for
								state = results[0].address_components[i];
								break;
							}
						}
					}
					//city data
					if(state) {
						var stateCode = state.short_name;
						$("#location").val(stateCode);
						getFoodList();
					}
					//alert(state.short_name + ":" + state.long_name)

				} else {
					getFoodList();
				}
			} else {
				getFoodList();
			}
		});
	}
	
	function scrollToTop()
	{
		$('html, body').animate({
			scrollTop: $("#foods").offset().top
		}, 1000);
	}
	
	