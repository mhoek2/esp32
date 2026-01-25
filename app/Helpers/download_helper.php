<?php
if (! function_exists('download_url')) {
    function download_url( $path ) : string {
    	return base_url( route_to( 'front.download', $path ) );
    }
}

if (! function_exists('readable_filesize')) {
    function readable_filesize($bytes, $decimals = 2)
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
    }
}