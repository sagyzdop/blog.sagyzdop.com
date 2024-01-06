<?php

// UPDATE FUNCTIONS

function updateCategoryTable($category, $connection)
{
    $sql_query = 'SELECT id FROM category WHERE name = ?';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute([$category]);
    $category_id = $prepared_statement->fetch(PDO::FETCH_COLUMN);

    if (!$category_id) {
        $sql_query = 'INSERT INTO category (name) VALUES (?)';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$category]);
        $category_id = $connection->lastInsertId();
    }

    return $category_id;
}

function removeRedundantCategories($existing_categories, $connection)
{
    $sql_query = 'SELECT name FROM category';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute();
    $all_categories = $prepared_statement->fetchAll(PDO::FETCH_COLUMN);

    $categoriesToRemove = array_diff($all_categories, $existing_categories);

    foreach ($categoriesToRemove as $category) {
        $sql_query = 'DELETE FROM category WHERE name = ?';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$category]);
    }

    $sql_query = 'SELECT name, id FROM category ORDER BY id ASC';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute();
    $all_existing_categories = $prepared_statement->fetchAll(PDO::FETCH_ASSOC);

    $i = 1;
    foreach ($all_existing_categories as $category) {
        if ($category['id'] < 0) continue;
        $sql_query = 'UPDATE category SET id = ? WHERE name = ?';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$i, $category['name']]);
        $i += 1;
    }
}

// UPDATING TAGS

function updateTagTable($post_id, $tagsFromMarkdown, $connection)
{
    if (!$tagsFromMarkdown) return 0;
    $connection->beginTransaction();

    $sql_query = 'SELECT tag.name FROM post_tag JOIN tag ON post_tag.tag_id = tag.id WHERE post_tag.post_id = ?';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute([$post_id]);
    $currentTags = $prepared_statement->fetchAll(PDO::FETCH_COLUMN);

    $tagsToAdd = array_diff($tagsFromMarkdown, $currentTags);
    $tagsToRemove = array_diff($currentTags, $tagsFromMarkdown);

    foreach ($tagsToRemove as $tag) {
        $sql_query = 'DELETE FROM post_tag WHERE post_id = ? AND tag_id = (SELECT id FROM tag WHERE name = ?)';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$post_id, $tag]);

        $sql_query = 'SELECT COUNT(*) FROM post_tag WHERE tag_id = (SELECT id FROM tag WHERE name = ?)';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$tag]);
        $tagCount = $prepared_statement->fetchAll(PDO::FETCH_COLUMN);

        if (!$tagCount) {
            $sql_query = 'DELETE FROM tag WHERE name = ?';
            $prepared_statement = $connection->prepare($sql_query);
            $prepared_statement->execute([$tag]);
        }
    }

    foreach ($tagsToAdd as $tag) {
        $sql_query = 'INSERT INTO tag (name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$tag]);
        $tag_id = $connection->lastInsertId();

        $sql_query = 'INSERT INTO post_tag (post_id, tag_id) VALUES (?, ?)';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$post_id, $tag_id]);
    }

    $connection->commit();
}

function removeRedundantTags($existing_tags, $connection)
{
    $sql_query = 'SELECT name FROM tag';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute();
    $all_tags = $prepared_statement->fetchAll(PDO::FETCH_COLUMN);

    $tagsToRemove = array_diff($all_tags, $existing_tags);

    foreach ($tagsToRemove as $tag) {
        $sql_query = 'DELETE FROM tag WHERE name = ?';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$tag]);
    }

    $sql_query = 'SELECT name FROM tag ORDER BY id ASC';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute();
    $all_existing_tags = $prepared_statement->fetchAll(PDO::FETCH_COLUMN);

    $i = 1;
    foreach ($all_existing_tags as $tag) {
        $sql_query = 'UPDATE tag SET id = ? WHERE name = ?';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$i, $tag]);
        $i += 1;
    }
}

function updatePostTable($md_file_name, $title, $content, $summary, $date_posted, $category, $tagsFromMarkdown, $connection)
{
    $category_id = updateCategoryTable($category, $connection);

    $sql_query = 'SELECT id FROM post WHERE md_file_name = ?';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute([$md_file_name]);

    $post = $prepared_statement->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        $sql_query = 'UPDATE post SET title = ?, content = ?, summary = ?, date_posted = ?, category_id = ? WHERE id = ?';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$title, $content, $summary, $date_posted, $category_id, $post['id']]);

        $post_id = $post['id'];
    } else {
        $sql_query = 'INSERT INTO post (md_file_name, title, content, summary, date_posted, category_id) VALUES (?, ?, ?, ?, ?, ?)';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$md_file_name, $title, $content, $summary, $date_posted, $category_id]);

        $post_id = $connection->lastInsertId();
    }

    updateTagTable($post_id, $tagsFromMarkdown, $connection);

    return $post_id;
}

function removeRedundantPosts($existing_posts, $connection)
{
    $sql_query = 'SELECT id FROM post';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute();
    $all_posts = $prepared_statement->fetchAll(PDO::FETCH_COLUMN);

    $postsToRemove = array_diff($all_posts, $existing_posts);

    foreach ($postsToRemove as $post) {
        $sql_query = 'DELETE FROM post WHERE id = ?';
        $prepared_statement = $connection->prepare($sql_query);
        $prepared_statement->execute([$post]);
    }

    //     Changing post_id to start from $i
    //     $sql_query = 'SELECT md_file_name, id, category_id FROM post WHERE id > 0 AND category_id > 0 ORDER BY id ASC';
    //     $prepared_statement = $connection->prepare($sql_query);
    //     $prepared_statement->execute();
    //     $all_existing_posts = $prepared_statement->fetchAll(PDO::FETCH_ASSOC);

    //     $i = 1;
    //     foreach ($all_existing_posts as $post) {
    //         $sql_query = 'UPDATE post SET id = ? WHERE md_file_name = ?';
    //         $prepared_statement = $connection->prepare($sql_query);
    //         $prepared_statement->execute([$i, $post['md_file_name']]);
    //         $i += 1;
    //     }
}
