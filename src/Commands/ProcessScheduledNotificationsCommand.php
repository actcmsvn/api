<?php

namespace ACTCMS\Api\Commands;

use ACTCMS\Api\Models\PushNotification;
use ACTCMS\Api\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:push-notification:process-scheduled', 'Xử lý và gửi thông báo đẩy theo lịch trình')]
class ProcessScheduledNotificationsCommand extends Command
{
    protected $signature = 'cms:push-notification:process-scheduled
                            {--limit=50 : Số lượng thông báo tối đa để xử lý}
                            {--dry-run : Hiển thị những gì sẽ được xử lý mà không thực sự gửi}';

    protected $description = 'Xử lý và gửi thông báo đẩy theo lịch trình đến hạn';

    protected PushNotificationService $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        parent::__construct();
        $this->pushNotificationService = $pushNotificationService;
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info('🕐 Đang xử lý thông báo đẩy theo lịch trình...');
        $this->line('');

        // Get scheduled notifications that are due
        $notifications = PushNotification::query()
            ->where('status', 'scheduled')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', Carbon::now());
            })
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('✅ Không có thông báo theo lịch trình để xử lý');

            return self::SUCCESS;
        }

        $this->info("Found {$notifications->count()} notification(s) to process");
        $this->line('');

        if ($dryRun) {
            $this->warn('🔍 CHẾ ĐỘ CHẠY THỬ - Thực tế sẽ không có thông báo nào được gửi đi');
            $this->line('');
        }

        $processed = 0;
        $successful = 0;
        $failed = 0;

        foreach ($notifications as $notification) {
            $processed++;

            $this->line("Đang xử lý thông báo #{$notification->id}: {$notification->title}");

            if ($dryRun) {
                $this->line("  → Sẽ gửi đến: {$notification->target_type}" .
                    ($notification->target_value ? " ({$notification->target_value})" : ''));

                continue;
            }

            try {
                $result = $this->sendNotification($notification);

                if ($result['success']) {
                    $successful++;
                    $this->line("  ✅ Đã gửi thành công (sent: {$result['sent_count']}, failed: {$result['failed_count']})");
                } else {
                    $failed++;
                    $this->line("  ❌ Thất bại: {$result['message']}");
                }

            } catch (\Exception $e) {
                $failed++;
                $this->line("  ❌ Lỗi: {$e->getMessage()}");

                // Mark notification as failed
                $notification->markAsFailed($e->getMessage());

                logger()->error('Xử lý thông báo theo lịch trình không thành công', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->line('');
        $this->info('📊 Tóm tắt quá trình xử lý:');
        $this->table(['Số liệu', 'Đếm'], [
            ['Tổng số đã xử lý', $processed],
            ['Thành công', $successful],
            ['Thất bại', $failed],
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function sendNotification(PushNotification $notification): array
    {
        $notificationData = [
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'target_type' => $notification->target_type,
            'target_value' => $notification->target_value,
            'action_url' => $notification->action_url,
            'image_url' => $notification->image_url,
            'data' => $notification->data,
        ];

        $result = match ($notification->target_type) {
            'all' => $this->pushNotificationService->sendToAll($notificationData),
            'platform' => $this->pushNotificationService->sendToPlatform($notification->target_value, $notificationData),
            'user_type' => $this->pushNotificationService->sendToUserType($notification->target_value, $notificationData),
            'user' => $this->pushNotificationService->sendToUser('customer', (int) $notification->target_value, $notificationData),
            default => throw new \InvalidArgumentException("Loại mục tiêu không hợp lệ: {$notification->target_type}")
        };

        // Update notification status
        if ($result['success']) {
            $notification->markAsSent($result['sent_count'], $result['failed_count']);
        } else {
            $notification->markAsFailed($result['message'] ?? 'Lỗi không xác định');
        }

        return $result;
    }
}
