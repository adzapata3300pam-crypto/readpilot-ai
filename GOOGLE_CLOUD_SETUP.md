# Supabase Storage Setup

ReadPilot stores student recordings in a private Supabase Storage bucket. Teachers do not enter storage credentials; ReadPilot uploads through the server and confirms a recording only after checking the object metadata. Optional Google OAuth is for ReadPilot sign-in only and is separate from storage.

## 1. Install dependencies

From this project folder, run `composer install`. XAMPP PHP needs PDO MySQL, cURL, Fileinfo, OpenSSL, and ZIP enabled. The upload limits should be at least `25M` for `upload_max_filesize` and `30M` for `post_max_size`.

## 2. Create a private bucket and S3 credentials

In the Supabase project, create a **private** Storage bucket. In Storage settings, enable the S3 protocol and generate an S3 access key and secret. Supabase warns that S3 keys provide broad storage access and bypass RLS, so keep them server-only and never put them in browser code, public source control, or screenshots.

Copy the S3 endpoint and region shown by Supabase. Prefer the direct storage hostname Supabase provides for your project. Copy `readpilot.secrets.examples.php` to `C:\xampp\readpilot-secrets.php` and set `SUPABASE_S3_ENDPOINT`, `SUPABASE_S3_REGION`, `SUPABASE_S3_ACCESS_KEY`, `SUPABASE_S3_SECRET_KEY`, and `SUPABASE_BUCKET`. The endpoint should include `/storage/v1/s3`; use the region exactly as shown in Supabase settings. Keep the real secrets file outside `htdocs`.

## 3. Test storage

Run `composer install` from the project folder. Sign in as a teacher, open **Cloud Recordings**, and use **Test cloud connection**. Then make a test reading with school/guardian permission checked, finish it, and use **Verify** on its row.

Supabase S3 access keys are never sent to a teacher's browser. Objects are private and playback uses a short-lived signed URL. The existing GCS settings in the local secrets file are retained only as an inactive fallback; the active storage code ignores them.
