# HTZone Sales Landing Page

A dynamic product showcase featuring carousels and a filterable product grid.

## Requirements

- Docker
- Docker Compose

## Quick Start

1. Clone the repository:
```bash
git clone <repository-url>
cd htzone-sales
```

2. Build and start the containers:
```bash
docker-compose up -d
```

3. Initialize the database:
```bash
docker-compose exec web php init_database.php
```

4. Access the application:
```
http://localhost:8080
```

## Development

The project uses Docker for development. The following volumes are mounted:
- `./:/var/www/html`: Project files
- `./database:/var/www/html/database`: SQLite database
- `./logs:/var/www/html/logs`: Application logs

## Structure

```
.
├── ajax/               # AJAX handlers
├── class/             # PHP classes
├── database/          # SQLite database
├── logs/              # Application logs
├── static/            # Static assets
│   ├── css/          # Stylesheets
│   └── js/           # JavaScript files
├── .htaccess         # Apache configuration
├── docker-compose.yml # Docker Compose configuration
├── Dockerfile        # Docker configuration
├── index.php         # Main application file
└── README.md         # This file
```

## Maintenance

The database is automatically updated every 6 hours via a cron job. You can manually trigger an update by running:

```bash
docker-compose exec web php update_database.php
```

## Security

- Directory listing is disabled
- Sensitive files are protected
- XSS protection is enabled
- Clickjacking protection is enabled
- Content-Type sniffing is prevented
