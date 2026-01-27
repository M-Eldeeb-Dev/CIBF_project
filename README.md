# CIBF 2026 - "أنا متطوع" (I am a Volunteer) App

A comprehensive management and information platform for volunteers and administrators at the **Cairo International Book Fair 2026**. This application facilitates volunteer scheduling, hall management, and real-time communication.

## 🚀 Tech Stack

- **Backend**: PHP (Session-based auth, core logic)
- **Frontend**: HTML5, Tailwind CSS
- **Database / Real-time**: Supabase (PostgreSQL + Real-time subscriptions)
- **Typography**: Cairo (Google Fonts)
- **Icons**: SVG-based system

## ✨ Key Features

### 👤 Volunteer Dashboard

- **Real-time Status**: Live presence tracking (Synced via Supabase).
- **Shift Management**: View current and upcoming rotations/tasks.
- **Interactive Hall Map**: Embedded viewer for fairgrounds navigation.
- **Publishers Directory**: Searchable directory of all publishers and their booth locations.
- **Volunteer Guide**: Access to technical and administrative guides.

### 🛠️ Admin Dashboard

- **Volunteer Oversight**: Monitor all active volunteers and their assigned sectors.
- **Map & Rotation Management**: Assign volunteers to specific halls and booths.
- **Global Alerts (Ticker)**: Broadcast urgent notes and notifications to all logged-in volunteers via a marquee ticker.
- **Presence Tracking**: Real-time view of who is currently on-site.

### 📚 Halls & Publishers Directory

- **Categorized Halls**: Navigate through Halls 1 to 6 (including sub-sections A, B, C).
- **Booth Search**: Instantly find publishers by name or booth number.
- **Capacity Info**: View booth dimensions and locations.

## 📂 Project Structure

- `admin-dashboard.php`: Main portal for administrators.
- `volunteer-dashboard.php`: Core interface for volunteers.
- `index.php`: Unified login page with Supabase authentication integration.
- `halls.php`: Comprehensive directory of all exhibition spaces.
- `js/`:
  - `auth-service.js`: Handles session management and Supabase auth.
  - `volunteers-service.js`: Manages real-time data fetching and subscriptions.
- `json_files/`: Local storage for notes and static data configurations.
- `parsing_files/`: Utility scripts for data processing.
- `pdfs/`: Storage for fairgrounds documentation and guides.

## 🔧 Setup & Configuration

1.  **Web Server**: Requires PHP 7.4 or higher.
2.  **Supabase Integration**:
    - Configure API keys in `js/supabase-client.js`.
    - Ensure the `volunteers` table is set up in Supabase with Real-time enabled.
3.  **Authentication**:
    - Volunteers log in using their ID codes (e.g., `O-0083`).
    - Admin access is restricted to code `O-9999`.

---

_Created by [Mohamed Eldeeb](https://www.linkedin.com/in/mh-deeb)_
