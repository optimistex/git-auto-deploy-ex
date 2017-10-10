<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Deploy log</title>
</head>
<body>
<h1>LOG </h1>
<pre>
<?php
if (file_exists('log.txt')) {
    include('log.txt');
} else {
    echo 'log.txt not found';
}
?>
</pre>
</body>
</html>