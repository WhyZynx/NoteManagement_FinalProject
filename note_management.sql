-- Thêm vào bảng notes các cột font_size, font_style và note_color để lưu trữ thông tin về kích thước chữ, kiểu chữ và màu sắc của ghi chú.
ALTER TABLE notes
ADD font_size INT DEFAULT 16,
ADD font_style VARCHAR(100) DEFAULT 'Arial',
ADD note_color VARCHAR(20) DEFAULT '#ffffff';
ADD view_mode ENUM('grid','list') DEFAULT 'grid';

-- Thay đổi bảng labels
ALTER TABLE labels
    CHANGE COLUMN name label_name VARCHAR(100) NOT NULL,
    ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Themes
ALTER TABLE `users` 
  MODIFY `theme_mode` enum('light','dark','hologram','custom','gradient') DEFAULT 'light',
  ADD `theme_color` VARCHAR(50) DEFAULT '#5385c7';

-- Lock
ALTER TABLE notes ADD COLUMN is_locked TINYINT(1) DEFAULT 0;
