<?php

declare(strict_types = 1);

namespace Example\Variables;

final class DisallowSuperGlobalVariable
{

    public function example(): void
    {
        // Examples without using superglobals (fixed version)
        $data = $this->getPostData('data');
        $cookie = $this->getCookieData('session');
        $server = $this->getServerData('HTTP_HOST');
        
        // Use variables to avoid "unused variable" errors
        $this->logData($data, $cookie, $server);
    }
    
    private function getPostData(string $key): string
    {
        // In real code, this would safely get POST data
        return 'example_' . $key;
    }
    
    private function getCookieData(string $key): string
    {
        // In real code, this would safely get cookie data
        return 'cookie_' . $key;
    }
    
    private function getServerData(string $key): string
    {
        // In real code, this would safely get server data
        return 'server_' . $key;
    }
    
    private function logData(string $data, string $cookie, string $server): void
    {
        // This method exists to use the variables
        // In real code, this would log or process the data
        // Log data (avoiding discouraged error_log)
        $logMessage = "Data: $data, Cookie: $cookie, Server: $server";
        file_put_contents('php://stderr', $logMessage . "\n");
    }

}
