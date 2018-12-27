CREATE TABLE accounts (
	accounts_id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	first_name	TEXT NOT NULL,
	last_name	TEXT NOT NULL,
	email	TEXT NOT NULL UNIQUE,
	password	TEXT NOT NULL,
	admin_confirm TEXT,
	session	TEXT UNIQUE
);

INSERT INTO accounts (accounts_id, first_name, last_name, email, password, admin_confirm)
VALUES (1,'Tom','Dilliplane', 'csaccalendar@gmail.com',
	 '$2y$10$vAk0FYxyxQAXF4exAAOWE.RyptwGhvdAqFUbEyeZ0.a3LT/e/VY8K',
	 '$2y$10$q/eOrPGFU9rpuwc2UHcYVuh7NvDJj.vPgxKYEbTyvjc04.BNX5V.e'); /* password: think45 admin_confirm: CsacGymIthaca1010 */
	 INSERT INTO accounts (accounts_id, first_name, last_name, email, password, admin_confirm)
	 VALUES (2,'David','Ticzon', 'david@gmail.com',
	 	 '$2y$10$K5qpHVLa2oRUKaM/JQhgOeNM1cUjbJVzD6WSMTgSNcsoPwmpYxapq',
	 	 ''); /* password: boolean10*/


CREATE TABLE reviews (
	reviews_id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	first_name	TEXT NOT NULL,
	content	TEXT NOT NULL,
	rating	INTEGER NOT NULL
);

INSERT INTO reviews (reviews_id, first_name, content, rating) VALUES (1,'Tom', 'Great gym! Had a lot of fun getting in shape with Tom.',5);
INSERT INTO reviews (reviews_id, first_name, content, rating) VALUES (2,'David', 'Nice people to be with. Clean and nice location in Ithaca.',5);
INSERT INTO reviews (reviews_id, first_name, content, rating) VALUES (3,'Kyle', 'Love coming to work out everyday at this gym.',5);

CREATE TABLE items (
	items_id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	item_name TEXT NOT NULL,
	file_name TEXT NOT NULL,
	file_ext TEXT NOT NULL,
	price	INTEGER NOT NULL
);

INSERT INTO items (items_id,item_name,file_name,file_ext,price) VALUES (1, 'T-Shirt','1', 'jpg', '10');
INSERT INTO items (items_id,item_name,file_name,file_ext,price) VALUES (2, 'Hoodie', '2', 'jpg', '40');
INSERT INTO items (items_id,item_name,file_name,file_ext,price) VALUES (3, 'Tank Top','3', 'jpg', '15');

CREATE TABLE appointments (
	appointments_id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	user_id	INTEGER NOT NULL,
	date_time TEXT NOT NULL,
	details TEXT NOT NULL
);

CREATE TABLE staff (
	staff_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	staff_name TEXT NOT NULL,
	staff_picture_name TEXT NOT NULL UNIQUE,
	staff_picture_ext TEXT NOT NULL,
	staff_bio TEXT NOT NULL
);

INSERT INTO staff (staff_id, staff_name, staff_picture_name, staff_picture_ext, staff_bio)
	VALUES(1, 'Tom Dilliplane', 'tom_dilliplane', 'jpg', "The owner of Cayuga Strength and Conditioning is Tom Dilliplane.
		Dilliplane served as the interim head strength and conditioning coach at
		the University of Akron, where he worked with all of its athletic teams.

		Dilliplane earned his master's degree at Akron in 1994 in athletic
		training/exercise physiology and earned his bachelor's degree at the
		University of Buffalo in health sciences and exercise physiology.

		Dilliplane and his wife, Melanie, the assistant gymnastics coach at Cornell,
		have two daughters, Maren and Eva. Tom Dilliplane begins his eighth year
		as a full-time assistant strength and conditioning coach at Cornell after
		serving as a volunteer for the previous three years. Dilliplane worked as
		a fitness coordinator for the Cornell Fitness Centers for five years before
		accepting his current position with varsity athletics.");


/*Content management system*/
CREATE TABLE cms(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	content TEXT NOT NULL,
	name TEXT NOT NULL UNIQUE
);
/**INDEX **/
INSERT INTO cms (content, name)
	VALUES('"Train each day like an olympian."', "philosophy");
INSERT INTO cms (content, name)
	VALUES("145 Yaple Rd", "street_addr");
INSERT INTO cms(content, name)
	VALUES("Ithaca, NY 14850", "city_state");
INSERT INTO cms(content, name)
		VALUES("Always Open", "hours_of_operation");

/**ABOUT**/
INSERT INTO cms(content, name)
		 VALUES("cayugasandc@icloud.com", "csac_email");
INSERT INTO cms (content, name)
	 	 VALUES("(607) 351-1886", "csac_phone");
INSERT INTO cms (content, name)
		 VALUES("Cayuga Strength and Conditioning", "csac_facebook");



INSERT INTO cms(content, name)
		 VALUES("When you workout later in the day, you are likely to get tired and lose your motivation to do it.", "fitness_tip");

/*INSERT INTO content (content, name)
		 	VALUES("", "");*/
