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

-----------------------------------------------------------------
--                          INDICES
-----------------------------------------------------------------

CREATE INDEX auction_bid ON "bid" USING hash (auction_id);

CREATE INDEX user_follow ON "follow_auction" USING hash (user_id);

CREATE INDEX start_auction ON "auction" USING btree (start_date);

----------------------------------------------------------------

-- Add column to auction to store computed ts_vectors.
ALTER TABLE "auction"
ADD COLUMN tsvectors TSVECTOR;

-- Create a function to automatically update ts_vectors.
CREATE FUNCTION auction_search_update() RETURNS TRIGGER AS 
$BODY$
BEGIN
    IF TG_OP = 'INSERT' THEN
            NEW.tsvectors = (
            setweight(to_tsvector('english', NEW.title), 'A') ||
            setweight(to_tsvector('english', NEW.description), 'B')
            );
    END IF;

    IF TG_OP = 'UPDATE' THEN
        IF (NEW.title <> OLD.title OR NEW.description <> OLD.description) THEN
        NEW.tsvectors = (
            setweight(to_tsvector('english', NEW.title), 'A') ||
            setweight(to_tsvector('english', NEW.description), 'B')
        );
        END IF;
    END IF;
    RETURN NEW;
END 
$BODY$
LANGUAGE plpgsql;

-- Create a trigger before insert or update on auction.
CREATE TRIGGER auction_search_update
 BEFORE INSERT OR UPDATE ON auction
 FOR EACH ROW
 EXECUTE PROCEDURE auction_search_update();


-- Finally, create a GIN index for ts_vectors.
CREATE INDEX search_idx ON "auction" USING GIN (tsvectors);


-----------------------------------------------------------------
--                          TRIGGERS
-----------------------------------------------------------------

CREATE FUNCTION checks_money_to_bid() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF NEW.value >= (SELECT credit FROM "user" WHERE NEW.user_id = id) 
        THEN RAISE EXCEPTION 'A user can only bid if he has the pretended bidding value either in credit.';
        END IF;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER checks_money_to_bid
        BEFORE INSERT OR UPDATE ON "bid"
        FOR EACH ROW
        EXECUTE PROCEDURE checks_money_to_bid();

-------------------------------------------------------------------

CREATE FUNCTION check_bidder_ownership() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF NEW.user_id = (SELECT seller_id FROM "auction" WHERE NEW.auction_id = id) THEN
           RAISE EXCEPTION 'The owner of the auction cannot bid in his own auction.';
        END IF;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER check_bidder_ownership
        BEFORE INSERT OR UPDATE ON "bid"
        FOR EACH ROW
        EXECUTE PROCEDURE check_bidder_ownership();

--------------------------------------------------------------------

CREATE FUNCTION min_value_bid() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF NEW.value <= (SELECT MAX(value) FROM "bid" WHERE NEW.auction_id = auction_id) THEN
           RAISE EXCEPTION 'A new bid must be strictly greater than the previous bid.';
        END IF;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER min_value_bid
        BEFORE INSERT OR UPDATE ON "bid"
        FOR EACH ROW
        EXECUTE PROCEDURE min_value_bid();

----------------------------------------------------------------

CREATE FUNCTION check_ownership_bid() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF ((SELECT id from "bid" WHERE value = (SELECT MAX(value) FROM "bid" WHERE NEW.user_id = user_id AND NEW.auction_id = auction_id))=
        (SELECT id from "bid" WHERE value = (SELECT MAX(value) FROM "bid" WHERE NEW.auction_id = auction_id))) AND
        (SELECT id from "bid" WHERE value = (SELECT MAX(value) FROM "bid" WHERE NEW.user_id = user_id AND NEW.auction_id = auction_id)) IS NOT NULL

        THEN
           RAISE EXCEPTION 'A user cannot bid if his bid is the current highest.';
        END IF;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER check_ownership_bid
        BEFORE INSERT OR UPDATE ON "bid"
        FOR EACH ROW
        EXECUTE PROCEDURE check_ownership_bid();

----------------------------------------------------------------

CREATE FUNCTION check_if_can_rate() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF NOT EXISTS (SELECT * FROM "bid" WHERE NEW.rater_id = user_id AND auction_id in (SELECT id FROM "auction" WHERE seller_id = NEW.rated_id)) THEN
           RAISE EXCEPTION 'A user can only rate another user if the former bid on an auction created by the latter.';
        END IF;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER check_if_can_rate
        BEFORE INSERT OR UPDATE ON "rating"
        FOR EACH ROW
        EXECUTE PROCEDURE check_if_can_rate();

----------------------------------------------------------------

CREATE FUNCTION checks_bid_date() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF NEW.date < (SELECT start_date FROM "auction" WHERE NEW.auction_id = id AND start_date IS NOT NULL) 
        THEN RAISE EXCEPTION 'The date of a bid must be after the starting date of the auction the bid was made on.';
        END IF;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER checks_bid_date
        BEFORE INSERT OR UPDATE ON "bid"
        FOR EACH ROW
        EXECUTE PROCEDURE checks_bid_date();

-----------------------------------------------------------------

CREATE FUNCTION follow_after_bid() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF NOT EXISTS (
            SELECT * FROM "follow_auction"
            WHERE user_id = NEW.user_id AND auction_id = NEW.auction_id
        )
        THEN INSERT INTO "follow_auction"(auction_id, user_id) VALUES (NEW.auction_id, NEW.user_id);
	END IF;
        RETURN NEW;       
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER follow_after_bid
        AFTER INSERT OR UPDATE ON "bid"
        FOR EACH ROW
        EXECUTE PROCEDURE follow_after_bid();

----------------------------------------------------------------

CREATE FUNCTION checks_if_can_block() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF EXISTS (SELECT * FROM "block" WHERE NEW.user_id = user_id AND end_date IS NULL) 
        THEN RAISE EXCEPTION 'A user can not be associated to more than one block during the same time period.';
        END IF;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER checks_if_can_block
        BEFORE INSERT OR UPDATE ON "block"
        FOR EACH ROW
        EXECUTE PROCEDURE checks_if_can_block();

----------------------------------------------------------------

CREATE FUNCTION update_user_rating() RETURNS TRIGGER AS
$BODY$
BEGIN
        UPDATE "user" 
        SET rating = (SELECT AVG(score) FROM "rating" WHERE rated_id = NEW.rated_id)
        WHERE id = NEW.rated_id;
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER update_user_rating
        AFTER INSERT OR UPDATE ON "rating"
        FOR EACH ROW
        EXECUTE PROCEDURE update_user_rating();

----------------------------------------------------------------

CREATE FUNCTION delete_user_auction() RETURNS TRIGGER AS
$BODY$
BEGIN
        UPDATE "auction" SET end_date = NULL FROM "auction" WHERE OLD.id  = seller_id;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER delete_user_auction
        AFTER DELETE ON "user"
        FOR EACH ROW
        EXECUTE PROCEDURE delete_user_auction();

----------------------------------------------------------------

CREATE FUNCTION delete_auction() RETURNS TRIGGER AS
$BODY$
BEGIN
        IF EXISTS (SELECT * FROM "bid" WHERE OLD.id = auction_id) 
        THEN RAISE EXCEPTION 'An auction can only be deleted if no bids have been made.';
        END IF;        
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER delete_auction
        AFTER DELETE ON "auction"
        FOR EACH ROW
        EXECUTE PROCEDURE delete_auction();

----------------------------------------------------------------

CREATE FUNCTION update_auction_deadline_after_bid() RETURNS TRIGGER AS
$BODY$
BEGIN
        UPDATE "auction" SET end_date = end_date + interval '30 minutes' FROM "bid" WHERE NEW.auction_id  = "auction".id 
        AND NEW.date >= end_date - interval '15 minutes';
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER update_auction_deadline_after_bid
        AFTER INSERT OR UPDATE ON "bid"
        FOR EACH ROW
        EXECUTE PROCEDURE update_auction_deadline_after_bid();

----------------------------------------------------------------

CREATE FUNCTION update_credit_after_transaction() RETURNS TRIGGER AS
$BODY$
BEGIN
        UPDATE "user" SET credit = credit + NEW.value FROM "transaction" WHERE NEW.user_id  = "user".id AND NEW.status = 'Accepted';
        RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER update_credit_after_transaction
        AFTER INSERT OR UPDATE ON "transaction"
        FOR EACH ROW
        EXECUTE PROCEDURE update_credit_after_transaction();
