<?php // Recipes provider

if (!array_key_exists('First', $_GET) or !array_key_exists('Count', $_GET)) {
	echo '{"error":"First and Count are missing from the request!"}';
	exit("");
}
$first = $_GET['First'];
$count = $_GET['Count'];
$filters = "";
if (array_key_exists('Filters', $_GET)) {
	$filters = $_GET['Filters'];
}
/*
+ingr => the ingredient should be there
-ingr => the ingredient shoudl not be there
all other words can stay in name, long name, or description
"text" => the exact text should be there
*/
$negs=[];
$positives=[];
$words=[];

preg_match_all('/([\+\-]{0,1}"[^"]+")/', $filters, $wholeWords);
if ($wholeWords) {
	foreach($wholeWords[1] as $match) {
		$filters = str_replace($match, "", $filters);
	}
	while (str_contains($filters, "  ")) {
		$filters = str_replace("  ", " ", $filters);
	}
	$tmp = $wholeWords[1];
	$wholeWords = [];
	foreach($tmp as $w) {
		if ($w) {
			array_push($wholeWords, $w);
		}
	}
}
preg_match_all('/(-[^\s"]+)\s{0,1}/', $filters, $negs);
if ($negs) {
	foreach($negs[1] as $match) {
		$filters = str_replace($match, "", $filters);
	}
	while (str_contains($filters, "  ")) {
		$filters = str_replace("  ", " ", $filters);
	}
	$tmp = $negs[1];
	$negs = [];
	foreach($tmp as $w) {
		if ($w) {
			array_push($negs, substr($w, 1));
		}
	}
}
preg_match_all('/(\+[^\s"]+)\s{0,1}/', $filters, $positivs);
if ($positivs) {
	foreach($positivs[1] as $match) {
		$filters = str_replace($match, "", $filters);
	}
	while (str_contains($filters, "  ")) {
		$filters = str_replace("  ", " ", $filters);
	}
	$tmp = $positivs[1];
	$positivs = [];
	foreach($tmp as $w) {
		if ($w) {
			array_push($positivs, substr($w, 1));
		}
	}
}
while (str_contains($filters, "  ")) {
	$filters = str_replace("  ", " ", $filters);
}
$tmp = explode(" ", $filters);
$words = [];
foreach($tmp as $w) {
	if ($w) {
		array_push($words, $w);
	}
}

foreach($wholeWords as $word) {
	if ($word) {
		if (substr($word, 0, 1)=="-") {
			array_push($negs, substr($word, 2, -1));
		} else if (substr($word, 0, 1)=="+") {
			array_push($positivs, substr($word, 2, -1));
		} else {
			array_push($words, substr($word, 1, -1));
		}
	}
}

if ((count($negs) > 0) or (count($positivs) > 0) or (count($words) > 0)) {
	$withFilters = True;
} else {
	$withFilters = False;
}

header('Content-Type: application/json; charset=utf-8');

// Get all values
$all=[];

foreach(scandir("../Recipes") as $dir) {
	if ($dir === "." or $dir === "..") { continue; }
	$fileName = "../Recipes/$dir/recipe.txt";
	if (!file_exists($fileName))  { continue; }
	$r = [];
	$linecount = 0;
	$handleFile = fopen($fileName, "r");
	while(!feof($handleFile) And $linecount < 9){
#0 Short name
#1 Long name
#2 Tags
#3 Image
#4 Date
#5 YouTube
#6 Music
#7 Description
#8 Dir (calculated)
		array_push($r, trim(substr(fgets($handleFile), 1)));
		$linecount++;
	}
	if (count($r) < 9) { continue; }
	$r[3] = "Recipes/$dir/".$r[3]; // Fix the full path for the image
	array_push($r, $dir);
	array_push($all, $r);
}

$sortMode=1; // Most recent first by default
if (array_key_exists('SortMode', $_GET)) {
	$sortMode = intval($_GET['SortMode']);
}
# 0 Oldest
# 1 Most recent
# 2 Alphabetical
# 3 Z-A


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
$found = 0;
$avail = 0;
$total = count($all);
// total: total number of recipes ignoring the filters
// count: number of returned recipes ($found)
// avail: total number of recipes respecting the filters

$dbg="";

echo '{"total":'.$total.', "result":[';
for($i=0; $i<$total; $i++) { // FIXME we should check also the recipes from 0 or our count will be wrong, start giving only the ones from First
	$r=$all[$i];
	$dbg=$dbg." i=".$i;
	
	// Check the filters
	if ($withFilters) {
		// Do we have any of the requested words?
		$good = false;
		if (count($words) == 0) {
			$good = true; // If there is nothing as specific text filter we start with all good
		}
		foreach($words as $w) {
			if (stripos($r[0], $w) !== false) { // Name
				$good = true;
				break;
			}
			if (stripos($r[1], $w) !== false) { // Long name
				$good = true;
			}
			if (stripos($r[2], $w) !== false) { // Tags
				$good = true;
			}
			if (stripos($r[7], $w) !== false) { // Description
				$good = true;
				break;
			}
			if (stripos($r[8], $w) !== false) { // Ingredients
				$good = true;
				break;
			}
		}
		// Must have ingredients or tags
		foreach($positivs as $p) {
			$dbg=$dbg." ".$p."=".$r[2];
			if (stripos($r[2], $p) === false and stripos($r[8], $p) === false) {
				$good = false;
				break;
			}
		}
		// Must not have ingredients and tags
		foreach($negs as $n) {
			if (stripos($r[2], $n) !== false or stripos($r[8], $n) !== false) {
				$good = false;
				break;
			}
		}
		if (!$good) {
			continue;
		}
	}
	
	
	if($found > 0 && $found < $count) {
		echo ",\n";
	}
	$avail++;
	
	if($found < $count and $avail > $first) {
		$found++;

		echo '{';	
		echo '"name":"'.$r[0].'", ';
		echo '"longname":"'.$r[1].'", ';
		echo '"tags":"'.$r[2].'", ';
		echo '"image":"'.$r[3].'", ';
		echo '"date":"'.$r[4].'", ';
		echo '"youtube":"'.$r[5].'", ';
		echo '"music":"'.$r[6].'", ';
		echo '"description":"'.$r[7].'", ';
		echo '"ingredients":"'.$r[8].'", ';
		echo '"dir":"'.$r[9].'"';
		echo '}';
	}
	
}
echo '],"count":'.$found.',"avail":'.$avail.',"dbg":"'.$dbg.'"}';

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

?>
