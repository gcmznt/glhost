<?php
    $hosts_file = '';  # file hosts di sistema - default ''
    $root = $_SERVER["DOCUMENT_ROOT"];  # posizione della server_root - default $_SERVER["DOCUMENT_ROOT"]

    function subfolders_in_hosts($hn = '') {
        global $hosts_file_content;
        if ($hn == '') return false;
        preg_match_all('/(?P<matches> [\w\d-]+.'.$hn.')/', $hosts_file_content, $matches);
        return $matches['matches'];
    }
    function is_in_hosts($hn = '') {
        global $hosts_file_content;
        return (($hn != '') && strpos($hosts_file_content, ' '.$hn)) ? true : false;
    }
    function add_to_localhost($pj = '', $go = false) {
        global $root;
        global $dir;
        global $hosts_file;
        global $hosts_file_content;

        preg_match('/^(?P<match>[\w\d-]+)$/', $pj, $matches);
        if ($pj != $matches['match']) return false;

        $hn = host_name($dir.$pj);
        $d = $root.$dir.$pj;
        if (is_dir($d) && !is_in_hosts($hn)) {
            $new_host = "\n127.0.0.1  ".$hn."    \t# automatically added by Giko";
            file_put_contents($hosts_file, $new_host, FILE_APPEND | LOCK_EX);
            $hosts_file_content = file_get_contents($hosts_file);
        }
        if ($go) {
            header('Location: http://'.$hn);
        }
    }
    function host_name($dir) {
        $path = explode('/', $dir);
        $hn = '';
        for ($i=count($path); $i>0; $i--) {
            if ($path[$i] != '' && $hn != '') $hn .= '.'.$path[$i];
            elseif ($path[$i] != '') $hn .= $path[$i];
        }
        return $hn.'.dev';
    }

    $hosts_file = (!$hosts_file && is_file('C:\Windows\System32\drivers\etc\hosts')) ? 'C:\Windows\System32\drivers\etc\hosts' : $hosts_file;
    $hosts_file = (!$hosts_file && is_file('/etc/hosts')) ? '/etc/hosts' : $hosts_file;
    $hosts_file_content = file_get_contents($hosts_file);
    $dir = (isset($_GET['dir'])) ? str_replace('/..', '', $_GET['dir']) : '/';
    $files = scandir($root.$dir, 0);
    $path = explode('/', $dir);
    $level = count($path)-2;
    $button_html = '<a href="%s" class="button %s">%s</a>';

    if (isset($_GET['add'])) add_to_localhost($_GET['add'], true);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
    <title>Giko's Localhost!</title>
    
    <style type="text/css">
        html { height: 100%; }
        body { font-family: Verdana, Arial, Helvetica, sans-serif; margin: 0; padding: 0; background: #eee; height: 100%; }
        h1 { position: fixed; padding: 9px 10px; margin: 0; top: 0; border-bottom: 2px solid #eee; background: rgba(255,255,255,0.85); width: 880px; z-index: 10; font-size: 16px; line-height: 20px; color: #333; }
        a { font-size: 11px; text-decoration: none; }
        .container { width: 900px; margin: 0 auto; background: #fff; min-height: 100%; }
        .side_column { width: 300px; position: fixed; height: 100%; padding-top: 40px; border-right: 2px solid #eee; }
        .side_column .messages { position: fixed; width: 302px; bottom: 10px; }
        .side_column .messages div { overflow: hidden; padding: 10px; margin-top: 10px; font-size: 12px; font-style: italic; }
        .side_column .ok { background: #cfc; color: #0a0; border-right: 4px solid #6c6; }
        .side_column .error { background: #fcc; color: #a00; border-right: 4px solid #c66; }
        .dir_tree { background: #fff; padding: 10px; overflow: auto; }
        .files_column { padding: 50px 10px 10px; margin: 0 0 0 302px; overflow: auto; list-style-type: none; }
        .files_column li { clear: both; }
        .button { float: left; border: 1px solid #ccc; background: #eee; margin: 3px; text-align: center; color: #666; display: block; border-radius: 5px; padding: 3px 6px; }
        .right { float: right; }
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
            <div class="messages">
<?php if ($level < 2) { if (!$hosts_file) { $hosts = false; ?>
                <div class="error">Impostare il file hosts</div>
<?php } else { $hosts = true; ?>
                <div class="ok"><?php echo $hosts_file; ?></div>
<?php if (!is_file($hosts_file)) { $hosts = false; ?>
                <div class="error">File di hosts non trovato</div>
<?php } elseif (!is_readable($hosts_file)) { $hosts = false; ?>
                <div class="error">File di hosts non leggibile</div>
<?php } elseif (!is_writable($hosts_file)) { $hosts = false; ?>
                <div class="error">Il file di host non &egrave; scrivibile</div>
<?php }}} ?>
            </div>
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
            $hostname = host_name($dir.$value);
            printf("<li>".$button_html, '?dir='.$dir.$value.'/', 'blue', $value);
            if ($level < 2) {
                if (is_in_hosts($hostname)) printf($button_html, 'http://'.$hostname, 'red right', $hostname);
                elseif ($hosts) printf($button_html, '?dir='.$dir.'&add='.$value, 'right', $hostname);
                $subhosts = subfolders_in_hosts($hostname);
                foreach ($subhosts AS $sh) { printf($button_html, 'http://'.$sh, 'yellow right', $sh); }
            }
            echo "</li>\n";
        }
    }
    foreach ($files AS $value) {
        if (is_file($root.$dir.$value) && ($_SERVER["SCRIPT_FILENAME"] != $root.$dir.$value)) {
            $color = (substr($value, 0, 5) == 'index') ? 'green' : '';
            printf("<li>".$button_html."</li>\n", $dir.$value, $color, $value);
        }
    }
?>
        </ul>
    </div>
</body>
</html>