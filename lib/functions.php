<?php

// GET FUNCTIONS
// Should return a value or rows from a database

function getCategoryName($category_id, $connection)
{
    $sql_query = 'SELECT name FROM category WHERE id = ?';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute([$category_id]);
    $category = $prepared_statement->fetch(PDO::FETCH_COLUMN);

    return $category;
}

function getCategoryId($category, $connection)
{
    $sql_query = 'SELECT id FROM category WHERE name = ?';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute([$category]);
    $category_id = $prepared_statement->fetch(PDO::FETCH_COLUMN);

    return $category_id;
}

function getTagsNames($post_id, $connection)
{
    $sql_query = 'SELECT t.name
        FROM tag t
        INNER JOIN post_tag pt ON t.id = pt.tag_id
        WHERE pt.post_id = ?';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute([$post_id]);
    $tags = $prepared_statement->fetchAll(PDO::FETCH_COLUMN);

    return $tags;
}

function getPosts($category, $tag, $connection, $sort = 'DESC')
{
    $conditions = [];
    $params = [];

    $category_id = getCategoryId($category, $connection);
    if ($category_id < 0) return null;

    $sort = strtoupper($sort);
    if ($sort !== 'ASC' && $sort !== 'DESC') {
        $sort = 'DESC';
    }

    $sql_query = 'SELECT p.id, p.title, p.content, p.summary, DATE_FORMAT(p.date_posted, "%d.%m.%Y") AS date_posted, p.category_id 
                  FROM post p ';

    if (isset($category)) {
        $conditions[] = 'p.category_id = ?';
        $params[] = $category_id;
    }

    if (isset($tag)) {
        $sql_query .= 'INNER JOIN post_tag pt ON p.id = pt.post_id 
                       INNER JOIN tag t ON pt.tag_id = t.id ';
        $conditions[] = 't.name = ?';
        $params[] = $tag;
    }

    if (!empty($conditions)) {
        $sql_query .= 'WHERE p.id > 0 AND ' . implode(' AND ', $conditions) . ' ORDER BY p.date_posted ' . $sort;
    } else {
        $sql_query .= 'WHERE p.id > 0 AND p.category_id > 0 ORDER BY p.date_posted ' . $sort;
    }

    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute($params);

    $posts = $prepared_statement->fetchAll(PDO::FETCH_ASSOC);

    return $posts;
}

function getPost($post_id, $connection)
{
    $sql_query = 'SELECT id, title, content, summary, DATE_FORMAT(date_posted, "%d.%m.%Y") AS date_posted, category_id FROM post WHERE id = ?';
    $prepared_statement = $connection->prepare($sql_query);
    $prepared_statement->execute([$post_id]);
    $post = $prepared_statement->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        $post['category'] = getCategoryName($post['category_id'], $connection);
        $post['tags'] = getTagsNames($post_id, $connection);
    }

    return $post;
}


// CREATE FUNCTIONS
// Should return a result from values passed

function createFilterLink($filter_category, $filter_tag, $tag = '', $sort = 'DESC')
{
    $link = 'home.php?sort=' . urlencode($sort);
    $parts = [];
    if ($filter_category) {
        $parts[] = 'filter_by_category=' . urlencode($filter_category);
    }
    if ($filter_tag || $tag) {
        $parts[] = 'filter_by_tag=' . urlencode($tag ?: $filter_tag);
    }
    if ($parts) {
        $link .= '&';
    }

    return $link . implode('&', $parts);
}

// DISPLAY FUNCTIONS
// Should contain 'echo' statements and do not return any value

function displayDatePosted($date_posted)
{
    if ($date_posted) {
        echo '<span>';
        echo '<date>';
        echo htmlspecialchars($date_posted);
        echo '</date>';
        echo '</span>';
    }
}

function displayPostListTitle($post_id, $title)
{
    if ($title) {
        echo '<div class="post-list-title">';
        echo '<a href="post.php?id=' . urlencode($post_id) . '&title=' . urlencode($title) . '">';
        echo htmlspecialchars($title);
        echo '</a>';
        echo '</div>';
    }
}

function displaySummary($summary)
{
    if ($summary) {
        echo '<p>';
        echo htmlspecialchars($summary);
        echo '</p>';
    }
}

function displayCategory($category_id, $category, $filter_tag = '')
{
    if ($category && $category_id > 0) {
        echo '<span>';
        echo '<a href="' . createFilterLink($category, $filter_tag, null, 'DESC') . '">';
        echo htmlspecialchars($category);
        echo '</a>';
        echo '</span>';
    }
}

function displayTags($tags, $filter_category = '', $filter_tag = '')
{
    if ($tags) {
        echo '<div class="post-meta">';
        foreach ($tags as $tag) {
            echo '<span>';
            echo '<a href="' . createFilterLink($filter_category, $filter_tag, $tag, 'DESC') . '">';
            echo htmlspecialchars('#' . $tag);
            echo '</a>';
            echo '</span>';
        }
        echo '</div>';
    }
}

function displayPosts($filter_category, $filter_tag, $connection, $sort = 'DESC')
{
    $posts = getPosts($filter_category, $filter_tag, $connection, $sort);

    if ($posts) {
        echo '<div class="page-content">';

        displayHomePageHeading($filter_category, $filter_tag);
        displaySortLink($filter_category, $filter_tag, $sort);

        echo '<hr>';

        echo '<ul class="posts-list">';
        foreach ($posts as $post) {
            $post_id = $post['id'];
            $title = $post['title'];
            $summary = $post['summary'];
            $date_posted = $post['date_posted'];
            $category_id = $post['category_id'];
            $category = getCategoryName($post['category_id'], $connection);
            $tags = getTagsNames($post['id'], $connection);


            echo '<li class="post">';

            echo '<div class="post-meta">';
            displayDatePosted($date_posted);
            displayCategory($category_id, $category, $filter_tag);
            echo '</div>';

            // Post list title
            displayPostListTitle($post_id, $title);

            // Post description
            displaySummary($summary);
            displayTags($tags, $filter_category, $filter_tag);

            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    } else {
        include 'no_post_message.php';
    }
}

function displayPost($post)
{
    if ($post) {
        $title = $post['title'];
        $content = $post['content'];
        $summary = $post['summary'];
        $date_posted = $post['date_posted'];
        $category = $post['category'];
        $category_id = $post['category_id'];
        $tags = $post['tags'];

        echo '<div class="page-content">';

        echo '<h1 class="page-title">';
        echo htmlspecialchars($title);
        echo '</h1>';

        echo '<div class="post-meta">';
        displayDatePosted($date_posted);
        displayCategory($category_id, $category);
        echo '</div>';

        echo '<hr>';
        displayTags($tags);

        echo '<article>';
        echo $content;
        echo '</article>';

        displayCommentLink($date_posted, $title);

        echo '</div>';
    } else {
        include 'no_post_message.php';
    }
}

function displayHomePageHeading($filter_category, $filter_tag)
{
    if ($filter_category && $filter_tag) {
        echo '<h1 class="page-title">';
        echo htmlspecialchars($filter_category  . ': ');
        echo htmlspecialchars('#' . $filter_tag);
        echo '</h1>';
    } elseif ($filter_category) {
        echo '<h1 class="page-title">';
        echo htmlspecialchars($filter_category);
        echo '</h1>';
    } elseif ($filter_tag) {
        echo '<h1 class="page-title">';
        echo htmlspecialchars('#' . $filter_tag);
        echo '</h1>';
    } else {
        echo '<h1 class="page-title">';
        echo 'Home';
        echo  '</h1>';
    }
}

function displaySortLink($filter_category, $filter_tag, $sort)
{
    if ($sort == 'ASC') {
        echo '<a href="' . createFilterLink($filter_category, $filter_tag, '', 'DESC') . '">';
        echo 'Show newest first';
        echo '</a>';
    } else {
        echo '<a href="' . createFilterLink($filter_category, $filter_tag, '', 'ASC') . '">';
        echo 'Show oldest first';
        echo '</a>';
    }
}

function displayCommentLink($date_posted, $title)
{
    $subject = 'Comment on the post on: ' . $date_posted . ' - ' . $title;

    // Mailto link
    echo '<a href="mailto:comments@sagyzdop.com?subject=' . $subject . '">Send a Comment</a>';
}
