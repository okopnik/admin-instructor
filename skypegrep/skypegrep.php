<?php
    header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="utf-8" lang="utf-8">
<head>
<title>IRT FAQ</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="en-us" />
<style type="text/css" media="screen">@import "main.css";</style>
<link rel="stylesheet" type="text/css" media="print" href="print.css" />
<link rel="Shortcut Icon" href="/favicon.ico" type="image/x-icon" />
<meta name="description" content="DESCRIBE" />
</head>
<body>

<style type='text/css'>
#content { max-width: 80%; font-size: 14px; }
.wrap { word-wrap:break-word; background-color: lightblue; border: 1px solid gray; width: 200px; height: 135px; font-size: 12px; padding: 14px; margins: 12px; }
a:link,:visited,:active { text-decoration: none; color: black; }
a:hover { text-decoration: none; color: gray; }
.back { background-color: green; font-weight: bold; text-align: center; }
.back a { color: white; }
.skype { line-height: 1.5em; }
.mark { background-color: lightgray; border: 2px outset black; padding: 1px 4px; }
.mark img { position: relative; top: 5px; }
.light { color: gray; font-weight: bold; line-height: 1.5em; }
.alert { color: red; font-weight: bold; }
.hl { font-weight: bold; color: red; background-color: #eee; }
</style>

<div id="content">

<form method='get'>
Enter your search text: <input type='text' id='mySearch' name='search'<?php if (!empty($_GET['search'])) echo "value='".$_GET['search']."'"; ?> autofocus>
<input type='submit' name='submit' value='Search'>
</form>
<script>
  if (!("autofocus" in document.createElement("input"))) {
    document.getElementById("mySearch").focus();
  }
</script>
<hr>

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('UTC');

$here = $_SERVER['PHP_SELF'];

// Load the auth info
require_once('.htinfo');

//if (empty($db['hostname'])){
//    echo "<pre class='alert'>No '.htinfo' auth file found; quitting.</pre>";
//    exit;
//}

$dbc = mysqli_connect($db['host'], $db['user'], $db['password'], 'db') or die("Error ".mysqli_error($dbc));
mysqli_query($dbc, "SET NAMES 'utf8'");
mysqli_query($dbc, "SET CHARACTER SET utf8");

if (empty($_GET['limit'])){ $limit = 30; } else { $limit = $_GET['limit']; }

$do_tables = 0;
# Check for/create tables as necessary
if (mysqli_num_rows(mysqli_query($dbc, "SHOW TABLES LIKE 'Messages'")) == 0){
    $do_tables++;
    echo "'Messages' table not found; creating... ";
    $res = mysqli_query($dbc, "CREATE TABLE Messages(id INT NOT NULL AUTO_INCREMENT,PRIMARY KEY(id),tag VARCHAR(64),name VARCHAR(64),timestamp INT(11),text VARCHAR(2048))");
    if (!$res){ printf("Error creating 'Messages': <b>%s</b><br>", mysqli_error($dbc)); } else { echo "Success.<br>"; }
}
if (mysqli_num_rows(mysqli_query($dbc, "SHOW TABLES LIKE 'Linked'")) == 0){
    $do_tables++;
    echo "'Linked' table not found; creating...<br>";
    $res = mysqli_query($dbc, "CREATE TABLE Linked(id INT NOT NULL AUTO_INCREMENT,PRIMARY KEY(id),query VARCHAR(256),type CHAR(1),answer INT)");
    if (!$res){ printf("Error creating 'Linked': <b>%s</b><br>", mysqli_error($dbc)); } else { echo "Success.<br>"; }
}

if ($do_tables > 0){
    echo "<p class='back'><a href='$here'>Return to search</a></p>";
    exit;
}

if (!empty($_GET['id'])){
    $srch = mysqli_real_escape_string($dbc, $_GET['search']);
    $id = mysqli_real_escape_string($dbc, $_GET['id']);
    $type = $_GET['type'];
    $q = "INSERT INTO Linked VALUES(NULL, '$srch', '$type', $id)";
    if (!mysqli_query($dbc, $q)) printf("Error: %s\n", mysqli_error($dbc));
}

if (!empty($_GET['search']) AND empty($_GET['ts'])){
    $s = mysqli_escape_string($dbc, $_GET['search']);
    $query = "SELECT * FROM Messages WHERE text LIKE '%$s%'";
    $result = mysqli_query($dbc, $query) or die("Error".mysqli_error($dbc));

    $count = mysqli_num_rows($result);
    if($count == 0){
       echo "No results found";
    }
    else{
        $ess = $count == 1 ? "" : "s";
        $big = $count > 100 ? "; try a more specific query":"";
        echo "<p><strong>$count result$ess found$big.</strong></p>";
        $c = 0;
        echo '<table border=0 cellspacing=10><tr>';
        while($row = mysqli_fetch_array($result)) {
            if (!empty($row['text'])){
                $c++;
                $text = html_entity_decode($row['text']);
                $name = html_entity_decode($row['name']);
                if (strlen($text) > 200) $text = substr($text, 0, 195)."[...]";
                if (strlen($text) < 200) $text = str_pad($text, 200);
                $name = str_replace(' ', '&nbsp;', $name);
                $line = "<span class='light'>".date('m/d/y',$row['timestamp'])." $name</span><br>".strip_tags($text);
                $lnk = "$here?ts=".$row['timestamp']."&search=$s";
                echo "<td><div class='wrap'><a href='$lnk'>$line</a></div></td>\n";
                if ($c % 6 == 0) echo "</tr><tr>\n";
            }
        }
        echo '</tr></table>';
    }
}
else if (!empty($_GET['ts'])){
    $search = $_GET['search'];
    $ts = $_GET['ts'];
    $s = htmlspecialchars($_GET['search']);
    $query = "SELECT * FROM Messages where timestamp >= $ts LIMIT $limit";
    $result = mysqli_query($dbc, $query) or die("Error".mysqli_error($dbc));

    echo '<table border=0>';
    while($row = mysqli_fetch_array($result)) {
        if (!empty($row['text'])){
            $text = html_entity_decode($row['text']);
            $id = $row['id'];
            $text = preg_replace("/($s)/", "<span class='hl'>$1</span>", $text);
            $ctx_q = "<a href='$here?search=$s&ts=$ts&limit=$limit&id=$id&type=q'><strong>Q</strong>&nbsp;<img src='thumb.png'></a>";
            $ctx_a = "<a href='$here?search=$s&ts=$ts&limit=$limit&id=$id&type=a'><strong>A</strong>&nbsp;<img src='thumb.png'></a>";
            $line = '['.date('m/d/y h:i',$row['timestamp'])."] <font color='green'><b>[".$row['name']."]</b></font> $text";
            echo "<tr><td width='100'><span class='mark'>$ctx_q</span>&nbsp;<span class='mark'>$ctx_a</span></td><td><span class='skype'>$line</span></td></tr>";
        }
    }
    echo '</table>';
    $limit += 20;
    echo "<p class='back'><a href='$here?ts=$ts&search=$s&limit=$limit'>Extend by 20 posts</a></p>";
    echo "<p class='back'><a href='$here'>Return to search</a></p>";
}

?>
</div>
</body>
</html>
