# Database Design - Issue #499: AQ_AI Calculation

## Nguyên tắc thiết kế

⛔ **TUYỆT ĐỐI KHÔNG lưu JSON vào database**

✅ **Giải pháp:**
- Tách dữ liệu thành các bảng normalized
- Lưu AI response thành file JSON riêng
- Lưu path đến file JSON trong bảng `quotation_route_files`

## Cấu trúc bảng

### 1. Bảng `quotation_routes` (Bảng chính)

**Mục đích:** Lưu thông tin tổng quan và kết quả tính toán

```sql
CREATE TABLE quotation_routes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Mã định danh
    route_code VARCHAR(50) UNIQUE NOT NULL COMMENT 'QR-YYYYMMDD-XXX',
    user_id BIGINT UNSIGNED NOT NULL COMMENT 'User thực hiện tính toán',
    quotation_id BIGINT UNSIGNED NULL COMMENT 'FK đến bảng quotations (nếu có)',
    
    -- Input từ user
    title VARCHAR(500) NULL COMMENT 'Tiêu đề: VD: 2025年度 4t配送計画用',
    pickup_location VARCHAR(500) NOT NULL COMMENT '積地 - Điểm bốc hàng',
    delivery_location VARCHAR(500) NOT NULL COMMENT '届け地 - Điểm giao hàng',
    return_location VARCHAR(500) NOT NULL COMMENT '帰社地 - Điểm về',
    start_time TIME NOT NULL COMMENT '運行開始時間',
    vehicle_type VARCHAR(50) DEFAULT '4t' COMMENT '車両区分',
    loading_time_minutes INT DEFAULT 60 COMMENT '積み込み作業時間',
    unloading_time_minutes INT DEFAULT 60 COMMENT '荷下ろし作業時間',
    user_break_time_minutes INT NULL COMMENT '休憩時間指定 (NULL = Auto)',
    
    -- Output từ AI - Summary
    total_distance_km DECIMAL(10,2) NULL COMMENT '総距離',
    estimated_end_time TIME NULL COMMENT '終了予定時刻',
    date_change BOOLEAN DEFAULT FALSE COMMENT '日付変更フラグ',
    
    -- Output từ AI - Time Breakdown
    total_duty_time_hours DECIMAL(5,2) NULL COMMENT '拘束時間（始業〜終業）',
    actual_working_hours DECIMAL(5,2) NULL COMMENT '実労働時間（休憩除く）',
    total_driving_time_minutes INT NULL COMMENT '総運転時間',
    total_handling_time_minutes INT NULL COMMENT '荷役時間合計（積込+荷下）',
    total_break_time_minutes INT NULL COMMENT '休憩時間合計',
    
    -- Output từ AI - Cost
    highway_fee DECIMAL(12,2) DEFAULT 0 COMMENT '高速道路料金合計',
    fuel_cost DECIMAL(12,2) DEFAULT 0 COMMENT '燃料費（nếu AI tính）',
    estimated_total_cost DECIMAL(12,2) DEFAULT 0 COMMENT '総費用見積',
    
    -- Output từ AI - Compliance
    is_compliant BOOLEAN DEFAULT TRUE COMMENT '法令基準を満たすか',
    applied_rule TEXT NULL COMMENT '適用したルールの説明',
    
    -- Metadata
    ai_model_used VARCHAR(50) NULL COMMENT 'AI model: gpt-4, claude-3.5, etc',
    calculation_duration_seconds INT NULL COMMENT 'Thời gian tính toán (giây)',
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending' COMMENT 'Trạng thái',
    error_message TEXT NULL COMMENT 'Lỗi nếu có',
    notes TEXT NULL COMMENT 'Ghi chú',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_user_date (user_id, created_at),
    INDEX idx_route_code (route_code),
    INDEX idx_status (status),
    INDEX idx_quotation (quotation_id),
    INDEX idx_pickup (pickup_location(100)),
    INDEX idx_delivery (delivery_location(100))
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Kết quả tính toán route từ AI cho báo giá';
```

**Ước tính dung lượng:** ~1-2KB/record

---

### 2. Bảng `quotation_route_locations` (Các điểm dừng)

**Mục đích:** Lưu chi tiết từng điểm trong route (normalized)

```sql
CREATE TABLE quotation_route_locations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    route_id BIGINT UNSIGNED NOT NULL COMMENT 'FK đến quotation_routes',
    
    -- Thứ tự và loại
    sequence_order TINYINT UNSIGNED NOT NULL COMMENT 'Thứ tự: 1, 2, 3...',
    location_type ENUM('pickup', 'delivery', 'return', 'waypoint') NOT NULL,
    
    -- Địa chỉ
    location_name VARCHAR(200) NULL COMMENT 'Tên địa điểm',
    address VARCHAR(500) NOT NULL COMMENT 'Địa chỉ đầy đủ',
    prefecture VARCHAR(50) NULL COMMENT '都道府県',
    city VARCHAR(100) NULL COMMENT '市区町村',
    latitude DECIMAL(10,8) NULL COMMENT '緯度',
    longitude DECIMAL(11,8) NULL COMMENT '経度',
    
    -- Thời gian tại điểm này
    arrival_time TIME NULL COMMENT '到着時刻',
    departure_time TIME NULL COMMENT '出発時刻',
    stay_duration_minutes INT NULL COMMENT '滞在時間（作業時間）',
    
    -- Khoảng cách từ điểm trước
    distance_from_previous_km DECIMAL(10,2) NULL COMMENT '前の地点からの距離',
    travel_time_from_previous_min INT NULL COMMENT '前の地点からの移動時間',
    
    -- Thông tin liên hệ (optional)
    contact_name VARCHAR(100) NULL COMMENT '担当者名',
    contact_phone VARCHAR(20) NULL COMMENT '連絡先電話番号',
    notes TEXT NULL COMMENT '備考',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key
    FOREIGN KEY (route_id) REFERENCES quotation_routes(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_route_sequence (route_id, sequence_order),
    INDEX idx_location_type (location_type),
    INDEX idx_prefecture (prefecture)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Các điểm dừng trong route (pickup, delivery, return)';
```

**Ước tính dung lượng:** ~0.5-1KB/record × 3-10 locations = ~3-10KB/route

---

### 3. Bảng `quotation_route_segments` (Chi tiết đoạn đường)

**Mục đích:** Lưu chi tiết từng đoạn đường giữa 2 điểm (normalized)

```sql
CREATE TABLE quotation_route_segments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    route_id BIGINT UNSIGNED NOT NULL COMMENT 'FK đến quotation_routes',
    from_location_id BIGINT UNSIGNED NOT NULL COMMENT 'FK đến quotation_route_locations',
    to_location_id BIGINT UNSIGNED NOT NULL COMMENT 'FK đến quotation_route_locations',
    
    segment_order TINYINT UNSIGNED NOT NULL COMMENT 'Thứ tự đoạn: 1, 2, 3...',
    
    -- Thông tin đoạn đường
    distance_km DECIMAL(10,2) NOT NULL COMMENT '距離',
    driving_time_minutes INT NOT NULL COMMENT '運転時間',
    
    -- Chi phí
    highway_fee DECIMAL(10,2) DEFAULT 0 COMMENT '高速料金（この区間）',
    fuel_cost DECIMAL(10,2) DEFAULT 0 COMMENT '燃料費（この区間）',
    
    -- Thông tin đường
    road_type ENUM('highway', 'national', 'prefectural', 'local') NULL COMMENT '道路種別',
    highway_name VARCHAR(200) NULL COMMENT '高速道路名（例: 東名高速道路）',
    route_description TEXT NULL COMMENT 'ルート概要（AI提供）',
    
    -- Điều kiện (optional)
    traffic_condition VARCHAR(50) NULL COMMENT '交通状況',
    weather_condition VARCHAR(50) NULL COMMENT '天候',
    notes TEXT NULL COMMENT '備考',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (route_id) REFERENCES quotation_routes(id) ON DELETE CASCADE,
    FOREIGN KEY (from_location_id) REFERENCES quotation_route_locations(id) ON DELETE CASCADE,
    FOREIGN KEY (to_location_id) REFERENCES quotation_route_locations(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_route_segment (route_id, segment_order),
    INDEX idx_road_type (road_type)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Chi tiết từng đoạn đường trong route';
```

**Ước tính dung lượng:** ~0.5KB/record × 2-5 segments = ~1-2.5KB/route

---

### 4. Bảng `quotation_route_files` (Lưu path file JSON)

**Mục đích:** Lưu đường dẫn đến file JSON request/response từ AI (để audit/debug)

```sql
CREATE TABLE quotation_route_files (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    route_id BIGINT UNSIGNED NOT NULL COMMENT 'FK đến quotation_routes',
    
    file_type ENUM('request', 'response') NOT NULL COMMENT 'Loại file',
    file_path VARCHAR(500) NOT NULL COMMENT 'Đường dẫn file: ai_responses/2025/12/QR-xxx.json',
    file_name VARCHAR(255) NOT NULL COMMENT 'Tên file: QR-20251212-001-response.json',
    file_size BIGINT UNSIGNED NULL COMMENT 'Kích thước file (bytes)',
    mime_type VARCHAR(50) DEFAULT 'application/json',
    
    storage_disk VARCHAR(50) DEFAULT 'local' COMMENT 'local, s3, etc',
    is_archived BOOLEAN DEFAULT FALSE COMMENT 'Đã archive chưa',
    archived_at TIMESTAMP NULL COMMENT 'Thời gian archive',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key
    FOREIGN KEY (route_id) REFERENCES quotation_routes(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_route_type (route_id, file_type),
    INDEX idx_created (created_at),
    INDEX idx_archived (is_archived, created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Lưu path đến file JSON request/response từ AI';
```

**Ước tính dung lượng:** ~0.3KB/record × 2 files (request + response) = ~0.6KB/route

---

## Tổng hợp dung lượng

**1 route calculation:**
- quotation_routes: ~1.5KB
- quotation_route_locations: ~5KB (3-5 locations)
- quotation_route_segments: ~1.5KB (2-3 segments)
- quotation_route_files: ~0.6KB (2 files)
- **Tổng DB:** ~8.6KB/route

**File JSON (lưu ngoài DB):**
- Request JSON: ~1-2KB
- Response JSON: ~3-5KB
- **Tổng Files:** ~4-7KB/route

**Tổng cộng:** ~12-15KB/route (rất nhẹ!)

**Ước tính cho 1 năm:**
- 1000 routes/tháng × 12 tháng = 12,000 routes
- Database: ~103MB
- Files: ~60-84MB
- **Tổng:** ~163-187MB/năm (RẤT NHẸ!)

---

## Mối quan hệ giữa các bảng

```
quotation_routes (1)
    ├── (N) quotation_route_locations
    ├── (N) quotation_route_segments
    └── (N) quotation_route_files

quotation_route_locations (1)
    ├── (N) quotation_route_segments (as from_location)
    └── (N) quotation_route_segments (as to_location)
```

---

## File Storage Structure

```
storage/app/
└── ai_responses/
    └── quotation_routes/
        ├── 2025/
        │   ├── 12/
        │   │   ├── QR-20251212-001-request.json
        │   │   ├── QR-20251212-001-response.json
        │   │   ├── QR-20251212-002-request.json
        │   │   └── QR-20251212-002-response.json
        │   └── 01/
        │       └── ...
        └── 2026/
            └── ...
```

**Cấu trúc file JSON response (ví dụ):**

```json
{
  "summary": {
    "total_distance_km": 150.5,
    "estimated_end_time": "18:30",
    "date_change": false,
    "compliance_check": {
      "is_compliant": true,
      "applied_rule": "運転時間が4時間を超えるため30分の休憩を追加しました"
    }
  },
  "time_breakdown": {
    "total_duty_time_hours": 9.5,
    "actual_working_hours": 8.5,
    "total_driving_time_minutes": 360,
    "total_handling_time_minutes": 120,
    "total_break_time_minutes": 60
  },
  "cost_breakdown": {
    "estimated_total_tolls": 5000
  },
  "route_details": {
    "section_1_pickup_to_delivery": {
      "distance_km": 80.0,
      "driving_time_minutes": 90,
      "toll_yen": 3000,
      "route_description": "東名高速道路経由"
    },
    "section_2_delivery_to_return": {
      "distance_km": 70.5,
      "driving_time_minutes": 85,
      "toll_yen": 2000,
      "route_description": "首都高速経由"
    }
  }
}
```

---

## Cleanup Policy

### Tự động xóa file cũ:

**Option 1: Xóa sau 30 ngày**
```php
// Chạy daily command
php artisan quotation:cleanup-old-files --days=30
```

**Option 2: Archive sang S3 sau 30 ngày, xóa sau 90 ngày**
```php
// Day 30: Move to S3
php artisan quotation:archive-files --days=30

// Day 90: Delete from S3
php artisan quotation:delete-archived-files --days=90
```

**Option 3: Giữ mãi (nếu cần audit lâu dài)**
- Không xóa, chỉ archive sang cold storage

---

## Migration Order

1. `create_quotation_routes_table.php`
2. `create_quotation_route_locations_table.php`
3. `create_quotation_route_segments_table.php`
4. `create_quotation_route_files_table.php`

---

## Ưu điểm của thiết kế này

✅ **Performance cao:**
- Không lưu JSON → Query nhanh
- Có index đầy đủ
- Normalized → Dễ JOIN, dễ filter

✅ **Dễ maintain:**
- Schema rõ ràng
- Dễ thêm column mới
- Dễ migration

✅ **Tiết kiệm storage:**
- Database nhẹ (~8.6KB/route)
- File JSON tách riêng, dễ xóa/archive

✅ **Dễ scale:**
- File JSON có thể move sang S3
- Database có thể partition theo năm/tháng
- Có thể cache kết quả

✅ **Audit trail đầy đủ:**
- Vẫn giữ được file JSON gốc để debug
- Có thể trace lại AI đã tính toán như thế nào
- Có thể reprocess nếu cần

