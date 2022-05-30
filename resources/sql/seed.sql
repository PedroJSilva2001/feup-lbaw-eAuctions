DROP SCHEMA IF EXISTS lbaw2114 CASCADE;
CREATE SCHEMA lbaw2114;
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



-----------------------------------------------------------------
--                          POPULATE 
-----------------------------------------------------------------

SET search_path TO lbaw2114;

INSERT INTO "user" (id, username, name, email, password, credit, picture, rating, isAdmin) VALUES
(1, 'wpietzner0', 'Wallie Pietzner', 'wpietzner0@livejournal.com', 'e8ZOUk6Pe5l', 4606.00, 'profile_pictures/1.png', 5.0, false),
(2, 'cfeltham1', 'Claudetta Feltham', 'cfeltham1@elegantthemes.com', 'Qj058xXwOU', 1529.00, 'profile_pictures/1.png', 4.0, false),
(3, 'aovanesian2', 'Ambros Ovanesian', 'aovanesian2@psu.edu', '$2y$10$q4z.Og0lKAqOBP7Bq0tLl.LAECXtIeSaLokw3sMm3XqtZ4AseYNPm', 6285.00, 'profile_pictures/1.png', 0.4, true),
(4, 'espoors3', 'Eveleen Spoors', 'espoors3@whitehouse.gov', 'QtYKk4', 3863.00, 'profile_pictures/1.png', 1.3, false),
(5, 'sguierre4', 'Silvan Guierre', 'sguierre4@usnews.com', 'Qi7snzG', 7437.00, null, 2.6, false),
(6, 'jharrold5', 'Juliane Harrold', 'jharrold5@multiply.com', 'UTvWMuTEJY', 70000.00, 'profile_pictures/1.png', 2.4, false),
(7, 'spoulden6', 'Susana Poulden', 'spoulden6@networkadvertising.org', 'sS4FASk', 1648.00, 'profile_pictures/1.png', 3.7, false),
(8, 'pjoynes7', 'Page Joynes', 'pjoynes7@51.la', 'nzKzXSij', 2944.00, 'profile_pictures/1.png', 3.6, false),
(9, 'gvanyukhin8', 'Giordano Vanyukhin', 'gvanyukhin8@homestead.com', '0wUqrvyd', 5286.00, 'profile_pictures/1.png',  4.7, false),
(10, 'abutson9', 'Ali Butson', 'abutson9@foxnews.com', 'kJSZvIVm', 800000.00, null, 2.3, false),
(11, 'tgeorgieva', 'Thacher Georgiev', 'tgeorgieva@yandex.ru', 'xvOV5eKAer', 323.00, null, 4.6, false),
(12, 'ptruittb', 'Pauly Truitt', 'ptruittb@japanpost.jp', 'fXre0lelQ', 80000.00, 'profile_pictures/1.png', 4.9, false),
(13, 'dmadinec', 'Dahlia Madine', 'dmadinec@wix.com', 'c7IBoOSeKX', 80000.00, 'profile_pictures/1.png', 0.0, false),
(14, 'channand', 'Cammy Hannan', 'channand@etsy.com', 'jzHgsdHDmdB',  80000.00, 'profile_pictures/1.png', 1.9, false),
(15, 'hbassinghame', 'Haslett Bassingham', 'hbassinghame@rambler.ru', 'tW6poR9nh', 80000.00, null, 2.8, false),
(16, 'cjeffsf', 'Caitlin Jeffs', 'cjeffsf@networkadvertising.org', 'czM61dV7', 80000.00, 'profile_pictures/1.png', 0.3, false),
(17, 'bforteg', 'Belia Forte', 'bforteg@linkedin.com', 'EJfAwC1y', 6570.00, null, 1.8, false),
(18, 'itrevillionh', 'Isidro Trevillion', 'itrevillionh@google.nl', 'OWafzvfR', 80000.00, 'profile_pictures/1.png', 4.3, false),
(19, 'emcfeatei', 'Emile McFeate', 'emcfeatei@1688.com', '1QyJ3t23o', 5402.00,  'profile_pictures/1.png', 3.0, false),
(20, 'wwesgatej', 'Westleigh Wesgate', 'wwesgatej@blogs.com', '5d0szNib', 8559.00, 'profile_pictures/1.png', 3.2, false),
(21, 'hbuckyk', 'Hurlee Bucky', 'hbuckyk@jiathis.com', 'pLuHMgRYPxFC', 80000.00, 'profile_pictures/1.png', 4.2, false),
(22, 'mwasmerl', 'Milzie Wasmer', 'mwasmerl@ed.gov', '9j9rEITe7zO', 1949.00,'profile_pictures/1.png', 0.6, false),
(23, 'jmaggiorim', 'Joane Maggiori', 'jmaggiorim@usda.gov', 'OfklCg9m', 339.00, 'profile_pictures/1.png', 2.4, false),
(24, 'aalgyn', 'Aldon Algy', 'aalgyn@symantec.com', 'bBYVfZ5oD',  8369.00, 'profile_pictures/1.png', 4.8, false),
(25, 'htryo', 'Helenelizabeth Try', 'htryo@xrea.com', 'w6eA8dHdFM', 2464.00, 'profile_pictures/1.png', 1.1, false),
(26, 'mkretchmerp', 'Micheil Kretchmer', 'mkretchmerp@odnoklassniki.ru', 'TDu2uK8WaYrp',  1086.00, null, 2.0, true),
(27, 'ofenderq', 'Orton Fender', 'ofenderq@fc2.com', 'upWYKEWz0w', 80000.00, null, 0.7, false),
(28, 'aelsonr', 'Abey Elson', 'aelsonr@un.org', '3Lfp6Jd1ulBs', 80000.00, null, 3.9, false),
(29, 'sfitzsimonss', 'Stavro Fitzsimons', 'sfitzsimonss@abc.net.au', 'C60LSfVmO3x', 80000.00, 'profile_pictures/1.png', 1.1, false),
(30, 'pbaltzart', 'Pail Baltzar', 'pbaltzart@yolasite.com', '2gd14vgDGBOx', 15.00, 'profile_pictures/1.png', 1.0, false),
(31, 'vconklinu', 'Valerye Conklin', 'vconklinu@google.com.au', 'bWM2E9',  80000.00, 'profile_pictures/1.png', 3.3, true),
(32, 'nkitchinghamv', 'Natalie Kitchingham', 'nkitchinghamv@zimbio.com', 'whu4oLifIF', 80000.00, 'profile_pictures/1.png', 0.5, false),
(33, 'jminerw', 'Jacqui Miner', 'jminerw@xing.com', '6fEwat', 80000.00, 'profile_pictures/1.png', 3.3, false),
(34, 'kdobrowlskix', 'Kele Dobrowlski', 'kdobrowlskix@psu.edu', 'DMEPiooG', 3953.00, null, 3.4, false),
(35, 'nfisbeyy', 'Norton Fisbey', 'nfisbeyy@hexun.com', 'pP7p4t', 80000.00, null, 3.0, false),
(36, 'pfalconerz', 'Pall Falconer', 'pfalconerz@nyu.edu', '4a2wqjmLkzsZ', 2740.00, null, 1.9, false),
(37, 'eskace10', 'Emalee Skace', 'eskace10@mit.edu', 'DTSxtI0', 5500.00, null, 1.7, false),
(38, 'ivon11', 'Inesita von Grollmann', 'ivon11@elpais.com', 'q7IV5L', 80000.00, 'profile_pictures/1.png', 3.3, false),
(39, 'uwilsey12', 'Ursula Wilsey', 'uwilsey12@npr.org', 'I3JjZ9x', 800000.00, 'profile_pictures/1.png', 3.9, false),
(40, 'jworsalls13', 'Jonah Worsalls', 'jworsalls13@moonfruit.com', 'ue6ytiH', 9641.1, 'profile_pictures/1.png', 1.4, false),
(41, 'user', 'user', 'user@gmail.com', '$2y$10$q4z.Og0lKAqOBP7Bq0tLl.LAECXtIeSaLokw3sMm3XqtZ4AseYNPm', 1000000000, 'profile_pictures/1.png', 0.0, false),
(42, 'admin', 'admin', 'admin@gmail.com', '$2y$10$q4z.Og0lKAqOBP7Bq0tLl.LAECXtIeSaLokw3sMm3XqtZ4AseYNPm', 1000000000, 'profile_pictures/1.png', 0.0, true);

SELECT pg_catalog.setval(pg_get_serial_sequence('user', 'id'), (SELECT MAX(id) FROM "user"));

INSERT INTO "auction" (id, seller_id, title, description, brand, colour, condition, year, start_date, end_date, base_value) VALUES
(1, 9, 'Iron Maiden - Senjutsu - 3xLP Album (Triple album), Limited edition - 2021/2021', ' Iron Maiden album. Trifold in high gloss sleeve with printed inner gutters featuring 3 printed inner sleeves. Which one side presents a high gloss finish and the other, standard gloss, and is completed with color disc labels. ', 
    'Iron Maiden', 'Black', 'Mint', '2021', '2021-11-30T00:00:00Z', '2022-05-06T02:14:27Z', 40.00),
(2, 9, 'LEGO - Policeman - Big Minifigure', 'Original LEGO product. The articulation of the head, arms, hands and legs is identical to the the normal sized LEGO Minifigures. It can serve as a night light, torch flash light or as a lamp. The lot will be sent via registered mail.', 
    'LEGO', null , 'Reasonable', '2004', '2021-12-01T23:00:00Z', '2022-01-31T23:30:00Z', 20.00),
(3, 7, 'Chanel Coat', 'Vintage Chanel coat. Sheep whool interior.',
     'Chanel','Brown', 'Poor', '1998', null, '2022-12-28T09:45:00Z', 200.00),
(4, 3, 'Spain 1936 - 30 cts blue. Coat of arms of Spain. Granada. Type 4 of the report block. - Edifil 801', '1936 Edifil 801, 30 cts blue stamp. Never hinged stamp. Shipped by registered mail. Please examine the photos.', 
    'Edifil', 'Blue', 'New', '1936', '2021-12-23T00:03:00Z', '2022-01-30T02:20:27Z', 120.00),
(5, 31, 'Nikon J1 + 30-110mm + Scheda 2GB + Borsa', 'Nikon 1: a smart and extremely compact lens. Equipped with a dedicated range of interchangeable compact lenses 1 Nikkor. Designed to offer new levels of speed, simplicity and fun for capturing the world in your own way. CMOS image sensor in CX format. Ultra high-speed autofocus system. EXPEED 3 image processor. Incredibly fast burst shooting mode. Autofocus lenses with image stabiliser.', 
    'Nikon', 'Black', 'Mint', '2014', '2021-12-15T05:10:00Z', '2021-12-20T02:20:27Z', 300.00),
(6, 25, 'Lorenzo Quinn - Después del amor', 'A beautiful art piece by Lorenzo Quinn about the long lasting love after a burning passion. Shipped by mail.', 
    'Luxury&Collections', 'Grey', 'New', '2000', '2021-11-15T00:00:00Z', '2022-02-14T23:59:59Z', 1350.00), 
(9, 40, 'Unknown - Violin - Germany', 'German 4/4 violin, built in Markneukirchen, built in the 1950s. The violin has no (repaired) cracks, only paint damage on the back (see photo).',
     null, 'Brown', 'Poor', '1950', null, '2022-01-11T16:45:00Z', 200.00),
(10, 2, 'BECKER FORGERIES (Pb/St restrikes, ca 1911-1914) - Roman Empire. Magnia Urbica', 'Karl Wilhelm Becker, born in 1772, is probably one of the best known forgers of ancient, medieval and early modern coins. He was concerned with coins as early as 1796, and from 1806 at the latest - he initially worked as an art and antiques dealer in Munich but, at that time, he was working as a goldsmith in Mannheim - he copied ancient coins. Not by casting, but by cutting new dies. Initially Becker forged Greek coins, but later he also created numerous copies of Roman, medieval and modern coins which were sold to his wealthy clientele. Although being accused of forgery from time to time - soon some numismatists/archaeologists were aware of the fact that Becker also sold copies - it never led to a conviction; Becker claimed that his “coins” were “instructive” in nature and not sold with the intention to deceive. Becker died in April 1830, after which his family started using the dies made by Becker to strike coins in a pewter alloy. Later these dies were sold to the Saalfeld Museum and in 1911 they were added to the collection of the Kaiser-Friedrich Museum in Berlin; the Berlin museum actually used the dies again to strike off-metal sets of the “coins”, such as the examples included in this themed auction. In ‘Becker the Counterfeiter’ (1924) British numismatist George F. Hill describes the various types of fake coins created by Becker - 331 die pairs are known, of which 133 copying Greek coins and 136 copying Roman coins - and he notes the following: “some of his efforts are of course wide of the mark; but others are as near to the original as anything that his successors have produced,”. ',
     'Becker Forgeries', 'Grey', 'Reasonable', null, '2021-10-01T10:45:00Z', '2022-01-29T15:45:00Z', 5.00),
(11, 16, 'Hermès - Birkin 35 Handbag', 'Titled “The Time Ahead”, the campaign consists of new and re-issued colours. Take a glimpse at Hermes’s perspective of the world where a beige tone, Argile, which means “clay” in French, is unveiled for this collection. The bag is made out of Tadelakt leather, which is extremely smooth to the touch. The guilloche textured engraved palladium hardware make this Birkin extra special.',
     'Hermés', 'Beige', 'New', '2013' , '2021-12-01T11:15:20Z', '2022-06-15T20:00:00Z', 20000.00),
(12, 22, 'Didier Bizet - Ma mère et ses amies', 'The day after his mother died, Didier Bizet takes on the task of paying tribute to her so that he never has to bury her. The silver prints and Kodachrome® slides are previous material. They reassure us in our perpetual quest for identity. This is how the photographer began a long work by trying to photograph her funeral, on that day in June. Then, he dug into his family’s photographic archives searching for a past that is a mystery to him. Stacks of boxes filled with carefully dated and arranged slides, yellowed envelopes of prints from France at the time of President Albert Lebrun. Some memories resurfaced and then disappeared, blurring and ending to turn his memory upside down.',
     'Galerie Taylor', null , 'Mint', '1956', '2021-12-21T11:00:20Z', '2022-03-10T09:30:00Z', 500.00),
(13, 32, 'Trix H0 - 22499 - Steam locomotive with tender - A1 "Bavaria" - K.Bay.Sts.B', 'Made in Nürnberg, Made from Metal, Exclusive and high Price Quality Trix H0, Length ca 13 cm , width ca 3,5 cm, height ca 5 cm, Weight ca 173 grams, Hallmarked / Design : Bayrische Dampflok Bavaria A1 ( Steam train lok ) EAN 4007864224999, Great workmanship, many valuable Details !, Perfect Gift for a sophisticated business men or women with style for yourself , perfect highlight for your collection or Office',
     'Trix', null , 'New', '1990', '2021-11-12T11:55:00Z', '2022-01-27T23:30:00Z', 8000.00),
(16, 37, 'Ford USA - Mustang Convertible', 'A fully restored 1966 Ford Mustang Convertible. This Mustang needs to be finished, the front seats and rear seat are NOT present with the car! Furthermore, the car is 90% complete in parts! The car can be viewed and picked up in Musselkanaal, the Netherlands. Viewing is possible Monday through Saturday from 8:30 to 17:00! The photos are part of the description, so please take a good look at them! It is recommended to view the car prior to placing a bid. The vehicle must be picked up within 4 weeks after the closing date of the auction, storage costs will be charged after this period.',
     'Ford','Blue', 'Reasonable', '1966','2021-11-30T00:00:15Z', '2021-12-12T13:00:15Z', 100000.00),     
(18, 35, 'Lionello Spada, detto Scimmia del Caravaggio (1576-1622), after - San Pietro liberato dall angelo', 'This large canvas dating back to the second half of the seventeenth century, is taken from the canvas of the same subject - typically Caravaggesque - by Lionello Spada (1576-1622) now kept at the National Gallery of Parma. The canvas is in good overall condition, there are restored colour losses, reversible. According to Christian tradition, the ancient Tullianum (or Mamertino) prison in the Roman Forum, below the church of San Giuseppe dei Falegnami, was the place of detention of the apostle Peter.',
     'Art E', null, 'Mint', '1650','2021-11-01T00:00:00Z', '2022-05-01T15:50:23Z', 5000.00),
(19, 24, 'U2 - The Joshua Tree - Official Sales Award - Presented to U2 - Official In-House award', 'Original Paltinum Island Album Records. Official Music Sales Awards from the USA . Presented to U2 for 1.000.000 sales. Great Award . Very rare official Sales Award.',
     'The Joshua Tree', null, 'New', '1987','2021-12-02T16:25:05Z', '2021-12-15T03:00:00Z', 120.00),
(21, 7, '4-Track Reel To Reel - Tape Deck', 'The TEAC X-1000 is a 3-head RTR tape recorder with 4-track, 2-channel stereo or mono system.Full serviced, adjusted to factory settings with professional tape. New capstan belt. This recorder sounds superb!  The lot will be sent via registered mail.', 
    'TEAC', 'Grey' , 'Poor', '1990', '2021-12-11T23:00:00Z', '2022-01-31T12:30:00Z', 600.00),
(22, 32, 'Quadro In rilievo Ferrari', 'Original Ferrari picture with embossed aluminium logo, wooden frame with aluminium effect. The picture was intended for the offices of Ferrari managers/executives. New, never hung. Shipping costs include transport insurance cost and the proper packaging material to protect the item during transport.', 
    'Ferrari', 'Black' , 'New', '2000', '2021-12-18T11:00:00Z', '2022-02-01T20:30:00Z', 300.00),
(23, 16, 'Nintendo Super Nintendo - Super Nes - Console with 6 Video Games', 'Supernes console with 6 video games, all tested and working. 3 games only cartridge, 3 box games without instructions. The boxes as shown in the photo are broken and ruined', 
    'Nintendo', null , 'Poor', '1992', '2021-12-21T12:00:00Z', '2022-02-11T23:30:00Z', 160.00),
(24, 2, 'Hulk, Spawn - The incredible Hulk #340 + The Incredulous Spawn #226', 'Key Incredible Hulk #340 and Parody Spawn #226. - Todd McFarlane covers - Stapled - First edition.', 
    'Marvel', null , 'Mint', '1962', '2021-12-01T23:00:00Z', '2022-01-31T23:30:00Z', 200.00);

INSERT INTO "auction" (id, seller_id, title, description, brand, colour, condition, year, start_date, end_date, base_value, type) VALUES   
(7, 14, 'Sign - Harley Davidson. Fat Boy. - Ande Rooney.', 'Famous logo of Harley Davidson. Fat Boy. Beautiful old enamel advertising sign by Ande Rooney 1991. Dimensions: Diameter Approximately 28.5 cm. Rare sign.', 
    'Harley Davidson', 'Blue', 'Reasonable', '1991', '2021-11-30T14:00:00Z', '2022-03-01T14:00:00Z', 4000.00, 'Private'),    
(8, 20, 'Lacépède / Desmarest - Oeuvres du Comte de Lacépède', 'Complete in five text volumes and one atlas. The first volume concerns "cétacés" and "quadrupèdes ovipares" [1833], the second "quadrupèdes ovipares" [1834] and "serpents", the third and fourth "poissons" [1835] and the fifth volume "élements des sciences naturelles" [1834] and is illustrated with anatomical black outline plates. The atlas volume [1836] contains all 118 hand coloured illustrations of whales, reptiles and fishes. ', 
    'Bruxelles Editions', null , 'Mint', '1836', '2021-12-25T21:30:22Z', '2022-05-01T00:00:00Z', 500.00, 'Private'),    
(14, 32, 'The State Democratic Executive Committee - [JFK Assassination Day] Original John F. Kennedy Texas Welcome Dinner Invitation ', 'This invitation issued by "The State Democratic Executive Committee" to the guests for attending the US President John F. Kennedy & US Vice President Lyndon B. Johnson Texas Welcome Dinner. It scheduled to occur on the night of Kennedy assassination, this dinner would never take place. 22nd November 1963 was the day of US President John F. Kennedy assassination,',
     'The State Democratic Executive Committee', null , 'New', '1963' , '2021-12-21T10:23:10Z', '2022-01-30T14:25:10Z', 600.00, 'Private'),
(15, 17, 'Christian Louboutin - Pumps - Size: Shoes / EU 37', 'Christian Louboutin shoes, never used, with dust bag. Shipping paid by the seller.',
     'Louboutin','Red', 'New', '2004', null, '2022-02-14T00:00:00Z', 5000.00, 'Private'),
(17, 5, 'Apple - Macintosh PLUS 1MB signed by “Steve Jobs”, RARE Software & Jobs Pixelart - With replacement box', 'Being an Apple enthusiast, I have collected RARE and VALUABLE hardware and software for my co-sentimental Apple companions. The lucky Macintosh Apple enthusiast can supplement his/her collection with this legendary collector item: the “Macintosh PLUS”.UNIQUE to this particular computer are “the engraved signatures” of the original Macintosh team - including the signature of “STEVE JOBS” - inside the CASE. The Macintosh team found the original Macintosh ART - and they saw themselves as ARTISTS. When you open “the case”, you understand why. This is the third model in the Macintosh line, introduced January 16, 1986 - two years after the original Macintosh (1984) and one year after the Macintosh 512K. Price tag at the time was US $ 2599 (equivalent of $ 6,062 in 2020).',
     'Apple','White', 'Mint', '1986','2021-12-15T19:42:22Z', '2022-04-15T19:00:00Z', 250.00, 'Private'),
(20, 24, 'Maino - Condorino - Road bicycle', 'Maino sports bicycle, 28" wheels, preserved in fair condition, with 3-speed Simplex gearbox. With some rust spots. It’s been standing still for some time, but is in overall working condition.',
     'Maino','Red', 'Reasonable', '1970', '2021-11-04T22:45:00Z', '2022-08-25T02:35:40Z', 375.00, 'Private');

SELECT pg_catalog.setval(pg_get_serial_sequence('auction', 'id'), (SELECT MAX(id) FROM "auction"));

INSERT INTO "category_auction" (auction_id, category) VALUES
(1, 'Music'),
(2, 'Toys'),
(3, 'Fashion'),
(4, 'Coins & Stamps'),
(5, 'Technology'),
(6, 'Art'),
(7, 'Automobilia'),
(8, 'Books'),
(9, 'Music'),
(10, 'Coins & Stamps'),
(11, 'Fashion'),
(12, 'Art'),
(13, 'Toys'),
(14, 'Books'),
(15, 'Fashion'),
(16, 'Automobilia'),
(17, 'Technology'), 
(18, 'Art'),
(19, 'Music'),
(20, 'Automobilia'),
(21, 'Music'),
(22, 'Automobilia'),
(22, 'Art'),
(23, 'Technology'),
(24, 'Books');


INSERT INTO "image" (id, path, auction_id) VALUES
(1, 'auction_pictures/1/1.jpg', 1), 
(2, 'auction_pictures/2/1.jpg', 2), 
(3, 'auction_pictures/3/1.jpg', 3), 
(4, 'auction_pictures/4/1.jpg', 4), (5, 'auction_pictures/4/2.jpg', 4),  
(6, 'auction_pictures/5/1.jpg', 5),  
(7, 'auction_pictures/6/1.jpg', 6), (8, 'auction_pictures/6/2.jpg', 6), (9, 'auction_pictures/6/3.jpg', 6),   
(10, 'auction_pictures/7/1.jpg', 7),
(11, 'auction_pictures/8/1.jpg', 8),
(12, 'auction_pictures/9/1.jpg', 9),
(13, 'auction_pictures/10/1.jpg', 10), (14, 'auction_pictures/10/2.jpg', 10),
(15, 'auction_pictures/11/1.jpg', 11),
(16, 'auction_pictures/12/1.jpg', 12),
(17, 'auction_pictures/13/1.jpg', 13),
(18, 'auction_pictures/14/1.jpg', 14),
(19, 'auction_pictures/15/1.jpg', 15),
(20, 'auction_pictures/16/1.jpg', 16), (21, 'auction_pictures/16/2.jpg', 16), (22, 'auction_pictures/16/3.jpg', 16), (23, 'auction_pictures/16/4.jpg', 16),
(24, 'auction_pictures/17/1.jpg', 17),
(25, 'auction_pictures/18/1.jpg', 18),
(26, 'auction_pictures/19/1.jpg', 19),
(27, 'auction_pictures/20/1.jpg', 20),
(28, 'auction_pictures/21/1.jpg', 21),
(29, 'auction_pictures/22/1.jpg', 22),
(30, 'auction_pictures/23/1.jpg', 23),
(31, 'auction_pictures/24/1.jpg', 24);

SELECT pg_catalog.setval(pg_get_serial_sequence('image', 'id'), (SELECT MAX(id) FROM "image"));

INSERT INTO "follow_auction" (auction_id, user_id) VALUES
(1, 12), (1, 18),(1, 34),
(2, 13), (2, 16), (2, 28), (2, 34), (2, 35), 
(4, 15), (4, 18),
(6, 6), (6, 14), (6, 31), 
(7, 1), (7, 2), (7, 6), (7, 11), (7, 18), (7, 29), 
(8, 21), (8, 27), 
(11, 5), (11, 17), 
(12, 6), (12, 12),
(13, 1), (13, 10), (13, 34), 
(14, 3), (14, 38),  
(16, 9), (16, 10), (16, 16), (16, 39), 
(17, 36), (17, 40), 
(18, 27), 
(19, 6), (19, 32), 
(20, 4), (20, 33);


INSERT INTO "bid" (id, user_id, auction_id, value, date) VALUES
(1, 12, 1, 50.00, '2021-11-30T05:00:00Z'),
(2, 18, 1, 83.00, '2021-11-30T10:00:00Z'),
(3, 12, 1, 100.00, '2021-11-30T23:00:00Z'), 
(4, 13, 2, 21.00, '2021-12-01T23:30:00Z'),
(5, 28, 2, 30.00, '2021-12-03T05:30:19Z'),
(6, 16, 2, 52.00, '2021-12-06T10:00:20Z'),
(7, 28, 2, 60.00, '2021-12-29T15:24:32Z'),
(8, 35, 2, 62.00, '2021-12-29T21:41:54Z'),
(9, 15, 4, 200.00, '2021-12-27T00:04:17Z'),
(10, 31, 6, 2100.00, '2021-11-21T10:49:12Z'),
(11, 14, 6, 4500.00, '2021-12-14T16:15:20Z' ),
(12, 27, 8, 780.00, '2021-12-30T17:24:21Z'),
(13, 12, 12, 501.00, '2021-12-29T17:33:16Z'),
(14, 6, 12, 520.00, '2021-12-30T12:23:13Z'),
(15, 38, 14, 780.00, '2021-12-22T20:44:50Z'),
(16, 10, 16, 200000.00, '2021-12-01T08:06:47Z'),
(17, 39, 16, 210000.00, '2021-12-05T23:52:56Z'),
(18, 10, 16, 300000.00, '2021-12-11T13:55:25Z'),
(19, 32, 19, 250.00, '2021-12-13T09:12:35Z'),
(20, 6, 19, 300.00, '2021-12-14T14:32:11Z'),
(21, 21, 20, 400.00, '2021-12-07T15:23:44Z'),
(22, 33, 20, 535.00, '2021-12-15T11:11:11Z');

SELECT pg_catalog.setval(pg_get_serial_sequence('bid', 'id'), (SELECT MAX(id) FROM "bid"));

INSERT INTO "comment" (id, user_id, auction_id, message, date) VALUES 
(1, 12, 1, 'Such a rare find!', '2021-11-30T05:10:14Z'),
(2, 12, 1, 'Hope I can get this album :)', '2021-12-03T14:00:32Z'),
(3, 16, 2, 'Just the LEGO I needed to complete my collection!', '2021-12-06T10:03:11Z'),
(4, 35, 2, 'My son will love this.', '2022-01-01T22:01:13Z'),
(5, 14, 6, 'Can the statue be picked up at a location instead of receiving it by mail?', '2022-02-14T20:30:24Z' ),
(6, 25, 6, 'No, the statue can only be sent by mail. Best Regards.', '2022-02-14T20:35:43Z'),
(7, 14, 6, 'Thanks for the fast reply.', '2022-02-14T20:40:55Z' ),
(8, 12, 12, 'I have been looking everywhere for this photograph. Hope I get it.', '2021-12-29T17:34:34Z'),
(9, 38, 14, 'Thank so much for inviting me! Such an important piece of history.', '2022-01-27T20:50:11Z'),
(10, 39, 16, 'This auction is in the bag.', '2021-12-10T14:12:10Z'),
(11, 10, 16, 'Going crazy for this car, but it is absolutely worth it.', '2021-12-11T13:56:45Z'),
(12, 32, 19, 'I love U2.', '2022-01-13T19:20:44Z'),
(13, 33, 20, 'This was exactly what I was looking for, I am so lucky you invited me for this auction.', '2022-07-15T12:11:14Z'),
(14, 33, 20, 'Just hoping no one else bids on this.', '2022-07-15T12:12:41Z'),
(15, 24, 20, 'Your welcome and best of luck to get my product!', '2022-07-20T14:52:31Z');

SELECT pg_catalog.setval(pg_get_serial_sequence('comment', 'id'), (SELECT MAX(id) FROM "comment"));

INSERT INTO "rating" (rater_id, rated_id, score) VALUES 
(6, 22, 4), (6, 24, 3),
(10, 37, 1),
(12, 9, 4), (12, 22, 5),
(13, 9, 2),
(14, 25, 3), 
(15, 3, 1),
(16, 9, 2),
(18, 9, 3),
(21, 24, 4),
(27, 20, 4), 
(28, 9, 3),
(31, 25, 4),
(32, 24, 4),
(33, 24, 3),
(35, 9, 3),
(38, 32, 1),
(39, 37, 4);

INSERT INTO "block" (id, admin_id, user_id, description, end_date) VALUES
(1, 3, 1, 'Bot Account.', null),
(2, 3, 8, 'Hacked Credit.', null);

INSERT INTO "block" (id, admin_id, user_id, description, start_date, end_date) VALUES
(3, 26, 7, 'Selling Stolen Product.', '2022-01-01T14:52:47Z', '2050-01-01T00:00:00Z'),
(4, 26, 21, 'Inappropriate Comment.', '2022-01-23T23:51:56Z', '2022-09-01T00:00:00Z'),
(5, 31, 23, 'Inappropriate Comment.', '2022-05-10-T02:01:14', '2022-11-10-T00:00:00');

SELECT pg_catalog.setval(pg_get_serial_sequence('block', 'id'), (SELECT MAX(id) FROM "block"));

INSERT INTO "notification" (id, user_id, auction_id, bid_id, date, type) VALUES
(1, 12, null, 3, '2021-11-30T10:00:00Z', 'New Bid'),
(3, 18, null, 2, '2021-11-30T23:00:00Z', 'New Bid'),
(4, 9, null, 1, '2021-11-30T05:00:00Z', 'New Bid'),
(5, 9, null, 3 ,'2021-11-30T10:00:00Z', 'New Bid'),
(6, 9, null, 2, '2021-11-30T23:00:00Z', 'New Bid'),
(7, 1, 13, null, '2022-07-17T21:30:00Z', 'Auction Ending Aproaching'),
(8, 10, 13, null, '2022-07-17T21:30:00Z', 'Auction Ending Aproaching'),
(9, 34, 13, null, '2022-07-17T21:30:00Z', 'Auction Ending Aproaching'),
(10, 9, 16, null,'2021-12-12T10:00:00Z', 'Auction Ending Aproaching'),
(11, 10, 16, null,'2021-12-12T10:00:00Z', 'Auction Ending Aproaching'),
(12, 16, 16, null,'2021-12-12T10:00:00Z', 'Auction Ending Aproaching'),
(13, 39, 16, null,'2021-12-12T10:00:00Z', 'Auction Ending Aproaching'),
(14, 9, 16, null,'2021-12-12T13:00:15Z', 'Auction Ended'),
(15, 10, 16, null,'2021-12-12T13:00:15Z', 'Auction Ended'),
(16, 16, 16, null,'2021-12-12T13:00:15Z', 'Auction Ended'),
(17, 39, 16, null,'2021-12-12T13:00:15Z', 'Auction Ended'),
(18, 10, 16, null,'2021-12-12T13:00:15Z', 'Winning Bid'),
(19, 27, 18, null, '2022-04-22T15:00:30Z','Auction Cancelled'),
(20, 36, 17, null, '2022-04-22T15:00:30Z','Auction Cancelled'),
(21, 40, 17, null, '2022-04-22T15:00:30Z','Auction Cancelled'),
(22, 6, 19, null,'2022-02-25T03:00:00Z', 'Auction Ended'),
(23, 32, 19, null,'2022-02-25T03:00:00Z', 'Auction Ended'),
(24, 6, 19, null,'2022-02-25T03:00:00Z', 'Winning Bid');

SELECT pg_catalog.setval(pg_get_serial_sequence('notification', 'id'), (SELECT MAX(id) FROM "notification"));

INSERT INTO "transaction" (id, user_id, value, description, date, method, status) VALUES 
(1, 10, 300.00, 'Add Credit to ', '2021-12-10T04:50:21Z', 'Transfer', 'Accepted'),
(2, 10, -300.00, 'Take Credit From ', '2021-12-12T13:00:30Z' , 'Transfer', 'Pending'),
(3, 37, 300000.00, 'Add Credit to ', '2021-12-12T13:00:50Z', 'Transfer', 'Pending'),
(4, 6, -300.00, 'Take Credit From ', '2021-12-25T03:01:10Z' , 'PayPal', 'Accepted'),
(5, 24, 300.00, 'Add Credit to ', '2021-12-10T13:01:50Z', 'Transfer', 'Declined'),
(6, 11, -100.00, 'Take Credit From ', '2021-12-06-T16:42:05Z', 'PayPal', 'Accepted'),
(7, 12, 550.0, 'Add Credit to ', '2021-11-30T09:37:22Z', 'Transfer', 'Pending'),
(8, 42, -500000.00, 'Take Credit From ', '2021-11-30T09:37:22Z', 'Transfer', 'Pending');

SELECT pg_catalog.setval(pg_get_serial_sequence('transaction', 'id'), (SELECT MAX(id) FROM "transaction"));
