<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Relay\Facades\Relay;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\PendingRequest;

use function Laravel\Prompts\note;
use function Laravel\Prompts\textarea;

class MCP extends Command
{
    protected $signature = 'prism:mcp';

    public function handle()
    {
        $response = $this->agent(textarea('Prompt'))->asText();

        note($response->text);
    }

    protected function agent(string $prompt): PendingRequest
    {
        return Prism::text()
            ->using(Provider::Gemini, 'gemini-2.5-flash')
            // ->withSystemPrompt(view('prompts.nova-v2'))
            ->withPrompt($prompt)
            ->withTools([
                ...Relay::tools('gemini'),
            ])
            ->usingTopP(1)
            ->withMaxSteps(99)
            ->withMaxTokens(8192);
    }
}