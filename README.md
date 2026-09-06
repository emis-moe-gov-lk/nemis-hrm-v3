# EMIS - Education Management Information System

A comprehensive Human Resource and Education Management platform built with a modern tech stack.

## 🚀 Technologies

- **Framework**: [Laravel 12+](https://laravel.com)
- **Frontend**: [Livewire 3](https://livewire.laravel.com) with [Flux UI](https://fluxui.dev)
- **Styling**: [Tailwind CSS](https://tailwindcss.com)
- **Permissions**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **Logging**: [Spatie Activitylog](https://spatie.be/docs/laravel-activitylog)
- **Reports**: [Maatwebsite Excel](https://docs.laravel-excel.com) & Various PDF Engines
- **Utilities**: Firebase JWT, Socialite, Simple QRCode, Spatie Browsershot

## 🛠️ Key Features

- **Dashboard**: Real-time analytics and geographic distribution visualization.
- **HR Management**: Complete tracking of Teachers, Principals, Directors, and other Educational Officers.
- **Institution Tracking**: Comprehensive database of educational institutions.
- **RBAC**: Fine-grained role-based access control.
- **Schedules**: Weekly planning and briefing management.
- **Dark Mode**: Premium, integrated dark mode support across all components.

## 💻 Local Development

1. **Clone the repository**
2. **Install dependencies**
    ```bash
    composer install
    npm install
    ```
3. **Setup environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    touch database/database.sqlite
    php artisan migrate --seed
    ```
4. **Run the application**

    ```bash
    # Terminal 1: Vite Dev Server
    npm run dev

    # Terminal 2: PHP Development Server
    php artisan serve
    ```

## 🐳 Docker

The project includes a Docker-based stack for Laravel (PHP-FPM), Nginx, and MySQL.

Default local ports are set to avoid conflict when your PC already runs Apache on `80` and MySQL on `3306`:

- app: `8080`
- mysql: `3308`

1. Create the Docker environment file
   ```bash
   cp .env.docker.example .env.docker
   ```
   Review `.env.docker` and update database, app, or WSO2 settings if needed.

2. (Optional) Override the host ports via a compose `.env` file or environment variables:
   ```bash
   echo "DOCKER_APP_PORT=8080" > .env
   echo "DOCKER_DB_PORT=3308" >> .env
   ```

3. Start the containers
   ```bash
   docker compose up -d --build
   ```

4. Seed the database (first run only — migrations run automatically at startup):
   ```bash
   docker compose exec app php artisan db:seed --force
   ```

5. Open the app at `http://localhost:8080`

   Default login: `superadmin@example.com` / `Password@123`

Useful commands:

```bash
docker compose down
docker compose logs -f
docker compose exec app php artisan migrate
docker compose exec app php artisan key:generate --show
```

## ⚙️ Ansible Deployment

An Ansible deployment bundle is available in [`ansible/`](ansible).

1. Update [`ansible/inventory/hosts.ini`](ansible/inventory/hosts.ini) with your server details.
2. Adjust deployment variables in [`ansible/group_vars/all.yml`](ansible/group_vars/all.yml).
3. Install the required collections:
   ```bash
   cd ansible
   ansible-galaxy collection install -r requirements.yml
   ```
4. Run the deploy playbook:
   ```bash
   ansible-playbook playbooks/deploy.yml
   ```

The playbook assumes a Debian/Ubuntu target host and will:

- install Docker and deployment dependencies
- sync the project to `/opt/cemis`
- generate a remote `.env.docker`
- build and start the stack with Docker Compose

## � API Documentation

The project includes interactive API documentation powered by [Scramble](https://scramble.dedoc.co), which automatically generates OpenAPI documentation for your API.

- **Interactive UI**: [{{APP_URL}}/docs/api]({{APP_URL}}/docs/api)
- **OpenAPI Specification**: `api.json`

The documentation features a "Try It" functionality, allowing for real-time testing of the API endpoints directly from the browser.

## �📄 License

This project is licensed under the MIT license.
