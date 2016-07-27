<?php


// TODO: look for all references of error() and replace with error_code()
// TODO: look for all references of notice() and replace with notice_code()

class SyncApiResponse implements SyncApiHeaders
{
	// TODO: remove $session_timeout
	public $session_timeout = FALSE;
	// TODO: remove $focus
	public $focus = NULL;						// focus element
	public $errors = array();					// list of errors @deprecated
	public $error_code = 0;						// error code
	public $error_data = NULL;					// error data
	// TODO: remove $notices
	public $notices = array();					// list of notices @deprecated
	public $notice_codes = array();				// list of notice codes
	public $success = 0;						// assume no success
	// TODO: remove $form
	public $form = NULL;						// form id
	// TODO: remove $validation
	public $validation = array();				// validation information
	public $result = NULL;						// the response from wp_remote_post()
	public $response = NULL;					// the response from wp_remote body, decoded into object
	public $nosend = FALSE;						// used to block sending of response when running Controller on Source site

	public $data = array();

	// constructor
	public function __construct()
	{
		if (!is_user_logged_in())
			$this->session_timeout = 1;
	}

	// return TRUE if instance is tracking any errors
	public function has_errors()
	{
		if (count($this->errors) || count($this->validation) || 0 !== $this->error_code)
			return TRUE;
		return FALSE;
	}

	// clears previous value in session timeout flag
	public function clear_timeout()
	{
		$this->session_timeout = 0;
	}

	// set a data property to be returned under the 'data.' element
	public function set($sName, $sValue)
	{
if ('errorcode' === $sName) SyncDebug::log(__METHOD__.'() called with data value "errorcode"', TRUE);
if ('error' === $sName) SyncDebug::log(__METHOD__.'() called with data value "error"', TRUE);
		$this->data[$sName] = $sValue;
	}

	/**
	 * Copy the data from another instance into current instance. Used when copying the results of an API call into response of an AJAX call.
	 * @param SyncApiResponse $response The instance's data to be copied
	 */
	public function copy(SyncApiResponse $response)
	{
		$this->error_code = $response->error_code;
		$this->notice_codes = $response->notice_codes;
		$this->notices = $response->notices;
		foreach ($response->data as $key => $data) {
			$this->data[$key] = $data;
		}
	}

	// set the form name
	public function form($sFormId)
	{
		$this->form = $sFormId;
	}

	// sets the form id to have focus
	public function focus($sElementId)
	{
		$this->focus = $sElementId;
	}

	// sets the success flag
	public function success($value)
	{
		$this->success = ($value ? 1 : 0);
	}

	// return TRUE if success value is set on
	public function is_success()
	{
		if (1 === $this->success)
			return TRUE;
		return FALSE;
	}

	/**
	 * Adds an error message to the 'errors.' element
	 * @deprecated Use `error_code()` instead
	 */
	public function error($sMsg)
	{
		$this->errors[] = $sMsg;
	}

	/**
	 * Sets error code for response object and sets success value to FALSE
	 * @param int $code The error code, one of `SyncApiRequest::ERROR_*` values
	 * @param mixed $data Optional data value to return with additional information about the error
	 * @return boolean Always FALSE so this can be used in a return;
	 */
	public function error_code($code, $data = NULL)
	{
		// only allow one error code
		if (0 === $this->error_code) {
			$this->error_code = intval($code);
			if (NULL !== $data)
				$this->error_data = $data;
			$this->success(FALSE);
		}
		return FALSE;
	}

	/**
	 * Returns error code stored in response instance
	 * @return int The error code
	 */
	public function get_error_code()
	{
		return $this->error_code;
	}

	/**
	 * Adds an notification message to the 'notices.' element
	 * @param string $sMsg The notice string to return to the user
	 * @deprecated Use `notice_code()` instead
	 */
//	public function notice($sMsg)
//	{
//		$this->notices[] = $sMsg;
//	}

	/**
	 * Sets a notice-level code to be returned to the user
	 * @param int $code One of `SyncApiRequest::NOTICE_*` values
	 */
	public function notice_code($code)
	{
		$this->notice_codes[] = $code;
	}

	// adds a validation message to the 'validation.' element
	public function validation($sField, $sMsg)
	{
		$val = new AjaxValidationObj($sField, $sMsg);
		$this->validation[] = $val;
		if (NULL === $this->focus)				// if the focus elemnet has not been set
			$this->focus($sField);				// set it here
	}

	/**
	 * Sends the contents of the ApiResponse instance to the caller of the API
	 * @param boolean $exit TRUE if script is to end after sending data; otherwise FALSE (default)
	 */
	public function send($exit = TRUE)
	{
		if ($this->nosend)
			return;

		global $wp_version;
		header(self::HEADER_SYNC_VERSION . ': ' . WPSiteSyncContent::PLUGIN_VERSION);		// send this header so sources will know that they're talking to SYNC
		header(self::HEADER_WP_VERSION . ': ' . $wp_version);								// send this header so sources will know that they're talking to WP
		header('Content-Type: application/json');

		if ($this->has_errors()) {
			$this->success = 0;					// force this
			$this->set('message', SyncApiRequest::error_code_to_string($this->error_code));
		}

		$output = $this->__toString();			// construct data to send to browser
		echo $output;							// send data to browser

		if ($exit)
			exit(0);							// stop script
	}

	/**
	 * Convert response data to a json encoded string
	 * @return string A JSON encoded string representation of the response data
	 */
	public function __toString()
	{
		$aOutput = array('error_code' => $this->error_code);
		if (0 !== $this->error_code)
			$aOutput['error_message'] = SyncApiRequest::error_code_to_string($this->error_code);

		if (NULL !== $this->error_data)
			$aOutput['error_data'] = $this->error_data;

		if ($this->session_timeout)
			$aOutput['session_timeout'] = 1;

		if (NULL !== $this->focus)
			$aOutput['focus'] = $this->focus;

		// @deprecated
		if (count($this->errors))
			$aOutput['errors'] = $this->errors;

		// @deprecated
//		if (count($this->notices))
//			$aOutput['notices'] = $this->notices;

		// add notification codes
		if (count($this->notice_codes)) {
			$aOutput['notice_codes'] = $this->notice_codes;
			$aOutput['notices'] = array();
			foreach ($this->notice_codes as $code)
				$aOutput['notices'][] = SyncApiRequest::notice_code_to_string($code);
		}

		if (0 !== $this->error_code || (count($this->errors) + count($this->validation) > 0))
			$aOutput['has_errors'] = 1;
		else
			$aOutput['has_errors'] = 0;

		if ($this->success)
			$aOutput['success'] = 1;
		else
			$aOutput['success'] = 0;

		if (NULL !== $this->form)
			$aOutput['form'] = $this->form;

		if (count($this->validation))
			$aOutput['validation'] = $this->validation;

		if (count($this->data))
			$aOutput['data'] = $this->data;
		else
			$aOutput['data']['error'] = 'none';

		// check WP version and use appropriate encoding method
		global $wp_version;
		if (version_compare($wp_version, '4.1', '>=') && function_exists('wp_json_encode'))
			$sOutput = wp_json_encode($aOutput);
		else
			$sOutput = json_encode($aOutput);
SyncDebug::log(__METHOD__.'() returning response data: ' . var_export($sOutput, TRUE));

		return $sOutput;
	}
}

// EOF