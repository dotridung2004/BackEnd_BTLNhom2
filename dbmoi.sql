-- Tắt kiểm tra khóa ngoại để có thể xóa bảng
SET FOREIGN_KEY_CHECKS=0;

-- Xóa sạch dữ liệu cũ trong các bảng
TRUNCATE TABLE `personal_access_tokens`;
TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `password_reset_tokens`;
TRUNCATE TABLE `makeup_classes`;
TRUNCATE TABLE `leave_requests`;
TRUNCATE TABLE `attendances`;
TRUNCATE TABLE `schedules`;
TRUNCATE TABLE `class_course_assignments`;
TRUNCATE TABLE `class_student`;
TRUNCATE TABLE `rooms`;
TRUNCATE TABLE `courses`;
TRUNCATE TABLE `classes`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `majors`;
TRUNCATE TABLE `divisions`;
TRUNCATE TABLE `departments`;

-- Bật lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS=1;

-- Bắt đầu chèn dữ liệu
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

INSERT INTO `classes` (id, name, semester, department_id, created_at, updated_at) VALUES
(1, '65CNTT1', '1', 1, NOW(), NOW()),
(2, '65CNTT2', '1', 1, NOW(), NOW()),
(3, '65KTPM1', '1', 1, NOW(), NOW()),
(4, '65HTTT1', '1', 1, NOW(), NOW()),
(5, '65QTKD1', '1', 2, NOW(), NOW()),
(6, '65KT1', '1', 2, NOW(), NOW()),
(7, '65XD1', '1', 3, NOW(), NOW()),
(8, '64CNTT1', '3', 1, NOW(), NOW()), -- Lớp năm 2, kỳ 3
(9, '64KTPM1', '3', 1, NOW(), NOW()),
(10, '66CNTT1', '1', 1, NOW(), NOW()); -- Lớp khóa mới

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

-- 
-- (SỬA) KHỐI 1: BẢNG `rooms` ĐÃ ĐƯỢC CHUYỂN SANG CẤU TRÚC MỚI
-- 
INSERT INTO `rooms` (id, `name`, building, floor, capacity, room_type, status, description, created_at, updated_at) VALUES
(1, 'K1-201', 'K1', 2, 60, 'Lí thuyết', 'Hoạt động', 'Nhà K1, Tầng 2', NOW(), NOW()),
(2, 'K1-202', 'K1', 2, 60, 'Lí thuyết', 'Hoạt động', 'Nhà K1, Tầng 2', NOW(), NOW()),
(3, 'C1-301', 'C1', 3, 40, 'Thực hành', 'Hoạt động', 'Nhà C1, Tầng 3, Phòng máy', NOW(), NOW()),
(4, 'C1-302', 'C1', 3, 40, 'Thực hành', 'Hoạt động', 'Nhà C1, Tầng 3, Phòng máy', NOW(), NOW()),
(5, 'C2-405', 'C2', 4, 150, 'Hội trường', 'Hoạt động', 'Nhà C2, Tầng 4, Giảng đường lớn', NOW(), NOW()),
(6, 'C5-501', 'C5', 5, 80, 'Lí thuyết', 'Hoạt động', 'Nhà C5, Tầng 5', NOW(), NOW()),
(7, 'C5-502', 'C5', 5, 80, 'Lí thuyết', 'Hoạt động', 'Nhà C5, Tầng 5', NOW(), NOW()),
(8, 'K1-301', 'K1', 3, 60, 'Lí thuyết', 'Hoạt động', 'Nhà K1, Tầng 3', NOW(), NOW()),
(9, 'K1-302', 'K1', 3, 60, 'Lí thuyết', 'Hoạt động', 'Nhà K1, Tầng 3', NOW(), NOW()),
(10, 'H1-201', 'H1', 2, 200, 'Hội trường', 'Hoạt động', 'Hội trường H1, Tầng 2', NOW(), NOW());

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



INSERT INTO `departments` (id, name, head_id, created_at, updated_at) VALUES
(11, 'Viện Kỹ thuật Biển', NULL, NOW(), NOW()),
(12, 'Viện Sinh thái Môi trường và Biến đổi khí hậu', NULL, NOW(), NOW()),
(13, 'Khoa Tại chức', NULL, NOW(), NOW()),
(14, 'Trung tâm Giáo dục Thể chất và Quốc phòng', NULL, NOW(), NOW()),
(15, 'Phòng Quản lý Đào tạo', 3, NOW(), NOW()), -- Giả sử ThS. Minh (ID 3) là trưởng phòng
(16, 'Phòng Công tác Chính trị và Quản lý Sinh viên', NULL, NOW(), NOW()),
(17, 'Phòng Hợp tác Quốc tế', NULL, NOW(), NOW()),
(18, 'Phòng Khoa học và Công nghệ', NULL, NOW(), NOW()),
(19, 'Viện Thủy lợi và Môi trường', NULL, NOW(), NOW()),
(20, 'Phòng Thí nghiệm trọng điểm Quốc gia về Động lực học Sông Biển', NULL, NOW(), NOW());

INSERT INTO `divisions` (id, code, name, department_id, description, created_at, updated_at) VALUES
(11, 'KTPM', 'Bộ môn Kỹ thuật phần mềm', 1, 'Tách ra từ bộ môn CNPM (ví dụ)', NOW(), NOW()),
(12, 'TMDT', 'Bộ môn Thương mại điện tử', 2, 'Phụ trách các môn học về E-commerce.', NOW(), NOW()),
(13, 'LOGI', 'Bộ môn Logistics', 2, 'Phụ trách chuyên ngành Logistics.', NOW(), NOW()),
(14, 'VLXD', 'Bộ môn Vật liệu Xây dựng', 3, NULL, NOW(), NOW()),
(15, 'DIAKT', 'Bộ môn Địa kỹ thuật', 3, 'Phụ trách các môn nền móng, cơ đất.', NOW(), NOW()),
(16, 'KTMT', 'Bộ môn Kỹ thuật Môi trường', 7, NULL, NOW(), NOW()),
(17, 'CTN', 'Bộ môn Cấp thoát nước', 7, NULL, NOW(), NOW()),
(18, 'TDH', 'Bộ môn Tự động hóa', 6, 'Phụ trách ngành Tự động hóa.', NOW(), NOW()),
(19, 'KTD', 'Bộ môn Kỹ thuật Điện', 6, 'Phụ trách ngành Kỹ thuật Điện.', NOW(), NOW()),
(20, 'CKM', 'Bộ môn Chế tạo máy', 5, 'Phụ trách các môn về cơ khí chế tạo.', NOW(), NOW());

INSERT INTO `majors` (id, code, name, department_id, created_at, updated_at) VALUES
(11, '7520301', 'Kỹ thuật Môi trường', 7, NOW(), NOW()),
(12, '7580210', 'Kỹ thuật Cấp thoát nước', 7, NOW(), NOW()),
(13, '7520216', 'Kỹ thuật Điều khiển và Tự động hóa', 6, NOW(), NOW()),
(14, '7520201', 'Kỹ thuật Điện', 6, NOW(), NOW()),
(15, '7520103', 'Kỹ thuật Cơ khí', 5, NOW(), NOW()),
(16, '7520114', 'Kỹ thuật Cơ điện tử', 5, NOW(), NOW()),
(17, '7580208', 'Kỹ thuật Tài nguyên nước', 4, NOW(), NOW()),
(18, '7850101', 'Quản lý tài nguyên và môi trường', 7, NOW(), NOW()),
(19, '7340122', 'Thương mại điện tử', 2, NOW(), NOW()),
(20, '7220201', 'Ngôn ngữ Anh', 10, NOW(), NOW());

INSERT INTO `users` (id, name, first_name, last_name, email, password, phone_number, role, status, division_id, major_id, created_at, updated_at) VALUES
-- Teachers
(11, 'TS. Trần Anh Tuấn', 'Trần', 'Anh Tuấn', 'tuan.ta@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345611', 'teacher', 'active', 20, NULL, NOW(), NOW()),
(12, 'ThS. Vũ Thị Mai', 'Vũ', 'Thị Mai', 'mai.vt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345612', 'teacher', 'active', 18, NULL, NOW(), NOW()),
-- Students
(13, 'Phạm Văn Đức', 'Phạm', 'Văn Đức', '65XD1.duc.pv@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654313', 'student', 'active', NULL, 8, NOW(), NOW()),
(14, 'Hoàng Thị Lan', 'Hoàng', 'Thị Lan', '65XD1.lan.ht@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654314', 'student', 'active', NULL, 8, NOW(), NOW()),
(15, 'Lê Văn Hùng', 'Lê', 'Văn Hùng', '65CK1.hung.lv@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654315', 'student', 'active', NULL, 15, NOW(), NOW()),
(16, 'Đào Thu Hương', 'Đào', 'Thu Hương', '65TĐH1.huong.dt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654316', 'student', 'active', NULL, 13, NOW(), NOW()),
(17, 'Trịnh Văn Nam', 'Trịnh', 'Văn Nam', '65KTMT1.nam.tv@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654317', 'student', 'active', NULL, 11, NOW(), NOW()),
(18, 'Bùi Thị Hoa', 'Bùi', 'Thị Hoa', '65CNTT2.hoa.bt@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654318', 'student', 'active', NULL, 1, NOW(), NOW()),
(19, 'Nguyễn Anh Dũng', 'Nguyễn', 'Anh Dũng', '65QTKD1.dung.na@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654319', 'student', 'active', NULL, 5, NOW(), NOW()),
(20, 'Mai Văn Toàn', 'Mai', 'Văn Toàn', '65CNTT1.toan.mv@tlu.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654320', 'student', 'active', NULL, 1, NOW(), NOW());

INSERT INTO `classes` (id, name, semester, department_id, created_at, updated_at) VALUES
(11, '65XD2', '1', 3, NOW(), NOW()),
(12, '65CK1', '1', 5, NOW(), NOW()),
(13, '65TĐH1', '1', 6, NOW(), NOW()),
(14, '65KTMT1', '1', 7, NOW(), NOW()),
(15, '65TMĐT1', '1', 2, NOW(), NOW()),
(16, '64CNTT2', '3', 1, NOW(), NOW()),
(17, '64QTKD1', '3', 2, NOW(), NOW()),
(18, '64XD1', '3', 3, NOW(), NOW()),
(19, '66QTKD1', '1', 2, NOW(), NOW()),
(20, '66XD1', '1', 3, NOW(), NOW());

INSERT INTO `courses` (id, name, code, credits, department_id, division_id, created_at, updated_at) VALUES
(11, 'Cơ học lý thuyết 1', 'MEC101', 3, 5, 20, NOW(), NOW()),
(12, 'Kỹ thuật Điện', 'ELE101', 3, 6, 19, NOW(), NOW()),
(13, 'Mạch điện tử 1', 'ELE201', 3, 6, 19, NOW(), NOW()),
(14, 'Kỹ thuật Điều khiển tự động', 'CON201', 3, 6, 18, NOW(), NOW()),
(15, 'Cơ học kết cấu 1', 'CEE202', 3, 3, 7, NOW(), NOW()),
(16, 'Bê tông cốt thép 1', 'CEE301', 4, 3, 7, NOW(), NOW()),
(17, 'Quản lý dự án xây dựng', 'CEM401', 3, 3, 7, NOW(), NOW()),
(18, 'Kế toán tài chính 1', 'ACC201', 3, 2, 6, NOW(), NOW()),
(19, 'Lập trình Hướng đối tượng', 'CSE221', 3, 1, 1, NOW(), NOW()),
(20, 'An toàn thông tin', 'CSE450', 3, 1, 4, NOW(), NOW());

-- 
-- (SỬA) KHỐI 2: BẢNG `rooms` ĐÃ ĐƯỢC CHUYỂN SANG CẤU TRÚC MỚI
-- 
INSERT INTO `rooms` (id, `name`, building, floor, capacity, room_type, status, description, created_at, updated_at) VALUES
(11, 'K1-401', 'K1', 4, 60, 'Lí thuyết', 'Hoạt động', 'Nhà K1, Tầng 4', NOW(), NOW()),
(12, 'K1-402', 'K1', 4, 60, 'Lí thuyết', 'Hoạt động', 'Nhà K1, Tầng 4', NOW(), NOW()),
(13, 'C1-201', 'C1', 2, 50, 'Lí thuyết', 'Hoạt động', 'Nhà C1, Tầng 2', NOW(), NOW()),
(14, 'C1-202', 'C1', 2, 50, 'Lí thuyết', 'Hoạt động', 'Nhà C1, Tầng 2', NOW(), NOW()),
(15, 'C5-301', 'C5', 3, 80, 'Lí thuyết', 'Hoạt động', 'Nhà C5, Tầng 3', NOW(), NOW()),
(16, 'C5-302', 'C5', 3, 80, 'Lí thuyết', 'Hoạt động', 'Nhà C5, Tầng 3', NOW(), NOW()),
(17, 'XCK-1', 'XCK', 1, 30, 'Xưởng', 'Hoạt động', 'Nhà Xưởng Cơ khí', NOW(), NOW()),
(18, 'C1-401', 'C1', 4, 40, 'Thực hành', 'Hoạt động', 'Nhà C1, Tầng 4, Lab Tự động hóa', NOW(), NOW()),
(19, 'K1-101', 'K1', 1, 40, 'Thực hành', 'Hoạt động', 'Nhà K1, Tầng 1, Lab Sức bền', NOW(), NOW()),
(20, 'SVD-01', 'SVD', 1, 1000, 'Sân bãi', 'Hoạt động', 'Sân Vận Động TLU (học GDTC)', NOW(), NOW());

INSERT INTO `class_student` (id, class_model_id, student_id, created_at, updated_at) VALUES
(11, 7, 13, NOW(), NOW()), -- SV Đức (13) vào lớp 65XD1 (7)
(12, 7, 14, NOW(), NOW()), -- SV Lan (14) vào lớp 65XD1 (7)
(13, 12, 15, NOW(), NOW()), -- SV Hùng (15) vào lớp 65CK1 (12)
(14, 13, 16, NOW(), NOW()), -- SV Hương (16) vào lớp 65TĐH1 (13)
(15, 14, 17, NOW(), NOW()), -- SV Nam (17) vào lớp 65KTMT1 (14)
(16, 2, 18, NOW(), NOW()), -- SV Hoa (18) vào lớp 65CNTT2 (2)
(17, 5, 19, NOW(), NOW()), -- SV Dũng (19) vào lớp 65QTKD1 (5)
(18, 1, 20, NOW(), NOW()), -- SV Toàn (20) vào lớp 65CNTT1 (1)
(19, 8, 8, NOW(), NOW()), -- SV An (8) học lớp 64CNTT1 (8)
(20, 9, 10, NOW(), NOW()); -- SV Cường (10) học lớp 64KTPM1 (9)

INSERT INTO `class_course_assignments` (id, class_id, course_id, teacher_id, semester, created_at, updated_at) VALUES
(11, 12, 11, 11, '2023-2024_1', NOW(), NOW()), -- Lớp 65CK1 (12), Môn Cơ LT 1 (11), GV Tuấn (11)
(12, 13, 14, 12, '2023-2024_1', NOW(), NOW()), -- Lớp 65TĐH1 (13), Môn KTĐK (14), GV Mai (12)
(13, 13, 13, 12, '2023-2024_1', NOW(), NOW()), -- Lớp 65TĐH1 (13), Môn Mạch ĐT (13), GV Mai (12)
(14, 7, 15, 7, '2023-2024_1', NOW(), NOW()), -- Lớp 65XD1 (7), Môn Cơ KC1 (15), GV Dũng (7)
(15, 7, 16, 7, '2023-2024_1', NOW(), NOW()), -- Lớp 65XD1 (7), Môn BTCT1 (16), GV Dũng (7)
(16, 1, 19, 4, '2023-2024_1', NOW(), NOW()), -- Lớp 65CNTT1 (1), Môn OOP (19), GV Hưng (4)
(17, 2, 19, 4, '2023-2024_1', NOW(), NOW()), -- Lớp 65CNTT2 (2), Môn OOP (19), GV Hưng (4)
(18, 1, 20, 5, '2023-2024_1', NOW(), NOW()), -- Lớp 65CNTT1 (1), Môn ATTT (20), GV Hà (5)
(19, 5, 18, 6, '2023-2024_1', NOW(), NOW()), -- Lớp 65QTKD1 (5), Môn Kế toán TC (18), GV Quỳnh (6)
(20, 18, 17, 7, '2023-2024_1', NOW(), NOW()); -- Lớp 64XD1 (18), Môn QLDAXD (17), GV Dũng (7)

INSERT INTO `schedules` (id, class_course_assignment_id, room_id, `date`, session, topic, status, created_at, updated_at) VALUES
(11, 11, 17, '2024-10-07', '1-3', 'Chương 1: Tĩnh học vật rắn', 'taught', NOW(), NOW()),
(12, 12, 18, '2024-10-07', '4-6', 'Chương 1: Giới thiệu Hàm truyền', 'taught', NOW(), NOW()),
(13, 14, 11, '2024-10-08', '1-3', 'Chương 1: Hệ tĩnh định', 'taught', NOW(), NOW()),
(14, 16, 3, '2024-10-08', '7-9', 'Bài 1: Lớp và Đối tượng', 'taught', NOW(), NOW()),
(15, 17, 4, '2024-10-09', '1-3', 'Bài 1: Lớp và Đối tượng (lớp 2)', 'taught', NOW(), NOW()),
(16, 11, 17, '2024-10-14', '1-3', 'Chương 2: Động học', 'scheduled', NOW(), NOW()),
(17, 12, 18, '2024-10-14', '4-6', 'Chương 2: Khảo sát hệ thống', 'scheduled', NOW(), NOW()),
(18, 14, 11, '2024-10-15', '1-3', 'Chương 2: Hệ siêu tĩnh', 'scheduled', NOW(), NOW()),
(19, 16, 3, '2024-10-15', '7-9', 'Bài 2: Tính kế thừa', 'scheduled', NOW(), NOW()),
(20, 17, 4, '2024-10-16', '1-3', 'Bài 2: Tính kế thừa (lớp 2)', 'scheduled', NOW(), NOW());

INSERT INTO `attendances` (id, schedule_id, student_id, status, note, created_at, updated_at) VALUES
(11, 11, 15, 'present', NULL, NOW(), NOW()), -- Lịch 11 (65CK1), SV Hùng (15)
(12, 12, 16, 'present', NULL, NOW(), NOW()), -- Lịch 12 (65TĐH1), SV Hương (16)
(13, 13, 13, 'late', 'Xe hỏng', NOW(), NOW()), -- Lịch 13 (65XD1), SV Đức (13)
(14, 13, 14, 'present', NULL, NOW(), NOW()), -- Lịch 13 (65XD1), SV Lan (14)
(15, 14, 8, 'present', NULL, NOW(), NOW()), -- Lịch 14 (65CNTT1 - OOP), SV An (8)
(16, 14, 9, 'absent', 'Về quê', NOW(), NOW()), -- Lịch 14 (65CNTT1 - OOP), SV Bình (9)
(17, 14, 20, 'present', NULL, NOW(), NOW()), -- Lịch 14 (65CNTT1 - OOP), SV Toàn (20)
(18, 15, 18, 'present', NULL, NOW(), NOW()), -- Lịch 15 (65CNTT2 - OOP), SV Hoa (18)
(19, 1, 15, 'absent', 'Nhầm lịch', NOW(), NOW()), -- SV Hùng (15) đi nhầm lịch (1)
(20, 4, 18, 'absent', 'Nhầm lịch', NOW(), NOW()); -- SV Hoa (18) đi nhầm lịch (4)

INSERT INTO `leave_requests` (id, teacher_id, schedule_id, reason, document_url, status, approved_by, created_at, updated_at) VALUES
(11, 4, 14, 'Nghỉ ốm (đã báo PĐT)', 'http://tlu.edu.vn/docs/sick_leave_11.pdf', 'approved', 1, NOW(), NOW()), -- GV Hưng (4) nghỉ buổi (14)
(12, 7, 13, 'Họp bộ môn Xây dựng', NULL, 'approved', 1, NOW(), NOW()), -- GV Dũng (7) nghỉ buổi (13)
(13, 11, 16, 'Việc gia đình đột xuất', NULL, 'pending', NULL, NOW(), NOW()), -- GV Tuấn (11) xin nghỉ buổi (16)
(14, 12, 17, 'Dự hội thảo Tự động hóa', 'http://tlu.edu.vn/docs/conf_tdh.pdf', 'pending', NULL, NOW(), NOW()), -- GV Mai (12) xin nghỉ buổi (17)
(15, 4, 19, 'Trùng lịch coi thi K64', NULL, 'rejected', 3, NOW(), NOW()), -- GV Hưng (4) xin nghỉ buổi (19)
(16, 5, 18, 'Nghỉ ốm', NULL, 'pending', NULL, NOW(), NOW()), -- GV Hà (5) xin nghỉ buổi (18) (Phân công 18 là GV Hà)
(17, 6, 19, 'Việc cá nhân', NULL, 'pending', NULL, NOW(), NOW()), -- GV Quỳnh (6) xin nghỉ buổi (19) (Logic sai, nhưng để đủ data)
(18, 7, 20, 'Họp khoa Xây dựng', NULL, 'pending', NULL, NOW(), NOW()), -- GV Dũng (7) xin nghỉ buổi (20) (Logic sai)
(19, 11, 11, 'Đón con ốm', NULL, 'rejected', 1, NOW(), NOW()), -- GV Tuấn (11) xin nghỉ buổi (11) (đã dạy)
(20, 12, 12, 'Cảm cúm', NULL, 'approved', 1, NOW(), NOW()); -- GV Mai (12) nghỉ buổi (12)

-- CHẠY 3 LỆNH NÀY TRƯỚC
INSERT INTO `schedules` (id, class_course_assignment_id, room_id, `date`, session, topic, status, created_at, updated_at) VALUES
(21, 16, 3, '2024-10-22', '7-9', 'Bù: Bài 1: Lớp và Đối tượng', 'makeup', NOW(), NOW()),
(22, 14, 11, '2024-10-22', '1-3', 'Bù: Chương 1: Hệ tĩnh định', 'makeup', NOW(), NOW()),
(23, 12, 18, '2024-10-21', '4-6', 'Bù: Chương 1: Giới thiệu Hàm truyền', 'makeup', NOW(), NOW());

-- SAU ĐÓ CHẠY 10 LỆNH NÀY
INSERT INTO `makeup_classes` (id, teacher_id, original_schedule_id, new_schedule_id, status, created_at, updated_at) VALUES
(11, 4, 14, 21, 'approved', NOW(), NOW()), -- GV Hưng (4) bù buổi (14) bằng lịch (21)
(12, 7, 13, 22, 'approved', NOW(), NOW()), -- GV Dũng (7) bù buổi (13) bằng lịch (22)
(13, 12, 12, 23, 'approved', NOW(), NOW()), -- GV Mai (12) bù buổi (12) bằng lịch (23)
(14, 11, 16, 21, 'pending', NOW(), NOW()), -- GV Tuấn (11) xin bù buổi (16) (lịch 21 đã bị GV Hưng đăng ký)
(15, 12, 17, 23, 'pending', NOW(), NOW()), -- GV Mai (12) xin bù buổi (17) (lịch 23 đã bị chính GV Mai đăng ký)
(16, 4, 1, 11, 'done', NOW(), NOW()), -- (Dữ liệu mẫu ngẫu nhiên)
(17, 5, 2, 12, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu ngẫu nhiên)
(18, 6, 4, 13, 'pending', NOW(), NOW()), -- (Dữ liệu mẫu ngẫu nhiên)
(19, 7, 5, 14, 'done', NOW(), NOW()), -- (Dữ liệu mẫu ngẫu nhiên)
(20, 4, 6, 15, 'pending', NOW(), NOW()); -- (Dữ liệu mẫu ngẫu nhiên)

INSERT INTO `password_reset_tokens` (email, token, created_at) VALUES
('tuan.ta@tlu.edu.vn', 'k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', NOW()),
('mai.vt@tlu.edu.vn', 'l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7', NOW()),
('65XD1.duc.pv@tlu.edu.vn', 'm3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8', NOW()),
('65XD1.lan.ht@tlu.edu.vn', 'n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9', NOW()),
('65CK1.hung.lv@tlu.edu.vn', 'o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0', NOW()),
('65TĐH1.huong.dt@tlu.edu.vn', 'p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1', NOW()),
('65KTMT1.nam.tv@tlu.edu.vn', 'q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2', NOW()),
('65CNTT2.hoa.bt@tlu.edu.vn', 'r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2g3', NOW()),
('65QTKD1.dung.na@tlu.edu.vn', 's9t0u1v2w3x4y5z6a7b8c9d0e1f2g3h4', NOW()),
('65CNTT1.toan.mv@tlu.edu.vn', 't0u1v2w3x4y5z6a7b8c9d0e1f2g3h4i5', NOW());