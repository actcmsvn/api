# Push Notification Commands

Thư mục này chứa các lệnh quản lý thông báo đẩy trong gói API ACTCMS.

## Available Commands

### 1. Send Push Notification Command

**Command:** `cms:push-notification:send`

Gửi thông báo đẩy đến các ứng dụng di động với nhiều tùy chọn nhắm mục tiêu khác nhau.

#### Usage Examples

**Interactive Mode:**
```bash
php artisan cms:push-notification:send --interactive
```

**Send to All Users:**
```bash
php artisan cms:push-notification:send \
  --title="Ra mắt sản phẩm mới" \
  --message="Check out our latest products!" \
  --type="promotion"
```

**Send to Specific Platform:**
```bash
php artisan cms:push-notification:send \
  --title="Bản cập nhật iOS có sẵn" \
  --message="Update your app to get the latest features" \
  --target="platform" \
  --target-value="ios"
```

**Send to User Type:**
```bash
php artisan cms:push-notification:send \
  --title="Cảnh báo của quản trị viên" \
  --message="System maintenance scheduled" \
  --target="user_type" \
  --target-value="admin"
```

**Send to Specific User:**
```bash
php artisan cms:push-notification:send \
  --title="Cập nhật đơn hàng" \
  --message="Your order has been shipped" \
  --target="user" \
  --target-value="123" \
  --user-type="customer"
```

**Schedule Notification:**
```bash
php artisan cms:push-notification:send \
  --title="Khuyến mại chớp nhoáng" \
  --message="50% off everything!" \
  --schedule="2024-12-25 09:00:00"
```

**With Rich Content:**
```bash
php artisan cms:push-notification:send \
  --title="Bài viết mới" \
  --message="Read our latest blog post" \
  --action-url="/blog/latest-post" \
  --image-url="https://example.com/image.jpg" \
  --data='{"category":"blog","post_id":123}'
```

#### Options

- `--title` - Tiêu đề thông báo (bắt buộc)
- `--message` - Tin nhắn thông báo (bắt buộc)
- `--type` - Loại thông báo (general, order, promotion, system) [mặc định: general]
- `--target` - Loại mục tiêu (all, platform, user_type, user) [mặc định: all]
- `--target-value` - Giá trị mục tiêu (required for platform, user_type, user targets)
- `--action-url` - URL để mở khi thông báo được nhấp vào
- `--image-url` - URL hình ảnh cho thông báo phong phú
- `--data` - Dữ liệu JSON bổ sung
- `--schedule` - Lịch trình thông báo (Y-m-d H:i:s format)
- `--user-type` - Kiểu người dùng khi mục tiêu là người dùng (customer, admin) [mặc định: customer]
- `--interactive` - Chạy ở chế độ tương tác

### 2. Process Scheduled Notifications Command

**Command:** `cms:push-notification:process-scheduled`

Xử lý và gửi thông báo đẩy theo lịch trình đã định.

#### Usage Examples

**Process All Due Notifications:**
```bash
php artisan cms:push-notification:process-scheduled
```

**Limit Processing:**
```bash
php artisan cms:push-notification:process-scheduled --limit=10
```

**Dry Run (Preview Only):**
```bash
php artisan cms:push-notification:process-scheduled --dry-run
```

#### Options

- `--limit` - Số lượng thông báo tối đa để xử lý [mặc định: 50]
- `--dry-run` - Hiển thị những gì sẽ được xử lý mà không thực sự gửi

#### Scheduling

Bạn có thể thêm lệnh này vào trình lập lịch Laravel của mình trong `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Process scheduled notifications every minute
    $schedule->command('cms:push-notification:process-scheduled')
             ->everyMinute()
             ->withoutOverlapping();
}
```

## Điều kiện tiên quyết

Trước khi sử dụng các lệnh này, hãy đảm bảo rằng:

1. **FCM Configuration** được thiết lập trong bảng quản trị:
   - Go to Settings → API Settings
   - Configure FCM Project ID
   - Upload Firebase service account JSON file

2. **Device Tokens** are registered:
   - Mobile apps should register device tokens via the API
   - Tokens are stored in the `device_tokens` table

3. **Database Tables** are migrated:
   - `push_notifications`
   - `push_notification_recipients`
   - `device_tokens`

## Notification Types

- `general` - General announcements
- `order` - Order-related notifications
- `promotion` - Marketing and promotional content
- `system` - System alerts and maintenance notices

## Target Types

- `all` - Send to all active device tokens
- `platform` - Send to specific platform (android/ios)
- `user_type` - Send to specific user type (customer/admin)
- `user` - Send to specific user by ID

## Error Handling

Các lệnh bao gồm xử lý lỗi toàn diện:

- Invalid FCM configuration
- Missing device tokens
- Invalid JSON data
- Network failures
- Invalid token cleanup

Tất cả lỗi đều được ghi vào nhật ký ứng dụng để gỡ lỗi.

## Monitoring

Kiểm tra trạng thái thông báo trong cơ sở dữ liệu:

```sql
-- View recent notifications
SELECT * FROM push_notifications ORDER BY created_at DESC LIMIT 10;

-- Check delivery rates
SELECT 
    title,
    sent_count,
    delivered_count,
    read_count,
    (delivered_count / sent_count * 100) as delivery_rate,
    (read_count / delivered_count * 100) as read_rate
FROM push_notifications 
WHERE sent_count > 0;
```
