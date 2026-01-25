<?php

namespace Config;

use CodeIgniter\Config\BaseService;

use App\ThirdParty\TextEditor;
use App\ThirdParty\TextEditorCKEditorCDN as CKEditorCDN;
use App\ThirdParty\TextEditorCKEditorGPL as CKEditorGPL;
use App\ThirdParty\TextEditorSummernote as Summernote;
	
/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
	
	public static function text_editor($getShared = true): TextEditor
	{
		if ($getShared) {
			return static::getSharedInstance('text_editor');
		}
		
		$text_editor = 'ckeditorGPL';
		
		switch ( $text_editor ) {
			case 'ckeditorCDN':
				return new CKEditorCDN();
			case 'ckeditorGPL':
				return new CKEditorGPL();	
			case 'summernote':
				return new Summernote();
		}
	}
}
