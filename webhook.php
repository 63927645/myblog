<?php
// 简单安全验证（可以后面升级）
$secret = "Abc12345679Wyzh20070330";

if ($_GET['key'] !== $secret) {
    exit("no permission");
}

$output = shell_exec("bash /www/wwwroot/wordpress/deploy.sh");

echo "<pre>$output</pre>";