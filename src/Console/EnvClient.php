<?php

namespace YangJiSen\QuickPassport\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;

class EnvClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:env-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the client ID and secret to the environment file';

    protected bool $alreadyPersonal = false;
    protected bool $alreadyPassword = false;

    /**
     * Execute the console command.
     */
    public function handle(ClientRepository $clients): void
    {
        $this->info($this->description);

        /* Todo 如果存在多个怎么处理 */
        $clients->getClient()->each(function ($client) {
            /* 前缀匹配 */
            $envPrefix = $client->password_client == 1 ? 'PASSWORD_GRAN' : 'PERSONAL_ACCESS';
            $configPrefix = $client->password_client == 1 ? 'password_grant' : 'personal_access';

            $byId = '';
            if($client->personal_access_client == 1) {
                if($this->alreadyPersonal) $byId = $client->getKey();
                $this->alreadyPersonal = true;
            }

            if($client->password_client == 1) {
                if($this->alreadyPassword) $byId = $client->getKey();
                $this->alreadyPassword = true;
            }

            /* 匹配的键值 */
            $envIdKey = "PASSPORT_{$envPrefix}_CLIENT{$byId}_ID";
            $configIdKey = "passport.{$configPrefix}_client{$byId}.id";

            $envSecretKey = "PASSPORT_{$envPrefix}_CLIENT{$byId}_SECRET";
            $configSecretKey = "passport.{$configPrefix}_client{$byId}.secret";

            /* 修改配置文件 */
            $this->line('<comment>Client ID:</comment> '.$client->getKey());
            $this->writeNewEnvironmentFileWith($envIdKey, $client->getKey(), $configIdKey);

            $this->line('<comment>Client secret:</comment> '.$client->secret);
            $this->writeNewEnvironmentFileWith($envSecretKey, $client->secret, $configSecretKey);
        });
    }


    /**
     * Write a new environment file with the given key.
     *
     * @param string $envKey
     * @param string $enVal
     * @param string $configKey
     * @return bool
     */
    protected function writeNewEnvironmentFileWith(string $envKey, string $enVal, string $configKey)
    {

        $replaced = preg_replace(
            $this->keyReplacementPattern($envKey, $configKey),
            "{$envKey}={$enVal}",
            $input = file_get_contents($this->laravel->environmentFilePath())
        );

        /* 配置不存在则追加写入 */
        if(!preg_match("/^{$envKey}=.*/m", $input)) {
            $lineEndingCount = [
                "\r\n" => substr_count($input, "\r\n"),
                "\r" => substr_count($input, "\r"),
                "\n" => substr_count($input, "\n"),
            ];

            $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

            if(Str::endsWith($envKey, '_ID')) $envKey = $eol.$envKey;

            file_put_contents($this->laravel->environmentFilePath(), "{$envKey}={$enVal}{$eol}", FILE_APPEND);
            return true;
        }

        if ($replaced === $input || $replaced === null) return false;

        file_put_contents($this->laravel->environmentFilePath(), $replaced);

        return true;
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern($envKey, $configKey)
    {
        $val = Arr::get($this->laravel['config'], $configKey, '');

        $escaped = preg_quote('='.$val, '/');

        return "/^{$envKey}{$escaped}/m";
    }


}
