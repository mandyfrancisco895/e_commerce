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



CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
INSERT INTO login_attempts VALUES("60","staff123@gmail.com","::1","2026-01-28 22:13:49","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("61","staff123@gmail.com","::1","2026-01-28 22:14:23","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("62","aiahdizon18@gmail.com","::1","2026-01-28 22:17:47","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("64","admin3321@gmail.com","::1","2026-01-29 17:21:07","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("65","admin76432321@gmail.com","::1","2026-01-29 17:31:46","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("69","admin1234@gmail.com","::1","2026-01-29 22:48:41","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("70","admin123@gmail.com","::1","2026-01-29 22:50:03","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0");
INSERT INTO login_attempts VALUES("71","staff321@gmd","::1","2026-01-31 01:33:20","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("74","mandyfrance84@gmail.com","::1","2026-01-31 23:13:57","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("75","mandyfrance84@gmail.com","::1","2026-01-31 23:14:04","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");
INSERT INTO login_attempts VALUES("76","mandyfrance84@gmail.com","::1","2026-01-31 23:14:12","Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36");


CREATE TABLE `maintenance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(255) NOT NULL,
  `action_performed` text NOT NULL,
  `status` varchar(50) DEFAULT 'Success',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO maintenance_logs VALUES("1","Gab","Created new admin account: mandy","Success","2026-01-29 00:23:00");


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

INSERT INTO order_items VALUES("83","58","129","Jersey Shirt","700.00","1",NULL,"700.00","2026-01-25 10:09:26");
INSERT INTO order_items VALUES("84","58","128","Black Shirt","700.00","1",NULL,"700.00","2026-01-25 10:09:26");
INSERT INTO order_items VALUES("85","58","127","Graphical shirt","500.00","1",NULL,"500.00","2026-01-25 10:09:26");
INSERT INTO order_items VALUES("86","58","125","Rich Boyz Shirt","700.00","1",NULL,"700.00","2026-01-25 10:09:26");
INSERT INTO order_items VALUES("87","59","129","Jersey Shirt","700.00","3",NULL,"2100.00","2026-01-25 10:59:24");
INSERT INTO order_items VALUES("88","59","128","Black Shirt","700.00","2",NULL,"1400.00","2026-01-25 10:59:24");
INSERT INTO order_items VALUES("89","59","125","Rich Boyz Shirt","700.00","1",NULL,"700.00","2026-01-25 10:59:24");


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

INSERT INTO orders VALUES("58","53","ORD-20260125-69757B5671005","2600.00","pending","62134 makati mercedes city","Cash on Delivery (COD)","pending","2026-01-25 10:09:26","2026-01-25 10:09:26");
INSERT INTO orders VALUES("59","53","ORD-20260125-6975870C72211","4200.00","pending","62134 makati mercedes city","Cash on Delivery (COD)","pending","2026-01-25 10:59:24","2026-01-25 10:59:24");


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
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
INSERT INTO products VALUES("126","Floral Shirt","A floral t-shirt is a casual shirt featuring flower patterns, adding a fresh and stylish look.","600.00","S,M","fit-3.jpg","11","2025-10-11 21:10:05","27","active");
INSERT INTO products VALUES("127","Graphical shirt","A graphical shirt is a t-shirt featuring printed designs, images, or text for a trendy, expressive style.","500.00","S,L,XL","fit-outwear-6.jpg","15","2025-10-11 21:11:04","48","active");
INSERT INTO products VALUES("128","Black Shirt","versatile top in black color, suitable for both casual and formal wear.","700.00","S,L","fit-outwear-9.jpg","11","2025-10-11 21:12:44","39","active");
INSERT INTO products VALUES("129","Jersey Shirt","A jersey shirt is a lightweight, stretchy top made from jersey fabric, often used for sports or casual wear.","700.00","S,L","fit-outwear-7.jpg","16","2025-10-11 21:13:40","35","active");


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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO stock_movements VALUES("11","129","set","50","quick_adjustment","Quick adjustment via inventory table","30","2025-10-11 22:39:18");
INSERT INTO stock_movements VALUES("12","129","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("13","128","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("14","127","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("15","126","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("16","125","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("17","124","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("18","123","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("19","122","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("20","121","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("21","120","","10","sample","Bulk operation: add by 10","30","2025-10-11 23:15:36");
INSERT INTO stock_movements VALUES("22","129","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("23","128","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("24","127","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("25","126","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("26","125","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("27","124","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("28","123","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("29","122","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("30","121","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("31","120","","20","restock","Bulk operation: add by 20","30","2025-10-11 23:22:56");
INSERT INTO stock_movements VALUES("32","129","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("33","128","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("34","127","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("35","126","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("36","125","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("37","124","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("38","123","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("39","122","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("40","121","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("41","120","","20","sale","Bulk operation: subtract by 20","30","2025-10-11 23:23:14");
INSERT INTO stock_movements VALUES("42","129","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("43","128","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("44","127","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("45","126","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("46","125","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("47","124","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("48","123","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("49","122","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("50","121","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("51","120","","1","restock","Bulk operation: add by 1","30","2025-10-12 17:55:11");
INSERT INTO stock_movements VALUES("52","129","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("53","128","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("54","127","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("55","126","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("56","125","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("57","124","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("58","123","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("59","122","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("60","121","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("61","120","","1","sale","Bulk operation: add by 1","30","2025-10-12 17:55:39");
INSERT INTO stock_movements VALUES("62","129","set","1","quick_adjustment","Quick adjustment via inventory table","30","2025-10-12 17:55:54");
INSERT INTO stock_movements VALUES("63","129","set","60","quick_adjustment","Quick adjustment via inventory table","30","2025-10-12 17:56:01");
INSERT INTO stock_movements VALUES("64","129","add","10","restock","sample","30","2025-10-16 20:47:58");
INSERT INTO stock_movements VALUES("65","129","","20","sale","Bulk operation: add by 20","30","2025-10-16 20:48:34");
INSERT INTO stock_movements VALUES("66","128","","20","sale","Bulk operation: add by 20","30","2025-10-16 20:48:34");
INSERT INTO stock_movements VALUES("67","127","","20","sale","Bulk operation: add by 20","30","2025-10-16 20:48:34");
INSERT INTO stock_movements VALUES("68","129","set","50","quick_adjustment","Quick adjustment via inventory table","30","2025-10-16 20:48:50");


CREATE TABLE `system_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operation` varchar(255) NOT NULL,
  `admin_user` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Success',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=535 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO system_audit_logs VALUES("54","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:36:05");
INSERT INTO system_audit_logs VALUES("55","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 18:36:08");
INSERT INTO system_audit_logs VALUES("56","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:36:10");
INSERT INTO system_audit_logs VALUES("57","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 18:42:50");
INSERT INTO system_audit_logs VALUES("58","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:42:52");
INSERT INTO system_audit_logs VALUES("59","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 18:43:02");
INSERT INTO system_audit_logs VALUES("60","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:43:03");
INSERT INTO system_audit_logs VALUES("61","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 18:46:08");
INSERT INTO system_audit_logs VALUES("62","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:46:10");
INSERT INTO system_audit_logs VALUES("63","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T21:46","Completed","2026-01-27 18:46:25");
INSERT INTO system_audit_logs VALUES("64","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:46:26");
INSERT INTO system_audit_logs VALUES("65","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T21:46","Completed","2026-01-27 18:47:12");
INSERT INTO system_audit_logs VALUES("66","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:47:13");
INSERT INTO system_audit_logs VALUES("67","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:52:00");
INSERT INTO system_audit_logs VALUES("68","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T21:46","Completed","2026-01-27 18:52:08");
INSERT INTO system_audit_logs VALUES("69","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:52:09");
INSERT INTO system_audit_logs VALUES("70","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T21:46","Completed","2026-01-27 18:52:11");
INSERT INTO system_audit_logs VALUES("71","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:52:12");
INSERT INTO system_audit_logs VALUES("72","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:52:30");
INSERT INTO system_audit_logs VALUES("73","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T21:46","Completed","2026-01-27 18:52:31");
INSERT INTO system_audit_logs VALUES("74","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:52:32");
INSERT INTO system_audit_logs VALUES("75","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T13:52","Completed","2026-01-27 18:52:37");
INSERT INTO system_audit_logs VALUES("76","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:52:38");
INSERT INTO system_audit_logs VALUES("77","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:55:07");
INSERT INTO system_audit_logs VALUES("78","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T13:52","Completed","2026-01-27 18:55:13");
INSERT INTO system_audit_logs VALUES("79","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:55:14");
INSERT INTO system_audit_logs VALUES("80","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T13:52","Completed","2026-01-27 18:55:19");
INSERT INTO system_audit_logs VALUES("81","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:55:20");
INSERT INTO system_audit_logs VALUES("82","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T13:52","Completed","2026-01-27 18:55:43");
INSERT INTO system_audit_logs VALUES("83","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:55:44");
INSERT INTO system_audit_logs VALUES("84","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T13:52","Completed","2026-01-27 18:55:51");
INSERT INTO system_audit_logs VALUES("85","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:55:53");
INSERT INTO system_audit_logs VALUES("86","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:58:57");
INSERT INTO system_audit_logs VALUES("87","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:58:58");
INSERT INTO system_audit_logs VALUES("88","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T13:52","Completed","2026-01-27 18:59:00");
INSERT INTO system_audit_logs VALUES("89","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 18:59:01");
INSERT INTO system_audit_logs VALUES("90","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:01:12");
INSERT INTO system_audit_logs VALUES("91","System Status Toggle","Gab","Global Maintenance Mode ACTIVATED","Completed","2026-01-27 19:01:14");
INSERT INTO system_audit_logs VALUES("92","Recovery Time Updated","Gab","Expected recovery set to: 2026-01-28T13:52","Completed","2026-01-27 19:01:14");
INSERT INTO system_audit_logs VALUES("93","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:01:14");
INSERT INTO system_audit_logs VALUES("94","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:02:12");
INSERT INTO system_audit_logs VALUES("95","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:02:16");
INSERT INTO system_audit_logs VALUES("96","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:02:21");
INSERT INTO system_audit_logs VALUES("97","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:03:02");
INSERT INTO system_audit_logs VALUES("98","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T13:52","Completed","2026-01-27 19:05:52");
INSERT INTO system_audit_logs VALUES("99","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:05:54");
INSERT INTO system_audit_logs VALUES("100","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:10:57");
INSERT INTO system_audit_logs VALUES("101","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:10:58");
INSERT INTO system_audit_logs VALUES("102","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:11:12");
INSERT INTO system_audit_logs VALUES("103","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:11:13");
INSERT INTO system_audit_logs VALUES("104","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:11:18");
INSERT INTO system_audit_logs VALUES("105","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:11:19");
INSERT INTO system_audit_logs VALUES("106","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:11:30");
INSERT INTO system_audit_logs VALUES("107","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:11:31");
INSERT INTO system_audit_logs VALUES("108","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:20:02");
INSERT INTO system_audit_logs VALUES("109","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:20:04");
INSERT INTO system_audit_logs VALUES("110","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:20:05");
INSERT INTO system_audit_logs VALUES("111","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:23:53");
INSERT INTO system_audit_logs VALUES("112","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:23:54");
INSERT INTO system_audit_logs VALUES("113","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:23:55");
INSERT INTO system_audit_logs VALUES("114","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:25:14");
INSERT INTO system_audit_logs VALUES("115","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:25:14");
INSERT INTO system_audit_logs VALUES("116","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:27:40");
INSERT INTO system_audit_logs VALUES("117","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:29:21");
INSERT INTO system_audit_logs VALUES("118","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:29:22");
INSERT INTO system_audit_logs VALUES("119","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:29:23");
INSERT INTO system_audit_logs VALUES("120","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:29:35");
INSERT INTO system_audit_logs VALUES("121","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:29:36");
INSERT INTO system_audit_logs VALUES("122","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:42:39");
INSERT INTO system_audit_logs VALUES("123","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:43:36");
INSERT INTO system_audit_logs VALUES("124","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:43:37");
INSERT INTO system_audit_logs VALUES("125","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:43:38");
INSERT INTO system_audit_logs VALUES("126","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:45:47");
INSERT INTO system_audit_logs VALUES("127","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:45:49");
INSERT INTO system_audit_logs VALUES("128","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:45:50");
INSERT INTO system_audit_logs VALUES("129","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:48:03");
INSERT INTO system_audit_logs VALUES("130","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:48:03");
INSERT INTO system_audit_logs VALUES("131","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:48:04");
INSERT INTO system_audit_logs VALUES("132","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T11:10","Completed","2026-01-27 19:48:06");
INSERT INTO system_audit_logs VALUES("133","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:48:07");
INSERT INTO system_audit_logs VALUES("134","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T14:48","Completed","2026-01-27 19:48:23");
INSERT INTO system_audit_logs VALUES("135","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:48:25");
INSERT INTO system_audit_logs VALUES("136","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:50:00");
INSERT INTO system_audit_logs VALUES("137","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T14:48","Completed","2026-01-27 19:50:02");
INSERT INTO system_audit_logs VALUES("138","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:50:03");
INSERT INTO system_audit_logs VALUES("139","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:54:09");
INSERT INTO system_audit_logs VALUES("140","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:54:35");
INSERT INTO system_audit_logs VALUES("141","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T14:48","Completed","2026-01-27 19:54:37");
INSERT INTO system_audit_logs VALUES("142","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:54:38");
INSERT INTO system_audit_logs VALUES("143","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:55:38");
INSERT INTO system_audit_logs VALUES("144","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:55:45");
INSERT INTO system_audit_logs VALUES("145","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T14:48","Completed","2026-01-27 19:55:47");
INSERT INTO system_audit_logs VALUES("146","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:55:48");
INSERT INTO system_audit_logs VALUES("147","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:57:38");
INSERT INTO system_audit_logs VALUES("148","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T14:48","Completed","2026-01-27 19:57:40");
INSERT INTO system_audit_logs VALUES("149","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:57:41");
INSERT INTO system_audit_logs VALUES("150","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:59:36");
INSERT INTO system_audit_logs VALUES("151","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T14:48","Completed","2026-01-27 19:59:40");
INSERT INTO system_audit_logs VALUES("152","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:59:41");
INSERT INTO system_audit_logs VALUES("153","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 19:59:48");
INSERT INTO system_audit_logs VALUES("154","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 20:00:27");
INSERT INTO system_audit_logs VALUES("155","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 20:03:22");
INSERT INTO system_audit_logs VALUES("156","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 20:09:45");
INSERT INTO system_audit_logs VALUES("157","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T15:11","Completed","2026-01-27 20:11:08");
INSERT INTO system_audit_logs VALUES("158","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T12:11","Completed","2026-01-27 20:11:20");
INSERT INTO system_audit_logs VALUES("159","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T15:11","Completed","2026-01-27 20:11:40");
INSERT INTO system_audit_logs VALUES("160","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 20:12:14");
INSERT INTO system_audit_logs VALUES("161","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T20:13","Completed","2026-01-27 20:13:23");
INSERT INTO system_audit_logs VALUES("162","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 20:13:28");
INSERT INTO system_audit_logs VALUES("163","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T15:15","Completed","2026-01-27 20:15:29");
INSERT INTO system_audit_logs VALUES("164","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T21:18","Completed","2026-01-27 20:18:42");
INSERT INTO system_audit_logs VALUES("165","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T21:38","Completed","2026-01-27 20:38:31");
INSERT INTO system_audit_logs VALUES("166","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 22:59:48");
INSERT INTO system_audit_logs VALUES("167","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:18:03");
INSERT INTO system_audit_logs VALUES("168","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:18:10");
INSERT INTO system_audit_logs VALUES("169","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:18:13");
INSERT INTO system_audit_logs VALUES("170","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:18:18");
INSERT INTO system_audit_logs VALUES("171","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T00:18","Completed","2026-01-27 23:18:45");
INSERT INTO system_audit_logs VALUES("172","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T00:18","Completed","2026-01-27 23:18:51");
INSERT INTO system_audit_logs VALUES("173","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:29:44");
INSERT INTO system_audit_logs VALUES("174","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:29:51");
INSERT INTO system_audit_logs VALUES("175","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:29:56");
INSERT INTO system_audit_logs VALUES("176","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:30:00");
INSERT INTO system_audit_logs VALUES("177","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 23:32:22");
INSERT INTO system_audit_logs VALUES("178","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:32:25");
INSERT INTO system_audit_logs VALUES("179","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 23:32:25");
INSERT INTO system_audit_logs VALUES("180","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:32:31");
INSERT INTO system_audit_logs VALUES("181","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 23:32:32");
INSERT INTO system_audit_logs VALUES("182","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:36:06");
INSERT INTO system_audit_logs VALUES("183","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:36:09");
INSERT INTO system_audit_logs VALUES("184","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:36:15");
INSERT INTO system_audit_logs VALUES("185","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:36:53");
INSERT INTO system_audit_logs VALUES("186","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:37:03");
INSERT INTO system_audit_logs VALUES("187","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:40:33");
INSERT INTO system_audit_logs VALUES("188","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:40:37");
INSERT INTO system_audit_logs VALUES("189","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:42:25");
INSERT INTO system_audit_logs VALUES("190","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:42:31");
INSERT INTO system_audit_logs VALUES("191","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:43:35");
INSERT INTO system_audit_logs VALUES("192","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:43:43");
INSERT INTO system_audit_logs VALUES("193","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 23:44:03");
INSERT INTO system_audit_logs VALUES("194","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:44:07");
INSERT INTO system_audit_logs VALUES("195","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-27 23:44:09");
INSERT INTO system_audit_logs VALUES("196","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:48:44");
INSERT INTO system_audit_logs VALUES("197","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:48:47");
INSERT INTO system_audit_logs VALUES("198","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:48:50");
INSERT INTO system_audit_logs VALUES("199","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:48:53");
INSERT INTO system_audit_logs VALUES("200","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:13");
INSERT INTO system_audit_logs VALUES("201","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:14");
INSERT INTO system_audit_logs VALUES("202","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:17");
INSERT INTO system_audit_logs VALUES("203","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:17");
INSERT INTO system_audit_logs VALUES("204","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:18");
INSERT INTO system_audit_logs VALUES("205","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:18");
INSERT INTO system_audit_logs VALUES("206","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:18");
INSERT INTO system_audit_logs VALUES("207","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:21");
INSERT INTO system_audit_logs VALUES("208","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:21");
INSERT INTO system_audit_logs VALUES("209","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:21");
INSERT INTO system_audit_logs VALUES("210","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:21");
INSERT INTO system_audit_logs VALUES("211","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:21");
INSERT INTO system_audit_logs VALUES("212","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:22");
INSERT INTO system_audit_logs VALUES("213","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:55","Completed","2026-01-27 23:55:22");
INSERT INTO system_audit_logs VALUES("214","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T18:55","Completed","2026-01-27 23:55:23");
INSERT INTO system_audit_logs VALUES("215","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T18:55","Completed","2026-01-27 23:55:23");
INSERT INTO system_audit_logs VALUES("216","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T18:55","Completed","2026-01-27 23:55:23");
INSERT INTO system_audit_logs VALUES("217","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T18:55","Completed","2026-01-27 23:55:25");
INSERT INTO system_audit_logs VALUES("218","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T18:55","Completed","2026-01-27 23:55:25");
INSERT INTO system_audit_logs VALUES("219","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T18:55","Completed","2026-01-27 23:55:26");
INSERT INTO system_audit_logs VALUES("220","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:31");
INSERT INTO system_audit_logs VALUES("221","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:32");
INSERT INTO system_audit_logs VALUES("222","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:33");
INSERT INTO system_audit_logs VALUES("223","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:33");
INSERT INTO system_audit_logs VALUES("224","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:33");
INSERT INTO system_audit_logs VALUES("225","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:33");
INSERT INTO system_audit_logs VALUES("226","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:33");
INSERT INTO system_audit_logs VALUES("227","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:55:34");
INSERT INTO system_audit_logs VALUES("228","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:58:13");
INSERT INTO system_audit_logs VALUES("229","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T16:58","Completed","2026-01-27 23:58:22");
INSERT INTO system_audit_logs VALUES("230","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-27 23:58:44");
INSERT INTO system_audit_logs VALUES("231","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:00","Completed","2026-01-28 00:00:50");
INSERT INTO system_audit_logs VALUES("232","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:01:06");
INSERT INTO system_audit_logs VALUES("233","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:01:09");
INSERT INTO system_audit_logs VALUES("234","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:01:33");
INSERT INTO system_audit_logs VALUES("235","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:01:36");
INSERT INTO system_audit_logs VALUES("236","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:01:55");
INSERT INTO system_audit_logs VALUES("237","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:01:58");
INSERT INTO system_audit_logs VALUES("238","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:02:00");
INSERT INTO system_audit_logs VALUES("239","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:02:21");
INSERT INTO system_audit_logs VALUES("240","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:02:24");
INSERT INTO system_audit_logs VALUES("241","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:02:31");
INSERT INTO system_audit_logs VALUES("242","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:02:39");
INSERT INTO system_audit_logs VALUES("243","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:02:41");
INSERT INTO system_audit_logs VALUES("244","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:02:44");
INSERT INTO system_audit_logs VALUES("245","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:04:09");
INSERT INTO system_audit_logs VALUES("246","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:04:12");
INSERT INTO system_audit_logs VALUES("247","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:05:04");
INSERT INTO system_audit_logs VALUES("248","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:05:08");
INSERT INTO system_audit_logs VALUES("249","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:12");
INSERT INTO system_audit_logs VALUES("250","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:15");
INSERT INTO system_audit_logs VALUES("251","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:15");
INSERT INTO system_audit_logs VALUES("252","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:15");
INSERT INTO system_audit_logs VALUES("253","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:16");
INSERT INTO system_audit_logs VALUES("254","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:16");
INSERT INTO system_audit_logs VALUES("255","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:16");
INSERT INTO system_audit_logs VALUES("256","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:16");
INSERT INTO system_audit_logs VALUES("257","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:16");
INSERT INTO system_audit_logs VALUES("258","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:05","Completed","2026-01-28 00:05:16");
INSERT INTO system_audit_logs VALUES("259","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:05:49");
INSERT INTO system_audit_logs VALUES("260","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:05:52");
INSERT INTO system_audit_logs VALUES("261","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:08:48");
INSERT INTO system_audit_logs VALUES("262","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:08:56");
INSERT INTO system_audit_logs VALUES("263","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:08:59");
INSERT INTO system_audit_logs VALUES("264","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:09:30");
INSERT INTO system_audit_logs VALUES("265","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:09:32");
INSERT INTO system_audit_logs VALUES("266","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:10:33");
INSERT INTO system_audit_logs VALUES("267","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:10:37");
INSERT INTO system_audit_logs VALUES("268","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:10:51");
INSERT INTO system_audit_logs VALUES("269","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:12:13");
INSERT INTO system_audit_logs VALUES("270","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:12:18");
INSERT INTO system_audit_logs VALUES("271","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:12:22");
INSERT INTO system_audit_logs VALUES("272","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:12:39");
INSERT INTO system_audit_logs VALUES("273","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:12:44");
INSERT INTO system_audit_logs VALUES("274","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:12:54");
INSERT INTO system_audit_logs VALUES("275","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:12:59");
INSERT INTO system_audit_logs VALUES("276","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:02");
INSERT INTO system_audit_logs VALUES("277","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:03");
INSERT INTO system_audit_logs VALUES("278","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:04");
INSERT INTO system_audit_logs VALUES("279","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:04");
INSERT INTO system_audit_logs VALUES("280","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:04");
INSERT INTO system_audit_logs VALUES("281","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:04");
INSERT INTO system_audit_logs VALUES("282","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:04");
INSERT INTO system_audit_logs VALUES("283","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:04");
INSERT INTO system_audit_logs VALUES("284","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:07");
INSERT INTO system_audit_logs VALUES("285","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:07");
INSERT INTO system_audit_logs VALUES("286","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:12","Completed","2026-01-28 00:13:07");
INSERT INTO system_audit_logs VALUES("287","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:15:58");
INSERT INTO system_audit_logs VALUES("288","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:16:03");
INSERT INTO system_audit_logs VALUES("289","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:13");
INSERT INTO system_audit_logs VALUES("290","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:14");
INSERT INTO system_audit_logs VALUES("291","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:14");
INSERT INTO system_audit_logs VALUES("292","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:14");
INSERT INTO system_audit_logs VALUES("293","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:14");
INSERT INTO system_audit_logs VALUES("294","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:15");
INSERT INTO system_audit_logs VALUES("295","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:15");
INSERT INTO system_audit_logs VALUES("296","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:15");
INSERT INTO system_audit_logs VALUES("297","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:15");
INSERT INTO system_audit_logs VALUES("298","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:15");
INSERT INTO system_audit_logs VALUES("299","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:17");
INSERT INTO system_audit_logs VALUES("300","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:17");
INSERT INTO system_audit_logs VALUES("301","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:17");
INSERT INTO system_audit_logs VALUES("302","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:17");
INSERT INTO system_audit_logs VALUES("303","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:17");
INSERT INTO system_audit_logs VALUES("304","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:18");
INSERT INTO system_audit_logs VALUES("305","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:18");
INSERT INTO system_audit_logs VALUES("306","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:18");
INSERT INTO system_audit_logs VALUES("307","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:18");
INSERT INTO system_audit_logs VALUES("308","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:19");
INSERT INTO system_audit_logs VALUES("309","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:19");
INSERT INTO system_audit_logs VALUES("310","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:19");
INSERT INTO system_audit_logs VALUES("311","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:19");
INSERT INTO system_audit_logs VALUES("312","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:19");
INSERT INTO system_audit_logs VALUES("313","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:20");
INSERT INTO system_audit_logs VALUES("314","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:20");
INSERT INTO system_audit_logs VALUES("315","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:20");
INSERT INTO system_audit_logs VALUES("316","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:21");
INSERT INTO system_audit_logs VALUES("317","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:21");
INSERT INTO system_audit_logs VALUES("318","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:16:21");
INSERT INTO system_audit_logs VALUES("319","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:17:06");
INSERT INTO system_audit_logs VALUES("320","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:08");
INSERT INTO system_audit_logs VALUES("321","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:09");
INSERT INTO system_audit_logs VALUES("322","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:09");
INSERT INTO system_audit_logs VALUES("323","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:10");
INSERT INTO system_audit_logs VALUES("324","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:10");
INSERT INTO system_audit_logs VALUES("325","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:10");
INSERT INTO system_audit_logs VALUES("326","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:10");
INSERT INTO system_audit_logs VALUES("327","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:10");
INSERT INTO system_audit_logs VALUES("328","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:11");
INSERT INTO system_audit_logs VALUES("329","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:11");
INSERT INTO system_audit_logs VALUES("330","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:11");
INSERT INTO system_audit_logs VALUES("331","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:11");
INSERT INTO system_audit_logs VALUES("332","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:11");
INSERT INTO system_audit_logs VALUES("333","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:12");
INSERT INTO system_audit_logs VALUES("334","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:12");
INSERT INTO system_audit_logs VALUES("335","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:12");
INSERT INTO system_audit_logs VALUES("336","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:12");
INSERT INTO system_audit_logs VALUES("337","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:12");
INSERT INTO system_audit_logs VALUES("338","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:13");
INSERT INTO system_audit_logs VALUES("339","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:13");
INSERT INTO system_audit_logs VALUES("340","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:13");
INSERT INTO system_audit_logs VALUES("341","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:13");
INSERT INTO system_audit_logs VALUES("342","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:13");
INSERT INTO system_audit_logs VALUES("343","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:13");
INSERT INTO system_audit_logs VALUES("344","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:15");
INSERT INTO system_audit_logs VALUES("345","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:15");
INSERT INTO system_audit_logs VALUES("346","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:17:31");
INSERT INTO system_audit_logs VALUES("347","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:34");
INSERT INTO system_audit_logs VALUES("348","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:17:35");
INSERT INTO system_audit_logs VALUES("349","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:17:38");
INSERT INTO system_audit_logs VALUES("350","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:41");
INSERT INTO system_audit_logs VALUES("351","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:17:42");
INSERT INTO system_audit_logs VALUES("352","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:17:51");
INSERT INTO system_audit_logs VALUES("353","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:17:52");
INSERT INTO system_audit_logs VALUES("354","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:16","Completed","2026-01-28 00:18:01");
INSERT INTO system_audit_logs VALUES("355","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:18:02");
INSERT INTO system_audit_logs VALUES("356","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:18","Completed","2026-01-28 00:18:09");
INSERT INTO system_audit_logs VALUES("357","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:18:10");
INSERT INTO system_audit_logs VALUES("358","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:22:50");
INSERT INTO system_audit_logs VALUES("359","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:22:54");
INSERT INTO system_audit_logs VALUES("360","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:18","Completed","2026-01-28 00:22:57");
INSERT INTO system_audit_logs VALUES("361","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:23:02");
INSERT INTO system_audit_logs VALUES("362","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:23:03");
INSERT INTO system_audit_logs VALUES("363","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:23","Completed","2026-01-28 00:23:12");
INSERT INTO system_audit_logs VALUES("364","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:23:13");
INSERT INTO system_audit_logs VALUES("365","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:23","Completed","2026-01-28 00:23:17");
INSERT INTO system_audit_logs VALUES("366","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:23:20");
INSERT INTO system_audit_logs VALUES("367","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:23:21");
INSERT INTO system_audit_logs VALUES("368","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:23:39");
INSERT INTO system_audit_logs VALUES("369","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T01:23","Completed","2026-01-28 00:23:43");
INSERT INTO system_audit_logs VALUES("370","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:23:46");
INSERT INTO system_audit_logs VALUES("371","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:23:50");
INSERT INTO system_audit_logs VALUES("372","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:23:56");
INSERT INTO system_audit_logs VALUES("373","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:24:02");
INSERT INTO system_audit_logs VALUES("374","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:24:12");
INSERT INTO system_audit_logs VALUES("375","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:27:43");
INSERT INTO system_audit_logs VALUES("376","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:27:56");
INSERT INTO system_audit_logs VALUES("377","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:28:02");
INSERT INTO system_audit_logs VALUES("378","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:28:11");
INSERT INTO system_audit_logs VALUES("379","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:28:21");
INSERT INTO system_audit_logs VALUES("380","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:31:30");
INSERT INTO system_audit_logs VALUES("381","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:31:36");
INSERT INTO system_audit_logs VALUES("382","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:31:50");
INSERT INTO system_audit_logs VALUES("383","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:31:53");
INSERT INTO system_audit_logs VALUES("384","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:31:54");
INSERT INTO system_audit_logs VALUES("385","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:31:55");
INSERT INTO system_audit_logs VALUES("386","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:34:59");
INSERT INTO system_audit_logs VALUES("387","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:35:02");
INSERT INTO system_audit_logs VALUES("388","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:35:03");
INSERT INTO system_audit_logs VALUES("389","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:35:06");
INSERT INTO system_audit_logs VALUES("390","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:35:07");
INSERT INTO system_audit_logs VALUES("391","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:35","Completed","2026-01-28 00:35:11");
INSERT INTO system_audit_logs VALUES("392","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:35:12");
INSERT INTO system_audit_logs VALUES("393","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:35","Completed","2026-01-28 00:35:14");
INSERT INTO system_audit_logs VALUES("394","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:35:15");
INSERT INTO system_audit_logs VALUES("395","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:35","Completed","2026-01-28 00:35:19");
INSERT INTO system_audit_logs VALUES("396","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:35:20");
INSERT INTO system_audit_logs VALUES("397","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T17:35","Completed","2026-01-28 00:35:22");
INSERT INTO system_audit_logs VALUES("398","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:35:25");
INSERT INTO system_audit_logs VALUES("399","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:35:49");
INSERT INTO system_audit_logs VALUES("400","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:36:00");
INSERT INTO system_audit_logs VALUES("401","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:36:06");
INSERT INTO system_audit_logs VALUES("402","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:42:46");
INSERT INTO system_audit_logs VALUES("403","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:43:24");
INSERT INTO system_audit_logs VALUES("404","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:43:28");
INSERT INTO system_audit_logs VALUES("405","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:44:17");
INSERT INTO system_audit_logs VALUES("406","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:49:16");
INSERT INTO system_audit_logs VALUES("407","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:49:19");
INSERT INTO system_audit_logs VALUES("408","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:52:43");
INSERT INTO system_audit_logs VALUES("409","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:52:51");
INSERT INTO system_audit_logs VALUES("410","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:52:53");
INSERT INTO system_audit_logs VALUES("411","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:57:04");
INSERT INTO system_audit_logs VALUES("412","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 00:57:52");
INSERT INTO system_audit_logs VALUES("413","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:59:50");
INSERT INTO system_audit_logs VALUES("414","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:59:55");
INSERT INTO system_audit_logs VALUES("415","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 00:59:59");
INSERT INTO system_audit_logs VALUES("416","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 01:00:07");
INSERT INTO system_audit_logs VALUES("417","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:01:05");
INSERT INTO system_audit_logs VALUES("418","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T02:01","Completed","2026-01-28 01:01:12");
INSERT INTO system_audit_logs VALUES("419","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 01:03:28");
INSERT INTO system_audit_logs VALUES("420","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T02:01","Completed","2026-01-28 01:03:33");
INSERT INTO system_audit_logs VALUES("421","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 01:03:33");
INSERT INTO system_audit_logs VALUES("422","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T02:01","Completed","2026-01-28 01:03:37");
INSERT INTO system_audit_logs VALUES("423","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 01:03:38");
INSERT INTO system_audit_logs VALUES("424","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T02:01","Completed","2026-01-28 01:03:41");
INSERT INTO system_audit_logs VALUES("425","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 01:03:41");
INSERT INTO system_audit_logs VALUES("426","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T02:01","Completed","2026-01-28 01:03:51");
INSERT INTO system_audit_logs VALUES("427","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 01:03:52");
INSERT INTO system_audit_logs VALUES("428","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:05:41");
INSERT INTO system_audit_logs VALUES("429","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:05:45");
INSERT INTO system_audit_logs VALUES("430","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:07:00");
INSERT INTO system_audit_logs VALUES("431","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:07:04");
INSERT INTO system_audit_logs VALUES("432","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:09:56");
INSERT INTO system_audit_logs VALUES("433","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:10:02");
INSERT INTO system_audit_logs VALUES("434","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:10:11");
INSERT INTO system_audit_logs VALUES("435","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:10:36");
INSERT INTO system_audit_logs VALUES("436","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:14:50");
INSERT INTO system_audit_logs VALUES("437","Recovery Time Updated","Gab","Expected recovery set to 2026-01-27T18:14","Completed","2026-01-28 01:14:55");
INSERT INTO system_audit_logs VALUES("438","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:15:19");
INSERT INTO system_audit_logs VALUES("439","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:15:23");
INSERT INTO system_audit_logs VALUES("440","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:20:47");
INSERT INTO system_audit_logs VALUES("441","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:20:49");
INSERT INTO system_audit_logs VALUES("442","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:20:52");
INSERT INTO system_audit_logs VALUES("443","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:23:38");
INSERT INTO system_audit_logs VALUES("444","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:08");
INSERT INTO system_audit_logs VALUES("445","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:11");
INSERT INTO system_audit_logs VALUES("446","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:15");
INSERT INTO system_audit_logs VALUES("447","Recovery Time Updated","Gab","Expected recovery set to 2026-01-28T02:24","Completed","2026-01-28 01:24:23");
INSERT INTO system_audit_logs VALUES("448","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:31");
INSERT INTO system_audit_logs VALUES("449","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:33");
INSERT INTO system_audit_logs VALUES("450","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:36");
INSERT INTO system_audit_logs VALUES("451","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:39");
INSERT INTO system_audit_logs VALUES("452","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:24:41");
INSERT INTO system_audit_logs VALUES("453","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:28:26");
INSERT INTO system_audit_logs VALUES("454","Recovery Time Updated","Gab","Expected recovery set to ","Completed","2026-01-28 01:28:31");
INSERT INTO system_audit_logs VALUES("455","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 01:29:02");
INSERT INTO system_audit_logs VALUES("456","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 01:29:04");
INSERT INTO system_audit_logs VALUES("457","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 01:29:10");
INSERT INTO system_audit_logs VALUES("458","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 01:29:12");
INSERT INTO system_audit_logs VALUES("459","System Status Update","Gab","Maintenance: OFF | Recovery: 2026-01-28T02:29","Completed","2026-01-28 01:29:15");
INSERT INTO system_audit_logs VALUES("460","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-28T02:29","Completed","2026-01-28 01:29:20");
INSERT INTO system_audit_logs VALUES("461","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 01:29:38");
INSERT INTO system_audit_logs VALUES("462","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 01:35:02");
INSERT INTO system_audit_logs VALUES("463","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 01:35:04");
INSERT INTO system_audit_logs VALUES("464","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 01:37:22");
INSERT INTO system_audit_logs VALUES("465","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 01:37:24");
INSERT INTO system_audit_logs VALUES("466","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 01:38:38");
INSERT INTO system_audit_logs VALUES("467","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 01:38:41");
INSERT INTO system_audit_logs VALUES("468","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 01:38:54");
INSERT INTO system_audit_logs VALUES("469","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 18:52:10");
INSERT INTO system_audit_logs VALUES("470","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 18:54:42");
INSERT INTO system_audit_logs VALUES("471","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 19:20:45");
INSERT INTO system_audit_logs VALUES("472","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 19:20:48");
INSERT INTO system_audit_logs VALUES("473","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 19:20:52");
INSERT INTO system_audit_logs VALUES("474","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 19:21:19");
INSERT INTO system_audit_logs VALUES("475","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 19:21:24");
INSERT INTO system_audit_logs VALUES("476","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 19:21:25");
INSERT INTO system_audit_logs VALUES("477","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 19:21:33");
INSERT INTO system_audit_logs VALUES("478","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-28 19:21:35");
INSERT INTO system_audit_logs VALUES("479","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 19:21:50");
INSERT INTO system_audit_logs VALUES("480","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 19:22:01");
INSERT INTO system_audit_logs VALUES("481","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 19:22:05");
INSERT INTO system_audit_logs VALUES("482","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-28 21:50:05");
INSERT INTO system_audit_logs VALUES("483","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 21:50:08");
INSERT INTO system_audit_logs VALUES("484","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-28T23:01","Completed","2026-01-28 22:01:24");
INSERT INTO system_audit_logs VALUES("485","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-28 22:03:27");
INSERT INTO system_audit_logs VALUES("486","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-29 00:42:58");
INSERT INTO system_audit_logs VALUES("487","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-29 01:03:56");
INSERT INTO system_audit_logs VALUES("488","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-29 01:07:19");
INSERT INTO system_audit_logs VALUES("489","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-29 01:07:20");
INSERT INTO system_audit_logs VALUES("490","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-29 01:07:47");
INSERT INTO system_audit_logs VALUES("491","System Status Update","mandy","Maintenance: ON | Recovery: 2026-01-29T22:32","Completed","2026-01-29 21:32:19");
INSERT INTO system_audit_logs VALUES("492","System Status Update","mandy","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-29 21:32:28");
INSERT INTO system_audit_logs VALUES("493","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-29 22:36:52");
INSERT INTO system_audit_logs VALUES("494","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-29 22:36:58");
INSERT INTO system_audit_logs VALUES("495","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-29 22:42:35");
INSERT INTO system_audit_logs VALUES("496","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-29 22:42:45");
INSERT INTO system_audit_logs VALUES("497","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-29 22:42:48");
INSERT INTO system_audit_logs VALUES("498","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-31 14:37:39");
INSERT INTO system_audit_logs VALUES("499","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 14:37:45");
INSERT INTO system_audit_logs VALUES("500","System Status Update","Gab","Maintenance: OFF | Recovery: 2026-01-31T15:37","Completed","2026-01-31 14:37:51");
INSERT INTO system_audit_logs VALUES("501","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-31T17:37","Completed","2026-01-31 14:37:58");
INSERT INTO system_audit_logs VALUES("502","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 14:38:08");
INSERT INTO system_audit_logs VALUES("503","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-31 14:49:20");
INSERT INTO system_audit_logs VALUES("504","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 14:49:23");
INSERT INTO system_audit_logs VALUES("505","System Status Update","Gab","Maintenance: OFF | Recovery: 2026-01-31T17:49","Completed","2026-01-31 14:49:30");
INSERT INTO system_audit_logs VALUES("506","System Status Update","Gab","Maintenance: OFF | Recovery: 2026-01-31T15:56","Completed","2026-01-31 14:56:30");
INSERT INTO system_audit_logs VALUES("507","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-31T15:56","Completed","2026-01-31 14:56:40");
INSERT INTO system_audit_logs VALUES("508","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 14:57:42");
INSERT INTO system_audit_logs VALUES("509","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-31T15:57","Completed","2026-01-31 14:57:57");
INSERT INTO system_audit_logs VALUES("510","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 14:58:15");
INSERT INTO system_audit_logs VALUES("511","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-31T15:58","Completed","2026-01-31 14:58:39");
INSERT INTO system_audit_logs VALUES("512","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 14:59:12");
INSERT INTO system_audit_logs VALUES("513","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-31T16:00","Completed","2026-01-31 15:00:44");
INSERT INTO system_audit_logs VALUES("514","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 15:01:39");
INSERT INTO system_audit_logs VALUES("515","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-31T16:04","Completed","2026-01-31 15:04:23");
INSERT INTO system_audit_logs VALUES("516","System Status Update","Gab","Maintenance: ON | Recovery: Not Specified","Completed","2026-01-31 15:04:29");
INSERT INTO system_audit_logs VALUES("517","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 15:18:19");
INSERT INTO system_audit_logs VALUES("518","System Status Update","Gab","Maintenance: ON | Recovery: 2026-01-31T19:35","Completed","2026-01-31 16:35:05");
INSERT INTO system_audit_logs VALUES("519","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 16:35:17");
INSERT INTO system_audit_logs VALUES("520","System Status Update","admin2","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 16:36:03");
INSERT INTO system_audit_logs VALUES("521","System Status Update","admin2","Maintenance: ON | Recovery: 2026-01-31T19:36","Completed","2026-01-31 16:36:16");
INSERT INTO system_audit_logs VALUES("522","System Status Update","admin2","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 16:36:24");
INSERT INTO system_audit_logs VALUES("523","System Status Update","admin2","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 16:53:50");
INSERT INTO system_audit_logs VALUES("524","System Status Update","admin2","Maintenance: OFF | Recovery: 2026-02-01T16:53","Completed","2026-01-31 16:53:57");
INSERT INTO system_audit_logs VALUES("525","System Status Update","admin2","Maintenance: ON | Recovery: 2026-02-01T16:54","Completed","2026-01-31 16:54:09");
INSERT INTO system_audit_logs VALUES("526","System Status Update","admin2","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 17:00:09");
INSERT INTO system_audit_logs VALUES("527","System Status Update","Gab","Maintenance: ON | Recovery: 2026-02-01T02:09","Completed","2026-01-31 23:09:21");
INSERT INTO system_audit_logs VALUES("528","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 23:09:47");
INSERT INTO system_audit_logs VALUES("529","System Status Update","Gab","Maintenance: ON | Recovery: 2026-02-01T00:13","Completed","2026-01-31 23:13:34");
INSERT INTO system_audit_logs VALUES("530","System Status Update","Gab","Maintenance: OFF | Recovery: 2026-02-01T00:15","Completed","2026-01-31 23:17:12");
INSERT INTO system_audit_logs VALUES("531","System Status Update","Gab","Maintenance: OFF | Recovery: 2026-02-01T00:17","Completed","2026-01-31 23:17:43");
INSERT INTO system_audit_logs VALUES("532","System Status Update","Gab","Maintenance: ON | Recovery: 2026-02-01T00:18","Completed","2026-01-31 23:20:01");
INSERT INTO system_audit_logs VALUES("533","System Status Update","Gab","Maintenance: OFF | Recovery: Not Specified","Completed","2026-01-31 23:20:12");
INSERT INTO system_audit_logs VALUES("534","Maintenance Toggle","Gab","System maintenance mode turned OFF","Completed","2026-01-31 23:32:11");


CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1790 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO system_settings VALUES("1","store_name","Empire - Shop","2026-01-25 16:59:25");
INSERT INTO system_settings VALUES("2","maintenance_mode","0","2026-01-31 23:20:12");
INSERT INTO system_settings VALUES("23","maint_message","","2026-01-31 23:20:12");
INSERT INTO system_settings VALUES("24","ip_whitelist","","2026-01-26 01:12:51");
INSERT INTO system_settings VALUES("25","recovery_time","","2026-01-31 23:20:12");
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
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO users VALUES("30","Gab","admin321@gmail.com","$2y$10$kLMBHM8ilzpdN4pSemAqruacxDdb9WtZTOEIL81n5cU2PETKAWngS","admin","Active","2025-09-07 00:05:08",NULL,NULL,"profile_30_1769794463.jpg");
INSERT INTO users VALUES("53","Mandy Francisco","Mandyfrancisco895@gmail.com","$2y$10$4A8LjZynhivBjV2jKoH5UefwbJ/az67si4/Tc7eyfY8fHvVyeXKSy","user","Active","2025-11-23 21:06:06","09938433970","62134 makati mercedes city","1763903226_mandy.jpg");
INSERT INTO users VALUES("54","Laurence Vargas","aujscvargas@gmail.com","$2y$10$3TuAG5UQMYeRKvNYBcNRLOtbJKE1jaHB/hIBWeew2NaDarsg9mFE2","user","Active","2025-11-23 21:13:09","09938433971","731 lansones st napico manggahan pasig city ","1763903658_duds.jpg");
INSERT INTO users VALUES("55","Haillie Geco","gecohaillie560@gmail.com","$2y$10$NBW8JWWqHNtR6WsZ/n/A8eUkITpV0D84xL9zRtpXbPGJPG7velAzS","user","Active","2025-11-23 21:16:05","09938433890","blck 8. Kasigahan St. Pasig city","1763903849_aa522e76-6c4c-4b74-a7d9-2afc6e4fa036.jpg");
INSERT INTO users VALUES("61","Gabriel Vargas","gabrielvargas0423@gmail.com","$2y$10$YKGH/jtsfffR4p7M0oceROf/bT965iiWMQkRkueaQ2JUqSeOHGRp2","user","Active","2026-01-14 11:54:24","09938433970","62134 makati mercedes city","user");
INSERT INTO users VALUES("91","mandy","admin4321@gmail.com","$2y$10$ryyJ4Vm3l10VhPO88hr9huQF/WJs.5U9c1jyLbR7jVmlw42r7zzi.","admin","Active","2026-01-29 00:23:00",NULL,NULL,"profile_91_1769689824.jpg");
INSERT INTO users VALUES("98","renz","adminrenz123@gmail.com","$2y$10$HoDv./nRqIy9/r4.zW96LuGfh3PS/OY0ozpEY5XKZiLSNizS5vZ6i","admin","Active","2026-01-29 22:43:57","","",NULL);
INSERT INTO users VALUES("99","renz123","admin12345@gmail.com","$2y$10$Vk1hJvAospM.wWeOX/iWHuICljEd54LBvio9oTWyPyw6hREDRGl7e","admin","Active","2026-01-29 22:45:02","09360562346","dgddg","profile_99_1769698016.jpg");
INSERT INTO users VALUES("114","staff","staff321@gmail.com","$2y$10$LxWxK1PFCIJFWUo6loXl0uHTcqKabnOD3P.v2dq6lKNHIL0XDPQkG","staff","Active","2026-01-31 01:26:13","","",NULL);
INSERT INTO users VALUES("115","staff1233","stafffffffffff@gmail.com","$2y$10$qHW3btLkyi.u9hIRv5qsnu6.nnR4dk3nOqrwQNI36kPjcTPr69Tl.","staff","Active","2026-01-31 02:14:59","","",NULL);
INSERT INTO users VALUES("117","admin2","admin45321@gmail.com","$2y$10$N5cHBzBODOy9lNyjVk8D..73TdPCKsLpgnzfetmVsIs/f0yTAO1u.","admin","Active","2026-01-31 16:35:48","","",NULL);
INSERT INTO users VALUES("118","stafff90","staff1234@gmail.com","$2y$10$vHXhd8Gr4Y7X7mzvwrJqe.S21jvpOEuNFReWQvsVgOg5469HB0u16","staff","Active","2026-01-31 23:23:06","","",NULL);
