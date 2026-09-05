# RagWhatsApp - Intelligent WhatsApp Automation System

RagWhatsApp is a powerful WhatsApp management system that combines a **Laravel Dashboard** with a **Node.js WhatsApp Gateway** based on the Baileys library. It allows users to manage multiple WhatsApp instances, automate responses, and monitor connection status in real-time.

---

## 🚀 Key Features

- **Multi-Instance Management:** Create and manage multiple WhatsApp accounts from a single dashboard.
- **Real-time QR Linking:** An intelligent polling system that displays QR codes and automatically detects when a phone is linked.
- **Session Persistence:** Uses multi-file authentication to keep accounts logged in even after gateway restarts.
- **Auto-Response Templates:** Define custom responses for greetings, fallbacks, and human handoff requests.
- **Complete Cleanup:** A secure "Delete" function that logs out the session from WhatsApp and wipes local authentication data.
- **Developer Friendly:** Simple REST API for checking statuses and triggering connections.

---

## 🛠 System Architecture

The system consists of two primary components:

1.  **Laravel Backend (`/`):**
    - Manages the database (PostgreSQL/MySQL/SQLite).
    - Handles user authentication and multi-tenancy.
    - Provides the web interface for managing instances and templates.
    - Proxies status requests to the Node.js gateway.

2.  **Node.js WhatsApp Gateway (`/whatsapp-gateway`):**
    - Built using **Baileys (Multi-Device library)**.
    - Handles WebSocket connections to WhatsApp servers.
    - Generates and serves QR codes as PNG buffers.
    - Manages the lifecycle of WhatsApp sessions (initializing, connecting, disconnected, logout).

---

## ⚙️ Installation

### 1. Requirements
- PHP 8.1+ & Composer
- Node.js 18+ & NPM
- Database (SQLite is default in some setups)

### 2. Laravel Setup
```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate
```

### 3. WhatsApp Gateway Setup
```bash
cd whatsapp-gateway
npm install
```

---

## 🏃‍♂️ Running the System

You need to have both the Laravel server and the Node.js gateway running simultaneously.

### Terminal 1: Laravel
```bash
php artisan serve
```

### Terminal 2: WhatsApp Gateway
```bash
cd whatsapp-gateway
npm start
```

---

## 📖 Usage Guide

1.  **Create an Instance:** In the dashboard, click "Create New Instance" and give it a name.
2.  **Connect WhatsApp:**
    - Click the **QR Code icon** on the instance card.
    - Wait for the QR code to appear.
    - Open WhatsApp on your phone -> **Linked Devices** -> **Link a Device**.
    - Scan the code. The dashboard will automatically detect the connection and refresh.
3.  **Manage Templates:** Edit the auto-response templates on the right side of the dashboard to customize how your bot interacts with users.
4.  **Delete/Disconnect:** If you need to remove an account, click the **Red Trash Icon**. This will log the session out of WhatsApp and delete all local data.

---

## 📁 Technical Notes

- **Auth Storage:** WhatsApp credentials are stored encrypted in `whatsapp-gateway/auth/[instance_id]`.
- **Gateway API:**
    - `GET /status`: List all active sessions.
    - `GET /status/:id`: Check specific session state and get the linked phone number.
    - `DELETE /session/:id`: Terminate and wipe a session.
- **Polling:** The dashboard uses Alpine.js to poll the status endpoint every 2 seconds during the QR linking process for a seamless UX.
# whatsap_rag
