<?php
/**
 * @var $source
 * @var $result
 * @var $errorMsg
 **/
?>
<!DOCTYPE html>
<html lang="en" class="ad-layout-narrow layout-single-column-post">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta property="og:site_name" content="All Women Stalk"/>
    <meta name="language" content="en"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=no"/>
    <meta name="referrer" content="always"/>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css"
          integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <h2>Emogify text</h2>
    <form method="post">
        <div class="form-group">
            <label>Source:</label>
            <textarea class="form-control" name="source" autofocus
                      rows="10"><?= htmlspecialchars($source); ?></textarea>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Convert</button>
        </div>
    </form>
    <? if (!empty($errorMsg)) : ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorMsg); ?></div>
    <? endif; ?>
    <? if (!empty($result)) : ?>
        <div class="form-group">
            <label>Result:</label>
            <textarea class="form-control" rows="10"><?= htmlspecialchars($result); ?></textarea>
        </div>
    <? endif; ?>
</div>
</body>
</html>
