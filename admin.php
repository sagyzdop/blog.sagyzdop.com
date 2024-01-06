<!DOCTYPE HTML>
<html lang="en">

<head>
    <?php
    $page_title = '👀';
    include 'head.php'; ?>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class='page-content'>
        <form action='update.php' method='POST'>
            <input type='password' name='update' id='update' />
            <input type='submit' value='Sign In' />
        </form>
    </div>

</body>