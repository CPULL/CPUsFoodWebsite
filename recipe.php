<?php // Generation of recipe
 include "Code/recipes.php";
 include "Code/database.php";
 $recipe = getRecipe($_GET['Recipe']);
?>
<!DOCTYPE html>
<html>
<head>
  <title>CPU's Food - <?= $recipe[0] ?></title>
	<link rel="stylesheet" href="style.css">
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<link rel="icon" type="image/x-icon" href="/Imgs/favicon.ico">
</head>
<body>

<div class="RecipeTitle" style="background-image: url(<?= $recipe[3] ?>);">
	<h1>CPU's food - <?= $recipe[1] ?></h1>
	<h2><?= $recipe[2] ?> - <?= $recipe[4] ?></h2>
	<a href=".."><button class="Back" title="Go back to all recipes">⇦ <span class="BackText">Back</span></button></a>
</div>

<div class="SearchBarTop"></div>

<div class="RecipeDescription"><?= $recipe[7] ?></div><br>
<?php
$amount=$recipe[9];
if (array_key_exists('Amount', $_GET)) { $amount=$_GET['Amount']; }
if (!$amount) { $amount = $recipe[9]; }
$mult = 1;
switch ($amount) {
	case "1": $mult = 1/$recipe[9]; break;
	case "2": $mult = 2/$recipe[9]; break;
	case "3": $mult = 3/$recipe[9]; break;
	case "4": $mult = 4/$recipe[9]; break;
	case "5": $mult = 5/$recipe[9]; break;
	case "6": $mult = 6/$recipe[9]; break;
	case "8": $mult = 8/$recipe[9]; break;
	case "10": $mult =10/$recipe[9]; break;
	case "12": $mult =12/$recipe[9]; break;
	default: $mult=1; break;
}

$locale=readLocaleFromDB(); 
?>

<?php
if ($mult!=1) {
	echo "Original recipe";
} else {
	echo "Recipe";
}
?>
 will yield: <b><?= $recipe[9] ?></b> <span class="Em5Space">&nbsp;</span> <select id="Amount">
<option <?php echo $amount==$recipe[9] ? "selected='selected'" : "" ?> value="<?= $recipe[9] ?>">Original (<?= $recipe[9] ?>)</option>
<option <?php echo $amount=="1"  ? "selected='selected'" : "" ?> value="1" >1</option>
<option <?php echo $amount=="2"  ? "selected='selected'" : "" ?> value="2" >2</option>
<option <?php echo $amount=="3"  ? "selected='selected'" : "" ?> value="3" >3</option>
<option <?php echo $amount=="4"  ? "selected='selected'" : "" ?> value="4" >4</option>
<option <?php echo $amount=="5"  ? "selected='selected'" : "" ?> value="5" >5</option>
<option <?php echo $amount=="6"  ? "selected='selected'" : "" ?> value="6" >6</option>
<option <?php echo $amount=="8"  ? "selected='selected'" : "" ?> value="8" >8</option>
<option <?php echo $amount=="10" ? "selected='selected'" : "" ?> value="10">10</option>
<option <?php echo $amount=="12" ? "selected='selected'" : "" ?> value="12">12</option>
</select>
<?php
if ($mult!=1) {
	$num = ceil(intval($recipe[9]) * $mult);
	$unit = $matches[2];
	echo '<span class="Em5Space">&nbsp;</span>New yield: <b>'.$num.$unit.'</b>';
}
?>

<?php
if (array_key_exists('Locale', $_GET)) { 
$locale=$_GET['Locale']; 
}
switch ($locale) { 
	case "1": 
	case "imperial": 
	case "Imperial": 
		$locale = 1;
		break;
	default: $locale = 0; break; // metric: grams, liters, but also tablespoons and "a bit of"
}
?>

<span class="Em5Space">&nbsp;</span> Measures: <select id="Locale">
<option <?php echo $locale=="0" ? "selected='selected'" : "" ?> value="0" >Metric   </option>
<option <?php echo $locale=="1" ? "selected='selected'" : "" ?> value="1" >Imperial </option>
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
for($i=10; $i<count($recipe); $i++) {
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
	case 1: // imperial
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
			case "teaspoon":
			case "teaspoons":
			case "tablespoon":
			case "tablespoons":
			case "pinch":
			case "bunch":
			case "bunches":
			case "bottle":
			case "bottles":
			case "glass":
			case "glasses":
			case "cup":
			case "cups":
				break;
		
			default:
				echo "<b style='color:red'>|$unit|$num|</b>";
		}
		// Plural/singular
		if ($unit=="teaspoon" And $num != 1) $unit="teaspoons";
		if ($unit=="teaspoons" And $num == 1) $unit="teaspoon";
		if ($unit=="tablespoon" And $num != 1) $unit="tablespoons";
		if ($unit=="tablespoons" And $num == 1) $unit="tablespoon";
		if ($unit=="pinch" And $num != 1) $unit="pinches";
		if ($unit=="pinches" And $num == 1) $unit="pinch";
		if ($unit=="bunch" And $num != 1) $unit="bunches";
		if ($unit=="bunches" And $num == 1) $unit="bunch";
		if ($unit=="glass" And $num != 1) $unit="glasses";
		if ($unit=="glasses" And $num == 1) $unit="glass";
		if ($unit=="bottle" And $num != 1) $unit="bottles";
		if ($unit=="bottles" And $num == 1) $unit="bottle";
		if ($unit=="cup" And $num != 1) $unit="cups";
		if ($unit=="cups" And $num == 1) $unit="cup";
		break;
	
	case 0: // metric, just round to grams and kg if the numbers are too small/big
		switch ($unit) {
			case "g":
				if ($num>1000) {
					$num = intval(ceil($num/100))/10;
					$unit="kg";
				}
				break;
			case "kg":
				if ($num<1) {
					$num = intval($num*1000); 
					$unit="g";
				}
				break;
		}
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
for($i=10; $i<count($recipe); $i++) {
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
	$.ajax({
    type: "GET",
    url: 'Code/updateLocaleOnDB.php?Locale=' + $("#Locale").val(),
    dataType: 'text',
    success: function (obj, textstatus) {
			console.log("OBJ", obj, textstatus);
		}
	}).then(function(value) {
		var url = window.location.href;
		let regex = /(.+)Locale=([^&]+)(.*)/i;
		var result = regex.exec(url);
		var loc = "Locale=" + ($("#Locale").val()==1 ? "Imperial" : "Metric");
		if (!result) url += "&" + loc;
		else url = result[1] + loc + result[3];
		window.location.replace(url);
	});
	
}).trigger( "change" );


$("#Locale").on("change", function(evt) { 
	if ($("#Locale").val() == "<?= $locale ?>") {
		return; // same value...
	}
}).trigger( "change" );

</script>


</body>
</html>

