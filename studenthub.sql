CREATE DATABASE studenthub;
USE studenthub;
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    course VARCHAR(100) NOT NULL,
    year INT NOT NULL
);
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(100) NOT NULL
);
CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    UNIQUE(student_id, event_id)
);
INSERT INTO students (name, email, course, year)
VALUES
('Diya Tolia', 'diya@gmail.com', 'Computer Science', 2),
('Riya Patel', 'riya@gmail.com', 'Information Technology', 2),
('Dhyey Shah', 'dhyey@gmail.com', 'Computer Engineering', 3);
INSERT INTO events (event_name, event_date, location)
VALUES
('Coding Workshop', '2026-10-05', 'Lab 1'),
('Web Development Seminar', '2026-10-10', 'Seminar Hall'),
('Hackathon', '2026-10-15', 'Auditorium');
INSERT INTO registrations (student_id, event_id)
VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 3);