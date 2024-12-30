START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS "comment" (
  comment_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  content TEXT COLLATE utf8mb4_general_ci NOT NULL,
  created_at DATETIME NOT NULL,
  post_id BIGINT UNSIGNED NOT NULL,
  author BIGINT UNSIGNED NOT NULL
) ;

INSERT INTO comment (comment_id, content, created_at, post_id, author, status) VALUES
(6, 'J''adore l''idée d''utiliser Tailwind CSS pour un design rapide et efficace. Merci pour cet article !', '2024-12-30 11:46:57', 13, 8, 'validated'),
(7, 'Les injections SQL me font toujours peur. Merci pour les conseils !', '2024-12-30 11:47:21', 14, 8, 'validated'),
(8, 'TypeScript améliore vraiment la qualité du code. Merci pour cet article !', '2024-12-30 11:47:43', 15, 8, 'validated'),
(9, 'Les PWA sont l''avenir des applications web. Merci pour cette introduction claire !', '2024-12-30 11:48:04', 16, 8, 'validated'),
(10, 'VS Code est définitivement mon outil préféré. Merci pour les autres suggestions !', '2024-12-30 11:48:22', 17, 8, 'validated'),
(51, 'React et Vue.js sont mes outils préférés, mais cet article m''a donné envie d''essayer Svelte.', '2024-12-01 13:00:00', 13, 9, 'validated'),
(52, 'C''est impressionnant de voir comment le front-end évolue. Merci pour ce résumé clair.', '2024-12-01 14:00:00', 13, 10, 'validated'),
(53, 'Super article ! J''ai découvert quelques astuces pour améliorer mes workflows.', '2024-12-01 15:00:00', 13, 11, 'validated'),
(54, 'Les frameworks modernes sont fascinants, et cet article explique bien leur utilité.', '2024-12-01 16:00:00', 13, 12, 'validated'),
(55, 'Je vais enfin m''attaquer à la gestion correcte des sessions grâce à cet article.', '2024-12-02 13:00:00', 14, 9, 'validated'),
(56, 'Merci pour cet article. Les erreurs courantes en PHP sont vraiment bien expliquées.', '2024-12-02 14:00:00', 14, 10, 'validated'),
(57, 'La sécurité est cruciale. Cet article est un bon rappel des bonnes pratiques.', '2024-12-02 15:00:00', 14, 11, 'validated'),
(58, 'Je vais partager cet article avec mes collègues. C''est une excellente ressource.', '2024-12-02 16:00:00', 14, 12, 'validated'),
(59, 'Je suis convaincu d''adopter TypeScript pour mes futurs projets.', '2024-12-03 13:00:00', 15, 9, 'validated'),
(60, 'Les types statiques sauvent tellement de temps lors du débogage. Article très utile.', '2024-12-03 14:00:00', 15, 10, 'validated'),
(61, 'TypeScript est définitivement un incontournable en 2024.', '2024-12-03 15:00:00', 15, 11, 'validated'),
(62, 'Merci pour cet article ! Je vais explorer les fonctionnalités avancées de TypeScript.', '2024-12-03 16:00:00', 15, 12, 'validated'),
(63, 'J''ai hâte d''implémenter les notifications push dans ma prochaine PWA.', '2024-12-04 13:00:00', 16, 9, 'validated'),
(64, 'Cet article explique bien les avantages des Progressive Web Apps. Merci !', '2024-12-04 14:00:00', 16, 10, 'validated'),
(65, 'Les PWA semblent être l''avenir du développement web.', '2024-12-04 15:00:00', 16, 11, 'validated'),
(66, 'Je vais tester la mise en cache des données avec Service Workers grâce à cet article.', '2024-12-04 16:00:00', 16, 12, 'validated'),
(67, 'Les outils de collaboration comme GitHub ont révolutionné le développement.', '2024-12-05 13:00:00', 17, 9, 'validated'),
(68, 'Cet article est une excellente introduction aux outils indispensables.', '2024-12-05 14:00:00', 17, 10, 'validated'),
(69, 'Merci pour ce guide ! J''ai découvert des outils que je ne connaissais pas.', '2024-12-05 15:00:00', 17, 11, 'validated'),
(70, 'Je vais essayer les outils de débogage recommandés dans cet article.', '2024-12-05 16:00:00', 17, 12, 'validated');

CREATE TABLE IF NOT EXISTS post (
  post_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(250) COLLATE utf8mb4_general_ci NOT NULL,
  chapo TEXT COLLATE utf8mb4_general_ci NOT NULL,
  content TEXT COLLATE utf8mb4_general_ci NOT NULL,
  author BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  PRIMARY KEY (post_id),
  UNIQUE KEY post_id (post_id),
  KEY author (author)
) ;

INSERT INTO post (post_id, title, chapo, content, author, created_at, updated_at) VALUES
(13, 'Les meilleures pratiques du développement front-end en 2024', 'Découvrez les dernières tendances et pratiques en matière de développement front-end pour améliorer vos projets web.', 'Le développement front-end évolue constamment, avec des frameworks comme React, Vue.js et Svelte. Les développeurs doivent également adopter des outils comme Tailwind CSS pour accélérer la création de designs modernes et accessibles. Dans cet article, nous explorons comment rester à jour et optimiser vos flux de travail.', 3, '2024-12-30 10:41:58', NULL),
(14, '10 erreurs courantes en PHP à éviter', 'Évitez les pièges classiques en PHP grâce à ce guide qui détaille les erreurs courantes et comment les corriger.', 'PHP reste l''un des langages les plus utilisés pour le développement web, mais de nombreuses erreurs peuvent ralentir vos projets. Nous couvrons des sujets comme la gestion des sessions, les failles de sécurité courantes comme les injections SQL, et l''utilisation efficace des frameworks comme Symfony et Laravel.', 3, '2024-12-30 10:43:55', '2024-12-30 09:49:31'),
(15, 'Pourquoi TypeScript est incontournable en 2024', 'TypeScript est devenu un incontournable pour les développeurs JavaScript. Découvrez pourquoi et comment l''adopter.', 'TypeScript ajoute des types statiques à JavaScript, offrant ainsi une meilleure sécurité et lisibilité du code. Avec des fonctionnalités comme l''inférence de types et une intégration facile avec les outils modernes, c''est un choix idéal pour les grands projets d''équipe.', 3, '2024-12-30 10:45:04', NULL),
(16, 'Introduction aux Progressive Web Apps (PWA)', 'Apprenez comment créer des applications web modernes qui rivalisent avec les applications natives.', 'Les Progressive Web Apps combinent le meilleur des applications web et natives, offrant une expérience utilisateur rapide et immersive. Nous explorerons comment mettre en œuvre des fonctionnalités comme le mode hors-ligne, les notifications push et l''installation sur le bureau.', 3, '2024-12-30 10:46:35', '2024-12-30 09:48:45'),
(17, 'Les outils indispensables pour les développeurs web', 'Découvrez une sélection des meilleurs outils pour améliorer votre productivité en développement web.', 'Des éditeurs de code comme VS Code aux outils de collaboration comme GitHub, cet article couvre une gamme d''outils qui permettent aux développeurs de travailler efficacement. Nous aborderons également des outils spécifiques pour le débogage, les tests, et le déploiement.', 3, '2024-12-30 10:48:21', NULL);

CREATE TABLE IF NOT EXISTS "user" (
  user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  last_name VARCHAR(150) COLLATE utf8mb4_general_ci NOT NULL,
  first_name VARCHAR(150) COLLATE utf8mb4_general_ci NOT NULL,
  email VARCHAR(250) COLLATE utf8mb4_general_ci NOT NULL
) ;

INSERT INTO user (user_id, last_name, first_name, email, password, role, created_at, updated_at, token, expire_at, pwd_reset_token, pwd_reset_expires_at) VALUES
(3, 'Fauquembergue', 'Adrien', 'adrien_fauque@hotmail.fr', '$2y$10$FVSQouWlo4wMuhcF3WNhiOO11UL8Vp6XGHaeSLWIkNUhl34DJc/QW', 'Admin', '2024-10-04 09:58:36', '2024-12-01 15:45:05', NULL, NULL, NULL, NULL),
(8, 'Alice', 'Dupont', 'alice.dupont@example.com', '$2y$10$6GFSDpVrs813p1LEAS9vpuikbh7ET728kpwAgbCr47K3isW94k5qO', 'User', '2024-12-30 10:27:28', NULL, NULL, NULL, NULL, NULL),
(9, 'Martin', 'Bob', 'bob.martin@example.com', '52bc47f80b6fe698e9a14327e1bb7ddfa39a21740c8c7017329f3ec555a3bcf6', 'User', '2023-12-10 14:45:00', NULL, NULL, NULL, NULL, NULL),
(10, 'Lemoine', 'Clara', 'clara.lemoine@example.com', 'c8cd48efd52d009071a8198ef35301cce2036cad0bf389a7e5b5fd5a7ce5e3c4', 'User', '2024-03-01 09:15:00', NULL, NULL, NULL, NULL, NULL),
(11, 'Morel', 'David', 'david.morel@example.com', '168d28673459c4a5aa56a09dfd6bb180746f9e7a688164e8d6dd2ebe16468afe', 'User', '2024-02-20 11:20:00', NULL, NULL, NULL, NULL, NULL),
(12, 'Blanc', 'Emma', 'emma.blanc@example.com', '8f49ed3f60eac40114d9ccd0f4c01c57cdfc1444c67cb2416d2a0e8704f16c0d', 'User', '2023-11-05 16:00:00', NULL, NULL, NULL, NULL, NULL),
(14, 'Admin', 'User', 'admin@example.com', '$2y$10$KsLfRb7J4I9NUQoXNS1O8.m8VeDiel/z09uWYTMiC7yVixLNIubsS', 'Admin', '2024-12-30 11:11:27', NULL, NULL, NULL, NULL, NULL);


ALTER TABLE post
  ADD CONSTRAINT post_ibfk_1 FOREIGN KEY (author) REFERENCES "user" (user_id);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
