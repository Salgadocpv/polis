# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Polis Engenharia is a PHP-based management system for an engineering company. It's a web application that runs on XAMPP/Apache with MySQL database, featuring client management, employee management, projects, and calendar functionality. The application includes PWA (Progressive Web App) capabilities with service worker and manifest.json.

## Development Environment

### Prerequisites
- XAMPP or similar (Apache + PHP + MySQL)
- Web server running on localhost
- MySQL database named `polis_db`

### Database Setup
Run the SQL schema from `sql/create_tables.sql` to create the required tables:
- `clientes` (clients)
- `colaboradores` (employees) 
- `projetos` (projects)
- `eventos` (calendar events)
- `usuarios` (system users)

### Running the Application
1. Start XAMPP services (Apache, MySQL)
2. Place project in `htdocs/polis/` directory
3. Import database schema from `sql/create_tables.sql`
4. Access via `http://localhost/polis/`

## Architecture

### File Structure
```
/api/              - PHP API endpoints for AJAX operations
  conexao.php      - Database connection configuration
  login.php        - User authentication
  clientes.php     - Client management API
  colaboradores.php - Employee management API  
  projetos.php     - Project management API
  eventos.php      - Calendar events API
  logout.php       - Session termination

/assets/css/       - Stylesheets
  style.css        - Main CSS with CSS custom properties

/includes/         - PHP includes
  header.php       - Top navigation bar
  sidebar.php      - Left sidebar menu

/listas/          - List/table views
  lista_clientes.php
  lista_colaboradores.php
  lista_projetos.php

/registros/       - Registration forms
  registrar_cliente.php
  registrar_colaborador.php
  registrar_projeto.php

/img/colaboradores/ - Employee photos storage
```

### Authentication & Sessions
- Session-based authentication using PHP sessions
- Login endpoint: `api/login.php` (POST JSON)
- Session validation on protected pages
- Logout: `api/logout.php`

### Database Connection
- Host: localhost
- User: root
- Password: (empty)
- Database: polis_db
- Connection file: `api/conexao.php`

### Frontend Architecture
- Vanilla JavaScript with Fetch API for AJAX
- No external JavaScript frameworks
- CSS custom properties for theming
- Font Awesome icons
- Google Fonts (Inter)
- PWA features with service worker

### CSS Architecture
Uses CSS custom properties in `:root` for consistent theming:
- `--cor-principal`: #012A4A (primary blue)
- `--cor-secundaria`: #144566 (secondary blue)
- `--cor-vibrante`: #00B4D8 (accent cyan)
- `--cor-fundo-card`: #EAEAEA (card background)

### Navigation Structure
- Fixed top header with logo, date/time, user info
- Collapsible left sidebar with main navigation
- Mobile-responsive with overlay for sidebar

### API Endpoints
All API endpoints return JSON and expect JSON input where applicable:
- `POST /api/login.php` - User authentication
- CRUD operations for clientes, colaboradores, projetos, eventos

## Development Notes

### Code Style
- PHP files use session validation at top of protected pages
- HTML uses semantic structure with accessibility considerations
- CSS follows BEM-like naming for components
- JavaScript uses modern ES6+ features with async/await

### Database Design
- Auto-incrementing IDs for all entities
- Foreign key relationships (projects -> clients)
- TIMESTAMP fields for audit trails
- UTF-8 charset for proper Portuguese character support

### Security Considerations
- Password hashing using PHP's `password_verify()`
- SQL prepared statements to prevent injection
- Session-based authentication
- Input validation on API endpoints

### PWA Features
- Service worker registration in `service-worker.js`
- Web app manifest in `manifest.json`
- Install prompt handling
- Offline capability considerations