<?php
if (! function_exists('setCSRFHeaderMeta')) {
    function setCSRFHeaderMeta() {
        return "<meta name='csrf-token' content='". csrf_hash() ."' />\n";
	}
}

if (! function_exists('updateCSRFMeta')) {
    function updateCSRFMeta() {
		$debug = array();
		
		if (ENVIRONMENT === 'development') {
			$debug[0] = "alert('missing crsf token');";
			$debug[1] = "console.log('new CSRF hash: ', response.new_csrf_token);";
			$debug[2] = "console.log('new CSRF hash from localstorage: ', event.newValue);";
		}
		
		// TODO:
		// look into : return <<<JS heredoc
        return "
			function updateCSRFMeta( response ){
				if ( typeof(response.new_csrf_token) === 'undefined')
				{
					". (isset($debug[0]) ? $debug[0] : '') ."
					location.reload();
				}

				". (isset($debug[1]) ? $debug[1] : '') ."

				$('input[name=\"". csrf_token() ."\"]').val(response.new_csrf_token);
				$('meta[name=\"csrf-token\"]').attr('content', response.new_csrf_token );
				
				localStorage.setItem('csrf_token', response.new_csrf_token);
			}
			
			// Listen for changes to localStorage (for CSRF token updates)
			if (typeof(Storage) !== 'undefined' && window.addEventListener) {
				window.addEventListener('storage', function(event) {
					if (event.key === 'csrf_token') {
						// Update CSRF token in the form input and meta tag
						$('input[name=\"". csrf_token() ."\"]').val(event.newValue);
						$('meta[name=\"csrf-token\"]').attr('content', event.newValue);

						". (isset($debug[2]) ? $debug[2] : '') ."
					}
				});
			}
			else {
				console.log('localstorage not supported for CSRF multi-tab');
			}
			";
    }
}

if (! function_exists('setCSRFPostData')) {
    function setCSRFPostData() {
        return "'". csrf_token() ."': $('meta[name=\"csrf-token\"]').attr('content')\n";
	}
}
