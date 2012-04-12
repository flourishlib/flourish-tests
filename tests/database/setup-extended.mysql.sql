CREATE TABLE user_details (
	user_id INTEGER PRIMARY KEY,
	photo VARCHAR(255) NOT NULL DEFAULT '',
	FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE other_user_details (
	id INTEGER PRIMARY KEY,
	avatar VARCHAR(255) NOT NULL DEFAULT '',
	FOREIGN KEY (id) REFERENCES users(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE record_labels (
	name VARCHAR(255) PRIMARY KEY
)ENGINE=InnoDB;

CREATE TABLE record_deals (
	record_label VARCHAR(255) NOT NULL,
	artist_id INTEGER NOT NULL REFERENCES artists(artist_id) ON DELETE CASCADE,
	FOREIGN KEY (record_label)  REFERENCES record_labels(name) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (record_label, artist_id)
)ENGINE=InnoDB;

CREATE TABLE favorite_albums (
	email VARCHAR(200) NOT NULL,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL,
	UNIQUE (email, position),
	FOREIGN KEY (email) REFERENCES users(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (email, album_id)
)ENGINE=InnoDB;

CREATE TABLE year_favorite_albums (
	email VARCHAR(200) NOT NULL,
	year INTEGER NOT NULL,
	album_id INTEGER NOT NULL REFERENCES albums(album_id) ON DELETE CASCADE,
	position INTEGER NOT NULL,
	UNIQUE (email, year, position),
	FOREIGN KEY (email) REFERENCES users(email_address) ON UPDATE CASCADE ON DELETE CASCADE,
	PRIMARY KEY (email, year, album_id)
)ENGINE=InnoDB;

CREATE TABLE top_albums (
	top_album_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	album_id INTEGER NOT NULL UNIQUE,
	position INTEGER NOT NULL UNIQUE,
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE invalid_tables (
	not_primary_key VARCHAR(200)
)ENGINE=InnoDB;

CREATE TABLE event_slots (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL
)ENGINE=InnoDB;

CREATE TABLE events (
	event_id INTEGER PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE,
	event_slot_id INTEGER UNIQUE,
	registration_url VARCHAR(255) NOT NULL DEFAULT '',
	FOREIGN KEY (event_slot_id) REFERENCES event_slots(id) ON DELETE SET NULL
)ENGINE=InnoDB;

CREATE TABLE registrations (
	event_id INTEGER NOT NULL,
	name VARCHAR(255) NOT NULL,
	PRIMARY KEY(event_id, name),
	FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE RESTRICT
)ENGINE=InnoDB;

CREATE TABLE event_details (
	event_id INTEGER NOT NULL PRIMARY KEY,
	allows_registration BOOLEAN NOT NULL,
	FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE RESTRICT
)ENGINE=InnoDB;

CREATE TABLE events_artists (
	event_id INTEGER NOT NULL,
	artist_id INTEGER NOT NULL,
	PRIMARY KEY(event_id, artist_id),
	FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE RESTRICT,
	FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE certification_levels (
	name VARCHAR(200) PRIMARY KEY
)ENGINE=InnoDB;

CREATE TABLE certifications (
	level VARCHAR(200) NOT NULL,
	album_id INTEGER NOT NULL,
	year INTEGER NOT NULL,
	PRIMARY KEY (album_id, level),
	FOREIGN KEY (level) REFERENCES certification_levels(name) ON DELETE CASCADE,
	FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE
)ENGINE=InnoDB;

BEGIN;

INSERT INTO user_details (user_id, photo) VALUES (1, 'will.png');
INSERT INTO user_details (user_id, photo) VALUES (2, 'john.jpg');
INSERT INTO user_details (user_id, photo) VALUES (3, 'foo.gif');
INSERT INTO user_details (user_id, photo) VALUES (4, 'bar.gif');

INSERT INTO record_labels (name) VALUES ('EMI');
INSERT INTO record_labels (name) VALUES ('Sony Music Entertainment');

INSERT INTO record_deals (record_label, artist_id) VALUES ('EMI', 1);
INSERT INTO record_deals (record_label, artist_id) VALUES ('Sony Music Entertainment', 2);

INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 2, 1);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 1, 2);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 3, 3);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 7, 4);
INSERT INTO favorite_albums (email, album_id, position) VALUES ('will@flourishlib.com', 4, 5);

INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 2, 1);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 1, 2);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 3, 3);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 7, 4);
INSERT INTO year_favorite_albums (email, year, album_id, position) VALUES ('will@flourishlib.com', 2009, 4, 5);

INSERT INTO favorite_albums (email, album_id, position) VALUES ('john@smith.com', 2, 1);

INSERT INTO events (title, start_date, end_date) VALUES ('First Event',   '2008-01-01', '2008-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Second Event',  '2008-02-01', '2008-02-08');
INSERT INTO events (title, start_date, end_date) VALUES ('Third Event',   '2008-02-01', '2008-02-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Fourth Event',  '2009-01-01', '2010-01-01');
INSERT INTO events (title, start_date, end_date) VALUES ('Fifth Event',   '2005-06-03', '2008-06-02');
INSERT INTO events (title, start_date, end_date) VALUES ('Sixth Event',   '2009-05-29', '2009-05-30');
INSERT INTO events (title, start_date, end_date) VALUES ('Seventh Event', '2008-01-02', '2008-01-03');
INSERT INTO events (title, start_date, end_date) VALUES ('Eight Event',   '2008-01-01', NULL);
INSERT INTO events (title, start_date, end_date) VALUES ('Ninth Event',   '2008-02-02', NULL); 

INSERT INTO top_albums (album_id, position) VALUES (1, 1);
INSERT INTO top_albums (album_id, position) VALUES (4, 2);
INSERT INTO top_albums (album_id, position) VALUES (5, 3);
INSERT INTO top_albums (album_id, position) VALUES (6, 4);
INSERT INTO top_albums (album_id, position) VALUES (2, 5);
INSERT INTO top_albums (album_id, position) VALUES (3, 6);



COMMIT;
