<!DOCTYPE HTML>
<html lang="en">

<head>
    <?php
    $update = $_POST["update"] ?? null;
    $page_title = "&#128064;";
    include 'head.php'; ?>
</head>

<body>
    <?php include 'header.php'; ?>
    <?php

    use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

    if ($update == 'test') {

        echo '<div class="page-content">';

        echo '<h1 class="page-title">Site Updated</h1>';

        include 'connect_database.php';
        include 'lib/functions.php';
        include 'lib/update-functions.php';
        include 'lib/composer-commonmark-config.php';

        $files = new DirectoryIterator('md');
        $existing_posts = [];
        $existing_categories = [];
        $existing_tags = [];

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() == 'md') {

                $md_file_name = $file->getFilename();
                $md_file_path = $file->getPathname();
                $md_file = fopen($md_file_path, "r");

                $parsed = $converter->convert(fread($md_file, filesize($md_file_path)));
                $content = $parsed->getContent();

                fclose($md_file);

                if ($parsed instanceof RenderedContentWithFrontMatter) {
                    $front_matter = $parsed->getFrontMatter();
                }

                $title = $front_matter['title'] ?? "No Title";
                $summary = $front_matter['summary'] ?? null;
                $date_posted = $front_matter['date_posted'];
                $category = $front_matter['category'];
                $tags = $front_matter['tags'] ?? [];

                $post_id = updatePostTable($md_file_name, $title, $content, $summary, $date_posted, $category, $tags, $connection);
                $existing_posts[] = $post_id;
                $existing_categories[] = $category;

                foreach ($tags as $tag) {
                    $existing_tags[] = $tag;
                }

                echo "<a href='post.php?id=" . urlencode($post_id) . "title=" . urlencode($title) . "'>" . htmlspecialchars($title) . "</a>";
            }
        }

        removeRedundantPosts($existing_posts, $connection);
        removeRedundantTags($existing_tags, $connection);
        removeRedundantCategories($existing_categories, $connection);

        $connection = null;

        echo '</div>';
    } else {
        include 'no_post_message.php';
    }
    ?>

</body>

</html>