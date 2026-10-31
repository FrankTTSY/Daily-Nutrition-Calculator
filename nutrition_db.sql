CREATE TABLE nutrition_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    date VARCHAR(100) NOT NULL,
    goal VARCHAR(255) NOT NULL,
    calories INT NOT NULL,
    p INT NOT NULL,
    c INT NOT NULL,
    f INT NOT NULL
);
