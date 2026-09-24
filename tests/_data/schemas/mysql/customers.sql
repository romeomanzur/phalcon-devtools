DROP TABLE IF EXISTS `customers`;

CREATE TABLE customers(
    id integer auto_increment,
    firstname Varchar(30),
    surname Varchar(30),
    membertype Varchar(6),
    dateofbirth date,
    PRIMARY KEY(id)
);

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Hedley","Reeves","U7B0Q","19-04-15"),("Lillian","Bright","B6O2I","19-04-17"),("Quin","Cherry","W2Q7K","19-04-15"),("Felix","Underwood","H9O8P","19-04-16"),("Kirestin","Finley","E5K6R","19-04-15"),("Brandon","West","A1L1Q","19-04-17"),("Plato","Vaughn","L2C3S","19-04-15"),("Kristen","Davenport","Q7S9K","19-04-15"),("Erasmus","Oneil","T7S0B","19-04-16"),("Steven","Kramer","I6G0M","19-04-15");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Damian","Houston","Y3U7T","19-04-16"),("Griffin","Beach","K1S6X","19-04-15"),("Kylie","Cohen","S3D2P","19-04-17"),("Wayne","Freeman","W5H9H","19-04-17"),("Charde","Lewis","L4B4B","19-04-17"),("Simone","Ratliff","M4I1N","19-04-17"),("Maggie","Guerrero","O4S3Z","19-04-17"),("Ferris","Fitzgerald","J1M0F","19-04-16"),("Yoshio","Reynolds","B6J9Y","19-04-17"),("Lacey","Austin","H3C8R","19-04-15");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Eugenia","Tyson","A6K1J","19-04-16"),("Slade","Farley","M7Y5K","19-04-16"),("Kylynn","Bolton","I7D5M","19-04-17"),("Patience","Ware","R1Y8S","19-04-17"),("Cooper","Bruce","R5Y2P","19-04-17"),("Justin","Harding","X9Q0H","19-04-16"),("Cleo","York","R4Z9A","19-04-17"),("Dante","Delacruz","L6H3S","19-04-17"),("Kylie","Oneal","C0U1Q","19-04-16"),("Macey","Salazar","L0Q8H","19-04-15");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Rosalyn","Chandler","M6B2H","19-04-15"),("Jorden","Pratt","Z8L6D","19-04-15"),("Kermit","Gaines","T7H1K","19-04-17"),("Kirsten","Grimes","B6N1L","19-04-15"),("Keegan","Wolfe","F2L4A","19-04-16"),("Kiara","Castaneda","D7P0D","19-04-17"),("Eleanor","Gomez","P4W3O","19-04-17"),("Abdul","Potter","T9P5Z","19-04-16"),("Denton","Mendoza","W0L1X","19-04-16"),("Omar","Morse","A2P8Q","19-04-17");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Martina","Mckinney","S0P7K","19-04-16"),("Hedley","Nguyen","L7U8O","19-04-15"),("Wanda","Dyer","T8Z1R","19-04-16"),("Olga","Witt","I0R6O","19-04-15"),("Leandra","Simmons","U6C4C","19-04-17"),("Lev","Cox","D8P2R","19-04-17"),("Maxine","Gilmore","E2D3R","19-04-15"),("Sean","Doyle","K1Z1M","19-04-17"),("Carl","Brady","M2K4X","19-04-15"),("Hilel","Huffman","N5W1L","19-04-17");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Ina","Mendoza","I0M8V","19-04-17"),("Uriah","Mccarthy","A0Z8A","19-04-15"),("Kay","Stevens","Q8J8G","19-04-15"),("Silas","Calhoun","Z8L4O","19-04-16"),("Nolan","Garrett","Z1G4A","19-04-17"),("Knox","Sosa","X0X3A","19-04-15"),("Calista","Barnes","D2R6H","19-04-17"),("Troy","Reed","K0P3L","19-04-17"),("Flynn","Fry","K1Z6P","19-04-17"),("Veda","Boone","B7P2C","19-04-17");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Madonna","Gilbert","U1T8G","19-04-17"),("Boris","Montgomery","V2L4R","19-04-16"),("Wing","Reese","U3L2S","19-04-15"),("Jackson","Bradford","J8C9Z","19-04-16"),("Jermaine","Mendez","Y2W5W","19-04-16"),("Astra","Chan","U0V6D","19-04-16"),("Kitra","Head","U2A3O","19-04-15"),("Honorato","Mullen","W3K6D","19-04-16"),("Brynn","Morse","O7K3B","19-04-17"),("Yolanda","Rivas","J2Z9L","19-04-16");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Cedric","Avery","L2P1G","19-04-15"),("Ann","Travis","K2B5E","19-04-17"),("Lee","Mathis","C0Z4A","19-04-17"),("Skyler","Cervantes","W9I3B","19-04-15"),("Hermione","Baird","C4V9Q","19-04-17"),("Helen","Clark","I3T9J","19-04-17"),("Maryam","Blackburn","S1I7Z","19-04-15"),("Rahim","Knapp","O2S7K","19-04-17"),("Gannon","Williamson","F9O2L","19-04-15"),("Stephen","Bradshaw","H9X5D","19-04-16");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Gil","Manning","Q3Z2R","19-04-16"),("Victoria","Alford","J4T7L","19-04-16"),("Calvin","Stout","R1H0I","19-04-17"),("Erica","Gregory","H2K1T","19-04-17"),("Finn","Maynard","I8K4T","19-04-17"),("Karina","Nichols","U1Y4I","19-04-17"),("Imogene","Soto","S6M7P","19-04-16"),("Juliet","Wilkins","P0X0K","19-04-15"),("Emma","Dejesus","L8P4G","19-04-16"),("Pearl","Henry","L9X3W","19-04-17");

INSERT INTO customers (firstname,surname,membertype,dateofbirth) VALUES ("Lillith","Garcia","L1V1Q","19-04-16"),("Zenia","Figueroa","X6J6W","19-04-17"),("Ulla","Kidd","V5N4T","19-04-16"),("Maxwell","Solis","Q0J1Y","19-04-16"),("Erasmus","Raymond","F8Y3G","19-04-16"),("Caleb","Byrd","A5D6F","19-04-17"),("Philip","Carpenter","B1R0E","19-04-17"),("Lisandra","Wilcox","N2Z7T","19-04-15"),("Joseph","Bush","N4Z8R","19-04-15"),("Kaitlin","Ferrell","J0G8Q","19-04-17");