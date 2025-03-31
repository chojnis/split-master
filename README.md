# split-master

Aplikacja do dzielenia kosztów (np. podróży) z klientem w React Native i backendem w Symfony (API Platform)

## Wymagania

Do uruchomienia potrzebne są zainstalowane następujące narzędzia:
- **Node.js** w wersji 22.x
- **npm** w wersji 10.x (dołączony do Node.js)
- **PHP-FPM** w wersji 8.2
- **Symfony CLI** ([instalacja](https://symfony.com/download))
- **Composer** ([instalacja](https://getcomposer.org/download/))
- **Ngrok (opcjonalne, ale zalecane)** (dostępne bez instalacji za pomocą: `npx ngrok` dzięki npm, weryfikacja `npx ngrok --version`)
- **Expo CLI** (dostępne bez instalacji za pomocą: `npx expo`, weryfikacja `npx expo --version`)
- **Urządzenie mobilne iOS/Android z zainstalowaną aplikacją Expo lub emulator** na przykład Android Studio [instalacja](https://docs.expo.dev/workflow/android-studio-emulator/)

# Instalacja

1. Sklonuj repozytorium:
    ```bash
    git clone https://github.com/chojnis/split-master.git
    cd split-master
    ```

2. Zainstaluj zależności:
    ```bash
    cd backend
    composer install
    cd ../frontend
    npm install
    ```

# Uruchamianie

1. Serwer:
Korzystając z Symfony CLI uruchamiamy serwer:
    ```bash
    cd backend
    symfony serve --port=8000
    ```
Opcjonalnie z przełącznikiem `-d` w celu uruchomienia w tle:
    ```bash
    symfony serve -d --port=8000
    ```

Żeby ułatwić połączenie między klientem a serwerem korzystamy z ngrok w celu stworzenia tunelu do publicznego adresu HTTPS:
    ```bash
    npx ngrok http 8000
    ```

Ostatecznie kopiujemy wygenerowany adres, żeby umieścić go w konfiguracji klienta.

2. Klient:

Tworzymy plik .env lub kopiujemy .env.example zmieniając jego nazwę:
    ```bash
    cd frontend
    cp .env.example .env
    ```

Do skopiowanego adresu wygenerowanego przez ngrok dodajemy `/api`:
    ```bash
    https://xxx/api
    ```

Zmodyfikowany adres ustawiamy w .env dla parametru `EXPO_PUBLIC_API_URL`:
    ```bash
    EXPO_PUBLIC_API_URL=https://xxx/api
    ```

Korzystając z Expo CLI uruchamiamy serwer deweloperski
    ```bash
    npx expo start --tunnel -c
    ```

Ostatecznie korzystając ze z konfigurowanego emulatora uruchamiamy go klawiszami `a` lub `i` w zależności od urządzenia lub skanujemy dołączony kod QR fizycznym urządzeniem.

## Database structure

![Untitled](https://github.com/user-attachments/assets/014bf146-b9da-49f2-91b3-af848fc816fc)
