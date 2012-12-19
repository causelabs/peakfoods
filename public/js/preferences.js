	var facebookId;
	
	$(function() {
		//mobile menu button

		$(".menu-btn").click(function(){
			$("#meta").toggleClass("on");
		});
		
		//recipe settings button

		$("#recipe-settings-link").click(function(){
			$("#recipe-settings").toggleClass("on");
		});
		
		$("#recipe-settings input[type=checkbox]").bind("click keyup",function(){
			setPreferences();
			if(yummly){
				yummly.getRecipiesFromApi();
			}
		});
		
		//facebook buttons
		$("#auth-loginlink").click(function(){
			login();
			return false;
		});
		$("#auth-logoutlink").click(function(){
			logout();
			return false;
		});
	});
	
	function login(){
		FB.login(function(response) {
			if (response.authResponse) {
				//passthru
			} 
		});
	}
	
	function logout(){
		FB.logout();
	}
	
	function logout(){
		FB.logout();
	}
	
	function getPreferences(){
		if((facebookId == null) || facebookId<1)
			return null;
		$.ajax({
			async:true,
			url: jsBase + '/index/preferences',
			type:'GET',
			cache:false,
			data:'format=json&id=' + facebookId,
			success:function(data){
				if(data.preferences){
					if(data.preferences.dairyFree == "1")
						$("#dairyFree").attr("checked","checked");
					else
						$("#dairyFree").removeAttr("checked");
					if(data.preferences.glutenFree == "1")
						$("#glutenFree").attr("checked","checked");
					else
						$("#glutenFree").removeAttr("checked");
					if(data.preferences.soyFree == "1")
						$("#soyFree").attr("checked","checked");
					else
						$("#soyFree").removeAttr("checked");
					if(data.preferences.sesameFree == "1")
						$("#sesameFree").attr("checked","checked");
					else
						$("#sesameFree").removeAttr("checked");
					if(data.preferences.vegetarian == "1")
						$("#vegetarian").attr("checked","checked");
					else
						$("#vegetarian").removeAttr("checked");
					if(data.preferences.noNuts == "1")
						$("#noNuts").attr("checked","checked");
					else
						$("#noNuts").removeAttr("checked");
				}
				yummly.getRecipiesFromApi();
			}
		});
	}
	function setPreferences(){
		if((facebookId == null) || facebookId<1)
			return null;
		var dairyFree = ($("#dairyFree").is(':checked')?1:0);
		var glutenFree = ($("#glutenFree").is(':checked')?1:0);
		var soyFree = ($("#soyFree").is(':checked')?1:0);
		var sesameFree = ($("#sesameFree").is(':checked')?1:0);
		var vegetarian = ($("#vegetarian").is(':checked')?1:0);
		var noNuts = ($("#noNuts").is(':checked')?1:0);
		$.ajax({
			async:true,
			url: '/index/preferences',
			type:'POST',
			data:'format=json&id=' + facebookId + '&dairyFree=' + dairyFree + '&glutenFree=' + glutenFree + '&soyFree=' + soyFree + 
				'&sesameFree=' + sesameFree + '&vegetarian=' + vegetarian + '&noNuts=' + noNuts,
			success:function(data){
				
			}
		});
	}