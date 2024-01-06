<?php
include 'connect_database.php';
include 'lib/functions.php';

$filter_category = $_GET['filter_by_category'] ?? null;
$filter_tag = $_GET['filter_by_tag'] ?? null;
$sort = $_GET['sort'] ?? 'DESC';

$page_title = 'home';
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
    <?php include 'head.php'; ?>
</head>

<body>
    <?php include 'header.php'; ?>

    <?php
    displayPosts($filter_category, $filter_tag, $connection, $sort);

    $connection = null;
    ?>

    <?php include 'footer.php'; ?>
</body>

</html>