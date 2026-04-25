CREATE TABLE IF NOT EXISTS venues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) DEFAULT NULL,
    capacity INT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS equipment_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    total_quantity INT NOT NULL DEFAULT 0,
    available_quantity INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_code VARCHAR(50) NOT NULL UNIQUE,
    requested_by_user_id INT NOT NULL,
    event_title VARCHAR(150) NOT NULL,
    department VARCHAR(150) NOT NULL,
    purpose TEXT NOT NULL,
    request_date DATE NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_requested_by_user_id (requested_by_user_id),
    INDEX idx_reservation_status (status),
    INDEX idx_reservation_datetime (start_datetime, end_datetime)
);

CREATE TABLE IF NOT EXISTS reservation_request_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_request_id INT NOT NULL,
    item_type ENUM('venue', 'equipment') NOT NULL,
    resource_id INT NOT NULL,
    resource_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_request_item (reservation_request_id),
    INDEX idx_item_lookup (item_type, resource_id),
    CONSTRAINT fk_reservation_request_items_request
        FOREIGN KEY (reservation_request_id) REFERENCES reservation_requests(id)
        ON DELETE CASCADE
);

INSERT INTO venues (name, location, capacity, is_active)
SELECT 'Auditorium', 'Main Building', 300, 1
WHERE NOT EXISTS (SELECT 1 FROM venues WHERE name = 'Auditorium');

INSERT INTO equipment_items (name, total_quantity, available_quantity, is_active)
SELECT 'Chairs', 500, 500, 1
WHERE NOT EXISTS (SELECT 1 FROM equipment_items WHERE name = 'Chairs');
