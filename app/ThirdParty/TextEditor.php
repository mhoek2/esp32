<?php

namespace App\ThirdParty;

/**
 * Provides base class for modular wysiwyg text editors
 *
 * To change the active editor:
 * 1. Open to app/Config/Services.php
 * 2. Locate text_editor()
 * 3. Set '$text_editor' to on of the switch options
 *
 * @package App\ThirdParty
 *
 */
class TextEditor
{
	protected string $apiKey;
}
