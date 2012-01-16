<?php

    function is_in_localhost($pj = '') {
        global $hosts_file_content;
        return (($pj != '') && strpos($hosts_file_content, $pj.'.dev')) ? true : false;
    }

    function add_to_localhost($pj = '') {
        global $root;
        global $dir;
        global $hosts_file;
        global $hosts_file_content;

        preg_match('/^(?P<match>[\w\d-]+)$/', $pj, $matches);
        if ($pj != $matches['match']) return false;

        $d = $root.$dir.$pj;
        if (is_dir($d) && !is_in_localhost($pj)) {
            $new_host = "\n127.0.0.1  ".$pj.".dev    \t# automatically added by Giko";
            file_put_contents($hosts_file, $new_host, FILE_APPEND | LOCK_EX);
            $hosts_file_content = file_get_contents($hosts_file);
        }
    }

    $hosts_file = '';  # file hosts di sistema
    $root = $_SERVER["DOCUMENT_ROOT"];  # posizione della server_root

    $hosts_file = (is_file($hosts_file)) ? $hosts_file : false;
    $hosts_file = (!$hosts_file && is_file('C:\Windows\System32\drivers\etc\hosts')) ? 'C:\Windows\System32\drivers\etc\hosts' : $hosts_file;
    $hosts_file = (!$hosts_file && is_file('/etc/hosts')) ? '/etc/hosts' : $hosts_file;
    $hosts_file_content = file_get_contents($hosts_file);
    $dir = (isset($_GET['dir'])) ? $_GET['dir'] : '/';
    $dir = str_replace('/..', '', $dir);
    $files = scandir($root.$dir, 0);
    $path = explode('/', $dir);
    $button_html = '<a href="%s" class="button %s">%s</a>';

    if (isset($_GET['add'])) add_to_localhost($_GET['add']);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=iso-8859-1" />
    <title>Giko's Localhost!</title>
    
    <style type="text/css">
        body { font-family: Verdana, Arial, Helvetica, sans-serif; margin: 0; padding: 0; background: #eee; }
        h1 { position: fixed; padding: 9px 10px; margin: 0; top: 0; border-top: 10px solid #eee; border-bottom: 2px solid #eee; background: rgba(255,255,255,0.85); width: 880px; z-index: 10; font-size: 16px; line-height: 20px; color: #333; }
        a { font-size: 11px; text-decoration: none; border-radius: 5px; padding: 3px 6px; display: block; float: left; }
        .container { width: 900px; margin: 10px auto; background: #fff; }
        .side_column { width: 300px; position: fixed; height: 100%; padding-top: 40px; }
        .side_column em { background: #fff; padding: 10px; overflow: auto; display: block; background: #fcc; color: #a00; border-bottom: 2px solid #eee; font-size: 12px; }
        .dir_tree { background: #fff; padding: 10px; overflow: auto; }
        .files_column { padding: 50px 10px 10px; margin: 0 0 0 300px; border-left: 2px solid #eee; overflow: auto; list-style-type: none; }
        .files_column li { clear: both; }
        .button { float: left; border: 1px solid #ccc; background: #eee; margin: 3px; text-align: center; color: #666; }
        .button:hover { border: 1px solid #aaa; background: #ddd; }
        .side_column .button { float: right; clear: both; }
        .red { border: 1px solid #caa; background: #fcc; color: #a00; font-weight: bold; }
        .red:hover { border: 1px solid #c66; background: #faa; }
        .green { border: 1px solid #aca; background: #cfc; color: #0a0; font-weight: bold; }
        .green:hover { border: 1px solid #6c6; background: #afa; }
        .blue { border: 1px solid #aac; background: #ccf; color: #00a; font-weight: bold; }
        .blue:hover { border: 1px solid #66c; background: #aaf; }
        .yellow { border: 1px solid #fe6; background: #ffb; color: #fa0; font-weight: bold; }
        .yellow:hover { border: 1px solid #fd3; background: #ff9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Giko's localhost!</h1>
        <div class="side_column">
<?php if (!$hosts_file) { ?>
            <em>Imposta il tuo file di host</em>
<?php } ?>
            <div class="dir_tree">
<a href="/" class="button yellow">/</a>
<?php
    $t = '/';
    foreach ($path AS $c) {
        if ($c != '') {
            printf($button_html."\n", '?dir='.$t.$c.'/', 'yellow', $c);
            $t .= $c . '/';
        }
    }
?>
            </div>
        </div>
        <ul class="files_column">
<?php
    if ($dir != '/') printf("<li>".$button_html."</li>\n", '?dir='.substr($dir, 0, strrpos($dir, '/', -2)).'/', '', '..');
    foreach ($files AS $value) {
        if (is_dir($root.$dir.$value) && $value != '.' && $value != '..') {
            printf("<li>".$button_html, '?dir='.$dir.$value.'/', 'blue', $value);
            if ($hosts_file && $dir == '/') {
                if (is_in_localhost($value)) printf($button_html, 'http://'.$value.'.dev', 'blue', $value.'.dev');
                else printf($button_html, '?add='.$value, '', 'add host');
            }
            echo "</li>\n";
        }
    }
    foreach ($files AS $value) {
        if (is_file($root.$dir.$value)) {
            $color = (substr($value, 0, 5) == 'index') ? ' green' : '';
            printf("<li>".$button_html."</li>\n", $color, $dir.$value, $value);
        }
    }
?>
        </ul>
    </div>
</body>
</html>