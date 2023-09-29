
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO users (name) VALUES
    ('Maya'),
    ('Kie'),
    ('Ron'),
    ('Jhon'),
    ('Smith'),
    ('Lily');

CREATE TABLE IF NOT EXISTS item_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO item_types (id, name) VALUES
    (1, 'Office Supply'),
    (2, 'Equipment'),
    (3, 'Furniture');

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    item_type INT NOT NULL
);

INSERT INTO items (id, name, item_type) VALUES
    (1, 'Pen', 1),
    (2, 'Printer', 2),
    (3, 'Marker', 1),
    (4, 'Scanner', 2),
    (5, 'Clear Tape', 1),
    (6, 'Standing Table', 2),
    (7, 'Shredder', 2),
    (8, 'Thumbtack', 1),
    (10, 'Paper Clip', 1),
    (11, 'A4 Sheet', 1),
    (12, 'Notebook', 1),
    (13, 'Chair', 3);

CREATE TABLE IF NOT EXISTS requests (
    req_id INT AUTO_INCREMENT PRIMARY KEY,
    requested_by VARCHAR(255) NOT NULL,
    requested_on DATE NOT NULL,
    ordered_on DATE NOT NULL,
    items TEXT NOT NULL
);

INSERT INTO requests (req_id, requested_by, requested_on, ordered_on, items) VALUES
    (1, 'maya', '2023-04-01', '2023-05-12', "[[1,1], [5,1], [3, 1]]"),
    (2, 'kie', '2023-04-03', '2023-05-12', "[[2,2]]"),
    (3, 'ron', '2023-04-10', '2023-05-12', "[[3,1],[10,1]]"),
    (4, 'maya', '2023-04-20', '2023-05-12', "[[4,2]]"),
    (5, 'john', '2023-05-01', '2023-05-12', "[[5,1],[12,1]]"),
    (6, 'smith', '2023-05-04', '2023-05-12', "[[6,2]]"),
    (7, 'john', '2023-05-10', '2023-05-12', "[[7,2]]"),
    (8, 'lily', '2023-05-11', '2023-05-12', "[[8,1],[11,1]]"),
    (9, 'lily', '2023-05-11', '2023-05-12', "[[7,2]]"),
    (10, 'lily', '2023-05-11', '2023-05-12', "[[13, 3]]");

CREATE TABLE IF NOT EXISTS summary (
    req_id INT AUTO_INCREMENT PRIMARY KEY,
    requested_by VARCHAR(255) NOT NULL,
    ordered_on DATE NOT NULL,
    items TEXT NOT NULL
);

INSERT INTO summary (req_id, requested_by, ordered_on, items) VALUES
    (1, 'maya', '2023-05-12', "[[1,1], [5,1], [3, 1]]"),
    (2, 'kie', '2023-05-12', "[[2,2]]"),
    (3, 'ron', '2023-05-12', "[[3,1],[10,1]]"),
    (5, 'john', '2023-05-12', "[[5,1][12,1]]"),
    (6, 'smith', '2023-05-12', "[[6,2]]"),
    (8, 'lily', '2023-05-12', "[[8,1],[11,1]]");