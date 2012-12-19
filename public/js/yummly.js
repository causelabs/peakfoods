	//create a Yummly object to namespace these functions
	var yummly = yummly || {};
	yummly.apiBaseUrl = yummly_url;
	
	yummly.appId = yummly_app_id;
	
	yummly.appKey = yummly_app_key;
	
	yummly.recipeIngredient = null;
	
	yummly.recipeId = null;
	
	yummly.setRecipeCard = function(ingredient,id){
		this.recipeIngredient = ingredient;
		this.recipeId = id;
	}
	
	yummly.getRecipiesFromApi = function(){
		ingredient = this.recipeIngredient;
		id = this.recipeId;
		
		if(!ingredient || !id){
			return false;
		}
		
		var options = [
			'_app_id=' + this.appId,
			'_app_key=' + this.appKey,
			'q=' + escape(ingredient)
		];
		
		//dietary filters
		if($("#dairyFree").is(':checked')) {
			options.push('allowedDiet%5B%5D=396%5EDairy-Free');
		}
		if($("#glutenFree").is(':checked')) {
			options.push('allowedDiet%5B%5D=393%5EGluten-Free');
		}
		if($("#noNuts").is(':checked')) {
			options.push('allowedDiet%5B%5D=394%5EPeanut-Free');
			options.push('allowedDiet%5B%5D=395%5ETree+Nut-Free');
		}
		if($("#soyFree").is(':checked')) {
			options.push('allowedDiet%5B%5D=400%5ESoy-Free');
		}
		if($("#sesameFree").is(':checked')) {
			options.push('allowedDiet%5B%5D=399%5ESesame-Free');
		}
		if($("#vegetarian").is(':checked')) {
			options.push('allowedDiet%5B%5D=387%5ELacto-ovo+vegetarian');
		}
		try{
			var url = this.apiBaseUrl + 'recipes';
			var getData = options.join('&');
			yummly.showLoader();
			$.ajax({
				async:true,
				url: url,
				type:'GET',
				cache:false,
				data:getData,
				dataType:'jsonp',
				success:function(data){
					var recipeHtml = '';
					if(data.matches && data.matches.length>0){
						for(i in data.matches){
							recipe = data.matches[i];
							recipeHtml += '<a class="recipeContainer" href="http://www.yummly.com/recipe/' + recipe.id + '" target="_blank">';
							recipeHtml += '<div class="clearfix" style="margin-bottom:10px;"></div>';
							if(recipe.smallImageUrls && recipe.smallImageUrls.length>0){
								recipeHtml += '<img src="' + recipe.smallImageUrls[0] + '" />';
							}
							recipeHtml += '<div class="recipeName">' + recipe.recipeName + '</div>';
							
							//stars
							if(recipe.rating && recipe.rating > 0){
								var starWidth = Math.floor(100 * (recipe.rating / 5));
								var starText = (Math.round(recipe.rating * 10) / 10) + " stars";
								recipeHtml += '<div class="recipeStarsContainer"><div class="recipeStars" style="width:' + starWidth + 'px;" title = "' + starText + '"></div></div>';
							}
							ingredients = recipe.ingredients.join(', ');
							if(ingredients.length>100) ingredients = ingredients.substr(0,100) + '...';
							recipeHtml += '<p class="recipeIngredients">' + ingredients + '</p>';
							recipeHtml += '<div class="clearfix" style="margin-bottom:10px;"></div>';
							recipeHtml += '</a>';
						}
						recipeHtml += '<div class="yummlyAttribution">' + data.attribution.html + '</div>';
					}
					else {
						recipeHtml += '<p class="noRecipes">There are no recipes that match your search criteria</p>';
					}
					$('#recipes .recipeScroller').html(recipeHtml);
					yummly.hideLoader(id);
				},
				error:function() {
					recipeHtml = '<p class="noRecipes">There are no recipes that match your search criteria</p>';
					$('#recipes .recipeScroller').html(recipeHtml);
					yummly.hideLoader();
				}
			})
		}
		catch(e){
			recipeHtml = '<p class="noRecipes">Failed to retrieve recipes.</p>';
			$('#recipes .recipeScroller').html(recipeHtml);
			yummly.hideLoader();
		}
	}
	
	yummly.showLoader = function(){
		$('#recipes .recipeScroller').hide();
		$('#recipes .recipeLoader').show();
	}
	
	yummly.hideLoader = function(){
		$('#recipes .recipeScroller').show();
		$('#recipes .recipeLoader').hide();
	}
