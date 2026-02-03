SET FOREIGN_KEY_CHECKS=0;


CREATE TABLE `account_lockouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `locked_until` datetime NOT NULL,
  `attempt_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_locked_until` (`locked_until`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_by_username` varchar(100) NOT NULL,
  `created_by_role` enum('admin','staff','user') NOT NULL,
  `file_size` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `backup_path` varchar(500) DEFAULT NULL,
  `status` enum('success','failed','deleted') DEFAULT 'success',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by_user_id`),
  KEY `idx_filename` (`filename`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_backup_logs_role` (`created_by_role`),
  CONSTRAINT `fk_backup_user` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO backup_logs VALUES("1","backup_2026-02-03_12-44-18.sql","30","Gab","admin","37462","C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_12-44-18.sql","deleted","::1","2026-02-03 12:44:18");
INSERT INTO backup_logs VALUES("2","backup_2026-02-03_17-25-56.sql","30","Gab","admin","38459","C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-25-56.sql","success","::1","2026-02-03 17:25:56");
INSERT INTO backup_logs VALUES("3","backup_2026-02-03_17-27-28.sql","137","mandy","admin","38826","C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-28.sql","success","::1","2026-02-03 17:27:28");
INSERT INTO backup_logs VALUES("4","backup_2026-02-03_17-27-34.sql","137","mandy","admin","39063","C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-34.sql","success","::1","2026-02-03 17:27:34");
INSERT INTO backup_logs VALUES("5","backup_2026-02-03_17-27-35.sql","137","mandy","admin","39300","C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-35.sql","success","::1","2026-02-03 17:27:35");
INSERT INTO backup_logs VALUES("6","backup_2026-02-03_17-27-42.sql","137","mandy","admin","39537","C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-42.sql","success","::1","2026-02-03 17:27:42");


CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO categories VALUES("2","Bottoms","2025-08-29 23:46:29","Sample","active");
INSERT INTO categories VALUES("3","Outerwear","2025-08-29 23:46:29","Sample","active");
INSERT INTO categories VALUES("4","Footwear","2025-08-29 23:46:29","Sample","active");
INSERT INTO categories VALUES("5","Accessories","2025-08-29 23:46:29","Sample","active");
INSERT INTO categories VALUES("11","Tops","2025-09-05 20:42:43","sample","active");
INSERT INTO categories VALUES("15","On Sale","2025-09-17 02:29:26","samplea","active");
INSERT INTO categories VALUES("16","New Arrivals","2025-09-17 02:29:36","sample","active");


CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL COMMENT 'Email or IP address',
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp(),
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO login_attempts VALUES("32","mlbbplays7@gmail.com","::1","2026-01-14 11:13:25","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("33","mlbbplays7@gmail.com","::1","2026-01-14 11:32:43","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("41","admin123@example.com","::1","2026-01-14 11:58:00","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("42","admin123@example.com","::1","2026-01-14 11:58:37","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("47","mandyadmin321@gmail.com","::1","2026-01-25 10:49:57","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("52","mandyfrancsico895@gmail.com","::1","2026-01-25 11:22:15","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("53","mandyfrancsico895@gmail.com","::1","2026-01-25 11:23:04","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("54","mandyfrancsico895@gmail.com","::1","2026-01-25 11:23:10","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("55","mandyfrancsico895@gmail.com","::1","2026-01-25 11:24:34","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("59","mandyfrance895@gmail.com","::1","2026-01-28 21:59:41","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("62","aiahdizon18@gmail.com","::1","2026-01-28 22:17:47","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("64","admin3321@gmail.com","::1","2026-01-29 17:21:07","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("65","admin76432321@gmail.com","::1","2026-01-29 17:31:46","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("69","admin1234@gmail.com","::1","2026-01-29 22:48:41","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("70","admin123@gmail.com","::1","2026-01-29 22:50:03","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("71","staff321@gmd","::1","2026-01-31 01:33:20","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("74","mandyfrance84@gmail.com","::1","2026-01-31 23:13:57","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("75","mandyfrance84@gmail.com","::1","2026-01-31 23:14:04","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("76","mandyfrance84@gmail.com","::1","2026-01-31 23:14:12","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("78","staffff4090@gmail.com","::1","2026-02-01 22:37:39","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("79","staffff4090@gmail.com","::1","2026-02-02 00:32:24","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("81","stafer@gmail.com","::1","2026-02-02 11:59:24","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("84","admin@gmail.com","::1","2026-02-02 15:14:06","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("86","try1@gmail.com","::1","2026-02-02 15:28:14","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("88","try1@gmail.com","::1","2026-02-02 16:00:46","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");


CREATE TABLE `maintenance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(255) NOT NULL,
  `action_performed` text NOT NULL,
  `status` varchar(50) DEFAULT 'Success',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO maintenance_logs VALUES("1","Gab","Created new admin account: mandy","Success","2026-01-29 00:23:00");
INSERT INTO maintenance_logs VALUES("2","Gab","Created new account: STAFFTRY (Role: Staff)","Success","2026-02-02 15:19:37");
INSERT INTO maintenance_logs VALUES("3","Gab","Created new account: try1 (Role: Staff)","Success","2026-02-02 15:27:07");
INSERT INTO maintenance_logs VALUES("4","Gab","Created new Staff account: ihaihafiaifhia","Success","2026-02-02 22:38:01");
INSERT INTO maintenance_logs VALUES("5","Gab","Created new Staff account: gab4","Success","2026-02-02 22:38:33");
INSERT INTO maintenance_logs VALUES("6","Gab","Created new Admin account: admin3214","Success","2026-02-02 22:47:37");
INSERT INTO maintenance_logs VALUES("7","Gab","Created new Staff account: TRYMANDY","Success","2026-02-02 22:58:08");
INSERT INTO maintenance_logs VALUES("8","Gab","Created new Staff account: try1111","Success","2026-02-02 23:10:08");
INSERT INTO maintenance_logs VALUES("9","Gab","Created new Staff account: trystaff","Success","2026-02-02 23:11:01");
INSERT INTO maintenance_logs VALUES("10","Gab","Created new Staff account: dwfsf","Success","2026-02-03 00:43:10");
INSERT INTO maintenance_logs VALUES("11","Gab","Created new Staff account: fsfs","Success","2026-02-03 01:36:04");
INSERT INTO maintenance_logs VALUES("12","Gab","Created new Staff account: dsdsds","Success","2026-02-03 02:00:38");
INSERT INTO maintenance_logs VALUES("13","Gab","Created new Staff account: dsds","Success","2026-02-03 02:05:59");
INSERT INTO maintenance_logs VALUES("14","Gab","Deleted Staff account: dsds (ID: 134)","Success","2026-02-03 17:22:51");
INSERT INTO maintenance_logs VALUES("15","Gab","Created new Staff account: osafmaomfoamfa","Success","2026-02-03 17:23:10");
INSERT INTO maintenance_logs VALUES("16","Gab","Deleted Staff account: trystaff (ID: 130)","Success","2026-02-03 17:23:14");
INSERT INTO maintenance_logs VALUES("17","Gab","Deleted Admin account: mandy (ID: 91)","Success","2026-02-03 17:26:44");
INSERT INTO maintenance_logs VALUES("18","Gab","Deleted Admin account: admin3214 (ID: 127)","Success","2026-02-03 17:26:47");
INSERT INTO maintenance_logs VALUES("19","Gab","Created new Admin account: mandy","Success","2026-02-03 17:27:03");


CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;



CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;



CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_otp_code` (`otp_code`),
  KEY `idx_used` (`used`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores password reset OTP codes';

INSERT INTO password_resets VALUES("1","gabrielvargas0423@gmail.com","794700","2025-11-30 22:47:43","1","2025-11-30 22:33:23","2025-11-30 22:32:43","::1");
INSERT INTO password_resets VALUES("2","gabrielvargas0423@gmail.com","178531","2025-11-30 22:50:49","1","2025-11-30 22:36:07","2025-11-30 22:35:49","::1");
INSERT INTO password_resets VALUES("3","gabrielvargas0423@gmail.com","422666","2025-11-30 22:51:36","1","2025-11-30 22:37:00","2025-11-30 22:36:36","::1");
INSERT INTO password_resets VALUES("4","gabrielvargas0423@gmail.com","142193","2025-11-30 22:56:34","1","2025-11-30 22:42:03","2025-11-30 22:41:34","::1");
INSERT INTO password_resets VALUES("5","gabrielvargas0423@gmail.com","821272","2025-12-01 18:27:46","1","2025-12-01 18:13:18","2025-12-01 18:12:46","::1");
INSERT INTO password_resets VALUES("6","gabrielvargas0423@gmail.com","159095","2025-12-01 21:29:59","1","2025-12-01 21:15:17","2025-12-01 21:14:59","::1");
INSERT INTO password_resets VALUES("7","Mandyfrancisco895@gmail.com","610767","2025-12-04 20:54:59","1",NULL,"2025-12-04 20:39:59","::1");
INSERT INTO password_resets VALUES("8","jamespeterduran826@gmail.com","615979","2025-12-04 20:55:29","0",NULL,"2025-12-04 20:40:29","::1");
INSERT INTO password_resets VALUES("9","gabrielvargas0423@gmail.com","253520","2025-12-04 21:21:42","1","2025-12-04 21:07:10","2025-12-04 21:06:42","::1");
INSERT INTO password_resets VALUES("10","Mandyfrancisco895@gmail.com","109436","2026-01-14 11:54:07","1","2026-01-14 11:39:42","2026-01-14 11:39:07","::1");
INSERT INTO password_resets VALUES("11","Mandyfrancisco895@gmail.com","595745","2026-01-14 12:04:24","1",NULL,"2026-01-14 11:49:24","::1");
INSERT INTO password_resets VALUES("12","Mandyfrancisco895@gmail.com","637190","2026-01-14 12:04:28","1",NULL,"2026-01-14 11:49:28","::1");
INSERT INTO password_resets VALUES("13","Mandyfrancisco895@gmail.com","922981","2026-01-14 12:05:41","1","2026-01-14 11:51:21","2026-01-14 11:50:41","::1");
INSERT INTO password_resets VALUES("14","Mandyfrancisco895@gmail.com","334581","2026-01-14 16:07:51","1",NULL,"2026-01-14 15:52:51","::1");
INSERT INTO password_resets VALUES("15","gabrielvargas0423@gmail.com","950719","2026-01-14 16:34:28","1",NULL,"2026-01-14 16:19:28","::1");
INSERT INTO password_resets VALUES("16","gabrielvargas0423@gmail.com","186310","2026-01-14 17:09:39","0",NULL,"2026-01-14 16:54:39","::1");
INSERT INTO password_resets VALUES("17","Mandyfrancisco895@gmail.com","566354","2026-01-25 10:47:26","1",NULL,"2026-01-25 10:32:26","::1");
INSERT INTO password_resets VALUES("18","mandyfrance84@gmail.com","481425","2026-01-25 11:07:02","1",NULL,"2026-01-25 10:52:02","::1");
INSERT INTO password_resets VALUES("19","Mandyfrancisco895@gmail.com","115651","2026-01-25 11:08:27","1","2026-01-25 10:55:29","2026-01-25 10:53:27","::1");
INSERT INTO password_resets VALUES("20","mandyfrance84@gmail.com","763260","2026-01-25 11:41:04","1","2026-01-25 11:28:30","2026-01-25 11:26:04","::1");


CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sizes` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO products VALUES("114","Round Bonet","A round bonnet is a circular head covering that fits neatly around the head, used for style or protection.","400.00","S,M,L","acc-1.jpg","5","2025-10-11 20:47:27","20","active");
INSERT INTO products VALUES("115","Cap","A round cap is a simple, circular headwear that fits closely on the head for comfort and style.","500.00","S,XL","acc-2.jpg","5","2025-10-11 20:48:29","15","active");
INSERT INTO products VALUES("116","Tote Bag","A tote bag is a large, sturdy, open-top bag with handles, used for carrying personal items or shopping.","300.00","S","acc-3.jpg","5","2025-10-11 20:49:24","9","active");
INSERT INTO products VALUES("117","Shoulder Bag","A shoulder bag is a bag with a long strap designed to be worn over the shoulder for easy carrying.","99.00","S,XL,XXL","acc-4.jpg","5","2025-10-11 20:50:22","0","inactive");
INSERT INTO products VALUES("118","UND Jorts","Jorts are denim shorts made from jeans, combining the look of jeans with the comfort of shorts.","999.00","S,M,L","fit-outwear-1.jpg","2","2025-10-11 20:52:00","15","active");
INSERT INTO products VALUES("119","Track Pants","Track pants are comfortable, lightweight pants designed for sports or casual wear, often with an elastic waist and ankle cuffs.","1500.00","S,M,XXL","fit-outwear-4.jpg","2","2025-10-11 20:56:03","79","active");
INSERT INTO products VALUES("120","Baggy Pants","Baggy pants are loose-fitting trousers with a relaxed, wide cut for comfort and a casual style.","1500.00","XS,L","fit-outwear-8.jpg","2","2025-10-11 20:56:49","19","active");
INSERT INTO products VALUES("121","UND Hoddie","A hoodie is a sweatshirt with a hood, often featuring a front pocket and drawstrings for casual comfort.","2000.00","S,L,XL","fit-outwear-3.jpg","3","2025-10-11 20:58:14","31","active");
INSERT INTO products VALUES("122","Track Suit","A tracksuit is a matching set of a jacket and pants made from lightweight fabric, worn for sports or casual wear.","7000.00","XS,S,M,L,XL","fit-outwear-5.jpg","3","2025-10-11 20:59:28","62","active");
INSERT INTO products VALUES("123","Leather Jacket","A leather jacket is a stylish outerwear made from leather, known for its durability and edgy look.","799.00","S,L","fit-outwear-11.jpg","15","2025-10-11 21:06:13","21","active");
INSERT INTO products VALUES("124","Polo Jacket","A polo jacket is a lightweight, collared jacket inspired by polo shirts, offering a neat and casual look.","5000.00","S,L","fit-outwear-12(front).jpg","16","2025-10-11 21:07:22","70","active");
INSERT INTO products VALUES("125","Rich Boyz Shirt","A shirt is a garment worn on the upper body, usually with a collar, sleeves, and buttons on the front.","700.00","S,M","fit-1.jpg","11","2025-10-11 21:09:02","47","active");
INSERT INTO products VALUES("126","Floral Shirt","A floral t-shirt is a casual shirt featuring flower patterns, adding a fresh and stylish look.","600.00","S,M","fit-3.jpg","11","2025-10-11 21:10:05","4506","active");
INSERT INTO products VALUES("127","Graphical shirt121","A graphical shirt is a t-shirt featuring printed designs, images, or text for a trendy, expressive style.","500.00","S,L,XL","fit-outwear-6.jpg","15","2025-10-11 21:11:04","2568","active");


CREATE TABLE `registration_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expiration` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_registration_email_otp` (`email`,`otp_code`,`used`),
  KEY `idx_registration_expiration` (`expiration`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO registration_otps VALUES("2","ecommercebsit2025@gmailcom","534026","2025-11-23 19:12:11","0","2025-11-23 19:02:11");
INSERT INTO registration_otps VALUES("3","ecommercebsit2025@gmail.com","166920","2025-11-23 19:13:20","0","2025-11-23 19:03:20");
INSERT INTO registration_otps VALUES("4","gabrielvargas0423@gmail.com","438631","2025-11-23 19:16:45","1","2025-11-23 19:06:45");
INSERT INTO registration_otps VALUES("5","gabrielvargas0423@gmail.com","815118","2025-11-23 19:41:06","1","2025-11-23 19:31:06");
INSERT INTO registration_otps VALUES("6","gecohaillie560@gmail.com","959517","2025-11-23 19:42:41","1","2025-11-23 19:32:41");
INSERT INTO registration_otps VALUES("7","gabrielvargas0423@gmail.com","269490","2025-11-23 19:45:45","1","2025-11-23 19:35:45");
INSERT INTO registration_otps VALUES("8","gabrielvargas0423@gmail.com","398807","2025-11-23 19:49:20","1","2025-11-23 19:39:20");
INSERT INTO registration_otps VALUES("9","gabrielvargas0423@gmail.com","886794","2025-11-23 19:52:16","1","2025-11-23 19:42:16");
INSERT INTO registration_otps VALUES("10","gabrielvargas0423@gmail.com","992655","2025-11-23 19:58:07","1","2025-11-23 19:48:07");
INSERT INTO registration_otps VALUES("11","gabrielvargas0423@gmail.com","858477","2025-11-23 20:00:29","1","2025-11-23 19:50:29");
INSERT INTO registration_otps VALUES("12","gabrielvargas0423@gmail.com","249251","2025-11-23 20:05:26","1","2025-11-23 19:55:26");
INSERT INTO registration_otps VALUES("13","gabrielvargas0423@gmail.com","211538","2025-11-23 20:08:11","1","2025-11-23 19:58:11");
INSERT INTO registration_otps VALUES("14","gabrielvargas0423@gmail.com","253675","2025-11-23 20:10:28","1","2025-11-23 20:00:28");
INSERT INTO registration_otps VALUES("15","purplee.hazee12@gmail.com","198141","2025-11-23 21:03:36","0","2025-11-23 20:53:36");
INSERT INTO registration_otps VALUES("16","jamespeterduran826@gmail.com","878814","2025-11-23 21:06:25","1","2025-11-23 20:56:25");
INSERT INTO registration_otps VALUES("17","Mandyfrancisco895@gmail.com","176660","2025-11-23 21:13:35","1","2025-11-23 21:03:35");
INSERT INTO registration_otps VALUES("18","laurencerafael8@gmail.com","628614","2025-11-23 21:18:54","1","2025-11-23 21:08:54");
INSERT INTO registration_otps VALUES("19","laurencerafael8@gmail.com","290711","2025-11-23 21:20:33","0","2025-11-23 21:10:33");
INSERT INTO registration_otps VALUES("20","aujscvargas@gmail.com","236431","2025-11-23 21:22:45","1","2025-11-23 21:12:45");
INSERT INTO registration_otps VALUES("21","gecohaillie560@gmail.com","640008","2025-11-23 21:25:28","1","2025-11-23 21:15:28");
INSERT INTO registration_otps VALUES("22","gabrielvargas0423@gmail.com","143890","2025-11-23 23:31:12","1","2025-11-23 23:21:12");
INSERT INTO registration_otps VALUES("23","gabrielvargas0423@gmail.com","320048","2025-11-30 20:38:55","1","2025-11-30 20:28:55");
INSERT INTO registration_otps VALUES("24","gabrielvargas0423@gmail.com","997663","2025-11-30 22:42:06","1","2025-11-30 22:32:06");
INSERT INTO registration_otps VALUES("25","gabrielvargas0423@gmail.com","337549","2025-12-01 18:22:16","1","2025-12-01 18:12:16");
INSERT INTO registration_otps VALUES("26","gabrielvargas0423@gmail.com","410751","2025-12-01 21:24:13","1","2025-12-01 21:14:13");
INSERT INTO registration_otps VALUES("27","gabrielvargas0423@gmail.com","206443","2025-12-04 21:15:49","1","2025-12-04 21:05:49");
INSERT INTO registration_otps VALUES("28","admin12345@gmail.com","279042","2025-12-10 14:48:49","0","2025-12-10 14:38:49");
INSERT INTO registration_otps VALUES("29","renziealvarez18@gmail.com","806693","2025-12-10 14:50:24","0","2025-12-10 14:40:24");
INSERT INTO registration_otps VALUES("30","mandyfrance84@gmail.com","166701","2025-12-10 14:52:32","1","2025-12-10 14:42:32");
INSERT INTO registration_otps VALUES("31","gabrielvargas0423@gmail.com","625943","2025-12-10 14:53:51","1","2025-12-10 14:43:51");
INSERT INTO registration_otps VALUES("32","gabrielvargas0423@gmail.com","193573","2026-01-14 12:03:48","1","2026-01-14 11:53:48");
INSERT INTO registration_otps VALUES("33","mandyfrance84@gmail.com","773558","2026-01-25 10:43:24","1","2026-01-25 10:33:24");
INSERT INTO registration_otps VALUES("34","mandyfrance84@gmail.com","256053","2026-01-25 10:53:48","1","2026-01-25 10:43:48");
INSERT INTO registration_otps VALUES("35","mandyfrance84@gmail.com","565115","2026-01-25 11:27:17","1","2026-01-25 11:17:17");
INSERT INTO registration_otps VALUES("36","mandyfrancisco895@gmail.com","587513","2026-02-03 13:14:45","1","2026-02-03 13:04:45");


CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `movement_type` enum('add','remove','set') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `stock_movements_ibfk_1` (`product_id`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO stock_movements VALUES("14","127","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("15","126","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("16","125","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("17","124","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("18","123","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("19","122","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("20","121","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("21","120","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("24","127","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("25","126","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("26","125","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("27","124","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("28","123","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("29","122","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("30","121","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("31","120","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("34","127","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("35","126","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("36","125","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("37","124","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("38","123","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("39","122","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("40","121","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("41","120","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("44","127","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("45","126","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("46","125","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("47","124","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("48","123","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("49","122","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("50","121","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("51","120","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("54","127","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("55","126","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("56","125","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("57","124","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("58","123","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("59","122","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("60","121","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("61","120","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("67","127","","20","sale","Bulk operation: add by 20","30","2025-10-16 20:48:34");
INSERT INTO stock_movements VALUES("72","127","add","123","restock","12","30","2026-02-02 16:41:12");
INSERT INTO stock_movements VALUES("73","126","add","1","restock","1","30","2026-02-02 16:41:52");
INSERT INTO stock_movements VALUES("89","127","set","234","quick_adjustment","Quick adjustment via inventory table","30","2026-02-03 00:45:33");
INSERT INTO stock_movements VALUES("90","127","add","2345","sale","f","30","2026-02-03 00:45:42");
INSERT INTO stock_movements VALUES("91","127","set","244","quick_adjustment","Quick adjustment via inventory table",NULL,"2026-02-03 00:46:25");
INSERT INTO stock_movements VALUES("92","126","add","234","sale","d",NULL,"2026-02-03 00:46:36");
INSERT INTO stock_movements VALUES("93","127","add","1245","restock","rwrwr",NULL,"2026-02-03 00:46:51");
INSERT INTO stock_movements VALUES("94","127","set","234","quick_adjustment","Quick adjustment via inventory table",NULL,"2026-02-03 01:00:48");
INSERT INTO stock_movements VALUES("95","126","add","4244","restock","",NULL,"2026-02-03 01:01:01");
INSERT INTO stock_movements VALUES("96","127","add","2345","restock","2",NULL,"2026-02-03 01:01:22");
INSERT INTO stock_movements VALUES("97","127","add","23455","restock","2",NULL,"2026-02-03 01:06:05");
INSERT INTO stock_movements VALUES("98","127","set","13","quick_adjustment","Quick adjustment via inventory table",NULL,"2026-02-03 01:06:09");
INSERT INTO stock_movements VALUES("99","127","set","134","quick_adjustment","Quick adjustment via inventory table",NULL,"2026-02-03 01:33:43");
INSERT INTO stock_movements VALUES("100","127","set","356","quick_adjustment","Quick adjustment via inventory table",NULL,"2026-02-03 02:03:13");
INSERT INTO stock_movements VALUES("101","127","set","123","quick_adjustment","Quick adjustment via inventory table",NULL,"2026-02-03 02:04:49");
INSERT INTO stock_movements VALUES("102","127","set","1234","quick_adjustment","Quick adjustment via inventory table","136","2026-02-03 17:24:42");
INSERT INTO stock_movements VALUES("103","127","add","1334","restock","23","136","2026-02-03 17:24:53");


CREATE TABLE `system_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operation` varchar(255) NOT NULL,
  `admin_user` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Success',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=584 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO system_audit_logs VALUES("576","System Status Update","Gab","Maintenance: ON | Recovery: 2026-02-03T00:49",NULL,"Completed","2026-02-02 21:49:36");
INSERT INTO system_audit_logs VALUES("577","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified",NULL,"Completed","2026-02-02 21:49:40");
INSERT INTO system_audit_logs VALUES("578","System Status Update","Gab","Maintenance: ON | Recovery: 2026-02-03T01:01",NULL,"Completed","2026-02-02 22:01:12");
INSERT INTO system_audit_logs VALUES("579","Test Operation","Admin User","This is a test log entry",NULL,"Completed","2026-02-02 22:02:38");
INSERT INTO system_audit_logs VALUES("580","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified",NULL,"Completed","2026-02-02 22:04:18");
INSERT INTO system_audit_logs VALUES("581","System Status Update","Gab","Maintenance: OFF | Recovery: 2026-02-03T01:46",NULL,"Completed","2026-02-02 22:46:34");
INSERT INTO system_audit_logs VALUES("582","System Status Update","Gab","Maintenance: ON | Recovery: 2026-02-03T01:46",NULL,"Completed","2026-02-02 22:46:42");
INSERT INTO system_audit_logs VALUES("583","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified",NULL,"Completed","2026-02-02 22:46:48");


CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1934 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO system_settings VALUES("1","store_name","Empire - Shop","2026-01-25 16:59:25");
INSERT INTO system_settings VALUES("2","maintenance_mode","0","2026-02-02 22:46:48");
INSERT INTO system_settings VALUES("23","maint_message","","2026-02-02 22:46:48");
INSERT INTO system_settings VALUES("24","ip_whitelist","","2026-01-26 01:12:51");
INSERT INTO system_settings VALUES("25","recovery_time","","2026-02-02 22:46:48");
INSERT INTO system_settings VALUES("540","maint_start_time","2026-01-28 00:13:07","2026-01-28 00:13:07");


CREATE TABLE `user_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expiration` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;



CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','staff') DEFAULT 'user',
  `status` enum('Active','Inactive','Blocked','Deactivated') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO users VALUES("30","Gab","admin321@gmail.com","$2y$10$kLMBHM8ilzpdN4pSemAqruacxDdb9WtZTOEIL81n5cU2PETKAWngS","admin","Active","2025-09-07 00:05:08",NULL,NULL,"profile_30_1770044076.jpg");
INSERT INTO users VALUES("54","Laurence Vargas","aujscvargas@gmail.com","$2y$10$3TuAG5UQMYeRKvNYBcNRLOtbJKE1jaHB/hIBWeew2NaDarsg9mFE2","user","Active","2025-11-23 21:13:09","09938433971","731 lansones st napico manggahan pasig city ","1763903658_duds.jpg");
INSERT INTO users VALUES("55","Haillie1 Geco","gecohaillie560@gmail.com","$2y$10$NBW8JWWqHNtR6WsZ/n/A8eUkITpV0D84xL9zRtpXbPGJPG7velAzS","user","Active","2025-11-23 21:16:05","09938433890","blck 8. Kasigahan St. Pasig city","1763903849_aa522e76-6c4c-4b74-a7d9-2afc6e4fa036.jpg");
INSERT INTO users VALUES("135","ddaDAD","mandyfrancisco895@gmail.com","$2y$10$0okeuJsFNMSlUQLwM50mIeK0wb.7sGp.aStyVsqL4YYo0L.jQEZcy","user","Active","2026-02-03 13:05:13","","","user");
INSERT INTO users VALUES("136","osafmaomfoamfa","osafmaomfoamfa@gmail.com","$2y$10$pgSQfb6YlVOe/OBek1nvsOaGT/Acioy1ZmyQ9uagJrokPTi3CoT0G","staff","Active","2026-02-03 17:23:10",NULL,NULL,"profile_136_1770110708.jpg");
INSERT INTO users VALUES("137","mandy","admin4321@gmail.com","$2y$10$GiGSfdcpEFx11rItfxzee.wW789y5aVFDRgEQZucU0OCQPAQBhwS6","admin","Active","2026-02-03 17:27:03",NULL,NULL,NULL);
