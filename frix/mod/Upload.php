<?
class UploadException extends Exception {};

class Upload {
	
	public $filename;
	public $upload;
	
	static $errors = array(
		UPLOAD_ERR_OK => 'Upload successful.',
		UPLOAD_ERR_INI_SIZE => 'File-size exceeds server limit.',
		UPLOAD_ERR_FORM_SIZE => 'File-size exceeds limit.',
		UPLOAD_ERR_PARTIAL => 'File partially uploaded.',
		UPLOAD_ERR_NO_FILE => 'No file uploaded.',
		UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder missing.',
		UPLOAD_ERR_CANT_WRITE => 'Couldn\'t write uploaded file.',
		UPLOAD_ERR_EXTENSION => 'File extension not permitted.'
	);
	
	function __construct ($upload) {
		
		$this->upload = $upload;
		$this->filename = '';
		
		if ($this->upload['error']) {
			
			throw new UploadException(
				// Error message
				sprintf('Upload error for file: "%s". %s',
					$this->upload['name'], 
					self::$errors[$this->upload['error']]
				),
				// Error code
				$this->upload['error']
			);
			
		}
		
		$this->filename = basename($this->upload['name']);
		
	}
	
	function save ($path) {
		
		// if (!move_uploaded_file($this->upload['tmp_name'], $path)) {
			// throw new UploadException(sprintf('Couldn\'t move file: "%s" to "%s".', $this->upload['name'], $path));
		// }
		
		if (is_uploaded_file($this->upload['tmp_name'])) {
			if (!copy($this->upload['tmp_name'], $path)) {
				throw new UploadException(sprintf('Couldn\'t move file: "%s" to "%s".', $this->upload['name'], $path));
			}
		}
		
		return true;
		
	}
	
}
?>