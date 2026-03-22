<?php


if (! function_exists('load_footer')) {
    function load_footer( &$data ) {
        $data['FP'] = service('floor_plan')->getFooterConfigJS( ($data['is_backoffice'] && $data['user']["is_admin"]) );
        $data['FP_footer'] = view('shared/floorplan/footer', $data );

        $data["footer"] = view('admin/footer', $data );
    }
}	