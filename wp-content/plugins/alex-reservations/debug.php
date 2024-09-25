<?php

function srrlog($text)
{
	ob_start();
    var_dump($text);
    $text = ob_get_clean();

    $file = plugin_dir_path( __FILE__ ) . 'logs.txt';
    $open = fopen( $file, "a" );
    fwrite($open, "\n" . date('Y-m-d h:i:s') . " :: " . $text);
    fclose($open);
}
