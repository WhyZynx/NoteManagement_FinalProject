-- Thêm vào bảng notes các cột font_size, font_style và note_color để lưu trữ thông tin về kích thước chữ, kiểu chữ và màu sắc của ghi chú.
ALTER TABLE notes
ADD font_size INT DEFAULT 16,
ADD font_style VARCHAR(100) DEFAULT 'Arial',
ADD note_color VARCHAR(20) DEFAULT '#ffffff';
ADD view_mode ENUM('grid','list') DEFAULT 'grid';
