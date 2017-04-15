<?php
header('Content-Type: application/json; charset=utf-8');

require("dbconnect.php");

$input = explode("/", $_GET["_url"]);

if (sizeof($input) != 3) {
	die(json_encode(array('error' => 'invalid arguments')));
}

$book  = intval($input[1]);
$range = $input[2];

$rangePos = strpos($range, "-");
if ($rangePos != 0) {
    $verseStart = substr($range, 0, $rangePos);
    $verseEnd = substr($range, $rangePos + 1);
} else {
    $verseStart = $range;
    $verseEnd = null;
}

$result = explode(":", $verseStart);
if (sizeof($result) == 2) {
	$chapter1 = intval($result[0]);
	$verse1 = intval($result[1]);
} else {
	die(json_encode(array('error' => 'invalid arguments')));
}
$verseStart = $chapter1 * 1000 + $verse1;

if ($verseEnd == null) {
	$chapter2 = $chapter1;
	$verse2 = $verse1;
} else {
	$result = explode(":", $verseEnd);
	if (sizeof($result) == 2) {
	    $chapter2 = intval($result[0]);
    	$verse2 = intval($result[1]);
	} else if (sizeof($result) == 1) {
        $chapter2 = $chapter1;
        $verse2 = intval($result[0]);
    } else {
	    die(json_encode(array('error' => 'invalid arguments')));
	}
}
$verseEnd = $chapter2 * 1000 + $verse2;

$sql = sprintf("SELECT Chapter, Verse, Lection FROM BibleText WHERE Book='%d' AND Chapter*1000+Verse>='%d' AND Chapter*1000+Verse<='%d'", $book, $verseStart, $verseEnd);
$result = mysql_query($sql) or die('Query failed: ' . mysql_error());

$chapters = [];
$chapterCount = 0;
$chapter = 0;
$verses = [];
$book = [];

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($chapter != $row["Chapter"]) {
		if (sizeof($verses) > 0) {
			$chapters["id"] = $chapterCount++;
			$chapters["title"] = "";
			$chapters["verses"] = $verses;
	        array_push($book, $chapters);
			$verses = [];
		}

		$chapter = $row["Chapter"];
		$chapters = [];
	}
	$verse = [];
	$verse["verse"] = $chapter.":".$row["Verse"]; 
	$verse["text"] = $row["lection"];
	array_push($verses, $verse);
}
mysql_free_result($result);

if (sizeof($verses) > 0) {
	$chapters["id"] = $chapterCount++;
	$chapters["title"] = "";
    $chapters["verses"] = $verses;
    array_push($book, $chapters);
}

echo json_encode(array('paragraphs' => $book), JSON_UNESCAPED_UNICODE);
?>
