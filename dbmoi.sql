INSERT INTO `departments` (id, name, head_id, created_at, updated_at) VALUES
(1, 'Khoa Công nghệ thông tin', NULL, NOW(), NOW()),
(2, 'Khoa Kinh tế và Quản lý', NULL, NOW(), NOW()),
(3, 'Khoa Kỹ thuật Xây dựng', NULL, NOW(), NOW()),
(4, 'Khoa Kỹ thuật tài nguyên nước', NULL, NOW(), NOW()),
(5, 'Khoa Cơ khí', NULL, NOW(), NOW()),
(6, 'Khoa Điện - Điện tử', NULL, NOW(), NOW()),
(7, 'Khoa Hóa và Môi trường', NULL, NOW(), NOW()),
(8, 'Khoa Lý luận chính trị', NULL, NOW(), NOW()),
(9, 'Viện Đào tạo và Khoa học ứng dụng Miền Trung', NULL, NOW(), NOW()),
(10, 'Trung tâm Đào tạo quốc tế', NULL, NOW(), NOW());

INSERT INTO `divisions` (id, code, name, department_id, description, created_at, updated_at) VALUES
(1, 'CNPM', 'Bộ môn Công nghệ phần mềm', 1, 'Phụ trách các môn học về quy trình, kỹ thuật phần mềm.', NOW(), NOW()),
(2, 'HTTT', 'Bộ môn Hệ thống thông tin', 1, 'Phụ trách các môn học về CSDL, phân tích thiết kế HTTT.', NOW(), NOW()),
(3, 'KHMT', 'Bộ môn Khoa học máy tính', 1, 'Phụ trách các môn học cơ sở, thuật toán, AI.', NOW(), NOW()),
(4, 'MMT', 'Bộ môn Mạng và Truyền thông dữ liệu', 1, 'Phụ trách các môn về mạng máy tính, an toàn thông tin.', NOW(), NOW()),
(5, 'QTKD', 'Bộ môn Quản trị kinh doanh', 2, 'Phụ trách các môn học về quản trị, marketing.', NOW(), NOW()),
(6, 'KETOAN', 'Bộ môn Kế toán', 2, 'Phụ trách các môn học về kế toán, kiểm toán.', NOW(), NOW()),
(7, 'XDDD', 'Bộ môn Xây dựng Dân dụng và Công nghiệp', 3, 'Phụ trách các môn kết cấu, nền móng.', NOW(), NOW()),
(8, 'CTGT', 'Bộ môn Công trình Giao thông', 3, 'Phụ trách các môn cầu đường.', NOW(), NOW()),
(9, 'TNN', 'Bộ môn Kỹ thuật tài nguyên nước', 4, NULL, NOW(), NOW()),
(10, 'TOAN', 'Bộ môn Toán', 1, 'Phụ trách các môn toán cơ sở cho khoa CNTT.', NOW(), NOW());

INSERT INTO `majors` (id, code, name, department_id, created_at, updated_at) VALUES
(1, '7480201', 'Công nghệ thông tin', 1, NOW(), NOW()),
(2, '7480103', 'Kỹ thuật phần mềm', 1, NOW(), NOW()),
(3, '7480104', 'Hệ thống thông tin', 1, NOW(), NOW()),
(4, '7480101', 'Khoa học máy tính', 1, NOW(), NOW()),
(5, '7340101', 'Quản trị kinh doanh', 2, NOW(), NOW()),
(6, '7340301', 'Kế toán', 2, NOW(), NOW()),
(7, '7510605', 'Logistics và Quản lý chuỗi cung ứng', 2, NOW(), NOW()),
(8, '7580201', 'Kỹ thuật xây dựng', 3, NOW(), NOW()),
(9, '7580301', 'Kinh tế xây dựng', 3, NOW(), NOW()),
(10, '7580205', 'Kỹ thuật xây dựng công trình giao thông', 3, NOW(), NOW());

INSERT INTO `users` (id, name, first_name, last_name, email, password, phone_number, role, status, division_id, major_id, created_at, updated_at) VALUES
-- Heads & Training Office
(1, 'PGS.TS. Nguyễn Thanh Tùng', 'Nguyễn', 'Thanh Tùng', 'tungnt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234561', 'head_of_department', 'active', 1, NULL, NOW(), NOW()),
(2, 'PGS.TS. Đỗ Thị Thu Hằng', 'Đỗ', 'Thị Thu Hằng', 'hangdtt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234562', 'head_of_department', 'active', 5, NULL, NOW(), NOW()),
(3, 'ThS. Nguyễn Thị Minh', 'Nguyễn', 'Thị Minh', 'minhnt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234563', 'training_office', 'active', NULL, NULL, NOW(), NOW()),
-- Teachers
(4, 'TS. Đặng Văn Hưng', 'Đặng', 'Văn Hưng', 'hungdv@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345674', 'teacher', 'active', 1, NULL, NOW(), NOW()),
(5, 'ThS. Lê Thị Thu Hà', 'Lê', 'Thị Thu Hà', 'haltt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345675', 'teacher', 'active', 2, NULL, NOW(), NOW()),
(6, 'TS. Nguyễn Hữu Quỳnh', 'Nguyễn', 'Hữu Quỳnh', 'quynhnh@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345676', 'teacher', 'active', 5, NULL, NOW(), NOW()),
(7, 'ThS. Hoàng Văn Dũng', 'Hoàng', 'Văn Dũng', 'dunghv@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345677', 'teacher', 'active', 7, NULL, NOW(), NOW()),
-- Students
(8, 'Nguyễn Văn An', 'Nguyễn', 'Văn An', '65CNTT1.an.nv@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'student', 'active', NULL, 1, NOW(), NOW()),
(9, 'Trần Thị Bình', 'Trần', 'Thị Bình', '65CNTT1.binh.tt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654322', 'student', 'active', NULL, 1, NOW(), NOW()),
(10, 'Lê Minh Cường', 'Lê', 'Minh Cường', '65KTPM1.cuong.lm@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654323', 'student', 'active', NULL, 2, NOW(), NOW());
UPDATE `departments` SET `head_id` = 1 WHERE `id` = 1; -- Gán PGS.TS. Nguyễn Thanh Tùng làm Trưởng khoa CNTT
UPDATE `departments` SET `head_id` = 2 WHERE `id` = 2; -- Gán PGS.TS. Đỗ Thị Thu Hằng làm Trưởng khoa KT&QL

INSERT INTO `classes` (id, name, semester, academic_year, department_id, created_at, updated_at) VALUES
(1, '65CNTT1', '1', '2023-2024', 1, NOW(), NOW()),
(2, '65CNTT2', '1', '2023-2024', 1, NOW(), NOW()),
(3, '65KTPM1', '1', '2023-2024', 1, NOW(), NOW()),
(4, '65HTTT1', '1', '2023-2024', 1, NOW(), NOW()),
(5, '65QTKD1', '1', '2023-2024', 2, NOW(), NOW()),
(6, '65KT1', '1', '2023-2024', 2, NOW(), NOW()),
(7, '65XD1', '1', '2023-2024', 3, NOW(), NOW()),
(8, '64CNTT1', '3', '2023-2024', 1, NOW(), NOW()), -- Lớp năm 2, kỳ 3
(9, '64KTPM1', '3', '2023-2024', 1, NOW(), NOW()),
(10, '66CNTT1', '1', '2024-2025', 1, NOW(), NOW()); -- Lớp khóa mới

INSERT INTO `courses` (id, name, code, credits, department_id, division_id, created_at, updated_at) VALUES
(1, 'Nhập môn Lập trình', 'CSE112', 3, 1, 1, NOW(), NOW()),
(2, 'Cấu trúc dữ liệu và Giải thuật', 'CSE211', 3, 1, 1, NOW(), NOW()),
(3, 'Cơ sở dữ liệu', 'CSE313', 3, 1, 2, NOW(), NOW()),
(4, 'Quản trị học', 'MAN101', 3, 2, 5, NOW(), NOW()),
(5, 'Marketing căn bản', 'MAR101', 3, 2, 5, NOW(), NOW()),
(6, 'Triết học Mác-Lênin', 'PHI101', 3, 8, NULL, NOW(), NOW()), -- Khoa Lý luận chính trị
(7, 'Phát triển ứng dụng Web', 'CSE480', 3, 1, 1, NOW(), NOW()),
(8, 'Nguyên lý Kế toán', 'ACC101', 3, 2, 6, NOW(), NOW()),
(9, 'Sức bền vật liệu 1', 'CEE201', 3, 3, 7, NOW(), NOW()),
(10, 'Toán Giải tích 1', 'MAT101', 3, 1, 10, NOW(), NOW());

INSERT INTO `rooms` (id, name, capacity, location, created_at, updated_at) VALUES
(1, 'K1-201', 60, 'Nhà K1, Tầng 2', NOW(), NOW()),
(2, 'K1-202', 60, 'Nhà K1, Tầng 2', NOW(), NOW()),
(3, 'C1-301 (Lab)', 40, 'Nhà C1, Tầng 3, Phòng máy', NOW(), NOW()),
(4, 'C1-302 (Lab)', 40, 'Nhà C1, Tầng 3, Phòng máy', NOW(), NOW()),
(5, 'C2-405 (Hall)', 150, 'Nhà C2, Tầng 4, Giảng đường lớn', NOW(), NOW()),
(6, 'C5-501', 80, 'Nhà C5, Tầng 5', NOW(), NOW()),
(7, 'C5-502', 80, 'Nhà C5, Tầng 5', NOW(), NOW()),
(8, 'K1-301', 60, 'Nhà K1, Tầng 3', NOW(), NOW()),
(9, 'K1-302', 60, 'Nhà K1, Tầng 3', NOW(), NOW()),
(10, 'H1-201 (Hội trường)', 200, 'Hội trường H1, Tầng 2', NOW(), NOW());

INSERT INTO `class_student` (id, class_model_id, student_id, created_at, updated_at) VALUES
(1, 1, 8, NOW(), NOW()), -- SV An (8) vào lớp 65CNTT1 (1)
(2, 1, 9, NOW(), NOW()), -- SV Bình (9) vào lớp 65CNTT1 (1)
(3, 3, 10, NOW(), NOW()), -- SV Cường (10) vào lớp 65KTPM1 (3)
(4, 2, 8, NOW(), NOW()), -- (Ví dụ, SV 8 học 2 lớp - thường là không đúng, nhưng để đủ 10 bản ghi)
(5, 4, 9, NOW(), NOW()), -- (Ví dụ)
(6, 5, 10, NOW(), NOW()), -- (Ví dụ)
(7, 6, 8, NOW(), NOW()), -- (Ví dụ)
(8, 7, 9, NOW(), NOW()), -- (Ví dụ)
(9, 8, 10, NOW(), NOW()), -- (Ví dụ)
(10, 9, 8, NOW(), NOW()); -- (Ví dụ)

INSERT INTO `class_course_assignments` (id, class_id, course_id, teacher_id, semester, created_at, updated_at) VALUES
(1, 1, 1, 4, '2023-2024_1', NOW(), NOW()), -- Lớp 65CNTT1 (1), Môn NMLT (1), GV Hưng (4)
(2, 1, 2, 4, '2023-2024_1', NOW(), NOW()), -- Lớp 65CNTT1 (1), Môn CTDL (2), GV Hưng (4)
(3, 1, 3, 5, '2023-2024_1', NOW(), NOW()), -- Lớp 65CNTT1 (1), Môn CSDL (3), GV Hà (5)
(4, 5, 4, 6, '2023-2024_1', NOW(), NOW()), -- Lớp 65QTKD1 (5), Môn QT học (4), GV Quỳnh (6)
(5, 5, 5, 6, '2023-2024_1', NOW(), NOW()), -- Lớp 65QTKD1 (5), Môn MKT CB (5), GV Quỳnh (6)
(6, 7, 9, 7, '2023-2024_1', NOW(), NOW()), -- Lớp 65XD1 (7), Môn SBVL (9), GV Dũng (7)
(7, 10, 1, 4, '2024-2025_1', NOW(), NOW()), -- Lớp 66CNTT1 (10), Môn NMLT (1), GV Hưng (4)
(8, 3, 7, 4, '2023-2024_1', NOW(), NOW()), -- Lớp 65KTPM1 (3), Môn Web (7), GV Hưng (4)
(9, 4, 3, 5, '2023-2024_1', NOW(), NOW()), -- Lớp 65HTTT1 (4), Môn CSDL (3), GV Hà (5)
(10, 1, 10, 5, '2023-2024_1', NOW(), NOW()); -- Lớp 65CNTT1 (1), Môn Toán 1 (10), GV Hà (5) (Giả sử GV Hà dạy Toán)

INSERT INTO `schedules` (id, class_course_assignment_id, room_id, `date`, session, topic, status, created_at, updated_at) VALUES
(1, 1, 1, '2024-09-02', '1-3', 'Bài 1: Giới thiệu chung về lập trình', 'taught', NOW(), NOW()),
(2, 1, 1, '2024-09-09', '1-3', 'Bài 2: Biến, kiểu dữ liệu và toán tử', 'taught', NOW(), NOW()),
(3, 1, 1, '2024-09-16', '1-3', 'Bài 3: Cấu trúc rẽ nhánh', 'cancelled', NOW(), NOW()),
(4, 4, 5, '2024-09-03', '4-6', 'Chương 1: Tổng quan về Quản trị học', 'taught', NOW(), NOW()),
(5, 4, 5, '2024-09-10', '4-6', 'Chương 2: Chức năng hoạch định', 'taught', NOW(), NOW()),
(6, 1, 1, '2024-09-23', '1-3', 'Bài 4: Cấu trúc lặp', 'scheduled', NOW(), NOW()),
(7, 4, 5, '2024-09-17', '4-6', 'Chương 3: Chức năng tổ chức', 'scheduled', NOW(), NOW()),
(8, 2, 3, '2024-09-04', '1-3', 'Bài 1: Mảng (Array)', 'taught', NOW(), NOW()),
(9, 2, 3, '2024-09-11', '1-3', 'Bài 2: Danh sách liên kết (Linked List)', 'scheduled', NOW(), NOW()),
(10, 1, 2, '2024-09-18', '7-9', 'Bài 3: Cấu trúc rẽ nhánh (Học bù)', 'makeup', NOW(), NOW());

INSERT INTO `attendances` (id, schedule_id, student_id, status, note, created_at, updated_at) VALUES
(1, 1, 8, 'present', NULL, NOW(), NOW()), -- Buổi 1 (lớp 1), SV An (8)
(2, 1, 9, 'present', NULL, NOW(), NOW()), -- Buổi 1 (lớp 1), SV Bình (9)
(3, 2, 8, 'late', 'Tắc đường', NOW(), NOW()), -- Buổi 2 (lớp 1), SV An (8)
(4, 2, 9, 'absent', 'Ốm (có giấy phép)', NOW(), NOW()), -- Buổi 2 (lớp 1), SV Bình (9)
(5, 8, 8, 'present', NULL, NOW(), NOW()), -- Buổi 8 (lớp 1), SV An (8)
(6, 8, 9, 'present', NULL, NOW(), NOW()), -- Buổi 8 (lớp 1), SV Bình (9)
(7, 10, 8, 'present', NULL, NOW(), NOW()), -- Buổi 10 (lớp 1 - bù), SV An (8)
(8, 10, 9, 'present', NULL, NOW(), NOW()), -- Buổi 10 (lớp 1 - bù), SV Bình (9)
(9, 1, 10, 'absent', 'Nhầm lịch', NOW(), NOW()), -- (Ví dụ SV Cường (10) đi nhầm vào buổi 1)
(10, 4, 8, 'absent', 'Nhầm lịch', NOW(), NOW()); -- (Ví dụ SV An (8) đi nhầm vào buổi 4)

INSERT INTO `leave_requests` (id, teacher_id, schedule_id, reason, document_url, status, approved_by, created_at, updated_at) VALUES
(1, 4, 3, 'Nghỉ ốm đột xuất', 'http://tlu.edu.vn/docs/sick_leave.pdf', 'approved', 1, NOW(), NOW()), -- GV Hưng (4) nghỉ buổi (3), Trưởng khoa Tùng (1) duyệt
(2, 6, 7, 'Đi dự hội thảo Marketing toàn quốc', 'http://tlu.edu.vn/docs/conference.pdf', 'pending', NULL, NOW(), NOW()), -- GV Quỳnh (6) nghỉ buổi (7), chờ duyệt
(3, 4, 6, 'Việc cá nhân gia đình', NULL, 'rejected', 3, NOW(), NOW()), -- GV Hưng (4) nghỉ buổi (6), P.Đào tạo Minh (3) từ chối
(4, 5, 9, 'Trùng lịch họp đột xuất của trường', NULL, 'approved', 1, NOW(), NOW()), -- GV Hà (5) nghỉ buổi (9), Trưởng khoa Tùng (1) duyệt
(5, 7, 6, 'Lý do sức khỏe', NULL, 'pending', NULL, NOW(), NOW()), -- (GV Dũng (7) xin nghỉ buổi 6 - logic sai vì không phải lịch của GV)
(6, 4, 2, 'Tham gia hội đồng chấm tốt nghiệp', NULL, 'pending', NULL, NOW(), NOW()),
(7, 5, 3, 'Trùng lịch coi thi', NULL, 'pending', NULL, NOW(), NOW()),
(8, 6, 4, 'Cảm cúm', NULL, 'pending', NULL, NOW(), NOW()),
(9, 7, 8, 'Họp bộ môn Xây dựng', NULL, 'pending', NULL, NOW(), NOW()),
(10, 4, 1, 'Xe hỏng giữa đường', NULL, 'rejected', 3, NOW(), NOW());

INSERT INTO `makeup_classes` (id, teacher_id, original_schedule_id, new_schedule_id, status, created_at, updated_at) VALUES
(1, 4, 3, 10, 'approved', NOW(), NOW()), -- GV Hưng (4) bù cho buổi (3) (đã nghỉ) bằng buổi mới (10)
(2, 5, 9, 8, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu, GV Hà (5) bù buổi (9) bằng buổi (8) - logic sai)
(3, 6, 7, 6, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu)
(4, 4, 1, 2, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu)
(5, 5, 2, 3, 'done', NOW(), NOW()), -- (Dữ liệu mẫu)
(6, 6, 4, 5, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu)
(7, 7, 5, 7, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu)
(8, 4, 6, 9, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu)
(9, 5, 8, 1, 'done', NOW(), NOW()), -- (Dữ liệu mẫu)
(10, 6, 10, 4, 'pending', NOW(), NOW()); -- (Dữ liệu mẫu)

INSERT INTO `password_reset_tokens` (email, token, created_at) VALUES
('tungnt@tlu.edu.vn', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', NOW()),
('hungdv@tlu.edu.vn', 'b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7', NOW()),
('haltt@tlu.edu.vn', 'c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8', NOW()),
('65CNTT1.an.nv@tlu.edu.vn', 'd4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9', NOW()),
('65CNTT1.binh.tt@tlu.edu.vn', 'e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0', NOW()),
('65KTPM1.cuong.lm@tlu.edu.vn', 'f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1', NOW()),
('minhnt@tlu.edu.vn', 'g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2', NOW()),
('hangdtt@tlu.edu.vn', 'h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3', NOW()),
('quynhnh@tlu.edu.vn', 'i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4', NOW()),
('dunghv@tlu.edu.vn', 'j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5', NOW());

INSERT INTO `sessions` (id, user_id, ip_address, user_agent, payload, last_activity) VALUES
('session_id_1', 1, '113.160.1.1', 'Mozilla/5.0... (Windows NT 10.0; Win64; x64)', 'long_base64_payload_string', 1728886400), -- Trưởng khoa Tùng
('session_id_2', 8, '118.70.2.2', 'Mozilla/5.0... (Linux; Android 13)', 'long_base64_payload_string', 1728886401), -- SV An
('session_id_3', 3, '113.160.1.2', 'Mozilla/5.0... (Windows NT 10.0; Win64; x64)', 'long_base64_payload_string', 1728886402), -- P.ĐT Minh
('session_id_4', 4, '27.72.3.3', 'Mozilla/5.0... (Macintosh; Intel Mac OS X 10_15_7)', 'long_base64_payload_string', 1728886403), -- GV Hưng
('session_id_5', 9, '118.70.2.3', 'Mozilla/5.0... (iPhone; CPU iPhone OS 17_0 like Mac OS X)', 'long_base64_payload_string', 1728886404), -- SV Bình
('session_id_6', NULL, '1.53.4.4', 'Mozilla/5.0... (Windows NT 10.0; Win64; x64)', 'long_base64_payload_string', 1728886405), -- Guest
('session_id_7', 2, '42.112.5.5', 'Mozilla/5.0... (Windows NT 10.0; Win64; x64)', 'long_base64_payload_string', 1728886406), -- Trưởng khoa Hằng
('session_id_8', 10, '14.225.6.6', 'Mozilla/5.0... (Linux; Android 12)', 'long_base64_payload_string', 1728886407), -- SV Cường
('session_id_9', 5, '27.72.3.4', 'Mozilla/5.0... (Windows NT 10.0; Win64; x64)', 'long_base64_payload_string', 1728886408), -- GV Hà
('session_id_10', 6, '113.160.1.3', 'Mozilla/5.0... (Windows NT 10.0; Win64; x64)', 'long_base64_payload_string', 1728886409); -- GV Quỳnh

INSERT INTO `personal_access_tokens` (id, tokenable_type, tokenable_id, name, token, abilities, created_at, updated_at) VALUES
(1, 'App\\Models\\User', 1, 'tlu-admin-api', 'hash1_unique_token_string', '["*"]', NOW(), NOW()),
(2, 'App\\Models\\User', 4, 'tlu-teacher-mobile', 'hash2_unique_token_string', '["read:schedule","write:attendance"]', NOW(), NOW()),
(3, 'App\\Models\\User', 8, 'tlu-student-mobile', 'hash3_unique_token_string', '["read:schedule","read:grades"]', NOW(), NOW()),
(4, 'App\\Models\\User', 3, 'tlu-training-export', 'hash4_unique_token_string', '["read:export"]', NOW(), NOW()),
(5, 'App\\Models\\User', 1, 'test-token', 'hash5_unique_token_string', '["*"]', NOW(), NOW()),
(6, 'App\\Models\\User', 5, 'tlu-teacher-mobile', 'hash6_unique_token_string', '["read:schedule","write:attendance"]', NOW(), NOW()),
(7, 'App\\Models\\User', 9, 'tlu-student-mobile', 'hash7_unique_token_string', '["read:schedule","read:grades"]', NOW(), NOW()),
(8, 'App\\Models\\User', 10, 'tlu-student-mobile', 'hash8_unique_token_string', '["read:schedule","read:grades"]', NOW(), NOW()),
(9, 'App\\Models\\User', 2, 'tlu-admin-api', 'hash9_unique_token_string', '["*"]', NOW(), NOW()),
(10, 'App\\Models\\User', 7, 'tlu-teacher-mobile', 'hash10_unique_token_string', '["read:schedule","write:attendance"]', NOW(), NOW());