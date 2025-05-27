-- Création des tables
CREATE TABLE customer_1557984 (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    given_name VARCHAR(50) NOT NULL,
    family_name VARCHAR(50) NOT NULL,
    phone_number BIGINT NOT NULL,
    password VARBINARY(255) NOT NULL
);

CREATE TABLE visits_1557984 (
    visit_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customer_1557984(customer_id)
);

-- L'insertion

INSERT INTO customer_1557984 (customer_id, given_name, family_name, phone_number, password)
VALUES 
(1, 'Test', 'User', 1234567890, AES_ENCRYPT('1', 'mysecretkey')),
(NULL, 'Alan', 'Huynh', 4198765432, AES_ENCRYPT('securePass1', 'mysecretkey')),
(NULL, 'Jane', 'Doe', 4123456789, AES_ENCRYPT('securePass2', 'mysecretkey'));

INSERT INTO visits_1557984 (customer_id, visit_date, visit_time)
VALUES 
(1, '2025-05-01', '10:30:00'),
(1, '2025-05-05', '14:00:00'),
(2, '2025-05-03', '09:00:00'),
(2, '2025-05-07', '16:00:00'),
(2, '2025-05-10', '12:30:00'),
(3, '2025-05-02', '11:00:00'),
(3, '2025-05-04', '15:15:00'),
(3, '2025-05-06', '08:45:00'),
(3, '2025-05-08', '13:00:00'),
(3, '2025-05-09', '17:30:00');
