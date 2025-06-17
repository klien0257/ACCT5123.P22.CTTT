SET DEFINE OFF;
-- DROP existing tables if present
BEGIN
  EXECUTE IMMEDIATE 'DROP TABLE subscription CASCADE CONSTRAINTS';
EXCEPTION WHEN OTHERS THEN NULL;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'DROP TABLE books CASCADE CONSTRAINTS';
EXCEPTION WHEN OTHERS THEN NULL;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'DROP TABLE customer CASCADE CONSTRAINTS';
EXCEPTION WHEN OTHERS THEN NULL;
END;
/

BEGIN
  EXECUTE IMMEDIATE 'DROP TABLE lib CASCADE CONSTRAINTS';
EXCEPTION WHEN OTHERS THEN NULL;
END;
/

-- CREATE lib table with extended column sizes + cover_url
CREATE TABLE lib (
  i_index number,
  r_index number,
  title         VARCHAR2(500),
  isbn_10       VARCHAR2(50),
  isbn_13       VARCHAR2(50),
  publish_date  VARCHAR2(50),
  key_col       VARCHAR2(200),
  subjects      CLOB,
  languages     VARCHAR2(200),
  description   CLOB,
  genres        VARCHAR2(500),
  image_id      VARCHAR2(50),
  image_url     VARCHAR2(2000)
);

-- customer table unchanged
CREATE TABLE customer(
  rollno   NUMBER PRIMARY KEY,
  name     VARCHAR2(50),
  no_card  NUMBER
);

-- books table (instances of lib.bookname)
CREATE TABLE books(
  bookno       NUMBER PRIMARY KEY,
  bookname     VARCHAR2(200),
  available    VARCHAR2(3),
  subscribed_to NUMBER
);

-- subscription table
CREATE TABLE subscription(
  bookno      NUMBER,
  rollno      NUMBER,
  do_sub      DATE,
  do_return   DATE,
  fineamount  NUMBER,
  status      VARCHAR2(20)
);

-- Index to speed up lookup by bookname
CREATE INDEX idx_books_bookname ON books(bookname);
CREATE INDEX idx_sub_bookno   ON subscription(bookno);
CREATE INDEX idx_sub_rollno   ON subscription(rollno);

-- The rental procedure (unchanged)
CREATE OR REPLACE PROCEDURE sub(bname IN VARCHAR2, roll_no IN NUMBER) IS
  stud_rec customer%ROWTYPE;
  book_no NUMBER;
  no_of_books NUMBER;
BEGIN
  SELECT * INTO stud_rec FROM customer WHERE rollno = roll_no;

  IF stud_rec.no_card <= 0 THEN
    RAISE_APPLICATION_ERROR(-20001, 'No cards available to rent books');
  END IF;

  SELECT COUNT(*) INTO no_of_books
    FROM books
   WHERE bookname = bname
     AND available = 'yes';

  IF no_of_books = 0 THEN
    RAISE_APPLICATION_ERROR(-20002, bname || ' is not available');
  END IF;

  SELECT MIN(bookno) INTO book_no
    FROM books
   WHERE bookname = bname
     AND available = 'yes';

  INSERT INTO subscription (bookno, rollno, do_sub, do_return, fineamount, status)
  VALUES (book_no, roll_no, SYSDATE, SYSDATE + 7, 0, 'ntreturned');

  UPDATE customer
     SET no_card = no_card - 1
   WHERE rollno = roll_no;

  UPDATE books
     SET available    = 'no',
         subscribed_to = roll_no
   WHERE bookno = book_no;

EXCEPTION
  WHEN NO_DATA_FOUND THEN
    RAISE_APPLICATION_ERROR(-20003, 'Customer does not exist');
END;
/


-- Sample customers
INSERT INTO customer VALUES (22520753, 'Lien', 2);
INSERT INTO customer VALUES (32520753, 'Neil', 3);
INSERT INTO customer VALUES (42520753, 'Eiln', 4);
INSERT INTO customer VALUES (52520753, 'Leni', 5);

COMMIT;

-- Check a handful of random rows
SELECT *
  FROM lib
WHERE title = '1 last shot';


