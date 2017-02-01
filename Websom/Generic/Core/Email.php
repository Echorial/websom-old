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
* $email = new Email(true, "Example Subject", "Example Body"); //This will create a new email object but not send it.
* if ($email->send(["person@example.com", "anotherperson@example.com"])) echo "Sent"; // Send the email to 2 emails, from the no reply email account.
* \endcode
*/
class Email extends Hookable {
	//Static stuff
	
	static public $config;
	
	static public function init() {
self::$config = Config::Get("Email", 'enabled = "on" ;`on` or `off`
dev_folder = "Websom/Website/Emails/" ;The folder(path from Document_root) where emails sent will be while website is not `live`
no_reply = "noreply@example.com" ;The account that should be used to send non reply emails. This is used by modules.');
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
	
	/**
	* Set this to a string to override the from header.
	*/
	public $customFrom = false;
	
	public $headers = [];
	
	//Object stuff
	/**
	* This will build the email and setup some headers.
	* 
	* @param string $from The email address to send from. Set this to true if you wish to send this email from the server no reply account.
	* @param string $subject The subject text.
	* @param string $body The body text of the email.
	*/
	public function __construct($from, $subject, $body) {
		$this->headers = [];
		if ($from === true) {
			$this->from = self::$config["no_reply"];
			$this->customFrom = Website_name." <".self::$config["no_reply"].">";
			$this->addHeader("Reply-To", Website_name." <".self::$config["no_reply"].">");
		}else{
			$this->from = $from;
		}
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
		
		$headers = "";
		if ($this->customFrom === false) {
			$headers = "From: ".$this->from;
		}else{
			$headers = "From: ".$this->customFrom;
		}
		
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