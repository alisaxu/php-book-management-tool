# php-book-management-tool

Here is a solution for developing an internal management tool that allows administrators to add, edit, and delete books and supports paging, sorting, and high-load queries

###1. Database Schema and SQL Scripts:

I chose MySQL for this tool because:

a. MySQL is highly compatible with PHP.
b. It offers excellent performance, indexing, and scalability options, especially for read-heavy applications.
c. It's widely supported and reliable for building CRUD applications.

Database Schema:https://github.com/alisaxu/php-book-management-tool/blob/main/books.sql

###2. PHP CRUD Functions (Add/Edit/Delete Books):

Function directory ：https://github.com/alisaxu/php-book-management-tool/blob/main/index.php
Function name : addBook,editBook,deleteBook

###3. PHP Function for Pagination and Sorting:

The function will allow sorting by any column (title, author, genre, price, release date, or review rating) and will display 10 books per page.

Function directory ：https://github.com/alisaxu/php-book-management-tool/blob/main/index.php
Function name : getBooks

###4. Stored Procedure for Top 10 Best Sellers:

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

###5. High load optimization strategy:

a. Index optimization: Create indexes for the copies_sold column to speed up queries.
b. Cache mechanism: Use Redis or Memcached to cache popular data to reduce database pressure.
c. Read/write separation: Implements efficient data read through master/slave replication and load balancing