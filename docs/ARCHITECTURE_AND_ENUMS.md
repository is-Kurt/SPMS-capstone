# SPMS Architecture, Enums, and System Governance Reference

> [!IMPORTANT]
> **SYSTEM INVARIANTS FOR AI AGENTS & DEVELOPERS:**
> This document is the **authoritative single source of truth** for architectural design, enum definitions, database schemas, and evaluation lifecycles in the Benguet State University Strategic Performance Management System (BSU-SPMS).
> 
> **ARCHITECTURAL RULES:**
> 1. Use canonical PHP backed enums (`App\Enums\*`) for all system states. Do not introduce arbitrary or ad-hoc status strings.
> 2. Evaluation form tabs, commitments, measures, ratings, and accomplishments are stored as structured JSON within `document_folders.tabs`. The database does not use separate `documents` or `templates` tables.
> 3. Maintain the separation between macro semester switches (`rating_cycles.status`) and micro ratee workflows (`document_folders.status`).
> 4. Enforce Civil Service Commission (CSC) cascading gating (e.g., OPCR target approval prior to subordinate cascade).

---

## 1. Canonical PHP Enums (`app/Enums/`)

All system states, access roles, document classifications, and background processing statuses are defined as PHP 8.1+ backed string enums under the `App\Enums` namespace.

### 1.1 `RatingCycleStatus` (`app/Enums/RatingCycleStatus.php`)
Governs the **Macro Institutional Operational Lifecycle** of an entire evaluation semester (`rating_cycles.status`).

```php
namespace App\Enums;

enum RatingCycleStatus: string 
{
    case ACTIVE = 'active';
    case CLOSED = 'closed';
}
```

| Enum Case | Raw String Value | UI Badge Text | Badge Styling | Description |
|---|---|---|---|---|
| `ACTIVE` | `'active'` | `ACTIVE` | Emerald Pill | Rating cycle is open. Target setting and evaluation submission deadlines are actively enforced. Submissions and reviews proceed normally. |
| `CLOSED` | `'closed'` | `CLOSED / ARCHIVED` | Slate / Gray Pill | Rating cycle is closed. All scores, evaluations, and folders across the university are frozen and strictly read-only. |

#### Helper Methods
- `$status->label(): string` – Returns human-readable label (`Active`, `Closed`).
- `$status->isActive(): bool` – Returns `true` if active.
- `$status->isClosed(): bool` – Returns `true` if closed.

---

### 1.2 `FolderStatus` (`app/Enums/FolderStatus.php`)
Governs the **Micro Ratee Evaluation Lifecycle** of individual evaluation workspaces (`document_folders.status`).

```php
namespace App\Enums;

enum FolderStatus: string 
{
    // --- Phase 1: Target Setting Phase ---
    case DRAFT_TARGET     = 'draft_target';
    case TARGET_SUBMITTED = 'target_submitted';
    case TARGET_APPROVED  = 'target_approved';
    case TARGET_MISSED    = 'target_missed';

    // --- Phase 2: Accomplishment & Evaluation Phase ---
    case DRAFT_EVALUATION = 'draft_evaluation';
    case EVAL_SUBMITTED   = 'eval_submitted';
    case EVAL_APPROVED    = 'eval_approved';
    case UNEVALUATED      = 'unevaluated';
}
```

#### Status Lifecycle Table

| Phase | Enum Case | Raw String Value | UI Badge Text | Badge Styling | Description |
|---|---|---|---|---|---|
| **Phase 1** | `DRAFT_TARGET` | `'draft_target'` | `DRAFT TARGET` | Neutral Slate | Ratee is drafting target commitments (or revising after return). |
| **Phase 1** | `TARGET_SUBMITTED` | `'target_submitted'` | `TARGET SUBMITTED` | Amber / Sky | Targets submitted; awaiting reviews from supervisors & TWG. |
| **Phase 1** | `TARGET_APPROVED` | `'target_approved'` | `TARGET APPROVED` | Emerald / Success | Final target approval granted. Targets locked for the period. |
| **Phase 1** | `TARGET_MISSED` | `'target_missed'` | `MISSED DEADLINE` | Rose / Danger | Ratee missed the deadline window for target submission. |
| **Phase 2** | `DRAFT_EVALUATION` | `'draft_evaluation'` | `DRAFT EVALUATION` | Neutral Slate | Evaluation window open; ratee drafting accomplishments & self-ratings (or revising after return). |
| **Phase 2** | `EVAL_SUBMITTED` | `'eval_submitted'` | `EVALUATION SUBMITTED` | Amber / Sky | Accomplishments submitted; awaiting evaluator ratings & calibration. |
| **Phase 2** | `EVAL_APPROVED` | `'eval_approved'` | `EVALUATION APPROVED` | Emerald / Success | Numerical ratings verified, calibrated, and finalized. Cycle complete. |
| **Phase 2** | `UNEVALUATED` | `'unevaluated'` | `MISSED DEADLINE` | Rose / Danger | Ratee missed the deadline window for accomplishment submission. |

#### Helper Methods & Progress Tracker
- `FolderStatus::canonical(string $status): self` – Normalizes raw status strings to a canonical enum case.
- `$status->isTargetPhase(): bool` – Returns `true` if in Phase 1 (`draft_target`, `target_submitted`, `target_approved`, `target_missed`).
- `$status->isEvaluationPhase(): bool` – Returns `true` if in Phase 2.
- `$status->stepperIndex(): int` – Maps to the 4-step progress tracker on the dashboard:
  - **Step 1 (Target Setting)**: `draft_target`, `target_submitted`, `target_missed`
  - **Step 2 (Target Approved)**: `target_approved`
  - **Step 3 (Evaluation Phase)**: `draft_evaluation`, `eval_submitted`, `unevaluated`
  - **Step 4 (Final Rating)**: `eval_approved`

---

### 1.3 `EvaluationRoutingStatus` (`app/Enums/EvaluationRoutingStatus.php`)
Governs the **Review Authority Consensus** (`evaluation_routings.status`). Each evaluating supervisor and compliance reviewer (TWG / PMT) has an independent routing record tracking their sign-off.

```php
namespace App\Enums;

enum EvaluationRoutingStatus: string
{
    case DRAFT    = 'draft';
    case QUEUED   = 'queued';
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case RETURNED = 'returned';
}
```

| Enum Case | Raw String Value | UI Badge (Supervisor) | UI Badge (TWG / PMT) | Visual Pill |
|---|---|---|---|---|
| `DRAFT` | `'draft'` | `Drafting` | `Drafting` | Slate Pill |
| `QUEUED` | `'queued'` | `Awaiting Endorsement` | `Queued for Verification` | Indigo Pill + Clock Icon |
| `PENDING` | `'pending'` | `Pending Review` | `Pending Verification` | Amber Pill + Pulsing Dot |
| `APPROVED` | `'approved'` | `Approved` | `Approved` | Emerald Pill + Checkmark |
| `RETURNED` | `'returned'` | `Returned` | `Returned` | Rose Pill + Return Icon |

> [!NOTE]
> - `FolderStatus` tracks the folder container's phase (`target_approved` vs `eval_approved`).
> - `EvaluationRoutingStatus` tracks each evaluator's individual decision (`approved` vs `returned`) within that phase.

---

### 1.4 `UserRole` (`app/Enums/UserRole.php`)
Governs role-based authorization and access control (RBAC).

```php
namespace App\Enums;

enum UserRole: string
{
    case ADMIN      = 'Admin';
    case SUPERVISOR = 'Supervisor';
    case EMPLOYEE   = 'Employee';
    case TWG        = 'TWG';
    case PMT        = 'PMT';
    case HR         = 'HR';
}
```

| Role Case | Raw Value | Display Label | Primary Responsibilities |
|---|---|---|---|
| `ADMIN` | `'Admin'` | System Administrator | Creates rating cycles, defines institutional deadlines, releases cycles to executive leadership, archives/restores folders. Does not have a personal ratee evaluation form. |
| `SUPERVISOR` | `'Supervisor'` | Supervisor / Division Head | Evaluates subordinate commitments and ratings (Vice Presidents, Deans, Department Chairs, Unit Heads). Cascades institutional goals to their distribution teams. |
| `EMPLOYEE` | `'Employee'` | Faculty / Staff Employee | Drafts target commitments, submits accomplishments, uploads Means of Verification (MOVs), self-rates. |
| `TWG` | `'TWG'` | Technical Working Group | Quality assurance and compliance reviewer. Verifies calibration standards before final approval. |
| `PMT` | `'PMT'` | Performance Management Team | Institutional governance committee. Reviews university-wide metrics and handles performance appeals. |
| `HR` | `'HR'` | Human Resources (HRDO) | Manages plantilla records, units, and personnel accounts. |

---

### 1.5 `DocType` (`app/Enums/DocType.php`)
Governs Civil Service Commission (CSC) performance commitment forms.

```php
namespace App\Enums;

enum DocType: string
{
    case OPCR  = 'OPCR';
    case DPCR  = 'DPCR';
    case IPCR  = 'IPCR';
    case IPERF = 'IPERF';
}
```

| Case | Raw Value | Full Form Title | Target User Group | Structure & Key Columns |
|---|---|---|---|---|
| `OPCR` | `'OPCR'` | Office Performance Commitment and Review | University President, VPAA, VPAF | MFO/PAPs, Success Indicators, **Allotted Budget**, **Accountable Divisions**, Actual Accomplishments, Q/E/T Ratings |
| `DPCR` | `'DPCR'` | Department Performance Commitment and Review | Deans, Directors, Department Chairs, Unit Heads | MFO/PAPs, Success Indicators, **Allotted Budget**, **Accountable Individuals**, Actual Accomplishments, Q/E/T Ratings |
| `IPCR` | `'IPCR'` | Individual Performance Commitment and Review | Teaching Faculty, Regular Non-Teaching Staff | Core, Strategic, and Support Functions, Success Indicators, Actual Accomplishments, Q/E/T Ratings |
| `IPERF` | `'IPERF'` | Individual Performance Evaluation Review Form | Contract of Service (COS) and Job Order (JO) Staff | Simplified Core rating sheet |

---

### 1.6 Supporting Enums
- **`EmailStatus`** (`app/Enums/EmailStatus.php`): `'pending'`, `'sending'`, `'sent'`, `'failed'`.
- **`InvitationStatus`** (`app/Enums/InvitationStatus.php`): `'pending'`, `'accepted'`, `'expired'`.

---

## 2. Institutional Rating Cycle vs. Ratee Folder Lifecycle

The system separates macro institutional administration from individual evaluation workflows through two distinct entities:

```
┌──────────────────────────────────────────────────────────┐
│                   RATING CYCLE ENTITY                    │
│            Table: rating_cycles (status column)          │
│            Governed By: App\Enums\RatingCycleStatus      │
│            Allowed Values: 'active' | 'closed'           │
└────────────────────────────┬─────────────────────────────┘
                             │ Has many
                             ▼
┌──────────────────────────────────────────────────────────┐
│                 DOCUMENT FOLDERS ENTITY                  │
│       Table: document_folders (status column)            │
│       Governed By: App\Enums\FolderStatus (10 states)    │
│                                                          │
│  ├─ Root Master Folder (Admin Container)                 │
│  │    parent_folder_id = null, user_id = Admin           │
│  │                                                       │
│  ├─ Executive Folders (VPAA / VPAF)                      │
│  │    parent_folder_id = Admin Folder                    │
│  │                                                       │
│  └─ Subordinate Folders (Deans -> Chairs -> Faculty)    │
│       parent_folder_id = Supervisor Folder               │
└──────────────────────────────────────────────────────────┘
```

### 2.1 The Rating Cycle (`rating_cycles.status`)
- **Scope**: University-wide semester administration.
- **Governing Enum**: `App\Enums\RatingCycleStatus` (`active`, `closed`).
- **Operational Behavior**:
  - When `'active'`, submission deadlines are checked dynamically and users can edit their forms according to their active phase.
  - When `'closed'`, all evaluations, scores, and folders across the university are locked in read-only mode.
- **Model**: `App\Models\RatingCycleModel`.

### 2.2 The Folder Status (`document_folders.status`)
- **Scope**: User workspace / individual evaluation form.
- **Governing Enum**: `App\Enums\FolderStatus` (10 canonical phases).
- **Operational Behavior**:
  - Directs individual progression through target setting and accomplishment evaluation (`draft_target` ➔ `target_submitted` ➔ `target_approved` ➔ `draft_evaluation` ➔ `eval_submitted` ➔ `eval_approved`).
- **Admin Root Folder**: The Admin's root folder (`parent_folder_id = null`) coordinates the period and links to `rating_cycles.id`. Because Administrators supervise the cycle rather than undergoing performance rating, Admin accounts have no personal evaluation form.

---

## 3. Cascading Architecture & Distribution Rules

Cascading distributes institutional goals and commitments from top leadership down to departments, units, and individual staff.

### 3.1 Role Cascading Rules

| User Role | Document Type | Can Cascade? | Cascading Conditions & Gating Rules |
|---|---|---|---|
| **Admin** | N/A (Master Container) | **YES** | **Cycle Release**: Releases the evaluation cycle to the top-tier leadership team (Executive Leadership: VPAA & VPAF). |
| **Vice Presidents (VPAA, VPAF)** | **OPCR** | **YES** | **CSC SPMS Gating Rule**: OPCR targets must be submitted and **approved by the University President** before cascading to subordinate Deans and Unit Heads (`Folder.php:666`). |
| **Deans & Unit Heads** | **DPCR** | **YES** | Can cascade anytime during the **Target Setting Phase** (`draft_target`, `target_submitted`, `target_approved`) to their subordinate Department Chairs or Unit Staff. |
| **Department Chairs** | **DPCR** | **YES** | Can cascade to their faculty team during the Target Setting Phase. Cascades department goals directly into faculty IPCR blueprints. |
| **Faculty & Staff** | **IPCR / IPERF** | **NO** | Ratees without direct subordinates. The cascade management panel is hidden for these accounts. |

### 3.2 Routing Presets (Teams) Requirement

Cascading operates on team presets (`routing_presets` and `routing_preset_members`). When a supervisor clicks **Cascade / Release to Selected Team**, the system executes the following steps:
1. Links `document_folders.routing_preset_id` to the chosen team ID.
2. Iterates over each member of the preset team.
3. Creates a child folder (`document_folders`) with `parent_folder_id = activeFolder.id`.
4. Initializes the appropriate form structure (`tabs` JSON) based on the member's assigned `doc_type`.
5. Creates an `evaluation_routings` record linking the subordinate back to the cascading supervisor for review.
6. Dispatches notification alerts to all recipient members.

If a supervisor only has one team assigned, the UI dropdown automatically preselects that team.

---

## 4. Unified Document-Folder Architecture

SPMS employs a single-table document architecture centered on `document_folders`.

### 4.1 Architectural Invariants
1. **Single-Table Form Storage (`document_folders.tabs`)**:
   - All evaluation forms, target commitments, success indicators, budget allocations, categories, and accomplishments are stored directly in `document_folders.tabs` as structured JSON.
   - The database does not use separate `documents` or `templates` tables.
2. **`DocumentModel` Compatibility Adapter**:
   - `App\Models\DocumentModel` points to the `document_folders` table (`protected $table = 'document_folders';`).
   - Query filters referencing `'document_folder_id'` are automatically redirected to `'id'` via overridden `where()` / `whereIn()` methods.
3. **Normalized Foreign Keys**:
   - `evaluation_routings.folder_id` references `document_folders.id`.
   - `document_comments.folder_id` references `document_folders.id`.
   - `document_attachments.folder_id` references `document_folders.id`.
4. **Deterministic Seeding**:
   - Test seeders execute `DELETE FROM sqlite_sequence` in `MasterSeeder` to maintain predictable primary keys (`users.id = 1` for Admin, `2` for TWG, `3` for PMT, etc.).
