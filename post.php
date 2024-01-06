<?php
include 'connect_database.php';
include 'lib/functions.php';

$post_id = $_GET['id'] ?? null;
$post = getPost($post_id, $connection);

$page_title = $post['title'] ?? 'are you lost babygirl?';
$summary = $post['summary'] ?? $page_title;
if ($post && $post['tags']) {
    $keywords = implode(", ", $post['tags']);
} else {
    $keywords = $page_title;
}

?>

<!DOCTYPE HTML>
<html lang="en">

<head>
    <?php include 'head.php'; ?>
</head>

<body>
    <?php include 'header.php'; ?>

    <?php
    displayPost($post);
    
    $connection = null;
    ?>

    <?php include 'footer.php'; ?>
</body>

</html>