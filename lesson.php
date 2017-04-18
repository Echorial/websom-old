<?php
include("Websom/Start.php");

if (!isset($_GET["c"]) OR !isset($_GET["l"])) {
	Go(Linker::get("Home"));
}else{
	$course = Schooler::getCourse(intval($_GET["c"]));
	
	if ($course === false) {
		echo Standards::error("Course not found.");
	}else{
		$lesson = Schooler::getLesson(intval($_GET["c"]), intval($_GET["l"]));
		if ($lesson === false) {
			echo Standards::error("Course not found.");
		}else{
			if (isset($_GET["e"])) {
				echo Schooler::lessonEditor($lesson);
			}else{
				echo Schooler::viewLesson($lesson);
			}
		}
	}
}

include("Websom/End.php");
?>