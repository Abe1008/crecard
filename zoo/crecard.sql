-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Май 17 2021 г., 17:22
-- Версия сервера: 5.5.52-MariaDB
-- Версия PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `crecard`
--

-- --------------------------------------------------------

--
-- Структура таблицы `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `dat` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'время регистрации',
  `uid` int(11) DEFAULT NULL COMMENT 'код пользователя',
  `ip` varchar(32) DEFAULT NULL COMMENT 'IP адрес пользователя',
  `url` varchar(255) DEFAULT NULL COMMENT 'адрес перехода после регистрации'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='лог регистрации';

-- --------------------------------------------------------

--
-- Структура таблицы `pays`
--

CREATE TABLE IF NOT EXISTS `pays` (
  `id` int(11) NOT NULL COMMENT 'индекс платежа',
  `uid` int(11) NOT NULL COMMENT 'индекс пользователя',
  `dat` datetime NOT NULL COMMENT 'время платежа',
  `sm` double NOT NULL DEFAULT '0' COMMENT 'сумма платежа',
  `prim` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'примечание',
  `ost` double NOT NULL DEFAULT '0' COMMENT 'остаток',
  `ifile` int(11) NOT NULL DEFAULT '0' COMMENT 'файл вложения (например скан чека)',
  `payoff` int(11) DEFAULT '0' COMMENT 'признак погашения долга 0-платеж, 1-погашение'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='платежи пользователей';

-- --------------------------------------------------------

--
-- Структура таблицы `p_files`
--

CREATE TABLE IF NOT EXISTS `p_files` (
  `ifile` int(11) NOT NULL COMMENT 'код файла',
  `file_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'имя файла документа',
  `file_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'тип файла документа',
  `file_size` int(11) DEFAULT '0' COMMENT 'размер файла документа',
  `file_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'хэш содержимого файла',
  `wdat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'время обновления'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(11) NOT NULL COMMENT 'индекс пользователя',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'идентификатор - электронная почта',
  `pwd` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'пароль',
  `wdat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'время создания (модификации)'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='пользователи';

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `pays`
--
ALTER TABLE `pays`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `p_files`
--
ALTER TABLE `p_files`
  ADD PRIMARY KEY (`ifile`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `pays`
--
ALTER TABLE `pays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'индекс платежа';
--
-- AUTO_INCREMENT для таблицы `p_files`
--
ALTER TABLE `p_files`
  MODIFY `ifile` int(11) NOT NULL AUTO_INCREMENT COMMENT 'код файла';
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'индекс пользователя';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
