SET search_path TO lbaw2114;

DROP TABLE IF EXISTS "transaction";
DROP TABLE IF EXISTS "notification";
DROP TABLE IF EXISTS "block";
DROP TABLE IF EXISTS "rating";
DROP TABLE IF EXISTS "comment";
DROP TABLE IF EXISTS "bid";
DROP TABLE IF EXISTS "follow_auction";
DROP TABLE IF EXISTS "image";
DROP TABLE IF EXISTS "category_auction";
DROP TABLE IF EXISTS "auction";
DROP TABLE IF EXISTS "password_reset_token";
DROP TABLE IF EXISTS "user";

DROP TYPE IF EXISTS TransactionStatus;
DROP TYPE IF EXISTS FormPayment;
DROP TYPE IF EXISTS NotificationType;
DROP TYPE IF EXISTS "Category";
DROP TYPE IF EXISTS AuctionType;
DROP TYPE IF EXISTS "Condition";

-----------------------------------------
-- Types
-----------------------------------------

CREATE TYPE AuctionType AS ENUM ('Public', 'Private');
CREATE TYPE "Condition" AS ENUM ('New', 'Mint', 'Reasonable', 'Poor');
CREATE TYPE "Category" AS ENUM ('Art', 'Technology', 'Books', 'Automobilia', 'Coins & Stamps', 'Music', 'Toys', 'Fashion');
CREATE TYPE NotificationType AS ENUM ('New Bid', 'Auction Ending Aproaching', 'Auction Ended', 'Auction Cancelled', 'Winning Bid');
CREATE TYPE FormPayment AS ENUM ('PayPal', 'Transfer');
CREATE TYPE TransactionStatus AS ENUM ('Accepted', 'Declined', 'Pending');

-----------------------------------------
-- Tables
-----------------------------------------

CREATE TABLE "user" (
  id SERIAL PRIMARY KEY,
  username TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE CHECK(email LIKE '_%@_%.__%'),
  password TEXT NOT NULL,
  credit FLOAT NOT NULL DEFAULT 0.0,
  picture TEXT,
  rating FLOAT NOT NULL DEFAULT 0.0,
  isAdmin BOOLEAN NOT NULL DEFAULT False
);

CREATE TABLE "password_resets" (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL
);

CREATE TABLE "auction"(
  id SERIAL PRIMARY KEY,
  seller_id INTEGER REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,                                 
  title TEXT NOT NULL,
  description TEXT,
  brand TEXT,
  colour TEXT,
  condition "Condition",
  year INTEGER CHECK(year = NULL OR year <= date_part('year', NOW())),
  start_date TIMESTAMP,
  end_date TIMESTAMP CHECK(end_date = NULL OR (start_date != NULL AND end_date >= start_date + interval '24 hours')),
  base_value FLOAT,
  type AuctionType NOT NULL DEFAULT 'Public'
);

CREATE TABLE "category_auction" (
  auction_id INTEGER REFERENCES "auction"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  category "Category" NOT NULL,
  PRIMARY KEY(auction_id, category)
);

CREATE TABLE "image" (
  id SERIAL PRIMARY KEY,
  path TEXT NOT NULL UNIQUE,
  auction_id INTEGER NOT NULL REFERENCES "auction"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE                                 
);

CREATE TABLE "follow_auction" (
  auction_id INTEGER REFERENCES "auction"
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  user_id INTEGER REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  PRIMARY KEY(auction_id, user_id)
);

CREATE TABLE "bid" (
  id SERIAL PRIMARY KEY,  
  user_id INTEGER REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,                                 
  auction_id INTEGER NOT NULL REFERENCES "auction"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  value FLOAT NOT NULL,
  date TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE "comment" (
  id SERIAL PRIMARY KEY,  
  user_id INTEGER REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,                                 
  auction_id INTEGER NOT NULL REFERENCES "auction"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  message TEXT NOT NULL,
  date TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE "rating" (
  rater_id INTEGER NOT NULL REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  rated_id INTEGER NOT NULL REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  score INTEGER NOT NULL CHECK(score >= 0 AND score <= 5),
  PRIMARY KEY(rater_id, rated_id)
);

CREATE TABLE "block" (
  id SERIAL PRIMARY KEY,
  admin_id INTEGER REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL,                         
  user_id INTEGER NOT NULL REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                                      
  description TEXT,
  start_date TIMESTAMP NOT NULL DEFAULT NOW(),
  end_date TIMESTAMP CHECK(end_date = NULL OR end_date > start_date)
);

CREATE TABLE "notification" (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  auction_id INTEGER REFERENCES "auction"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,   
  bid_id INTEGER REFERENCES "bid"(id)   
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                
  date TIMESTAMP NOT NULL DEFAULT NOW(),
  seen BOOLEAN NOT NULL DEFAULT False,
  type NotificationType NOT NULL

  CONSTRAINT notification_of CHECK((auction_id IS NULL AND bid_id IS NOT NULL) OR (auction_id IS NOT NULL AND bid_id IS NULL))
);

CREATE TABLE "transaction" (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES "user"(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,                                 
  value FLOAT NOT NULL,
  description TEXT,
  date TIMESTAMP NOT NULL DEFAULT NOW(),
  method FormPayment NOT NULL,
  status TransactionStatus NOT NULL
);