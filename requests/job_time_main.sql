-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 29, 2020 lúc 10:21 AM
-- Phiên bản máy phục vụ: 10.4.13-MariaDB
-- Phiên bản PHP: 7.4.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `job_time_main`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_12_02_044343_create_roles_table', 1),
(4, '2019_12_02_044720_create_role_user_table', 1),
(5, '2020_02_05_095501_create_off_days_table', 1),
(6, '2020_02_06_104142_add-foreign-key-off-days', 1),
(8, '2019_09_17_074029_create_departments_table', 3),
(9, '2019_09_17_081324_create_projects_table', 4),
(10, '2019_09_17_082116_create_types_table', 4),
(11, '2019_09_21_054146_create_issues_table', 4),
(12, '2019_10_31_073431_create_schedules_table', 4),
(13, '2019_11_29_072145_create_jobs_table', 4),
(18, '2019_12_17_095454_add-foreign-key-projects', 5),
(19, '2019_12_17_095723_add-foreign-key-issues', 5),
(20, '2019_12_17_100129_add-foreign-key-schedules', 5),
(21, '2019_12_17_100237_add-foreign-key-jobs', 5),
(22, '2020_01_04_012105_add_memo_to_schedules_table', 6),
(23, '2020_03_03_031313_add_description_to_job_type', 6),
(24, '2020_03_03_094843_add_page_to_issue', 6),
(25, '2020_06_05_061017_create_reports_table', 7),
(27, '2020_09_01_082116_add_team_to_users_table', 8),
(28, '2020_09_07_083048_create_teams_table', 8);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `off_days`
--

CREATE TABLE `off_days` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `off_days`
--

INSERT INTO `off_days` (`id`, `user_id`, `date`, `type`, `status`, `created_at`, `updated_at`) VALUES
(8, 3, '2020-01-02', 'all_day', 'approved', '2020-02-10 02:51:55', '2020-02-10 02:51:55'),
(9, 3, '2020-01-28', 'all_day', 'approved', '2020-02-10 02:52:25', '2020-02-10 02:52:25'),
(10, 8, '2020-01-27', 'all_day', 'approved', '2020-02-10 18:27:36', '2020-02-10 18:27:36'),
(11, 9, '2020-01-10', 'all_day', 'approved', '2020-02-10 18:32:02', '2020-02-10 18:32:02'),
(12, 9, '2020-01-17', 'all_day', 'approved', '2020-02-10 18:32:13', '2020-02-10 18:32:13'),
(13, 9, '2020-01-18', 'morning', 'approved', '2020-02-10 18:32:16', '2020-02-10 18:32:16'),
(15, 7, '2020-01-08', 'all_day', 'approved', '2020-02-10 18:33:58', '2020-02-10 18:33:58'),
(16, 7, '2020-01-24', 'all_day', 'approved', '2020-02-10 18:37:27', '2020-02-10 18:37:27'),
(17, 7, '2020-01-27', 'all_day', 'approved', '2020-02-10 18:37:30', '2020-02-10 18:37:30'),
(18, 7, '2020-01-04', 'morning', 'approved', '2020-02-10 18:38:24', '2020-02-10 18:38:24'),
(19, 7, '2020-02-15', 'morning', 'approved', '2020-02-10 18:40:05', '2020-02-10 18:40:05'),
(20, 9, '2020-01-24', 'all_day', 'approved', '2020-02-10 18:40:17', '2020-02-10 18:40:17'),
(21, 4, '2020-01-21', 'afternoon', 'approved', '2020-02-10 18:40:45', '2020-02-10 18:40:45'),
(22, 5, '2020-01-27', 'all_day', 'approved', '2020-02-10 18:41:14', '2020-02-10 18:41:14'),
(23, 4, '2020-01-24', 'all_day', 'approved', '2020-02-10 18:41:29', '2020-02-10 18:41:29'),
(25, 9, '2020-02-05', 'morning', 'approved', '2020-02-10 18:41:59', '2020-02-10 18:41:59'),
(26, 4, '2020-02-04', 'afternoon', 'approved', '2020-02-10 18:42:49', '2020-02-10 18:42:49'),
(27, 5, '2020-01-14', 'all_day', 'approved', '2020-02-10 18:46:22', '2020-02-10 18:46:22'),
(28, 4, '2020-02-19', 'all_day', 'approved', '2020-02-20 00:08:26', '2020-02-20 00:08:26'),
(33, 9, '2020-02-21', 'all_day', 'approved', '2020-02-25 18:21:20', '2020-02-25 18:21:20'),
(34, 9, '2020-02-24', 'all_day', 'approved', '2020-02-25 18:21:22', '2020-02-25 18:21:22'),
(35, 9, '2020-02-25', 'all_day', 'approved', '2020-02-25 18:21:24', '2020-02-25 18:21:24'),
(37, 5, '2020-03-10', 'all_day', 'approved', '2020-03-08 21:39:13', '2020-03-08 21:39:13'),
(39, 5, '2020-03-16', 'afternoon', 'approved', '2020-03-16 21:05:47', '2020-03-16 21:05:47'),
(40, 9, '2020-03-06', 'all_day', 'approved', '2020-03-23 19:28:14', '2020-03-23 19:28:14'),
(49, 14, '2020-03-25', 'all_day', 'approved', '2020-03-31 02:15:13', '2020-03-31 02:15:13'),
(51, 8, '2020-03-11', 'all_day', 'approved', '2020-03-31 02:17:03', '2020-03-31 02:17:03'),
(52, 4, '2020-03-14', 'morning', 'approved', '2020-03-31 02:17:23', '2020-03-31 02:17:23'),
(53, 5, '2020-04-16', 'afternoon', 'approved', '2020-04-19 18:19:04', '2020-04-19 18:19:04'),
(54, 4, '2020-04-17', 'morning', 'approved', '2020-04-22 21:08:36', '2020-04-22 21:08:36'),
(55, 4, '2020-04-28', 'afternoon', 'approved', '2020-04-28 17:51:25', '2020-04-28 17:51:25'),
(56, 3, '2020-05-04', 'all_day', 'approved', '2020-05-01 01:11:37', '2020-05-01 01:11:37'),
(57, 9, '2020-05-09', 'morning', 'approved', '2020-05-08 02:13:15', '2020-05-08 02:13:15'),
(58, 8, '2020-05-15', 'morning', 'approved', '2020-05-27 21:37:10', '2020-05-27 21:37:10'),
(59, 8, '2020-05-04', 'all_day', 'approved', '2020-05-27 21:37:35', '2020-05-27 21:37:35'),
(60, 5, '2020-05-26', 'afternoon', 'approved', '2020-05-31 20:45:15', '2020-05-31 20:45:15'),
(61, 8, '2020-06-06', 'morning', 'approved', '2020-06-09 18:28:16', '2020-06-09 18:28:16'),
(62, 8, '2020-06-05', 'afternoon', 'approved', '2020-06-09 18:28:18', '2020-06-09 18:28:18'),
(63, 8, '2020-06-02', 'all_day', 'approved', '2020-06-09 18:28:29', '2020-06-09 18:28:29'),
(64, 14, '2020-06-10', 'all_day', 'approved', '2020-06-17 01:06:20', '2020-06-17 01:06:20'),
(65, 4, '2020-06-20', 'morning', 'approved', '2020-06-21 21:36:08', '2020-06-21 21:36:08'),
(66, 5, '2020-06-16', 'all_day', 'approved', '2020-06-29 02:17:05', '2020-06-29 02:17:05'),
(67, 9, '2020-07-18', 'morning', 'approved', '2020-07-19 21:39:42', '2020-07-19 21:39:42'),
(68, 8, '2020-07-23', 'all_day', 'approved', '2020-07-23 20:57:03', '2020-07-23 20:57:03'),
(69, 3, '2020-07-24', 'all_day', 'approved', '2020-07-27 02:35:19', '2020-07-27 02:35:19'),
(77, 5, '2020-09-12', 'morning', 'approved', '2020-09-17 18:44:57', '2020-09-17 18:44:57'),
(78, 19, '2020-09-12', 'morning', 'approved', '2020-09-21 03:11:52', '2020-09-21 03:11:52'),
(79, 19, '2020-07-24', 'all_day', 'approved', '2020-09-21 03:13:27', '2020-09-21 03:13:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'An Admin User', '2020-01-07 18:45:58', '2020-01-07 18:45:58'),
(2, 'planner', 'A Planner User', '2020-01-07 18:45:58', '2020-01-07 18:45:58'),
(3, 'japanese_planner', 'A Japanese Planner User', '2020-01-07 18:45:58', '2020-01-07 18:45:58'),
(4, 'employee', 'An Employee User', '2020-01-07 18:45:58', '2020-01-07 18:45:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role_user`
--

CREATE TABLE `role_user` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `role_user`
--

INSERT INTO `role_user` (`id`, `role_id`, `user_id`) VALUES
(1, 1, 1),
(3, 2, 3),
(4, 4, 4),
(6, 4, 5),
(8, 2, 7),
(9, 2, 8),
(10, 4, 9),
(12, 3, 2),
(13, 1, 10),
(14, 2, 11),
(15, 4, 12),
(16, 4, 13),
(17, 2, 14),
(18, 3, 15),
(19, 3, 16),
(20, 3, 17),
(21, 3, 18),
(23, 2, 19),
(24, 4, 20);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teams`
--

CREATE TABLE `teams` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `teams`
--

INSERT INTO `teams` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
(1, 'dtp', 'DTP', NULL, NULL),
(2, 'path', 'PATH', NULL, NULL),
(3, 'web', 'WEB', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disable_date` date DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `team`, `language`, `email`, `disable_date`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Trần Tăng Quang', 'admin', '1,2,3', 'vi', 'quang.tran@kilala.vn', NULL, '$2y$10$EAGx2JnBt870veVtPY/E8.4Bq73eQY9S5wsC9jyApo1yemvcfWNYC', '74gAXIv2J0m9HCfwogVsTXQscJXYDO8QIeNCdcwYWiW40aOlUf1IClcLSH0w', '2019-12-31 18:45:58', '2020-09-29 00:41:39'),
(2, 'Furuoya Masato', 'furuoya', '1', 'ja', 'furuoya_masato@kilala.vn', NULL, '$2y$10$1vRThhpGHfN8O3AWp2U/.umN0L5hpUBFlb3v3WYWV4yO1IfeYEXTi', '8Y4Q7mPfRWZoVMaY3FZAm756L193XYQOlcC9A3j938q9iNdMVWCEw6zDGB4C', '2019-12-31 18:45:59', '2020-09-29 00:43:08'),
(3, 'Dương Thị Bích Ngọc', 'ngocduong', '1', 'vi', 'ngoc.duong@kilala.vn', NULL, '$2y$10$Pk3z1sCT3GFszdi4IUE1s.fLzBYN0OShL0zFp05m2h3dQVviaeq/K', 'GNmXP4zvhUgJBdPxjrWwGMZ3bFIvU1TFE49QmPaswHDgpjdkZ7GKMXwZDTtL', '2019-12-31 18:45:59', '2020-09-29 00:42:19'),
(4, 'Đinh Thị Hạnh Nguyện', 'nguyendinh', '1', 'vi', 'nguyen.dinh@kilala.vn', '2020-07-29', '$2y$10$BV7HUz/3tzfhYRSxwnlJSuxhJW6gAGofe8Kd.zpyIYlFMFyZ4jRpi', 'VGsFmnUA1EKrYYgmrRX4wwpt05Pma8b4S6zGMabZLyY8VyklN0G3s76IdL53', '2019-12-31 18:45:59', '2020-09-29 00:44:04'),
(5, 'Lê Đào Ngọc Diệp', 'dieple', '1', 'vi', 'diep.le@kilala.vn', NULL, '$2y$10$q.a8COx0mZ9VRuhwD6tAau.9UWg.lyHqWGqaTm1YPXoZ9OCQTb55W', 'UGOr3zdmbLG3zXtAhiIQAi7sE2b4eYXX6XVVpsyRYKil8Fe4qBVAnez4wCw1', '2019-12-31 19:53:20', '2020-09-29 00:44:12'),
(7, 'Phạm Hà Thái Vy', 'vypham', '1', 'vi', 'vy.pham@kilala.vn', '2020-03-23', '$2y$10$zwC.odKsgeu/6EjHoNIzL.ZBDRyYtmdGde6MTjC.rq0kIyF9JDCuG', 'na7bWqlsQfIlsO013WmdqVWkQg0vFs3SfpBJr4y6iJ5veKtZofoOTBgQfC3G', '2019-12-31 19:58:43', '2020-09-29 00:42:26'),
(8, 'Thái Hữu Lợi', 'loithai', '1', 'vi', 'loi.thai@kilala.vn', NULL, '$2y$10$88RyQ5lfU7gm.Pn8zL5ziOSF1K3ueE.wXdoLNqUPzWlDxtwLHsacS', 'jKbdy8FGldUPNpDs0tU459NLhCXUWaHjuZWQmuecdZoG6y1ScXphGWRt4oOu', '2019-12-31 20:08:35', '2020-09-29 00:42:34'),
(9, 'Hoàng Ngọc Phương Trâm', 'tramhoang', '1', 'vi', 'tram.hoang@kilala.vn', NULL, '$2y$10$e4o39u3DZWB57cOGAqw9x.EHN66krXdFzDJ/8H2Zk4yMZ9kFJXEz.', 'XIIkJ2uykhDsDkMZfVgTBM48QTNSqxVxgQkjwJj4viIt1movXQeACy68uDyH', '2019-12-31 20:17:22', '2020-09-29 00:44:19'),
(10, 'Furuoya Masato', 'furuoya_admin', '1', 'ja', 'furuoya_admin@kilala.vn', NULL, '$2y$10$YCCyW1Oub7T0vnlry0jiLeXQQYmUS7NOd8LaKnzz8WHc6YJ0ts0.y', '8qhDz1uPwADoQCAAEAMWjrE2zRVhx4CF7kzQETjMQKeGY6BnQ6LelyMcBD9p', '2020-02-04 19:46:54', '2020-09-29 00:42:07'),
(11, 'Furuoya Masato', 'furuoya_vn_planner', '1', 'ja', 'furuoya_vn_planner@kilala.vn', NULL, '$2y$10$Sh/rsep1LoagpZpRAtwrT.rC0pKo7M0MeyWnruuFwA8H4Z5eMRHDe', '44sXMMnQm9CNVHieVSvqD6c3Qc7u5maAaSLeNYJaOZ1QTKJf5APwaxnuoR3D', '2020-02-04 19:53:08', '2020-09-29 00:42:41'),
(12, 'Furuoya Masato', 'furuoya_employee', '1', 'ja', 'furuoya_employee@kilala.vn', NULL, '$2y$10$TQlIdzacVf3q0mLFHBDQne6pmQ0rrUW/BjDIwz7Zas6QmSocDprzK', 'PklgBcV3hwjv2zsdV6TGBGL2ANkZq2o3SNmbjdUonfDEwMF6y6WB5CouxdaE', '2020-02-04 19:56:39', '2020-09-29 00:44:28'),
(13, 'Huỳnh Thị Kim Ngọc', 'ngochuynh', '1', 'vi', 'ngoc.huynh@kilala.vn', NULL, '$2y$10$0Q5gBhorOrQ7C0H5V47FQOfQi1fwLSt/FqpsijorWqoFcLX3dUAKC', 'iS0hWnGAZscwvbYFUtFAMn1bKnpoM25QsXNs9ViXuPys7Z57ywMzzUkWC47i', '2020-01-31 21:06:55', '2020-09-29 00:44:35'),
(14, 'Long Yến Thanh', 'thanhlong', '1', 'vi', 'thanh.long@kilala.vn', '2020-07-07', '$2y$10$.Pav.Dw/XRTdS1bk/2oUPOYlLidOIKuh9B2.NwSc2yj/6qBmfPUry', 'oYhR75R1gqWphXrzuSdQrL1DwGOnldB57Ul6HXWFaSuQ52a8pHHvgQVQRF1z', '2020-03-01 19:27:22', '2020-09-29 00:42:49'),
(15, 'Miyano Kohei', 'miyano_kohei', '1', 'ja', 'miyano_kohei@yuidea.co.jp', NULL, '$2y$10$4LWKivTTchz4eCz5Bl1k1evK0DKgd7RVqGbTqATUgr65sLTJy45C2', 'PQyMFwJivgNAtfthtW4q8DAyY9j49MHu5PCRXBlKsYNfkJ4pXNQqb88PdpRF', '2020-03-27 03:47:23', '2020-09-29 00:43:17'),
(16, 'Tsuchiya Yumiko', 'tsuchiya_yumiko', '1', 'ja', 'tsuchiya_yumiko@yuidea.co.jp', NULL, '$2y$10$j/GrRkUklVZweMqtnk09ouX.vWjSgp2.enAblwd.idJgOmIYBKYJ2', 'cVw8jXZ00Y3Dxre6KJT0VvjnzDdb37Vwh0unWTICfcQADNIXXHXQm9vqj061', '2020-03-27 03:49:37', '2020-09-29 00:43:33'),
(17, 'Hayashi Masanori', 'hayashi_masanori', '1', 'ja', 'hayashi_masanori@yuidea.co.jp', NULL, '$2y$10$4F7YfYyMmAfDxf1jAcGYgeGxt6SHSBAdgt1tTAXHjtB9dq6Or0JPq', 'kXtsoKME78zwjp70XFx32hTT2koXoaHPolqDKKaaC6AHF0dS9m61PeBWc5bG', '2020-05-08 03:18:11', '2020-09-29 00:43:41'),
(18, 'Yokote Hiroaki', 'yokote_hiroaki', '1,2', 'ja', 'yokote_hiroaki@yuidea.co.jp', NULL, '$2y$10$dve93642bsXYo9lXB5cesOc91FuqFnaEDPUbZpClahghljWETGUBq', 'PWznO1yXe05NkqRqya47QB2NaOAF8UVXbEjBE8tnOr8oWS2BIyppdGMmQhHv', '2020-05-29 00:34:47', '2020-09-29 00:43:54'),
(19, 'Vũ Thị Thu Ngân', 'nganvu', '1', 'vi', 'ngan.vu@kilala.vn', NULL, '$2y$10$VLws0f92i6rZy4iMKUDiguFmVSAguE7.2eQczstJuZ9hv6MHyjYbu', 'yaHM3yClLIjKsI5iIDKl8EXgjOuJmQ65bqBuSdMiMgsZaDE8W7tUMIbQdGVW', '2020-05-31 20:08:28', '2020-09-29 00:42:58'),
(20, 'Đặng Thị Thúy Vân', 'vandang', '1', 'vi', 'van.dang@kilala.vn', NULL, '$2y$10$zAVEKLALC48jfqsCERmT1OzdRiuufVVQ7hnuPm.RIOQUJzCTu2kLm', 'rjanULwKM97Ir0AVAAaUcUHxbv8puz1h7U7EAKISRE3qnJBjqCnAvRamIIny', '2020-07-31 21:08:56', '2020-09-29 00:44:43');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `off_days`
--
ALTER TABLE `off_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `off_days_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `off_days`
--
ALTER TABLE `off_days`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `off_days`
--
ALTER TABLE `off_days`
  ADD CONSTRAINT `off_days_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
