CREATE DATABASE IF NOT EXISTS readpilot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE readpilot;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  google_sub VARCHAR(255) NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('teacher', 'admin') NOT NULL,
  school VARCHAR(160) NOT NULL DEFAULT '',
  grade_level VARCHAR(80) NULL,
  section_name VARCHAR(80) NULL,
  bio TEXT NOT NULL DEFAULT '',
  status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'active',
  last_login DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT NOT NULL DEFAULT '';
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE users ADD COLUMN IF NOT EXISTS google_sub VARCHAR(255) NULL;

INSERT IGNORE INTO users
  (full_name, email, password_hash, role, school, grade_level, section_name, status, last_login)
VALUES
  ('Ms. Hernandez', 'teacher@readpilot.local', '$2y$10$AlpEExDNWr7OJDm.B7fSF.L2a3G3RR3jq4HAYkMnWlTXHoAxA57lK', 'teacher', 'ReadPilot School', 'Grade 3', 'Section A', 'active', NULL),
  ('Elena Hernandez', 'elena.hernandez@readpilot.app', '$2y$10$YYe2373idpvUas08T4/P6um4pXch3tXYuIftCS3Q0BSbMA4v49JNO', 'teacher', 'ReadPilot School', 'Grade 3', 'Section B', 'active', '2026-08-21 07:58:00'),
  ('Priya Nair', 'priya.nair@readpilot.app', '$2y$10$YYe2373idpvUas08T4/P6um4pXch3tXYuIftCS3Q0BSbMA4v49JNO', 'teacher', 'ReadPilot School', 'Grade 1', 'Section A', 'active', '2026-08-21 09:12:00'),
  ('Diego Fuentes', 'diego.fuentes@readpilot.app', '$2y$10$YYe2373idpvUas08T4/P6um4pXch3tXYuIftCS3Q0BSbMA4v49JNO', 'teacher', 'ReadPilot School', 'Grade 3', 'Section A', 'active', '2026-08-20 15:41:00'),
  ('Marco Villanueva', 'marco.villanueva@readpilot.app', '$2y$10$YYe2373idpvUas08T4/P6um4pXch3tXYuIftCS3Q0BSbMA4v49JNO', 'teacher', 'ReadPilot School', 'Grade 5', 'Section A', 'inactive', '2026-08-20 16:47:00'),
  ('Liza Cortez', 'liza.cortez@readpilot.app', '$2y$10$YYe2373idpvUas08T4/P6um4pXch3tXYuIftCS3Q0BSbMA4v49JNO', 'teacher', 'ReadPilot School', 'Grade 2', 'Section B', 'active', '2026-08-19 08:20:00'),
  ('Noel Bautista', 'noel.bautista@readpilot.app', '$2y$10$YYe2373idpvUas08T4/P6um4pXch3tXYuIftCS3Q0BSbMA4v49JNO', 'teacher', 'ReadPilot School', 'Grade 4', 'Section A', 'active', '2026-08-18 13:05:00'),
  ('Rafael Santos', 'admin@readpilot.local', '$2y$10$/hMq7hKJQPRD1BTeIKbX/.P/jtpvVT.TZpKSV/ni1UF7l/QKy0M96', 'admin', 'ReadPilot School', NULL, NULL, 'active', NULL),
  ('Ana Cabrera', 'ana.cabrera@readpilot.app', '$2y$10$YYe2373idpvUas08T4/P6um4pXch3tXYuIftCS3Q0BSbMA4v49JNO', 'admin', 'ReadPilot School', NULL, NULL, 'active', '2026-08-17 17:15:00');

CREATE TABLE IF NOT EXISTS sections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_teacher_section (teacher_id, name),
  CONSTRAINT fk_section_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO sections (teacher_id, name)
SELECT id, 'Section A' FROM users WHERE email = 'teacher@readpilot.local'
UNION ALL SELECT id, 'Section B' FROM users WHERE email = 'teacher@readpilot.local'
UNION ALL SELECT id, 'Section C' FROM users WHERE email = 'teacher@readpilot.local';

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  section_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  color VARCHAR(20) NOT NULL DEFAULT '#6fbf5a',
  book VARCHAR(190) NOT NULL DEFAULT '',
  wpm SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  accuracy TINYINT UNSIGNED NOT NULL DEFAULT 0,
  sessions_count INT UNSIGNED NOT NULL DEFAULT 0,
  sessions_this_week INT UNSIGNED NOT NULL DEFAULT 0,
  books_completed INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('new', 'ontrack', 'support', 'inactive') NOT NULL DEFAULT 'new',
  last_active DATETIME NULL,
  notes TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_teacher_student_name (teacher_id, name),
  INDEX idx_students_teacher (teacher_id),
  CONSTRAINT fk_student_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_student_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

ALTER TABLE students MODIFY status ENUM('new', 'ontrack', 'support', 'inactive') NOT NULL DEFAULT 'new';

INSERT IGNORE INTO students (teacher_id, section_id, name, color, book, wpm, accuracy, sessions_count, sessions_this_week, books_completed, status, last_active, notes)
SELECT u.id, s.id, x.name, x.color, x.book, x.wpm, x.accuracy, x.sessions_count, x.sessions_this_week, x.books_completed, x.status, x.last_active, x.notes
FROM (SELECT 'Carmen Reyes' name, 'Section A' section_name, '#6fbf5a' color, 'A Rainy Day Surprise' book, 312 wpm, 98 accuracy, 15 sessions_count, 3 sessions_this_week, 8 books_completed, 'ontrack' status, '2024-05-12 15:45:00' last_active, '' notes
UNION ALL SELECT 'Eva Mendoza','Section A','#c9924d','The Three Little Pigs',298,95,10,2,5,'ontrack','2024-05-12 14:55:00',''
UNION ALL SELECT 'Isabella Ramos','Section A','#f2a13a','Journey to the Stars',284,90,9,2,6,'ontrack','2024-05-12 14:30:00',''
UNION ALL SELECT 'Diego Santos','Section A','#8b6bd1','The Kind Knight',245,88,8,1,4,'ontrack','2024-05-11 13:15:00',''
UNION ALL SELECT 'Maya Cruz','Section B','#4fa3b8','How Butterflies Are Born',268,92,11,2,7,'ontrack','2024-05-11 11:40:00',''
UNION ALL SELECT 'Liam Torres','Section B','#ea5d5d','Our Solar System',190,79,6,1,3,'support','2024-05-10 16:05:00','Struggles with multisyllable words'
UNION ALL SELECT 'Sofia Delgado','Section B','#6fbf5a','The Grumpy Garden Gnome',255,91,9,2,5,'ontrack','2024-05-10 10:20:00',''
UNION ALL SELECT 'Mateo Villanueva','Section B','#f2a13a','The Lion and the Mouse',210,83,7,1,3,'ontrack','2024-05-09 15:00:00',''
UNION ALL SELECT 'Ana Bautista','Section C','#8b6bd1','A Rainy Day Surprise',172,75,5,0,2,'support','2024-05-06 09:50:00','Needs encouragement to keep pace'
UNION ALL SELECT 'Noah Garcia','Section C','#4fa3b8','Journey to the Stars',302,96,12,3,9,'ontrack','2024-05-12 09:10:00',''
UNION ALL SELECT 'Camila Flores','Section C','#ea5d5d','The Three Little Pigs',230,87,8,1,4,'ontrack','2024-05-08 13:35:00',''
UNION ALL SELECT 'Ethan Morales','Section C','#c9924d','How Butterflies Are Born',260,93,10,2,6,'ontrack','2024-05-07 14:15:00','') x
JOIN users u ON u.email = 'teacher@readpilot.local'
JOIN sections s ON s.teacher_id = u.id AND s.name = x.section_name
;

CREATE TABLE IF NOT EXISTS reading_sessions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  book VARCHAR(190) NOT NULL,
  wpm SMALLINT UNSIGNED NOT NULL,
  accuracy TINYINT UNSIGNED NOT NULL,
  duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  tricky_words TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seed_session (teacher_id, student_id, book, created_at),
  INDEX idx_sessions_teacher (teacher_id, created_at),
  CONSTRAINT fk_session_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_session_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS session_recordings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  session_id BIGINT UNSIGNED NOT NULL,
  bucket VARCHAR(222) NOT NULL,
  storage_key VARCHAR(1024) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL,
  etag VARCHAR(255) NOT NULL DEFAULT '',
  duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  consent_confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_recording_session (session_id),
  INDEX idx_recordings_teacher_created (teacher_id, created_at),
  CONSTRAINT fk_recording_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_recording_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_recording_session FOREIGN KEY (session_id) REFERENCES reading_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE session_recordings ADD COLUMN IF NOT EXISTS consent_confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

INSERT INTO reading_sessions (teacher_id, student_id, book, wpm, accuracy, duration_seconds, tricky_words, created_at)
SELECT teacher.id, student.id, seed.book, seed.wpm, seed.accuracy, 0, '', seed.created_at
FROM (SELECT 'Carmen Reyes' student_name, 'The Lion and the Mouse' book, 379 wpm, 100 accuracy, '2024-05-12 15:45:00' created_at
UNION ALL SELECT 'Carmen Reyes','The Lion and the Mouse',312,98,'2024-05-12 15:20:00'
UNION ALL SELECT 'Eva Mendoza','The Three Little Pigs',298,95,'2024-05-12 14:55:00'
UNION ALL SELECT 'Isabella Ramos','Journey to the Stars',284,90,'2024-05-12 14:30:00'
UNION ALL SELECT 'Diego Santos','The Kind Knight',245,88,'2024-05-11 13:15:00'
UNION ALL SELECT 'Maya Cruz','How Butterflies Are Born',268,92,'2024-05-11 11:40:00'
UNION ALL SELECT 'Liam Torres','Our Solar System',190,79,'2024-05-10 16:05:00'
UNION ALL SELECT 'Sofia Delgado','The Grumpy Garden Gnome',255,91,'2024-05-10 10:20:00'
UNION ALL SELECT 'Mateo Villanueva','The Lion and the Mouse',210,83,'2024-05-09 15:00:00'
UNION ALL SELECT 'Ana Bautista','A Rainy Day Surprise',172,75,'2024-05-06 09:50:00'
UNION ALL SELECT 'Noah Garcia','Journey to the Stars',302,96,'2024-05-12 09:10:00'
UNION ALL SELECT 'Camila Flores','The Three Little Pigs',230,87,'2024-05-08 13:35:00'
UNION ALL SELECT 'Ethan Morales','How Butterflies Are Born',260,93,'2024-05-07 14:15:00') seed
JOIN students student ON student.name = seed.student_name
JOIN users teacher ON teacher.id = student.teacher_id AND teacher.email = 'teacher@readpilot.local'
WHERE NOT EXISTS (
  SELECT 1 FROM reading_sessions existing
  WHERE existing.teacher_id = teacher.id
    AND existing.student_id = student.id
    AND existing.book = seed.book
    AND existing.created_at = seed.created_at
);

CREATE TABLE IF NOT EXISTS quiz_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  score SMALLINT UNSIGNED NOT NULL,
  total_questions SMALLINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_attempt_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_attempt_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_ai_reports (
  quiz_attempt_id BIGINT UNSIGNED PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  reading_session_count INT UNSIGNED NOT NULL DEFAULT 0,
  reading_wpm SMALLINT UNSIGNED NULL,
  reading_accuracy TINYINT UNSIGNED NULL,
  summary TEXT NOT NULL,
  recommendation TEXT NOT NULL,
  model_name VARCHAR(80) NOT NULL DEFAULT 'readpilot-quiz-insight-engine',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_quiz_report_student_created (teacher_id, student_id, created_at),
  CONSTRAINT fk_quiz_report_attempt FOREIGN KEY (quiz_attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
  CONSTRAINT fk_quiz_report_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_quiz_report_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS resources (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL UNIQUE,
  author VARCHAR(190) NOT NULL,
  genre VARCHAR(80) NOT NULL,
  level VARCHAR(80) NOT NULL,
  lexile VARCHAR(40) NOT NULL DEFAULT '',
  words INT UNSIGNED NOT NULL DEFAULT 0,
  description TEXT NOT NULL,
  tags_json JSON NOT NULL,
  story_json JSON NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_resource_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS resource_assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  resource_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_assignment_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_assignment_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  CONSTRAINT fk_assignment_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS resource_materials (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  resource_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL DEFAULT 'text/plain',
  content_text LONGTEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_material_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  CONSTRAINT fk_material_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_quizzes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  resource_id INT UNSIGNED NULL,
  title VARCHAR(190) NOT NULL,
  questions_json JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_teacher_quiz_title (teacher_id, title),
  CONSTRAINT fk_quiz_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_quiz_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO resources (title, author, genre, level, lexile, words, description, tags_json, story_json)
VALUES
 ('The Lion and the Mouse','Aesop (retold)','Fable','Grade 3','430L',620,'A classic fable about kindness and an unexpected friendship between a mighty lion and a small mouse','["Fiction","Read Aloud"]',NULL),
 ('Journey to the Stars','Priya Anand','Sci-Fi','Grade 3','510L',980,'A young astronaut-in-training imagines a voyage past the moon and back before bedtime','["Fiction","Space","Adventure"]',NULL),
 ('The Three Little Pigs','Traditional','Folktale','Grade 2–3','390L',540,'The timeless tale of three pigs, three houses, and one very determined wolf','["Fiction","Folktale"]',NULL),
 ('A Rainy Day Surprise','Maribel Cruz','Realistic Fiction','Grade 3','460L',710,'When recess gets rained out, a class discovers an unexpected indoor adventure','["Fiction","Friendship"]',NULL),
 ('How Butterflies Are Born','Dr. Elena Ford','Nonfiction','Grade 3','540L',860,'A step-by-step look at metamorphosis, from egg to caterpillar to butterfly','["Nonfiction","Science"]',NULL),
 ('The Kind Knight','Owen Park','Fantasy','Grade 3','470L',690,'A knight who would rather solve problems with kindness than a sword','["Fiction","Fantasy"]',NULL),
 ('Our Solar System','NASA Kids (adapted)','Nonfiction','Grade 3–4','600L',1100,'An overview of the eight planets and what makes each one unique','["Nonfiction","Space"]',NULL),
 ('The Grumpy Garden Gnome','Nora Bell','Fantasy','Grade 3','440L',640,'A gnome who dislikes visitors learns the value of good company','["Fiction","Fantasy","Humor"]',NULL),
 ('The Garden That Grew Together','ReadPilot','Realistic Fiction','Grade 3','520L',109,'Neighbors revive a shared garden by planning together and taking turns caring for it','["Fiction","Community","Intermediate"]','["On Saturday, the neighborhood garden looked smaller than Ana remembered.","Three days of hot sun had dried the soil.","The bean leaves drooped beside their empty watering can.","Ana asked the neighbors what they could do.","Mr. Lee suggested collecting rain in clean barrels.","The children measured the roof to find a safe place for one barrel.","Ms. Ortiz checked the plan before they filled it.","Then each family chose one day to water the beds.","A week later, the beans stood tall again.","Tiny white flowers opened near the fence.","At harvest time, everyone brought a basket.","The garden had grown because each person shared a small job."]'),
 ('The River''s Quiet Warning','ReadPilot','Nonfiction','Grade 4','680L',175,'Students investigate how rain, bare soil, and streamside plants affect a river after storms','["Nonfiction","Science","Advanced"]','["After three days of rain, the river rose past the painted mark beneath Cedar Bridge.","The water moved fast, carrying twigs but leaving the larger branches behind.","At first, the school team assumed the old footpath caused the muddy bank.","Their measurements suggested a different story: the bank was bare where grass had been removed.","Roots from native grasses hold soil in place, while pavement sends rain quickly toward the river.","The team mapped where puddles formed after each storm, noting which drains emptied near the bend.","They planted grasses above the bank and placed signs asking walkers to stay on the path.","A month later, another storm brought less mud to the river, although the water still rose.","The change did not prove the plants caused every difference; rainfall had also been lighter.","The students compared photos, rain totals, and water samples before drawing a careful conclusion.","They recommended keeping the grasses and gathering measurements through the next wet season.","Their report showed how patient observations can turn a worry into a testable plan."]'),
 ('Short Vowel Sound Practice','ReadPilot','Phonics Practice','Grade 2','300L',45,'A short, decodable reading that repeats simple consonant-vowel-consonant words with short a, e, i, o, and u sounds.','["Phonics","Decoding","Short Vowels","Beginner"]','["Sam has a red bag.","A black cat naps on the bag.","Sam gets a pen and a big map.","The cat sits on the map.","Sam puts the map in a box.","Then the cat hops in the box.","Sam and the cat grin."]'),
 ('Syllable Steps: The Picnic','ReadPilot','Phonics Practice','Grade 2–3','400L',47,'A guided decodable story with familiar two-syllable words to practice clapping, dividing, and blending word parts.','["Phonics","Decoding","Syllables","Multisyllabic Words"]','["Mia packs a picnic basket.","She puts a napkin and a lemon in it.","Her sister brings a paper plate.","They follow a sunset path to the garden.","A rabbit hops beside them.","Mia shares a muffin with her sister.","They clean up and walk home together."]'),
 ('Read Smoothly: A Garden Walk','ReadPilot','Fluency Practice','Grade 2–3','420L',41,'Short, rhythmic sentences with repeated phrases to help developing readers build accurate, comfortable pacing.','["Fluency","Repeated Reading","Beginner"]','["We walk to the garden.","We walk past the gate.","We walk by the green rows.","Stop and look.","A bee hums by a flower.","A bird sings from a branch.","We walk slowly home.","We can read it once more."]'),
 ('Meaning Makers: The Missing Mittens','ReadPilot','Comprehension Practice','Grade 3','480L',74,'A brief story with clear clues that supports retelling, finding the main idea, and making simple inferences.','["Comprehension","Main Idea","Inference","Intermediate"]','["Nora looked for her red mittens before recess.","The bench was empty, but a few drops of snow rested on it.","She saw a small trail of snow leading toward the coat hooks.","A classmate was hanging up a wet coat nearby.","Nora checked beside the hooks and found her mittens.","She thanked her classmate for bringing them inside.","Outside, Nora joined her friends in the snow.","Small clues helped Nora solve the problem."]')

CREATE TABLE IF NOT EXISTS access_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  google_sub VARCHAR(255) NULL,
  password_hash VARCHAR(255) NOT NULL DEFAULT '',
  school VARCHAR(160) NOT NULL,
  reason TEXT NOT NULL,
  requested_role ENUM('teacher', 'admin') NOT NULL,
  id_document_path VARCHAR(255) NULL,
  status ENUM('pending', 'approved', 'declined') NOT NULL DEFAULT 'pending',
  reviewed_by INT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_access_status (status),
  CONSTRAINT fk_access_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE access_requests ADD COLUMN IF NOT EXISTS google_sub VARCHAR(255) NULL;
ALTER TABLE access_requests ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE access_requests ADD COLUMN IF NOT EXISTS id_document_path VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  details VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_created (created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recommendations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  source ENUM('ai', 'specialist', 'manual') NOT NULL DEFAULT 'manual',
  status ENUM('pending', 'approved', 'applied', 'dismissed') NOT NULL DEFAULT 'pending',
  related_book VARCHAR(190) NOT NULL DEFAULT '',
  skill_focus VARCHAR(190) NOT NULL DEFAULT '',
  practice_notes TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  approved_at DATETIME NULL,
  applied_at DATETIME NULL,
  INDEX idx_recommendations_teacher_status (teacher_id, status, created_at),
  CONSTRAINT fk_recommendation_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_recommendation_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE quiz_attempts ADD COLUMN IF NOT EXISTS session_id BIGINT UNSIGNED NULL AFTER student_id;

CREATE TABLE IF NOT EXISTS session_ai_evaluations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  session_id BIGINT UNSIGNED NOT NULL UNIQUE,
  quiz_attempt_id BIGINT UNSIGNED NULL,
  fluency_rating ENUM('advanced', 'proficient', 'approaching', 'emerging') NOT NULL DEFAULT 'proficient',
  comprehension_rating ENUM('excellent', 'good', 'partial', 'needs_support') NOT NULL DEFAULT 'good',
  overall_progress_status ENUM('rapid_growth', 'on_track', 'steady', 'needs_intervention') NOT NULL DEFAULT 'on_track',
  progress_narrative TEXT NOT NULL,
  phonics_insight TEXT NOT NULL,
  comprehension_insight TEXT NOT NULL,
  strengths_json JSON NOT NULL,
  struggles_json JSON NOT NULL,
  actionable_next_step TEXT NOT NULL,
  model_name VARCHAR(80) NOT NULL DEFAULT 'gemini-1.5-flash',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ai_eval_student (student_id, created_at),
  INDEX idx_ai_eval_teacher (teacher_id),
  CONSTRAINT fk_eval_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_eval_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_eval_session FOREIGN KEY (session_id) REFERENCES reading_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_eval_quiz FOREIGN KEY (quiz_attempt_id) REFERENCES quiz_attempts(id) ON DELETE SET NULL
) ENGINE=InnoDB;
