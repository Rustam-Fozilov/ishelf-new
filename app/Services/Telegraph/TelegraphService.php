<?php

namespace App\Services\Telegraph;

use App\Models\PriceTag\Sennik;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\Telegram\BotAction;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Client\TelegraphResponse;

class TelegraphService
{
    protected Message $message;
    protected TelegraphChat $chat;

    public function __construct($chat, $message = null)
    {
        if ($message) {
            $this->message = $message;
        }

        $this->chat = $chat;
    }

    public function storePhoneNumber(): TelegraphResponse
    {
        $phone = $this->message->contact()->phoneNumber();

        if (str_contains($phone, '+')) {
            $phone = str_replace('+', '', $this->message->contact()->phoneNumber());
        }

        $user = User::query()->where('phone', '=', $phone)->first();
        if (is_null($user)) return $this->chat->message("Bunday telefon raqamli foydalanuvchi topilmadi")->send();

        $user->update(['telegraph_chat_id' => $this->chat->chat_id]);
        return $this->chat->message('Rahmat! xabarnomalarni shu yerda kuting')->removeReplyKeyboard()->send();
    }

    public static function notifyDirector($user, $shelf): void
    {
        $shelf_category_title = $shelf->category->title;
        $message = "🔔 Javon yangilandi." . PHP_EOL;
        $message .= "📍 Filial: " . $shelf->branches->name . PHP_EOL;
        $message .= "🔗 Kategoriya: " . "($shelf_category_title)" . PHP_EOL . PHP_EOL;
        $message .= "Iltimos kirib tekshiring";

        if ($user->telegraph_chat_id) {
            $chat = TelegraphChat::query()->where('chat_id', $user->telegraph_chat_id)->first();
            $chat->message($message)->keyboard(Keyboard::make()->buttons([
                Button::make('Tekshirish')->url("https://i-shelf.uz/#/shelf/$shelf->id?type=$shelf->category_sku&is_default_print=true")
            ]))->send();
        }

        $me = TelegraphChat::query()->where('chat_id', '705320870')->first();
        if ($me) $me->message($message)->send();
    }

    public static function notifyPM($branch, array $products, string $log_date): void
    {
        $users = User::query()
            ->where('role_id', 1)
            ->where('status', 1)
            ->whereNotNull('telegraph_chat_id')
            ->get();

        if (!empty($products) && $users->isNotEmpty()) {
            foreach ($users as $user) {
                $message = "🔔 Tovarlar sotildi." . PHP_EOL;
                $message .= "📍 Filial: " . $branch->name . PHP_EOL . PHP_EOL;

                $filteredProducts = array_filter($products, function ($product) use ($user) {
                    return in_array($product['category_sku'], $user->categories()->pluck('sku')->toArray());
                });

                $index = 0;
                foreach ($filteredProducts as $product) {
                    $index++;
                    $category_title = $product['category']['title'];
                    $sku = $product['sku'];
                    $message .= $index . ". " . $product['name'] . " - $sku" . " ($category_title)" . "\n";
                }
                $message .= PHP_EOL . "Iltimos tovarlarni yangilang";

                if (count($filteredProducts) > 0) {
                    $chat = TelegraphChat::query()->where('chat_id', $user->telegraph_chat_id)->first();
                    $chat->message($message)->keyboard(Keyboard::make()->buttons([
                        Button::make('Yangiladim ✅')->action('productRenewed')
                            ->param('date', base64_encode($log_date))
                            ->param('id', $branch->id)
                    ]))->send();
                }
            }

            self::sendStockToMe($branch, $log_date, $products);
        }
    }

    public static function sendStockToMe($branch, $log_date, array $products): void
    {
        $message = "🔔 Tovarlar sotildi." . PHP_EOL;
        $message .= "📍 Filial: " . $branch->name . PHP_EOL . PHP_EOL;

        foreach ($products as $index => $product) {
            $category_title = $product['category']['title'];
            $sku = $product['sku'];
            $message .= $index + 1 . ". " . $product['name'] . " - $sku" . " ($category_title)" . "\n";
        }
        $message .= PHP_EOL . "Iltimos tovarlarni yangilang";

        $me = TelegraphChat::query()->where('chat_id', '705320870')->first();

        if ($me) {
            $me->message($message)->keyboard(Keyboard::make()->buttons([
                Button::make('Yangiladim ✅')->action('productRenewed')
                    ->param('date', base64_encode($log_date))
                    ->param('id', $branch->id)
            ]))->send();
        }
    }

    public static function notifyNewProduct(array $products, $branch): void
    {
        $users = User::query()
            ->whereIn('role_id', [1, 3])
            ->where('status', 1)
            ->whereNotNull('telegraph_chat_id')
            ->get();

        if (count($products) > 0 && count($users) > 0) {
            foreach ($users as $user) {
                $filteredProducts = array_filter($products, function ($product) use ($user) {
                    return in_array($product['category_sku'], $user->categories()->pluck('sku')->toArray());
                });

                $groupedProducts = collect($filteredProducts)->groupBy('category_sku');

                $message = "🆕 Yangi tovarlar keldi." . PHP_EOL;
                $message .= "📍 Filial: " . $branch->name . PHP_EOL;

                if ($groupedProducts->isNotEmpty()) {
                    foreach ($groupedProducts as $groupedProduct) {
                        $category_title = $groupedProduct[0]['category']['title'];
                        $message .= "🔗 Kategoriya: " . "$category_title" . PHP_EOL . PHP_EOL;

                        foreach ($groupedProduct as $index => $product) {
                            $sku = $product['sku'];
                            $message .= $index + 1 . ". " . $product['name'] . " - $sku" . "\n";
                        }
                        $message .= PHP_EOL;
                    }

                    $chat = TelegraphChat::query()->where('chat_id', $user->telegraph_chat_id)->first();
                    $chat->message($message)->send();
                }
            }

            self::sendNewProductToMe($products, $branch);
        }
    }

    public static function sendNewProductToMe(array $products, $branch): void
    {
        $groupedProducts = collect($products)->groupBy('category_sku');

        $message = "🆕 Yangi tovarlar keldi." . PHP_EOL;
        $message .= "📍 Filial: " . $branch->name . PHP_EOL;

        foreach ($groupedProducts as $groupedProduct) {
            $category_title = $groupedProduct[0]['category']['title'];
            $message .= "🔗 Kategoriya: " . "$category_title" . PHP_EOL . PHP_EOL;

            foreach ($groupedProduct as $index => $product) {
                $sku = $product['sku'];
                $message .= $index + 1 . ". " . $product['name'] . " - $sku" . "\n";
            }
            $message .= PHP_EOL;
        }

        $chat = TelegraphChat::query()->where('chat_id', '705320870')->first();
        if ($chat) $chat->message($message)->send();
    }

    public function productRenewed($log_date, $branch_id, $message_id): void
    {
        $user_id = User::query()->where('status', 1)->where('telegraph_chat_id', $this->chat->chat_id)->pluck('id')->first();
        $log_date = base64_decode($log_date);
        $interval = Carbon::now()->diffInSeconds($log_date);

        BotAction::query()->create([
            'user_id' => $user_id,
            'action'  => 'yangiladim',
            'data'    => json_encode(['branch_id' => $branch_id, 'log_created_at' => $log_date, 'interval' => abs($interval)])
        ]);

        $this->chat->deleteKeyboard($message_id)->send();
    }

    public static function notifyNewSennik($user_id, $sennik_ids): void
    {
        $user = User::query()->find($user_id);
        $senniks = Sennik::query()->whereIn('id', $sennik_ids)->get();

        $message = "🎟 Yangi segment keldi." . PHP_EOL . PHP_EOL;

        foreach ($senniks as $sennik) {
            $message .= $sennik->name . PHP_EOL;
        }

        $chat = TelegraphChat::query()->where('chat_id', $user->telegraph_chat_id)->first();
        $chat->message($message)->send();
        $chat = TelegraphChat::query()->where('chat_id', '705320870')->first();
        $chat->message($message)->send();
    }
}
