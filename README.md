# Benguet State University — Strategic Performance Management System (BSU-SPMS)

> A web-based system built for Benguet State University to make evaluating faculty and staff simple, fair, and paperless—following official Civil Service Commission (CSC) guidelines.

---

## About the Project

Every semester, university professors and staff need to set work goals, show proof of what they accomplished, and receive fair performance ratings. In the past, this meant dealing with stacks of paper forms, lost document attachments, and complicated spreadsheet calculations.

BSU-SPMS moves this entire process online. Developed as an Information Technology capstone project for the Benguet State University Human Resource Development Office (HRDO), the system connects everyone—from university leaders down to individual faculty members—in one clear, organized place.

---

## How It Works: The 4-Stage Performance Cycle

The system guides users through the four official steps of the evaluation cycle:

```
┌──────────────────────┐        ┌──────────────────────┐
│       Stage 1        │        │       Stage 2        │
│     Goal Setting     │ ─────> │ Tracking & Guidance  │
│  (Set work targets)  │        │ (Upload work proof)  │
└──────────────────────┘        └──────────┬───────────┘
           ▲                               │
           │                               ▼
┌──────────┴───────────┐        ┌──────────────────────┐
│       Stage 4        │        │       Stage 3        │
│  Rewards & Reports   │ <───── │   Grading & Review   │
│ (Bonuses & exports)  │        │ (Calculate ratings)  │
└──────────────────────┘        └──────────────────────┘
```

| Stage | What Happens | Who Is Involved |
| :--- | :--- | :--- |
| **1. Goal Setting** | Top leaders set university goals first. Deans, department chairs, and teachers align their personal targets and get supervisor approval online. | Executives, Deans, Chairs, Faculty & Staff |
| **2. Tracking & Guidance** | Employees upload digital proof of their work (reports, certificates, materials) while supervisors provide ongoing advice and coaching notes. | Employees & Immediate Supervisors |
| **3. Grading & Review** | Employees rate their own accomplishments, and supervisors review and confirm the scores. The system calculates all official final ratings automatically. | Employees & Supervisors |
| **4. Rewards & Reports** | The university checks who qualifies for government bonuses, sees where more training is needed, and exports official, ready-to-print Excel reports. | HRDO, Performance Committee, Employees |

---

## What the System Does

- **Custom views for every role:** University administrators, deans, department chairs, and individual teachers each get an interface showing only what they need to see.
- **One-click official forms:** Creates official government Excel forms (OPCR, DPCR, and IPCR) with all formulas, titles, and headers already filled in.
- **Account protection:** Offers two-factor login codes (2FA) and keeps a clear log of who made changes and when.
- **Made for phones and computers:** Fully responsive so users can approve goals or check ratings from a phone, tablet, or laptop, with a comfortable dark mode option.

---

## How the Technologies Fit Together

```
┌────────────────────────────────────────────────────────┐
│               User Screen (Phones & PCs)               │
│         Tailwind CSS, Vanilla CSS & JavaScript         │
│   (Handles page design, buttons, drawers, and themes)  │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                  Main System Engine                    │
│                 CodeIgniter 4 (PHP)                    │
│     (Processes logins, scores, and evaluation rules)   │
└───────────────┬────────────────────────┬───────────────┘
                │                        │
                ▼                        ▼
┌──────────────────────────────┐ ┌───────────────────────┐
│       Database Storage       │ │  Excel Report Builder │
│       MySQL / MariaDB        │ │     PhpSpreadsheet    │
│ (Stores accounts and grades) │ │ (Creates CSC reports) │
└──────────────────────────────┘ └───────────────────────┘
```

| Part of System | Tool | Simple Explanation |
| :--- | :--- | :--- |
| **What You See (Look & Feel)** | Tailwind CSS & Modern CSS | Makes the website look clean and adjust smoothly to phone and computer screens. |
| **Interactive Controls** | JavaScript | Powers clickable buttons, slide-out menus, and instant updates without reloading. |
| **Text & Goal Editor** | TinyMCE | A familiar word-processing box to type and format comments and work targets. |
| **Behind the Scenes (Engine)** | CodeIgniter 4 (PHP) | Connects all pages, checks user permissions, and runs the official rating calculations. |
| **Information Storage** | MySQL / MariaDB | Safely stores all user accounts, uploaded targets, and final evaluation scores. |
| **Report Generator** | PhpSpreadsheet | Automatically builds and formats official government Excel files for printing. |

---

## Capstone Project Team

- **Kurt** — System Architecture, Backend Logic, and Security
- **Nanashi (`PeroroFaust`)** — User Experience, Mobile Design, and Interface Improvements

---

## Context and Guidelines

Built specifically for **Benguet State University (BSU)** in La Trinidad, Benguet.  
All evaluation steps, scoring formulas, and rating categories follow the official rules of the Philippine **Civil Service Commission (CSC Memorandum Circular No. 6, s. 2012)**.
