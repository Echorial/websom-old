<?php
/**
* \defgroup Email Email
* 
* Websom comes with a simple email tool that modules and code should use.
* 
* Note: Emails sent in dev mode will be put into a local folder.
* 
*/

/**
* \ingroup Email
* 
* This class is used to construct and send emails.
* Example:
* \code
* $email = new Email("noreply@example.com", "person@example.com", "Example Subject", "Example Body"); //This will create a new email object but not send it.
* $email->headers("Reply-To", "noreply@example.com");
* if ($email->send()) echo "Sent"; // Send the email.
* \endcode
*/
class Email extends Hookable {
	//Static stuff
	
	static public $config;
	
	static public function init() {
self::$config = Config::Get("Email", 'enabled = "on" ;`on` or `off`
dev_folder = "Websom/Website/Emails/" ;The folder(path from Document_root) where emails sent will be while website is not `live`');
	
	}
	
	/**
	* Email address that the email is from.
	*/
	public $from;
	
	/**
	* The email subject.
	*/
	public $subject;
	
	/**
	* The body of the email.
	*/
	public $body;
	
	public $headers = [];
	
	//Object stuff
	/**
	* This will build the email and setup some headers.
	* 
	* @param string $from The email address to send from.
	* @param string $subject The subject text.
	* @param string $body The body text of the email.
	*/
	public function __construct($from, $subject, $body) {
		$this->headers = [];
		$this->from = $from;
		$this->subject = $subject;
		$this->body = $body;
	}
	
	/**
	* This will add a header to the email.
	* 
	* @param string $key The header name.
	* @param mixed $value The header value.
	* 
	* @return void
	*/
	public function addHeader($key, $value) {
		$this->headers[$key] = $value;
	}
	
	/**
	* This will send this email to every email in the $emails param.
	* 
	* Hooks: "send" params($emailObject)
	* 
	* @param array $emails The addresses to send this email to.
	* 
	* 
	* 
	* @return An array of addresses with true if sent or false if failure.
	*/
	public function send($emails) {
		if ($this->event("send", [$this])) //Let code cancel the send.
			return;
		
		$headers = "From: ".$this->from;
		foreach ($this->headers as $key => $value) {
			$headers .= "\r\n".$key.": ".$value;
		}
		
		if (Websom::$Live) {
			$success = [];
			foreach ($emails as $address) {
				$success[$address] = mail($address, $this->subject, $this->body, $headers);
			}
			return $success;
		}
		
$file = "Sent to: ".implode(",
", $emails)."

Headers: ".$headers."
----------------------
       Subject

".$this->subject."

----------------------
        Body

".$this->body;
	
		if (!file_exists(Document_root."/".self::$config["dev_folder"]))
			mkdir(Document_root."/".self::$config["dev_folder"]);
		
		file_put_contents(rtrim(Document_root."/".self::$config["dev_folder"], "/\\")."/".date("Y m d H i s")." ".$this->subject.".txt", $file);
		
		return true;
	}
}

?>