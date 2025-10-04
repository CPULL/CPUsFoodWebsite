<?php // Generation of recipe
 include "Code/recipes.php";
 $recipe = getRecipe($_GET['Recipe']);
?>
<!DOCTYPE html>
<html>
<head>
  <title>CPU's Food - <?= $recipe[0] ?></title>
	<link rel="stylesheet" href="style.css">
	<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
	<link rel="icon" type="image/x-icon" href="/Imgs/favicon.ico">
</head>
<body>

<div class="RecipeTitle" style="background-image: url(<?= $recipe[3] ?>);">
	<h1>CPU's food - <?= $recipe[1] ?></h1>
	<h2><?= $recipe[2] ?> - <?= $recipe[4] ?></h2>
	<button onclick="window.history.go(-1); return false;" class="Back" title="Go back to all recipes">⇦ <span class="BackText">Back</span></button>
</div>

<div class="SearchBarTop"></div>

<div class="RecipeDescription"><?= $recipe[7] ?></div><br>
<?php
$amount="1";
if (array_key_exists('Amount', $_GET)) { $amount=$_GET['Amount']; }
if (!$amount) { $amount = "1"; }
switch ($amount) {
	case "1/3": $mult = 0.333; break;
	case "1/2": $mult = 0.5; break;
	case "2/3": $mult = 0.666; break;
	case "1"  : $mult = 1; break;
	case "4/3": $mult = 1.333; break;
	case "2"  : $mult = 2; break;
	case "3"  : $mult = 3; break;
}

$locale="metric";
if (array_key_exists('Locale', $_GET)) { $locale=$_GET['Locale']; }
if (!$locale) { $locale = "metric"; }
switch ($locale) { 
	case "metric": $locale = "metric"; break; // grams, liters, but also tablespoons and "a bit of"
	case "imperial": $locale = "imperial"; break;
}

?>

<?php
if ($mult!=1) {
	echo "Original recipe";
} else {
	echo "Recipe";
}
?>
 will yield: <b><?= $recipe[8] ?></b> <span class="Em5Space">&nbsp;</span> <select id="Amount">
<option <?php echo $amount=="1/3" ? "selected='selected'" : "" ?> value="1/3">1/3</option>
<option <?php echo $amount=="1/2" ? "selected='selected'" : "" ?> value="1/2">1/2</option>
<option <?php echo $amount=="2/3" ? "selected='selected'" : "" ?> value="2/3">2/3</option>
<option <?php echo $amount=="1"   ? "selected='selected'" : "" ?> value="1">Exactly this amount</option>
<option <?php echo $amount=="4/3" ? "selected='selected'" : "" ?> value="4/3">4/3</option>
<option <?php echo $amount=="2"   ? "selected='selected'" : "" ?> value="2">double</option>
<option <?php echo $amount=="3"   ? "selected='selected'" : "" ?> value="3">triple</option>
</select>
<?php
if ($mult!=1) {
	preg_match('/(\d+)(.*)/', $recipe[8], $matches);
	if (is_array($matches)) { 
		$num = ceil(intval($matches[1]) * $mult);
		$unit = $matches[2];
		echo '<span class="Em5Space">&nbsp;</span>New yield: <b>'.$num.$unit.'</b>';
	}
}
?>

<span class="Em5Space">&nbsp;</span> <select id="Locale">
<option <?php echo $locale=="imperial"      ? "selected='selected'" : "" ?> value="imperial"     >Imperial      </option>
<option <?php echo $locale=="metric"        ? "selected='selected'" : "" ?> value="metric"       >Metric        </option>
</select>
<br>
<br>
<hr>
<?php // Parse all lines and get the list of all ingredients
// [amount unit_ prep _ingredient]
// {amount unit}
// <ingredient>
$ingrs = [];
$sortedingr = [];
for($i=9; $i<count($recipe); $i++) {
	$line = $recipe[$i];
	if (substr($line,0,1)==="*") { // Title ["", "", title, null]
		$ingrs[$line] = ["", "", $line, null];
		$sortedingr[count($sortedingr)] = $line;
		continue;
	}
	if (substr($line,0,1)==="=") { // Title not for ingredients
		continue;
	}
	$d = $i+1;
	preg_match_all('/(\[[\d\.]+)([^_]*)_([^\[<]+)_([^\]]+])/', $line, $matches); // [amount unit_ prep _ingredient] ingr = [amount, unit, prep, ingredient]
	if (is_array($matches)) {
		$amounts = $matches[1];
		$units = $matches[2];
		$preps = $matches[3];
		$types = $matches[4];
		for($j=0; $j<count($matches[0]); $j++) {
			$key = $matches[0][$j];
			$ingrs[$key] = [cleanTags($amounts[$j]), cleanTags($units[$j]), $preps[$j], cleanTags($types[$j])];
			$sortedingr[count($sortedingr)] = $key;
		}
	}
	preg_match_all('/(\{[\d\.]+)([^\}]*\})/', $line, $matches); // {amount unit} ingr = [amount, unit, null, null]
	if (is_array($matches)) {
		$amounts = $matches[1];
		$units = $matches[2];
		for($j=0; $j<count($matches[0]); $j++) {
			$key = $matches[0][$j];
			$ingrs[$key] = [cleanTags($amounts[$j]), cleanTags($units[$j]), null, null];
			$sortedingr[count($sortedingr)] = $key;
//FIXME			echo "<li><b>DBG amount: </b>$d - ".$ingrs[$key][0].$ingrs[$key][1];
		}
	}
	preg_match_all('/(<[^>]+>)/', $line, $matches); // <ingredient> ingr = [null, null, null, ingredient]
	if (is_array($matches)) {
		$types = $matches[1];
		for($j=0; $j<count($matches[0]); $j++) {
			$key = $matches[0][$j];
			$ingrs[$key] = [null, null, null, cleanTags($types[$j])];
			$sortedingr[count($sortedingr)] = $key;
		}
	}
}

function cleanTags($str) {
	return str_replace(["<",">","[","]","{","}","_"], "", trim($str));
}

function replaceIngredients($line, $ingrs, $mult, $locale) {
	foreach(array_keys($ingrs) as $key) {
		if (str_contains($line, $key)) {
//			echo "<li><b>XXXXX:</b>".htmlentities($line);
			$ingr = calculateLocalAmount($ingrs[$key], $locale, $mult);
			$num = $ingr[0];
			$type = $ingr[3];
			$replacement = "$num ".$ingr[1].$ingr[2].'<b><a href="Ingredients/'.$type.'.html">'.$type."</a></b>";
			$line = str_replace($key, $replacement, $line);
		}
	}
	preg_match_all('/(<[^<\"\/]+>)/', $line, $matches); // Replace the ingredients with links
	if (is_array($matches)) { 
		foreach($matches[0] as $match) {
			$repl = substr($match, 1, -1);
			$repl = "<b><a href='Ingredients/".$repl.".html'>".$repl."</a></b>";
			str_replace($match, $repl, $line);
//FIXME			echo "<li><b>DBG:</b>".htmlentities($line);
		}
	}
	
	return $line;
}

function calculateLocalAmount($ingr, $locale, $mult) {
	$num = $ingr[0] * $mult;
	$unit = $ingr[1];
	
switch ($locale) { 
	case "imperial": 
		switch ($unit) {
			case "g": 
				$unit = "lbs";
				$num = $num * 0.0022;
				if ($num < .25) { $num = $num * 16; $unit = "oz"; }
				$num = ceil($num * 20) / 20.0;
				break;
				
			case "l": 
			case "liter": 
			case "liters": 
				$num = $num * 1000.0;
			case "ml":
				$unit = "gal";
				$num = $num * 0.000264172;
				if ($num < .25) { $num = $num * 16; $unit = "cup"; }
				$num = ceil($num * 20) / 20.0;
				break;
				
			case "°C":
				$num = $num * 9/5 + 32;
				$unit = "°F";
				break;
				
			case "mm":
				$num = $num / 10;
			case "cm":
				$num = $num * 0.393701;
				$unit = "in";
		
			case "": // Pure numbers
				break;
		
			default:
				echo "<b style='color:red'>|$unit|$num|</b>";
		}
		if ($unit=="teaspoon" And $num != 1) $unit="teaspoons";
		if ($unit=="teaspoons" And $num == 1) $unit="teaspoon";
		if ($unit=="tablespoon" And $num != 1) $unit="tablespoons";
		if ($unit=="tablespoons" And $num == 1) $unit="tablespoon";
		break;
		
	case "metric": $locale = "metric"; break; // grams, liters, but also tablespoons and "a bit of"
}
	if ($unit=="*") { // Used only to force integer numbers
		$num=ceil($num);
		$unit="";
	}

	$num = ceil($num * 20) / 20;
	if ($num - intval($num) <= .05) { 
		if ($num > 0) {
			$num = intval($num); 
			if ($num==0) { $unit="a pinch";  }
		}
		if ($num==0) { $num=""; }
	}
	else if ($num - intval($num) >= .9) {
		$num = intval($num) + 1;
	}
	
	if ($num > 10) { $num = ceil($num); }

	switch($num) {
		case 0.25: $num = "¼"; break;
		case 0.3:
		case 0.33:
		case 0.333:
			$num = "⅓"; break;
		case 0.5: $num = "half"; break;
		case 0.6:
		case 0.66:
		case 0.666:
			$num = "⅔"; break;
		case .75: $num = "¾"; break;
	}
	if ($num==0) { $num=""; }
	$ingr[0] = $num ;
	$ingr[1] = $unit;
	
	
	return $ingr;
}


?>

<h1>Ingredients</h1>
<ul>
<?php

for($i=0; $i<count($sortedingr); $i++) {
	$ingr = $ingrs[$sortedingr[$i]];
	
// echo "<li>$i) ".(($ingr[0])==null?".":"%").$ingr[0]."|".(($ingr[1])==null?".":"%").$ingr[1]."|".(($ingr[2])==null?".":"%").$ingr[2]."|".(($ingr[3])==null?".":"%").$ingr[3]."|".$sortedingr[$i];
	
	if (($ingr[0])==null And ($ingr[1])==null And ($ingr[3])==null) {
		echo "<li style='display:block'><br><b>".trim(substr($ingr[2],1))."</b>";
		continue;
	}
	if ($ingr[2]==null Or $ingr[3]==null) { // Skip measures and ingredients
		continue;
	}
	
	$ingr = calculateLocalAmount($ingr, $locale, $mult);
	$num = $ingr[0];
	$quantity = $num;
//FIXME	$quantity = ceil(intval($num * 100.0) * $mult) / 100.0;
	echo "<li>".$quantity." ".$ingr[1].$ingr[2].$ingr[3];
}
?>
</ul>

<h1>Preparation</h1>
<ul>
<?php
for($i=9; $i<count($recipe); $i++) {
	$line = $recipe[$i];
	$begin = substr($line, 0, 1);
	if ($begin == "*" Or $begin == "=") { // Title
		echo "<li style='display:block;'> <h3>".trim(substr($line, 1))."</h3>";
	} else if ($begin == "?") { // Image
	// FIXME
	} else {
		echo "<li> <input type='checkbox'>".replaceIngredients($recipe[$i], $ingrs, $mult, $locale);
	}
}

?>
</ul>


<hr>

<h1>Video</h1>
<iframe width="546" height="970" src="<?= $recipe[5] ?>" title="<?= $recipe[1] ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<br><?= $recipe[6] ?>
<hr>


<script>
$("#Amount").on("change", function(evt) { 
	if ($("#Amount").val() == "<?= $amount ?>") {
		return; // same value...
	}
	var url = window.location.href;
	let regex = /(.+)Amount=([\d/]+)(.*)/i;
	var result = regex.exec(url);
	if (!result) url += "&Amount=" + $("#Amount").val();
	else {
		url = result[1] + "Amount=" + $("#Amount").val() + result[3];
	}
	window.location.replace(url);
}).trigger( "change" );

$("#Locale").on("change", function(evt) { 
	if ($("#Locale").val() == "<?= $locale ?>") {
		return; // same value...
	}
	var url = window.location.href;
	let regex = /(.+)Locale=([\d/]+)(.*)/i;
	var result = regex.exec(url);
	if (!result) url += "&Locale=" + $("#Locale").val();
	else {
		url = result[1] + "Locale=" + $("#Locale").val() + result[3];
	}
	window.location.replace(url);
}).trigger( "change" );

</script>


</body>
</html>

