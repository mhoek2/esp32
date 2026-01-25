<?php


if (! function_exists('load_header')) {
    function load_header( &$data ) {

        $data["header"] = view('admin/header', $data );
    }
}	