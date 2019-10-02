<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <?=$this->getStylesheets();?>

    <script type="text/javascript">BASE_URL = "<?=\P3rc1val\Url::getPrefix()?>";</script>
    <script type="text/javascript">WEBSOCKET_URL = "<?=\P3rc1val\Deployment::WEBSOCKET_CLIENT_URL?>";</script>

    <script type="text/javascript" src="assets/js/dist/main.js"></script>
    <meta charset="UTF-8">
    <title>RemoteCMD</title>
</head>
<body>
    <?=$this->content?>
</body>
</html>