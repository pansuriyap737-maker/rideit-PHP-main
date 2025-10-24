-- Create completedtrip table
CREATE TABLE IF NOT EXISTS `completedtrip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `car_number_plate` varchar(50) NOT NULL,
  `pickup` varchar(100) NOT NULL,
  `drop_location` varchar(100) NOT NULL,
  `ride_datetime` datetime NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  KEY `driver_id` (`driver_id`),
  KEY `passenger_id` (`passenger_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;