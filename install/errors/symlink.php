<!doctype html>
<!--[if lt IE 9]><html class="ie"><![endif]-->
<!--[if gte IE 9]><!--><html><!--<![endif]-->
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <title>Error</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<!--
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
-->

    <link rel="stylesheet" href="<?= $fwurls['bootcss'] ?>" media="screen,print"/>

</head>
<body lang="en-GB">
    <article class="container">
	<div class="row">
	   <div class="col-md-offset-3 col-md-6">
	        <p>
                    Your file system is using symbolic links to name the root of the server
                    and the installer cannot locate itself in your file hierarchy.
                </p>
	   </div>
	</div>
    </article>
    <script src="<?= $fwurls['bootjs'] ?>"></script>
</body>
</html>