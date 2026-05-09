USE vending_machine;

INSERT INTO products (name, price, quantity_available)
VALUES
    ('Coke', 3.990, 10),
    ('Pepsi', 6.885, 10),
    ('Water', 0.500, 10)
ON DUPLICATE KEY UPDATE
    price = VALUES(price),
    quantity_available = VALUES(quantity_available);

INSERT INTO users (username, email, password_hash, role)
VALUES
    (
        'admin',
        'admin@example.com',
        '$2y$10$8m9sXquaxEekqWJx9Mx2au9fIlW5xFhQQA4xBu0xM0gzM4Q3t6V.y',
        'Admin'
    ),
    (
        'user',
        'user@example.com',
        '$2y$10$8m9sXquaxEekqWJx9Mx2au9fIlW5xFhQQA4xBu0xM0gzM4Q3t6V.y',
        'User'
    )
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    role = VALUES(role);