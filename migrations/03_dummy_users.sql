-- Create dummy users for testing
-- Login hanya dengan email dan role (tanpa password)
INSERT INTO users (email, password_hash, role) VALUES
('peserta@test.com', NULL, 'PESERTA'),
('panitia@test.com', NULL, 'PANITIA'),
('juri@test.com', NULL, 'JURI'),
('peserta2@test.com', NULL, 'PESERTA'),
('juri2@test.com', NULL, 'JURI');

-- Note: password_hash di-set NULL karena login tidak memerlukan password
-- Cukup masukkan email dan pilih role untuk login
