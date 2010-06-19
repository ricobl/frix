<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title><?= Frix::config('PROJECT_TITLE') ?> -- Error <?= $error_code ?></title>
</head>

<body>

<h1>Error: <?= $error_code ?></h1>

<p><?= $error_msg ?></p>

</body>
</html>
