<?php

namespace App\ThirdPArty;

use App\ThirdParty\TextEditor;

/**
 * Implements the free Summernote CDN version.
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
class TextEditorSummernote extends TextEditor
{
	protected string $apiKey =  "";

	/**
	 * Loads the stylesheet.
	 * This is placed in the header
	 *
	 * @return string The HTML link tag.
	 */
    public function load_style()
    {
        return '
		<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
		<style>
			.note-editor.note-airframe, .note-editor.note-frame {
				text-align: initial;
				background:#fff;
			}
		</style>
		';
    }

	/**
	 * Loads or imports the external javascript libaries.
	 * On pages that use wysiwyg textareas, this is called before the <script> tag
	 *
	 * @return string The HTML script tag of external javascript libaries.
	 */
    public function load_script()
    {
        return '<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>';
    }

	/**
	 * Sets up the functions required to create a new instance of a textarea
	 *
	 * @return string The javascript functions and arrays.
	 */
    public function init_script()
    {
        return "
            function set_text_editor( elementID ) {
				$(elementID).summernote({
					height: 300,
					toolbar: [
						['style', ['bold', 'italic', 'underline', 'clear']],
						['font', ['strikethrough', 'superscript', 'subscript']],
						['font', ['fontname', 'fontsize', 'color', 'background']],
						['para', ['ul', 'ol', 'paragraph']],
						/*['insert', ['link', 'picture', 'video']],*/
						['table', ['table']],
						['view', ['fullscreen', 'codeview', 'help']],
					],
					fontNames: ['Arial', 'Verdana', 'Times New Roman'],
					fontSizes: ['10', '12', '14', '18', '24', '36'],
					colors: [
						['#000000', '#FF0000', '#00FF00', '#0000FF', '#FFFF00'],
						['#FF00FF', '#00FFFF', '#000000', '#CCCCCC', '#FFFFFF'],
					], 
					callbacks: {
						onImageUpload: function(files) {
							
						},
						onMediaDelete: function(target) {
							
						}
					}
				});
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
    public function assign_editor($dom_id)
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
    public function get($dom_id)
    {
        return "$( {$dom_id} ).summernote('code');";
    }
}
