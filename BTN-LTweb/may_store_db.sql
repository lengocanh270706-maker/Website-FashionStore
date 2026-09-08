DROP DATABASE IF EXISTS may_store_db;
CREATE DATABASE may_store_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE may_store_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    avatar VARCHAR(255),
    dob DATE,
    gender VARCHAR(20),
    role ENUM('user','admin') DEFAULT 'user',
    status INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id,name,email,password,phone,address,avatar,dob,gender,role,status,created_at) VALUES
(1,'Admin','admin@gmail.com','admin123','0901234567','TP. Hồ Chí Minh',NULL,NULL,NULL,'admin',1,'2026-09-04 15:44:43'),
(2,'Nguyễn Văn An','an@gmail.com','an1234','0912345678','TP. Hồ Chí Minh',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(3,'Trần Thị Bình','binh@gmail.com','binh1234','0987654321','Đồng Nai',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(4,'Lê Minh Anh','anh@gmail.com','anh1234','0933333333','TP. Hồ Chí Minh',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(5,'Phạm Hoàng Nam','nam@gmail.com','nam1234','0944444444','Bình Dương',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(6,'Võ Ngọc Mai','mai@gmail.com','mai1234','0955555555','TP. Hồ Chí Minh',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(7,'Nguyễn Khánh Linh','linh@gmail.com','linh1234','0966666666','Long An',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(8,'Trần Minh Khang','khang@gmail.com','khang1234','0977777777','123, Phường Ba Đình, Thành phố Hà Nội',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(9,'Đặng Thu Hà','ha@gmail.com','ha1234','0988888888','Đồng Nai',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(10,'Bùi Gia Huy','huy@gmail.com','huy1234','0999999999','TP. Hồ Chí Minh',NULL,NULL,NULL,'user',1,'2026-09-04 15:44:43'),
(12,'vy','vy@gmail.com','vy1234','','',NULL,NULL,NULL,'user',1,'2026-09-06 17:34:52');

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status INT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (id,name,description,status) VALUES
(1,'Áo','Các loại áo thời trang',1),
(2,'Quần','Các loại quần thời trang',1),
(3,'Váy','Các loại váy nữ',1),
(4,'Áo khoác','Các loại áo khoác',1),
(5,'Phụ kiện','Phụ kiện thời trang',1),
(6,'Đồ thể thao','Trang phục thể thao',0);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    description TEXT,
    quantity INT DEFAULT 0,
    main_image VARCHAR(255),
    status INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    gender ENUM('male','female') NOT NULL DEFAULT 'female',
    FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (id,category_id,name,price,description,quantity,main_image,status,created_at,gender) VALUES
(1,1,'Áo thun Basic trắng',199000,'Áo thun cotton màu trắng',1,'ao-thun-trang.jpg',1,'2026-09-04 15:44:43','female'),
(2,1,'Áo thun Basic đen',199000,'Áo thun cotton màu đen',45,'ao-thun-den.jpg',1,'2026-09-04 15:44:43','female'),
(3,1,'Áo polo nam',299000,'Áo polo nam phong cách trẻ trung',40,'ao-polo.jpg',1,'2026-09-04 15:44:43','male'),
(4,1,'Áo sơ mi trắng',329000,'Áo sơ mi trắng form basic',35,'ao-so-mi.jpg',1,'2026-09-04 15:44:43','female'),
(5,1,'Áo croptop nữ',189000,'Áo croptop nữ trẻ trung',30,'ao-croptop.jpg',1,'2026-09-04 15:44:43','female'),
(6,1,'Áo len cổ lọ',359000,'Áo len mềm mại',25,'ao-len.jpg',1,'2026-09-04 15:44:43','female'),
(7,1,'Áo cardigan',329000,'Áo cardigan nữ thời trang',20,'cardigan.jpg',1,'2026-09-04 15:44:43','female'),
(8,2,'Quần jean xanh',399000,'Quần jean denim xanh',30,'quan-jean.jpg',1,'2026-09-04 15:44:43','female'),
(9,2,'Quần jean đen',419000,'Quần jean đen basic',25,'quan-jean-den.jpg',1,'2026-09-04 15:44:43','female'),
(10,2,'Quần kaki nam',349000,'Quần kaki nam form basic',25,'quan-kaki.jpg',1,'2026-09-04 15:44:43','male'),
(11,2,'Quần short nam',229000,'Quần short nam thời trang',35,'quan-short.jpg',1,'2026-09-04 15:44:43','male'),
(12,2,'Quần ống rộng nữ',359000,'Quần ống rộng nữ',28,'quan-ong-rong.jpg',1,'2026-09-04 15:44:43','female'),
(13,2,'Quần jogger',299000,'Quần jogger thể thao',30,'quan-jogger.jpg',1,'2026-09-04 15:44:43','female'),
(14,3,'Váy hoa nữ',459000,'Váy hoa nữ phong cách nhẹ nhàng',20,'vay-hoa.jpg',1,'2026-09-04 15:44:43','female'),
(15,3,'Váy chữ A',399000,'Váy chữ A thanh lịch',25,'vay-chu-a.jpg',1,'2026-09-04 15:44:43','female'),
(16,3,'Váy body đen',429000,'Váy body màu đen',18,'vay-body.jpg',1,'2026-09-04 15:44:43','female'),
(17,3,'Váy maxi',499000,'Váy maxi nữ thời trang',20,'vay-maxi.jpg',1,'2026-09-04 15:44:43','female'),
(18,3,'Chân váy jean',299000,'Chân váy jean nữ',25,'chan-vay-jean.jpg',1,'2026-09-04 15:44:43','female'),
(19,4,'Áo khoác bomber',599000,'Áo khoác bomber năng động',15,'ao-bomber.jpg',1,'2026-09-04 15:44:43','female'),
(20,4,'Áo khoác jean',549000,'Áo khoác jean thời trang',18,'ao-khoac-jean.jpg',1,'2026-09-04 15:44:43','female'),
(21,4,'Áo hoodie',459000,'Áo hoodie unisex',25,'ao-hoodie.jpg',1,'2026-09-04 15:44:43','female'),
(22,4,'Áo khoác dù',399000,'Áo khoác dù chống nắng',20,'ao-khoac-du.jpg',1,'2026-09-04 15:44:43','female'),
(23,5,'Mũ lưỡi trai',99000,'Mũ lưỡi trai thời trang',40,'mu.jpg',1,'2026-09-04 15:44:43','female'),
(24,5,'Túi tote',159000,'Túi tote canvas',35,'tui-tote.jpg',1,'2026-09-04 15:44:43','female'),
(25,5,'Thắt lưng da',199000,'Thắt lưng da basic',25,'that-lung.jpg',1,'2026-09-04 15:44:43','female'),
(26,6,'Áo thể thao',259000,'Áo thể thao nam nữ',30,'ao-the-thao.jpg',1,'2026-09-04 15:44:43','female'),
(27,6,'Quần thể thao',299000,'Quần thể thao thoải mái',30,'quan-the-thao.jpg',1,'2026-09-04 15:44:43','female');

CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(20) NOT NULL,
    color VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO product_variants (id,product_id,size,color,quantity) VALUES
(1,1,'S','Trắng',10),(2,1,'M','Trắng',20),(3,1,'L','Trắng',20),
(4,2,'S','Đen',10),(5,2,'M','Đen',20),(6,2,'L','Đen',15),
(7,3,'M','Đen',15),(8,3,'L','Đen',15),(9,3,'XL','Đen',10),
(10,4,'M','Trắng',15),(11,4,'L','Trắng',12),(12,4,'XL','Trắng',8),
(13,5,'S','Hồng',10),(14,5,'M','Hồng',10),(15,5,'L','Hồng',10),
(16,6,'S','Xám',8),(17,6,'M','Xám',10),(18,6,'L','Xám',7),
(19,7,'S','Be',7),(20,7,'M','Be',7),(21,7,'L','Be',6),
(22,8,'S','Xanh',10),(23,8,'M','Xanh',10),(24,8,'L','Xanh',10),
(25,9,'S','Đen',8),(26,9,'M','Đen',9),(27,9,'L','Đen',8),
(28,10,'M','Be',12),(29,10,'L','Be',13),
(30,11,'M','Đen',15),(31,11,'L','Đen',20),
(32,12,'S','Đen',8),(33,12,'M','Đen',10),(34,12,'L','Đen',10),
(35,13,'M','Xám',10),(36,13,'L','Xám',10),(37,13,'XL','Xám',10),
(38,14,'S','Hoa',5),(39,14,'M','Hoa',10),(40,14,'L','Hoa',5),
(41,15,'S','Đen',8),(42,15,'M','Đen',10),(43,15,'L','Đen',7),
(44,16,'S','Đen',5),(45,16,'M','Đen',8),(46,16,'L','Đen',5),
(47,17,'S','Hoa',6),(48,17,'M','Hoa',7),(49,17,'L','Hoa',7),
(50,18,'S','Xanh',8),(51,18,'M','Xanh',9),(52,18,'L','Xanh',8),
(53,19,'M','Đen',5),(54,19,'L','Đen',10),
(55,20,'M','Xanh',8),(56,20,'L','Xanh',10),
(57,21,'M','Xám',8),(58,21,'L','Xám',10),(59,21,'XL','Xám',7),
(60,22,'M','Đen',10),(61,22,'L','Đen',10),
(62,23,'Free','Đen',40),(63,24,'Free','Trắng',35),(64,25,'Free','Nâu',25),
(65,26,'M','Đen',15),(66,26,'L','Đen',15),(67,27,'M','Đen',15),(68,27,'L','Đen',15);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO product_images (id,product_id,image) VALUES
(1,1,'ao-thun-trang-1.jpg'),(2,1,'ao-thun-trang-2.jpg'),(3,1,'ao-thun-trang-3.jpg'),
(4,2,'ao-thun-den-1.jpg'),(5,2,'ao-thun-den-2.jpg'),
(6,3,'ao-polo-1.jpg'),(7,3,'ao-polo-2.jpg'),
(8,4,'ao-so-mi-1.jpg'),(9,4,'ao-so-mi-2.jpg'),
(10,5,'ao-croptop-1.jpg'),(11,5,'ao-croptop-2.jpg'),
(12,6,'ao-len-1.jpg'),(13,6,'ao-len-2.jpg'),
(14,7,'cardigan-1.jpg'),(15,7,'cardigan-2.jpg'),
(16,8,'quan-jean-1.jpg'),(17,8,'quan-jean-2.jpg'),
(18,9,'quan-jean-den-1.jpg'),(19,9,'quan-jean-den-2.jpg'),
(20,10,'quan-kaki-1.jpg'),(21,10,'quan-kaki-2.jpg'),
(22,11,'quan-short-1.jpg'),(23,11,'quan-short-2.jpg'),
(24,12,'quan-ong-rong-1.jpg'),(25,12,'quan-ong-rong-2.jpg'),
(26,13,'quan-jogger-1.jpg'),(27,13,'quan-jogger-2.jpg'),
(28,14,'vay-hoa-1.jpg'),(29,14,'vay-hoa-2.jpg'),
(30,15,'vay-chu-a-1.jpg'),(31,15,'vay-chu-a-2.jpg'),
(32,16,'vay-body-1.jpg'),(33,16,'vay-body-2.jpg'),
(34,17,'vay-maxi-1.jpg'),(35,17,'vay-maxi-2.jpg'),
(36,18,'chan-vay-jean-1.jpg'),(37,18,'chan-vay-jean-2.jpg'),
(38,19,'ao-bomber-1.jpg'),(39,19,'ao-bomber-2.jpg'),
(40,20,'ao-khoac-jean-1.jpg'),(41,20,'ao-khoac-jean-2.jpg'),
(42,21,'ao-hoodie-1.jpg'),(43,21,'ao-hoodie-2.jpg'),
(44,22,'ao-khoac-du-1.jpg'),(45,22,'ao-khoac-du-2.jpg'),
(46,23,'mu-1.jpg'),(47,23,'mu-2.jpg'),
(48,24,'tui-tote-1.jpg'),(49,24,'tui-tote-2.jpg'),
(50,25,'that-lung-1.jpg'),(51,25,'that-lung-2.jpg'),
(52,26,'ao-the-thao-1.jpg'),(53,26,'ao-the-thao-2.jpg'),
(54,27,'quan-the-thao-1.jpg'),(55,27,'quan-the-thao-2.jpg');

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cart (id,user_id,created_at,updated_at) VALUES
(1,2,'2026-09-04 15:44:43','2026-09-04 23:53:27'),
(2,3,'2026-09-04 15:44:43','2026-09-05 09:22:31'),
(3,4,'2026-09-04 15:44:43','2026-09-06 00:36:53'),
(4,5,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(5,6,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(6,7,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(7,8,'2026-09-04 22:58:18','2026-09-06 23:34:30'),
(8,9,'2026-09-05 09:21:24','2026-09-05 09:21:24'),
(9,12,'2026-09-06 17:35:30','2026-09-06 17:37:04'),
(10,1,'2026-09-07 00:17:45','2026-09-07 00:17:45');

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES cart(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cart_items (id,cart_id,product_id,variant_id,quantity,price) VALUES
(6,4,5,14,2,189000),(7,5,11,31,1,229000),(8,6,24,63,1,159000),(19,7,26,65,1,259000);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    total_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    note TEXT,
    status ENUM('pending','confirmed','shipping','completed','cancelled') DEFAULT 'pending',
    payment_method ENUM('cod','banking') DEFAULT 'cod',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO orders (id,user_id,order_code,total_price,customer_name,phone,address,note,status,payment_method,created_at,updated_at) VALUES
(1,2,'DH000001',797000,'Nguyễn Văn An','0912345678','TP. Hồ Chí Minh','Giao giờ hành chính','completed','cod','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(2,3,'DH000002',599000,'Trần Thị Bình','0987654321','Đồng Nai','Gọi trước khi giao','pending','banking','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(3,4,'DH000003',398000,'Lê Minh Anh','0933333333','TP. Hồ Chí Minh','','confirmed','cod','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(4,5,'DH000004',698000,'Phạm Hoàng Nam','0944444444','Bình Dương','','shipping','cod','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(5,6,'DH000005',458000,'Võ Ngọc Mai','0955555555','TP. Hồ Chí Minh','','completed','banking','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(6,7,'DH000006',499000,'Nguyễn Khánh Linh','0966666666','Long An','','completed','cod','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(7,8,'DH000007',399000,'Trần Minh Khang','0977777777','TP. Hồ Chí Minh','','cancelled','cod','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(8,9,'DH000008',817000,'Đặng Thu Hà','0988888888','Đồng Nai','','completed','banking','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(9,10,'DH000009',549000,'Bùi Gia Huy','0999999999','TP. Hồ Chí Minh','','confirmed','cod','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(10,2,'DH000010',199000,'Nguyễn Văn An','0912345678','TP. Hồ Chí Minh','','pending','cod','2026-09-04 15:44:43','2026-09-04 15:44:43'),
(11,2,'DH20260904185327486',628000,'Nguyễn Văn An','0912345678','TP. Hồ Chí Minh','','confirmed','cod','2026-09-04 23:53:27','2026-09-04 23:53:27'),
(12,3,'DH20260905042231143',948000,'Trần Thị Bình','0987654321','Đồng Nai','','completed','cod','2026-09-05 09:22:31','2026-09-05 09:22:31'),
(13,4,'DH20260905193653650',3023000,'Lê Minh Anh','0933333333','TP. Hồ Chí Minh','','confirmed','cod','2026-09-06 00:36:53','2026-09-06 00:36:53'),
(14,8,'DH20260906112431960',5997000,'Trần Minh Khang','0977777777','TP. Hồ Chí Minh','','cancelled','cod','2026-09-06 16:24:31','2026-09-06 16:30:18'),
(15,8,'DH20260906113124525',189000,'Trần Minh Khang','0977777777','TP. Hồ Chí Minh','','cancelled','cod','2026-09-06 16:31:24','2026-09-06 16:31:57'),
(16,12,'DH20260906123704235',189000,'vy','1234','123, Xã Mỹ Lý, Tỉnh Nghệ An','','confirmed','cod','2026-09-06 17:37:04','2026-09-06 17:37:04'),
(17,8,'DH20260906181727467',688000,'Trần Minh Khang','0977777777','DA, Xã Liên Sơn, Tỉnh Phú Thọ','','pending','cod','2026-09-06 23:17:27','2026-09-06 23:17:27'),
(18,8,'DH20260906183430822',1661000,'Trần Minh Khang','0977777777','123, Phường Ba Đình, Thành phố Hà Nội','','pending','cod','2026-09-06 23:34:30','2026-09-06 23:34:30');

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO order_items (id,order_id,product_id,variant_id,quantity,price) VALUES
(1,1,1,2,2,199000),(2,1,8,23,1,399000),(3,2,19,53,1,599000),
(4,3,2,5,2,199000),(5,4,3,7,1,299000),(6,4,20,55,1,399000),
(7,5,11,31,2,229000),(8,6,17,48,1,499000),(9,7,9,26,1,399000),
(10,8,21,61,1,459000),(11,8,24,63,1,159000),(12,8,25,64,1,199000),
(13,9,20,55,1,549000),(14,10,1,1,1,199000),(15,11,8,23,1,399000),
(16,11,1,2,1,199000),(17,12,14,39,2,459000),(18,13,19,53,3,599000),
(19,13,3,8,4,299000),(20,14,20,56,10,549000),(21,14,24,63,3,159000),
(22,15,24,63,1,159000),(23,16,24,63,1,159000),(24,17,21,59,1,459000),
(25,17,25,64,1,199000),(26,18,25,64,5,199000),(27,18,24,63,4,159000);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    author_id INT NOT NULL,
    status INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO posts (id,title,content,image,author_id,status,created_at,updated_at) VALUES
(1,'Xu hướng thời trang mùa hè 2026','<p>Khám phá những xu hướng thời trang nổi bật trong mùa hè.</p>','fashion-summer.jpg',1,1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(2,'Bí quyết phối đồ đơn giản','<p>Một số cách phối đồ đơn giản và phù hợp với nhiều phong cách.</p>','phoi-do.jpg',1,1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(3,'Cách chọn quần jean phù hợp','<p>Một số mẹo chọn quần jean phù hợp với từng dáng người.</p>','chon-jean.jpg',1,1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(4,'Xu hướng màu sắc năm 2026','<p>Những màu sắc thời trang được yêu thích trong năm nay.</p>','mau-sac.jpg',1,1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(5,'Mẹo bảo quản quần áo','<p>Cách bảo quản quần áo luôn bền đẹp và mới.</p>','bao-quan.jpg',1,1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(6,'Sale cuối tháng','<p>Nhiều sản phẩm đang được giảm giá hấp dẫn.</p>','sale.jpg',1,1,'2026-09-04 15:44:43','2026-09-04 15:44:43');

CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    start_at DATETIME,
    end_at DATETIME,
    status INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO banners (id,title,image,link,start_at,end_at,status,created_at,updated_at) VALUES
(1,'Bộ sưu tập mùa hè','banner-summer.jpg','products.php',NULL,NULL,1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(2,'Sale lên đến 50%','banner-sale.jpg','products.php','2026-09-07 08:00:00','2026-09-10 23:59:59',1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(3,'Thời trang mới 2026','banner-new.jpg','products.php',NULL,NULL,1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(4,'Ưu đãi đặc biệt','banner-sale2.jpg','products.php','2026-09-11 08:00:00','2026-09-15 23:59:59',1,'2026-09-04 15:44:43','2026-09-04 15:44:43'),
(5,'Bộ sưu tập thể thao','banner-sport.jpg','/Website-FashionStore/BTN-LTweb/products.php',NULL,NULL,1,'2026-09-04 15:44:43','2026-09-06 21:32:02');
