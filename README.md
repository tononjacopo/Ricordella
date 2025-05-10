# 📒 InkDrop - Full-stack project

The project is a note-taking web application where users can create, manage, and prioritize notes with customizable titles, texts, and deadlines, providing an intuitive and efficient way to organize personal tasks and reminders.
## 🌐 Live Demo

⚠️DUE TO MAINTENANCE REASON THE PAGE ATM IS NOT ONLINE

## 🔥 Features

- ✅ **Responsive Design** – Optimized for all screen sizes 📱💻
- ✅ **AOS Animations** – Smooth effects on scroll 🔄
- ✅ **Interactive Sidebar** – Dynamic side navigation 🏷️
- ✅ **Smooth Scrolling** – Fluid user experience 🚀
- ✅ **Enhanced Accessibility** – Optimized for all users 👥

## 📸 Screenshots

### 🔹 Homepage

<img src="https://github.com/tononjacopo/ricordella/blob/main/screenshot/home.png" width="700">

*Main screen of the project.*

### 🔹 register Section

<img src="https://github.com/tononjacopo/ricordella/blob/main/screenshot/register.png" width="700">

*Register form for users.*

### 🔹 Notes Page

<img src="https://github.com/tononjacopo/ricordella/blob/main/screenshot/notes-page.png" width="700">

*Detailed view of the Ricordella offerings.*

### 🔹 Notes Creation

<img src="https://github.com/tononjacopo/ricordella/blob/main/screenshot/new-note.png" height="400">

*Detailed view on how to create a new note.*

### 🔹 Premium Page

<img src="https://github.com/tononjacopo/ricordella/blob/main/screenshot/premium.png" width="700">

*Prewmium page coming soon.*

## 🗁 Project Structure

```plaintext
ricordella/
│
├── /public/               # Cartella accessibile da browser
│   ├── index.php          # Front controller (entry point)
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── logout.php
│   └── assets/            # style, script, media
│
├── /app/                  # Logica dell'applicazione
│   ├── config.php         # Configurazione DB, sessioni, timeout
│   ├── routes.php         # Routing base se usi un router custom
│   ├── auth/              # Autenticazione e gestione utente
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── logout.php
│   │   └── session.php    # Timeout inattività
│   ├── controllers/       # Logica applicativa
│   │   └── NoteController.php
│   ├── models/            # Accesso al database
│   │   ├── User.php
│   │   └── Note.php
│   ├── middlewares/       # Anti-DoS, rate limiter, auth checks
│   │   ├── RateLimiter.php
│   │   └── AuthMiddleware.php
│   └── utils/             # Funzioni di supporto
│       
│
├── /views/                # HTML e template
│   ├── layout.php
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   └── note_form.php
│
├── .htaccess              # Protezione accessi e URL rewriting
│ 
├── database/
│   └── schema.sql         #scheme sql
│
├── .env                   # Variabili di ambiente (non commitare su Git)
├── composer.json          # Configurazione delle dipendenze PHP
├── package.json           # (opzionale) Se usi strumenti JS (es. Webpack, Vite)
└── README.md              # Documentazione del progetto
```

## 🛠️ Technologies Used

- 🏗️ **HTML5** – Semantic page structure
- 🎨 **CSS3** – Modern, responsive styles
- ⚡ **JavaScript (ES6+)** – Dynamic functionality
- 🐘 **PHP** - Server-side scripting
- 💾 **SQL** - Database management

## 📩 Contact

- [🌐 Portfolio](https://tononjacopo.com)
- [🔗 LinkedIn](https://it.linkedin.com/in/tononjacopo)
- [💡 LeetCode](https://leetcode.com/tononjacopo)
- [❌ X](https://x.com/devtononjacopo)
- [🎨 Dribbble](https://dribbble.com/tononjacopo)

📩 **Email**: [info@tononjacopo.com](mailto:info@tononjacopo.com)

## 📝 License

This project is distributed under the **MIT** license. Feel free to use, modify, and distribute it! 🚀

---

**🔗 Consider giving a ⭐ on GitHub if you find it useful!** 😊✨
