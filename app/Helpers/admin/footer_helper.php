<?php


if (! function_exists('load_footer')) {
    function load_footer( &$data ) {

        $data["footer"] = view('admin/footer', $data );
    }
}	