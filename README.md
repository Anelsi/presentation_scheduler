# Presentation Scheduler

PHP/MySQL presentation scheduling project.

## Configuration

Create a local environment file from the committed template:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Use `.env` for local settings such as database credentials, ports, `APP_BASE_URL`, and optional AWS S3 values. Do not commit `.env`.

## Run With Docker

```bash
docker compose up --build
```

Open:

```text
http://localhost:3000
```

Docker creates the MySQL schema and sample data on the first database startup. If the Docker database volume already exists and you need to recreate the sample data from scratch, reset it with:

```bash
docker compose down -v
docker compose up --build
```

## Run With XAMPP

1. Copy the project into the chosen XAMPP web folder.
2. Copy `.env.example` to `.env`.
3. Update the XAMPP database values in `.env`.
4. Set `APP_BASE_URL` to the local project URL, for example:

```env
APP_BASE_URL=http://localhost:3000/w26/presentation_scheduler
```

5. Create and seed the database:

```bash
php database/seed.php
```

If the `php` command is not available globally, run the command from the XAMPP shell or import `database/schema.sql` and `database/seed.sql` manually in phpMyAdmin.

## Optional S3

AWS S3 is optional. Leave `AWS_BUCKET`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, and `AWS_SESSION_TOKEN` empty to run the project locally without external services.
