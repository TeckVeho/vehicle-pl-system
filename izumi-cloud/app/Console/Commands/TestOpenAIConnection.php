<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI;

class TestOpenAIConnection extends Command
{
    protected $signature = 'openai:test';
    protected $description = 'Test OpenAI API connection and configuration';

    public function handle()
    {
        $this->info('=== Testing OpenAI Connection ===');
        $this->newLine();

        // Check API key
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            $this->error('❌ OPENAI_API_KEY is not configured in .env file');
            return 1;
        }
        
        $this->info('✅ API Key found: ' . substr($apiKey, 0, 10) . '...' . substr($apiKey, -4));
        
        // Check model
        $model = config('services.openai.model', 'gpt-4-turbo-preview');
        $this->info('✅ Model configured: ' . $model);
        $this->newLine();

        // Test simple connection
        $this->info('Testing simple API call...');
        try {
            $client = OpenAI::client($apiKey);
            
            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "Hello from OpenAI!" in one sentence.'],
                ],
                'max_tokens' => 50,
            ]);

            $content = $response->choices[0]->message->content;
            $this->info('✅ Connection successful!');
            $this->line('Response: ' . $content);
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error('❌ Connection failed: ' . $e->getMessage());
            return 1;
        }

        // Test JSON mode (required for route calculation)
        $this->info('Testing JSON response format...');
        try {
            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that outputs JSON.'],
                    ['role' => 'user', 'content' => 'Return a JSON object with fields: status (success), message (Hello World), timestamp (current time)'],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            $json = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->info('✅ JSON mode working correctly!');
                $this->line('Response: ' . json_encode($json, JSON_PRETTY_PRINT));
            } else {
                $this->error('❌ JSON parsing failed');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ JSON mode test failed: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('🎉 All tests passed! OpenAI is ready to use.');
        
        return 0;
    }
}
