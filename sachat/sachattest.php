<?php

SAtest_LoadTemes();
global $dirArray, $indexCount;

if (empty($_POST['satesttheme']))
    $_POST['satesttheme'] = 'default';

echo'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head> 
	     <link rel="stylesheet" type="text/css" href="http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/ctest.css" />
        <title>SA Chat Test Page</title> 
        <script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=head&amp;theme=' . $_POST['satesttheme'] . '"></script>
    </head>
<body>
    <script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=body&amp;theme=' . $_POST['satesttheme'] . '"></script>
        
	<h2>This file is here to preview themes in SA Chat and show it is working</h2>
	<p><strong>Chances are if it works here then it\'s conflicting with another smf mod</strong></p>';

echo'<form action="http://' . $_SERVER['HTTP_HOST'] . '' . $_SERVER['PHP_SELF'] . '" method="post">
    <strong>Select theme:</strong>
	<select name="satesttheme">';
for ($index = 0; $index < $indexCount; $index++) {
    if (substr($dirArray[$index], 0, 1) != '.' && $dirArray[$index] != "index.php") { // don't list hidden files
        echo'  <option value="' . $dirArray[$index] . '"', $_POST['satesttheme'] == $dirArray[$index] ? 'selected="selected"' : '', '>' . $dirArray[$index] . '</option>';
    }
}
echo'</select>
   <input type="submit" value="Test theme" />
   </form>
   <h2>You can use these codes to show the SA Chat outside of SMF</h2>
   <p><strong>Head code</strong></p>
   <textarea name="sahead" cols="60" rows="3" readonly="readonly">http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=head&amp;theme=' . $_POST['satesttheme'] . '</textarea>
   <p><strong>Body code</strong></p>
   <textarea name="sabody" cols="60" rows="3" readonly="readonly">http://' . $_SERVER['HTTP_HOST'] . '' . dirname($_SERVER['PHP_SELF']) . '/index.php?action=body&amp;theme=' . $_POST['satesttheme'] . '</textarea>
  </body> 
</html>';

function SAtest_LoadTemes() {
    global $dirArray, $indexCount;
    $myDirectory = opendir('themes');
    while ($entryName = readdir($myDirectory)) {
        $dirArray[] = $entryName;
    }
    closedir($myDirectory);
    $indexCount = count($dirArray);
    sort($dirArray);
}

?>