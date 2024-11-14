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

# Endpoint API

Berikut adalah daftar endpoint yang tersedia dalam service ini:

## 1. Webhook Agent Allocation

URL: `POST /api/v1/webhook/agent_allocation`

Deskripsi: Digunakan sebagai webhook ketika ada pesan baru masuk. Endpoint ini akan menentukan agen yang akan ditugaskan ke room chat baru berdasarkan jumlah customer yang sedang ditangani.

Contoh Payload:

```json
{
    "room_id": "12345",
    "message": "Hello, I need help!",
    "customer_id": "67890"
}
```

Respons:

```json
{
    "status": "success",
    "agent_id": "agent_01"
}
```

## 2. Webhook Mark as Resolved

URL: `POST /api/v1/webhook/mark_as_resolved`
Deskripsi: Digunakan sebagai webhook ketika agen menandai sebuah room/chat sudah resolved.

Contoh Payload:

```json
{
    "room_id": "12345",
    "agent_id": "agent_01"
}
```

Respons:

```json
{
    "status": "success",
    "message": "Room marked as resolved."
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

Respons:

```json
{
    "status": "success",
    "max_customers": 5
}
```

## 4. Setting URL Mark as Resolved

URL: `POST /api/v1/settings/set_mark_as_resolved`

Deskripsi: Mengatur URL yang digunakan oleh Multichannel Qiscus untuk mengirim notifikasi bahwa agen sudah mengubah status room/chat menjadi "resolved".

Contoh Payload:

```json
{
    "url": "https://yourdomain.com/api/v1/webhook/mark_as_resolved"
}
```

Respons:

```json
{
    "status": "success",
    "url": "https://yourdomain.com/api/v1/webhook/mark_as_resolved"
}
```

# Konfigurasi

-   **Max Customers:** Pengaturan jumlah maksimum customer per agen dapat diubah melalui endpoint `/api/v1/settings/max_customers` atau dengan mengedit file konfigurasi di .env.
-   **URL Mark as Resolved:** URL yang digunakan untuk notifikasi mark as resolved dapat diubah melalui endpoint `/api/v1/settings/set_mark_as_resolved`.
-   **Supervisor** Setup Supervisor untuk kelola antrian Job.

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
