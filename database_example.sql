-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 16 Ara 2025, 17:06:21
-- Sunucu sürümü: 11.8.3-MariaDB-log
-- PHP Sürümü: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `u248562152_giftapp`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `gifts`
--

CREATE TABLE `gifts` (
  `id` int(11) NOT NULL,
  `wall_id` int(11) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `gift_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `position_x` int(11) DEFAULT 0,
  `position_y` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `themes`
--

CREATE TABLE `themes` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `css_class` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `theme_gifts`
--

CREATE TABLE `theme_gifts` (
  `id` int(11) NOT NULL,
  `theme_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `walls`
--

CREATE TABLE `walls` (
  `id` int(11) NOT NULL,
  `wall_uuid` varchar(32) NOT NULL,
  `creator_name` varchar(100) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `theme_id` int(11) DEFAULT NULL,
  `event_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `gifts`
--
ALTER TABLE `gifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wall_id` (`wall_id`);

--
-- Tablo için indeksler `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Tablo için indeksler `theme_gifts`
--
ALTER TABLE `theme_gifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `theme_id` (`theme_id`);

--
-- Tablo için indeksler `walls`
--
ALTER TABLE `walls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wall_uuid` (`wall_uuid`),
  ADD KEY `theme_id` (`theme_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `gifts`
--
ALTER TABLE `gifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `themes`
--
ALTER TABLE `themes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `theme_gifts`
--
ALTER TABLE `theme_gifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `walls`
--
ALTER TABLE `walls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `gifts`
--
ALTER TABLE `gifts`
  ADD CONSTRAINT `gifts_ibfk_1` FOREIGN KEY (`wall_id`) REFERENCES `walls` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `theme_gifts`
--
ALTER TABLE `theme_gifts`
  ADD CONSTRAINT `theme_gifts_ibfk_1` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `walls`
--
ALTER TABLE `walls`
  ADD CONSTRAINT `walls_ibfk_1` FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
