<?php
	
function readThumbnails($sortMode) { // sort: 0 date ASC, 1 Date DESC, 2 ALPH ASC, 3 ALPH DESC
	$all = [];
	foreach(scandir("Recipes") as $dir) {
		if ($dir === "." or $dir === "..") { continue; }
		$fileName = "Recipes/$dir/recipe.txt";
		if (!file_exists($fileName))  { continue; }
		$r = [];
		$linecount = 0;
    $handleFile = fopen($fileName, "r");
		while(!feof($handleFile) And $linecount < 9){
      array_push($r, trim(substr(fgets($handleFile), 1)));
      $linecount++;
    }
		if (count($r) < 9) { continue; }
		$r[3] = "Recipes/$dir/".$r[3];
		array_push($r, $dir);
		array_push($all, $r);
	}
	
	switch ($sortMode) {
		case 0: // Date asc
			usort($all, "compareDatesASC");
			break;
		case 2:
			usort($all, "comparenameASC");
			break;
		case 3:
			usort($all, "comparenameDESC");
			break;
		default:
			usort($all, "compareDatesDESC");
	}
	
	foreach($all as $recipe) {
		echo '<a href="recipe.php?Recipe='.$recipe[9].'" style="text-decoration:none;" title="'.$recipe[7].'"><div class="RecipeBlock"><img class="RecipeImage" src="'.$recipe[3].'"><p class="RecipeName">'.$recipe[0].'</p><p class="RecipeTags">'.$recipe[2].'</p></div></a>';
	}
	
}

function compareDatesASC($a, $b) {
	if ($a[4] == $b[4]) { return 0; }
	return $a[4] < $b[4] ? -1 : 1;
}
function compareDatesDESC($a, $b) {
	if ($a[4] == $b[4]) { return 0; }
	return $a[4] > $b[4] ? -1 : 1;
}
function comparenameASC($a, $b) {
	if ($a[0] == $b[0]) { return 0; }
	return $a[0] < $b[0] ? -1 : 1;
}
function comparenameDESC($a, $b) {
	if ($a[0] == $b[0]) { return 0; }
	return $a[0] > $b[0] ? -1 : 1;
}

function getRecipe($dir) {
	$fileName = "Recipes/$dir/recipe.txt";
	if (!file_exists($fileName))  { return [$dir,$dir,$dir,$dir,$dir,$dir,$dir,$dir,$dir,$dir,$dir]; }
	$r = [];
	$handleFile = fopen($fileName, "r");
	while(!feof($handleFile)){
		$line = fgets($handleFile);
		$begin = substr($line, 0, 1);
		if ($begin==="-" Or $begin==="#") {
			array_push($r, trim(substr($line, 1)));
		} else if (strlen(trim($line))>0) {
			array_push($r, trim($line));
		}
	}
	$r[3] = str_replace(" ", "%20", 'Recipes/'.$dir.'/'.$r[3]);
	
	preg_match('/(\d+)(.*)/', $r[9], $matches);
	if (is_array($matches)) { 
		$r[9] = intval($matches[1]);
	}
	if ($r[9] == 0) $r[9] = 1;
	return $r;
}

function countRecipes() {
	$num = 0;
	foreach(scandir("Recipes") as $dir) {
		if ($dir === "." or $dir === "..") { continue; }
		$fileName = "Recipes/$dir/recipe.txt";
		if (!file_exists($fileName))  { continue; }
		$linecount = 0;
    $handleFile = fopen($fileName, "r");
		while(!feof($handleFile) And $linecount < 5){
      $linecount++;
    }
		if ($linecount < 5) { continue; }
		$num++;
	}
	return $num;
}
?>