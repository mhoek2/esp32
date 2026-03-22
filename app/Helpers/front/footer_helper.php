<?php


if (! function_exists('load_footer')) {
    function load_footer( &$data ) {

        $data['FP'] = service('floor_plan')->getFooterConfigJS( false );
        $data['FP_footer'] = view('shared/floorplan/footer', $data );

        $data["footer"] = view('front/footer', $data );
    }
}	