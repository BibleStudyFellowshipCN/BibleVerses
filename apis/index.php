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
$verseStart = $book * 1000000 + $chapter1 * 1000 + $verse1;

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
$verseEnd = $book * 1000000 + $chapter2 * 1000 + $verse2;

$sql = sprintf("SELECT Id, Lection FROM Verse WHERE Id>='%d' AND Id<='%d'",
	mysql_real_escape_string($book),
	mysql_real_escape_string($verseStart),
	mysql_real_escape_string($verseEnd));
$result = mysql_query($sql) or die('Query failed: ' . mysql_error());

$chapters = [];
$chapterId = 0;
$chapter = 0;
$verses = [];
$paragraphs = [];

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($chapter != $row["Chapter"]) {
		if (sizeof($verses) > 0) {
			$chapters["id"] = $chapterId++;
			$chapters["title"] = "";
			$chapters["verses"] = $verses;
	        array_push($paragraphs, $chapters);
			$verses = [];
		}
		$chapter = $row["Id"] / 1000 % 1000;
		$chapters = [];
	}
	$verseId = $row["Id"] % 1000;
	$verse["verse"] = $chapter.":".$verseId; 
	$verse["text"] = $row["Lection"];
	array_push($verses, $verse);
}
mysql_free_result($result);

if (sizeof($verses) > 0) {
	$chapters["id"] = $chapterId;
	$chapters["title"] = "";
    $chapters["verses"] = $verses;
    array_push($paragraphs, $chapters);
}

echo json_encode(array('paragraphs' => $paragraphs), JSON_UNESCAPED_UNICODE);
?>