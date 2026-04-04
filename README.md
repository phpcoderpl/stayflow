# StayFlow

System rezerwacji apartamentow online. Wlasna alternatywa dla Booking.com z bezposrednimi platnosciami.

## Stack

- **Backend:** Laravel 13 + SQLite
- **Frontend:** Vue 3 + Inertia 3 + Tailwind v4 + vue-i18n (PL/EN)
- **Deploy:** Railway (Nixpacks + FrankenPHP)

## Funkcjonalnosci

- Panel admina — zarzadzanie obiektami, zdjecia, udogodnienia
- Rezerwacje — kalendarz, blokady dat, ceny sezonowe, min. nocy
- Platnosci online — Stripe / HotPay (BLIK, karty, przelewy)
- Synchronizacja kalendarzy — iCal import/export (Booking.com, Airbnb, Google Calendar)
- Maile — potwierdzenia, przypomnienia, SMTP konfigurowalny z panelu
- Widgety HTML — kalendarz, formularz rezerwacji do osadzania
- SEO — Schema.org, meta tagi, sitemap, Google Analytics
- Wielojezycznosc — PL + EN
- Guest portal — gosc sprawdza rezerwacje przez unikalny link
- Raporty — przychody, oblozenie, per-obiekt i zbiorcze
- Import z Booking.com — podajesz URL, system kopiuje dane

## Wymagania lokalne

- PHP 8.3+ z rozszerzeniami: pdo_sqlite, bcmath, gd, zip, intl, mbstring
- Node.js 22+
- Composer

## Instalacja lokalna

```bash
git clone git@github.com:phpcoderpl/stayflow.git
cd stayflow
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
php artisan serve
```

Panel admina: http://localhost:8000/admin/dashboard
Login: `admin@stayflow.pl` / `password`

## Deploy na Railway

### 1. Nowy serwis

W istniejacym projekcie Railway: **New** > **Service** > **GitHub Repo** > `phpcoderpl/stayflow`

### 2. Volume

Dodaj Volume zamontowany na `/var/data` — tam zyje baza SQLite. Bez tego dane znikna po kazdym redeploy.

### 3. Zmienne srodowiskowe

```
APP_KEY=base64:wygeneruj-przez-php-artisan-key-generate--show
APP_ENV=production
APP_URL=https://twoja-domena.up.railway.app
DB_CONNECTION=sqlite
DB_DATABASE=/var/data/database.sqlite
```

### 4. Port

Railway zapyta o target port — wpisz `8080`.

### 5. SSL

Railway daje darmowy SSL automatycznie na domenach `*.up.railway.app`.

### 6. Pierwsze uruchomienie

Przy pierwszym deploy `start.sh` odpala `php artisan migrate --force`. Aby zasiac dane demo:

```bash
npm install -g @railway/cli
railway login
railway link
railway run php artisan db:seed --force
```

### 7. Poczta (SMTP)

Railway nie ma wbudowanej poczty. Skonfiguruj SMTP w panelu: **Ustawienia** > **Email** > **Konfiguracja SMTP**.

Przykladowe ustawienia:

| Dostawca | Serwer | Port | Szyfrowanie |
|---|---|---|---|
| OVH / nazwa.pl | `ssl0.ovh.net` | 465 | SSL |
| Gmail | `smtp.gmail.com` | 587 | TLS |
| Resend.com | `smtp.resend.com` | 587 | TLS |

## Backup i migracja na inny serwer

### Dane (SQLite)

```bash
# Pobranie z Railway
railway run cat /var/data/database.sqlite > backup.sqlite

# Wgranie na nowy serwer
scp backup.sqlite user@serwer:/var/data/database.sqlite
```

### Zdjecia (storage)

```bash
# Pobranie z Railway
railway run tar czf - storage/app/public | tar xzf -

# Wgranie na nowy serwer
scp -r storage/app/public/* user@serwer:/sciezka/storage/app/public/
```

### Co backupowac

| Co | Gdzie | Opis |
|---|---|---|
| Baza danych | `/var/data/database.sqlite` | Wszystkie dane (uzytkownicy, rezerwacje, ustawienia) |
| Zdjecia | `storage/app/public/` | Zdjecia obiektow, kontakt |
| Kod | GitHub repo | Cala aplikacja |

Reszta (vendor, node_modules, cache) jest odtwarzalna z repo.

## Struktura projektu

```
app/
  Http/Controllers/
    Web/          — panel admina (Properties, Bookings, Settings...)
    Public/       — strona publiczna (PropertyList, Booking, GuestPortal)
  Models/         — Eloquent modele (Property, Booking, Guest, Setting...)
  Services/       — logika biznesowa (PricingService, ICalService, EmailService...)
database/
  migrations/     — struktura bazy
  seeders/        — dane demo (admin, udogodnienia, szablony email)
resources/
  js/Pages/       — strony Vue (Inertia)
  js/i18n/        — tlumaczenia PL/EN
  js/composables/ — wspoldzielona logika (amenityIcons)
  js/Layouts/     — AdminLayout, PublicLayout
```
