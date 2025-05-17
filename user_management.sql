-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 17, 2025 lúc 05:52 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `user_management`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(50) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_password` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `reset_password`, `avatar`, `created_at`, `role`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$W7kPq3nJ7kL8mN2uO7pX9vK1wL2mN3pQ4r5t6uY7v8w9x0y1Z5Z5Z5', NULL, NULL, '2025-05-17 01:22:06', 1),
(2, 'minh', 'mi@gmail.com', '$2y$10$XC3vIP7RSBv8jCDy2WFEeuyj39E/a7daSUu1DSU4zATiuZ1p8CP2O', NULL, 'https://i.pinimg.com/originals/b2/ea/a0/b2eaa0d4918d54021f9c7aa3fc3d3cf3.jpg', '2025-05-16 10:20:18', 0),
(3, 'mi', 'minhgs@gmail.com', '$2y$10$XKaOi8q5fdqku.oy68GJBeyg8iH/I/6LFReSXNqBGNpqthfuyAkc.', NULL, 'uploads/avatar_3_1747406792.jpg', '2025-05-16 14:18:20', 0),
(4, 'm', 'minhs@gmail.com', '$2y$10$.dnsII/GZ/B6IAGykYMTa.U7Wxf.bttfG7JaWvmYisN64EuyMJK0W', NULL, 'uploads/avatar_4_1747409219.jpg', '2025-05-16 14:51:15', 0),
(5, 'minhgs', 'minh@gmail.com', '$2y$10$1E72PCSrn9yTcZOmutcdfuRdwoQSgFOc4CxKjiPKJtdUa/5Q0RAM2', NULL, 'uploads/avatar_5_1747450757.jpg', '2025-05-16 23:29:31', 0),
(10, 'minhhhh', 'i@gmail.com', '$2y$10$lxX0f2SQFO00/0pw9ha2IeDDYK2TjjtXP5yMmRSLkSRFl6KrVwGdy', NULL, NULL, '2025-05-17 03:33:54', 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
