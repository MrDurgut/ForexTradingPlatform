-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 17 Oca 2025, 07:44:27
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `borsa`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `operations`
--

CREATE TABLE `operations` (
  `user_id` int(100) NOT NULL,
  `symbol` varchar(100) NOT NULL,
  `opType` tinyint(1) NOT NULL,
  `alisFiyat` double NOT NULL,
  `targetRate` decimal(10,5) DEFAULT NULL,
  `price` double NOT NULL,
  `opFinish` tinyint(1) NOT NULL,
  `opDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `operations`
--

INSERT INTO `operations` (`user_id`, `symbol`, `opType`, `alisFiyat`, `targetRate`, `price`, `opFinish`, `opDate`) VALUES
(3, 'EURUSD', 1, 1.08, NULL, 10000, 0, '2025-01-17 06:07:36'),
(3, 'EURUSD', 0, 1.08, NULL, 10000, 1, '2025-01-17 06:07:36'),
(3, 'EURUSD', 0, 1.08, NULL, 10000, 1, '2025-01-17 06:07:36'),
(3, 'EURUSD', 0, 1.03, NULL, 10000000, 1, '2025-01-17 06:07:36'),
(3, 'EURUSD', 1, 1.1, 1.10000, 1000, 0, '2025-01-17 06:18:35'),
(3, 'EURUSD', 1, 1.02, 1.02000, 1000, 0, '2025-01-17 06:18:53'),
(3, 'EURUSD', 1, 1.0288, 1.02880, 1000, 0, '2025-01-17 06:19:24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `settings`
--

CREATE TABLE `settings` (
  `id` int(10) NOT NULL,
  `st_name` varchar(100) NOT NULL,
  `st_description` varchar(100) NOT NULL,
  `st_keywords` varchar(100) NOT NULL,
  `st_logo` varchar(100) NOT NULL,
  `st_mserver` varchar(100) NOT NULL,
  `st_mport` int(10) NOT NULL,
  `st_musername` varchar(100) NOT NULL,
  `st_mpassword` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `settings`
--

INSERT INTO `settings` (`id`, `st_name`, `st_description`, `st_keywords`, `st_logo`, `st_mserver`, `st_mport`, `st_musername`, `st_mpassword`) VALUES
(1, 'Nuance', 'Açıklama', 'Anahtar Kelimeler', 'LogoKonumu', 'deneme@deneme.com', 0, 'deneme', 'deneme');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `userID` int(10) NOT NULL,
  `userCookie` int(100) NOT NULL,
  `permission` int(1) NOT NULL,
  `userName` varchar(100) NOT NULL,
  `mailAdress` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `surName` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `phoneNumber` int(100) NOT NULL,
  `validateAccount` tinyint(1) NOT NULL,
  `bankAdress` varchar(20) NOT NULL,
  `bankName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`userID`, `userCookie`, `permission`, `userName`, `mailAdress`, `password`, `firstName`, `surName`, `birthday`, `phoneNumber`, `validateAccount`, `bankAdress`, `bankName`) VALUES
(3, 1720766458, 1, 'Nogii', 'pcseviyoz@gmail.com', '$2y$10$Un7vtPNw17HDwryvCPEjsuVJSitjUPH4fRrEjhAH7KYafnBudkg1e', 'Enes', 'Durgut', '2003-02-26', 2147483647, 0, 'TR12345678910', 'Yapı Kredi'),
(5, 1737092631, 2, 'bgfbfgb', 'gfbgfb@gmail.com', '$2y$10$xukbIxVc2jSmG0.pyV09GeUNZr6L6NVYPC8u1aqSD6Kez7fF7G57y', '', '', '0000-00-00', 0, 0, '', '');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `wallet`
--

CREATE TABLE `wallet` (
  `user_id` int(100) NOT NULL,
  `wallet_id` varchar(100) NOT NULL,
  `balance` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `wallet`
--

INSERT INTO `wallet` (`user_id`, `wallet_id`, `balance`) VALUES
(3, '6789f0ad71d5c', 8700),
(5, '', 100);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdraw') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'completed',
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `type`, `amount`, `status`, `transaction_date`) VALUES
(1, 3, 'deposit', 100.00, 'completed', '2025-01-17 06:10:38'),
(2, 3, 'deposit', 100.00, 'completed', '2025-01-17 06:10:44'),
(3, 3, 'deposit', 1000.00, 'completed', '2025-01-17 06:18:25'),
(4, 5, 'deposit', 100.00, 'completed', '2025-01-17 06:27:22');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`);

--
-- Tablo için indeksler `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`userID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
