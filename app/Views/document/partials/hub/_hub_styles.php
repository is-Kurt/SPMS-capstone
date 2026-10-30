                <style>
                    /* BSU SPMS Official Paper Hub - Adaptive Light & Dark Styles */
                    .spms-hub-card {
                        background-color: transparent;
                        border: none;
                        border-radius: 0;
                        padding: 0;
                        box-shadow: none;
                        position: relative;
                        overflow: visible;
                        width: 100%;
                    }
                    .dark .spms-hub-card {
                        background-color: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                    }
                    @media (min-width: 640px) {
                        .spms-hub-card {
                            padding: 0 !important;
                        }
                    }
                    @media (min-width: 1024px) {
                        .spms-hub-card {
                            padding: 0 !important;
                        }
                    }

                        .spms-hub-header-wrap {
                            display: flex;
                            flex-direction: column;
                            gap: 4px;
                            margin-bottom: 20px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-header-wrap {
                                flex-direction: row;
                                justify-content: space-between;
                                align-items: flex-start;
                                gap: 16px;
                                margin-bottom: 28px;
                            }
                        }

                        .spms-hub-header-main {
                            flex: 1;
                            min-width: 0;
                        }

                        .spms-hub-kicker-row {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            width: 100%;
                            gap: 8px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-kicker-row {
                                display: block;
                            }
                        }

                        .spms-hub-kicker {
                            color: #71717a;
                            font-size: 10px;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 0;
                            display: block;
                        }
                        .dark .spms-hub-kicker {
                            color: #a1a1aa !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-kicker {
                                font-size: 11px;
                                margin-bottom: 4px;
                            }
                        }

                        .spms-hub-title {
                            color: #0f172a;
                            font-size: 18px;
                            font-weight: 800;
                            letter-spacing: -0.01em;
                            line-height: 1.25;
                            margin: 0;
                        }
                        .dark .spms-hub-title {
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-title {
                                font-size: 24px;
                                margin: 0;
                            }
                        }
                        @media (min-width: 1024px) {
                            .spms-hub-title {
                                font-size: 27px;
                            }
                        }

                        .spms-hub-subtitle {
                            color: #64748b;
                            font-size: 11px;
                            font-weight: 500;
                            margin-top: 2px;
                            margin-bottom: 0;
                        }
                        .dark .spms-hub-subtitle {
                            color: #a1a1aa !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-subtitle {
                                font-size: 13px;
                                margin-top: 6px;
                            }
                        }

                        .spms-hub-subtitle-val {
                            color: #0f172a;
                            font-weight: 700;
                        }
                        .dark .spms-hub-subtitle-val {
                            color: #ffffff !important;
                        }

                        .spms-hub-status-pill {
                            background-color: #f4fdf7;
                            border: 1px solid #bbf7d0;
                            color: #166534;
                            border-radius: 9999px;
                            padding: 3px 8px;
                            font-size: 9px;
                            font-weight: 800;
                            letter-spacing: 0.05em;
                            text-transform: uppercase;
                            display: inline-flex;
                            align-items: center;
                            gap: 5px;
                            white-space: nowrap;
                        }
                        .dark .spms-hub-status-pill {
                            background-color: #0e291e !important;
                            border: 1px solid #144630 !important;
                            color: #86efac !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-pill {
                                padding: 6px 14px;
                                font-size: 11px;
                                gap: 8px;
                            }
                        }

                        .spms-hub-status-pill-mobile {
                            display: inline-flex !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-pill-mobile {
                                display: none !important;
                            }
                        }

                        .spms-hub-status-pill-desktop {
                            display: none !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-pill-desktop {
                                display: block !important;
                            }
                        }

                        .spms-hub-status-dot {
                            width: 5px;
                            height: 5px;
                            border-radius: 9999px;
                            background-color: #16a34a;
                            display: inline-block;
                        }
                        .dark .spms-hub-status-dot {
                            background-color: #22c55e !important;
                        }

                        /* Status Pill Color Variants */
                        .spms-hub-status-pill.is-danger {
                            background-color: #fef2f2 !important;
                            border-color: #fecaca !important;
                            color: #991b1b !important;
                        }
                        .dark .spms-hub-status-pill.is-danger {
                            background-color: #2a1215 !important;
                            border-color: #4c1d24 !important;
                            color: #fca5a5 !important;
                        }
                        .spms-hub-status-pill.is-danger .spms-hub-status-dot {
                            background-color: #ef4444 !important;
                        }

                        .spms-hub-status-pill.is-warning {
                            background-color: #fffbeb !important;
                            border-color: #fde68a !important;
                            color: #92400e !important;
                        }
                        .dark .spms-hub-status-pill.is-warning {
                            background-color: #2b1d0c !important;
                            border-color: #53320d !important;
                            color: #fcd34d !important;
                        }
                        .spms-hub-status-pill.is-warning .spms-hub-status-dot {
                            background-color: #f59e0b !important;
                        }

                        .spms-hub-status-pill.is-info {
                            background-color: #f0f9ff !important;
                            border-color: #bae6fd !important;
                            color: #075985 !important;
                        }
                        .dark .spms-hub-status-pill.is-info {
                            background-color: #0c2135 !important;
                            border-color: #163b5c !important;
                            color: #7dd3fc !important;
                        }
                        .spms-hub-status-pill.is-info .spms-hub-status-dot {
                            background-color: #0ea5e9 !important;
                        }

                        .spms-hub-status-pill.is-neutral {
                            background-color: #f8fafc !important;
                            border-color: #e2e8f0 !important;
                            color: #475569 !important;
                        }
                        .dark .spms-hub-status-pill.is-neutral {
                            background-color: #1e1e24 !important;
                            border-color: #33333d !important;
                            color: #94a3b8 !important;
                        }
                        .spms-hub-status-pill.is-neutral .spms-hub-status-dot {
                            background-color: #64748b !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-dot {
                                width: 7px;
                                height: 7px;
                            }
                        }

                        .spms-hub-tracker-label {
                            color: #64748b;
                            font-size: 10.5px;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-top: 18px;
                            margin-bottom: 12px;
                            display: block;
                        }
                        .dark .spms-hub-tracker-label {
                            color: #a1a1aa !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tracker-label {
                                font-size: 11px;
                                margin-top: 28px;
                                margin-bottom: 16px;
                            }
                        }

                        .spms-hub-stepper-row {
                            display: flex;
                            align-items: center;
                            gap: 6px;
                            overflow-x: auto;
                            padding-bottom: 8px;
                            margin-bottom: 18px;
                            -webkit-overflow-scrolling: touch;
                            scrollbar-width: thin;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stepper-row {
                                gap: 12px;
                                padding-bottom: 6px;
                                margin-bottom: 28px;
                            }
                        }

                        .spms-hub-stepper-arrow {
                            width: 28px;
                            height: 12px;
                            flex-shrink: 0;
                            margin: 0 4px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stepper-arrow {
                                width: 36px;
                                height: 12px;
                                margin: 0 6px;
                            }
                        }
                        @media (min-width: 1280px) {
                            .spms-hub-stepper-arrow {
                                width: 48px;
                                height: 12px;
                                margin: 0 8px;
                            }
                        }

                        .spms-hub-circle-active {
                            width: 26px;
                            height: 26px;
                            border-radius: 9999px;
                            background-color: #f59e0b;
                            border: none;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }

                        .spms-hub-circle-active-dot {
                            width: 8px;
                            height: 8px;
                            border-radius: 9999px;
                            background-color: #ffffff;
                        }
                        .dark .spms-hub-circle-active-dot {
                            background-color: #18181b !important;
                        }

                        .spms-hub-circle-inactive {
                            width: 26px;
                            height: 26px;
                            border-radius: 9999px;
                            border: 1.5px solid #cbd5e1;
                            background-color: #f8fafc;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }
                        .dark .spms-hub-circle-inactive {
                            border: 1.5px solid #3f3f46 !important;
                            background-color: transparent !important;
                        }

                        .spms-hub-circle-completed {
                            width: 26px;
                            height: 26px;
                            border-radius: 9999px;
                            background-color: #10b981;
                            border: none;
                            color: #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }

                        .spms-hub-step-text-active {
                            color: #0f172a;
                            font-size: 11.5px;
                            font-weight: 700;
                            white-space: nowrap;
                            margin-left: 6px;
                        }
                        .dark .spms-hub-step-text-active {
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-step-text-active {
                                font-size: 13px;
                                margin-left: 8px;
                            }
                        }

                        .spms-hub-step-text-inactive {
                            color: #94a3b8;
                            font-size: 11.5px;
                            font-weight: 500;
                            white-space: nowrap;
                            margin-left: 6px;
                        }
                        .dark .spms-hub-step-text-inactive {
                            color: #a1a1aa !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-step-text-inactive {
                                font-size: 13px;
                                margin-left: 8px;
                            }
                        }

                        .spms-hub-stat-grid {
                            display: flex;
                            flex-direction: column;
                            gap: 8px;
                            margin-bottom: 14px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stat-grid {
                                display: grid;
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                                gap: 12px;
                                margin-bottom: 22px;
                            }
                            .spms-hub-stat-grid.spms-hub-stat-grid-2 {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }
                        @media (min-width: 768px) {
                            .spms-hub-stat-grid {
                                gap: 16px;
                                margin-bottom: 28px;
                            }
                        }

                        .spms-hub-stat-tile {
                            background-color: #f8fafc;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            padding: 11px 14px;
                            display: flex;
                            flex-direction: row;
                            justify-content: space-between;
                            align-items: center;
                            gap: 12px;
                            min-height: auto;
                            min-width: 0;
                            overflow: hidden;
                        }
                        .dark .spms-hub-stat-tile {
                            background-color: #27272a !important;
                            border: 1px solid #27272a !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stat-tile {
                                flex-direction: column;
                                justify-content: space-between;
                                align-items: flex-start;
                                padding: 14px 14px;
                                border-radius: 14px;
                                min-height: 115px;
                                gap: 0;
                            }
                        }
                        @media (min-width: 1024px) {
                            .spms-hub-stat-tile {
                                padding: 16px 16px;
                                min-height: 125px;
                            }
                        }

                        .spms-hub-tile-label {
                            color: #64748b;
                            font-size: 10.5px;
                            font-weight: 600;
                            margin: 0;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                        .dark .spms-hub-tile-label {
                            color: #a1a1aa !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-label {
                                font-size: 11px;
                            }
                        }

                        .spms-hub-tile-value {
                            color: #0f172a;
                            font-size: 12px;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                            font-variant-numeric: tabular-nums;
                            margin-top: 2px;
                            margin-bottom: 0;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                        .dark .spms-hub-tile-value {
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-value {
                                font-size: 12.5px;
                                margin-top: 4px;
                            }
                        }
                        @media (min-width: 1024px) {
                            .spms-hub-tile-value {
                                font-size: 13px;
                                margin-top: 5px;
                            }
                        }

                        .spms-hub-window-pill {
                            background-color: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            color: #047857;
                            font-size: 9.5px;
                            font-weight: 800;
                            padding: 3px 8px;
                            border-radius: 9999px;
                            display: inline-flex;
                            align-items: center;
                            gap: 5px;
                            width: fit-content;
                            margin-top: 0;
                            white-space: nowrap;
                            flex-shrink: 0;
                        }
                        .dark .spms-hub-window-pill {
                            background-color: #0e291e !important;
                            border: 1px solid #144630 !important;
                            color: #86efac !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-window-pill {
                                font-size: 11px;
                                padding: 4px 12px;
                                gap: 7px;
                                margin-top: 14px;
                            }
                        }

                        .spms-hub-tile-dash {
                            width: 20px;
                            height: 2px;
                            background-color: #cbd5e1;
                            border-radius: 9999px;
                            margin-top: 0;
                            flex-shrink: 0;
                        }
                        .dark .spms-hub-tile-dash {
                            background-color: #3f3f46 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-dash {
                                width: 34px;
                                height: 3px;
                                margin-top: 20px;
                            }
                        }

                        .spms-hub-status-text {
                            font-size: 9.5px;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                            display: block;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            margin-top: 0;
                            flex-shrink: 0;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-text {
                                font-size: 11px;
                                margin-top: 8px;
                            }
                        }

                        .spms-hub-tile-btn {
                            background-color: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            color: #047857;
                            font-size: 11px;
                            font-weight: 600;
                            padding: 6px 12px;
                            border-radius: 8px;
                            cursor: pointer;
                            width: fit-content;
                            margin-top: 0;
                            text-decoration: none;
                            display: inline-block;
                            transition: all 0.15s ease;
                            white-space: nowrap;
                            flex-shrink: 0;
                        }
                        .spms-hub-tile-btn:hover {
                            background-color: #d1fae5;
                            color: #065f46;
                        }
                        .dark .spms-hub-tile-btn {
                            background-color: #27272a !important;
                            border: 1px solid #3f3f46 !important;
                            color: #34d399 !important;
                        }
                        .dark .spms-hub-tile-btn:hover {
                            background-color: #3f3f46 !important;
                            color: #ffffff !important;
                            border-color: #52525b !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-btn {
                                font-size: 12px;
                                padding: 6px 16px;
                                margin-top: 14px;
                            }
                        }

                        .spms-hub-actions-bar {
                            display: grid;
                            grid-template-columns: repeat(2, minmax(0, 1fr));
                            gap: 8px;
                            width: 100%;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-actions-bar {
                                display: flex;
                                flex-direction: row;
                                flex-wrap: wrap;
                                align-items: center;
                                gap: 6px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-actions-bar {
                                gap: 10px;
                            }
                        }

                        .spms-hub-btn-primary {
                            grid-column: 1 / -1;
                            width: 100%;
                            background-color: #f59e0b;
                            color: #000000;
                            font-size: 11px;
                            font-weight: 900;
                            letter-spacing: 0.04em;
                            text-transform: uppercase;
                            padding: 11px 16px;
                            border-radius: 9px;
                            border: none;
                            cursor: pointer;
                            text-decoration: none;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            transition: all 0.15s ease;
                            white-space: nowrap;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-btn-primary {
                                grid-column: auto;
                                width: auto;
                                font-size: 10.5px;
                                padding: 10px 12px;
                                border-radius: 8px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-btn-primary {
                                font-size: 11.5px;
                                padding: 12px 18px;
                                border-radius: 9px;
                            }
                        }
                        .spms-hub-btn-primary:hover {
                            background-color: #e08e06;
                            color: #000000;
                        }
                        .spms-hub-banner {
                            background-color: #f4f4f5;
                            border: 1px solid #e4e4e7;
                            color: #27272a;
                        }
                        .dark .spms-hub-banner {
                            background-color: #18181b !important;
                            border: 1px solid #27272a !important;
                            color: #d4d4d8 !important;
                        }
                        .spms-hub-banner-icon {
                            background-color: #fef3c7;
                            border: 1px solid #fde68a;
                            color: #d97706;
                        }
                        .dark .spms-hub-banner-icon {
                            background-color: #27272a !important;
                            border: 1px solid #3f3f46 !important;
                            color: #f59e0b !important;
                        }
                        .spms-hub-banner-title {
                            color: #09090b;
                        }
                        .dark .spms-hub-banner-title {
                            color: #ffffff !important;
                        }

                        .spms-hub-btn-secondary {
                            width: 100%;
                            background-color: #f1f5f9;
                            border: 1px solid #cbd5e1;
                            color: #1e293b;
                            font-size: 10.5px;
                            font-weight: 700;
                            padding: 8px 4px;
                            border-radius: 8px;
                            cursor: pointer;
                            text-decoration: none;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                            transition: all 0.15s ease;
                            white-space: nowrap;
                            box-sizing: border-box;
                            line-height: 1.25;
                        }
                        .dark .spms-hub-btn-secondary {
                            background-color: #27272a !important;
                            border: 1px solid #3f3f46 !important;
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-btn-secondary {
                                width: auto;
                                font-size: 10.5px;
                                padding: 10px 10px;
                                border-radius: 8px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-btn-secondary {
                                font-size: 11px;
                                padding: 11px 14px;
                                border-radius: 9px;
                            }
                        }
                        .spms-hub-btn-secondary:hover {
                            background-color: #e2e8f0;
                            border-color: #94a3b8;
                            color: #0f172a;
                        }
                        .dark .spms-hub-btn-secondary:hover {
                            background-color: #3f3f46 !important;
                            border-color: #52525b !important;
                            color: #ffffff !important;
                        }

                        /* Standalone secondary button in actions bar: spans full width on mobile, auto width on desktop */
                        .spms-hub-actions-bar > .spms-hub-btn-secondary:last-child:nth-child(2),
                        .spms-hub-btn-secondary-full {
                            grid-column: span 3;
                            width: 100%;
                            padding: 10px 16px;
                            font-size: 11px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-actions-bar > .spms-hub-btn-secondary:last-child:nth-child(2),
                            .spms-hub-btn-secondary-full {
                                width: auto;
                                grid-column: auto;
                                padding: 10px 12px;
                                font-size: 10.5px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-actions-bar > .spms-hub-btn-secondary:last-child:nth-child(2),
                            .spms-hub-btn-secondary-full {
                                padding: 11px 14px;
                                font-size: 11px;
                            }
                        }

                        @media (max-width: 1023px) {
                            #bottom-sheet.spms-bottom-sheet-collapsed {
                                transform: translateY(calc(100% - 46px)) !important;
                            }
                            #bottom-sheet.spms-bottom-sheet-expanded {
                                transform: translateY(0) !important;
                            }
                        }

                    </style>
