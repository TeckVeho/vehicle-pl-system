<?php

namespace App\Services;

use OpenAI\Client;
use OpenAI\Factory;
use Illuminate\Support\Facades\Log;


class OpenAIService
{
    protected Client $client;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key');

        if (empty($apiKey)) {
            Log::info('OpenAI API key is not configured. Please set MIX_OPENAI_API_KEY in your .env file.');
        }

        $factory = new Factory();
        $this->client = $factory->withApiKey($apiKey)->make();
    }

    /**
     * Get the OpenAI client instance
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Generate chat completion
     *
     * @param array $messages
     * @param string $model
     * @param array $options
     * @return array
     */
    public function chatCompletion(array $messages, string $model = 'gpt-5-mini', array $options = []): array
    {
        $params = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $options);

        $response = $this->client->chat()->create($params);

        return $response->toArray();
    }

    /**
     * Get chat completion text content
     *
     * @param array $messages
     * @param string $model
     * @param array $options
     * @return string|null
     */
    public function getChatCompletionText(array $messages, string $model = 'gpt-5-mini', array $options = []): ?string
    {
        $response = $this->chatCompletion($messages, $model, $options);

        return $response['choices'][0]['message']['content'] ?? null;
    }

    /**
     * Translate Japanese text to English and Chinese (Simplified)
     *
     * @param string $japaneseText
     * @param string $model
     * @return array
     */
    public function translateFromJapanese(string $japaneseText, string $model = 'gpt-5-mini'): array
    {
        $prompt = "Translate the following Japanese text to English and Chinese (Simplified).

CRITICAL: LINE BREAKS MUST BE PRESERVED EXACTLY
- Every line break in the original text MUST be preserved in the translation
- Use \\n in JSON strings to represent each line break
- Do NOT combine multiple lines into one
- Do NOT remove empty lines
- The structure and spacing of the original must remain identical

TRANSLATION GUIDELINES:
1. Preserve ALL line breaks and formatting EXACTLY as they appear in the original text.
2. For numbered items (①, ②, etc.), translate to (1), (2), etc. in English and （1）, （2）, etc. in Chinese.
3. **MANDATORY INDUSTRY TERMINOLOGY:**
   You MUST use the following translations for specific terms. Do NOT translate these literally.

   [Vehicle/Parts]
   - 観音扉 → \"Double swing doors\" or \"Barn doors\" (En) / \"对开门\" (Cn)
   - ウイング → \"Wing-body truck\" (En) / \"翼展车\" or \"飞翼车\" (Cn)
   - ゲート → \"Tail lift\" or \"Power gate\" (En) / \"尾板\" or \"升降尾板\" (Cn)
   - デジタコ → \"Digital Tachograph\" (En) / \"数字行驶记录仪\" (Cn)
   - カゴ / カゴ車 → \"Roll box pallet\" or \"Roll cage\" (En) / \"笼车\" or \"笼箱\" (Cn)
   - 輪止め → \"Wheel chocks\" (En) / \"车轮挡块\" (Cn)
   - 番重 → \"Plastic crate\" or \"Food tray\" (En) / \"周转箱\" or \"胶箱\" (Cn)
   - 空番重 → \"Empty crate\" or \"Empty tray\" (En) / \"空箱\" (Cn)

   [Operations/Loading]
   - ラッシング → \"Lashing belt\" or \"Cargo strap\" (En) / \"紧固带\" or \"拉紧器\" (Cn)
   - バラ積み → \"Floor loading\" or \"Loose loading\" (En) / \"散装\" or \"人工堆码\" (Cn)
   - 手積み / 手降ろし → \"Manual loading\" / \"Manual unloading\" (En) / \"人工装货\" / \"人工卸货\" (Cn)
   - 横持ち → \"Shuttling\" or \"Cross-dock transfer\" (En) / \"短驳\" or \"库内搬运\" (Cn)
   - 才数 (さいすう) → \"Cubic volume (CFT)\" (En) / \"材积\" or \"体积\" (Cn)
   - 個口 (こぐち) → \"Parcels\" or \"Units\" (En) / \"件数\" or \"包裹数\" (Cn)
   - 立て (e.g., 1立て) → \"Roll cage\" or \"Cage\" (unit count) (En) / \"笼\" or \"台\" (Cn)
   - 点呼 → \"Roll call\" or \"Pre-trip inspection\" (En) / \"点名\" or \"发车前检查\" (Cn)
   - 待機 → \"Waiting time\" or \"Detention\" (En) / \"待机\" or \"等候卸货\" (Cn)
   - 宵積み → \"Overnight loading\" or \"Pre-loading\" (En) / \"隔夜装货\" or \"预装\" (Cn)
   - トンボ帰り → \"Immediate return trip\" (En) / \"即刻返程\" (Cn)
   - 地場 → \"Local hauling\" or \"Short-haul\" (En) / \"短途运输\" or \"市内配送\" (Cn)
   - ○○便 (e.g. CVS便) → \"○○ Route\" (En) / \"○○配送\" or \"○○班车\" (Cn)

   [Accidents/Incidents]
   - もらい事故 → \"Non-fault accident\" or \"Hit by other\" (En) / \"被动事故\" or \"对方全责\" (Cn)
   - 自損 → \"Solo vehicle accident\" (En) / \"单方事故\" (Cn)
   - バック事故 → \"Reversing accident\" (En) / \"倒车事故\" (Cn) NOTE: Never use 'Back'.
   - オカマを掘る → \"Rear-end collision\" (En) / \"追尾\" (Cn) NOTE: Do NOT translate literally.
   - ゴールデンパッケージ (GP) → \"Vehicle damage incident (Golden Package)\" (En) / \"自车损坏的'黄金包装'事故\" (Cn)
   - サンキュー事故 → \"Thank-you accident\" (En) / \"谢谢事故\" (Cn)
   - だろう運転 → \"Wishful driving\" (En) / \"臆测驾驶\" (Cn)
   - かもしれない運転 → \"Defensive driving\" (En) / \"防御性驾驶\" (Cn)
   - 有責 → \"At-fault\" (En) / \"有责\" or \"全责\" (Cn)
   - 無責 → \"Not-fault\" (En) / \"无责\" (Cn)

   [Other]
   - 業態 → \"Route\" or \"Client\" (En) / \"业务内容\" or \"配送线路\" (Cn)
   - マック便 → \"McDonald's Delivery\" (En) / \"麦当劳配送便\" (Cn)
   - 第１事業部 → \"Dai 1 Business Division\" (En) / \"第一事业部\" (Chinese)

4. Maintain professional and formal tone appropriate for business incident reports.
5. Translate dates and times naturally (e.g., \"12月6日　6時20分頃\" → \"December 6, around 6:20 AM\" / \"12月6日　约6:20\").

Return the result in JSON format with the following structure (no markdown, no extra text):
{
    \"english\": \"translated English text\",
    \"chinese\": \"translated Chinese (Simplified) text\",
    \"review_needed\": \"List any Japanese terms here that you were unsure how to translate or that might be potential mistranslations even after using the list. If everything is clear, set this to null or an empty string.\"
}

IMPORTANT: Reply with ONLY this JSON object, no code block and no explanation. In the JSON strings, use \\n for EVERY line break.

Japanese text to translate:
{$japaneseText}";

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional translator specializing in business and logistics documents. Always return valid JSON format only, without any additional text or explanation. CRITICAL: Preserve EVERY line break from the original text using \\n in JSON strings. Do NOT combine lines or remove empty lines. The line break structure must match the original exactly. Translate with accuracy and maintain the formal tone of business reports.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        $response = $this->getChatCompletionText($messages, $model);

        if (empty($response)) {
            Log::info('Failed to get translation response from OpenAI.');
        }

        // Parse JSON response with flags to preserve unicode and slashes
        $decoded = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // If response is not valid JSON, try to extract JSON from the response
            preg_match('/\{[^}]+\}/s', $response, $matches);
            if (!empty($matches[0])) {
                $decoded = json_decode($matches[0], true, 512, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::info('Invalid JSON response from OpenAI: ' . json_last_error_msg());
            }
        }

        // Ensure line breaks are preserved (convert \n to actual line breaks)
        $english = $decoded['english'] ?? '';
        $chinese = $decoded['chinese'] ?? '';
        $review_needed = $decoded['review_needed'] ?? '';

        // Replace escaped newlines with actual newlines if they exist as literal strings
        $english = str_replace(['\\n', '\\\\n'], "\n", $english);
        $chinese = str_replace(['\\n', '\\\\n'], "\n", $chinese);

        return [
            'english' => $english,
            'chinese' => $chinese,
            //'review_needed' => $review_needed,
        ];
    }
}

