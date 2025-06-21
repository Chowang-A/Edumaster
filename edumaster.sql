--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

-- Started on 2025-06-21 09:47:53

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 228 (class 1259 OID 24848)
-- Name: course_images; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.course_images (
    id integer NOT NULL,
    course_id integer NOT NULL,
    image_path text NOT NULL,
    uploaded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.course_images OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 24847)
-- Name: course_images_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.course_images_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.course_images_id_seq OWNER TO postgres;

--
-- TOC entry 5015 (class 0 OID 0)
-- Dependencies: 227
-- Name: course_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.course_images_id_seq OWNED BY public.course_images.id;


--
-- TOC entry 224 (class 1259 OID 24812)
-- Name: course_videos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.course_videos (
    id integer NOT NULL,
    course_id integer,
    video_title character varying(255) NOT NULL,
    video_path text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.course_videos OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 24811)
-- Name: course_videos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.course_videos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.course_videos_id_seq OWNER TO postgres;

--
-- TOC entry 5016 (class 0 OID 0)
-- Dependencies: 223
-- Name: course_videos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.course_videos_id_seq OWNED BY public.course_videos.id;


--
-- TOC entry 218 (class 1259 OID 24693)
-- Name: courses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.courses (
    id integer NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    image_url text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    category character varying,
    teacher_id integer NOT NULL
);


ALTER TABLE public.courses OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 24692)
-- Name: courses_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.courses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.courses_id_seq OWNER TO postgres;

--
-- TOC entry 5017 (class 0 OID 0)
-- Dependencies: 217
-- Name: courses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.courses_id_seq OWNED BY public.courses.id;


--
-- TOC entry 222 (class 1259 OID 24778)
-- Name: enrollments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.enrollments (
    id integer NOT NULL,
    user_id integer,
    course_id integer,
    enrolled_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.enrollments OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 24777)
-- Name: enrollments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.enrollments_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.enrollments_id_seq OWNER TO postgres;

--
-- TOC entry 5018 (class 0 OID 0)
-- Dependencies: 221
-- Name: enrollments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.enrollments_id_seq OWNED BY public.enrollments.id;


--
-- TOC entry 226 (class 1259 OID 24827)
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.messages (
    id integer NOT NULL,
    sender_id integer NOT NULL,
    receiver_id integer NOT NULL,
    subject character varying(255) NOT NULL,
    message text NOT NULL,
    send_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    visible_to_sender boolean DEFAULT true,
    visible_to_receiver boolean DEFAULT true,
    deleted_by character varying(10),
    deleted_at timestamp without time zone
);


ALTER TABLE public.messages OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 24826)
-- Name: messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.messages_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.messages_id_seq OWNER TO postgres;

--
-- TOC entry 5019 (class 0 OID 0)
-- Dependencies: 225
-- Name: messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.messages_id_seq OWNED BY public.messages.id;


--
-- TOC entry 234 (class 1259 OID 24894)
-- Name: options; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.options (
    id integer NOT NULL,
    question_id integer,
    option_text text NOT NULL,
    is_correct boolean DEFAULT false
);


ALTER TABLE public.options OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 24893)
-- Name: options_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.options ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.options_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 232 (class 1259 OID 24881)
-- Name: questions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.questions (
    id integer NOT NULL,
    quiz_id integer,
    question_text text NOT NULL
);


ALTER TABLE public.questions OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 24880)
-- Name: questions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.questions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.questions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 236 (class 1259 OID 24908)
-- Name: quiz_attempts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.quiz_attempts (
    id integer NOT NULL,
    quiz_id integer,
    user_id integer,
    score integer NOT NULL,
    total_questions integer NOT NULL,
    attempted_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    total integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.quiz_attempts OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 24907)
-- Name: quiz_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.quiz_attempts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.quiz_attempts_id_seq OWNER TO postgres;

--
-- TOC entry 5020 (class 0 OID 0)
-- Dependencies: 235
-- Name: quiz_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.quiz_attempts_id_seq OWNED BY public.quiz_attempts.id;


--
-- TOC entry 230 (class 1259 OID 24867)
-- Name: quizzes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.quizzes (
    id integer NOT NULL,
    teacher_id integer,
    title character varying(255) NOT NULL,
    subject character varying(255),
    topic character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.quizzes OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 24866)
-- Name: quizzes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.quizzes ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.quizzes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- TOC entry 220 (class 1259 OID 24763)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    password text NOT NULL,
    role character varying(20) DEFAULT 'student'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    security_question_1 character varying(255),
    security_answer_1 character varying(255),
    security_question_2 character varying(255),
    security_answer_2 character varying(255),
    CONSTRAINT role_check CHECK (((role)::text = ANY ((ARRAY['student'::character varying, 'teacher'::character varying, 'admin'::character varying, 'system'::character varying])::text[]))),
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['student'::character varying, 'teacher'::character varying, 'admin'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 24762)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 5021 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4800 (class 2604 OID 24851)
-- Name: course_images id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.course_images ALTER COLUMN id SET DEFAULT nextval('public.course_images_id_seq'::regclass);


--
-- TOC entry 4794 (class 2604 OID 24815)
-- Name: course_videos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.course_videos ALTER COLUMN id SET DEFAULT nextval('public.course_videos_id_seq'::regclass);


--
-- TOC entry 4787 (class 2604 OID 24696)
-- Name: courses id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.courses ALTER COLUMN id SET DEFAULT nextval('public.courses_id_seq'::regclass);


--
-- TOC entry 4792 (class 2604 OID 24781)
-- Name: enrollments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.enrollments ALTER COLUMN id SET DEFAULT nextval('public.enrollments_id_seq'::regclass);


--
-- TOC entry 4796 (class 2604 OID 24830)
-- Name: messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages ALTER COLUMN id SET DEFAULT nextval('public.messages_id_seq'::regclass);


--
-- TOC entry 4804 (class 2604 OID 24911)
-- Name: quiz_attempts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quiz_attempts ALTER COLUMN id SET DEFAULT nextval('public.quiz_attempts_id_seq'::regclass);


--
-- TOC entry 4789 (class 2604 OID 24766)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5001 (class 0 OID 24848)
-- Dependencies: 228
-- Data for Name: course_images; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.course_images (id, course_id, image_path, uploaded_at) FROM stdin;
12	10	uploads/images/1750441995_6855a00b66aad_vecteezy_cyber-big-data-flow-blockchain-data-fields-brain-network_22263426.jpg	2025-06-20 23:23:15.401204
13	10	uploads/images/1750441995_6855a00b69209_download.jpg	2025-06-20 23:23:15.401204
14	11	uploads/images/1750442764_6855a30c500bc_istockphoto-1500469862-640x640.jpg	2025-06-20 23:36:04.321333
15	11	uploads/images/1750442764_6855a30c51b99_Screenshot_2025-06-20_233417.png	2025-06-20 23:36:04.321333
16	12	uploads/images/1750443247_6855a4ef49631_Professional-video-editing-thumbnail-56999f1b.png	2025-06-20 23:44:07.247975
17	12	uploads/images/1750443247_6855a4ef4a7b5_Videography-and-Video-Editing-Courses-Bundle.jpg	2025-06-20 23:44:07.247975
18	6	uploads/images/1750475678WhatsApp Image 2025-06-02 at 15.38.21_2f7c4fa9.jpg	2025-06-21 08:44:38.437772
19	8	uploads/images/1750475929java.jpg	2025-06-21 08:48:49.649431
21	8	uploads/images/1750476494Javascript1.jpg	2025-06-21 08:58:14.83952
\.


--
-- TOC entry 4997 (class 0 OID 24812)
-- Dependencies: 224
-- Data for Name: course_videos; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.course_videos (id, course_id, video_title, video_path, created_at) FROM stdin;
3	6	test	uploads/videos/1749378871_68456737e2d50_vid-8.mp4	2025-06-08 16:04:31.922867
5	8	Javascript	https://youtu.be/cpoXLj24BDY?si=CryiTo6Ky8I7dB1x	2025-06-20 20:15:44.396115
8	10	data science 1	https://youtu.be/gDZ6czwuQ18?si=Sp2qx_n6MmD9eABZ	2025-06-20 23:23:15.401204
9	10	Data science 2	https://youtu.be/ua-CiDNNj30?si=eIDMwikZgeJUErWm	2025-06-20 23:23:15.401204
10	10	data science intro	https://youtu.be/9R3X0JoCLyU?si=kKQY-3PHTlHoarUR	2025-06-20 23:23:15.401204
11	11	Biology	https://youtu.be/3tisOnOkwzo?si=FuFhRiiX_bYe_tfG	2025-06-20 23:36:04.321333
12	12	videography 1	https://youtu.be/x1WMLywZWGM?si=lZ54H7O-LMGS7zyz	2025-06-20 23:44:07.247975
13	12	Videography 2	https://youtu.be/wj9EMo9EjGU?si=lnbF01QpsdtnYToB	2025-06-20 23:44:07.247975
\.


--
-- TOC entry 4991 (class 0 OID 24693)
-- Dependencies: 218
-- Data for Name: courses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.courses (id, title, description, image_url, created_at, category, teacher_id) FROM stdin;
6	test	ww	\N	2025-06-08 16:04:31.922867	test	2
10	Data science	learn easy simple data sciences	\N	2025-06-20 23:23:15.401204	science	9
11	Biology	easily learn Biology chapters	\N	2025-06-20 23:36:04.321333	science	9
12	Videography	learn simple and easy videography	\N	2025-06-20 23:44:07.247975	Videography	9
8	JavaScript	Learn With EduMaster	\N	2025-06-20 20:15:44.396115	Programming	2
\.


--
-- TOC entry 4995 (class 0 OID 24778)
-- Dependencies: 222
-- Data for Name: enrollments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.enrollments (id, user_id, course_id, enrolled_at) FROM stdin;
20	3	8	2025-06-20 20:16:24.121971
27	3	10	2025-06-21 09:00:03.201875
29	3	11	2025-06-21 09:00:31.347316
30	3	6	2025-06-21 09:00:45.347816
\.


--
-- TOC entry 4999 (class 0 OID 24827)
-- Dependencies: 226
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, sender_id, receiver_id, subject, message, send_at, visible_to_sender, visible_to_receiver, deleted_by, deleted_at) FROM stdin;
10	6	2	Student Left Course	Student: student (ID: 3) has left the course: test 2.	2025-06-18 00:04:17.175928	t	f	\N	\N
11	1	2	test	hello	2025-06-18 16:15:40.500699	f	f	\N	\N
19	6	2	Student Left Course	Student: student (ID: 3) has left the course: test 2.	2025-06-19 10:22:24.456626	t	t	\N	\N
20	6	2	Student Left Course	Student: student (ID: 3) has left the course: test.	2025-06-20 09:24:55.231181	t	t	\N	\N
21	6	2	Student Left Course	Student: student (ID: 3) has left the course: test.	2025-06-20 15:02:40.649517	t	t	\N	\N
22	1	2	test	hello	2025-06-20 16:26:19.352778	t	t	\N	\N
18	3	2	test	hello	2025-06-19 08:42:20.868024	t	t	\N	\N
26	9	3	Removed from course	You have been removed from the course ID 10 by your teacher.	2025-06-21 07:55:50.104361	t	t	\N	\N
27	9	9	Removed from course	You have been removed from the course ID 11 by your teacher.	2025-06-21 08:04:27.436207	t	f	receiver	2025-06-21 04:34:45
25	9	9	Removed from course	You have been removed from the course ID 12 by your teacher.	2025-06-21 07:55:21.868408	t	f	receiver	2025-06-21 04:34:49
28	2	1	Removed from course	You have been removed from the course ID 8 by your teacher.	2025-06-21 08:43:09.485333	t	t	\N	\N
29	2	2	Removed from course	You have been removed from the course ID 8 by your teacher.	2025-06-21 08:43:16.201035	t	t	\N	\N
30	2	9	Removed from course	You have been removed from the course ID 8 by your teacher.	2025-06-21 08:43:22.592591	t	t	\N	\N
31	6	2	Student Left Course	Student: john (ID: 3) has left the course: test.	2025-06-21 08:59:57.339563	t	t	\N	\N
32	6	9	Student Left Course	Student: john (ID: 3) has left the course: Videography.	2025-06-21 09:00:23.936768	t	t	\N	\N
\.


--
-- TOC entry 5007 (class 0 OID 24894)
-- Dependencies: 234
-- Data for Name: options; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.options (id, question_id, option_text, is_correct) FROM stdin;
8020	13	Golgi apparatus	f
8021	13	Nucleus	f
8022	13	mitochondria	t
8023	13	Lysosome	f
8024	14	Cytoplasm	t
8025	14	cell wall	f
8026	14	Vacuole	f
8027	14	Chromatic 	f
8028	15	Cell membrane 	f
8029	15	Cytoplasm	f
8030	15	Cell wall	t
8031	15	Mitochondria	f
8032	16	Vacuole	t
8033	16	Chloroplast	f
8034	16	Nucleus	f
8035	16	Cell wall	f
8036	17	To produce energy	f
8037	17	To controls what enters and leaves the Cell	t
8038	17	To protect DNA	f
8039	17	To create proteins 	f
8016	12	10	t
8017	12	199	f
8018	12	11	f
8019	12	12	f
\.


--
-- TOC entry 5005 (class 0 OID 24881)
-- Dependencies: 232
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.questions (id, quiz_id, question_text) FROM stdin;
12	6	what is 12-2
13	8	        what is the powerhouse of the cell?
14	8	        jelly like substance inside a cell that holds organelles 
15	8	structure that is present in plant cell and protects and supports them but is not in animal cell?
16	8	   Structure in a plant cell that store water and nutrients 
17	8	        function of cell membrane 
\.


--
-- TOC entry 5009 (class 0 OID 24908)
-- Dependencies: 236
-- Data for Name: quiz_attempts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.quiz_attempts (id, quiz_id, user_id, score, total_questions, attempted_at, total) FROM stdin;
4	6	3	0	1	2025-06-20 18:55:29.217095	0
\.


--
-- TOC entry 5003 (class 0 OID 24867)
-- Dependencies: 230
-- Data for Name: quizzes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.quizzes (id, teacher_id, title, subject, topic, created_at) FROM stdin;
6	2	test	test	test	2025-06-19 22:03:13.553934
8	9	Biology	science	Cells	2025-06-21 00:05:56.859802
\.


--
-- TOC entry 4993 (class 0 OID 24763)
-- Dependencies: 220
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, password, role, created_at, security_question_1, security_answer_1, security_question_2, security_answer_2) FROM stdin;
13	digital	dddd@gmail.com	$2y$10$YUWkV10EF9VF6XGDCB3OguIXOExRRX6l7MmDZ0l8buh50Hk8JXPOK	student	2025-06-20 16:24:22.59698	what is your name ?	ddd	why are you here ?	to d
14	bjjb	dfn@gmail.com	$2y$10$T5c4LuNlvFN6M7UlTyLIR.EJ7amZ2vNhxtEeVtHoesVQhS.ooMiTq	student	2025-06-20 22:24:18.913584	what is your name ?	qq1q	why are you here ?	qqqqq
15	hh	h@gmail.com	$2y$10$lUhDucPchBJFfvA/AT1BzuCdq6UWKp.phUSi2qk3mHDHkShNglk4O	student	2025-06-20 22:31:52.695264	what is your name ?	n	why are you here ?	H
9	user11	u@gmail.com	$2y$10$CdIc8THUkD5gb4wxQRqt0.nbwse56ocBC/Z8ywuuL1oAB5P6jV8lS	teacher	2025-06-20 10:21:31.069292	what is your name ?	user	how much is 2+2	4
3	john	s@gmail.com	$2y$10$cqNOTVgMIFO2HMnRsKYtOeE7xlmITVzF1jNYajTv/dLOsi4mEPA3G	student	2025-06-06 17:52:49.289748	what is your name ?	user	why are you here ?	to test
2	prototeacher	teacher@gmail.com	$2y$10$6jFsPD.yyo46iPD.YxokP.snS0ewWRgOjJ/jjolI6TGQqk3rqkBa2	teacher	2025-06-06 17:44:49.208004	what is your name ?	teacher	www	yes
6	system	system_edu@gmail.com	$2y$10$EW3egoHC4rG/SUdFhXbdoeNc1mHC3bvMu14CqNAcZA98a10kNKxIG	student	2025-06-14 21:03:56.278308	what is your name ?	system	why	ok
8	Mark	m@gmail.com	$2y$10$T.RGrnVYtvHk6H/ytW4BWuuh8ZM7/xIKQ17cHmc/c4EHKyaenKuuW	student	2025-06-20 09:35:45.915703	what is your name ?	Ra	why are you here ?	Ra
1	ADMIN	admin@gmail.com	$2y$10$KfBAv.DDTz9x7xmd7O5cgOzhV6xKQwjEDfWkaHkFUQu0dDEBYiM.y	admin	2025-06-06 16:02:20.655786	what is your name ?	user	why are you here ?	to test
10	vv	v@gmail.com	$2y$10$rug..P3J9vSGkb6eLHIpe.rpqE2nSfrYOMnu9/hQNDt5cdLDqo6vm	student	2025-06-20 10:32:26.812651	what is your name ?	2	why are you here ?	11
11	Rahul	rahul12@gmail.com	$2y$10$udOOTZPrrlc7gjYqa6dTaO.ZOIAWRpaYE1KY0rFDSb2aCPZw5V2ti	student	2025-06-20 16:11:23.346727	dog name	german	phone	iphone 13
12	chowang	chowang@gmail.com	$2y$10$Vo6XrZswq9FeoeOM81mIE.gtBi1GXkhf812dUSzv.xmXojNlWwQtO	student	2025-06-20 16:17:47.063573	what is your name ?	Sherpa	why	beacuse
\.


--
-- TOC entry 5022 (class 0 OID 0)
-- Dependencies: 227
-- Name: course_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.course_images_id_seq', 22, true);


--
-- TOC entry 5023 (class 0 OID 0)
-- Dependencies: 223
-- Name: course_videos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.course_videos_id_seq', 13, true);


--
-- TOC entry 5024 (class 0 OID 0)
-- Dependencies: 217
-- Name: courses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.courses_id_seq', 12, true);


--
-- TOC entry 5025 (class 0 OID 0)
-- Dependencies: 221
-- Name: enrollments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.enrollments_id_seq', 30, true);


--
-- TOC entry 5026 (class 0 OID 0)
-- Dependencies: 225
-- Name: messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.messages_id_seq', 32, true);


--
-- TOC entry 5027 (class 0 OID 0)
-- Dependencies: 233
-- Name: options_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.options_id_seq', 8039, true);


--
-- TOC entry 5028 (class 0 OID 0)
-- Dependencies: 231
-- Name: questions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.questions_id_seq', 17, true);


--
-- TOC entry 5029 (class 0 OID 0)
-- Dependencies: 235
-- Name: quiz_attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.quiz_attempts_id_seq', 4, true);


--
-- TOC entry 5030 (class 0 OID 0)
-- Dependencies: 229
-- Name: quizzes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.quizzes_id_seq', 8, true);


--
-- TOC entry 5031 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 15, true);


--
-- TOC entry 4824 (class 2606 OID 24856)
-- Name: course_images course_images_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.course_images
    ADD CONSTRAINT course_images_pkey PRIMARY KEY (id);


--
-- TOC entry 4820 (class 2606 OID 24820)
-- Name: course_videos course_videos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.course_videos
    ADD CONSTRAINT course_videos_pkey PRIMARY KEY (id);


--
-- TOC entry 4810 (class 2606 OID 24701)
-- Name: courses courses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT courses_pkey PRIMARY KEY (id);


--
-- TOC entry 4816 (class 2606 OID 24784)
-- Name: enrollments enrollments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_pkey PRIMARY KEY (id);


--
-- TOC entry 4818 (class 2606 OID 24786)
-- Name: enrollments enrollments_user_id_course_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_user_id_course_id_key UNIQUE (user_id, course_id);


--
-- TOC entry 4822 (class 2606 OID 24835)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 4830 (class 2606 OID 24901)
-- Name: options options_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.options
    ADD CONSTRAINT options_pkey PRIMARY KEY (id);


--
-- TOC entry 4828 (class 2606 OID 24887)
-- Name: questions questions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_pkey PRIMARY KEY (id);


--
-- TOC entry 4832 (class 2606 OID 24914)
-- Name: quiz_attempts quiz_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quiz_attempts
    ADD CONSTRAINT quiz_attempts_pkey PRIMARY KEY (id);


--
-- TOC entry 4826 (class 2606 OID 24874)
-- Name: quizzes quizzes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quizzes
    ADD CONSTRAINT quizzes_pkey PRIMARY KEY (id);


--
-- TOC entry 4812 (class 2606 OID 24775)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 4814 (class 2606 OID 24773)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4839 (class 2606 OID 24857)
-- Name: course_images course_images_course_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.course_images
    ADD CONSTRAINT course_images_course_id_fkey FOREIGN KEY (course_id) REFERENCES public.courses(id) ON DELETE CASCADE;


--
-- TOC entry 4836 (class 2606 OID 24821)
-- Name: course_videos course_videos_course_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.course_videos
    ADD CONSTRAINT course_videos_course_id_fkey FOREIGN KEY (course_id) REFERENCES public.courses(id) ON DELETE CASCADE;


--
-- TOC entry 4834 (class 2606 OID 24792)
-- Name: enrollments enrollments_course_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_course_id_fkey FOREIGN KEY (course_id) REFERENCES public.courses(id) ON DELETE CASCADE;


--
-- TOC entry 4835 (class 2606 OID 24787)
-- Name: enrollments enrollments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.enrollments
    ADD CONSTRAINT enrollments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4833 (class 2606 OID 24806)
-- Name: courses fk_teacher; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.courses
    ADD CONSTRAINT fk_teacher FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4837 (class 2606 OID 24841)
-- Name: messages messages_receiver_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_receiver_id_fkey FOREIGN KEY (receiver_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4838 (class 2606 OID 24836)
-- Name: messages messages_sender_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_sender_id_fkey FOREIGN KEY (sender_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4842 (class 2606 OID 24902)
-- Name: options options_question_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.options
    ADD CONSTRAINT options_question_id_fkey FOREIGN KEY (question_id) REFERENCES public.questions(id) ON DELETE CASCADE;


--
-- TOC entry 4841 (class 2606 OID 24888)
-- Name: questions questions_quiz_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_quiz_id_fkey FOREIGN KEY (quiz_id) REFERENCES public.quizzes(id) ON DELETE CASCADE;


--
-- TOC entry 4843 (class 2606 OID 24915)
-- Name: quiz_attempts quiz_attempts_quiz_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quiz_attempts
    ADD CONSTRAINT quiz_attempts_quiz_id_fkey FOREIGN KEY (quiz_id) REFERENCES public.quizzes(id) ON DELETE CASCADE;


--
-- TOC entry 4844 (class 2606 OID 24920)
-- Name: quiz_attempts quiz_attempts_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quiz_attempts
    ADD CONSTRAINT quiz_attempts_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4840 (class 2606 OID 24875)
-- Name: quizzes quizzes_teacher_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.quizzes
    ADD CONSTRAINT quizzes_teacher_id_fkey FOREIGN KEY (teacher_id) REFERENCES public.users(id) ON DELETE CASCADE;


-- Completed on 2025-06-21 09:47:53

--
-- PostgreSQL database dump complete
--

