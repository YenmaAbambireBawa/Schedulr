-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2026 at 09:19 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `schedulr_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `credits` int(11) DEFAULT 1,
  `course_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_name`, `dept_id`, `credits`, `course_description`, `created_at`) VALUES
(1, 'CS 111', 'Introduction to Computing & Information Systems', 1, 1, 'Foundational course introducing students to core computing concepts, information systems design, and the role of technology in modern society. Covers computer hardware, software, networking basics, and information management principles.', '2026-02-05 14:57:10'),
(2, 'CS 112', 'Computer Programming for Engineering', 1, 1, 'Programming fundamentals tailored for engineering students. Focuses on problem-solving techniques, algorithm development, and implementation using a high-level programming language. Emphasizes computational thinking and applications in engineering contexts.', '2026-02-05 14:57:10'),
(3, 'CS 212', 'Computer Programming for Computer Science', 1, 1, 'Comprehensive introduction to programming for computer science majors. Covers object-oriented programming concepts, data types, control structures, functions, and basic software development practices. Builds strong foundation for advanced computer science studies.', '2026-02-05 14:57:10'),
(4, 'CS 221', 'Discrete Theory and Structures', 1, 1, 'Mathematical foundations essential for computer science. Topics include logic, set theory, proof techniques, relations, functions, combinatorics, graph theory, and discrete probability. Develops rigorous mathematical thinking crucial for algorithm analysis and theoretical computer science.', '2026-02-05 14:57:10'),
(5, 'CS 312', 'Intermediate Computer Programming', 1, 1, 'Advanced programming course focusing on software design patterns, complex data structures, file I/O, exception handling, and multi-threaded programming. Emphasizes code organization, testing, debugging, and professional software development practices.', '2026-02-05 14:57:10'),
(6, 'CS 313', 'Intermediate Computer Programming', 1, 1, 'Alternative intermediate programming course covering advanced programming techniques, software architecture, design principles, and development methodologies. Prepares students for upper-level computer science coursework and industry applications.', '2026-02-05 14:57:10'),
(7, 'CS 314', 'Human Computer Interaction', 1, 1, 'Study of interaction between humans and computer systems. Covers user-centered design principles, usability evaluation methods, interface design guidelines, accessibility standards, and user experience research. Includes hands-on projects in interface prototyping and evaluation.', '2026-02-05 14:57:10'),
(8, 'CS 323', 'Database Systems', 1, 1, 'Comprehensive introduction to database management systems. Topics include relational database design, normalization, SQL, transaction management, indexing, and query optimization. Students design and implement databases for real-world applications.', '2026-02-05 14:57:10'),
(9, 'CS 331', 'Computer Organization and Architecture', 1, 1, 'Exploration of computer hardware architecture and organization. Covers digital logic, processor design, memory hierarchy, assembly language programming, instruction sets, and computer arithmetic. Provides understanding of how software interacts with hardware at the system level.', '2026-02-05 14:57:10'),
(10, 'CS 341', 'Web Technologies', 1, 1, 'Modern web development covering both front-end and back-end technologies. Topics include HTML5, CSS3, JavaScript, responsive design, RESTful APIs, server-side programming, web frameworks, and deployment. Students build full-stack web applications.', '2026-02-05 14:57:10'),
(11, 'CS 400', 'Thesis', 1, 1, 'Capstone research project requiring students to conduct original research in computer science. Includes literature review, research methodology, experimentation or development, analysis, and formal thesis writing. Culminates in oral defense and written dissertation.', '2026-02-05 14:57:10'),
(12, 'CS 410', 'Applied Project', 1, 1, 'Practical capstone project applying computer science knowledge to solve real-world problems. Students work individually or in teams to design, implement, test, and deploy a substantial software system. Includes project management, documentation, and presentation components.', '2026-02-05 14:57:10'),
(13, 'CS 415', 'Software Engineering', 1, 1, 'Comprehensive study of software engineering principles and practices. Covers software development life cycle, requirements engineering, design methodologies, testing strategies, project management, version control, and collaborative development. Emphasizes professional software development in team environments.', '2026-02-05 14:57:10'),
(14, 'CS 422', 'Data Structures & Algorithms', 1, 1, 'In-depth study of fundamental data structures (arrays, linked lists, stacks, queues, trees, graphs, hash tables) and algorithms (sorting, searching, traversal). Includes algorithm complexity analysis, Big-O notation, and trade-offs in algorithm design. Essential foundation for advanced computer science topics.', '2026-02-05 14:57:10'),
(15, 'CS 424', 'Advanced Database Systems', 1, 1, 'Advanced database topics including distributed databases, NoSQL systems, database security, data warehousing, big data technologies, database performance tuning, and emerging database paradigms. Prepares students for enterprise-level database administration and design.', '2026-02-05 14:57:10'),
(16, 'CS 432', 'Computer Networks and Data Communications', 1, 1, 'Comprehensive study of computer networking and data communications. Topics include network protocols (TCP/IP), OSI model, network security, wireless networks, network design and administration. Hands-on experience with network configuration and troubleshooting.', '2026-02-05 14:57:10'),
(17, 'CS 435', 'Operating Systems', 1, 1, 'Fundamental concepts in operating system design and implementation. Covers process management, scheduling algorithms, memory management, file systems, deadlock handling, concurrency, and system security. Includes practical exercises in OS-level programming.', '2026-02-05 14:57:10'),
(18, 'CS 442', 'E-Commerce', 1, 1, 'Electronic commerce systems and technologies. Topics include e-business models, online payment systems, security in e-commerce, digital marketing, supply chain management, and legal/ethical issues. Students design and implement e-commerce solutions.', '2026-02-05 14:57:10'),
(19, 'CS 443', 'Mobile Application Development', 1, 1, 'Development of mobile applications for iOS and Android platforms. Covers mobile UI/UX design, platform-specific APIs, mobile database integration, location-based services, push notifications, and app deployment. Students create functional mobile applications.', '2026-02-05 14:57:10'),
(20, 'CS 451', 'Networks & Distributed Computing', 1, 1, 'Advanced networking and distributed computing concepts. Topics include distributed algorithms, remote procedure calls, distributed file systems, cloud computing, peer-to-peer systems, and distributed application design. Focuses on building scalable distributed systems.', '2026-02-05 14:57:10'),
(21, 'CS 452-CG', 'Computer Graphics', 1, 1, 'Computer graphics principles and algorithms. Covers 2D/3D graphics, transformations, rendering techniques, lighting models, texture mapping, ray tracing, and graphics programming using modern APIs. Includes practical projects in graphics application development.', '2026-02-05 14:57:10'),
(22, 'CS 452-ML', 'Machine Learning', 1, 1, 'Introduction to machine learning theory and applications. Topics include supervised learning (regression, classification), unsupervised learning (clustering), neural networks, decision trees, feature engineering, model evaluation, and practical ML implementation using modern frameworks.', '2026-02-05 14:57:10'),
(23, 'CS 453', 'Robotics', 1, 1, 'Robotics systems design and autonomous agent programming. Covers robot kinematics, sensors and actuators, path planning, localization, mapping, computer vision for robotics, and robot control systems. Hands-on experience with robot programming and simulation.', '2026-02-05 14:57:10'),
(24, 'CS 454', 'Artificial Intelligence', 1, 1, 'Artificial intelligence concepts, techniques, and applications. Topics include search algorithms, knowledge representation, reasoning under uncertainty, expert systems, natural language processing, and AI ethics. Students implement AI algorithms and explore contemporary AI applications.', '2026-02-05 14:57:10'),
(25, 'CS 456', 'Algorithm Design and Analysis', 1, 1, 'Advanced algorithm design techniques and complexity analysis. Covers dynamic programming, greedy algorithms, divide-and-conquer, graph algorithms, NP-completeness, approximation algorithms, and randomized algorithms. Emphasizes problem-solving strategies and algorithm optimization.', '2026-02-05 14:57:10'),
(26, 'CS 457', 'Data Mining', 1, 1, 'Data mining techniques and knowledge discovery from large datasets. Topics include data preprocessing, association rules, classification algorithms, clustering methods, pattern recognition, text mining, and big data analytics. Practical applications using industry-standard data mining tools.', '2026-02-05 14:57:10'),
(27, 'SOAN 111', 'Leadership Seminar 1', 2, 1, 'Foundational leadership development course introducing key leadership theories, styles, and practices. Students explore personal leadership qualities, ethical leadership, teamwork, and effective communication. Includes self-assessment and leadership skill-building activities.', '2026-02-05 14:57:10'),
(28, 'SOAN 220', 'Embodied African Aesthetics Foundations', 2, 1, 'Introduction to African aesthetic traditions and embodied cultural expressions. Explores the philosophical and cultural foundations of African arts, performance, and creative practices. Examines how aesthetics shape identity, community, and cultural continuity in African contexts.', '2026-02-05 14:57:10'),
(29, 'SOAN 221', 'Leadership Seminar 2', 2, 1, 'Intermediate leadership development building on foundational concepts. Focuses on organizational leadership, conflict resolution, team dynamics, and leadership in diverse contexts. Students develop practical leadership skills through case studies and experiential learning.', '2026-02-05 14:57:10'),
(30, 'SOAN 225', 'Ghanaian Popular Culture', 2, 1, 'Examination of contemporary Ghanaian popular culture including music, film, fashion, media, and digital culture. Analyzes how popular culture reflects and shapes Ghanaian society, identity, and social change. Explores the intersection of tradition and modernity in cultural production.', '2026-02-05 14:57:10'),
(31, 'SOAN 227', 'Religion in Africa', 2, 1, 'Comprehensive survey of religious traditions and practices across Africa. Covers indigenous African religions, Christianity, Islam, and religious syncretism. Examines the role of religion in African societies, politics, and cultural identity. Explores contemporary religious movements and interfaith dynamics.', '2026-02-05 14:57:10'),
(32, 'SOAN 233', 'African Music and the Contemporary Art Music Scene', 2, 1, 'Study of African music traditions and contemporary art music scene. Traces historical development of African musical forms, instruments, and performance practices. Examines the influence of African music on global music genres and contemporary African artists shaping the international music landscape.', '2026-02-05 14:57:10'),
(33, 'SOAN 235', 'Embodied African Aesthetic Foundations: West African Traditional and Contemporary Dance Praxis', 2, 1, 'Advanced study and practice of West African traditional and contemporary dance forms. Combines theoretical understanding with practical training in various dance styles. Explores dance as cultural expression, storytelling medium, and contemporary artistic practice in West African contexts.', '2026-02-05 14:57:10'),
(34, 'SOAN 301', 'Introduction to Africana Studies: The Global Black Experience', 2, 1, 'Interdisciplinary introduction to Africana Studies examining the global Black experience across the African diaspora. Covers African and African diaspora history, culture, politics, and social movements. Analyzes themes of identity, resistance, creativity, and transnational Black solidarity.', '2026-02-05 14:57:10'),
(35, 'SOAN 311', 'Leadership Seminar 3', 2, 1, 'Advanced leadership seminar focusing on strategic leadership, organizational change, innovation, and leadership in complex environments. Students develop leadership philosophies and apply advanced leadership concepts to real-world challenges through projects and case analyses.', '2026-02-05 14:57:10'),
(36, 'SOAN 322', 'African Cultural Institutions', 2, 1, 'In-depth study of African cultural institutions and their role in society. Examines traditional and modern institutions including kinship systems, political structures, educational systems, economic organizations, and cultural preservation mechanisms. Analyzes institutional adaptation and transformation in contemporary Africa.', '2026-02-05 14:57:10'),
(37, 'SOAN 325', 'Research Methods', 2, 1, 'Comprehensive research methods course covering qualitative and quantitative research methodologies in social sciences. Topics include research design, data collection techniques, sampling, statistical analysis, ethnographic methods, and research ethics. Students design and conduct original research projects.', '2026-02-05 14:57:10'),
(38, 'SOAN 411', 'Leadership Seminar 4', 2, 1, 'Capstone leadership seminar integrating leadership theory and practice. Students reflect on their leadership development journey, create leadership action plans, and address contemporary leadership challenges. Emphasizes ethical leadership, social responsibility, and leading in a globalized world.', '2026-02-05 14:57:10'),
(39, 'POLS 221', 'African Philosophical Thought', 3, 1, 'Exploration of African philosophical traditions and thinkers. Examines major philosophical movements, indigenous knowledge systems, ethics, epistemology, and metaphysics in African thought. Analyzes contributions of African philosophers to global philosophical discourse and contemporary debates.', '2026-02-05 14:57:10'),
(40, 'POLS 231', 'Africa in International Settings: Africa Beyond Aid', 3, 1, 'Analysis of Africa\'s role in international relations beyond traditional aid dependency narratives. Examines African agency in global politics, economic partnerships, trade relations, regional integration, and Africa\'s engagement with international institutions. Explores alternative development models and South-South cooperation.', '2026-02-05 14:57:10'),
(41, 'POLS 234', 'Comparative Politics: Politics in Africa', 3, 1, 'Comparative analysis of political systems, institutions, and processes across African states. Examines democratization, authoritarian regimes, electoral systems, political parties, civil society, and political culture. Uses comparative framework to understand diversity and commonalities in African politics.', '2026-02-05 14:57:10'),
(42, 'POLS 322', 'China-Africa Relations', 3, 1, 'Comprehensive examination of political, economic, and cultural relations between China and African nations. Analyzes Chinese investment, trade, development assistance, and diplomatic engagement in Africa. Explores implications for African development, sovereignty, and global power dynamics.', '2026-02-05 14:57:10'),
(43, 'POLS 332', 'Governance in Africa', 3, 1, 'Study of governance structures, processes, and challenges in African contexts. Topics include democratic governance, good governance principles, accountability mechanisms, corruption, decentralization, public policy, and state-society relations. Examines governance reforms and capacity building in African states.', '2026-02-05 14:57:10'),
(44, 'ENGL 001', 'Writing, Public Speaking, and Multimedia Communications', 4, 1, 'Foundational communication skills course developing writing, public speaking, and multimedia communication competencies. Covers academic writing, rhetorical analysis, oral presentation techniques, visual communication, and digital literacy. Prepares students for effective communication in academic and professional contexts.', '2026-02-05 14:57:10'),
(45, 'ENGL 112', 'Written and Oral Communication', 4, 1, 'Integrated development of written and oral communication skills. Emphasizes argumentative writing, critical reading, research skills, and formal presentation. Students produce various written genres and deliver structured oral presentations with emphasis on clarity, coherence, and audience awareness.', '2026-02-05 14:57:10'),
(46, 'ENGL 113', 'Text and Meaning', 4, 1, 'Literary analysis and interpretation course examining how meaning is constructed in texts. Covers literary theory, close reading techniques, textual analysis, and interpretation strategies. Students analyze various literary forms and develop critical thinking skills for engaging with complex texts.', '2026-02-05 14:57:10'),
(47, 'ENGL 215', 'African Literature', 4, 1, 'Survey of African literature across genres, periods, and regions. Examines major African writers, literary movements, and themes including colonialism, independence, identity, and modernity. Analyzes oral traditions, post-colonial literature, and contemporary African writing in global context.', '2026-02-05 14:57:10'),
(48, 'ENGL 231', 'African Literature & Film (Women Writing Africa: Female Writers in Modern African Literatures and Films)', 4, 1, 'Focused study of African women writers and filmmakers in modern African literatures and cinema. Examines female perspectives on African experiences, gender issues, feminism, and women\'s agency. Analyzes literary and cinematic representations of women and contributions of female artists to African cultural production.', '2026-02-05 14:57:10'),
(49, 'FRENC 111', 'Introductory French 1', 5, 1, 'Beginning French language course for students with no prior French experience. Develops basic skills in reading, writing, speaking, and listening. Introduces fundamental grammar, vocabulary, and cultural aspects of French-speaking world. Emphasizes communicative competence in everyday situations.', '2026-02-05 14:57:10'),
(50, 'FRENC 122', 'Professional French 1', 5, 1, 'French language course focusing on professional and business contexts. Develops vocabulary and communication skills for workplace settings including business correspondence, presentations, meetings, and negotiations. Integrates cultural aspects of professional environments in Francophone regions.', '2026-02-05 14:57:10'),
(51, 'FRENC 123', 'Introductory French 2', 5, 1, 'Continuation of introductory French building on foundational skills. Expands vocabulary, introduces more complex grammatical structures, and develops intermediate reading, writing, speaking, and listening abilities. Deepens cultural understanding of Francophone societies and customs.', '2026-02-05 14:57:10'),
(52, 'FRENC 214', 'Professional French 2', 5, 1, 'Advanced professional French developing sophisticated communication skills for business and professional environments. Covers advanced business writing, formal presentations, intercultural communication, and specialized professional vocabulary. Prepares students for French-language professional careers.', '2026-02-05 14:57:10'),
(53, 'FRENC 315', 'Francophone Literature, Films and Creative Writing', 5, 1, 'Exploration of Francophone literature, films, and creative writing from French-speaking regions worldwide. Analyzes literary and cinematic works from Africa, Caribbean, Europe, and other Francophone areas. Students engage in creative writing exercises and critical analysis of Francophone cultural productions.', '2026-02-05 14:57:10'),
(54, 'FYE 001', 'How to Communicate Like a Leader (Optional)', 6, 1, 'Optional workshop developing communication skills essential for leadership roles. Covers verbal and non-verbal communication, active listening, persuasive speaking, professional presence, and effective messaging. Designed to help first-year students build confidence in leadership communication.', '2026-02-05 14:57:10'),
(55, 'FYE 002', 'English Bridge (Optional)', 6, 1, 'Optional intensive English language preparation course for students needing additional support. Focuses on academic English skills including reading comprehension, academic writing, listening, and speaking. Bridges gap between current English proficiency and university-level requirements.', '2026-02-05 14:57:10'),
(56, 'GEN 101', 'Neo-Traditional African Dance Forms', 7, 1, 'Practical exploration of neo-traditional African dance forms blending traditional movements with contemporary choreography. Students learn various dance styles, understand cultural contexts, and develop performance skills. Examines how traditional dances evolve and remain relevant in modern African societies.', '2026-02-05 14:57:10'),
(57, 'GEN 102', 'African FolkTales: How Stories Shape Us', 7, 1, 'Study of African folktales and storytelling traditions exploring how narratives shape identity, values, and cultural understanding. Analyzes storytelling techniques, moral lessons, cultural symbolism, and the role of stories in transmitting knowledge across generations. Students engage with oral and written narrative traditions.', '2026-02-05 14:57:10'),
(58, 'GEN 201', 'Approaches to African Development', 7, 1, 'Examination of various approaches to African development including economic, social, political, and sustainable development paradigms. Analyzes development theories, challenges, successes, and alternative models. Explores African-centered development strategies and the role of indigenous knowledge in development planning.', '2026-02-05 14:57:10'),
(59, 'GEN 202', 'Quantitative Estimation & Data Viz', 7, 1, 'Introduction to quantitative reasoning and data visualization techniques. Covers statistical concepts, data analysis methods, estimation techniques, and visual representation of data. Students learn to interpret quantitative information critically and communicate findings effectively using various visualization tools.', '2026-02-05 14:57:10'),
(60, 'GEN 203', 'Information Technology', 7, 1, 'Foundational information technology course covering essential IT concepts and skills. Topics include computer systems, operating systems, productivity software, internet technologies, cybersecurity basics, and digital literacy. Prepares students to effectively use technology in academic and professional settings.', '2026-02-05 14:57:10'),
(61, 'GEN 204', 'Principles of Design', 7, 1, 'Introduction to fundamental design principles applicable across various disciplines. Covers elements of design, color theory, composition, typography, visual hierarchy, and user-centered design thinking. Students apply design principles to create effective visual communications and solve design problems.', '2026-02-05 14:57:10'),
(62, 'GEN 205', 'Math Bridge (Optional)', 7, 1, 'Optional mathematics preparation course for students needing additional support in foundational math concepts. Reviews algebra, functions, geometry, and basic calculus concepts. Bridges gap between current mathematics proficiency and university-level mathematics requirements across disciplines.', '2026-02-05 14:57:10'),
(63, 'GEN 206', 'Entrepreneurship Universe', 7, 1, 'Introduction to entrepreneurship covering the entrepreneurial mindset, opportunity recognition, business model development, innovation, and startup fundamentals. Students explore entrepreneurial careers, social entrepreneurship, and pathways to creating ventures. Emphasizes creativity, problem-solving, and entrepreneurial thinking.', '2026-02-05 14:57:10'),
(64, 'GEN 207', 'Math Bridge for Engineers (Optional)', 7, 1, 'Optional intensive mathematics preparation specifically designed for engineering students. Covers advanced algebra, trigonometry, pre-calculus, and introductory calculus concepts essential for engineering coursework. Provides strong mathematical foundation for engineering disciplines.', '2026-02-05 14:57:10');

-- --------------------------------------------------------

--
-- Table structure for table `course_prerequisites`
--

CREATE TABLE `course_prerequisites` (
  `prereq_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `prerequisite_course_id` int(11) NOT NULL,
  `prerequisite_type` varchar(20) DEFAULT 'required',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_prerequisites`
--

INSERT INTO `course_prerequisites` (`prereq_id`, `course_id`, `prerequisite_course_id`, `prerequisite_type`, `created_at`) VALUES
(1, 2, 1, 'required', '2026-02-18 20:13:26'),
(2, 3, 2, 'required', '2026-02-18 20:13:26'),
(3, 5, 3, 'required', '2026-02-18 20:13:26'),
(4, 6, 3, 'required', '2026-02-18 20:13:26'),
(5, 7, 3, 'required', '2026-02-18 20:13:26'),
(6, 8, 3, 'required', '2026-02-18 20:13:26'),
(7, 9, 3, 'required', '2026-02-18 20:13:26'),
(8, 10, 5, 'required', '2026-02-18 20:13:26'),
(9, 10, 6, 'or', '2026-02-18 20:13:26'),
(10, 13, 5, 'required', '2026-02-18 20:13:26'),
(11, 13, 6, 'or', '2026-02-18 20:13:26'),
(12, 14, 5, 'required', '2026-02-18 20:13:26'),
(13, 14, 6, 'or', '2026-02-18 20:13:26'),
(14, 14, 4, 'required', '2026-02-18 20:13:26'),
(15, 15, 8, 'required', '2026-02-18 20:13:26'),
(16, 16, 9, 'required', '2026-02-18 20:13:26'),
(17, 17, 9, 'required', '2026-02-18 20:13:26'),
(18, 17, 14, 'required', '2026-02-18 20:13:26'),
(19, 18, 10, 'required', '2026-02-18 20:13:26'),
(20, 18, 8, 'required', '2026-02-18 20:13:26'),
(21, 19, 5, 'required', '2026-02-18 20:13:26'),
(22, 19, 6, 'or', '2026-02-18 20:13:26'),
(23, 20, 16, 'required', '2026-02-18 20:13:26'),
(24, 21, 14, 'required', '2026-02-18 20:13:26'),
(25, 22, 14, 'required', '2026-02-18 20:13:26'),
(26, 22, 4, 'required', '2026-02-18 20:13:26'),
(27, 23, 9, 'required', '2026-02-18 20:13:26'),
(28, 23, 14, 'required', '2026-02-18 20:13:26'),
(29, 24, 14, 'required', '2026-02-18 20:13:26'),
(30, 24, 4, 'required', '2026-02-18 20:13:26'),
(31, 25, 14, 'required', '2026-02-18 20:13:26'),
(32, 26, 14, 'required', '2026-02-18 20:13:26'),
(33, 26, 8, 'required', '2026-02-18 20:13:26'),
(34, 29, 27, 'required', '2026-02-18 20:13:26'),
(35, 33, 28, 'required', '2026-02-18 20:13:26'),
(36, 35, 29, 'required', '2026-02-18 20:13:26'),
(37, 36, 34, 'required', '2026-02-18 20:13:26'),
(38, 38, 35, 'required', '2026-02-18 20:13:26'),
(39, 42, 40, 'required', '2026-02-18 20:13:26'),
(40, 42, 41, 'or', '2026-02-18 20:13:26'),
(41, 43, 41, 'required', '2026-02-18 20:13:26'),
(42, 45, 44, 'required', '2026-02-18 20:13:26'),
(43, 46, 44, 'required', '2026-02-18 20:13:26'),
(44, 47, 45, 'required', '2026-02-18 20:13:26'),
(45, 47, 46, 'or', '2026-02-18 20:13:26'),
(46, 48, 45, 'required', '2026-02-18 20:13:26'),
(47, 48, 46, 'or', '2026-02-18 20:13:26'),
(48, 50, 49, 'required', '2026-02-18 20:13:26'),
(49, 51, 49, 'required', '2026-02-18 20:13:26'),
(50, 52, 50, 'required', '2026-02-18 20:13:26'),
(51, 53, 51, 'required', '2026-02-18 20:13:26'),
(52, 53, 52, 'or', '2026-02-18 20:13:26');

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `registration_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_email` varchar(255) NOT NULL,
  `mycamu_email` varchar(255) NOT NULL,
  `mycamu_password_encrypted` text NOT NULL,
  `registration_status` enum('pending','verified','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `verification_token` varchar(255) DEFAULT NULL,
  `verification_token_expires` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `timetable_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`timetable_options`)),
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_registrations`
--

INSERT INTO `course_registrations` (`registration_id`, `student_id`, `student_email`, `mycamu_email`, `mycamu_password_encrypted`, `registration_status`, `verification_token`, `verification_token_expires`, `email_verified`, `email_verified_at`, `timetable_options`, `submitted_at`, `processed_at`, `error_message`, `created_at`, `updated_at`) VALUES
(2, 17, 'lia.bawa@ashesi.edu.gh', 'yenma.bawa@ashesi.edu.gh', 'T50u0uM4IjDMj+ZtEuVboTl3OEZaS1YvcmVGaFRiN3g3a0lITlE9PQ==', 'pending', 'bbfd9b9856c2fd841e22b0602e9b8e08c99566f35f31b0f7269b052860d69740', '2026-02-08 21:21:30', 0, NULL, '{\"option1\":{\"courses\":[\"CS 424\"],\"sections\":{\"CS 424\":0}},\"option2\":{\"courses\":[\"SOAN 301\"],\"sections\":{\"SOAN 301\":1}},\"option3\":{\"courses\":[\"GEN 203\"],\"sections\":{\"GEN 203\":0}}}', '2026-02-07 21:21:30', NULL, NULL, '2026-02-07 21:21:30', '2026-02-07 21:21:30'),
(3, 17, 'lia.bawa@ashesi.edu.gh', 'yenma.bawa@ashesi.edu.gh', 'swRFUPPGAGzT7GbVxSflAnBBNzkyaTl4VTIzVVpFNXgrZUtuU3c9PQ==', 'pending', 'f0f86372cd2a683f8f72dcb1ed52a7a960ce01fd4cd69d7e53c63e3ffa4663dd', '2026-02-08 21:22:53', 0, NULL, '{\"option1\":{\"courses\":[\"POLS 231\"],\"sections\":{\"POLS 231\":0}},\"option2\":{\"courses\":[\"ENGL 001\"],\"sections\":{\"ENGL 001\":1}},\"option3\":{\"courses\":[\"ENGL 001\"],\"sections\":{\"ENGL 001\":0}}}', '2026-02-07 21:22:53', NULL, NULL, '2026-02-07 21:22:53', '2026-02-07 21:22:53'),
(4, 20, 'bennetta.avaga@ashesi.edu.gh', 'yenma.bawa@ashesi.edu.gh', 'WGDfZrFDKB7U5SNRE0H1EjBFS1B0bjNvWWs4ek9CeGFrTlhlaFE9PQ==', 'pending', '9d7dc35a5086ad3c56f787b1660e7af88ac52882dbfb34f3a841554a6557cafe', '2026-02-08 21:28:14', 0, NULL, '{\"option1\":{\"courses\":[\"CS 400\"],\"sections\":{\"CS 400\":0}},\"option2\":{\"courses\":[\"CS 221\"],\"sections\":{\"CS 221\":0}},\"option3\":{\"courses\":[\"FRENC 111\"],\"sections\":{\"FRENC 111\":0}}}', '2026-02-07 21:28:14', NULL, NULL, '2026-02-07 21:28:14', '2026-02-07 21:28:14'),
(5, 21, 'abambirebawayenma@gmail.com', 'yenma.bawa@ashesi.edu.gh', 'lMgqcVa1A+k6CSiZLB2yp1pwQWhmVEdyM0l3cWdhMWFVUlVYL2c9PQ==', 'pending', '71956ebe1162d9c03f258db975a5dc712e1b3b5dc3520c8ea1cfa709e3801d39', '2026-02-08 21:35:24', 0, NULL, '{\"option1\":{\"courses\":[\"CS 221\"],\"sections\":{\"CS 221\":0}},\"option2\":{\"courses\":[\"CS 400\"],\"sections\":{\"CS 400\":1}},\"option3\":{\"courses\":[\"GEN 202\"],\"sections\":{\"GEN 202\":0}}}', '2026-02-07 21:35:24', NULL, NULL, '2026-02-07 21:35:24', '2026-02-07 21:35:24'),
(6, 22, 'nadia.buari@ashesi.edu.gh', 'nadia.buari@ashesi.edu.gh', 'Y+QPSKkfzju6n/Bce5HZAENKV0FNQ0liTWVXM2h6VkJsWGVOUlE9PQ==', 'pending', '021695e10fe0abbdb20445de175c09da0f15b5e4a8cbfe75cff3433ae61f24b4', '2026-02-08 21:41:34', 0, NULL, '{\"option1\":{\"courses\":[\"CS 221\"],\"sections\":{\"CS 221\":1}},\"option2\":{\"courses\":[\"FYE 002\",\"SOAN 325\"],\"sections\":{\"FYE 002\":0,\"SOAN 325\":1}},\"option3\":{\"courses\":[\"FYE 002\"],\"sections\":{\"FYE 002\":1}}}', '2026-02-07 21:41:34', NULL, NULL, '2026-02-07 21:41:34', '2026-02-07 21:41:34'),
(7, 25, 'zayabawa2017@gmail.com', 'yenma.bawa@ashesi.edu.gh', 'QnTWNRkJa2Ctv7K0LamkglNKblFMYXJTNkNmZjlTRzZNYjI0V2c9PQ==', 'pending', '594ca0713769baa3a662968b776fecb1325ea58fcae525e8268013572c710ecd', '2026-02-19 19:33:14', 0, NULL, '{\"option1\":{\"courses\":[\"CS 400\"],\"sections\":{\"CS 400\":1}},\"option2\":{\"courses\":[\"FRENC 111\"],\"sections\":{\"FRENC 111\":0}},\"option3\":{\"courses\":[\"FYE 001\"],\"sections\":{\"FYE 001\":0}}}', '2026-02-18 19:33:14', NULL, NULL, '2026-02-18 19:33:14', '2026-02-18 19:33:14');

-- --------------------------------------------------------

--
-- Table structure for table `course_sections`
--

CREATE TABLE `course_sections` (
  `section_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `section_number` tinyint(4) NOT NULL DEFAULT 1,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `slot_id` int(11) NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_sections`
--

INSERT INTO `course_sections` (`section_id`, `course_id`, `section_number`, `day_of_week`, `slot_id`, `room`, `instructor`, `capacity`, `created_at`) VALUES
(1, 1, 1, 'Monday', 1, NULL, NULL, 35, '2026-02-18 20:18:11'),
(2, 1, 2, 'Tuesday', 2, NULL, NULL, 35, '2026-02-18 20:18:11'),
(3, 1, 3, 'Wednesday', 3, NULL, NULL, 35, '2026-02-18 20:18:11'),
(4, 1, 4, 'Thursday', 4, NULL, NULL, 35, '2026-02-18 20:18:11'),
(5, 2, 1, 'Monday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(6, 2, 2, 'Wednesday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(7, 3, 1, 'Tuesday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(8, 3, 2, 'Thursday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(9, 3, 3, 'Friday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(10, 4, 1, 'Monday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(11, 4, 2, 'Wednesday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(12, 5, 1, 'Tuesday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(13, 5, 2, 'Thursday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(14, 6, 1, 'Friday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(15, 7, 1, 'Wednesday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(16, 8, 1, 'Monday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(17, 8, 2, 'Thursday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(18, 9, 1, 'Tuesday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(19, 9, 2, 'Friday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(20, 10, 1, 'Monday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(21, 10, 2, 'Wednesday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(22, 10, 3, 'Friday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(23, 11, 1, 'Friday', 4, NULL, NULL, 20, '2026-02-18 20:18:11'),
(24, 12, 1, 'Thursday', 5, NULL, NULL, 20, '2026-02-18 20:18:11'),
(25, 13, 1, 'Tuesday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(26, 13, 2, 'Thursday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(27, 14, 1, 'Monday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(28, 14, 2, 'Wednesday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(29, 15, 1, 'Tuesday', 5, NULL, NULL, 25, '2026-02-18 20:18:11'),
(30, 16, 1, 'Monday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(31, 16, 2, 'Thursday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(32, 17, 1, 'Wednesday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(33, 18, 1, 'Friday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(34, 19, 1, 'Tuesday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(35, 19, 2, 'Friday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(36, 20, 1, 'Thursday', 6, NULL, NULL, 25, '2026-02-18 20:18:11'),
(37, 21, 1, 'Monday', 6, NULL, NULL, 25, '2026-02-18 20:18:11'),
(38, 22, 1, 'Tuesday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(39, 22, 2, 'Thursday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(40, 23, 1, 'Wednesday', 6, NULL, NULL, 25, '2026-02-18 20:18:11'),
(41, 24, 1, 'Monday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(42, 24, 2, 'Friday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(43, 25, 1, 'Tuesday', 6, NULL, NULL, 25, '2026-02-18 20:18:11'),
(44, 26, 1, 'Wednesday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(45, 27, 1, 'Monday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(46, 27, 2, 'Tuesday', 4, NULL, NULL, 25, '2026-02-18 20:18:11'),
(47, 27, 3, 'Wednesday', 5, NULL, NULL, 25, '2026-02-18 20:18:11'),
(48, 27, 4, 'Friday', 1, NULL, NULL, 25, '2026-02-18 20:18:11'),
(49, 28, 1, 'Thursday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(50, 29, 1, 'Monday', 4, NULL, NULL, 25, '2026-02-18 20:18:11'),
(51, 29, 2, 'Wednesday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(52, 30, 1, 'Tuesday', 5, NULL, NULL, 35, '2026-02-18 20:18:11'),
(53, 31, 1, 'Thursday', 2, NULL, NULL, 35, '2026-02-18 20:18:11'),
(54, 32, 1, 'Friday', 3, NULL, NULL, 35, '2026-02-18 20:18:11'),
(55, 33, 1, 'Wednesday', 1, NULL, NULL, 25, '2026-02-18 20:18:11'),
(56, 34, 1, 'Monday', 5, NULL, NULL, 35, '2026-02-18 20:18:11'),
(57, 34, 2, 'Thursday', 3, NULL, NULL, 35, '2026-02-18 20:18:11'),
(58, 35, 1, 'Tuesday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(59, 35, 2, 'Friday', 4, NULL, NULL, 25, '2026-02-18 20:18:11'),
(60, 36, 1, 'Wednesday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(61, 37, 1, 'Monday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(62, 37, 2, 'Thursday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(63, 38, 1, 'Tuesday', 1, NULL, NULL, 25, '2026-02-18 20:18:11'),
(64, 38, 2, 'Friday', 5, NULL, NULL, 25, '2026-02-18 20:18:11'),
(65, 39, 1, 'Monday', 2, NULL, NULL, 35, '2026-02-18 20:18:11'),
(66, 40, 1, 'Wednesday', 4, NULL, NULL, 35, '2026-02-18 20:18:11'),
(67, 41, 1, 'Tuesday', 3, NULL, NULL, 35, '2026-02-18 20:18:11'),
(68, 41, 2, 'Thursday', 1, NULL, NULL, 35, '2026-02-18 20:18:11'),
(69, 42, 1, 'Friday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(70, 43, 1, 'Monday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(71, 44, 1, 'Monday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(72, 44, 2, 'Tuesday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(73, 44, 3, 'Thursday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(74, 44, 4, 'Friday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(75, 45, 1, 'Monday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(76, 45, 2, 'Wednesday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(77, 45, 3, 'Friday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(78, 46, 1, 'Tuesday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(79, 46, 2, 'Thursday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(80, 46, 3, 'Friday', 1, NULL, NULL, 30, '2026-02-18 20:18:11'),
(81, 47, 1, 'Wednesday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(82, 48, 1, 'Thursday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(83, 49, 1, 'Monday', 4, NULL, NULL, 25, '2026-02-18 20:18:11'),
(84, 49, 2, 'Wednesday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(85, 49, 3, 'Friday', 3, NULL, NULL, 25, '2026-02-18 20:18:11'),
(86, 50, 1, 'Tuesday', 4, NULL, NULL, 25, '2026-02-18 20:18:11'),
(87, 50, 2, 'Thursday', 2, NULL, NULL, 25, '2026-02-18 20:18:11'),
(88, 51, 1, 'Monday', 5, NULL, NULL, 25, '2026-02-18 20:18:11'),
(89, 51, 2, 'Wednesday', 3, NULL, NULL, 25, '2026-02-18 20:18:11'),
(90, 52, 1, 'Friday', 5, NULL, NULL, 25, '2026-02-18 20:18:11'),
(91, 53, 1, 'Tuesday', 6, NULL, NULL, 25, '2026-02-18 20:18:11'),
(92, 54, 1, 'Wednesday', 6, NULL, NULL, 40, '2026-02-18 20:18:11'),
(93, 54, 2, 'Friday', 6, NULL, NULL, 40, '2026-02-18 20:18:11'),
(94, 55, 1, 'Thursday', 6, NULL, NULL, 30, '2026-02-18 20:18:11'),
(95, 56, 1, 'Tuesday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(96, 57, 1, 'Monday', 6, NULL, NULL, 35, '2026-02-18 20:18:11'),
(97, 58, 1, 'Tuesday', 2, NULL, NULL, 35, '2026-02-18 20:18:11'),
(98, 58, 2, 'Thursday', 4, NULL, NULL, 35, '2026-02-18 20:18:11'),
(99, 59, 1, 'Monday', 2, NULL, NULL, 30, '2026-02-18 20:18:11'),
(100, 59, 2, 'Wednesday', 4, NULL, NULL, 30, '2026-02-18 20:18:11'),
(101, 60, 1, 'Tuesday', 1, NULL, NULL, 35, '2026-02-18 20:18:11'),
(102, 60, 2, 'Thursday', 3, NULL, NULL, 35, '2026-02-18 20:18:11'),
(103, 60, 3, 'Friday', 2, NULL, NULL, 35, '2026-02-18 20:18:11'),
(104, 61, 1, 'Monday', 3, NULL, NULL, 30, '2026-02-18 20:18:11'),
(105, 61, 2, 'Friday', 5, NULL, NULL, 30, '2026-02-18 20:18:11'),
(106, 62, 1, 'Wednesday', 6, NULL, NULL, 30, '2026-02-18 20:18:11'),
(107, 63, 1, 'Tuesday', 3, NULL, NULL, 35, '2026-02-18 20:18:11'),
(108, 63, 2, 'Thursday', 1, NULL, NULL, 35, '2026-02-18 20:18:11'),
(109, 64, 1, 'Monday', 6, NULL, NULL, 30, '2026-02-18 20:18:11');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `dept_code` varchar(10) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `dept_code`, `dept_name`, `created_at`) VALUES
(1, 'CS', 'Computer Science', '2026-02-05 14:57:10'),
(2, 'SOAN', 'Sociology and Anthropology', '2026-02-05 14:57:10'),
(3, 'POLS', 'Political Science', '2026-02-05 14:57:10'),
(4, 'ENGL', 'English', '2026-02-05 14:57:10'),
(5, 'FRENC', 'French', '2026-02-05 14:57:10'),
(6, 'FYE', 'First Year Experience', '2026-02-05 14:57:10'),
(7, 'GEN', 'General Studies', '2026-02-05 14:57:10');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_questionnaires`
--

CREATE TABLE `student_questionnaires` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program` varchar(255) NOT NULL,
  `year` varchar(10) NOT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `enrollment_status` varchar(50) NOT NULL,
  `time_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`time_preferences`)),
  `day_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`day_preferences`)),
  `course_load` varchar(20) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `accommodations_needed` tinyint(1) DEFAULT 0,
  `accommodation_details` text DEFAULT NULL,
  `referral` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `slot_id` int(11) NOT NULL,
  `slot_label` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`slot_id`, `slot_label`, `start_time`, `end_time`) VALUES
(1, '8:00 - 9:30', '08:00:00', '09:30:00'),
(2, '9:45 - 11:15', '09:45:00', '11:15:00'),
(3, '11:30 - 13:00', '11:30:00', '13:00:00'),
(4, '13:15 - 14:45', '13:15:00', '14:45:00'),
(5, '15:00 - 16:30', '15:00:00', '16:30:00'),
(6, '16:45 - 17:45', '16:45:00', '17:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL COMMENT 'NULL for admin users',
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `remember_token` varchar(100) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `student_id`, `password_hash`, `role`, `remember_token`, `email_verified_at`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, 'John Doe', 'john.doe@university.edu', 'STU001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', NULL, NULL, '2026-02-03 22:04:00', '2026-02-03 22:04:00', NULL, 1),
(2, 'Admin User', 'admin@university.edu', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL, '2026-02-03 22:04:00', '2026-02-03 22:04:00', NULL, 1),
(4, 'Yenma Abambire Bawa', 'yenma.bawa@ashesi.edu.gh', '', '$2y$10$bldPZPydtze9H5yaO3aabO.1KwjakTlSuJd98VSsxvSxwDbXk.HVS', 'admin', '8c892fbbe4c4d2f87a0e06f3ec568eec85040b27d41ac70d7a289c638ff8c6ec', NULL, '2026-02-03 22:05:13', '2026-02-09 10:40:48', '2026-02-09 10:40:48', 1),
(5, 'Yenma Abambire Bawa', 'yenma.ada@ashesi.edu.gh', '29692026', '$2y$10$yOXUH2aDSA1POu7ETfJwu.EXWD6nemCZ6Meeh7kXz0wHTOdDuP.Ai', 'student', NULL, NULL, '2026-02-03 22:06:39', '2026-02-03 22:06:39', NULL, 1),
(7, 'Ama Apo', 'ama.ampo@ashesi.edu.gh', '123422026', '$2y$10$k4.Ee4EmErjh8remgRwbPePQA1CkyEDq2Ho35J.KS08S00fW.IpIW', 'student', NULL, NULL, '2026-02-03 22:18:02', '2026-02-03 22:18:02', NULL, 1),
(8, 'Naana Go', 'naana.go@ashesi.edu.gh', '23452026', '$2y$10$TRSjJu/AzwLld2FQqMgQIOcwUwRXkd5V0v0xgJ.xyqa.UDhvdWxjW', 'student', NULL, NULL, '2026-02-03 22:21:52', '2026-02-03 22:21:52', NULL, 1),
(9, 'Naana Go', 'naango@ashesi.edu.gh', '23452026', '$2y$10$dpMc8Gmr27eWn2JhZ1faROOMIFRuGq4mLlv8J1L4/e0.l2ALPB6bO', 'student', NULL, NULL, '2026-02-03 22:29:29', '2026-02-03 23:02:21', '2026-02-03 23:02:21', 1),
(10, 'Yenma Abambire Bawa', 'naangyo@ashesi.edu.gh', '23952026', '$2y$10$Y2whunyDgk1GUQGmLCZoxOu4Ogb8Kg1C4vgCFkKH.LOYOHVk9nqLe', 'student', NULL, NULL, '2026-02-03 23:03:26', '2026-02-03 23:03:26', NULL, 1),
(11, 'Nsoma Abambire Bawa', 'nsoma.bawa@ashesi.edu.gh', '20082026', '$2y$10$1WJF.n/kU.yXTbTvpi1CWu0Ibi8SK1WrFOoIzn1Q.pMkylgoD9/Hu', 'student', NULL, NULL, '2026-02-04 12:29:01', '2026-02-04 12:29:01', NULL, 1),
(12, 'Nsoma Abambire Bawa', 'nab@ashesi.edu.gh', '19072026', '$2y$10$26/nSh21VfA.mTqcn4ISwOWbXY82PhuPsXXQUL5lSHCpk/xOY/ZEa', 'student', '07cd33fab255794dad4ea4861b988d31487ab8b465c82ca8b49150d3bfbff5aa', NULL, '2026-02-04 12:35:07', '2026-02-04 13:15:53', '2026-02-04 13:15:53', 1),
(13, 'Joel Doe', 'joel@ashesi.edu.gh', '', '$2y$10$Z1qL6mBJs4UyB4OFzS4TfOHf1s4inone/xhiBKmfXM2FZ9MdLxPp2', 'admin', '2fe7da270a398ddc18b238e7ce610b908f04c46683e57b0705ae9ed057ee3480', NULL, '2026-02-04 12:45:48', '2026-02-04 12:47:02', '2026-02-04 12:47:02', 1),
(14, 'Bennett Caulley', 'benny@ashesi.edu.gh', '12342024', '$2y$10$YuRmcTbYySFiSqlRHpnMFO5ZtIw5mIGNmXei3aimtta15VVkAuSo6', 'student', NULL, NULL, '2026-02-05 15:16:58', '2026-02-05 15:16:58', NULL, 1),
(15, 'Nsoma Abambire Bawa', 'nabo@ashesi.edu.gh', '20782026', '$2y$10$/yCYNf13nR4IQZkjT1BW1OSSAqoeAu/G7KXMxsUzU.vkN8O3Snr0.', 'student', NULL, NULL, '2026-02-05 15:18:19', '2026-02-05 15:18:19', NULL, 1),
(16, 'Nsoma Abambire Bawa', 'naboy@ashesi.edu.gh', '26782026', '$2y$10$kC5DHYDldrq3YTqK/jvFCOvtd8Y9y4p1.ArRS5DVm1AaxC3JXkT7S', 'student', NULL, NULL, '2026-02-05 15:23:38', '2026-02-05 15:23:38', NULL, 1),
(17, 'Yenmalia Bawa', 'lia.bawa@ashesi.edu.gh', '09872026', '$2y$10$ASPYyszs8z6efQfnH.u72e3VyJs3zresqiQ8FZi46Uqy4VIQdKF5O', 'student', '897419ee2ff52ed8cfc9b3633a0ab9b651bd505f5bf6c7af461449a0d838b588', NULL, '2026-02-05 16:00:04', '2026-02-07 20:05:41', '2026-02-07 20:05:41', 1),
(18, 'Sophia Mathew', 'sophie.matt@ashesi.edu.gh', '56742026', '$2y$10$y06a04K5Or/7GkuQ/zuq3uiyiSQpxhcDFa94Ca3ZwGqGYd9Yw8MVW', 'student', '661a2354f55690fb2b64a10d43d7afa7ceb082eb25edbd3d109a352b64e2222e', NULL, '2026-02-05 16:29:02', '2026-02-06 12:00:44', '2026-02-06 12:00:44', 1),
(19, 'John Didi', 'john.didi@ashesi.edu.gh', '11112025', '$2y$10$aPkZtu/db9IuXr0kOcbhJ.YOjkRjGyVQWuTnP2Ns64lYSaa7YTqoC', 'student', NULL, NULL, '2026-02-07 18:07:46', '2026-02-07 18:07:46', NULL, 1),
(20, 'Bennetta Avaga', 'bennetta.avaga@ashesi.edu.gh', '12342026', '$2y$10$AOuUVk0iD.ri/XpHI3OQaeSktCnzZSx2Fjg/glMEJbOwefCoyiEzS', 'student', NULL, NULL, '2026-02-07 20:25:59', '2026-02-07 20:25:59', NULL, 1),
(21, 'Yenma Abambire Bawa', 'abambirebawayenma@gmail.com', '19082026', '$2y$10$vMV4LZ5oY1pceiwBmcxgce3yo1udaKJF2JmsSl16v78Lv2DcaLjYC', 'student', NULL, NULL, '2026-02-07 20:33:28', '2026-02-07 20:33:28', NULL, 1),
(22, 'Nadia Buari', 'nadia.buari@ashesi.edu.gh', '08762026', '$2y$10$KjA3SJVAjzOoopDhb5DYAO1sb9MqSLOKZJ0V.u6hM2ssgMd9lTZ8i', 'student', NULL, NULL, '2026-02-07 20:38:49', '2026-02-07 20:38:49', NULL, 1),
(24, 'joel ackam', 'joel.ackam@ashesi.gh', '79442026', '$2y$10$Wzrzi7cBo60WrqYTRk3zj..3U7kLxMSUJPxgSH6GTLD6DxLS6TTLq', 'student', NULL, NULL, '2026-02-09 10:31:35', '2026-02-09 10:31:35', NULL, 1),
(25, 'Zaya Bawa', 'zayabawa2017@gmail.com', '12342029', '$2y$10$JoUbCfrY3tqExFQKhZPsUuLuq8AOFOX.m0SJh2TsLCLvb7G5gZoMK', 'student', NULL, NULL, '2026-02-18 18:30:35', '2026-02-18 18:30:35', NULL, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_courses_with_dept`
-- (See below for the actual view)
--
CREATE TABLE `v_courses_with_dept` (
`course_id` int(11)
,`course_code` varchar(20)
,`course_name` varchar(200)
,`dept_name` varchar(100)
,`credits` int(11)
,`course_description` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_course_prerequisites`
-- (See below for the actual view)
--
CREATE TABLE `v_course_prerequisites` (
`course` varchar(20)
,`course_name` varchar(200)
,`prerequisite` varchar(20)
,`prerequisite_name` varchar(200)
,`prerequisite_type` varchar(20)
);

-- --------------------------------------------------------

--
-- Structure for view `v_courses_with_dept`
--
DROP TABLE IF EXISTS `v_courses_with_dept`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_courses_with_dept`  AS SELECT `c`.`course_id` AS `course_id`, `c`.`course_code` AS `course_code`, `c`.`course_name` AS `course_name`, `d`.`dept_name` AS `dept_name`, `c`.`credits` AS `credits`, `c`.`course_description` AS `course_description` FROM (`courses` `c` join `departments` `d` on(`c`.`dept_id` = `d`.`dept_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_course_prerequisites`
--
DROP TABLE IF EXISTS `v_course_prerequisites`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_course_prerequisites`  AS SELECT `c1`.`course_code` AS `course`, `c1`.`course_name` AS `course_name`, `c2`.`course_code` AS `prerequisite`, `c2`.`course_name` AS `prerequisite_name`, `cp`.`prerequisite_type` AS `prerequisite_type` FROM ((`course_prerequisites` `cp` join `courses` `c1` on(`cp`.`course_id` = `c1`.`course_id`)) join `courses` `c2` on(`cp`.`prerequisite_course_id` = `c2`.`course_id`)) ORDER BY `c1`.`course_code` ASC, `cp`.`prerequisite_type` ASC, `c2`.`course_code` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  ADD PRIMARY KEY (`prereq_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `prerequisite_course_id` (`prerequisite_course_id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_student_email` (`student_email`),
  ADD KEY `idx_registration_status` (`registration_status`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `unique_section` (`course_id`,`section_number`,`day_of_week`),
  ADD KEY `fk_section_course` (`course_id`),
  ADD KEY `fk_section_slot` (`slot_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`),
  ADD UNIQUE KEY `dept_code` (`dept_code`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_attempted_at` (`attempted_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- Indexes for table `student_questionnaires`
--
ALTER TABLE `student_questionnaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_program` (`program`),
  ADD KEY `idx_year` (`year`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`slot_id`),
  ADD UNIQUE KEY `slot_label` (`slot_label`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_remember_token` (`remember_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  MODIFY `prereq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course_sections`
--
ALTER TABLE `course_sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_questionnaires`
--
ALTER TABLE `student_questionnaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`);

--
-- Constraints for table `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  ADD CONSTRAINT `course_prerequisites_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `course_prerequisites_ibfk_2` FOREIGN KEY (`prerequisite_course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `course_sections`
--
ALTER TABLE `course_sections`
  ADD CONSTRAINT `fk_section_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `fk_section_slot` FOREIGN KEY (`slot_id`) REFERENCES `time_slots` (`slot_id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_questionnaires`
--
ALTER TABLE `student_questionnaires`
  ADD CONSTRAINT `student_questionnaires_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
