-- Sample data for Presentation Scheduler.
-- Login credentials:
--   student@example.com / Student123
--   admin@example.com   / Admin123

INSERT INTO users (name, email, password, role) VALUES
('Student User', 'student@example.com', '$2y$10$4RoUOllLXHsCe1kk4Iep9eRq4j6pba0ZqUC5b0Kq4BbRdrdI3lInO', 'student'),
('Admin User', 'admin@example.com', '$2y$10$nRqK6NK0owdYMl8WCd6nfeLveIIsyyYgNPssm7PXFuMm/hPCFb4B2', 'admin')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password = VALUES(password),
    role = VALUES(role);

INSERT INTO presentations
(
    title,
    description,
    presenter,
    category,
    presentation_date,
    start_time,
    end_time,
    room,
    file_url,
    s3_key,
    status
)
SELECT
    'Cloud Deployments with Docker',
    'A practical session about packaging a PHP application with Docker and running it with a MySQL database.',
    'Ivan Ivanov',
    'Cloud',
    '2026-07-10',
    '10:00:00',
    '11:00:00',
    'Room 101',
    NULL,
    NULL,
    'upcoming'
WHERE NOT EXISTS (
    SELECT 1 FROM presentations WHERE title = 'Cloud Deployments with Docker'
);

INSERT INTO presentations
(
    title,
    description,
    presenter,
    category,
    presentation_date,
    start_time,
    end_time,
    room,
    file_url,
    s3_key,
    status
)
SELECT
    'AI Tools for Student Projects',
    'Overview of useful AI tools for brainstorming, code review, documentation and project planning.',
    'Maria Petrova',
    'AI',
    '2026-07-11',
    '12:00:00',
    '13:00:00',
    'Room 202',
    NULL,
    NULL,
    'upcoming'
WHERE NOT EXISTS (
    SELECT 1 FROM presentations WHERE title = 'AI Tools for Student Projects'
);

INSERT INTO presentations
(
    title,
    description,
    presenter,
    category,
    presentation_date,
    start_time,
    end_time,
    room,
    file_url,
    s3_key,
    status
)
SELECT
    'Modern Web Forms in PHP',
    'A presentation about HTML forms, server-side validation, sessions and safe database writes with PDO.',
    'Georgi Georgiev',
    'Web',
    '2026-07-12',
    '14:00:00',
    '15:00:00',
    'Room 303',
    NULL,
    NULL,
    'upcoming'
WHERE NOT EXISTS (
    SELECT 1 FROM presentations WHERE title = 'Modern Web Forms in PHP'
);

INSERT INTO presentations
(
    title,
    description,
    presenter,
    category,
    presentation_date,
    start_time,
    end_time,
    room,
    file_url,
    s3_key,
    status
)
SELECT
    'Password Security Basics',
    'Past presentation about password hashing, secure authentication flows and common mistakes in web applications.',
    'Elena Dimitrova',
    'Security',
    '2026-05-20',
    '09:30:00',
    '10:30:00',
    'Room 404',
    NULL,
    NULL,
    'past'
WHERE NOT EXISTS (
    SELECT 1 FROM presentations WHERE title = 'Password Security Basics'
);
