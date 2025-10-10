<!DOCTYPE html>
<html>
<head>
  <title>CPU's Food</title>
	<link id="stylesheet" rel="stylesheet" href="style.css">
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<link rel="icon" type="image/x-icon" href="/Imgs/favicon.ico">
<meta property="og:image" content="https://cpusfood.nafoeverywhere.org/Imgs/CPU's%20food%20banner.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">	
<meta property="og:title" content="CPU's Food">	
<meta property="og:description" content="List of all recipes I published, with detailed ingredients and steps. And links to the videos">	
<meta property="og:url" content="cpusfood.nafoeverywhere.org">	
</head>
<body>

<?php // Code to check how many times this user connected
 include 'Code/database.php';
 $acessing = countAccess();
 $sortMode = $acessing[3];
 include 'Code/recipes.php';
 $numRecipes = countRecipes();
?>



<div class="Headertop">
	<h1>CPU's food</h1>
	<table class="HeadertopStats">
	<tr><td>Total number of visits: <?= $acessing[0] ?></td><td class="EmSpace">&nbsp;</td><td>Total number of recipes: <?= $numRecipes ?></td><td class="EmSpace">&nbsp;</td><td class="EmSpace">&nbsp;</td></tr>
	<tr><td>You visited: <?= $acessing[1] ?> times</td><td class="EmSpace">&nbsp;</td><td>Last access: <?= $acessing[2] ?></td><td class="EmSpace">&nbsp;</td><td class="EmSpace">Login</td></tr>
	</table>
</div>

<input id="SearchBar" name="SearchBar" type=text class="SearchBar" placeholder="Search the recipe you wish..." >
<span class="EmSpace">&nbsp;</span>
<button class="SearchButton" onclick="$('#PopupPrefs').css('display','block');"><img class="SearchButtonImage" src="Imgs/Prefs.png"></button>
<div>&nbsp;</div>

<p style="color:red;">Work in progress...</p>

<div class="PaginationButtons">
	<button onclick="goFirst()">⏮ First</button>
	<button onclick="goPrev()">⏴ Prev</button>
	<span id="PaginationTop">0/0</span>
	<button onclick="goNext()">⏵ Next</button>
	<button onclick="goLast()">⏭ Last</button>
</div>
<div id="RecipesContainer" class="RecipesContainer">
</div>
<div class="PaginationButtons">
	<button onclick="goFirst()">⏮ First</button>
	<button onclick="goPrev()">⏴ Prev</button>
	<span id="PaginationBottom">0/0</span>
	<button onclick="goNext()">⏵ Next</button>
	<button onclick="goLast()">⏭ Last</button>
</div>

<?php
echo "<br><br><hr><br><br>";
?>

<div class="popupBack" id="PopupCookies" style="display:none">
	<div class="Popup">
	Yes, we use cookies.<br><br>
	<button onclick="$('#PopupCookies').css('display','none');">OK!</button>
	</div>
</div>
<div class="popupBack" id="PopupPrefs" style="display:none">
	<div class="PopupPrefs">
	Preferences<br><br>
	<table class="PrefsTable">
	<tr><td>Theme:</td><td>
		<select id="Theme" style="font-size: 0.75em;"><button><selectedcontent></selectedcontent></button>
			<option value="0">Dark</option><option value="1">Light</option>
		</select>
	</td></tr>
	<tr><td>Font-size:</td><td>
		<select id="FontSize" style="font-size: 0.75em;"><button><selectedcontent></selectedcontent></button>
			<option value="0">-4</option>
			<option value="1">-3</option>
			<option value="2">-2</option>
			<option value="3">-1</option>
			<option value="4">Normal</option>
			<option value="5">+1</option>
			<option value="6">+2</option>
			<option value="7">+3</option>
			<option value="8">+4</option>
		</select>
	</td></tr>
	<tr><td>Sorting:</td><td>
		<select id="SortMode" style="font-size: 0.75em;"><button><selectedcontent></selectedcontent></button>
			<option value="0" <?php echo $sortMode==0 ? "selected='selected'" : "" ?> >Oldest first</option>
			<option value="1" <?php echo $sortMode==1 ? "selected='selected'" : "" ?> >Most recent first</option>
			<option value="2" <?php echo $sortMode==2 ? "selected='selected'" : "" ?> >Alphabetical</option>
			<option value="3" <?php echo $sortMode==3 ? "selected='selected'" : "" ?> >Z-A</option>
		</select>
	</td></tr>
	<tr><td>Measurement system:</td><td>
		<select id="Locale" style="font-size: 0.75em;"><button><selectedcontent></selectedcontent></button>
			<option value="0" <?php echo $sortMode==0 ? "selected='selected'" : "" ?> >Metric</option>
			<option value="1" <?php echo $sortMode==1 ? "selected='selected'" : "" ?> >Imperial</option>
		</select>
	</td></tr>
	<tr><td>Recipes per page:</td><td>
		<select id="RecipesPerPage" style="font-size: 0.75em;"><button><selectedcontent></selectedcontent></button>
			<option value="2">2</option>
			<option value="4">4</option>
			<option value="6">6</option>
			<option value="8">8</option>
			<option value="10">10</option>
			<option value="12">12</option>
			<option value="14">14</option>
			<option value="16">16</option>
			<option value="18">18</option>
			<option value="20">20</option>
			<option value="22">22</option>
			<option value="24">24</option>
		</select>
	</td></tr>
	<tr><td></td><td></td></tr>
	</table>
	<button onclick="$('#PopupPrefs').css('display','none');">OK!</button>
	</div>
</div>


<script>
let allReady = false;
let firstRecipe = 0;
let avail = 0;
let prefs = {
	sortMode: 1, // Newest first
	locale: 0, // Metric
	fontSize: 4, // 1em
	recipesPerPage: 12, // 12
	theme: 0, // Dark
}

startUp();

async function startUp() {
	await checkCookies();
	
	// Update all dropdown values to trigger the changes
	$("#SortMode").val(prefs.sortMode);
	$("#Locale").val(prefs.locale);
	$("#FontSize").val(prefs.fontSize);
	$("#RecipesPerPage").val(prefs.recipesPerPage);
	$("#Theme").val(prefs.theme);
	// Update the values for CSS
	// FontSize
	$("body").css("font-size", (prefs.fontSize / 10 + .6) + "em");
	//Theme 
	if (prefs.theme == 0) {
		$("#stylesheet").attr("href", "style.css");
	} else {
		$("#stylesheet").attr("href", "styleLight.css");
	}
	
	// Load the expected data
	loadRecipes();
	allReady = true;
}

function loadRecipes() {
	$.ajax({
		method: "GET",
		url: "Code/recipesProvider.php?First=" + firstRecipe + "&Count=" + prefs.recipesPerPage + "&SortMode=" + prefs.sortMode + "&Filters=" + encodeURIComponent($("#SearchBar").val()),
		complete: function(data) {
			var res;
			try {
				res = JSON.parse(data.responseText);
			} catch (err) {
				console.log(data);
				console.log(err);
				return;
			}
			$("#RecipesContainer").empty();
			if (res.count == 0) {
				$("#RecipesContainer").html("No recipes satisfy your filters!");
			}
			if (res.error) {
				$("#RecipesContainer").html(res.error);
			}
			
			for(var i=0; i<res.count; i++) {
				var r = res.result[i];
				$("#RecipesContainer").append(
					'<a href="recipe.php?Recipe=' + r.dir +
					'" style="text-decoration:none;" title="' + r.description +
					'"><div class="RecipeBlock"><img class="RecipeImage" src="' + r.image +
					'"><p class="RecipeName">' + r.name +
					'</p><p class="RecipeTags">' + r.tags +
					'</p></div></a>');
			}
			avail = res.avail;
			var pos = Math.ceil((firstRecipe + 1) / prefs.recipesPerPage) + " / " + Math.ceil(avail / prefs.recipesPerPage);
			$("#PaginationTop").html(pos);
			$("#PaginationBottom").html(pos);
			
		},
		error: function(err) {
			console.log(err);
		}
	});
}

$("#SortMode").on("change", function(evt) { 
	if (prefs.sortMode == $("#Locale").val()) {
		return; // same value... do not force the database update
	}
	prefs.sortMode = $("#SortMode").val();
	setCookie("sortMode", prefs.sortMode);
	if (allReady) loadRecipes();
}).trigger( "change" );

$("#Locale").on("change", function(evt) { 
	if (prefs.locale == $("#Locale").val()) {
		return; // same value...
	}
	prefs.locale = $("#Locale").val();
	setCookie("locale", prefs.locale);
}).trigger( "change" );

$("#FontSize").on("change", function(evt) { 
	if (prefs.fontSize == $("#FontSize").val()) {
		return; // same value...
	}
	prefs.fontSize = $("#FontSize").val();
	setCookie("fontSize", prefs.fontSize);
	
	$("body").css("font-size", (prefs.fontSize / 10 + .6) + "em");
	
}).trigger( "change" );

$("#RecipesPerPage").on("change", function(evt) { 
	if (prefs.recipesPerPage == $("#RecipesPerPage").val()) {
		return; // same value...
	}
	prefs.recipesPerPage = $("#RecipesPerPage").val();
	setCookie("recipesPerPage", prefs.recipesPerPage);
	if (allReady) loadRecipes();
}).trigger( "change" );

$("#Theme").on("change", function(evt) { 
	if (prefs.theme == $("#Theme").val()) {
		return; // same value... do not force the database update
	}
	prefs.theme = $("#Theme").val();
	setCookie("theme", prefs.theme);
	
	if (prefs.theme == 0) {
		$("#stylesheet").attr("href", "style.css");
	} else {
		$("#stylesheet").attr("href", "styleLight.css");
	}
}).trigger( "change" );

$("#SearchBar").on("keypress", (evt) => {
	if (evt.key === "Enter") {
		firstRecipe = 0;
		loadRecipes();
	}
});

async function setCookie(key, val) {
	await cookieStore.set(key, val);
	await cookieStore.set("expires", Date.now() + 30 * 24 * 60 * 60 * 1000); // 1 month
}
async function checkCookies() {
	const cookies = await cookieStore.getAll();
  if (cookies.length > 0) {
    cookies.forEach((cookie) => {
			prefs[cookie.name] = cookie.value;
		});
  } else {
    $('#PopupCookies').css('display','block');
  }

	try {
		Promise.all(
			Object.keys(prefs).map(async (prop) => {
				await cookieStore.set(prop, prefs[prop]);
			}),
		);
		
    await cookieStore.set("expires", Date.now() + 30 * 24 * 60 * 60 * 1000); // 1 month

  } catch (error) {
    console.log('Error setting cookies:', error);
  }
}


function goFirst() {
	firstRecipe = 0;
	loadRecipes();
}
function goPrev() {
	firstRecipe -= parseInt(prefs.recipesPerPage);
	if (firstRecipe < 0) { firstRecipe = 0; }
	loadRecipes();
}
function goNext() {
	firstRecipe += parseInt(prefs.recipesPerPage);
	if (firstRecipe >= avail) {
		firstRecipe = avail - parseInt(prefs.recipesPerPage);
		if (firstRecipe < 0) { firstRecipe = 0; }
	}
	loadRecipes();
}
function goLast() {
	firstRecipe = avail - parseInt(prefs.recipesPerPage);
	if (firstRecipe < 0) { firstRecipe = 0; }
	loadRecipes();
}


</script>

</body>
</html>
<?php
/*
Add ability to create an account (just email/password, that will not be stored in clear), used to comment and upvote/downvote
add number of likes to recipes
add comments to recipes
add ability to show multiple images of the recipes, probably on bottom
Use a better way to find all ingredients for a recipe
Use a DB to store the main info from the recipes, and use an admin page to update the DB

users: id, name, email, password, sortMode, font size, color scheme, locale, recipes per page, creationdate

add tooltip on filters
allow to filter also on tags

*/
?>