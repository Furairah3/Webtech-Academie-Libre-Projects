-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 29 nov. 2025 à 20:13
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `learnstep`
--

-- --------------------------------------------------------

--
-- Structure de la table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `module_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `graded` tinyint(1) DEFAULT 0,
  `grade` varchar(10) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `corrected_file` varchar(255) DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `assignments`
--

INSERT INTO `assignments` (`id`, `user_id`, `module_id`, `file_path`, `submitted_at`, `graded`, `grade`, `feedback`, `corrected_file`, `graded_at`, `graded_by`) VALUES
(1, 4, 2, 'assets/uploads/assign_4_2_1764383846.pdf', '2025-11-29 02:37:26', 0, '44', 'GOOD FOR A BEGINING COME FOR OFFICE HOUR', NULL, '2025-11-29 17:21:48', 3);

-- --------------------------------------------------------

--
-- Structure de la table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `thumbnail`, `created_at`) VALUES
(1, 'Web Development Fundamentals', 'Learn HTML, CSS, and JavaScript to build modern websites.', 'webdev.jpg', '2025-11-27 12:12:40'),
(2, 'Python Programming for Beginners', 'A beginner-friendly course covering Python basics and real-world projects.', 'python.jpg', '2025-11-27 12:12:40'),
(3, 'Data Science Essentials', 'Understand data analysis, visualization, and machine learning basics.', 'datascience.jpg', '2025-11-27 12:12:40'),
(4, 'Database Design & SQL', 'Learn how to design databases and write SQL queries like a pro.', 'database.jpg', '2025-11-27 12:12:40'),
(5, 'Cybersecurity Basics', 'Introduction to ethical hacking, network security, and vulnerabilities.', 'cybersecurity.jpg', '2025-11-27 12:12:40'),
(6, 'Advanced Database with MySQL', 'Understand how databases work.\r\nLearn SQL queries, relationships, CRUD operations, and database design.', 'course_1764330550.jpg', '2025-11-28 11:49:10'),
(7, 'Français', 'Développe la maîtrise de la langue à travers la lecture, la compréhension, la grammaire, la conjugaison et la rédaction.', 'course_1764368231.jpeg', '2025-11-28 22:17:11'),
(8, 'Mathématiques', 'Introduction à l’arithmétique, l’algèbre et la géométrie pour résoudre des problèmes simples et concrets.', 'course_1764368254.jpeg', '2025-11-28 22:17:34'),
(9, 'Physique – Chimie', 'Découverte des lois de base de la nature, des phénomènes physiques et des premières réactions chimiques.', 'course_1764368276.jpeg', '2025-11-28 22:17:56'),
(10, 'Sciences de la Vie et de la Terre (SVT)- BEPC', 'Étude du corps humain, du monde vivant, de l’environnement et des notions essentielles de santé.', 'course_1764368316.jpeg', '2025-11-28 22:18:36'),
(11, 'Histoire – Géographie BEPC', 'Comprendre les grandes civilisations, les événements importants et la répartition des populations dans le monde', 'course_1764368353.jpeg', '2025-11-28 22:19:13'),
(12, 'Anglais (niveau collège)', 'Bases de la langue anglaise : vocabulaire, phrases simples, compréhension et communication.', 'course_1764368377.jpeg', '2025-11-28 22:19:37'),
(13, 'Français BAC', 'Analyse de textes, techniques de dissertation et outils d’expression écrite avancée.', 'course_1764368408.png', '2025-11-28 22:20:08'),
(14, 'Mathématiques BAC', 'Notions fondamentales : fonctions, suites, statistiques, géométrie analytique et calcul.', 'course_1764368437.jpeg', '2025-11-28 22:20:37'),
(15, 'Physique – Chimie BAC', 'Étude des phénomènes physiques et chimiques appliqués : mécanisme, énergie, réactions, matériaux.', 'course_1764368466.jpeg', '2025-11-28 22:21:06'),
(16, 'Sciences de la Vie et de la Terre (SVT) BAC', 'Compréhension du vivant : génétique, reproduction, immunité, géologie et environnement.', 'course_1764368500.jpeg', '2025-11-28 22:21:40'),
(17, 'Histoire – Géographie BAC', 'Analyse des grandes évolutions du monde moderne, de la géopolitique et des enjeux de développement.', 'course_1764368526.jpeg', '2025-11-28 22:22:06'),
(18, 'Philosophie', 'Introduction aux grandes questions de l’existence : liberté, justice, vérité, conscience et culture.', 'course_1764368593.jpeg', '2025-11-28 22:23:13'),
(19, 'Anglais', 'Amélioration de l’expression orale et écrite, compréhension de textes et production argumentée.', 'course_1764368615.jpeg', '2025-11-28 22:23:35');

-- --------------------------------------------------------

--
-- Structure de la table `course_resources`
--

CREATE TABLE `course_resources` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('pdf','book','youtube','website') NOT NULL,
  `url` text NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lectures`
--

CREATE TABLE `lectures` (
  `id` int(11) NOT NULL,
  `module_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `youtube_url` varchar(255) NOT NULL,
  `lecture_order` int(11) NOT NULL,
  `duration` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `lectures`
--

INSERT INTO `lectures` (`id`, `module_id`, `title`, `youtube_url`, `lecture_order`, `duration`) VALUES
(1, 1, 'Introduction + Instalation', 'https://www.youtube.com/watch?v=oPV2sjMG53U&list=PLZPZq0r_RZOMskz6MdsMOgxzheIyjo-BZ&index=1&pp=iAQB', 1, NULL),
(2, 1, 'How to create a DATABASE', 'https://www.youtube.com/watch?v=9LQ9rGoGfYQ&list=PLZPZq0r_RZOMskz6MdsMOgxzheIyjo-BZ&index=2&pp=iAQB', 2, NULL),
(3, 2, 'L1: History of Internet | Protocols | World Wide Web(WWW) | HTTP | Web Engineering Lectures', 'https://youtu.be/8VWu_8c_7NE?list=PLV8vIYTIdSnbwIFENjqBK7yyAkSVSoLBC', 1, NULL),
(4, 2, 'L2: Introduction to HTML | Features of HTML | HTML Example | Web Engineering Lectures', 'https://youtu.be/OxJTnSEAva4?list=PLV8vIYTIdSnbwIFENjqBK7yyAkSVSoLBC', 2, NULL),
(5, 2, 'L3: Introduction to HTML Tags | Basic Syntax and Description of HTML Tags | Web Engineering Lectures', 'https://youtu.be/ZTcD5A_BRlg?list=PLV8vIYTIdSnbwIFENjqBK7yyAkSVSoLBC', 3, NULL),
(6, 2, 'https://youtu.be/7d5TigU-Olk?list=PLV8vIYTIdSnbwIFENjqBK7yyAkSVSoLBC', 'L4: HTML Lists with example | Ordered, Unordered and Description/ Definition Lists | Web Engineering', 4, NULL),
(7, 5, 'Les fractions au brevet', 'https://www.youtube.com/watch?v=-3OUCNOFVuU', 1, NULL),
(8, 5, 'Calcul littéral complet - 3e:', 'https://www.youtube.com/watch?v=yg4NQylKmTc&list=PLSOFOcyBRpIj-PmIXC2WES6pz2syGd14z&index=1\r\nhttps://www.youtube.com/watch?v=DiaDt7loIAk&list=PLSOFOcyBRpIj-PmIXC2WES6pz2syGd14z&index=2\r\nhttps://www.youtube.com/watch?v=JoKEcV9_PLE&list=PLSOFOcyBRpIj-PmIXC2', 2, NULL),
(9, 6, 'PYTHAGORE AU BREVET', 'https://www.youtube.com/watch?v=8tcclPHCmJ8', 1, NULL),
(10, 5, 'Calcul littéral complet - 3e:', 'https://www.youtube.com/watch?v=4vnjoWrTNy0&list=PLSOFOcyBRpIj-PmIXC2WES6pz2syGd14z&index=5https://www.youtube.com/watch?v=yg4NQylKmTc&list=PLSOFOcyBRpIj-PmIXC2WES6pz2syGd14z&index=1', 12, NULL),
(11, 7, 'BREVET PHYSIQUE CHIMIE 2025', 'https://www.youtube.com/watch?v=TuxSB-2MfFM&list=PLFf1gA-ubk_QsXItuYD6aWz02fHGSNbTD&index=1', 1, NULL),
(12, 8, 'BREVET PHYSIQUE CHIMIE 💪 100 QUESTIONS ✅ QCM POUR RÉVISER', 'https://www.bing.com/videos/riverview/relatedvideo?q=brevet+chime+cour&mid=57CD022BAFF5A956BDF057CD022BAFF5A956BDF0&FORM=VIRE', 1, NULL),
(13, 27, 'Basic English Grammar: Parts of Speech – noun, verb, adjective, pronoun, adverb.', 'https://www.youtube.com/watch?v=SceDmiBEESI', 1, NULL),
(14, 27, 'Sound like a native speaker: Delete the \'H\'!', 'https://www.youtube.com/watch?v=xLYAQ5Wergc', 1, NULL),
(15, 4, 'BREVET de FRANÇAIS 2024 : TOUT le programme en 30 minutes:', 'https://www.youtube.com/watch?v=Q8-8DqcEGX8', 1, NULL),
(16, 3, 'analyse du text', 'https://www.youtube.com/watch?v=plK3w243yZY&list=PLWUMKDJ6TcocjTTAICbMhMl5GmDFLnvDu', 1, NULL),
(17, 9, 'BREVET SVT', 'https://www.youtube.com/watch?v=dWAF3Cury_4&list=PLGPSh2zOzhw3AegXvnInKNQChllf69yvq', 1, NULL),
(18, 21, 'CHAP.1: STABILITÉ GÉNÉTIQUE ET ÉVOLUTION CLONALE - Bio Logique', 'https://www.youtube.com/watch?v=4UJbyApHaYk', 1, NULL),
(19, 22, 'complete svt playlist', 'https://www.youtube.com/watch?v=4UJbyApHaYk&list=PLBpZ88xnznBA3-pV6uX32qmrfwXKMBRI9', 1, NULL),
(20, 12, 'CHAP.2: LES CONSÉQUENCES GÉNÉTIQUES DE LA REPRODUCTION SEXUÉE:', 'https://www.youtube.com/watch?v=jmnbJuemCfY', 1, NULL),
(21, 21, 'CHAP.3: LA COMPLEXIFICATION DES GÉNOMES -Bio Logique', 'https://www.youtube.com/watch?v=-34LUTT5oT4', 1, NULL),
(22, 15, 'Français Pour le bac:', 'https://www.youtube.com/watch?v=WrryMEBj2oc&list=PLY-quDF0nYMwDPUO-iycvzBSLiq86s74b', 1, NULL),
(23, 16, 'Comment faire une Dissertation et passer de 4,5 à 20/20 📈', 'https://www.bing.com/videos/riverview/relatedvideo?q=bac+dissertation+video&&mid=A7E3C3F298B463EA86DAA7E3C3F298B463EA86DA&FORM=VAMGZC', 1, NULL),
(24, 17, 'Limites et continuité. 2 Bac sciences', 'https://www.youtube.com/watch?v=3dU-Yxfz1Ro&list=PLX4JM5un62hBMbpw3CP3BHywvM4oitzbY', 1, NULL),
(25, 18, 'Limites des fonctions - Tale Spé:', 'https://www.youtube.com/watch?v=YPwJyYDsmxM&list=PLVUDmbpupCarS4Qp45vTwsEGYMOJtgBxE', 1, NULL),
(26, 20, 'Terminale - physique chimie complete playlist:', 'https://www.youtube.com/playlist?list=PL4nRTBusZ_9cZUBZ5ulNsE3E5cTQ8bgEx', 1, NULL),
(27, 19, 'CINÉTIQUE CHIMIQUE ✅ Cours complet 💪 Terminale', 'https://www.bing.com/videos/riverview/relatedvideo?&q=pc+terninal+bac&&mid=55AFB51E53C80DF0DEE455AFB51E53C80DF0DEE4&&FORM=VRDGAR', 1, NULL),
(28, 23, 'complete playlist', 'https://www.youtube.com/playlist?list=PLFTT_fqZxTyLd7mLjbiv2U8t-v6efhbWQ', 1, NULL),
(29, 23, 'Histoire-Géographie - Terminale', 'https://www.youtube.com/playlist?list=PL_1WVGjLTYqIJpC0nVF0e-27-xb-Q1azI', 1, NULL),
(30, 25, 'Toutes les notions de philosophie du bac 2025 !', 'https://www.youtube.com/playlist?list=PLQa-9OWwltEtcqwHzFk-GcpH03M0TuVCt', 1, NULL),
(31, 26, 'BAC de philo : La conscience - L\'inconscient', 'https://www.youtube.com/watch?v=tgXL9-iT7qU', 1, NULL),
(32, 11, 'Play all Play all Histoire-Géographie-EMC 3e', 'https://www.youtube.com/playlist?list=PLrkh0FrxZNQ_B8kWbounpJRWpJOsOtN2Q', 1, NULL),
(33, NULL, '', '', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `module_order` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `modules`
--

INSERT INTO `modules` (`id`, `course_id`, `title`, `module_order`, `quiz_id`) VALUES
(1, 6, 'Introduction to MySQL Concepts', 1, NULL),
(2, 1, 'Introduction to Web Tech', 1, 1),
(3, 7, 'Compréhension et analyse de textes', 1, NULL),
(4, 7, 'Grammaire, conjugaison et rédaction', 1, 3),
(5, 8, 'Arithmétique et fractions', 1, 4),
(6, 8, 'Géométrie et résolution de problèmes', 1, 5),
(7, 9, 'Notions fondamentales de physique (force, énergie, mouvement)', 1, NULL),
(8, 9, 'Introduction à la chimie (matière, changements d’état, réactions)', 1, NULL),
(9, 10, 'Corps humain et santé', 1, NULL),
(10, 10, 'Monde vivant et environnement', 1, NULL),
(11, 11, 'Grandes civilisations et événements historiques', 1, NULL),
(12, 10, 'Géographie humaine et physique', 1, NULL),
(13, 12, 'Vocabulaire et grammaire de base', 1, NULL),
(14, 12, 'Compréhension et communication simple', 1, NULL),
(15, 13, 'Analyse littéraire et commentaire de texte', 1, 6),
(16, 13, 'Méthodologie de dissertation et écriture', 1, 7),
(17, 14, 'Fonctions et dérivation', 1, NULL),
(18, 14, 'Statistiques, probabilités et suites', 1, NULL),
(19, 15, 'Électricité, mécanique et énergie', 1, 8),
(20, 15, 'Chimie générale et transformations chimiques', 1, 9),
(21, 16, 'Génétique et reproduction', 1, 11),
(22, 16, 'Géologie, immunologie et environnement', 1, 12),
(23, 17, 'Grandes transformations du monde moderne', 1, 13),
(24, 17, 'Mondialisation, territoires et enjeux géopolitiques', 1, 14),
(25, 18, 'Concepts fondamentaux (justice, liberté, conscience…)', 1, 15),
(26, 18, 'Méthodologie de la dissertation et étude des auteurs', 1, 16),
(27, 19, 'Compréhension écrite et orale', 1, 17),
(28, 19, 'Expression écrite (essays) et communication avancée', 1, 18);

-- --------------------------------------------------------

--
-- Structure de la table `module_assignments`
--

CREATE TABLE `module_assignments` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `module_assignments`
--

INSERT INTO `module_assignments` (`id`, `module_id`, `title`, `description`, `file_path`, `posted_at`, `created_by`, `created_at`) VALUES
(1, 2, 'Assignemt ONE', '', 'assets/uploads/assign_posted_2_1764358553.pdf', '2025-11-28 19:35:53', 1, '2025-11-29 16:23:59'),
(2, 1, 'Asign1', 'jjbdbbjbfjbjfb webwe jbfefewreih dbfdoiwqbfq wbfewgfF BADSBAF', NULL, '2025-11-29 17:11:35', 1, '2025-11-29 17:11:35'),
(3, 3, 'Module 1 : Compréhension et analyse de textes — Homework', 'Lis un texte de 10–15 lignes (au choix).\r\nRéponds ensuite aux questions suivantes :\r\n\r\nQuel est le thème principal du texte ?\r\n\r\nTrouve deux informations importantes.\r\n\r\nIdentifie un adjectif et donne son contraire.\r\n\r\nRésume le texte en 4–5 lignes.', NULL, '2025-11-29 18:20:44', 1, '2025-11-29 18:20:44'),
(4, 4, 'Module 2 : Grammaire, conjugaison, rédaction — Homework', 'Transforme ces phrases au passé composé :\r\n\r\nJe marche vers l’école.\r\n\r\nNous finissons nos devoirs.\r\n\r\nRéécris ces phrases au pluriel :\r\n\r\nLe garçon lit un livre.\r\n\r\nRédige un petit texte de 8 lignes : “Une journée que je n’oublierai jamais.”', NULL, '2025-11-29 18:21:18', 1, '2025-11-29 18:21:18'),
(5, 5, 'Module 1 : Arithmétique et fractions — Homework', 'Calcule :\r\n\r\n3/4 + 2/3\r\n5/6 + 1/4​\r\n\r\n\r\nDécompose 84 en facteurs premiers.\r\n\r\nTrouve le PGCD de 24 et 36.', NULL, '2025-11-29 18:22:48', 1, '2025-11-29 18:22:48'),
(6, 6, 'Module 2 : Géométrie et résolution de problèmes — Homework', 'Calcule le périmètre d’un triangle de côtés 7 cm, 9 cm et 12 cm.\r\n\r\nTrouve l’aire d’un rectangle : L=8 cm, l=5 cm.\r\n\r\nRésous ce problème :\r\nUn vélo parcourt 12 km en 40 minutes. Quelle est sa vitesse en km/h ?', NULL, '2025-11-29 18:23:25', 1, '2025-11-29 18:23:25'),
(7, 7, 'Module 1 : Physique fondamentale — Homework', 'Explique ce qu’est une force.\r\n\r\nCalcule la vitesse d’un objet qui parcourt 150 m en 10 secondes.\r\n\r\nDonne 2 formes d’énergie et un exemple pour chacune.', NULL, '2025-11-29 18:23:58', 1, '2025-11-29 18:23:58'),
(8, 8, 'Module 2 : Introduction à la chimie — Homework', 'Définis : soluté, solvant, mélange homogène.\r\n\r\nCite deux changements d’état avec un exemple.\r\n\r\nExplique pourquoi la glace flotte sur l’eau.', NULL, '2025-11-29 18:24:18', 1, '2025-11-29 18:24:18'),
(9, 9, 'Module 1 : Corps humain et santé — Homework', 'Explique le rôle du système respiratoire.\r\n\r\nDessine un schéma simple du cœur (avec légendes).\r\n\r\nDonne 3 conseils pour rester en bonne santé.', NULL, '2025-11-29 18:24:55', 1, '2025-11-29 18:24:55'),
(10, 10, 'Module 2 : Monde vivant et environnement — Homework', 'Explique la photosynthèse en 4 lignes.\r\n\r\nDonne un exemple de chaîne alimentaire.\r\n\r\nCite trois causes de la pollution de l’eau.', NULL, '2025-11-29 18:25:21', 1, '2025-11-29 18:25:21'),
(11, 11, 'Module 1 : Civilisations et histoire — Homework', 'Choisis une civilisation (Égyptiens, Romains, Empire du Mali).\r\n\r\nDécris 3 caractéristiques importantes.\r\n\r\nPlace sur une frise 3 grands événements historiques (inventés ou connus).', NULL, '2025-11-29 18:26:15', 1, '2025-11-29 18:26:15'),
(12, 12, 'Module 2 : Géographie physique et humaine — Homework', 'Définis : continent, climat, population.\r\n\r\nCite les 5 pays frontaliers de ton pays (si tu ne sais pas, fais une recherche).\r\n\r\nExplique le climat de ta région en 5 lignes.', NULL, '2025-11-29 18:26:57', 1, '2025-11-29 18:26:57'),
(13, 27, 'Module 1 : Reading & Listening — Homework', 'Lire un texte court (10–15 lignes). Répondre :\r\n\r\nMain idea (idée principale)\r\n\r\n3 keywords\r\n\r\nRésumé en 5 lignes.', NULL, '2025-11-29 18:27:34', 1, '2025-11-29 18:27:34'),
(14, 28, 'Module 2 : Essay Writing — Homework', 'Rédiger un essay de 120 mots sur :\r\n“The importance of technology in education.”', NULL, '2025-11-29 18:28:03', 1, '2025-11-29 18:28:03'),
(15, 15, 'Module 1 : Analyse littéraire — Homework', 'Choisis un texte littéraire d’au moins 20 lignes.\r\n\r\nAnalyse obligatoirement :\r\n\r\nLe thème principal\r\n\r\nLe registre littéraire\r\n\r\nLe point de vue narratif\r\n\r\n4 procédés littéraires (exemples : métaphore, comparaison, hyperbole, champ lexical)\r\n\r\nRédige un commentaire structuré de 10 à 12 lignes.', NULL, '2025-11-29 18:29:02', 1, '2025-11-29 18:29:02'),
(16, 16, 'Module 2 : Dissertation — Homework', '2. Mathématiques\r\nModule 1 : Fonctions & dérivation — Homework\r\n\r\nOn considère : f(x) = x² – 3x + 2\r\n\r\nDéterminer f′(x).\r\n\r\nCalculer f(4).\r\n\r\nÉtudier la variation de la fonction :\r\n\r\nTableau de variation exigé.\r\n\r\nSigne de la dérivée.\r\n\r\nExtremum.', NULL, '2025-11-29 18:29:40', 1, '2025-11-29 18:29:40'),
(17, 17, 'Module 1 : Fonctions & dérivation — Homework', 'On considère : f(x) = x² – 3x + 2\r\n\r\nDéterminer f′(x).\r\n\r\nCalculer f(4).\r\n\r\nÉtudier la variation de la fonction :\r\n\r\nTableau de variation exigé.\r\n\r\nSigne de la dérivée.\r\n\r\nExtremum.', NULL, '2025-11-29 18:36:17', 1, '2025-11-29 18:36:17'),
(18, 18, 'Module 2 : Statistiques & Probabilités — Homework', 'Données : 8 ; 10 ; 12 ; 14 ; 14 ; 16\r\n\r\nCalculer la moyenne.\r\n\r\nTrouver la médiane.\r\n\r\nUne urne contient 3 boules rouges et 2 bleues.\r\n\r\nDonner la probabilité de tirer une boule rouge.', NULL, '2025-11-29 18:39:59', 1, '2025-11-29 18:39:59'),
(19, 19, 'Module 1 : Électricité & mécanique — Homework', 'Dans un circuit : U = 12 V ; R = 4 Ω.\r\n\r\nCalculer l’intensité du courant.\r\n\r\nDéfinir l’énergie cinétique.\r\n\r\nCalculer l’énergie cinétique d’un objet de 5 kg se déplaçant à 6 m/s.', NULL, '2025-11-29 18:40:31', 1, '2025-11-29 18:40:31'),
(20, 20, 'Module 2 : Transformations chimiques — Homework', 'Expliquer la différence entre une réaction endothermique et exothermique.\r\n\r\nÉquilibrer l’équation :\r\n\r\n𝐻\r\n2\r\n+\r\n𝑂\r\n2\r\n→\r\n𝐻\r\n2\r\n𝑂\r\nH\r\n2\r\n	​\r\n\r\n+O\r\n2\r\n	​\r\n\r\n→H\r\n2\r\n	​\r\n\r\nO\r\n\r\nDonner un exemple précis d’oxydation.', NULL, '2025-11-29 18:41:16', 1, '2025-11-29 18:41:16'),
(21, 21, 'Module 1 : Génétique & reproduction — Homework', 'Expliquer clairement la différence entre génotype et phénotype.\r\n\r\nRéaliser un schéma simple d’un chromosome (légendes obligatoires).\r\n\r\nDonner un cas réel d’hérédité (exemple : couleur des yeux).', NULL, '2025-11-29 18:41:45', 1, '2025-11-29 18:41:45'),
(22, 22, 'Module 2 : Géologie & immunologie — Homework', 'Décrire le rôle des anticorps.\r\n\r\nExpliquer les étapes de formation d’un volcan.\r\n\r\nCiter deux actions concrètes pour protéger l’environnement.', NULL, '2025-11-29 18:42:23', 1, '2025-11-29 18:42:23'),
(23, 23, 'Module 1 : Transformations du monde moderne — Homework', 'Expliquer la révolution industrielle en 5 lignes.\r\n\r\nDonner 3 conséquences de la colonisation.\r\n\r\nRéaliser une frise chronologique comprenant 3 dates majeures du XXᵉ siècle.', NULL, '2025-11-29 18:42:56', 1, '2025-11-29 18:42:56'),
(24, 24, 'Module 2 : Mondialisation & enjeux — Homework', 'Définir précisément la mondialisation.\r\n\r\nDonner 2 avantages et 2 inconvénients.\r\n\r\nExpliquer le rôle des grandes puissances dans la géopolitique mondiale actuelle.', NULL, '2025-11-29 18:43:36', 1, '2025-11-29 18:43:36'),
(25, 25, 'Module 1 : Concepts fondamentaux — Homework', 'Rédiger un texte argumenté (10 lignes minimum) répondant à la question :\r\n“Sommes-nous vraiment libres ?”', NULL, '2025-11-29 18:44:03', 1, '2025-11-29 18:44:03'),
(26, 26, 'Module 2 : Méthodologie — Homework', 'Rédiger une introduction complète (accroche, définition, problématique, annonce du plan) sur le sujet :\r\n“La justice est-elle la même pour tous ?”', NULL, '2025-11-29 18:44:30', 1, '2025-11-29 18:44:30'),
(27, 13, 'Module 1 : Vocabulary & Grammar — Homework', 'Translate into English :\r\n\r\nJe vais à l’école chaque matin.\r\n\r\nNous aimons jouer au football.\r\n\r\nConjugate verbs in the present simple :\r\n\r\n(to study)\r\n\r\n(to watch)\r\n\r\nList 10 vocabulary words about family.', NULL, '2025-11-29 18:46:17', 1, '2025-11-29 18:46:17'),
(28, 14, 'Module 2 : Communication simple — Homework', 'Write a short dialogue (8 lines) between two friends talking about their weekend.', NULL, '2025-11-29 18:46:40', 1, '2025-11-29 18:46:40');

-- --------------------------------------------------------

--
-- Structure de la table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `module_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `passing_score` int(11) DEFAULT 70
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quizzes`
--

INSERT INTO `quizzes` (`id`, `module_id`, `title`, `passing_score`) VALUES
(1, 2, 'Quiz 1', 70),
(2, 15, 'Compréhension', 100),
(3, 4, 'Grammaire et conjugaison', 100),
(4, 5, 'Arithmétique', 100),
(5, 6, 'Géométrie', 100),
(6, 15, 'Analyse litteraire', 100),
(7, 16, 'Dissertation', 100),
(8, 19, 'Notions fondamentales de physique', 99),
(9, 20, 'Introduction à la chimie', 100),
(10, 21, 'Corps humain et santé', 70),
(11, 21, 'Génétique et reproduction', 70),
(12, 22, 'Géologie, immunologie et environnement', 70),
(13, 23, 'Grandes transformations du monde moderne', 70),
(14, 24, 'Mondialisation, territoires et enjeux géopolitiques', 70),
(15, 25, 'Concepts fondamentaux', 70),
(16, 26, 'Méthodologie de la dissertation et étude des auteurs', 70),
(17, 27, 'Compréhension écrite et orale', 70),
(18, 28, 'Expression écrite et communication', 70);

-- --------------------------------------------------------

--
-- Structure de la table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `passed` tinyint(1) DEFAULT 0,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `user_id`, `quiz_id`, `score`, `total_questions`, `passed`, `attempted_at`) VALUES
(4, 4, 1, 1, 2, 0, '2025-11-29 09:53:58'),
(6, 4, 1, 2, 2, 1, '2025-11-29 15:11:56'),
(7, 4, 1, 2, 2, 1, '2025-11-29 15:18:28');

-- --------------------------------------------------------

--
-- Structure de la table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('single','multiple') DEFAULT 'single',
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_option` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`) VALUES
(1, 1, 'Which tag is used to create a hyperlink in HTML?', 'single', '<link>', '<a>', '<href>', '<url>', 'B'),
(2, 1, 'Which of the following are examples of block-level elements?', 'multiple', '<div>', '<p>', '<span>', '<h1>', 'A,B,C'),
(3, 2, 'Quel est le rôle principal du titre d’un texte ?', 'single', 'a) Raconter l’histoire', 'b) Donner le thème', 'c) Expliquer les personnages', 'd) Remplacer l’introduction', 'B'),
(4, 2, 'Que signifie « narrateur » ?', 'single', 'a) Celui qui lit', 'b) Celui qui raconte l’histoire', 'c) Le héros', 'd) Un lieu', 'B'),
(5, 2, 'Dans un récit, que trouve-t-on souvent ?', 'single', 'a) Équations', 'b) Dates historiques', 'c) Personnages', 'd) Réactions chimiques', 'C'),
(6, 2, 'Quel mot est synonyme de « joyeux » ?', 'single', 'a) Heureux', 'b) Triste', 'c) Fâché', 'd) Fatigué', 'A'),
(7, 2, 'Quel est l’objectif d’un résumé ?', 'single', 'a) Allonger le texte', 'b) Changer l’histoire', 'c) Raconter l’essentiel', 'd) Ajouter des détails', 'C'),
(8, 3, 'Quel est le verbe dans la phrase : « Paul mange une mangue » ?', 'single', 'a) Paul', 'b) Mange', 'c) Une', 'd) Mangue', 'B'),
(9, 3, '« Nous finissons » est au :', 'single', 'a) Présent', 'b) Futur', 'c) Passé composé', 'd) Imparfait', 'A'),
(10, 3, 'Le féminin de « acteur » est :', 'single', 'a) Acteuse', 'b) Actrice', 'c) Actorine', 'd) Actante', 'B'),
(11, 3, 'Un adjectif qualifie :', 'single', 'a) Un verbe', 'b) Un nom', 'c) Un pronom', 'd) Une préposition', 'B'),
(12, 4, '12 ÷ 3 = ?', 'single', 'a) 2', 'b) 4', 'c) 6', 'd) 12', 'B'),
(13, 4, 'Le PPCM de 2 et 4 est :', 'single', 'a) 2', 'b) 3', 'c) 4', 'd) 8', 'C'),
(14, 4, '3/4 est égal à :', 'single', 'a) 0,25', 'b) 0,75', 'c) 1,25', 'd) 4', 'B'),
(15, 4, 'Le nombre premier est :', 'single', 'a) 9', 'b) 12', 'c) 7', 'd) 15', 'C'),
(16, 4, '5 × (3 + 1) =', 'single', 'a) 20', 'b) 15', 'c) 8', 'd) 12', 'A'),
(17, 5, 'Un triangle a :', 'single', 'a) 2 côtés', 'b) 3 côtés', 'c) 4 côtés', 'd) 5 côtés', 'C'),
(18, 5, 'Une figure symétrique est :', 'single', 'a) Identique d’un côté à l’autre', 'b) Toujours un cercle', 'c) Toujours un carré', 'd) Toujours un solide', 'A'),
(19, 5, 'Le périmètre est :', 'single', 'a) L’intérieur', 'b) La surface', 'c) Le contour', 'd) Le volume', 'C'),
(20, 5, 'L’unité d’aire est :', 'single', 'a) cm', 'b) m', 'c) cm²', 'd) km', 'C'),
(21, 6, 'Le narrateur omniscient connaît :', 'single', 'a) Rien', 'b) Peu de choses', 'c) Tout', 'd) Seulement le héros', 'C'),
(22, 6, 'La thèse est :', 'single', 'a) Une phrase décorative', 'b) L’idée défendue', 'c) Une définition', 'd) Un exemple', 'B'),
(23, 6, 'Un champ lexical est :', 'single', 'a) Un verger', 'b) Une liste de mots liés', 'c) Une phrase', 'd) Un paragraphe', 'B'),
(24, 6, 'Un texte argumentatif sert à :', 'single', 'a) Raconter', 'b) Décrire', 'c) Convaincre', 'd) Faire rire', 'C'),
(25, 6, 'Un sonnet possède :', 'single', 'a) 8 vers', 'b) 12 vers', 'c) 14 vers', 'd) 20 vers', 'C'),
(26, 7, 'Une dissertation comporte :', 'single', 'a) 1 partie', 'b) 2 parties', 'c) 3 parties', 'd) 4 parties', 'C'),
(27, 7, 'La problématique est :', 'single', 'a) Un résumé', 'b) La question centrale', 'c) Une conclusion', 'd) Un exemple', 'B'),
(28, 7, 'Une transition sert à :', 'single', 'a) Changer de sujet', 'b) Lier les parties', 'c) Décorer', 'd) Ajouter des chiffres', 'B'),
(29, 7, 'Une citation est utile pour :', 'single', 'a) Raconter', 'b) Illustrer', 'c) Changer de thème', 'd) Remplir de l’espace', 'B'),
(30, 7, 'La conclusion doit :', 'single', 'a) Ouvrir et résumer', 'b) Ajouter des arguments', 'c) Répéter l’introduction', 'd) Introduire un nouveau sujet', 'A'),
(31, 8, 'La force se mesure en :', 'single', 'a) Joules', 'b) Newtons', 'c) Watts', 'd) Volts', 'B'),
(32, 8, 'L’appareil qui mesure le courant électrique s’appelle :', 'single', 'a) Ampèremètre', 'b) Thermomètre', 'c) Chronomètre', 'd) Baromètre', 'A'),
(33, 8, 'La vitesse se calcule avec :', 'single', 'a) distance ÷ temps', 'b) temps × masse', 'c) masse ÷ distance', 'd) force × distance', 'A'),
(34, 8, 'Le Soleil est :', 'single', 'a) Une planète', 'b) Un astéroïde', 'c) Une étoile', 'd) Un satellite', 'C'),
(35, 8, 'L’énergie électrique peut être produite par :', 'single', 'a) Des nuages', 'b) Le vent', 'c) Les pierres', 'd) Le bois seul', 'B'),
(36, 9, 'L’eau est composée de :', 'single', 'a) H₂O', 'b) CO₂', 'c) O₂', 'd) NaCl', 'A'),
(37, 9, 'Une solution sucrée est un :', 'single', 'a) Solvant', 'b) Soluté + solvant', 'c) Gaz', 'd) Solide pur', 'B'),
(38, 9, 'Un changement d’état solide → liquide se nomme :', 'single', 'a) Fusion', 'b) Ébullition', 'c) Condensation', 'd) Sublimation', 'A'),
(39, 9, 'Le sel de cuisine est :', 'single', 'a) Fer', 'b) Cuivre', 'c) NaCl', 'd) HCl', 'C'),
(40, 9, 'Un mélange homogène est un mélange :', 'single', 'a) Séparable facilement', 'b) Dont les composants sont visibles', 'c) Dont les composants ne sont pas visibles', 'd) Qui chauffe', 'C'),
(41, 10, 'L’organe responsable de la respiration est :', 'single', 'a) Le cœur', 'b) Le foie', 'c) Les poumons', 'd) L’estomac', 'C'),
(42, 10, 'Le sang transporte surtout :', 'single', 'a) De l’oxygène', 'b) De la lumière', 'c) Du son', 'd) Du sable', 'A'),
(43, 10, 'Le système nerveux contrôle :', 'single', 'a) Les cheveux', 'b) Les mouvements et sensations', 'c) La digestion seulement', 'd) Le poids', 'B'),
(44, 10, 'La carie attaque :', 'single', 'a) Les yeux', 'b) La peau', 'c) Les dents', 'd) Les os', 'C'),
(45, 10, 'Pour rester en bonne santé il faut :', 'single', 'a) Manger équilibré', 'b) Dormir peu', 'c) Ne pas boire d’eau', 'd) Ne jamais faire de sport', 'A'),
(46, 11, 'L’ADN se trouve principalement dans :', 'single', 'a) Le cytoplasme', 'b) Le noyau', ') La membrane', 'd) La mitochondrie', 'B'),
(47, 11, 'Un gène est :', 'single', 'a) Une cellule', 'b) Une unité d’information héréditaire', 'c) Une hormone', 'd) Une enzyme', 'A'),
(48, 11, 'La reproduction sexuée implique :', 'single', 'a) Un seul parent', 'b) Deux parents', 'c) Aucun parent', 'd) Trois parents', 'B'),
(49, 11, 'La fécondation correspond à :', 'single', 'a) La division cellulaire', 'b) La rencontre d’un spermatozoïde et d’un ovule', 'c) La photosynthèse', 'd) La mutation génétique', 'B'),
(50, 11, 'Les chromosomes déterminent :', 'single', 'a) La couleur de l’air', 'b) Les caractéristiques héréditaires', 'c) La température du corps', 'd) La vitesse des nerfs', 'A'),
(51, 12, 'La lithosphère est :', 'single', 'a) L’air', 'b) La croûte terrestre et le manteau supérieur', 'c) L’eau des océans', 'd) Le noyau interne', 'B'),
(52, 12, 'Les anticorps sont produits par :', 'single', 'a) Les globules rouges', 'b) Les globules blancs', 'c) Les os', 'd) Le foie', 'B'),
(53, 12, 'Le volcanisme peut créer :', 'single', 'a) Des montagnes et de nouvelles terres', 'b) Des océans', 'c) La photosynthèse', 'd) Du vent', 'A'),
(54, 12, 'La déforestation entraîne :', 'single', 'a) Une augmentation de l’oxygène', 'b) La perte de biodiversité', 'c) La protection des sols', 'd) La baisse des températures', 'B'),
(55, 12, 'L’érosion est :', 'single', 'a) La destruction progressive de la surface terrestre', 'b) La formation de montagnes', 'c) La croissance des arbres', 'd) La reproduction des animaux', 'A'),
(56, 13, 'La Révolution industrielle a commencé :', 'single', 'a) Au XIXe siècle en Europe', 'b) Au XXe siècle en Asie', 'c) Au XVIIIe siècle en Amérique', 'd) Au XVIIe siècle en Afrique', 'A'),
(57, 13, 'La Première Guerre mondiale a eu lieu :', 'single', 'a) 1914–1918', 'b) 1939–1945', 'c) 1800–1815', 'd) 1900–1905', 'A'),
(58, 13, 'Le suffrage universel donne le droit de vote :', 'single', 'a) Aux hommes uniquement', 'b) À tous les citoyens adultes', 'c) Aux rois', 'd) Aux enfants', 'B'),
(59, 13, 'La révolution technologique concerne :', 'single', 'a) Les outils et machines modernes', 'b) La politique', 'c) La religion', 'd) La littérature', 'A'),
(60, 13, 'La Décolonisation après 1945 concerne principalement :', 'single', 'a) L’Asie et l’Afrique', 'b) L’Europe et l’Amérique', 'c) L’Océanie seulement', 'd) L’Antarctique', 'A'),
(61, 14, 'La mondialisation implique :', 'single', 'a) L’isolement des pays', 'b) L’interconnexion économique, culturelle et politique', 'c) La fermeture des frontières', 'd) L’abandon des langues locales', 'B'),
(62, 14, 'Un territoire peut être :', 'single', 'a) Une ville, une région ou un pays', 'b) Seulement une forêt', 'c) Seulement un continent', 'd) Seulement une île', 'A'),
(63, 14, 'La géopolitique étudie :', 'single', 'a) Les étoiles', 'b) Les relations de pouvoir et de territoire entre États', 'c) Les animaux', 'd) La météo', 'B'),
(64, 14, 'Un pays émergent est :', 'single', 'a) Très pauvre et instable', 'b) En développement rapide et influent', 'c) Ancien et stagnant', 'd) Une colonie', 'B'),
(65, 14, 'Les ONG interviennent principalement pour :', 'single', 'a) La politique internationale', 'b) Le développement, la santé et l’environnement', 'c) La guerre', 'd) L’armement', 'B'),
(66, 15, 'La justice signifie :', 'single', 'a) Respect des règles et équité', 'b) La richesse', 'c) Le pouvoir', 'd) La guerre', 'A'),
(67, 15, 'La liberté consiste à :', 'single', 'a) Faire tout ce qu’on veut sans limite', 'b) Agir selon ses choix en respectant autrui', 'c) Obéir toujours', 'd) Ignorer les lois', 'B'),
(68, 15, 'La conscience est :', 'single', 'a) La mémoire uniquement', 'b) La capacité de penser et de percevoir', 'c) La force physique', 'd) La chance', 'B'),
(69, 15, 'L’éthique étudie :', 'single', 'a) La science', 'b) La morale et les comportements corrects', 'c) La géographie', 'd) L’histoire', 'B'),
(70, 15, 'L’existentialisme s’intéresse :', 'single', 'a) À l’âme humaine et au choix individuel', 'b) À la physique', 'c) À la chimie', 'd) À la littérature uniquement', 'A'),
(71, 16, 'Méthodologie de la dissertation et étude des auteurs', 'single', 'a) Introduction, développement, conclusion', 'b) Résumé et questions', 'c) Dessins et schémas', 'd) Une seule phrase', 'A'),
(72, 16, 'La problématique :', 'single', 'a) Résume le texte', 'b) Pose la question centrale à traiter', 'c) Donne la conclusion', 'd) Présente l’auteur seulement', 'B'),
(73, 16, 'Une citation dans la dissertation sert à :', 'single', 'a) Illustrer un argument', 'b) Remplir la page', 'c) Confondre le lecteur', 'd) Introduire un nouveau sujet', 'A'),
(74, 16, 'Étudier un auteur philosophique consiste à :', 'single', 'a) Ignorer ses idées', 'b) Comprendre sa pensée et ses concepts', 'c) Copier son texte', 'd) Critiquer sans comprendre', 'A'),
(75, 16, 'La conclusion d’une dissertation doit :', 'single', 'a) Résumer et ouvrir sur une réflexion', 'b) Introduire un nouveau sujet', 'c) Ajouter des informations non vues', 'd) Répéter l’introduction mot pour mot', 'A'),
(76, 17, '« She is reading a book » signifie :', 'single', 'a) Elle écrit un livre', 'b) Elle lit un livre', 'c) Elle mange', 'd) Elle dort', 'B'),
(77, 17, '« I have been to Paris » est au temps :', 'single', 'a) Présent simple', 'b) Present perfect', 'c) Past simple', 'd) Future', 'B'),
(78, 17, 'Dans un dialogue, « How are you? » se traduit par :', 'single', 'a) Où es-tu ?', 'b) Comment ça va ?', 'c) Que fais-tu ?', 'd) Quand arrives-tu ? ✔ Réponse : b', 'B'),
(79, 17, 'Le mot « quickly » est un :', 'single', 'a) Nom', 'b) Verbe', 'c) Adverbe', 'd) Adjectif', 'C'),
(80, 17, 'Compréhension orale : Si quelqu’un dit « I am feeling tired », il est :', 'single', 'a) Heureux', 'b) Fatigué', 'c) Affamé', 'd) Excité', 'B'),
(81, 18, 'Un essay doit contenir :', 'single', 'a) Introduction, développement, conclusion', 'b) Dessins uniquement', 'c) Titres uniquement', 'd) Une phrase', 'A'),
(82, 18, '« In my opinion » est utilisé pour :', 'single', 'a) Exprimer son avis', 'b) Poser une question', 'c) Saluer', 'd) Conclure', 'A'),
(83, 18, 'Pour argumenter, on peut utiliser :', 'single', 'a) Firstly, Secondly, Finally', 'b) I eat, You eat', 'c) Bonjour, Merci', 'd) Il était une fois', 'A'),
(84, 18, 'Une lettre formelle commence souvent par :', 'single', 'a) Dear Sir/Madam', 'b) Hi buddy', 'c) Salut', 'd) Mon ami', 'A'),
(85, 18, 'Une conclusion efficace :', 'single', 'a) Résume et donne une ouverture', 'b) Ajoute des idées nouvelles', 'c) Ignore le sujet', 'd) Copie l’introduction', 'A');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.jpg',
  `bio` text DEFAULT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `username`, `email`, `phone`, `password`, `profile_pic`, `bio`, `role`, `created_at`) VALUES
(1, 'chidima', 'Praise', 'admin', 'admin@learnstep.com', NULL, '$2y$10$J4y2K9vF8s4r5t6y7u8i9oP0l.mN1b2c3d4e5f6g7h8i9j0kL1m2n', 'default.jpg', NULL, 'admin', '2025-11-27 11:03:31'),
(2, 'Chidima Praise', 'Jude Ugwu', 'chichibest', 'chichibest@gmail.com', NULL, '$2y$10$h0SGo4SFASqvwiwzEHY0Iud6TT2ZkF/P8TGr9jQZMQz6aHWlS3FMK', 'default.jpg', NULL, 'student', '2025-11-27 11:43:39'),
(3, 'Head', 'Admin', 'Head Admin', 'boss@learnstep.com', NULL, '$2y$10$ml3b.5/qL.KAgeaoNs5TNuJq5blyouYUUbDmTaSg73pvs9yHLFLRW', 'profile_3.jpg', NULL, 'admin', '2025-11-27 13:16:59'),
(4, 'moussa', 'Ali', 'moussa', 'moussa@gmail.com', '84021804', '$2y$10$Jnr7UIAcpfb./xinDVK8qOUQUW8KxGQsg7cSH6fNBtJftIYD0l9T.', 'profile_4.jpeg', 'hfhfdgdxjgv', 'student', '2025-11-28 01:55:47');

-- --------------------------------------------------------

--
-- Structure de la table `user_answers`
--

CREATE TABLE `user_answers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` varchar(10) NOT NULL,
  `attempt_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_answers`
--

INSERT INTO `user_answers` (`id`, `user_id`, `question_id`, `answer`, `attempt_at`) VALUES
(1, 4, 1, 'B', '2025-11-29 15:18:28'),
(2, 4, 2, 'A', '2025-11-29 15:18:28'),
(3, 4, 2, 'B', '2025-11-29 15:18:28');

-- --------------------------------------------------------

--
-- Structure de la table `user_module_progress`
--

CREATE TABLE `user_module_progress` (
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_module_progress`
--

INSERT INTO `user_module_progress` (`user_id`, `module_id`, `completed`, `completed_at`) VALUES
(3, 1, 1, '2025-11-28 15:16:56'),
(4, 1, 1, '2025-11-29 02:34:55'),
(4, 2, 1, '2025-11-29 09:54:40');

-- --------------------------------------------------------

--
-- Structure de la table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `lecture_id` int(11) DEFAULT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_progress`
--

INSERT INTO `user_progress` (`id`, `user_id`, `lecture_id`, `completed`, `completed_at`) VALUES
(1, 3, 1, 1, '2025-11-28 15:05:25'),
(2, 3, 2, 1, '2025-11-28 15:16:56'),
(3, 4, 1, 1, '2025-11-29 02:20:08'),
(4, 4, 2, 1, '2025-11-29 02:34:55'),
(5, 4, 3, 1, '2025-11-29 02:36:45');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Index pour la table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `course_resources`
--
ALTER TABLE `course_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Index pour la table `lectures`
--
ALTER TABLE `lectures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Index pour la table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Index pour la table `module_assignments`
--
ALTER TABLE `module_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_id` (`module_id`);

--
-- Index pour la table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Index pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Index pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_answers`
--
ALTER TABLE `user_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_answer_per_question` (`user_id`,`question_id`,`answer`,`attempt_at`),
  ADD KEY `idx_user_question` (`user_id`,`question_id`),
  ADD KEY `idx_attempt` (`attempt_at`);

--
-- Index pour la table `user_module_progress`
--
ALTER TABLE `user_module_progress`
  ADD PRIMARY KEY (`user_id`,`module_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Index pour la table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`lecture_id`),
  ADD KEY `lecture_id` (`lecture_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `course_resources`
--
ALTER TABLE `course_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `lectures`
--
ALTER TABLE `lectures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `module_assignments`
--
ALTER TABLE `module_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `user_answers`
--
ALTER TABLE `user_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `course_resources`
--
ALTER TABLE `course_resources`
  ADD CONSTRAINT `course_resources_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lectures`
--
ALTER TABLE `lectures`
  ADD CONSTRAINT `lectures_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `module_assignments`
--
ALTER TABLE `module_assignments`
  ADD CONSTRAINT `module_assignments_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);

--
-- Contraintes pour la table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_module_progress`
--
ALTER TABLE `user_module_progress`
  ADD CONSTRAINT `user_module_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_module_progress_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`lecture_id`) REFERENCES `lectures` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
