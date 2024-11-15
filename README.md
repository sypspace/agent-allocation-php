# Agent Allocation PHP

Agent Allocation PHP adalah sebuah service backend yang digunakan untuk menentukan (_assign_) seorang agen ke sebuah room chat baru yang dibuat oleh customer. Aplikasi ini dirancang untuk terintegrasi dengan platform multichannel Qiscus dan menyediakan beberapa endpoint webhook yang dapat digunakan untuk mengelola alokasi agen dan konfigurasi lainnya.

## Fitur Utama

-   **Alokasi Agen Otomatis:** Mengalokasikan agen ke room chat baru yang dibuat oleh customer.
-   **Webhook Integrasi:** Mendukung integrasi dengan webhook untuk notifikasi pesan baru dan status resolved.
-   **Pengaturan Jumlah Customer Maksimum:** Mengatur jumlah maksimum customer yang bisa ditangani oleh seorang agen secara bersamaan.
-   **Pengaturan URL Mark as Resolved:** Mengatur URL webhook untuk notifikasi status room/chat menjadi resolved.

## Prasyarat

-   PHP 8.0 atau lebih baru
-   Composer
-   Server dengan akses internet (untuk menerima webhook)

## Instalasi

1. Clone repository ini:

    ```bash
    git clone https://github.com/username/agent-allocation-php.git
    cd agent-allocation-php
    ```

2. Install dependencies menggunakan Composer:

    ```bash
    composer install
    ```

3. Konfigurasi environment dengan membuat file .env berdasarkan contoh .env.example.
    ```bash
    LOG_STACK=single,slack
    QISCUS_BASE_URL=https://multichannel.qiscus.com
    QISCUS_AGENT_ID=
    QISCUS_APP_ID=
    QISCUS_SECRET=
    QISCUS_EMAIL=
    QISCUS_PASSWORD=
    LOG_SLACK_WEBHOOK_URL=
    ```
4. Jalankan migrasi dan seeder database
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

# Endpoint API

Berikut adalah daftar endpoint yang tersedia dalam service ini:

## 1. Webhook Agent Allocation

URL: `POST /api/v1/webhook/agent_allocation`

Deskripsi: Digunakan sebagai webhook ketika ada pesan baru masuk. Endpoint ini akan menentukan agen yang akan ditugaskan ke room chat baru berdasarkan jumlah customer yang sedang ditangani.

Contoh Payload:

```json
{
    "app_id": "xrmcf-2s1ciqkustajhlv",
    "avatar_url": "https://omnichannel.qiscus.com/img/ic_qiscus_client.png",
    "candidate_agent": null,
    "email": "cust1@example.com",
    "extras": "{\"additional_extras\":{\"timezone_offset\":7},\"notes\":null,\"timezone_offset\":null,\"user_properties\":[],\"user_properties_migrated\":true}",
    "is_new_session": true,
    "is_resolved": false,
    "latest_service": null,
    "name": "cust1",
    "room_id": "273683234",
    "source": "qiscus"
}
```

Respons:

```json
{
    "status": 200,
    "message": "Successfully received.",
    "data": null
}
```

## 2. Webhook Mark as Resolved

URL: `POST /api/v1/webhook/mark_as_resolved`
Deskripsi: Digunakan sebagai webhook ketika agen menandai sebuah room/chat sudah resolved.

Contoh Payload:

```json
{
    "customer": {
        "additional_info": [],
        "avatar": "https://omnichannel.qiscus.com/img/ic_qiscus_client.png",
        "name": "cust#2",
        "user_id": "hello@world.com"
    },
    "resolved_by": {
        "email": "qiscus_166a3_qticketing.demo1@gmail.com",
        "id": 162787,
        "is_available": true,
        "name": "QTicketing Demo 1",
        "type": "admin"
    },
    "service": {
        "first_comment_id": "2487134935",
        "id": 115830865,
        "is_resolved": true,
        "last_comment_id": "2488202680",
        "notes": "selesai.",
        "room_id": "274083256",
        "source": "qiscus"
    }
}
```

Respons:

```json
{
    "status": 200,
    "message": "Room marked as resolved.",
    "data": null
}
```

## 3. Setting Max Customer

URL: `GET|POST /api/v1/settings/max_customers`

Deskripsi: Mengatur jumlah maksimum customer yang bisa ditangani oleh seorang agen secara bersamaan.

Contoh Payload (POST):

```json
{
    "max_customers": 5
}
```

Respons (POST):

```json
{
    "status": 200,
    "message": "Successfully updated",
    "data": {
        "max_customers": 5
    }
}
```

Respons (GET):

```json
{
    "status": 200,
    "message": "Success",
    "data": {
        "max_customers": 20
    }
}
```

## 4. Setting URL Mark as Resolved

URL: `POST /api/v1/settings/set_mark_as_resolved`

Deskripsi: Mengatur URL yang digunakan oleh Multichannel Qiscus untuk mengirim notifikasi bahwa agen sudah mengubah status room/chat menjadi "resolved".

Contoh Payload:

```json
{
    "endpoint": "https://yourdomain.com/api/v1/webhook/mark_as_resolved",
    "enable": true
}
```

Respons:

```json
{
    "status": 200,
    "message": "Successfully updated",
    "data": {
        "mark_as_resolved_webhook_url": "https://yourdomain.com/api/v1/webhook/mark_as_resolved",
        "is_allocate_agent_webhook_enabled": true
    }
}
```

# Konfigurasi

-   **Max Customers:** Pengaturan jumlah maksimum customer per agen dapat diubah melalui endpoint `/api/v1/settings/max_customers` atau dengan mengedit file konfigurasi di .env.
-   **URL Mark as Resolved:** URL yang digunakan untuk notifikasi mark as resolved dapat diubah melalui endpoint `/api/v1/settings/set_mark_as_resolved`.
-   **Supervisor** Install dan konfigurasi Supervisor untuk kelola antrian Job.

# Kontribusi

Jika Anda ingin berkontribusi pada project ini, mohon ikuti langkah berikut:

1. Fork repository ini.
2. Buat branch baru: git checkout -b fitur-baru.
3. Commit perubahan Anda: git commit -m 'Menambahkan fitur baru'.
4. Push ke branch: git push origin fitur-baru.
5. Buat pull request.

# Lisensi

Project ini dilisensikan di bawah [MIT License](#).

# Kontak

Jika Anda memiliki pertanyaan atau masukan, silakan hubungi tim pengembang di dev@sypspace.com.
