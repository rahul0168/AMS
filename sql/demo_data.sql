
INSERT INTO veranstaltung (title, event_type, event_date) VALUES
('PHP Workshop', 'Workshop', '2025-08-10'),
('Annual Seminar', 'Seminar', '2025-08-15');

INSERT INTO nutzer (name, role, department_id) VALUES
('Alice', 'admin', 1),
('Bob', 'manager', 2),
('Charlie', 'viewer', 3),
('David', 'viewer', 3);

INSERT INTO anwesenheits_kontrolle (user_id, event_id, status) VALUES
(1, 1, 'present'),
(2, 1, 'present'),
(3, 1, 'absent'),
(3, 2, 'present'),
(4, 2, 'present');
