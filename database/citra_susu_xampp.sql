-- CITRA SUSU - MySQL / XAMPP
-- Import melalui phpMyAdmin. Database: citra_susu
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
CREATE DATABASE IF NOT EXISTS citra_susu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE citra_susu;

DROP TABLE IF EXISTS migrations;
DROP TABLE IF EXISTS store_settings;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS return_details;
DROP TABLE IF EXISTS `returns`;
DROP TABLE IF EXISTS goods_receipt_details;
DROP TABLE IF EXISTS goods_receipts;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS product_batches;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS job_batches;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS cache;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL UNIQUE,
 email_verified_at TIMESTAMP NULL, password VARCHAR(255) NOT NULL, remember_token VARCHAR(100) NULL,
 role VARCHAR(20) NOT NULL DEFAULT 'buyer', phone VARCHAR(30) NULL, address TEXT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1, chat_enabled TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL, INDEX users_role_index(role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE password_reset_tokens(email VARCHAR(255) PRIMARY KEY, token VARCHAR(255) NOT NULL, created_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE sessions(id VARCHAR(255) PRIMARY KEY,user_id BIGINT UNSIGNED NULL,ip_address VARCHAR(45) NULL,user_agent TEXT NULL,payload LONGTEXT NOT NULL,last_activity INT NOT NULL,INDEX sessions_user_id_index(user_id),INDEX sessions_last_activity_index(last_activity)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE cache(`key` VARCHAR(255) PRIMARY KEY,value MEDIUMTEXT NOT NULL,expiration INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE cache_locks(`key` VARCHAR(255) PRIMARY KEY,owner VARCHAR(255) NOT NULL,expiration INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE jobs(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,queue VARCHAR(255) NOT NULL,payload LONGTEXT NOT NULL,attempts TINYINT UNSIGNED NOT NULL,reserved_at INT UNSIGNED NULL,available_at INT UNSIGNED NOT NULL,created_at INT UNSIGNED NOT NULL,INDEX jobs_queue_index(queue)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE job_batches(id VARCHAR(255) PRIMARY KEY,name VARCHAR(255) NOT NULL,total_jobs INT NOT NULL,pending_jobs INT NOT NULL,failed_jobs INT NOT NULL,failed_job_ids LONGTEXT NOT NULL,options MEDIUMTEXT NULL,cancelled_at INT NULL,created_at INT NOT NULL,finished_at INT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE failed_jobs(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,uuid VARCHAR(255) NOT NULL UNIQUE,connection TEXT NOT NULL,queue TEXT NOT NULL,payload LONGTEXT NOT NULL,exception LONGTEXT NOT NULL,failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255) NOT NULL UNIQUE,slug VARCHAR(255) NOT NULL UNIQUE,description TEXT NULL,icon VARCHAR(255) NULL,is_active TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE products(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,category_id BIGINT UNSIGNED NULL,sku VARCHAR(255) NOT NULL UNIQUE,name VARCHAR(255) NOT NULL,description TEXT NULL,price DECIMAL(12,2) NOT NULL DEFAULT 0,purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,unit VARCHAR(30) NOT NULL DEFAULT 'pcs',stock INT UNSIGNED NOT NULL DEFAULT 0,min_stock INT UNSIGNED NOT NULL DEFAULT 5,image VARCHAR(255) NULL,is_active TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,deleted_at TIMESTAMP NULL,CONSTRAINT products_category_fk FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE suppliers(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(255) NOT NULL,pic VARCHAR(255) NULL,phone VARCHAR(30) NULL,email VARCHAR(255) NULL,address TEXT NULL,notes TEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,deleted_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE product_batches(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,product_id BIGINT UNSIGNED NOT NULL,supplier_id BIGINT UNSIGNED NULL,batch_code VARCHAR(255) NOT NULL,quantity INT UNSIGNED NOT NULL DEFAULT 0,production_date DATE NULL,received_date DATE NOT NULL,expiry_date DATE NOT NULL,status VARCHAR(20) NOT NULL DEFAULT 'active',purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,checked_by BIGINT UNSIGNED NULL,checked_at TIMESTAMP NULL,notes TEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,INDEX product_batches_expiry_index(expiry_date),INDEX product_batches_product_expiry_index(product_id,expiry_date),CONSTRAINT batches_product_fk FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,CONSTRAINT batches_supplier_fk FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,CONSTRAINT batches_checked_fk FOREIGN KEY(checked_by) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE stock_movements(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,product_id BIGINT UNSIGNED NOT NULL,batch_id BIGINT UNSIGNED NULL,user_id BIGINT UNSIGNED NULL,type VARCHAR(30) NOT NULL,quantity INT NOT NULL,balance INT UNSIGNED NOT NULL DEFAULT 0,reference VARCHAR(255) NULL,notes TEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT movement_product_fk FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,CONSTRAINT movement_batch_fk FOREIGN KEY(batch_id) REFERENCES product_batches(id) ON DELETE SET NULL,CONSTRAINT movement_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE goods_receipts(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,receipt_number VARCHAR(255) NOT NULL UNIQUE,supplier_id BIGINT UNSIGNED NOT NULL,user_id BIGINT UNSIGNED NOT NULL,received_at DATE NOT NULL,total DECIMAL(14,2) NOT NULL DEFAULT 0,proof VARCHAR(255) NULL,notes TEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT receipts_supplier_fk FOREIGN KEY(supplier_id) REFERENCES suppliers(id),CONSTRAINT receipts_user_fk FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE goods_receipt_details(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,goods_receipt_id BIGINT UNSIGNED NOT NULL,product_id BIGINT UNSIGNED NOT NULL,batch_id BIGINT UNSIGNED NULL,quantity INT UNSIGNED NOT NULL,purchase_price DECIMAL(12,2) NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT receipt_detail_receipt_fk FOREIGN KEY(goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,CONSTRAINT receipt_detail_product_fk FOREIGN KEY(product_id) REFERENCES products(id),CONSTRAINT receipt_detail_batch_fk FOREIGN KEY(batch_id) REFERENCES product_batches(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `returns`(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,return_number VARCHAR(255) NOT NULL UNIQUE,supplier_id BIGINT UNSIGNED NULL,user_id BIGINT UNSIGNED NOT NULL,status VARCHAR(25) NOT NULL DEFAULT 'pending',proof VARCHAR(255) NULL,reason TEXT NOT NULL,admin_note TEXT NULL,approved_at TIMESTAMP NULL,approved_by BIGINT UNSIGNED NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT returns_supplier_fk FOREIGN KEY(supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,CONSTRAINT returns_user_fk FOREIGN KEY(user_id) REFERENCES users(id),CONSTRAINT returns_approver_fk FOREIGN KEY(approved_by) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE return_details(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,return_id BIGINT UNSIGNED NOT NULL,product_id BIGINT UNSIGNED NOT NULL,batch_id BIGINT UNSIGNED NULL,quantity INT UNSIGNED NOT NULL,unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT return_detail_return_fk FOREIGN KEY(return_id) REFERENCES `returns`(id) ON DELETE CASCADE,CONSTRAINT return_detail_product_fk FOREIGN KEY(product_id) REFERENCES products(id),CONSTRAINT return_detail_batch_fk FOREIGN KEY(batch_id) REFERENCES product_batches(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE orders(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_number VARCHAR(255) NOT NULL UNIQUE,user_id BIGINT UNSIGNED NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'pending',delivery_method VARCHAR(30) NOT NULL DEFAULT 'internal',payment_method VARCHAR(30) NOT NULL DEFAULT 'cod',payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid',subtotal DECIMAL(14,2) NOT NULL,shipping_cost DECIMAL(14,2) NOT NULL DEFAULT 0,total DECIMAL(14,2) NOT NULL,shipping_address TEXT NOT NULL,phone VARCHAR(30) NOT NULL,notes TEXT NULL,confirmed_at TIMESTAMP NULL,completed_at TIMESTAMP NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,INDEX orders_status_index(status),CONSTRAINT orders_user_fk FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE order_details(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id BIGINT UNSIGNED NOT NULL,product_id BIGINT UNSIGNED NOT NULL,quantity INT UNSIGNED NOT NULL,unit_price DECIMAL(12,2) NOT NULL,subtotal DECIMAL(14,2) NOT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT order_details_order_fk FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,CONSTRAINT order_details_product_fk FOREIGN KEY(product_id) REFERENCES products(id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE payments(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id BIGINT UNSIGNED NOT NULL,method VARCHAR(30) NOT NULL,amount DECIMAL(14,2) NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'pending',proof VARCHAR(255) NULL,verified_by BIGINT UNSIGNED NULL,paid_at TIMESTAMP NULL,note TEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT payments_order_fk FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,CONSTRAINT payments_verifier_fk FOREIGN KEY(verified_by) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE deliveries(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id BIGINT UNSIGNED NOT NULL UNIQUE,courier_id BIGINT UNSIGNED NULL,status VARCHAR(30) NOT NULL DEFAULT 'waiting',tracking_number VARCHAR(255) NULL,courier_note TEXT NULL,assigned_at TIMESTAMP NULL,started_at TIMESTAMP NULL,delivered_at TIMESTAMP NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT deliveries_order_fk FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,CONSTRAINT deliveries_courier_fk FOREIGN KEY(courier_id) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE conversations(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,buyer_id BIGINT UNSIGNED NOT NULL,assigned_to BIGINT UNSIGNED NULL,subject VARCHAR(255) NULL,status VARCHAR(20) NOT NULL DEFAULT 'open',last_message_at TIMESTAMP NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,INDEX conversations_buyer_status_index(buyer_id,status),CONSTRAINT conversations_buyer_fk FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE,CONSTRAINT conversations_assignee_fk FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE messages(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,conversation_id BIGINT UNSIGNED NOT NULL,sender_id BIGINT UNSIGNED NOT NULL,body TEXT NOT NULL,read_at TIMESTAMP NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT messages_conversation_fk FOREIGN KEY(conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,CONSTRAINT messages_sender_fk FOREIGN KEY(sender_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE notifications(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,type VARCHAR(50) NOT NULL,title VARCHAR(255) NOT NULL,body TEXT NULL,url VARCHAR(255) NULL,read_at TIMESTAMP NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT notifications_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE activity_logs(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NULL,action VARCHAR(255) NOT NULL,subject_type VARCHAR(255) NULL,subject_id BIGINT UNSIGNED NULL,description TEXT NULL,ip_address VARCHAR(45) NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL,CONSTRAINT activity_user_fk FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE store_settings(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,`key` VARCHAR(255) NOT NULL UNIQUE,`value` LONGTEXT NULL,created_at TIMESTAMP NULL,updated_at TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migration ledger: import SQL first, then `php artisan migrate` will not recreate tables.
CREATE TABLE migrations(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,migration VARCHAR(255) NOT NULL,batch INT NOT NULL);
INSERT INTO migrations(migration,batch) VALUES
('0001_01_01_000000_create_users_table',1),
('0001_01_01_000001_create_cache_table',1),
('0001_01_01_000002_create_jobs_table',1),
('2026_11_09_000001_add_role_fields_to_users_table',2),
('2026_11_09_000002_create_citra_susu_tables',2);

-- Demo accounts (password for all accounts: password)
INSERT INTO users(name,email,password,role,phone,is_active,chat_enabled,created_at,updated_at) VALUES
('Admin Citra Susu','admin@citrasusu.test','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','081234567890',1,1,NOW(),NOW()),
('Siti Karyawan','karyawan@citrasusu.test','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','employee','081234567891',1,1,NOW(),NOW()),
('Budi Kurir','kurir@citrasusu.test','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','courier','081234567892',1,0,NOW(),NOW()),
('Bunda Suci','pembeli@citrasusu.test','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','buyer','081234567893',1,0,NOW(),NOW());
INSERT INTO categories(name,slug,is_active,created_at,updated_at) VALUES
('Susu Bayi','susu-bayi',1,NOW(),NOW()),('Popok Bayi','popok-bayi',1,NOW(),NOW()),('Perlengkapan Bayi','perlengkapan-bayi',1,NOW(),NOW()),('Perawatan Bayi','perawatan-bayi',1,NOW(),NOW()),('Kebutuhan Rumah Tangga','kebutuhan-rumah-tangga',1,NOW(),NOW());
INSERT INTO products(category_id,sku,name,description,price,purchase_price,unit,stock,min_stock,is_active,created_at,updated_at) VALUES
(1,'SUS-001','SGM Ananda 0-6 Bulan 400g','Produk pilihan Citra Susu untuk kebutuhan keluarga.',85000,63750,'box',20,5,1,NOW(),NOW()),
(1,'SUS-002','Dancow Fortigro 800g','Produk pilihan Citra Susu untuk kebutuhan keluarga.',92000,69000,'box',20,5,1,NOW(),NOW()),
(2,'POP-001','MamyPoko Pants M 20 pcs','Produk pilihan Citra Susu untuk kebutuhan keluarga.',48000,36000,'pack',20,5,1,NOW(),NOW()),
(2,'POP-002','Sweety Bronze M 22 pcs','Produk pilihan Citra Susu untuk kebutuhan keluarga.',43000,32250,'pack',20,5,1,NOW(),NOW()),
(4,'PRW-001','Minyak Telon 60ml','Produk pilihan Citra Susu untuk kebutuhan keluarga.',25000,18750,'botol',20,5,1,NOW(),NOW()),
(4,'PRW-002','Baby Oil 100ml','Produk pilihan Citra Susu untuk kebutuhan keluarga.',22000,16500,'botol',20,5,1,NOW(),NOW()),
(3,'BYI-001','Botol Susu 120ml','Produk pilihan Citra Susu untuk kebutuhan keluarga.',35000,26250,'pcs',20,5,1,NOW(),NOW()),
(3,'BYI-002','Tisu Basah 50 sheets','Produk pilihan Citra Susu untuk kebutuhan keluarga.',18000,13500,'pack',20,5,1,NOW(),NOW());
INSERT INTO suppliers(name,pic,phone,address,notes,created_at,updated_at) VALUES ('Supplier Demo','PIC Supplier','081200000001','Binjai, Sumatera Utara','Data awal untuk pengujian',NOW(),NOW());
INSERT INTO product_batches(product_id,supplier_id,batch_code,quantity,received_date,expiry_date,purchase_price,status,created_at,updated_at) VALUES
(1,1,'B-SUS001-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),63750,'active',NOW(),NOW()),
(2,1,'B-SUS002-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),69000,'active',NOW(),NOW()),
(3,1,'B-POP001-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),36000,'active',NOW(),NOW()),
(4,1,'B-POP002-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),32250,'active',NOW(),NOW()),
(5,1,'B-PRW001-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),18750,'active',NOW(),NOW()),
(6,1,'B-PRW002-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),16500,'active',NOW(),NOW()),
(7,1,'B-BYI001-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),26250,'active',NOW(),NOW()),
(8,1,'B-BYI002-01',20,CURDATE(),DATE_ADD(CURDATE(),INTERVAL 18 MONTH),13500,'active',NOW(),NOW());
INSERT INTO stock_movements(product_id,batch_id,user_id,type,quantity,balance,reference,notes,created_at,updated_at) VALUES
(1,1,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW()),(2,2,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW()),
(3,3,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW()),(4,4,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW()),
(5,5,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW()),(6,6,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW()),
(7,7,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW()),(8,8,1,'in',20,20,'SEED','Stok awal demo',NOW(),NOW());
INSERT INTO store_settings(`key`,`value`,created_at,updated_at) VALUES
('store_name','Citra Susu',NOW(),NOW()),('store_address','Binjai, Sumatera Utara',NOW(),NOW()),('store_phone','081234567890',NOW(),NOW()),('internal_delivery_fee','5000',NOW(),NOW()),('expiry_alert_days','30',NOW(),NOW());
SET FOREIGN_KEY_CHECKS=1;
