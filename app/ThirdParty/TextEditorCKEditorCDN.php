<?php

namespace App\ThirdPArty;

use App\ThirdParty\TextEditor;

/**
 * Implements the CKEditor CDN version using a apiKey.
 *
 * @package App\ThirdParty
 *
 * @example
 * ```
 * // Controller:
 * $this->data['text_editor'] = service('text_editor');
 *
 * // View:
 * // header: 
 * <?=service('text_editor')->load_style()?>
 *
 * // footer:
 * <?=$text_editor->load_script()?>
 * <script {csp-script-nonce}>
 * 	$(document).ready(function () {
 * 		<?=$text_editor->init_script()?>
 * 		<?=$text_editor->assign_editor('"#id_of_textarea"')?>
  		let value = <?=$text_editor->get('#id_of_textarea"')?>
 * 	});
 * </script>
 * ```
 */
class TextEditorCKEditorCDN extends TextEditor
{
	protected string $apiKey = "eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3NzA0MjIzOTksImp0aSI6IjdiNzA0MzZhLWE2MDMtNGI2Yi1hOGQxLTJmYzk3MWEzYmIxYyIsImxpY2Vuc2VkSG9zdHMiOlsiMTI3LjAuMC4xIiwibG9jYWxob3N0IiwiMTkyLjE2OC4qLioiLCIxMC4qLiouKiIsIjE3Mi4qLiouKiIsIioudGVzdCIsIioubG9jYWxob3N0IiwiKi5sb2NhbCJdLCJ1c2FnZUVuZHBvaW50IjoiaHR0cHM6Ly9wcm94eS1ldmVudC5ja2VkaXRvci5jb20iLCJkaXN0cmlidXRpb25DaGFubmVsIjpbImNsb3VkIiwiZHJ1cGFsIl0sImxpY2Vuc2VUeXBlIjoiZGV2ZWxvcG1lbnQiLCJmZWF0dXJlcyI6WyJEUlVQIiwiQk9YIl0sInZjIjoiZDYwZjUzMDUifQ.z2QBK-JsDzcivBdrBMos8JHYhKbS4nUDXdFJkRtnmpyThmyA9JEfG5mFfGjIN8pZCdtrTYrirVWdxKcm5Ptkhw";
	
	/**
	 * Loads the stylesheet.
	 * This is placed in the header
	 *
	 * @return string The HTML link tag.
	 */
	public function load_style()
	{
		return '<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.1.0/ckeditor5.css">';
	}
	
	/**
	 * Loads or imports the external javascript libaries.
	 * On pages that use wysiwyg textareas, this is called before the <script> tag
	 *
	 * @return string The HTML script tag of external javascript libaries.
	 */
	public function load_script()
	{
		return '<script src="https://cdn.ckeditor.com/ckeditor5/44.1.0/ckeditor5.umd.js"></script>';
	}
	
	/**
	 * Sets up the functions required to create a new instance of a textarea
	 *
	 * @return string The javascript functions and arrays.
	 */
	public function init_script()
	{
		return "
			let CKEditorArray = [];
			
			const {
				ClassicEditor,
				Essentials,
				Paragraph,
				Bold,
				Italic,
				Font,
				Heading,
				Link,
				BlockQuote,
				CodeBlock,
				List,
				TodoList,
			} = CKEDITOR;
			
			function set_text_editor( elementID ){
				ClassicEditor.create( document.querySelector( elementID ), {
					licenseKey: '{$this->apiKey}',
					plugins: [ Essentials, Paragraph, Bold, Font, Heading, Link, Italic, BlockQuote, CodeBlock, List, TodoList, ],
					toolbar: {
						items: [
							'undo', 'redo',
							'|',
							'heading',
							'|',
							'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
							'|',
							'bold', 'italic', 'strikethrough', 'subscript', 'superscript', 'code',
							'|',
							'link', 'uploadImage', 'blockQuote', 'codeBlock',
							'|',
							'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent'
						],
						shouldNotGroupWhenFull: false
					}
				} )
				.then( editor => {
					CKEditorArray[elementID] = editor;
				} )
				.catch( error => {
					console.error( error );
				} );
			}
		";
	}
	
	/**
	 * Use this wrapper in the view template to create a new textarea instance
	 *
	 * @param string $dom_id The DOM ID of the element, includes #.
	 *
	 * @return string The javascript syntax
	 */		
	public function assign_editor( string $dom_id )
	{
		return "set_text_editor( {$dom_id} );";
	}
	
	/**
	 * Use this wrapper in the view template to get the value of a textarea
	 *
	 * @param string $dom_id The DOM ID of the element, includes #.
	 *
	 * @return string The javascript syntax
	 */	
	public function get( string $dom_id )
	{
		return "CKEditorArray[ {$dom_id} ].getData();";
	}
}
