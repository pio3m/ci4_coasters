# 🎢 **System Kolejek Górskich - API**
🚀 **Zaawansowany system zarządzania kolejkami górskimi**  
🔧 **Technologie:** PHP 8.1, CodeIgniter 4, Redis, Nginx, Docker

---

## 📖 **Opis projektu**
"System Kolejek Górskich" to API do zarządzania kolejkami górskimi i ich wagonami. System pozwala na:  
✅ **Rejestrację i zarządzanie kolejkami górskimi**  
✅ **Obsługę wagonów (dodawanie, usuwanie)**  
✅ **Monitorowanie zasobów i wykrywanie problemów (brak wagonów, personelu)**  
✅ **Obsługę zdarzeń w czasie rzeczywistym przy użyciu Redis Pub/Sub**  
✅ **Podział na środowiska (development/production) i automatyczne logowanie błędów**

---

## 🛠 **Technologie użyte w projekcie**
| 🔧 Technologia  | 📌 Wykorzystanie |
|---------------|-----------------|
| **PHP 8.1** | Backend aplikacji, CodeIgniter 4 |
| **CodeIgniter 4** | REST API, MVC, obsługa żądań |
| **Redis** | Przechowywanie danych, Pub/Sub do monitoringu |
| **Docker & Docker Compose** | Izolacja środowiska, łatwa konfiguracja |
| **Nginx** | Serwer proxy dla aplikacji |
| **Xdebug** | Debugowanie aplikacji w PHPStorm |

---

## 🚀 **Szybki start (Lokalna instalacja z Dockerem)**
### **1️⃣ Klonowanie repozytorium**
```sh
git clone https://github.com/twoj-nick/system-kolejek-gorskich.git
cd system-kolejek-gorskich
```

### **2️⃣ Uruchomienie aplikacji w Dockerze**
```sh
docker-compose up -d --build
```
📌 **Co się stanie?**
- Uruchomi się **PHP 8.1 + CodeIgniter 4**
- Redis zostanie uruchomiony jako **baza danych**
- Nginx obsłuży ruch HTTP na porcie **8080**

---

## 🌐 **Dostępne endpointy API**
📌 **Baza URL**: `http://localhost:8080/api`

### **🎢 Kolejki górskie**
| Metoda | Endpoint | Opis |
|--------|---------|------|
| `POST` | `/coasters` | Rejestracja nowej kolejki |
| `PUT` | `/coasters/{id}` | Aktualizacja danych kolejki |
| `GET` | `/statistics` | Pobranie statystyk systemu |

### **🚋 Wagony**
| Metoda | Endpoint | Opis |
|--------|---------|------|
| `POST` | `/coasters/{id}/wagons` | Dodanie nowego wagonu |
| `DELETE` | `/coasters/{id}/wagons/{wagonId}` | Usunięcie wagonu |

---

## 🛠 **Konfiguracja środowiska**
📌 **Ustawienia znajdują się w pliku `.env`**

📌 **Zmiana trybu środowiska:**
```ini
CI_ENVIRONMENT=development
```
**Dostępne tryby:**
- `development` – pełne logowanie, lokalne testy
- `production` – ograniczone logowanie, zoptymalizowane działanie

---

## 📊 **Monitorowanie w czasie rzeczywistym**
🚀 **Uruchomienie systemu monitorującego Redis Pub/Sub:**
```sh
php spark monitor
```
📌 **Co robi monitoring?**  
✅ **Śledzi dodawanie/usuwanie wagonów**  
✅ **Raportuje braki personelu i wagonów**  
✅ **Zapisuje logi do pliku i Redis**

---

## 🛠 **Debugowanie z Xdebug w PHPStorm**
📌 **Dodaj do `docker-compose.override.yml` (jeśli korzystasz z PHPStorm):**
```yaml
services:
  php:
    environment:
      - XDEBUG_MODE=debug
      - XDEBUG_CLIENT_HOST=host.docker.internal
      - XDEBUG_CLIENT_PORT=9003
```
📌 **Uruchomienie Xdebug w kontenerze:**
```sh
docker-compose restart php
```
📌 **Ustawienia w PHPStorm:**
- `PHP > Debug > Xdebug`
- `IDE Key: PHPSTORM`
- `Server: localhost:8080`

---

## 📜 **Logi i błędy**
📌 **Logi aplikacji zapisywane są w:**
```sh
writable/logs/log-YYYY-MM-DD.php
```
📌 **Logi błędów i alertów można też sprawdzić w Redis:**
```sh
docker exec -it kolejka_redis redis-cli
LRANGE logs:coasters 0 -1
```

---

---

## 🧪 **Testowanie API w Postman**
📌 **Importuj plik Postman z katalogu `/tests/postman_collection.json`**  
📌 **Przykładowe testy:**
```sh
curl -X POST http://localhost:8080/api/coasters \
     -H "Content-Type: application/json" \
     -d '{
         "liczba_personelu": 5,
         "liczba_klientow": 500,
         "dl_trasy": 2000,
         "godziny_od": "08:00",
         "godziny_do": "18:00"
     }'
```

---

## 📦 **Struktura projektu**
```
📂 system-kolejek-gorskich
├── 📂 app
│   ├── 📂 Controllers
│   ├── 📂 Services
│   ├── 📂 Config
│   ├── 📂 Filters
├── 📂 docker
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── php-local.ini
├── 📂 writable
│   ├── 📂 logs
│   ├── 📂 cache
├── .env
├── docker-compose.yml
├── README.md
```

---

## 📌 **Autor**
👨‍💻 **[Twoje Imię i Nazwisko / Nick na GitHubie]**  
📧 **email@example.com**  
🔗 **[LinkedIn](https://linkedin.com/in/twoj-profil)**

🚀 **Jeśli podoba Ci się ten projekt, zostaw ⭐ na GitHubie!** 😊
