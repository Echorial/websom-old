<?php
define("Console_Page", true);
define("Suppress_Modules", true);
include("Websom/Start.php");
Page("blank.html");

echo Get_Widget(new Console_Console);

include("Websom/End.php");
?>