# SealSphere – Industrial ERP & Inventory Management System

*Built for MS Engineering – Mechanical Seal Manufacturing*

## Project Overview

SealSphere is a full-stack Enterprise Resource Planning (ERP) system built for **MS Engineering**, a business manufacturing and supplying industrial mechanical seals — including Pusher Seals, Cartridge Seals, and O-Rings & Gaskets. The system was designed to solve a real operational problem: technical, engineering-focused products are difficult to sell and manage through generic tools, since buyers need to understand exact shapes and specifications, and the business needs live visibility into stock, clients, and revenue rather than static spreadsheets.

Unlike a typical portfolio CRUD project, SealSphere is built as a working two-sided platform: a **public-facing storefront** where potential clients can browse the product catalog, inspect products in an interactive 360° viewer, and submit inquiries — and a **secured admin backend** where the business owner manages inventory, clients, orders, invoicing, and revenue analytics from a single dashboard.

## Problem Statement

Small industrial manufacturers frequently operate with disconnected tools: inventory tracked manually, client inquiries arriving through phone calls with no record-keeping, and no way to generate a professional invoice without third-party software. For a product category like mechanical seals — where the exact profile, fit, and material of a part matters — static product photos on a basic website also fail to communicate what a buyer actually needs to know before ordering. SealSphere addresses both problems: it centralizes the full business workflow (inquiry → order → invoice) into one system, and it gives technical products a genuinely useful 360° visual inspection tool built directly into the browser.

## Key Features

### Public Storefront (`index.php`)
- **Live ERP Stats Bar** — The homepage pulls real-time counts directly from the database (total clients, total products, total orders) via `mysqli_num_rows()` queries, so the storefront always reflects the current state of the business rather than static numbers.
- **Dynamic Product Catalog with Category Filtering** — Products are fetched live from MySQL (`SELECT * FROM products`) alongside a distinct category list, allowing visitors to filter by product type (Pusher Seals, Cartridge Seals, O-Rings & Gaskets).
- **360° Interactive Product Viewer** — Each product card includes a "360° View" button that opens a Bootstrap modal (`frontend3DModal`) and applies a custom CSS keyframe animation (`rotateY(0deg)` to `rotateY(360deg)`) to visually rotate the product image, giving buyers a genuine sense of the part's form without needing a 3D model file or a WebGL library.
- **AJAX-Powered Inquiry Form** — Visitors can submit inquiries (name, email, company, category, message) without a page reload; the form posts to the backend, which inserts directly into the `inquiries` table using escaped input before returning a success/error response consumed by JavaScript.

### Admin Dashboard (`dashboard.php`)
- **Session-Protected Access** — Every admin page starts with a security check that redirects unauthenticated users to the login page before any data is queried.
- **Live Business Metrics** — The dashboard calculates total products, revenue from paid orders, pending order count, and total inquiries directly from live queries each time the page loads.
- **Recent Activity Feed** — Pulls the 5 most recent orders (joined across `orders`, `clients`, and `products`) and the 5 most recent inquiries, giving the admin an at-a-glance view of what needs attention.
- **Dark Sidebar Navigation** — A fixed, full-height dark sidebar with the company logo and menu items provides consistent navigation across all admin pages.

### Product, Client & Order Management
- **Full Product CRUD** — `add_product.php`, `edit_product.php`, and `delete_product.php` handle the complete lifecycle of inventory items, including category, material, price, and stock quantity.
- **Client Management** (`clients.php`, `add_client.php`) — Stores company name, contact person, email, phone, and status for every business client.
- **Order Management** (`orders.php`, `add_order.php`, `update_status.php`) — Orders link a client to a product with quantity and total amount, and support status transitions (Pending → Paid) that flow directly into the revenue calculations shown on the dashboard.

### Invoice Generation (`print_bill.php`)
Generates a print-ready, professionally styled tax invoice for any order, pulling the client's company details, contact info, and product pricing through a three-table JOIN across `orders`, `clients`, and `products`. The invoice layout includes the company logo, a styled header, and a clean print-optimized design — turning what would normally require external invoicing software into a built-in feature.

### Analytics Module (`analytics.php`)
A dedicated financial analytics page that computes:
- Total paid revenue vs. total pending revenue
- Top 5 products by revenue, grouped and ranked directly in SQL
- Top 5 clients by order count, grouped and ranked directly in SQL

This data is structured specifically to feed into chart visualizations, giving the business owner a clear picture of which products and clients drive the most revenue.

### Inquiry Tracking (`inquiries.php`)
A dedicated module to review and manage all inquiries submitted through the public storefront's AJAX form, allowing the business to follow up on leads before they convert into confirmed orders.

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP with mysqli (both procedural and object-oriented usage across different modules) |
| Database | MySQL — schema covering products, clients, orders, and inquiries |
| Frontend | HTML5, CSS3, Bootstrap 5.3 |
| Fonts / Icons | Google Fonts (Outfit, Montserrat, Poppins), Font Awesome 6.4 |
| Animation | AOS (Animate On Scroll) library, custom CSS keyframes for the 360° viewer |
| Interactivity | Vanilla JavaScript with AJAX-driven inquiry submission and modal control |

## Database Schema Overview

| Table | Purpose |
|---|---|
| products | Inventory: name, category, material, price, quantity, image |
| clients | Company name, contact person, email, phone, status |
| orders | Links client_id + product_id, tracks quantity, total amount, and status |
| inquiries | Captures name, email, company, category, product reference, and message from the public form |

Relationships between orders, clients, and products are used throughout the admin dashboard, analytics, and invoice generation via SQL JOIN queries, keeping client and product data normalized rather than duplicated in every order record.

## Project Structure

```
seal_erp/
├── index.php                 → Public storefront: catalog, live stats, 360° viewer, inquiry form
├── admin_login.php            → Admin authentication (session-based)
├── dashboard.php                → Admin dashboard: live metrics, recent orders/inquiries
├── products.php                  → Product listing (admin)
├── add_product.php                → Add new product
├── edit_product.php                → Edit existing product
├── delete_product.php               → Remove product
├── clients.php                       → Client management
├── add_client.php                     → Add new client
├── orders.php                          → Order management
├── add_order.php                        → Create new order
├── update_status.php                     → Update order status
├── print_bill.php                         → Print-ready invoice generation
├── analytics.php / analysis.php            → Revenue & performance analytics
├── inquiries.php                            → Inquiry management
├── connection.php                            → Centralized DB connection
├── logout.php
├── seal_erp.sql                               → Database schema & seed data
└── assets/                                      → Product images and company logo
```

## Architecture & Design Decisions

The public storefront and admin backend are deliberately built as two distinct experiences sharing the same database: the homepage is fully public and optimized for presentation (live stats, catalog, 360° viewer, inquiry capture), while every admin-facing file enforces a session check before executing any query — a simple but effective access-control pattern given the project's scale.

The decision to build the 360° product viewer with pure CSS rotateY keyframes instead of a JavaScript 3D library was intentional: it keeps the feature dependency-free and fast-loading, which matters for a storefront where product images already carry meaningful file sizes.

The analytics module's queries are pre-aggregated in PHP (grouped and limited directly in SQL) rather than pulling raw data and processing it client-side, keeping the chart-rendering logic on the frontend simple and letting the database do what it's best at.

## Challenges Faced & Solutions

Representing mechanically precise products like seals through static catalog photography is genuinely limiting for buyers who need to understand a part's geometry. Building a 360° inspection tool without a full 3D asset pipeline required finding a lightweight technique — CSS rotation animation over a 2D product image — that approximates the value of true 3D inspection using only assets the business already had.

Connecting analytics, invoicing, and the dashboard's recent-activity feed all required careful JOIN design across orders, clients, and products to avoid duplicating data while still surfacing readable, human-friendly information instead of raw foreign key IDs.

## Learning Outcomes

- Designing a relational schema for a real B2B workflow spanning inquiry, order, and invoicing stages
- Writing and optimizing SQL JOIN and GROUP BY queries for dashboards, analytics, and invoice generation
- Implementing session-based access control across multiple admin-only PHP files
- Building an AJAX-driven form flow without a page reload, including server-side validation and escaping
- Creating a genuinely useful interactive feature (360° viewer) using lightweight CSS/JS instead of defaulting to a heavier library
- Structuring a project so a public marketing-style frontend and a secured operational backend can coexist cleanly on the same codebase

## Future Enhancements

- Migrate password storage to a properly hashed and salted format
- Add email notifications when a new inquiry is submitted
- Integrate a payment gateway to accept order deposits directly from the storefront
- Introduce role-based permissions for multiple admin/staff accounts
- Extend the 360° viewer with zoom and cross-section views for more detailed inspection
- Move inline style blocks into shared CSS files to reduce duplication across pages

---

*Independently designed and developed to translate a real, industry-specific business process — mechanical seal manufacturing and distribution — into a functional, structured software system, combining live business data, financial analytics, and a technically creative product-visualization feature.*
