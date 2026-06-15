-- 1. Tabla principal para almacenar las colecciones creadas por el usuario
CREATE TABLE IF NOT EXISTS `collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_uuid` char(36) NOT NULL COMMENT 'UUID del dueño de la colección',
  `name` varchar(150) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_collections` (`user_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabla pivote para enlazar los libros guardados con sus respectivas colecciones
CREATE TABLE IF NOT EXISTS `collection_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL,
  `saved_book_id` int(11) NOT NULL COMMENT 'Apunta al id de saved_books',
  `added_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_collection_book` (`collection_id`,`saved_book_id`),
  KEY `idx_collection_id` (`collection_id`),
  KEY `idx_saved_book_id` (`saved_book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;