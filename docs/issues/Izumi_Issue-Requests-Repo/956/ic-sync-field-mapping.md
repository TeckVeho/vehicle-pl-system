# Phân tích Ánh xạ Dữ liệu Đồng bộ (Data Mapping) từ Izumi Cloud sang VPL (Issue #956)

Tài liệu này chi tiết hóa cách "lắp ráp" và biến đổi (transform) từng field dữ liệu cho các API đồng bộ Master Sync từ hệ thống Izumi Cloud (IC) sang hệ thống VPL.

## 1. Đồng bộ Người dùng (POST `/api/users/sync`)
Gửi danh sách User từ IC sang VPL.

| Field y/cầu của VPL | Nguồn dữ liệu bên IC (Bảng `users`) | Logic Transform (Biến đổi) tại IC |
| :--- | :--- | :--- |
| `userId` | `$user->id` (hoặc `uuid`) | Không cần đổi, lấy ID nguyên bản làm `externalId` cho VPL. |
| `email` | `$user->email` | Map trực tiếp 1-1. |
| `name` | `$user->name` | Map trực tiếp 1-1. |
| `role` | `$user->roles` (Spatie) | **[CẦN BIẾN ĐỔI]** Phải viết hàm chuyển đổi từ Role của IC sang đúng Enum tiếng Nhật của VPL (Vd: `CREW`, `DX`, `経理財務`). |
| `password` | `$user->password` | Tùy chọn. Tốt nhất là bỏ trống hoặc truyền chuỗi mặc định, VPL sẽ tự mã hóa. |

## 2. Đồng bộ Khóa học / Tuyến đường (POST `/api/courses/sync`)
Lưu ý IC không lưu cấu trúc `Course` hoàn toàn giống VPL.

| Field y/cầu của VPL | Nguồn dữ liệu bên IC (Bảng `courses`) | Logic Transform (Biến đổi) tại IC |
| :--- | :--- | :--- |
| `departmentId` | `$course->department_id` | **[CẦN BIẾN ĐỔI]** Phải tra bảng `departments` lấy mã String nội bộ (Ví dụ: ID `1` -> `"LOC001"`). |
| `code` | `$course->course_code` | Map trực tiếp 1-1. |
| `name` | Không có sẵn cột `name` | **[CẦN BIẾN ĐỔI]** VPL bắt buộc trường `name`. IC phải tự nối chuỗi (Ví dụ `$course->course_type . ' - ' . $course->address`) để tạo tên có nghĩa. |
| `sortOrder` | Không có | Có thể truyền mặc định `0`, VPL sẽ tự động tăng dần (Auto-increment). |
| `externalId` | `$course->id` | ID gốc để VPL dùng cho tính năng UPSERT. |

## 3. Đồng bộ Phương tiện (POST `/api/vehicles/sync`)
Đây là API cần transform nhiều nhất do IC lưu tách rời thông tin biển số.

| Field y/cầu của VPL | Nguồn dữ liệu bên IC (Bảng `vehicles`) | Logic Transform (Biến đổi) tại IC |
| :--- | :--- | :--- |
| `departmentId` | `$vehicle->department_id` | Map ID số nguyên sang Array Mã String (Vd `"LOC001"`). |
| `vehicleNo` | `latestNumberPlateHistory()` | **[CẦN BIẾN ĐỔI]** Cột `vehicles` của IC không có biển số xe trực tiếp. Phải load relation `latestNumberPlateHistory` và lấy ra biển số mới nhất. |
| `serviceType` | `$vehicle->truck_classification` | Dùng phân loại xe của IC (hoặc `driving_classification`) để map sang. |
| `tonnage` | `$vehicle->tonnage` | Map trực tiếp 1-1 (VPL dùng để tính phí bảo hiểm tải trọng). |
| `externalId` | `$vehicle->id` | ID nội bộ IC. |
| `courseExternalId`| Khóa ngoại hoặc mảng phân công | **[CẦN BIẾN ĐỔI]** Bảng xe IC bị khuyết cột `course_id`. Có thiết lập dynamic từ playlist tài xế để truyền được ID course gốc của IC, hoặc trả NULL. |

## 4. Đồng bộ Tài xế (POST `/api/drivers/sync`)
Dữ liệu lấy từ bảng `employees` của IC.

| Field y/cầu của VPL | Nguồn dữ liệu bên IC (Bảng `employees`)| Logic Transform (Biến đổi) tại IC |
| :--- | :--- | :--- |
| `departmentId` | Bảng `employee_department` | **[CẦN BIẾN ĐỔI]** Lấy từ relation `departments()` (Many-to-Many), trích Department chính và map thành code `"LOCxxx"`. |
| `code` | `$employee->employee_code` | Map trực tiếp 1-1. |
| `name` | `$employee->name` | Map trực tiếp 1-1. |
| `externalId` | `$employee->id` | ID của IC làm tham chiếu gốc. |

*Lưu ý: Để gán Driver vào Course, IC cần query thêm dữ liệu từ ATMTC trước khi đóng gói payload.*

## 5. API Đồng bộ Chi phí Xe (POST `/api/vehicle-monthly-costs/sync`)
Dữ liệu trộn lẫn giữa IC và hệ thống ITP bên ngoài. Định dạng danh sách theo theo `yearMonth`.

| Field y/cầu của VPL | Nguồn IC cung cấp / lấy từ API | Logic Transform (Biến đổi) tại IC |
| :--- | :--- | :--- |
| `vehicleId` / `vehicleExternalId`| ID gốc của xe (IC) | Lấy `$vehicle->id`. |
| `leaseDepreciation`, `vehicleDepreciation`, `insuranceCost`, `taxCost`, `vehicleLease` | Các bảng `MaintenanceLease`, `InsuranceRate`... | Truy vấn tổng hợp số tiền cố định tương ứng theo xe/tháng. |
| `fuelEfficiency`, `roadUsageFee` | **Fetch cổng API ITP** | IC làm Proxy, gọi API sang ITP lấy 2 thông số này, ghép vào JSON cho VPL. |

## 6. Khối Chi phí Phòng ban (POST `/api/location-monthly-expenses/sync`)
IC lấy dữ liệu kế toán từ **PCA**.

| Field y/cầu của VPL | Nguồn IC cung cấp / lấy từ API | Logic Transform (Biến đổi) tại IC |
| :--- | :--- | :--- |
| `locationId` / `departmentId` | ID phòng ban IC | Diễn dịch ID sang `"LOC001"` quy định. |
| `accountItemCode` | Các field báo cáo | **[CẦN BIẾN ĐỔI]** Mapping giữa mã của hệ thống PCA cấp với 20 mã chuẩn (`6150`-`6189`) mà VPL quy định. |
| `amount` | Báo cáo PCA | Con số thực chi do báo cáo trả về. |

---
**TỔNG KẾT:**
Để tích hợp thành công, Izumi Cloud cần phát triển một tiến trình (như Job Data Export hoặc API Resource Class) chuyên biệt làm nhiệm vụ **ETL (Extract, Transform, Load)**. Không trút dữ liệu thô thẳng mà phải "nặn" lại tên, mapping ID sang mảng String và đặc biệt phải cắm chung dữ liệu gọi từ hệ thống thứ 3 (ITP, PCA) vào thành một bộ Payload thống nhất trước khi gọi sang VPL.
