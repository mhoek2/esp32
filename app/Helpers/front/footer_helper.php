<?php


if (! function_exists('load_footer')) {
    function load_footer( &$data ) {

        $data["footer"] = view('front/footer', $data );
    }
}	