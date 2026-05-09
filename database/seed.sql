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
        '$2y$12$RTKvlzoCdqKPjTczV7dxC.g9Zq15prIOrrpiu5QIKfUrsKkusXfJC',
        'Admin'
    ),
    (
        'user',
        'user@example.com',
        '$2y$12$s5fzsUj5E8zwzLdoOVlSIuYFjRPra9ObMeZqfo8YiQ87y5teOunWG',
        'User'
    )
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    role = VALUES(role);