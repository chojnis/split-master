# Split Master

Aplikacja do dzielenia kosztów (np. podróży) z klientem w React Native i backendem w API Platform (Symfony)

## Wymagania

Do uruchomienia potrzebne są zainstalowane następujące narzędzia:
- **Node.js** w wersji 22.x
- **npm** w wersji 10.x (dołączony do Node.js)
- **Docker** w wersji 24.x
- **Docker Compose** w wersji 2.x
- **Expo CLI** (dostępne bez instalacji za pomocą: `npx expo`, weryfikacja `npx expo --version`)
- **Urządzenie mobilne iOS/Android z zainstalowaną aplikacją Expo lub emulator** na przykład Android Studio [instalacja](https://docs.expo.dev/workflow/android-studio-emulator/)

> Wszystkie przedstawione w dalszej części polecenia będą wykonywane w systemie Linux, natomiast aplikacja jest możliwa do zainstalowania i uruchomienia także na systemach Windows i MacOS przy użyciu tych samych narzędzi.

## Kod źródłowy

1. Klonujemy repozytorium:
    ```bash
    git clone https://github.com/chojnis/split-master.git
    cd split-master
    ```

    > W pobranym repozytorium znajdują się dwa foldery odpowiedzialne za back-end i front-end, w których powinny odbywać się instalacje i uruchamianie obu z części.

## Instalacja i uruchamianie części back-endowej

```bash
cd backend
```


1. Kopiujemy `.env.example` zmieniając jego nazwę na `.env`:
    ```bash
    cp .env.example .env
    ```
    > Zawartość pliku jest uzupełniona danymi przykładowymi, które wystarczą uruchomienia wersji deweloperskiej

2. Budujemy obraz Dockerowy:
   ```bash
   docker compose build --no-cache
   ```
   
3. W kontekście lokalnej integracji z częścią front-endową aplikacji, konieczna jest znajomość adresu IP serwera back-endu w sieci lokalnej (przy założeniu, że serwer klienta znajduje się w tej samej sieci). W przypadku systemu Linux można w tym celu wykorzystać polecenie ip:
    ```bash
    ip a
    ```

4. Uruchamiamy środowisko wirtualne z serwerem back-endu:
    ```bash
    SERVER_NAME=http://[ADRES_IP] docker compose up --wait
    ```

5. Instalujemy zależności:
    ```bash
    docker exec php composer install
    ```

6. Inicjalizujemy strukturę bazy danych:
    ```bash
    docker exec php php bin/console doctrine:schema:create
    ```

7. Generujemy klucze publiczny/prywatny dla mechanizmu tokenów JWT:
    ```bash
    docker exec php php bin/console lexik:jwt:generate-keypair
    ```

8. Inicjalizujemy przykładowe dane:
    ```bash
    docker exec php php bin/console doctrine:fixtures:load
    ```
    > Inicjalizacja danych początkowych jest opcjonalna, ale aplikacja wymaga przynajmniej jednej waluty w tabeli ```currency```


Efektem przeprowadzonych kroków jest gotowe środowisko deweloperskie, które umożliwia wprowadzanie aktywnych zmian w kodzie, a także udostępnia:
* aplikację API Platform na porcie 80
    * /api
    * /api/docs
* zintegrowaną bazę danych MariaDB z gotową strukturą
* klienta graficznego bazy danych udostępnionego na porcie 8080
* przykładowych użytkowników z grupami i transakcjami:
    * pierwszy@user.com:password
    * drugi@user.com:password
    * trzeci@user.com:password

### Instalacja i uruchamianie części front-endowej

```bash
cd frontend
```

1. Kopiujemy `.env.example` zmieniając jego nazwę na `.env`:
    ```bash
    cp .env.example .env
    ```

2. Uzupełniamy zmienną ```EXPO_PUBLIC_API_URL``` lokalnym serwerem API:
   ```bash
   EXPO_PUBLIC_API_URL=http://[ADRES_IP]/api
   ```

3. Instalujemy zależności:
   ```bash
   npm i
   ```

4. Uruchamiamy serwer deweloperski:
   ```bash
   npx expo start --tunnel
   ```

5. Instalujemy aplikację [Expo Go](https://expo.dev/go) na urządzeniu (fizycznym lub emulatorze), następnie skanujemy kod QR (lub wprowadzamy adres ręcznie) w celu połączenia się z aplikacją
   > Ważnym jest, że to środowisko deweloperskie nie obsługuje części natywnych funkcjonalności, co w kontekście aplikacji przekłada się na brak możliwości zmiany daty transakcji podczas jej tworzenia lub edycji. W celu pełnego doświadczenia, należy [zbudować](https://docs.expo.dev/build/setup/) natywną wersję aplikacji:
   > ```bash
   > eas build --platform android --profile preview
   > ```


## Dokumentacja
### Relacje bazy danych

![Untitled (3)](https://github.com/user-attachments/assets/0c7e4938-3649-4a62-a2ab-b3f77f5af503)

