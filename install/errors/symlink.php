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

    <link rel="stylesheet" href="<?php echo $fwurls['bootcss'][0]; ?>" media="screen,print"/>

</head>
<body lang="en-GB">
    <article class="container">
        <section class="row">
           <div class="mx-auto col-md-6 mt-5">
                <p>
                    Your file system is using symbolic links to name the root of the server
                    and the installer cannot locate itself in your file hierarchy.
                </p>
           </div>
        </section>
    </article>
    <script src="<?php echo $fwurls['bootjs'][0]; ?>"></script>
</body>
</html>