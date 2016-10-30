<?php
/**
* \ingroup TemplateClasses
*
* Information: 
*
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
* \brief This is the template class for all 'Widgets'.
*
*
* Widgets are used everywhere in websom. Typically these are used in modules, for allowing other programmers to easily call upon your widget and receive some sort of html structured
* item. A good example of a widget could be, if you have a Module that manages Forums, and you want to implement this Forum tool into a page on your website. To do so you would create
* a new Forum_Viewer widget and call Get_Widget($myForumViewer), then echo that onto your page.
*
* To create custom widgets you would simply extend the Widget class and override the needed methods.
*/
class Widget {
	public $Is_Widget = true;
	public $owner = "None";
	public $name = "Untitled_Widget";
	/**
	* \brief This method is called when the widget is used.
	* Information:
	* - Return: string
	*
	*/
	function get(){
		return "<label>Blank Widget</label>";
	}
}
?>