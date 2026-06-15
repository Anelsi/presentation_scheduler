CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20) DEFAULT 'student'
);

CREATE TABLE IF NOT EXISTS presentations (
    id INT AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255),
    description TEXT,

    presenter VARCHAR(100),
    category VARCHAR(100),

    presentation_date DATE,

    start_time TIME,
    end_time TIME,

    room VARCHAR(50),

    file_url TEXT,
    s3_key TEXT,

    status VARCHAR(20) DEFAULT 'upcoming',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS interests (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT,
    category VARCHAR(100),

    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS personal_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT,
    presentation_id INT,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (presentation_id) REFERENCES presentations(id)
);
