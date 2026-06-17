# Interview Internship — Laravel App

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm

## How to Run

**1. Clone the repository**
```bash
git clone https://github.com/athallaabhi/InterviewInternship.git
cd InterviewInternship
```

**2. Install PHP dependencies**
```bash
composer install
```

**3. Install Node dependencies**
```bash
npm install
```

**4. Set up environment**
```bash
cp .env.example .env
php artisan key:generate
```

**5. Set up the database**
```bash
php artisan migrate
```

**6. Build frontend assets**
```bash
npm run build
```

**7. Start the development server**
```bash
php artisan serve
```

The app will be available at `http://localhost:8000`.

> For local development you can run `npm run dev` alongside `php artisan serve` to enable hot-reloading.
