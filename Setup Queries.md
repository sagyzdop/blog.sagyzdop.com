SQL queries for MySQL to set up the database to work with the project. Tables must be created in the given order.

1. Creating table for categories:

```
CREATE TABLE category (
  id int PRIMARY KEY,
  name varchar(255) NOT NULL UNIQUE
);
```

2. Creating table for tags:

```
CREATE TABLE tag (
  id int PRIMARY KEY,
  name varchar(255) NOT NULL UNIQUE,
);
```

3. Creating table for posts:

```
CREATE TABLE post (
  id int PRIMARY KEY,
  md_file_name varchar(255) NOT NULL UNIQUE,
  title varchar(255) DEFAULT NULL,
  content text DEFAULT NULL,
  summary varchar(255) DEFAULT NULL,
  date_posted date DEFAULT NULL,
  category_id int DEFAULT NULL,
  CONSTRAINT fk_category
    FOREIGN KEY (category_id) 
    REFERENCES category (id) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
);
```

4. Creating table for post-tag relations:

```
CREATE TABLE post_tag (
  post_id int NOT NULL,
  tag_id int NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  CONSTRAINT fk_post
    FOREIGN KEY (post_id) 
    REFERENCES post (id) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT fk_tag
    FOREIGN KEY (tag_id) 
    REFERENCES tag (id) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
);
```