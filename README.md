# hejamuni_task_flow# 🚀 HEJAMUNI STRUCT — TaskFlow Platform

> A full-featured productivity and business management platform built with Laravel — combining task management, job lead tracking, financial accounting, and daily productivity logging in one unified workspace.

![Platform](https://img.shields.io/badge/Platform-Web-blue?style=flat-square)
![Backend](https://img.shields.io/badge/Backend-Laravel%20%28PHP%29-red?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square)

---

## 📸 Screenshots

| Dashboard | Tasks Board | Job Leads |
|-----------|-------------|-----------|
| ![Dashboard](screenshots/dashboard.png) | ![Board](screenshots/board.png) | ![Jobs](screenshots/job-leads.png) |

---

## ✨ Features

### 🗂️ Task Management
- Create, assign, and prioritize tasks (Critical / High / Medium / Low)
- Filter by status, priority, and category
- Kanban Board View with Pending, In Progress, Completed, and Delayed columns
- Task completion tracking with efficiency scoring

### 💼 Job Lead Tracking
- Track job applications with pipeline funnel (Imported → Applied → Interview → Offer)
- Import job leads directly from **WhatsApp messages** using AI Smart Parser
- Supports link posts and plain text job vacancy formats
- Auto-extracts email, phone, salary, and location from pasted job posts

### 💰 Finance & Accounting
- Double-entry bookkeeping with chart of accounts (Assets, Liabilities)
- Quick transaction types: Expense, Salary, Income, Transfer, Invoice, Utility, Rent
- Income vs Expenses bar chart visualization
- Trial Balance and Journal reports
- Net Summary with debit/credit account totals

### 📊 Dashboard & Analytics
- Real-time productivity overview with completion trends (2W / 1M views)
- Productivity Score out of 100 (Efficiency, Completion, Overdue, Applications)
- Status breakdown donut chart

### 📅 Daily Log
- Daily mood, energy level, and focus score tracking
- Time tracking: Total minutes worked, productive time, breaks
- 14-day activity history with per-day efficiency

### 👤 Profile & Settings
- User profile management with timezone support
- Dark / Light theme toggle
- Secure password change

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP / Laravel |
| Frontend | Blade Templates + JavaScript |
| Database | MySQL |
| Authentication | Laravel Auth (Session-based) |
| Charting | Chart.js / Custom SVG |
| Deployment | Free.nf / Custom Hosting |

---

## 🚀 Getting Started

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL
- Node.js & NPM (for frontend assets)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/hejamuni-struct.git
cd hejamuni-struct

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
npm install && npm run build

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure your database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hejamuni_struct
DB_USERNAME=root
DB_PASSWORD=

# 7. Run migrations and seed data
php artisan migrate --seed

# 8. Start the development server
php artisan serve
```

Visit: `http://localhost:8000`

### Demo Credentials
```
Email:    alex@taskflow.dev
Password: password
```

---

## 📁 Project Structure

```
hejamuni-struct/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php
│   │   ├── TaskController.php
│   │   ├── JobLeadController.php
│   │   ├── FinanceController.php
│   │   └── DailyLogController.php
│   └── Models/
├── resources/
│   └── views/
│       ├── dashboard/
│       ├── tasks/
│       ├── jobs/
│       ├── finance/
│       └── log/
├── routes/
│   └── web.php
├── database/
│   ├── migrations/
│   └── seeders/
└── public/
```

---

## 🌐 Live Demo

🔗 [https://hejamuni.free.nf](https://hejamuni.free.nf/login)

---

## 📋 Roadmap

- [ ] Mobile app (Flutter)
- [ ] REST API for external integrations
- [ ] Email notifications for overdue tasks
- [ ] Team / multi-user workspace support
- [ ] Export reports to PDF / Excel
- [ ] WhatsApp Bot integration for task creation

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m "Add: your feature description"`
4. Push to your branch: `git push origin feature/your-feature-name`
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Umayanga**
- GitHub: [@YOUR_GITHUB](https://github.com/YOUR_GITHUB)
- LinkedIn: [Your LinkedIn](https://linkedin.com/in/YOUR_LINKEDIN)

---

> Built with ❤️ in Sri Lanka 🇱🇰
