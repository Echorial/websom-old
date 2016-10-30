<?php
/**
* \defgroup Responive Responive
*/

$Responives = array();

/**
* \ingroup Responive
* This will register the responsive object passed on the current page.
*
* Information: 
*
* 	- Return: void
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Get_Responsive ($responsive) {
	global $Responives;
	array_push($Responives, $responsive);
}

/**
* \ingroup Responive
* This will return true or false depending upon if the provided responsive is included.
*
* Information: 
*
* 	- Return: boolean
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Responsive_Included($response) {
	global $Responives;
	foreach($Responives as $resp)
		if ($response == get_class($resp)) return true;
	return false;
}

/**
* \ingroup Responive
* This will register the passed responsive only if it has not been registered already.
* 
* Information: 
*
* 	- Return: void
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Responsive_Once($responsive) {
	if (!Responsive_Included($response))
		Get_Responsive($responsive);
}

/// \cond
function Websom_Check_Responsive () {
	global $Responives;
	if (count($_POST) == 0) return false;
	if (!isset($_POST['responiveid'])) return false;
	if ($_POST['responiveid'] > count($Responives) OR $_POST['responiveid'] < 0) return false;
	$__POST = $_POST;
	unset($__POST['responiveid']);
	$responseData = $Responives[$_POST['responiveid']-1]->response(json_decode(json_encode($__POST), true));
	$data = ['responsive_321_type' => false];
	if (is_array($responseData)) {
		$data = $responseData;
		$data['responsive_321_type'] = true;
	}
	cancel(JSON_encode($data));
}

function Get_Responsive_Scripts() {
	global $Responives;
	$scripts = '<script>var responsives = [';
	foreach ($Responives as $responsive) {
		$scripts .= 'function (respond, response) {'.$responsive->javascript().'},';
	}
	return rtrim($scripts, ',').'];</script>';
}
/// \endcond

/**
* \ingroup Responive
* \brief The Responive class is a template for creating quick client-server comunication
*
* Information: 
*
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
* 
* The point of Responive is to provide a quick way of allowing the client to contact the server and vise versa.<hr>
* A good example of this would to have a file explorer. Javascript will detect when a client drags a file into a folder or creates a new file.
* Then the javascript would send a message to the server saying move this `file` `here`.
* 
* Example:
* \code
* //Pseudo code
* class fileMessaging extends Responive {
*	function javascript () { //Override the javascript method
*		return "$('.file').onDropOn('.folder', function () {
*			respond({moveFile: true, fileId: $(this).attr('file-id'), where: folderId});
*		})";
*	}
*	function response($msg) {
*		if (isset($msg['moveFile'])) {
*			moveFile($msg['fileId'], $msg['where']);
*		}
*		return ['successMessage' => 'Success'];
*	}
* }
*
* \endcode
*
* To implement a custom responsive object into a page you need to call Get_Responsive() this function will add the object to a list of responsives that will be included on your page.
*
* <hr>
* 
* <div class="warning">Do not use this for constant server client IO</div>
*
*/
class Responsive {
	/**
	* The javscript that you return has some hidden functions you can call.
	*
	* Javascript Functions:
	*	- respond ( object message , [callback serverResponse(object serverMessage)] )
	*	- response ( callback serverResponse(object serverMessage) )
	* 
	* Information:
	* 	- Return: string(of javascript)
	*/
	function javascript() {
		
	}
	/**
	* 
	* This method is called when the client javascript sends a message to the server.
	* 
	* Return the message to send back to the client.
	* 
	* Information:
	* 	- Return: Associative array
	*/
	function response($clientMessage) {
		
	}
}
?>