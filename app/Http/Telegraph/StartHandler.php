<?php

namespace App\Http\Telegraph;

use App\Services\Telegraph\TelegraphService;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Database\QueryException;
use Illuminate\Support\Stringable;

class StartHandler extends WebhookHandler
{
    public function start(): void
    {
        $this->chat->message("Salom, " . $this->message->from()->firstName() . " 👋" . PHP_EOL . "⬇️ Kontaktingizni yuboring (tugmani bosib)")
            ->replyKeyboard(ReplyKeyboard::make()->buttons([
                ReplyButton::make("☎️ Kontaktni Yuborish")->requestContact(),
            ])->resize())
            ->send();
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $service = new TelegraphService($this->chat, $this->message);

        try {
            if ($this->message->contact()?->phoneNumber()) {
                $service->storePhoneNumber();
            }
        } catch (\Exception|\Throwable|QueryException $e) {
            // error chiqsa o'zimga jo'natyapman
            $this->sendThrowToMe($e);
        }
    }

    public function productRenewed($date, $id): void
    {
        try {
            $service = new TelegraphService($this->chat, $this->message);
            $service->productRenewed($date, $id, $this->messageId);
        } catch (\Exception|\Throwable|QueryException $e) {
            // error chiqsa o'zimga jo'natyapman
            $this->sendThrowToMe($e);
        }
    }

    public function sendThrowToMe(\Throwable|\Exception|QueryException $e): void
    {
        $message = "Message: " . $e->getMessage() . PHP_EOL;
        $message .= "File: " . $e->getFile() . PHP_EOL;
        $message .= "Line: " . $e->getLine() . PHP_EOL;
        $message .= "ChatId: " . $this->chat->chat_id . PHP_EOL;
        $message .= "Phone: " . $this->message?->contact()?->phoneNumber() ?? null . PHP_EOL;

        $rustam = TelegraphChat::query()->where('chat_id', '705320870')->first();
        $rustam->message($message)->send();
    }
}
