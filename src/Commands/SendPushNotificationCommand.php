<?php

namespace ACTCMS\Api\Commands;

use ACTCMS\Api\Models\PushNotification;
use ACTCMS\Api\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:push-notification:send', 'Gửi thông báo đẩy đến ứng dụng di động')]
class SendPushNotificationCommand extends Command
{
    protected $signature = 'cms:push-notification:send
                            {--title= : Tiêu đề thông báo}
                            {--message= : Tin nhắn thông báo}
                            {--type=general : Loại thông báo (general, order, promotion, system)}
                            {--target=all : Loại mục tiêu (all, platform, user_type, user)}
                            {--target-value= : Giá trị mục tiêu (android/ios for platform, customer/admin for user_type, user_id for user)}
                            {--action-url= : URL hành động khi thông báo được nhấp vào}
                            {--image-url= : URL hình ảnh cho thông báo phong phú}
                            {--data= : Dữ liệu JSON bổ sung}
                            {--schedule= : Lịch trình thông báo (Y-m-d H:i:s format)}
                            {--user-type=customer : Kiểu người dùng khi mục tiêu là người dùng (customer, admin)}
                            {--interactive : Chạy ở chế độ tương tác}';

    protected $description = 'Gửi thông báo đẩy đến các ứng dụng di động với nhiều tùy chọn nhắm mục tiêu khác nhau';

    protected PushNotificationService $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        parent::__construct();
        $this->pushNotificationService = $pushNotificationService;
    }

    public function handle(): int
    {
        if ($this->option('interactive')) {
            return $this->handleInteractive();
        }

        return $this->handleNonInteractive();
    }

    protected function handleInteractive(): int
    {
        $this->info('🚀 Người gửi thông báo đẩy');
        $this->line('');

        // Get notification details
        $title = $this->ask('Tiêu đề thông báo');
        if (empty($title)) {
            $this->error('Tiêu đề là bắt buộc');

            return self::FAILURE;
        }

        $message = $this->ask('Tin nhắn thông báo');
        if (empty($message)) {
            $this->error('Tin nhắn là bắt buộc');

            return self::FAILURE;
        }

        $type = $this->choice('Loại thông báo', ['general', 'order', 'promotion', 'system'], 'general');

        $target = $this->choice('Đối tượng mục tiêu', ['all', 'platform', 'user_type', 'user'], 'all');

        $targetValue = null;
        if ($target === 'platform') {
            $targetValue = $this->choice('Select platform', ['android', 'ios']);
        } elseif ($target === 'user_type') {
            $targetValue = $this->choice('Select user type', ['customer', 'admin']);
        } elseif ($target === 'user') {
            $userType = $this->choice('Select user type', ['customer', 'admin']);
            $userId = $this->ask('Enter user ID');
            if (! is_numeric($userId)) {
                $this->error('User ID must be numeric');

                return self::FAILURE;
            }
            $targetValue = $userId;
        }

        $actionUrl = $this->ask('Action URL (optional)');
        $imageUrl = $this->ask('Image URL (optional)');

        $addData = $this->confirm('Add custom data?', false);
        $data = null;
        if ($addData) {
            $dataInput = $this->ask('Enter JSON data');
            if ($dataInput) {
                $data = json_decode($dataInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Invalid JSON data');

                    return self::FAILURE;
                }
            }
        }

        $schedule = null;
        if ($this->confirm('Lên lịch thông báo?', false)) {
            $schedule = $this->ask('Schedule time (Y-m-d H:i:s format)');
            if ($schedule && ! strtotime($schedule)) {
                $this->error('Định dạng ngày không hợp lệ');

                return self::FAILURE;
            }
        }

        $notificationData = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'target_type' => $target,
            'target_value' => $targetValue,
            'action_url' => $actionUrl,
            'image_url' => $imageUrl,
            'data' => $data,
            'user_type' => $target === 'user' ? $userType : null,
        ];

        if ($schedule) {
            $notificationData['scheduled_at'] = $schedule;
        }

        return $this->sendNotification($notificationData);
    }

    protected function handleNonInteractive(): int
    {
        $title = $this->option('title');
        $message = $this->option('message');

        if (empty($title) || empty($message)) {
            $this->error('Tiêu đề và tin nhắn là bắt buộc. Sử dụng --title và --message tùy chọn hoặc chạy với --interactive');

            return self::FAILURE;
        }

        $target = $this->option('target');
        $targetValue = $this->option('target-value');

        // Validate target and target-value combination
        if (in_array($target, ['platform', 'user_type', 'user']) && empty($targetValue)) {
            $this->error("Giá trị mục tiêu được yêu cầu khi mục tiêu là '{$target}'");

            return self::FAILURE;
        }

        $data = null;
        if ($this->option('data')) {
            $data = json_decode($this->option('data'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Dữ liệu JSON không hợp lệ');

                return self::FAILURE;
            }
        }

        $schedule = $this->option('schedule');
        if ($schedule && ! strtotime($schedule)) {
            $this->error('Định dạng ngày lịch trình không hợp lệ');

            return self::FAILURE;
        }

        $notificationData = [
            'title' => $title,
            'message' => $message,
            'type' => $this->option('type'),
            'target_type' => $target,
            'target_value' => $targetValue,
            'action_url' => $this->option('action-url'),
            'image_url' => $this->option('image-url'),
            'data' => $data,
            'user_type' => $this->option('user-type'),
        ];

        if ($schedule) {
            $notificationData['scheduled_at'] = $schedule;
        }

        return $this->sendNotification($notificationData);
    }

    protected function sendNotification(array $notificationData): int
    {
        try {
            $this->line('');
            $this->info('📱 Đang chuẩn bị gửi thông báo...');

            // Create notification record
            $pushNotification = PushNotification::createFromRequest($notificationData, Auth::id());

            if (isset($notificationData['scheduled_at']) && $notificationData['scheduled_at']) {
                $this->info("✅ Thông báo được lên lịch vào: {$notificationData['scheduled_at']}");
                $this->info("ID thông báo: {$pushNotification->id}");

                return self::SUCCESS;
            }

            // Send immediately
            $result = $this->sendBasedOnTarget($notificationData);

            $this->displayResult($result, $pushNotification);

            return $result['success'] ? self::SUCCESS : self::FAILURE;

        } catch (\Exception $e) {
            $this->error("Không gửi được thông báo: {$e->getMessage()}");
            logger()->error('Lệnh thông báo đẩy không thành công', [
                'error' => $e->getMessage(),
                'data' => $notificationData,
            ]);

            return self::FAILURE;
        }
    }

    protected function sendBasedOnTarget(array $notificationData): array
    {
        $target = $notificationData['target_type'];
        $targetValue = $notificationData['target_value'];

        switch ($target) {
            case 'all':
                return $this->pushNotificationService->sendToAll($notificationData);

            case 'platform':
                return $this->pushNotificationService->sendToPlatform($targetValue, $notificationData);

            case 'user_type':
                return $this->pushNotificationService->sendToUserType($targetValue, $notificationData);

            case 'user':
                $userType = $notificationData['user_type'] ?? 'customer';

                return $this->pushNotificationService->sendToUser($userType, (int) $targetValue, $notificationData);

            default:
                throw new \InvalidArgumentException("Loại mục tiêu không hợp lệ: {$target}");
        }
    }

    protected function displayResult(array $result, PushNotification $pushNotification): void
    {
        $this->line('');

        if ($result['success']) {
            $this->info('✅ Thông báo đã được gửi thành công!');
        } else {
            $this->error('❌ Không gửi được thông báo');
        }

        $this->table(['Metric', 'Count'], [
            ['Sent', $result['sent_count'] ?? 0],
            ['Failed', $result['failed_count'] ?? 0],
        ]);

        if (isset($result['message'])) {
            $this->line("Message: {$result['message']}");
        }

        $this->line("ID thông báo: {$pushNotification->id}");
        $this->line('');
    }
}
