# DayFlow – Daily Planner
**IMS566 Advanced Web Design Development and Content Management**
**Individual Assignment**

---

## Project Description

DayFlow is a personal daily planning web application built with PHP, HTML, CSS, and JavaScript. It allows users to manage their tasks, visualise their daily schedule, and track habits — all from a clean, responsive interface.

---

## Features Included

| Feature | Details |
|---|---|
| Authentication | Login & Register with simulated hardcoded credentials and error feedback |
| Responsive Navigation | Sidebar with mobile hamburger toggle, links all pages |
| Dashboard | Summary stats, donut chart (task status), bar chart (by category), mini calendar, today's tasks, habit streaks |
| Task Manager | Filterable/searchable task table with priority, status, category; priority pie chart; quick stats |
| Daily Schedule | Time-block view, event table, horizontal bar chart for time distribution |
| Habit Tracker | Habit cards with streak progress, streak comparison chart, habits overview table |
| Data Visualisation | Chart.js — donut, bar, pie, horizontal bar charts throughout |
| Footer | Professional footer with metadata on all pages |
| Content Management | Structured sections with titles, metadata, semantic HTML |

---

## How to Test Login

1. Open XAMPP and start **Apache**
2. Place the `daily-planner` folder in `htdocs/`
3. Visit `http://localhost/daily-planner/`
4. Use any of the following demo credentials:

| Username | Password | Role |
|---|---|---|
| `admin` | `admin123` | Administrator |
| `sarah` | `sarah2024` | Planner |
| `demo` | `demo1234` | Guest |

---

## Project Structure

```
daily-planner/
├── index.php           # Login page
├── register.php        # Register page
├── logout.php          # Session destroy
├── dashboard.php       # Main dashboard
├── tasks.php           # Task manager (Data View 1)
├── schedule.php        # Daily schedule (Data View 2)
├── habits.php          # Habit tracker (Data View 3)
├── css/
│   └── style.css       # Main stylesheet
├── js/
│   └── main.js         # Shared JavaScript
├── includes/
│   ├── config.php      # Session, auth functions, sample data
│   ├── navbar.php      # Sidebar navigation component
│   ├── topbar.php      # Top header component
│   └── footer.php      # Footer component
└── README.md
```

---

## Frameworks & Libraries Used

- **PHP 8** — Server-side logic, sessions, authentication
- **Bootstrap 5** — Responsive grid and utility classes (via CDN)
- **Chart.js 4** — Data visualisation (donut, bar, pie charts)
- **Google Fonts** — Playfair Display + DM Sans typography
- **Custom CSS** — CSS variables, animations, responsive layout

---

## Deployment

1. **Local (XAMPP):** Copy folder to `htdocs/`, start Apache, visit `http://localhost/daily-planner/`
2. **GitHub Repository:** [https://github.com/username/daily-planner](https://github.com/username/daily-planner)
3. **Live Demo (GitHub Pages):** [https://username.github.io/daily-planner/](https://username.github.io/daily-planner/)

> **Note:** GitHub Pages serves static files only. For full PHP functionality, use XAMPP or a PHP hosting service.

---

## Rubric Coverage

| Criteria | Implementation |
|---|---|
| Design & Creativity | Custom CSS design system, Playfair Display + DM Sans fonts, amber/sage colour palette, animations |
| Authentication | Login/Register with session, hardcoded credentials, error & success feedback |
| Navigation & Menu | Fixed sidebar, mobile-responsive hamburger menu, active state highlights |
| Dashboard | Stats cards, 2 Chart.js charts, today's tasks, mini calendar, habit streaks |
| Data View Pages | tasks.php (task table + filters), schedule.php (time blocks + table), habits.php (cards + table) |
| Data Visualisation | Donut (status), Bar (category), Pie (priority), Horizontal bar (schedule), Grouped bar (streaks) |
| Footer & Content Management | Consistent footer, semantic HTML, metadata on all pages |
| Technical Quality | Structured PHP includes, CSS variables, responsive breakpoints, commented code |

---

*Developed for IMS566 – Universiti Teknologi MARA*
