<?php // Recipes provider

if (!array_key_exists('First', $_GET) or !array_key_exists('Count', $_GET)) {
	echo '{"error":"First and Count are missing from the request!"}';
	exit("First and Count are missing from the request!");
}
$first = $_GET['First'];
$count = $_GET['Count'];

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
$max = count($all);
echo '{"count":'.count($all).', "result":[';
for($i=$first; $i<$count and $i<$max; $i++) {
	if($i!=$first) {
		echo ",\n";
	}
	$r=$all[$i];

echo '{';	
echo '"name":"'.$r[0].'", ';
echo '"longname":"'.$r[1].'", ';
echo '"tags":"'.$r[2].'", ';
echo '"image":"'.$r[3].'", ';
echo '"date":"'.$r[4].'", ';
echo '"youtube":"'.$r[5].'", ';
echo '"music":"'.$r[6].'", ';
echo '"description":"'.$r[7].'", ';
echo '"ingredients":"'.$r[8].'"';
echo '"dir":"'.$r[9].'"';
echo '}';
	
}
echo ']}';	

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
