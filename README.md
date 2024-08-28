# php-book-management-tool

Here is a solution for developing an internal management tool that allows administrators to add, edit, and delete books and supports paging, sorting, and high-load queries

## Solution

### 1. Database Schema and SQL Scripts:

I chose MySQL for this tool because:

- MySQL is highly compatible with PHP.
- It offers excellent performance, indexing, and scalability options, especially for read-heavy applications.
- It's widely supported and reliable for building CRUD applications.

Database Schema:https://github.com/alisaxu/php-book-management-tool/blob/main/docker/mysql/init.sql

### 2. PHP CRUD Functions (Add/Edit/Delete Books):

- Function directory ：https://github.com/alisaxu/php-book-management-tool/blob/main/src/index.php
- Function name : addBook,editBook,deleteBook

### 3. PHP Function for Pagination and Sorting:

The function will allow sorting by any column (title, author, genre, price, release date, or review rating) and will display 10 books per page.

- Function directory ：https://github.com/alisaxu/php-book-management-tool/blob/main/src/index.php
- Function name : getBooks

### 4. Stored Procedure for Top 10 Best Sellers:

In high-load scenarios, index and cache optimization is used to achieve the best performance

Indexes and stored procedures:

```shell
CREATE INDEX idx_copies_sold ON books (copies_sold);

DELIMITER //

#Get the top 10 books with the highest sales and highest ratings
CREATE PROCEDURE getTopBestSellers()
BEGIN
    SELECT title, author, genre, price, release_date, review_rating, copies_sold
    FROM books
    ORDER BY copies_sold DESC, review_rating DESC
    LIMIT 10;
END //

DELIMITER ;
```

You can invoke this stored procedure with the following command:

```shell
CALL getTopBestSellers();
```

### 5. High load optimization strategy:

- Index optimization: Create indexes for the copies_sold column to speed up queries.
- Cache mechanism: Use Redis or Memcached to cache popular data to reduce database pressure.
- Read/write separation: Implements efficient data read through master/slave replication and load balancing


## Deployment procedure

- Pull the code locally
```shell
git clone git@github.com:alisaxu/php-book-management-tool.git
```

- Build image
```shell
docker-compose up -d
docker-compose down

#Reconstruct image
docker-compose up --build
```

- Log in to mysql
```shell
#password : userpassword
docker exec -it php-book-management-tool-mysql-1 mysql -uuser -p
use dbbook;

#Verify that the data is successfully added, deleted, and modified
select * from books;

#Display field information
SHOW COLUMNS FROM books;
```

- Enter the nginx container
```shell
docker-compose exec -it nginx /bin/sh
#Code address
cd /var/www/html
```

## How to test features

- Api document: https://apifox.com/apidoc/shared-d861ca62-a404-4fce-84e8-f5bbbb89b8ce

- Curl:

```shell
#addBook

curl --location --request POST 'http://localhost:8080/addBook' \
--header 'User-Agent: Apifox/1.0.0 (https://apifox.com)' \
--form 'title="12356"' \
--form 'author="1"' \
--form 'genre="1"' \
--form 'price="2"' \
--form 'release_date="2021-01-09"' \
--form 'review_rating="2.0"'


#editBook

curl --location --request POST 'http://localhost:8080/editBook' \
--header 'User-Agent: Apifox/1.0.0 (https://apifox.com)' \
--form 'title="1235"' \
--form 'author="1"' \
--form 'genre="1"' \
--form 'price="2"' \
--form 'release_date="2021-01-09"' \
--form 'review_rating="2.0"' \
--form 'id="1"'

#deleteBook

curl --location --request POST 'http://localhost:8080/addBook' \
--header 'User-Agent: Apifox/1.0.0 (https://apifox.com)' \
--form 'title="12356"' \
--form 'author="1"' \
--form 'genre="1"' \
--form 'price="2"' \
--form 'release_date="2021-01-09"' \
--form 'review_rating="2.0"'

#getBooks

curl --location --request POST 'http://localhost:8080/getBooks' \
--header 'User-Agent: Apifox/1.0.0 (https://apifox.com)' \
--form 'price="2"' \
--form 'release_date="2021-01-09"' \
--form 'review_rating="2.0"'
```