SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS livre;

CREATE TABLE livre (
    nolivre INT AUTO_INCREMENT,
    isbn13 VARCHAR(13) UNIQUE NOT NULL,
    titre VARCHAR(255) NOT NULL,
    noauteur INT,
    editeur VARCHAR(100),
    anneeparution INT,
    categorie VARCHAR(50),
    resume TEXT,
    image VARCHAR(255),
    dateajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (nolivre),
    FOREIGN KEY (noauteur) REFERENCES auteur(noauteur)
);

SET FOREIGN_KEY_CHECKS=1;
