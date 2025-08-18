# Hướng dẫn cấu hình Cloudflare R2 cho upload hình ảnh

## 1. Tạo R2 Bucket trên Cloudflare

1. Đăng nhập vào [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Chọn tài khoản của bạn
3. Vào **R2 Object Storage** từ sidebar
4. Nhấn **Create bucket**
5. Đặt tên bucket (ví dụ: `boxpos-materials`)
6. Chọn region (khuyến nghị: `auto`)
7. Nhấn **Create bucket**

## 2. Tạo API Token

1. Trong R2 dashboard, vào **Manage R2 API tokens**
2. Nhấn **Create API token**
3. Đặt tên token (ví dụ: `BoxPOS Materials Upload`)
4. Chọn quyền:
   - **Object Read & Write** cho bucket của bạn
5. Nhấn **Create API token**
6. **Lưu lại** Access Key ID và Secret Access Key

## 3. Cấu hình Custom Domain (Tùy chọn)

Để có URL đẹp hơn cho hình ảnh:

1. Trong bucket settings, vào **Settings** tab
2. Tìm **Custom Domains**
3. Nhấn **Connect Domain**
4. Nhập domain/subdomain (ví dụ: `cdn.yoursite.com`)
5. Cấu hình DNS theo hướng dẫn

## 4. Cấu hình trong Laravel

Cập nhật file `.env`:

```env
# Cloudflare R2 Configuration
CLOUDFLARE_R2_ACCESS_KEY_ID=your_access_key_id_here
CLOUDFLARE_R2_SECRET_ACCESS_KEY=your_secret_access_key_here
CLOUDFLARE_R2_DEFAULT_REGION=auto
CLOUDFLARE_R2_BUCKET=your_bucket_name
CLOUDFLARE_R2_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://your_custom_domain_or_bucket_url
```

### Lấy thông tin cần thiết:

- **Access Key ID & Secret**: Từ bước 2
- **Bucket Name**: Tên bucket từ bước 1
- **Account ID**: Tìm trong Cloudflare Dashboard > Right sidebar
- **Endpoint**: `https://[account_id].r2.cloudflarestorage.com`
- **URL**: 
  - Nếu có custom domain: `https://cdn.yoursite.com`
  - Nếu không: `https://pub-[hash].r2.dev` (tìm trong bucket settings)

## 5. Test kết nối

Chạy command để test:

```bash
php artisan material:test-r2
```

## 6. Cấu hình CORS (Nếu cần)

Nếu bạn cần truy cập từ frontend JavaScript:

1. Trong bucket settings, vào **Settings** tab
2. Tìm **CORS policy**
3. Thêm policy:

```json
[
  {
    "AllowedOrigins": ["https://yoursite.com"],
    "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
    "AllowedHeaders": ["*"],
    "ExposeHeaders": ["ETag"],
    "MaxAgeSeconds": 3000
  }
]
```

## 7. Cấu hình Public Access (Nếu cần)

Để hình ảnh có thể truy cập công khai:

1. Trong bucket settings, vào **Settings** tab
2. Tìm **Public access**
3. Bật **Allow public access**

## Lưu ý bảo mật

- Không commit API keys vào git
- Sử dụng IAM policies để giới hạn quyền truy cập
- Cân nhắc sử dụng signed URLs cho nội dung nhạy cảm
- Định kỳ rotate API keys

## Troubleshooting

### Lỗi kết nối
- Kiểm tra API keys có đúng không
- Kiểm tra endpoint URL
- Kiểm tra bucket name

### Lỗi upload
- Kiểm tra quyền write của API token
- Kiểm tra kích thước file (R2 limit: 5TB/object)
- Kiểm tra CORS nếu upload từ browser

### Lỗi hiển thị hình ảnh
- Kiểm tra public access settings
- Kiểm tra custom domain configuration
- Kiểm tra URL format trong code