<!DOCTYPE html>
<html>
<head>
  <title>CPU's Food</title>
	<link rel="stylesheet" href="style.css">
	<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
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
	<tr><td>Total number of visits: <?= $acessing[0] ?></td><td class="EmSpace">&nbsp;</td><td>Total number of recipes: <?= $numRecipes ?></td></tr>
	<tr><td>You visited: <?= $acessing[1] ?> times</td><td class="EmSpace">&nbsp;</td><td>Last access: <?= $acessing[2] ?></td></tr>
	</table>
</div>

<input id=SearchBar" name=SearchBar" type=text class="SearchBar" placeholder="Search the recipe you wish..." >
<span class="EmSpace">&nbsp;</span>
<button class="SearchButton" onclick="search();"><img class="SearchButtonImage" src="Imgs/MagnifierGlass.png"></button>
<div class="SortMode"> Sort mode: <select id="SortMode" style="font-size: 1em;">
	<button>
		<selectedcontent></selectedcontent>
	</button>
<option value="0" <?php echo $sortMode==0 ? "selected='selected'" : "" ?> >Oldest</option>
<option value="1" <?php echo $sortMode==1 ? "selected='selected'" : "" ?> >Most recent</option>
<option value="2" <?php echo $sortMode==2 ? "selected='selected'" : "" ?> >Alphabetical</option>
<option value="3" <?php echo $sortMode==3 ? "selected='selected'" : "" ?> >Z-A</option></select></div>

<p style="color:red;">Work in progress...</p>

<div class="RecipesContainer">

<?php
	readThumbnails($sortMode);
?>

</div>

<?php
echo "<br><br><hr><br><br>";
?>



<script>
$("#SortMode").on("change", function(evt) { 
	if ($("#SortMode").val() == <?= $sortMode ?>) {
		return; // same value... do not force the database update
	}

	fetch('Code/updater.php?SortMode='+$("#SortMode").val()) 
      .then(response => response.text()) 
      .then(data => { 
        console.log(data);
				window.location.reload(true);
      }); 
	
}).trigger( "change" );
</script>

</body>
</html>
<?php
/*
Add some pagination for the recipes, or infinite scrolling (maybe on mobile)
Add a way to increase/decrease the font size
Add ability to create an account (just email/password, that will not be stored in clear), used to comment and upvote/downvote
add number of likes to recipes
add comments to recipes
add ability to show multiple images of the recipes, probably on bottom
Add filters in the search
Change the direct creation of the thumbnails with a service that will provide the data, and javascript will create the actual thumbnails
For the amounts, check how many servings we had, and show only valid options (1, 2, 3, etc.)
*/
?>