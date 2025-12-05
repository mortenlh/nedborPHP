<?php
require_once '../vendor/autoload.php';

$path = '../data/';
$nedbor = new \Momme\Nedbor\Nedbor($path);
$nedbor->getCvsdata();

$colors = ['white', '#3d5afe', 'brown', 'lightgreen', 'red', 'blue', 'yellow', '#d500f9', 'darkgreen', '#ff5252', '#3d5afe', '#ffab00', '#c51162'];
$svglink = $nedbor->sumnedborSVGLink($colors);

?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nedbør statestik</title>
    <link rel="icon" type="image/png" href="public/favicon.png">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-colors-flat.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            /*font-family: Segoe UI, Arial, sans-serif;*/
            background-color: #ffffff;
            overflow-x: scroll;
        }

        a {
            font-size: 16px;
        }

        .maxmonth {
            color: white;
            background-color: #ff5252;
        }

        .minmonth {
            color: black;
            background-color: #ffff8d;
        }

        .over100 {
            color: white;
            background-color: #0091ea;
        }

        table caption {
            font-family: Segoe UI, Arial, sans-serif;
            font-size: 28px;
        }
    </style>
</head>

<body>
    <div class="w3-container">
        <h1>Nedbør statestik <i class="bi bi-cloud-rain-fill"></i></h1>
        <a href="<?php echo $svglink; ?>" target="_blank">Nedbør årstotal <i class="bi bi-graph-up"></i> (Åbner i nyt vindue)</a>
    </div>
    <div class="w3-container">
        <div class="w3-row">
            <div class="w3-col w3-padding-top-10" style="width:100%">
                <?php echo $nedbor; ?>
            </div>
        </div>
        <div class="w3-row">
            <div class="w3-col w3-padding-top-32" style="width:50%">
                <?php echo $nedbor->nedborToHTML(); ?>
            </div>
        </div>
        <div class="w3-row">
            <div class="w3-col w3-padding-top-32" style="width:50%">
                <?php echo $nedbor->sumnedborToHTML(); ?>
            </div>
        </div>
    </div>
</body>

</html>