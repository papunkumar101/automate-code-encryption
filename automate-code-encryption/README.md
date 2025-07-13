# 🔐 Obfuscate Tools Suite

This repository provides two tools to prepare web applications for production by obfuscating and optimizing your codebase:

1. **JavaScript Obfuscator Tool** (Node.js Web Interface)
2. **Universal PHP/JS/CSS Cleaner Tool** (PHP Web Interface)

---

## 📁 Project Structure

```
obfuscate/
├── nodejs-obfuscator/  # JavaScript Obfuscator Tool (Node.js + Express)
└── php-obfuscator/        # PHP/JS/CSS Cleaner Tool (PHP 8.3+)
```

---

## 🧩 1. JavaScript Obfuscator Tool

A lightweight web tool built with Node.js and Express to recursively obfuscate .js files using javascript-obfuscator.

### 📦 Requirements

- Node.js (v14+)
- npm

### 🔧 Installation & Usage

```bash
cd js-obfuscator
npm install
node index.js
```

---

## 🧩 2. Universal PHP/JS/CSS Cleaner Tool (PHP CLI Tool)

A PHP-based tool to clean, minify, and optimize PHP, JavaScript, and CSS files. It also downloads external CDN resources and replaces links with local paths.

### 🧪 Requirements

- PHP 8.3 or higher

### ▶ Usage

```bash
cd php-cleaner
php -S localhost:8000 index.php
```